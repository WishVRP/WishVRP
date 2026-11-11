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

    private function start(string $email = 'spieler@example.org'): array
    {
        return $this->postJson('/weihnachtsaktion/start/', ['email' => $email, 'conditions' => '1'])->assertOk()->json();
    }

    public function test_tiers_and_cap(): void
    {
        $this->assertSame([0, 0, 5, 5, 10, 10], array_map([XmasEntry::class, 'percentFor'], [0, 3, 4, 9, 10, 500]));
        config(['ulbrich.xmas.tiers' => [['min' => 1, 'percent' => 25]]]);
        $this->assertSame(10, XmasEntry::percentFor(5), 'nie mehr als 10 %');
    }

    public function test_full_round_gives_code_and_only_once_per_person(): void
    {
        $r = $this->start('Anna.Test+xmas@GoogleMail.com');
        $this->assertSame('ok', $r['status']);
        $this->travel(31)->seconds();
        $done = $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => 12])->assertOk()->json();
        $this->assertSame([12, 10], [$done['score'], $done['percent']]);
        $this->assertMatchesRegularExpression('/^XMAS-[A-Z0-9]{6}$/', $done['code']);

        // gleiche Person (andere Schreibweise) → keine zweite Teilnahme, gleicher Code
        $again = $this->start('annatest@gmail.com');
        $this->assertSame(['played', $done['code']], [$again['status'], $again['code']]);
        // Ergebnis kann nicht nachträglich verbessert werden
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => 80])->assertJson(['percent' => 10, 'score' => 12]);
        $this->assertSame(1, XmasEntry::count());
    }

    public function test_aborted_game_counts_as_participation(): void
    {
        $this->start('weg@example.org');
        $again = $this->start('weg@example.org');
        $this->assertSame(['played', 0, null], [$again['status'], $again['percent'], $again['code']]);
    }

    public function test_cheating_is_capped_or_rejected(): void
    {
        $fast = $this->start('schnell@example.org');
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $fast['token'], 'score' => 50])->assertJson(['score' => 0, 'percent' => 0, 'code' => null]);

        $big = $this->start('viel@example.org');
        $this->travel(31)->seconds();
        $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $big['token'], 'score' => 999])->assertJson(['score' => 60, 'percent' => 10]);
    }

    public function test_code_redeems_once_and_reduces_amount(): void
    {
        $r = $this->start();
        $this->travel(31)->seconds();
        $code = $this->postJson('/weihnachtsaktion/ergebnis/', ['token' => $r['token'], 'score' => 5])->json('code');

        $base = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'A', 'email' => 'a@example.org', 'consent' => '1', 'source' => '/klappmesser/'];
        $this->post('/anfrage/', $base + ['discount_code' => strtolower($code)])->assertSessionHasNoErrors();
        $order = Order::sole();
        $this->assertSame([$code, 5, '664.05'], [$order->discount_code, $order->discount_percent, $order->amount]);
        $this->assertNotNull(XmasEntry::sole()->redeemed_at);

        $this->post('/anfrage/', $base + ['discount_code' => $code])->assertSessionHasErrors('discount_code');
        $this->post('/anfrage/', $base + ['discount_code' => 'XMAS-FALSCH'])->assertSessionHasErrors('discount_code');
        $this->assertSame(1, Order::count());
    }

    public function test_page_conditions_teaser_and_campaign_end(): void
    {
        $html = $this->get('/weihnachtsaktion/')->assertOk()->getContent();
        $this->assertStringContainsString('data-xmas-form', $html);
        $this->assertStringContainsString('Jede Person kann nur einmal teilnehmen.', $html);
        $this->assertStringContainsString('höchstens 10 %', $html);
        $this->assertStringContainsString('Gültig bis 31. Dezember 2026', $html);
        $this->assertStringNotContainsString('data-xmas-practice', $html, 'keine Übungsrunde');
        $this->assertStringContainsString('Es gibt keine Probe- oder Übungsrunde.', $html);
        $this->assertStringContainsString('href="/weihnachtsaktion/"', $this->get('/')->getContent());
        $this->assertStringContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent());

        $this->post('/e/', ['n' => 'spiel', 'p' => '/weihnachtsaktion/'], ['User-Agent' => 'Mozilla/5.0 Chrome/130'])->assertNoContent();
        $this->assertSame(1, SiteEvent::where('name', 'spiel')->count());

        config(['ulbrich.xmas.ends_on' => now()->subDay()->toDateString(), 'ulbrich.xmas.valid_until' => now()->subDay()->toDateString()]);
        $this->assertStringContainsString('Die Weihnachtsaktion ist vorbei', $this->get('/weihnachtsaktion/')->getContent());
        $this->postJson('/weihnachtsaktion/start/', ['email' => 'spaet@example.org', 'conditions' => '1'])->assertStatus(410);
        $this->assertStringNotContainsString('name="discount_code"', $this->get('/klappmesser/')->getContent());
    }
}
