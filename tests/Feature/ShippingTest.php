<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Support\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_round_up_to_99(): void
    {
        $this->assertSame(7.99, Shipping::roundUp(7.69));
        $this->assertSame(7.99, Shipping::roundUp(7.00));
        $this->assertSame(7.99, Shipping::roundUp(7.99));
        $this->assertSame(24.99, Shipping::roundUp(24.10));
        $this->assertSame(10.99, Shipping::roundUp(10.49));
    }

    public function test_germany_keeps_799_and_weight_tiers(): void
    {
        $this->assertSame(7.99, Shipping::quote('DE')['price']);              // 1500 g Standard → bis 5 kg: 7,69 € → 7,99 €
        $this->assertSame(10.99, Shipping::quote('DE', 8000)['price']);       // bis 10 kg: 10,49 € → 10,99 €
        $this->assertNull(Shipping::quote('DE', 20000)['price']);             // darüber: auf Anfrage
        $this->assertSame(7.99, Shipping::domestic());
    }

    public function test_country_zones_and_rest_of_world_with_customs(): void
    {
        $eu = ShippingZone::query()->where('name', 'EU')->first();
        ShippingRate::create(['shipping_zone_id' => $eu->id, 'max_weight_g' => 5000, 'dhl_price' => 17.49, 'label' => 'DHL Paket EU bis 5 kg']);
        $world = ShippingZone::query()->where('rest_of_world', true)->first();
        ShippingRate::create(['shipping_zone_id' => $world->id, 'max_weight_g' => 5000, 'dhl_price' => 46.20, 'label' => 'DHL Paket Welt']);

        $at = Shipping::quote('AT');
        $this->assertSame(17.99, $at['price']);
        $this->assertFalse($at['customs']);
        $this->assertSame('Österreich', $at['country_name']);

        $us = Shipping::quote('US');
        $this->assertSame(46.99, $us['price']);
        $this->assertTrue($us['customs']);

        $this->assertNull(Shipping::quote('CH')['price']);   // Europa außerhalb EU: noch kein Preis → auf Anfrage
    }

    public function test_dhl_express_live_price_used_when_table_has_none(): void
    {
        config(['services.dhl_express' => ['key' => 'k', 'secret' => 's', 'account' => '123', 'test' => true]]);
        Http::fake(['express.api.dhl.com/*' => Http::response(['products' => [
            ['productName' => 'EXPRESS WORLDWIDE', 'totalPrice' => [['currencyType' => 'BILLC', 'priceCurrency' => 'EUR', 'price' => 61.40], ['currencyType' => 'PULCL', 'priceCurrency' => 'USD', 'price' => 66]]],
            ['productName' => 'ECONOMY SELECT', 'totalPrice' => [['currencyType' => 'BILLC', 'priceCurrency' => 'EUR', 'price' => 48.15]]],
        ]])]);

        $q = Shipping::quote('CH', 1500, '8001', 'Zürich');
        $this->assertSame(48.99, $q['price']);                 // günstigstes Produkt, aufgerundet
        $this->assertSame('dhl_express', $q['source']);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/mydhlapi/test/rates') && str_contains($r->url(), 'destinationCountryCode=CH') && $r->hasHeader('Authorization'));
    }

    public function test_quote_endpoint_returns_formatted_price_and_customs_note(): void
    {
        $this->getJson('/versandkosten/?land=DE')->assertOk()->assertJson(['price' => 7.99, 'formatted' => '7,99 €']);
        $this->getJson('/versandkosten/?land=CH')->assertOk()->assertJson(['price' => null])->assertJsonFragment(['customs' => true]);
        $this->getJson('/versandkosten/?land=XYZ')->assertStatus(422);
    }

    public function test_product_purchase_uses_server_side_shipping_and_unknown_country_becomes_request(): void
    {
        Mail::fake();
        $p = Product::create(['name' => 'Damast-Kochmesser', 'slug' => 'damast-kochmesser', 'category' => 'damast', 'status' => 'aktiv', 'price' => 400, 'stock' => 2, 'weight_g' => 1200]);
        $base = ['name' => 'Kim', 'email' => 'kim@example.org', 'consent' => '1', 'agb' => '1', 'versand' => 'versand', 'street' => 'Weg 1', 'postal_code' => '8001', 'city' => 'Zürich'];

        // Schweiz ohne Preis → Anfrage, keine Zahlung, Versand offen
        $this->post($p->url(), $base + ['country_code' => 'CH'])->assertRedirect()->assertSessionHasNoErrors();
        $o = Order::query()->latest('id')->first();
        $this->assertNull($o->shipping_amount);
        $this->assertSame('Schweiz', $o->country);
        $this->assertSame('CH', $o->country_code);
        $this->assertSame('Versandkosten auf Anfrage', $o->shipping_note);
        $html = preg_replace('/\s+/', ' ', (new \App\Mail\CustomerUpdateMail($o, 'eingang'))->render());
        $this->assertStringContainsString('wird dir per E-Mail genannt', $html);
        $this->assertStringNotContainsString('Zahlung per Überweisung', $html);

        // Deutschland → 7,99 € serverseitig, egal was der Browser schickt
        $this->post($p->url(), $base + ['country_code' => 'DE', 'shipping_amount' => '0.01', 'postal_code' => '74196', 'city' => 'Neuenstadt'])->assertSessionHasNoErrors();
        $this->assertSame('7.99', Order::query()->latest('id')->first()->shipping_amount);

        $this->post($p->url(), $base + ['country_code' => 'ZZ'])->assertSessionHasErrors('country_code');
    }

    public function test_product_page_shows_order_summary_before_purchase(): void
    {
        config(['ulbrich.tax_mode' => 'regular']);
        $p = Product::create(['name' => 'Damast-Kochmesser', 'slug' => 'damast-kochmesser', 'category' => 'damast', 'status' => 'aktiv', 'price' => 400, 'stock' => 1, 'weight_g' => 1200]);
        $html = preg_replace('/\s+/', ' ', $this->get($p->url())->assertOk()->getContent());
        $this->assertStringContainsString('Deine Bestellung im Überblick', $html);
        $this->assertStringContainsString('data-ship-value="400.00"', $html);
        $this->assertStringContainsString('<strong data-sum-total>407,99 €</strong>', $html);   // 400 + DHL Deutschland
        $this->assertStringContainsString('Lieferzeit:', $html);
        $this->assertStringContainsString('inkl. 19 % MwSt.', $html);
        $this->assertStringContainsString('/widerruf/', $html);
    }
}
