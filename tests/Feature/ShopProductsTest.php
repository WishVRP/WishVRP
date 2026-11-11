<?php

namespace Tests\Feature;

use App\Models\EditionPiece;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        Storage::fake('public');
    }

    private function product(array $over = []): Product
    {
        $img = UploadedFile::fake()->image('messer.jpg', 1200, 900)->store('products', 'public');

        return Product::create($over + [
            'name' => 'Damast-Kochmesser 21 cm mit Olivenholzgriff', 'category' => 'damast', 'status' => 'aktiv', 'price' => 480, 'stock' => 1,
            'short' => 'Kochmesser mit Damastklinge und Olivenholzgriff, ein Einzelstück aus der Werkstatt.',
            'description' => "Ein **Allrounder** für die Küche.\n\n<script>x</script>", 'images' => [$img],
            'specs' => [['label' => 'Klingenlänge', 'value' => '21 cm']],
        ]);
    }

    private function buyer(array $extra = []): array
    {
        return $extra + ['name' => 'Kim Käufer', 'email' => 'kim@example.org', 'consent' => '1', 'agb' => '1', 'versand' => 'versand',
            'street' => 'Bergweg 1', 'postal_code' => '70173', 'city' => 'Stuttgart'];
    }

    public function test_product_page_has_google_product_data_and_is_listed(): void
    {
        $p = $this->product();
        $this->assertSame('/shop/damastmesser/damast-kochmesser-21-cm-mit-olivenholzgriff/', $p->url());

        $html = $this->get($p->url())->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('480,00 €', $html);
        $this->assertStringContainsString('<strong>Allrounder</strong>', $html);
        $this->assertStringNotContainsString('<script>x</script>', $html);
        preg_match('/<meta name="description" content="([^"]+)"/', $html, $d);
        $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($d[1])));
        $this->assertGreaterThanOrEqual(120, mb_strlen(html_entity_decode($d[1])));

        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);
        $product = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'Product');
        $this->assertSame(['480.00', 'EUR', 'https://schema.org/InStock'], [$product['offers']['price'], $product['offers']['priceCurrency'], $product['offers']['availability']]);
        $this->assertSame('Klingenlänge', $product['additionalProperty'][0]['name']);
        $this->assertStringContainsString('/storage/products/', $product['image'][0]);

        $this->assertStringContainsString($p->url(), $this->get('/shop/damastmesser/')->getContent(), 'in der Kategorie gelistet');
        $this->assertStringContainsString($p->url(), $this->get('/shop/')->getContent(), 'Neu im Shop');
        $this->assertStringContainsString('https://messermanufaktur-ulbrich.de'.$p->url(), $this->get('/sitemap.xml')->getContent());
        $feed = $this->get('/produkte.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<g:price>480.00 EUR</g:price>', $feed);
        $this->assertStringContainsString('<g:availability>in_stock</g:availability>', $feed);
        $this->assertStringContainsString($p->name, $this->get('/llms.txt')->getContent());
    }

    public function test_drafts_are_hidden_and_sold_items_have_no_buy_form(): void
    {
        $draft = $this->product(['status' => 'entwurf', 'slug' => 'entwurf-messer']);
        $this->get($draft->url())->assertNotFound();
        $this->assertStringNotContainsString($draft->url(), $this->get('/sitemap.xml')->getContent());

        $sold = $this->product(['stock' => 0, 'slug' => 'verkauft-messer']);
        $this->assertSame('verkauft', $sold->status);
        $html = $this->get($sold->url())->assertOk()->getContent();
        $this->assertStringContainsString('https://schema.org/SoldOut', $html);
        $this->assertStringNotContainsString('id="kaufen"', $html);
    }

    public function test_buy_without_stripe_is_a_binding_request_with_shipping(): void
    {
        $p = $this->product();
        $this->post($p->url(), $this->buyer(['street' => '']))->assertSessionHasErrors('street');
        $this->post($p->url(), $this->buyer())->assertRedirect($p->url().'#kaufen')->assertSessionHas('order_ok');
        $o = Order::sole();
        $this->assertSame(['messer', 'neu', '480.00', '7.99', 'versand', $p->id], [$o->type, $o->status, $o->amount, $o->shipping_amount, $o->shipping_method, $o->product_id]);

        $this->post($p->url(), $this->buyer(['versand' => 'abholung', 'street' => '', 'postal_code' => '', 'city' => '', 'email' => 'b@example.org']))->assertSessionHasNoErrors();
        $this->assertSame('0.00', Order::latest('id')->first()->shipping_amount);
    }

    public function test_buy_with_stripe_charges_product_and_shipping_and_marks_sold_after_payment(): void
    {
        config(['services.stripe.secret' => 'sk_test_x', 'services.stripe.webhook_secret' => 'whsec_test']);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_prod_1', 'url' => 'https://checkout.stripe.com/p'])]);
        $p = $this->product();

        $this->post($p->url(), $this->buyer())->assertRedirect('https://checkout.stripe.com/p');
        Http::assertSent(fn ($r) => $r['line_items[0][price_data][unit_amount]'] == 48000 && $r['line_items[1][price_data][unit_amount]'] == 799);
        $this->assertFalse($p->fresh()->available(), 'während der Zahlung reserviert');
        $this->post($p->url(), $this->buyer(['email' => 'zweiter@example.org']))->assertSessionHasErrors('versand');

        \App\Support\OrderPaid::paid(Order::sole());
        $p->refresh();
        $this->assertSame([0, 'verkauft'], [$p->stock, $p->status]);
        $this->assertSame('bezahlt', Order::sole()->status);
    }

    public function test_newsletter_discount_applies_to_product(): void
    {
        $p = $this->product();
        \App\Models\DiscountCode::create(['code' => 'NEWS-PROD10', 'percent' => 10, 'email' => 'kim@example.org', 'email_key' => 'kim@example.org', 'source' => 'newsletter', 'valid_until' => now()->addDays(5)]);
        $this->post($p->url(), $this->buyer(['discount_code' => 'NEWS-PROD10']))->assertSessionHasNoErrors();
        $this->assertSame('432.00', Order::sole()->amount);
    }

    public function test_edition_100_online_payment_reserves_and_releases_number(): void
    {
        config(['services.stripe.secret' => 'sk_test_x', 'services.stripe.webhook_secret' => 'whsec_test']);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_ed_1', 'url' => 'https://checkout.stripe.com/e'])]);
        $this->assertStringContainsString('Zahlungspflichtig bestellen', $this->get('/klappmesser/')->getContent());

        $this->post('/anfrage/', ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'number' => 33, 'versand' => 'abholung',
            'name' => 'E', 'email' => 'e@example.org', 'consent' => '1', 'source' => '/klappmesser/'])->assertRedirect('https://checkout.stripe.com/e');
        Http::assertSent(fn ($r) => $r['line_items[0][price_data][unit_amount]'] == 69900 && ! isset($r['line_items[1][price_data][unit_amount]']));
        $this->assertSame('reserviert', EditionPiece::where('number', 33)->value('status'));

        \App\Support\OrderPaid::expired(Order::sole());
        $this->assertSame('frei', EditionPiece::where('number', 33)->value('status'));
    }
}
