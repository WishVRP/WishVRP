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

    /** Anmelden → Link (Token aus der DB) → Spiel starten. */
    private function start(string $email = 'spieler@example.org'): array
    {
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => $email, 'conditions' => '1'])->assertOk()->assertJson(['status' => 'sent']);
        $t = XmasEntry::where('email_key', XmasEntry::emailKey($email))->value('verify_token');

        return $this->postJson('/weihnachtsaktion/start/', ['t' => $t])->assertOk()->json() + ['t' => $t];
    }

    private function playFor(int $score, string $email = 'spieler@example.org'): array
    {
        $r = $this->start($email);
        $this->travel(31)->seconds();

        return $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => $score])->assertOk()->json();
    }

    public function test_tiers_and_cap(): void
    {
        $this->assertSame([0, 0, 5, 5, 10, 10], array_map([XmasEntry::class, 'percentFor'], [0, 3, 4, 9, 10, 500]));
        config(['ulbrich.xmas.tiers' => [['min' => 1, 'percent' => 25]]]);
        $this->assertSame(10, XmasEntry::percentFor(5), 'nie mehr als 10 %');
    }

    public function test_game_only_starts_via_personal_email_link(): void
    {
        $r = $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'anna@example.org', 'conditions' => '1'])->assertOk()->json();
        $this->assertStringContainsString('/weihnachtsaktion/?t=', $r['dev_link'], 'Testlink nur außerhalb der Produktion');
        $this->assertNull(XmasEntry::sole()->started_at);

        $this->postJson('/weihnachtsaktion/start/', ['t' => str_repeat('x', 48)])->assertNotFound();
        $page = $this->get(parse_url($r['dev_link'], PHP_URL_PATH).'?'.parse_url($r['dev_link'], PHP_URL_QUERY))->getContent();
        $this->assertStringContainsString('data-xmas-go', $page);
    }

    public function test_full_round_gives_code_and_only_once_per_person(): void
    {
        $done = $this->playFor(12, 'Anna.Test+xmas@GoogleMail.com');
        $this->assertSame([12, 10], [$done['score'], $done['percent']]);
        $this->assertMatchesRegularExpression('/^XMAS-[A-Z0-9]{6}$/', $done['code']);

        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'annatest@gmail.com', 'conditions' => '1'])->assertStatus(422);
        $t = XmasEntry::sole()->verify_token;
        $this->postJson('/weihnachtsaktion/start/', ['t' => $t])->assertJson(['status' => 'played', 'code' => $done['code']]);
        $this->assertStringContainsString('Du hast schon gespielt', $this->get('/weihnachtsaktion/?t='.$t)->getContent());
        $this->assertSame(1, XmasEntry::count());
    }

    public function test_aborted_game_counts_and_link_works_only_once(): void
    {
        $r = $this->start('weg@example.org');
        $this->postJson('/weihnachtsaktion/start/', ['t' => $r['t']])->assertJson(['status' => 'played', 'percent' => 0, 'code' => null]);
    }

    public function test_disposable_addresses_and_connection_limit(): void
    {
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'x@mailinator.com', 'conditions' => '1'])->assertStatus(422);
        foreach (['a', 'b', 'c'] as $n) {
            $this->postJson('/weihnachtsaktion/anmelden/', ['email' => "{$n}@example.org", 'conditions' => '1'])->assertOk();
        }
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'd@example.org', 'conditions' => '1'])->assertStatus(429);
        $this->assertSame(3, XmasEntry::count());
    }

    public function test_cheating_is_capped_or_rejected(): void
    {
        $fast = $this->start('schnell@example.org');
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $fast['token'], 'score' => 50])->assertJson(['score' => 0, 'percent' => 0, 'code' => null]);
        $this->assertSame(60, $this->playFor(999, 'viel@example.org')['score']);
    }

    public function test_code_needs_same_email_phone_and_only_one_per_household(): void
    {
        $code = $this->playFor(5, 'erika@example.org')['code'];
        $order = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'Erika Muster', 'consent' => '1', 'source' => '/klappmesser/'];

        $this->post('/anfrage/', $order + ['email' => 'andere@example.org', 'phone' => '0171 1234567', 'discount_code' => $code])->assertSessionHasErrors('discount_code');
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'discount_code' => $code])->assertSessionHasErrors('phone');
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'phone' => '0171 1234567', 'discount_code' => strtolower($code)])->assertSessionHasNoErrors();
        $this->assertSame(['5', '664.05'], [(string) Order::sole()->discount_percent, Order::sole()->amount]);
        $this->post('/anfrage/', $order + ['email' => 'erika@example.org', 'phone' => '0171 1234567', 'discount_code' => $code])->assertSessionHasErrors('discount_code');

        // zweite E-Mail derselben Person (gleiche Telefonnummer) → kein zweiter Rabatt
        $this->travelBack();
        RateLimiterReset::clear();
        $code2 = $this->playFor(5, 'erika.zweit@example.org')['code'];
        $this->post('/anfrage/', $order + ['email' => 'erika.zweit@example.org', 'phone' => '+49 171 1234567', 'discount_code' => $code2])
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
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'spaet@example.org', 'conditions' => '1'])->assertStatus(410);
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
