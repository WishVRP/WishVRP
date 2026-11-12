<?php

namespace Tests\Feature;

use App\Mail\CustomerUpdateMail;
use App\Models\BlockedCheckout;
use App\Models\ExportRule;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Support\Customs;
use App\Support\ExportCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExportCheckTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $type = 'einhandmesser'): Product
    {
        return Product::create(['name' => 'Damast-Einhandmesser', 'slug' => 'damast-einhandmesser', 'category' => 'damast', 'status' => 'aktiv', 'price' => 500, 'stock' => 3, 'knife_type' => $type, 'weight_g' => 800]);
    }

    private function buyer(string $country): array
    {
        return ['name' => 'Kim', 'email' => 'kim@example.org', 'consent' => '1', 'agb' => '1', 'versand' => 'versand', 'street' => 'Weg 1', 'postal_code' => '1000', 'city' => 'Stadt', 'country_code' => $country];
    }

    public function test_rule_precedence_and_safe_default(): void
    {
        $this->assertSame('erlaubt', ExportCheck::evaluate('DE', 'einhandmesser')['action']);
        $this->assertSame('pruefen', ExportCheck::evaluate('GB', 'einhandmesser')['action']);       // keine Regel → vorsichtig prüfen
        $this->assertSame('erlaubt', ExportCheck::evaluate('GB', 'kein_messer')['action']);         // Ringe überall
        ExportRule::create(['country' => 'GB', 'knife_type' => '*', 'action' => 'erlaubt', 'reason' => 'Test']);
        ExportRule::create(['country' => 'GB', 'knife_type' => 'einhandmesser', 'action' => 'gesperrt', 'reason' => 'Einfuhrverbot laut Test-Auskunft']);
        $this->assertSame('gesperrt', ExportCheck::evaluate('GB', 'einhandmesser')['action']);      // Land+Art schlägt Land+alle
        $this->assertSame('erlaubt', ExportCheck::evaluate('GB', 'kuechenmesser')['action']);
        $this->assertSame('8211 93 00', ExportCheck::evaluate('GB', 'einhandmesser')['hs']);
    }

    public function test_blocked_country_cannot_order_and_attempt_is_logged_with_reason(): void
    {
        Mail::fake();
        ExportRule::create(['country' => 'GB', 'knife_type' => 'einhandmesser', 'action' => 'gesperrt', 'reason' => 'Einfuhrverbot für Einhandmesser']);
        $p = $this->product();

        $this->post($p->url(), $this->buyer('GB'))->assertSessionHasErrors('country_code');
        $this->assertSame(0, Order::query()->count());
        $b = BlockedCheckout::query()->firstOrFail();
        $this->assertSame('GB', $b->country);
        $this->assertSame('Einfuhrverbot für Einhandmesser', $b->reason);
        $this->assertSame('kim@example.org', $b->email);
        Mail::assertNothingSent();

        $this->getJson('/versandkosten/?land=GB&produkt='.$p->id)->assertJsonPath('export.action', 'gesperrt');
        // Abholung bleibt möglich
        $this->post($p->url(), array_merge($this->buyer('GB'), ['versand' => 'abholung']))->assertSessionHasNoErrors();
    }

    public function test_review_country_becomes_request_without_payment_with_note(): void
    {
        Mail::fake();
        $eu = ShippingZone::query()->where('name', 'EU')->first();
        ShippingRate::create(['shipping_zone_id' => $eu->id, 'max_weight_g' => 5000, 'dhl_price' => 17.49]);
        $p = $this->product('klappmesser');

        $this->post($p->url(), $this->buyer('FR'))->assertSessionHasNoErrors();
        $o = Order::query()->latest('id')->first();
        $this->assertSame('17.99', $o->shipping_amount);
        $this->assertStringContainsString('Einfuhr prüfen', $o->export_note);
        $html = preg_replace('/\s+/', ' ', (new CustomerUpdateMail($o, 'eingang'))->render());
        $this->assertStringContainsString('Wir prüfen den Versand in dein Land', $html);
        $this->assertStringNotContainsString('Zahlung per Überweisung', $html);
    }

    public function test_customs_prepaid_is_calculated_and_added_to_total(): void
    {
        Mail::fake();
        $world = ShippingZone::query()->where('rest_of_world', true)->first();
        $world->update(['customs_mode' => 'vorab', 'duty_percent' => 5, 'vat_percent' => 10, 'customs_fee' => 12]);
        ShippingRate::create(['shipping_zone_id' => $world->id, 'max_weight_g' => 5000, 'dhl_price' => 46.20]);
        ExportRule::create(['country' => 'US', 'knife_type' => 'kuechenmesser', 'action' => 'erlaubt', 'reason' => 'Test']);

        // 500 € Ware, 46,99 € Versand: Zoll 25,00 + EUSt (500+46,99+25)*10 % = 57,20 + 12 = 94,20 → 94,99
        $c = Customs::estimate('US', 500, 46.99);
        $this->assertSame('vorab', $c['mode']);
        $this->assertSame(94.99, $c['amount']);

        $p = $this->product('kuechenmesser');
        $this->getJson('/versandkosten/?land=US&produkt='.$p->id)->assertJsonPath('customs_formatted', '94,99 €')->assertJsonPath('export.action', 'erlaubt');
        $this->post($p->url(), $this->buyer('US'))->assertSessionHasNoErrors();
        $o = Order::query()->latest('id')->first();
        $this->assertSame('94.99', $o->customs_amount);
        $this->assertSame(round(500 + 46.99 + 94.99, 2), $o->totalDue());
        $this->assertStringContainsString('8211 92 00', $o->export_note);   // Zollpapiere-Hinweis mit Tarifnummer
        $this->assertStringContainsString('Zoll &amp; Einfuhrabgaben', (new CustomerUpdateMail($o, 'eingang'))->render());
    }

    public function test_receiver_pays_customs_shows_hint_only(): void
    {
        $world = ShippingZone::query()->where('rest_of_world', true)->first();
        $this->assertSame('empfaenger', Customs::estimate('US', 500, 40)['mode']);
        $this->assertNull(Customs::estimate('US', 500, 40)['amount']);
        $this->assertSame('keine', Customs::estimate('AT', 500, 18)['mode']);
        $this->assertTrue($world->customs);
    }

    public function test_edition_blocked_releases_reserved_number(): void
    {
        Mail::fake();
        ExportRule::create(['country' => 'AU', 'knife_type' => 'klappmesser', 'action' => 'gesperrt', 'reason' => 'Test-Sperre']);
        $this->post('/anfrage/', ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'number' => 40, 'versand' => 'versand', 'country_code' => 'AU',
            'name' => 'A', 'email' => 'a@example.org', 'consent' => '1', 'street' => 'x', 'postal_code' => '2000', 'city' => 'Sydney'])->assertSessionHasErrors('country_code');
        $this->assertSame('frei', \App\Models\EditionPiece::query()->where('number', 40)->value('status'));
        $this->assertSame(1, BlockedCheckout::query()->count());
    }
}
