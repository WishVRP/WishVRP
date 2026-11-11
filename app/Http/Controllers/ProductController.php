<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteEvent;
use App\Support\PageMeta;
use App\Support\Schema;
use App\Support\Stripe;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Produktseiten /shop/{kategorie}/{produkt}/ und Kauf (Stripe, sonst verbindliche Anfrage). */
class ProductController extends Controller
{
    public static function categoryKey(string $segment): ?string
    {
        foreach (Product::CATEGORIES as $key => [, $path]) {
            if ($path === '/shop/'.$segment.'/') {
                return $key;
            }
        }

        return null;
    }

    private function find(string $category, string $slug): Product
    {
        $key = self::categoryKey($category);
        abort_unless($key, 404);

        return Product::query()->visible()->where('category', $key)->where('slug', $slug)->firstOrFail();
    }

    public static function description(Product $p): string
    {
        $base = trim(($p->short ?: Str::limit(strip_tags(Str::markdown((string) $p->description)), 90, '')), ' .');
        $text = "{$p->name}: {$base}. Handgefertigt in der Messermanufaktur Ulbrich, ".number_format((float) $p->price, 0, ',', '.').' €.';
        if (mb_strlen($text) < 140) {
            $text .= ' Versand in Deutschland 7,99 € oder Abholung in Stein am Kocher.';
        }

        return mb_strlen($text) > 160 ? rtrim(mb_substr($text, 0, 157), ' ,.;').'…' : $text;
    }

    public function show(string $category, string $slug): View
    {
        $product = $this->find($category, $slug);
        $path = $product->url();
        $meta = new PageMeta(
            title: Str::limit($product->name, 48, ''),
            description: self::description($product),
            path: $path,
            ogImage: $product->imageUrls()[0] ?? null,
            breadcrumb: [['name' => 'Startseite', 'path' => '/'], ['name' => 'Shop', 'path' => '/shop/'],
                ['name' => $product->categoryName(), 'path' => Product::CATEGORIES[$product->category][1]], ['name' => $product->name, 'path' => $path]],
        );
        $schema = Schema::graph([Schema::webPage($meta), Schema::breadcrumb($meta), Schema::shopProduct($product)]);

        return view('pages.shop.product', ['meta' => $meta, 'schema' => $schema, 'product' => $product, 'path' => $path, 'c' => config('ulbrich')]);
    }

    public function buy(Request $request, string $category, string $slug, OrderController $orders): RedirectResponse
    {
        $product = $this->find($category, $slug);
        if (filled($request->input('website'))) {
            return back();
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:3000'],
            'consent' => ['accepted'],
            'agb' => ['accepted'],
            'discount_code' => ['nullable', 'string', 'max:20'],
            'versand' => ['required', 'in:versand,abholung'],
            'street' => ['required_if:versand,versand', 'nullable', 'string', 'max:160'],
            'postal_code' => ['required_if:versand,versand', 'nullable', 'string', 'max:20'],
            'city' => ['required_if:versand,versand', 'nullable', 'string', 'max:120'],
            'country_code' => ['required_if:versand,versand', 'nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Support\Shipping::countries()))],
        ], ['country_code.required_if' => 'Bitte wähle das Land für den Versand.', 'country_code.in' => 'Bitte wähle ein Land aus der Liste.','consent.accepted' => 'Bitte bestätige die Datenschutzhinweise.', 'agb.accepted' => 'Bitte bestätige AGB und Widerrufsbelehrung.',
            'street.required_if' => 'Bitte gib deine Lieferadresse an.', 'postal_code.required_if' => 'Bitte gib deine Postleitzahl an.', 'city.required_if' => 'Bitte gib deinen Ort an.']);

        if (! $product->available()) {
            return back()->withErrors(['versand' => 'Dieses Stück ist leider gerade nicht mehr verfügbar.'])->withInput();
        }
        // Versand: serverseitig nach Land, Gewicht (Produkt) und Preistabelle bzw. DHL Express berechnet
        $ship = $data['versand'] === 'versand'
            ? \App\Support\Shipping::quote($data['country_code'] ?? 'DE', $product->weight_g, $data['postal_code'] ?? null, $data['city'] ?? null)
            : null;
        $shipping = $ship ? $ship['price'] : 0.0;   // null = Versandkosten auf Anfrage
        $order = Order::create([
            'type' => 'messer', 'status' => 'neu',
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            'item' => $product->name, 'quantity' => 1, 'product_id' => $product->id, 'amount' => $product->price,
            'street' => $data['street'] ?? null, 'postal_code' => $data['postal_code'] ?? null, 'city' => $data['city'] ?? null,
            'country' => $ship['country_name'] ?? null, 'country_code' => $ship['country'] ?? null,
            'shipping_method' => $data['versand'], 'shipping_amount' => $shipping,
            'shipping_note' => $ship ? ($ship['price'] === null ? 'Versandkosten auf Anfrage' : $ship['label'].($ship['customs'] ? ' · zzgl. Zoll/Einfuhrabgaben im Zielland' : '')) : null,
            'source_path' => $product->url(),
            'payment_method' => Stripe::enabled() ? 'stripe' : 'ueberweisung',
            'hold_until' => Stripe::enabled() ? now()->addMinutes((int) config('ulbrich.booking.hold_minutes')) : null,
        ]);
        SiteEvent::create(['name' => 'anfrage', 'path' => $product->url(), 'occurred_on' => now()->toDateString()]);

        if ($ship && $ship['price'] === null) {
            // Versandkosten unbekannt (z. B. Land ohne Tabellenpreis): als Anfrage, wir nennen die Kosten, dann Zahlung
            \App\Support\OrderMail::received($order);

            return redirect($product->url().'#kaufen')->with('order_ok', $order->number);
        }

        return $orders->payOrder($order, $data, $product->url().'#kaufen');
    }
}
