<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteEvent;
use App\Models\Voucher;
use App\Support\Cart;
use App\Support\CourseCalendar;
use App\Support\Customs;
use App\Support\ExportCheck;
use App\Support\OrderMail;
use App\Support\OrderPaid;
use App\Support\Shipping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Warenkorb (/warenkorb/) und Kasse (/kasse/): Messer und Kursbuchungen, Zahlung per Überweisung.
 * An der Kasse entsteht je Kurstag ein Kurs-Vorgang (Kalender, Erinnerung, Umbuchen) und für alle Messer zusammen ein Shop-Vorgang.
 */
class CartController extends Controller
{
    public function add(Request $request): RedirectResponse
    {
        if ($request->input('type') === 'kurs') {
            $data = $request->validate([
                'kurs' => ['required', 'string'],
                'datum' => ['required', 'date_format:Y-m-d'],
                'teilnehmer' => ['required', 'integer', 'min:1', 'max:'.config('ulbrich.booking.max_persons')],
            ], ['datum.required' => 'Bitte wähle im Kalender einen freien Tag.', 'kurs.required' => 'Bitte wähle einen Kurs.']);
            $course = Course::query()->published()->where('slug', $data['kurs'])->first();
            if (! $course || ! CourseCalendar::bookable($course)) {
                return back()->withErrors(['kurs' => 'Bitte wähle einen online buchbaren Kurs.'])->withInput();
            }
            $start = Carbon::parse($data['datum'])->startOfDay();
            if ($start->lt(CourseCalendar::first()) || ! CourseCalendar::available($course, $start)) {
                return back()->withErrors(['datum' => 'Dieser Tag ist leider nicht frei. Bitte wähle einen anderen.'])->withInput();
            }
            Cart::addCourse($course, $start->toDateString(), (int) $data['teilnehmer']);

            return redirect('/warenkorb/')->with('cart_added', $course->name.' am '.$start->locale('de')->isoFormat('D. MMMM YYYY'));
        }

        $data = $request->validate(['produkt' => ['required', 'integer'], 'menge' => ['nullable', 'integer', 'min:1', 'max:20']]);
        $product = Product::query()->visible()->find($data['produkt']);
        if (! $product || ! $product->available()) {
            return back()->withErrors(['menge' => 'Dieses Stück ist leider gerade nicht verfügbar.']);
        }
        Cart::addProduct($product, (int) ($data['menge'] ?? 1));

        return redirect('/warenkorb/')->with('cart_added', $product->name);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['key' => ['required', 'string', 'max:40'], 'menge' => ['required', 'integer', 'min:0', 'max:20']]);
        Cart::setQty($data['key'], (int) $data['menge']);

        return redirect('/warenkorb/');
    }

    public function checkout(Request $request, OrderController $orders): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect('/kasse/');   // Honeypot
        }
        $lines = Cart::lines();
        if (! $lines) {
            return redirect('/warenkorb/');
        }
        if (collect($lines)->whereNotNull('problem')->isNotEmpty()) {
            return redirect('/warenkorb/')->withErrors(['cart' => 'Bitte prüfe deinen Warenkorb – mindestens eine Position ist nicht mehr verfügbar.']);
        }
        $hasProducts = collect($lines)->contains('type', 'product');
        $hasCourses = collect($lines)->contains('type', 'kurs');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:3000'],
            'consent' => ['accepted'],
            'agb' => ['accepted'],
            'discount_code' => ['nullable', 'string', 'max:20'],
            'gutschein_code' => ['nullable', 'string', 'max:20'],
            'versand' => [Rule::requiredIf($hasProducts), 'nullable', 'in:versand,abholung'],
            'street' => ['required_if:versand,versand', 'nullable', 'string', 'max:160'],
            'postal_code' => ['required_if:versand,versand', 'nullable', 'string', 'max:20'],
            'city' => ['required_if:versand,versand', 'nullable', 'string', 'max:120'],
            'country_code' => ['required_if:versand,versand', 'nullable', Rule::in(array_keys(Shipping::countries()))],
        ], [
            'consent.accepted' => 'Bitte bestätige die Datenschutzhinweise.',
            'agb.accepted' => $hasCourses ? 'Bitte bestätige AGB, Kursbedingungen und Widerrufsbelehrung.' : 'Bitte bestätige AGB und Widerrufsbelehrung.',
            'street.required_if' => 'Bitte gib deine Lieferadresse an.', 'postal_code.required_if' => 'Bitte gib deine Postleitzahl an.',
            'city.required_if' => 'Bitte gib deinen Ort an.', 'country_code.required_if' => 'Bitte wähle das Land für den Versand.',
            'versand.required' => 'Bitte wähle Versand oder Abholung.',
        ]);

        // Versand, Einfuhr und Zoll für die Messer
        $shipping = null;
        $ship = null;
        $export = null;
        $customs = null;
        $goods = Cart::subtotal($lines, 'product');
        if ($hasProducts && $data['versand'] === 'versand') {
            $ship = Shipping::quote($data['country_code'], Cart::weight($lines), $data['postal_code'] ?? null, $data['city'] ?? null);
            $export = Cart::export($lines, $ship['country']);
            if ($export && $export['action'] === 'gesperrt') {
                ExportCheck::logBlocked($export, $ship['country'], $export['knife_type'], $data + ['item' => 'Warenkorb', 'source_path' => '/kasse/']);
                throw ValidationException::withMessages(['country_code' => $export['message']]);
            }
            $shipping = $ship['price'];
            $customs = $shipping !== null ? Customs::estimate($ship['country'], $goods, (float) $shipping) : null;
        }

        // Rabattcode: einmal prüfen, gilt für alle passenden Positionen (Euro-Betrag nur einmal)
        [$code] = $orders->discount($data);
        $types = collect($lines)->map(fn ($l) => $l['type'] === 'kurs' ? 'kurs' : 'messer')->unique();
        if ($code instanceof DiscountCode) {
            $eligible = $types->filter(fn ($t) => $code->appliesTo($t));
            if ($eligible->isEmpty()) {
                throw ValidationException::withMessages(['discount_code' => 'Dieser Rabattcode gilt nicht für deinen Warenkorb ('.$code->scopeLabel().').']);
            }
            $base = collect($lines)->filter(fn ($l) => $eligible->contains($l['type'] === 'kurs' ? 'kurs' : 'messer'))->sum('total');
            if ($code->min_order !== null && $base < (float) $code->min_order) {
                throw ValidationException::withMessages(['discount_code' => 'Dieser Rabattcode gilt ab einem Bestellwert von '.number_format((float) $code->min_order, 2, ',', '.').' €.']);
            }
        }

        // Kurs-Gutschein (Wertgutschein GS-…): nur für Kursbuchungen
        $voucher = null;
        if (filled($data['gutschein_code'] ?? null)) {
            $voucher = Voucher::usable($data['gutschein_code']);
            $inUse = $voucher && Order::query()->where('voucher_id', $voucher->id)->where('status', '!=', 'storniert')->exists();
            if (! $voucher || $inUse) {
                throw ValidationException::withMessages(['gutschein_code' => 'Dieser Gutscheincode ist ungültig, abgelaufen oder schon eingelöst.']);
            }
            if (! $hasCourses) {
                throw ValidationException::withMessages(['gutschein_code' => 'Kurs-Gutscheine gelten für Kursbuchungen. Für Messer nutze bitte einen Rabattcode.']);
            }
        }

        $contact = ['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            'payment_method' => 'ueberweisung', 'status' => 'neu'];

        $created = DB::transaction(function () use ($lines, $contact, $data, $ship, $shipping, $export, $customs, $goods) {
            $out = [];
            foreach (collect($lines)->where('type', 'kurs') as $l) {
                $end = $l['start']->copy()->addDays(CourseCalendar::days($l['course']) - 1);
                if (! CourseCalendar::available($l['course'], $l['start'])) {
                    return $l['name'];   // Tag inzwischen vergeben
                }
                $out[] = Order::create($contact + [
                    'type' => 'kurs', 'course_id' => $l['course']->id, 'course_start' => $l['start'], 'course_end' => $end,
                    'participants' => $l['qty'], 'amount' => $l['total'], 'source_path' => '/kasse/',
                ]);
            }
            $products = collect($lines)->where('type', 'product')->values();
            if ($products->isNotEmpty()) {
                $items = $products->map(fn ($l) => ['product_id' => $l['product']->id, 'name' => $l['name'], 'qty' => $l['qty'], 'price' => $l['unit'], 'url' => $l['url']])->all();
                $out[] = Order::create($contact + [
                    'type' => 'messer',
                    'item' => Str::limit($products->map(fn ($l) => $l['name'].($l['qty'] > 1 ? ' × '.$l['qty'] : ''))->implode(', '), 187),
                    'items' => $items,
                    'quantity' => $products->sum('qty'),
                    'product_id' => $products->count() === 1 ? $products[0]['product']->id : null,
                    'knife_type' => $products[0]['product']->knife_type,
                    'amount' => $goods,
                    'street' => $data['street'] ?? null, 'postal_code' => $data['postal_code'] ?? null, 'city' => $data['city'] ?? null,
                    'country' => $ship['country_name'] ?? null, 'country_code' => $ship['country'] ?? null,
                    'shipping_method' => $data['versand'], 'shipping_amount' => $ship ? $shipping : 0.0,
                    'shipping_note' => $ship ? ($ship['price'] === null ? 'Versandkosten auf Anfrage' : $ship['label'].(($customs['mode'] ?? null) === 'empfaenger' ? ' · zzgl. Zoll/Einfuhrabgaben im Zielland' : '')) : null,
                    'customs_amount' => ($customs['mode'] ?? null) === 'vorab' ? $customs['amount'] : null,
                    'export_note' => $export ? ExportCheck::note($export, (bool) $ship['customs']) : null,
                    'source_path' => '/kasse/',
                ]);
            }

            return $out;
        });
        if (is_string($created)) {
            return redirect('/warenkorb/')->withErrors(['cart' => "Der gewählte Tag für „{$created}“ ist gerade vergeben worden. Bitte wähle einen anderen."]);
        }

        // Rabatt anwenden: Prozent auf alle passenden Vorgänge, Euro-Betrag auf den ersten; Nutzung zählt einmal
        if ($code) {
            $redeemed = false;
            foreach ($created as $order) {
                if ($code instanceof DiscountCode && (! $code->appliesTo($order->type) || ($code->type === 'amount' && $redeemed))) {
                    continue;
                }
                $orders->applyDiscount($order, $code, ! $redeemed);
                $redeemed = true;
            }
        }
        if ($voucher && ($first = collect($created)->firstWhere('type', 'kurs'))) {
            $first->forceFill(['voucher_id' => $voucher->id, 'voucher_amount' => min((float) $voucher->value, (float) $first->amount)])->save();
        }

        $total = 0.0;
        foreach ($created as $order) {
            $order->refresh();
            $total += $order->totalDue();
            if ($order->totalDue() <= 0) {
                OrderPaid::paid($order, $order->voucher_id ? 'gutschein' : 'rabatt');
            } else {
                if ($order->voucher_id) {
                    Voucher::query()->whereKey($order->voucher_id)->update(['status' => 'eingeloest', 'redeemed_order_id' => $order->id, 'redeemed_at' => now()]);
                }
                OrderMail::received($order);
            }
            SiteEvent::create(['name' => $order->type === 'kurs' ? 'buchung' : 'anfrage', 'path' => '/kasse/', 'occurred_on' => now()->toDateString()]);
        }
        Cart::clear();

        return redirect('/kasse/')->with('checkout_done', [
            'numbers' => collect($created)->pluck('number')->all(),
            'total' => round($total, 2),
            'email' => $data['email'],
            'shipping_open' => $ship && $shipping === null || ($export['action'] ?? null) === 'pruefen',
        ]);
    }
}
