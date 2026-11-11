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

    /** Anmelden (E-Mail + Handy) → SMS-Code (Testmodus) → bestätigen → Spiel starten. */
    private function start(string $email = 'spieler@example.org', string $phone = '0171 1234567'): array
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => $email, 'phone' => $phone, 'conditions' => '1'])->assertOk()->assertJson(['status' => 'sms'])->json();
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code']])->assertOk()->assertJson(['status' => 'verified']);

        return $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertOk()->json() + ['ref' => $r['ref']];
    }

    private function playFor(int $score, string $email = 'spieler@example.org', string $phone = '0171 1234567'): array
    {
        $r = $this->start($email, $phone);
        $this->travel(31)->seconds();

        return $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => $score])->assertOk()->json();
    }

    public function test_tiers_and_cap(): void
    {
        $this->assertSame([0, 0, 5, 5, 10, 10], array_map([XmasEntry::class, 'percentFor'], [0, 3, 4, 9, 10, 500]));
        config(['ulbrich.xmas.tiers' => [['min' => 1, 'percent' => 25]]]);
        $this->assertSame(10, XmasEntry::percentFor(5), 'nie mehr als 10 %');
    }

    public function test_mobile_numbers_are_normalised_and_landlines_rejected(): void
    {
        $this->assertSame('+491711234567', XmasEntry::mobileE164('0171 1234567'));
        $this->assertSame('+491711234567', XmasEntry::mobileE164('+49 (171) 123-4567'));
        $this->assertSame('+491711234567', XmasEntry::mobileE164('0049 171 1234567'));
        $this->assertSame('+436641234567', XmasEntry::mobileE164('+43 664 1234567'));
        $this->assertNull(XmasEntry::mobileE164('07131 123456'), 'Festnetz');
        $this->assertNull(XmasEntry::mobileE164('171 1234567'), 'ohne Vorwahl');
    }

    public function test_game_needs_confirmed_sms_code(): void
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'anna@example.org', 'phone' => '0151 2345678', 'conditions' => '1'])->assertOk()->json();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $r['dev_code']);
        $this->assertStringContainsString('+4915', $r['message']);
        $this->assertStringNotContainsString('2345678', $r['message'], 'Nummer maskiert');

        $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertForbidden();
        $wrong = $r['dev_code'] === '123456' ? '654321' : '123456';
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $wrong])->assertStatus(422);
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code']])->assertOk();
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertJson(['status' => 'ok']);
    }

    public function test_code_expires_and_attempts_are_limited(): void
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'b@example.org', 'phone' => '0160 1112223', 'conditions' => '1'])->json();
        foreach (range(1, 5) as $i) {
            $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => '000000'])->assertStatus($r['dev_code'] === '000000' ? 200 : 422);
        }
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r['ref'], 'code' => $r['dev_code']])->assertStatus(429);

        $this->travel(61)->seconds();
        $r2 = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'b@example.org', 'phone' => '0160 1112223', 'conditions' => '1'])->assertOk()->json();
        $this->travel(11)->minutes();
        $this->postJson('/weihnachtsaktion/code/', ['ref' => $r2['ref'], 'code' => $r2['dev_code']])->assertStatus(422);
    }

    public function test_full_round_gives_code_and_only_once_per_phone_and_email(): void
    {
        $done = $this->playFor(12, 'Anna.Test+xmas@GoogleMail.com', '0171 1234567');
        $this->assertSame([12, 10], [$done['score'], $done['percent']]);
        $this->assertMatchesRegularExpression('/^XMAS-[A-Z0-9]{6}$/', $done['code']);

        // gleiche Handynummer mit neuer E-Mail → abgelehnt
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'ganz.neu@example.org', 'phone' => '+49 171 1234567', 'conditions' => '1'])->assertStatus(422);
        // gleiche E-Mail mit neuer Nummer → abgelehnt
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'annatest@gmail.com', 'phone' => '0172 9998887', 'conditions' => '1'])->assertStatus(422);
        $this->assertSame(1, XmasEntry::count());
    }

    public function test_aborted_game_counts(): void
    {
        $r = $this->start('weg@example.org', '0176 5554443');
        $this->postJson('/weihnachtsaktion/start/', ['ref' => $r['ref']])->assertJson(['status' => 'played', 'percent' => 0, 'code' => null]);
    }

    public function test_disposable_addresses_and_connection_limit(): void
    {
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'x@mailinator.com', 'phone' => '0171 1000000', 'conditions' => '1'])->assertStatus(422);
        foreach (['1', '2', '3'] as $n) {
            $this->postJson('/weihnachtsaktion/anmelden/', ['email' => "{$n}@example.org", 'phone' => "0171 100000{$n}", 'conditions' => '1'])->assertOk();
        }
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'd@example.org', 'phone' => '0171 1000009', 'conditions' => '1'])->assertStatus(429);
        $this->assertSame(3, XmasEntry::count());
    }

    public function test_production_never_starts_without_real_sms_provider(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        $this->app['env'] = 'production';
        config(['services.sms.driver' => 'log']);
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'p@example.org', 'phone' => '0171 2000000', 'conditions' => '1'])->assertStatus(503);
        config(['services.sms.driver' => 'seven', 'services.sms.seven_key' => null]);
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'p@example.org', 'phone' => '0171 2000000', 'conditions' => '1'])->assertStatus(503);
    }

    public function test_seven_driver_sends_sms_via_api(): void
    {
        \Illuminate\Support\Facades\Http::fake(['gateway.seven.io/*' => \Illuminate\Support\Facades\Http::response(['success' => '100'])]);
        config(['services.sms.driver' => 'seven', 'services.sms.seven_key' => 'test-key']);
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 's@example.org', 'phone' => '0171 3000000', 'conditions' => '1'])->assertOk()->json();
        $this->assertArrayNotHasKey('dev_code', $r, 'kein Code im Browser bei echter SMS');
        \Illuminate\Support\Facades\Http::assertSent(fn ($req) => $req->url() === 'https://gateway.seven.io/api/sms'
            && $req->hasHeader('X-Api-Key', 'test-key') && $req['to'] === '+491713000000' && preg_match('/\b\d{6}\b/', $req['text']));
    }

    public function test_cheating_is_capped_or_rejected(): void
    {
        $fast = $this->start('schnell@example.org', '0171 6666666');
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $fast['token'], 'score' => 50])->assertJson(['score' => 0, 'percent' => 0, 'code' => null]);
        $this->assertSame(60, $this->playFor(999, 'viel@example.org', '0171 7777777')['score']);
    }

    public function test_code_needs_same_email_and_phone_and_only_one_per_household(): void
    {
        $code = $this->playFor(5, 'erika@example.org', '0171 1234567')['code'];
        $order = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'Erika Muster', 'consent' => '1', 'source' => '/klappmesser/', 'postal_code' => '74196'];

        $this->post('/anfrage/', $order + ['email' => 'andere@example.org', 'phone' => '0171 1234567', 'discount_code' => $code])->assertSessionHasErrors('discount_code');
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'phone' => '0172 0000000', 'discount_code' => $code])
            ->assertSessionHasErrors(['discount_code' => 'Der Rabattcode gilt nur zusammen mit der Handynummer, mit der gespielt wurde.']);
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'discount_code' => $code])->assertSessionHasErrors('phone');
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'phone' => '+49 171 1234567', 'discount_code' => strtolower($code)])->assertSessionHasNoErrors();
        $this->assertSame(['5', '664.05'], [(string) Order::sole()->discount_percent, Order::sole()->amount]);
        $this->post("/anfrage/", $order + ["email" => "erika@example.org", "phone" => "0171 1234567", "discount_code" => $code])->assertSessionHasErrors("discount_code");

        // zweite Teilnahme derselben Person mit anderer Nummer/E-Mail → beim Einlösen gleicher Name + PLZ → abgelehnt
        RateLimiterReset::clear();
        $code2 = $this->playFor(5, 'erika.zweit@example.org', '0160 9999999')['code'];
        $this->post('/anfrage/', $order + ['email' => 'erika.zweit@example.org', 'phone' => '0160 9999999', 'discount_code' => $code2])
            ->assertSessionHasErrors(['discount_code' => 'Pro Person bzw. Haushalt kann nur ein Weihnachtsrabatt eingelöst werden.']);
        $this->assertSame(1, Order::count());
    }

    public function test_page_conditions_teaser_and_campaign_end(): void
    {
        $html = $this->get('/weihnachtsaktion/')->assertOk()->getContent();
        $this->assertStringContainsString('data-xmas-form', $html);
        $this->assertStringContainsString('Jede Person kann nur einmal teilnehmen.', $html);
        $this->assertStringContainsString('Ein Weihnachtsrabatt pro Person bzw. Haushalt.', $html);
        $this->assertStringContainsString('höchstens 10 %', $html);
        $this->assertStringContainsString('Gültig bis 31. Dezember 2026', $html);
        $this->assertStringNotContainsString('data-xmas-practice', $html, 'keine Übungsrunde');
        $this->assertStringContainsString('href="/weihnachtsaktion/"', $this->get('/')->getContent());
        $this->assertStringContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent());

        $this->post('/e/', ['n' => 'spiel', 'p' => '/weihnachtsaktion/'], ['User-Agent' => 'Mozilla/5.0 Chrome/130'])->assertNoContent();
        $this->assertSame(1, SiteEvent::where('name', 'spiel')->count());

        config(['ulbrich.xmas.ends_on' => now()->subDay()->toDateString(), 'ulbrich.xmas.valid_until' => now()->subDay()->toDateString()]);
        $this->assertStringContainsString('Die Weihnachtsaktion ist vorbei', $this->get('/weihnachtsaktion/')->getContent());
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'spaet@example.org', 'phone' => '0171 4000000', 'conditions' => '1'])->assertStatus(410);
        $this->assertStringNotContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent());
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
