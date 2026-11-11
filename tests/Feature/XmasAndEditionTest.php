<?php

namespace Tests\Feature;

use App\Models\EditionPiece;
use App\Models\Order;
use App\Models\SiteEvent;
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

    public function test_edition_grid_shows_name_only_with_consent_and_never_claims_free(): void
    {
        EditionPiece::where('number', 17)->update(['status' => 'vergeben', 'buyer_name' => 'Erika M.', 'show_name' => true]);
        EditionPiece::where('number', 18)->update(['status' => 'vergeben', 'buyer_name' => 'Max K.', 'show_name' => false]);

        $html = $this->get('/klappmesser/')->assertOk()->getContent();
        $this->assertStringContainsString('data-tip="Nr. 17 · gehört Erika M."', $html);
        $this->assertStringContainsString('data-tip="Nr. 18 · vergeben"', $html);
        $this->assertStringNotContainsString('Max K.', $html, 'Name ohne Zustimmung darf nicht erscheinen');
        $this->assertStringContainsString('data-tip="Nr. 95 · Sonderstück"', $html);
        $this->assertStringNotContainsString('frei"', $html);
        $this->assertSame(100, substr_count($html, 'class="serial"'), '100 Felder im Raster');
    }

    public function test_home_uses_the_same_grid(): void
    {
        EditionPiece::where('number', 3)->update(['status' => 'reserviert']);
        $this->assertStringContainsString('data-tip="Nr. 3 · reserviert"', $this->get('/')->getContent());
    }

    public function test_public_name_is_only_stored_with_consent(): void
    {
        $base = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'A', 'email' => 'a@example.org', 'consent' => '1', 'source' => '/klappmesser/'];
        $this->post('/anfrage/', $base + ['public_name' => 'Anna B.']);
        $this->post('/anfrage/', $base + ['public_name' => 'Ben C.', 'show_name' => '1']);

        $this->assertSame([null, 'Ben C.'], Order::orderBy('id')->pluck('public_name')->all());
    }

    public function test_xmas_page_has_game_unset_surprise_is_marked_and_play_is_counted(): void
    {
        $html = $this->get('/weihnachtsaktion/')->assertOk()->getContent();
        $this->assertStringContainsString('data-xmas-game', $html);
        $this->assertStringContainsString('data-missing="Weihnachtsaktion', $html, 'Überraschung ist noch nicht festgelegt → markiert');
        $this->assertStringContainsString('noindex', $html);

        $this->assertStringContainsString('href="/weihnachtsaktion/"', $this->get('/')->getContent(), 'Hinweis auf der Startseite');
        $this->assertStringContainsString('href="/weihnachtsaktion/"', $this->get('/geschenke/weihnachten/')->getContent());

        $this->post('/e/', ['n' => 'spiel', 'p' => '/weihnachtsaktion/'], ['User-Agent' => 'Mozilla/5.0 Chrome/130'])->assertNoContent();
        $this->assertSame(1, SiteEvent::where('name', 'spiel')->count());
    }

    public function test_configured_surprise_is_shown_and_campaign_ends(): void
    {
        config(['ulbrich.xmas.surprise' => ['title' => 'Test-Überraschung', 'text' => 'Nur ein Test.', 'code' => 'TEST24']]);
        $html = $this->get('/weihnachtsaktion/')->getContent();
        $this->assertStringContainsString('Test-Überraschung', $html);
        $this->assertStringContainsString('TEST24', $html);

        config(['ulbrich.xmas.ends_on' => now()->subDay()->toDateString()]);
        $this->assertStringContainsString('Die Weihnachtsaktion ist vorbei', $this->get('/weihnachtsaktion/')->getContent());
        $this->assertStringNotContainsString('href="/weihnachtsaktion/"', $this->get('/')->getContent());
    }
}
