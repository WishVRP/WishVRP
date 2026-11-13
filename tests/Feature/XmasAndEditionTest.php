<?php

namespace Tests\Feature;

use App\Models\EditionPiece;
use App\Models\Order;
use App\Models\SiteEvent;
use App\Models\XmasEntry;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmasAndEditionTest extends TestCase
{
    public function test_xmas_terms_name_organizer_and_mark_missing_age_and_countries(): void
    {
        $html = $this->get('/weihnachtsaktion/')->getContent();
        $this->assertStringContainsString('Veranstalter:', $html);
        $this->assertStringContainsString('Wannenstraße 33', $html);
        $this->assertStringContainsString('Mindestalter folgt', $html);
        $this->assertStringContainsString('meldet dich nicht zum Newsletter an', $html);
    }

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    /* ---------------- Edition 100 ---------------- */

    public function test_nineteen_sold_are_red_named_are_green_free_are_selectable(): void
    {
        $this->assertSame(19, EditionPiece::where('status', 'vergeben')->count());
        EditionPiece::where('number', 20)->update(['status' => 'vergeben', 'buyer_name' => 'Erika M.', 'show_name' => true]);
        EditionPiece::where('number', 21)->update(['status' => 'vergeben', 'buyer_name' => 'Max K.', 'show_name' => false]);

        $html = $this->get('/klappmesser/')->assertOk()->getContent();
        $this->assertSame(100, substr_count($html, 'class="serial"'));
        $this->assertSame(20, substr_count($html, 'is-sold'), '19 + 1 ohne Namen rot');
        $this->assertStringContainsString('data-tip="Nr. 20 · gehört Erika M."', $html);
        $this->assertStringContainsString('data-tip="Nr. 21 · verkauft"', $html);
        $this->assertStringNotContainsString('Max K.', $html, 'Name ohne Zustimmung darf nicht erscheinen');
        $this->assertStringContainsString('data-tip="Nr. 22 · noch frei" aria-label="Nr. 22 · noch frei"', $html);
        $this->assertStringContainsString('data-number="22"', $html);
        $this->assertStringNotContainsString('data-number="95"', $html, 'Sonderstücke nicht frei wählbar');
        $this->assertStringContainsString('<option value="22"', $html, 'freie Nummer im Formular');
        $this->assertStringNotContainsString('<option value="5"', $html);
    }

    public function test_ordering_a_number_reserves_it_and_payment_makes_it_green_with_name(): void
    {
        $base = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'Anna B.', 'email' => 'a@example.org', 'consent' => '1', 'source' => '/klappmesser/'];
        $this->post('/anfrage/', $base + ['number' => 42, 'show_name' => '1', 'public_name' => 'Anna B.'])->assertSessionHasNoErrors();

        $order = Order::sole();
        $this->assertStringEndsWith('Nr. 42', $order->item);
        $piece = EditionPiece::where('number', 42)->first();
        $this->assertSame(['reserviert', $order->id], [$piece->status, $piece->order_id]);
        $this->assertStringContainsString('Nr. 42 · reserviert', $this->get('/klappmesser/')->getContent());

        $this->post('/anfrage/', $base + ['number' => 42, 'email' => 'b@example.org'])->assertSessionHasErrors('number');

        $order->update(['status' => 'bezahlt']);
        $this->assertStringContainsString('data-tip="Nr. 42 · gehört Anna B."', $this->get('/klappmesser/')->getContent());

        $order->update(['status' => 'storniert']);
        $this->assertSame('frei', EditionPiece::where('number', 42)->value('status'));
    }

    public function test_name_is_only_stored_with_consent(): void
    {
        $base = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'A', 'email' => 'a@example.org', 'consent' => '1', 'source' => '/klappmesser/'];
        $this->post('/anfrage/', $base + ['number' => 30, 'public_name' => 'Ohne Häkchen']);
        Order::sole()->update(['status' => 'bestaetigt']);

        $html = $this->get('/klappmesser/')->getContent();
        $this->assertStringContainsString('data-tip="Nr. 30 · verkauft"', $html);
        $this->assertStringNotContainsString('Ohne Häkchen', $html);
    }

    /* ---------------- Weihnachtsaktion ---------------- */

    /** Standard (kostenlos): E-Mail → persönlicher Link → Spiel starten. */
    private function start(string $email = 'spieler@example.org'): array
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => $email, 'conditions' => '1'])->assertOk()->assertJson(['status' => 'sent'])->json();
        $t = substr($r['dev_link'], strpos($r['dev_link'], '?t=') + 3);
        $this->get('/weihnachtsaktion/?t='.$t)->assertOk()->assertSee('data-ref="'.$t.'"', false);

        return $this->postJson('/weihnachtsaktion/start/', ['ref' => $t])->assertOk()->json() + ['ref' => $t];
    }

    /** Optional: SMS-Bestätigung. */
    private function smsStart(string $email, string $phone): array
    {
        config(['ulbrich.xmas.verification' => 'sms']);
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => $email, 'phone' => $phone, 'conditions' => '1'])->assertOk()->assertJson(['status' => 'sms'])->json();
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code']])->assertOk();

        return $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertOk()->json() + ['ref' => $r['ref']];
    }

    private function playFor(int $score, string $email = 'spieler@example.org'): array
    {
        $r = $this->start($email);
        $this->travel(config('ulbrich.xmas.seconds') + 1)->seconds();

        return $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => $score])->assertOk()->json();
    }

    public function test_tiers_and_cap(): void
    {
        $this->assertSame([0, 0, 5, 5, 10, 10], array_map([XmasEntry::class, 'percentFor'], [0, 5, 6, 13, 14, 500]));
        config(['ulbrich.xmas.tiers' => [['min' => 1, 'percent' => 25]]]);
        $this->assertSame(10, XmasEntry::percentFor(5), 'nie mehr als 10 %');
    }

    public function test_email_link_is_required_and_works_only_once(): void
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'anna@example.org', 'conditions' => '1'])->assertOk()->json();
        $this->assertStringContainsString('/weihnachtsaktion/?t=', $r['dev_link']);
        $entry = XmasEntry::sole();
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $entry->verify_token])->assertForbidden();   // Link noch nicht geöffnet

        $this->get('/weihnachtsaktion/?t='.$entry->verify_token)->assertSee('data-xmas-go', false);
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $entry->verify_token])->assertJson(['status' => 'ok']);
        $this->get('/weihnachtsaktion/?t='.$entry->verify_token)->assertSee('Du hast schon gespielt');
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $entry->verify_token])->assertJson(['status' => 'played']);
    }

    public function test_full_round_gives_code_and_only_once_per_email(): void
    {
        $done = $this->playFor(24, 'Anna.Test+xmas@GoogleMail.com');
        $this->assertSame([24, 10], [$done['score'], $done['percent']]);
        $this->assertMatchesRegularExpression('/^XMAS-[A-Z0-9]{6}$/', $done['code']);
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'annatest@gmail.com', 'conditions' => '1'])->assertStatus(422);
        $this->assertSame(1, XmasEntry::count());
    }

    public function test_disposable_addresses_and_connection_limit(): void
    {
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'x@mailinator.com', 'conditions' => '1'])->assertStatus(422);
        foreach (['1', '2', '3'] as $n) {
            $this->postJson('/weihnachtsaktion/anmelden/', ['email' => "{$n}@example.org", 'conditions' => '1'])->assertOk();
        }
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'd@example.org', 'conditions' => '1'])->assertStatus(429);
        $this->assertSame(3, XmasEntry::count());
    }

    public function test_cheating_is_capped_or_rejected(): void
    {
        $fast = $this->start('schnell@example.org');
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $fast['token'], 'score' => 50])->assertJson(['score' => 0, 'percent' => 0, 'code' => null]);
        $this->assertSame(130, $this->playFor(999, 'viel@example.org')['score']);
    }

    public function test_one_discount_per_household_and_admin_warning(): void
    {
        $code = $this->playFor(10, 'erika@example.org')['code'];
        $order = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'consent' => '1', 'source' => '/klappmesser/'];
        $erika = ['name' => 'Erika Muster', 'phone' => '0171 1234567', 'street' => 'Hauptstraße 5', 'postal_code' => '74196'];

        $this->post('/anfrage/', $order + $erika + ['email' => 'andere@example.org', 'discount_code' => $code])->assertSessionHasErrors('discount_code');
        $this->post('/anfrage/', $order + array_diff_key($erika, ['phone' => 1]) + ['email' => 'erika@example.org', 'discount_code' => $code])->assertSessionHasErrors('phone');
        $this->post('/anfrage/', $order + $erika + ['email' => 'erika@example.org', 'discount_code' => strtolower($code)])->assertSessionHasNoErrors();
        $this->assertSame(['5', '664.05'], [(string) Order::sole()->discount_percent, Order::sole()->amount]);
        $this->post('/anfrage/', $order + $erika + ['email' => 'erika@example.org', 'discount_code' => $code])->assertSessionHasErrors('discount_code');

        // zweite E-Mail derselben Person: andere Nummer, aber gleiche Anschrift → abgelehnt
        RateLimiterReset::clear();
        $code2 = $this->playFor(10, 'erika.zweit@example.org')['code'];
        $this->post('/anfrage/', $order + ['name' => 'E. Muster', 'phone' => '0160 9999999', 'street' => 'Hauptstr. 5', 'postal_code' => '74196', 'email' => 'erika.zweit@example.org', 'discount_code' => $code2])
            ->assertSessionHasErrors(['discount_code' => 'Pro Person bzw. Haushalt kann nur ein Weihnachtsrabatt eingelöst werden.']);

        // dritte E-Mail, alles anders außer dem Namen → Einlösung möglich, aber Warnung in der Verwaltung
        RateLimiterReset::clear();
        $code3 = $this->playFor(10, 'erika.drei@example.org')['code'];
        $this->post('/anfrage/', $order + ['name' => 'Erika Muster', 'phone' => '0152 1111111', 'street' => 'Bergweg 1', 'postal_code' => '70173', 'email' => 'erika.drei@example.org', 'discount_code' => $code3])->assertSessionHasNoErrors();
        $third = Order::query()->latest('id')->first();
        $this->assertCount(1, $third->discountWarnings());
        $this->assertStringContainsString('Erika Muster', $third->discountWarnings()[0]);
    }

    /* ---- optionale SMS-Bestätigung ---- */

    public function test_mobile_numbers_are_normalised_and_landlines_rejected(): void
    {
        $this->assertSame('+491711234567', XmasEntry::mobileE164('0171 1234567'));
        $this->assertSame('+491711234567', XmasEntry::mobileE164('+49 (171) 123-4567'));
        $this->assertSame('+491711234567', XmasEntry::mobileE164('0049 171 1234567'));
        $this->assertSame('+436641234567', XmasEntry::mobileE164('+43 664 1234567'));
        $this->assertNull(XmasEntry::mobileE164('07131 123456'), 'Festnetz');
    }

    public function test_sms_mode_one_phone_one_game_and_redeem_with_same_phone(): void
    {
        $r = $this->smsStart('sms@example.org', '0171 5555555');
        $this->assertSame('ok', $r['status']);
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'neu@example.org', 'phone' => '+49 171 5555555', 'conditions' => '1'])->assertStatus(422);

        $this->travel(config('ulbrich.xmas.seconds') + 1)->seconds();
        $code = $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => 10])->json('code');
        $order = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'S', 'consent' => '1', 'email' => 'sms@example.org'];
        $this->post('/anfrage/', $order + ['phone' => '0172 0000000', 'discount_code' => $code])
            ->assertSessionHasErrors(['discount_code' => 'Der Rabattcode gilt nur zusammen mit der Handynummer, mit der gespielt wurde.']);
        $this->post('/anfrage/', $order + ['phone' => '0171 5555555', 'discount_code' => $code])->assertSessionHasNoErrors();
    }

    public function test_sms_code_wrong_attempts_and_production_guard(): void
    {
        config(['ulbrich.xmas.verification' => 'sms']);
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'b@example.org', 'phone' => '0160 1112223', 'conditions' => '1'])->json();
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertForbidden();
        foreach (range(1, 5) as $i) {
            $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code'] === '000000' ? '111111' : '000000'])->assertStatus(422);
        }
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code']])->assertStatus(429);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->app['env'] = 'production';
        config(['services.sms.driver' => 'log']);
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'p@example.org', 'phone' => '0171 2000000', 'conditions' => '1'])->assertStatus(503);
    }

    public function test_seven_driver_sends_sms_via_api(): void
    {
        \Illuminate\Support\Facades\Http::fake(['gateway.seven.io/*' => \Illuminate\Support\Facades\Http::response(['success' => '100'])]);
        config(['ulbrich.xmas.verification' => 'sms', 'services.sms.driver' => 'seven', 'services.sms.seven_key' => 'test-key']);
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 's@example.org', 'phone' => '0171 3000000', 'conditions' => '1'])->assertOk()->json();
        $this->assertArrayNotHasKey('dev_code', $r);
        \Illuminate\Support\Facades\Http::assertSent(fn ($req) => $req->url() === 'https://gateway.seven.io/api/sms' && $req->hasHeader('X-Api-Key', 'test-key') && $req['to'] === '+491713000000');
    }

    public function test_page_conditions_teaser_and_campaign_end(): void
    {
        $html = $this->get('/weihnachtsaktion/')->assertOk()->getContent();
        $this->assertStringContainsString('data-xmas-form', $html);
        $this->assertStringNotContainsString('name="phone"', $html, 'kostenlose Variante ohne Handynummer');
        $this->assertStringContainsString('Jede Person kann nur einmal teilnehmen.', $html);
        $this->assertStringContainsString('Ein Weihnachtsrabatt pro Person bzw. Haushalt.', $html);
        $this->assertStringContainsString('höchstens 10 %', $html);
        $this->assertStringContainsString('Gültig bis 31. Dezember 2026', $html);
        $this->assertStringContainsString('href="/weihnachtsaktion/"', $this->get('/')->getContent());
        $this->assertStringContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent());

        $this->post('/e/', ['n' => 'spiel', 'p' => '/weihnachtsaktion/'], ['User-Agent' => 'Mozilla/5.0 Chrome/130'])->assertNoContent();
        $this->assertSame(1, SiteEvent::where('name', 'spiel')->count());

        config(['ulbrich.xmas.ends_on' => now()->subDay()->toDateString(), 'ulbrich.xmas.valid_until' => now()->subDay()->toDateString()]);
        $this->assertStringContainsString('Die Weihnachtsaktion ist vorbei', $this->get('/weihnachtsaktion/')->getContent());
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'spaet@example.org', 'conditions' => '1'])->assertStatus(410);
        $this->assertStringContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent(), 'Feld bleibt für Newsletter-Codes');
    }
}

/** Hilfsklasse: IP-Limit zwischen zwei Teilnahmen im selben Test zurücksetzen. */
final class RateLimiterReset
{
    public static function clear(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
    }
}
