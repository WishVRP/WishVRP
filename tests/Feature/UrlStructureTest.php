<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlStructureTest extends TestCase
{
    use RefreshDatabase;

    /** Jede Alt-URL aus der Redirect-Map: genau ein 301 direkt aufs Ziel. */
    public function test_every_exact_redirect_is_a_single_301_to_its_target(): void
    {
        foreach (config('redirects.exact') as $from => $to) {
            $response = $this->get($from);
            $response->assertStatus(301);
            $this->assertSame($to, $response->headers->get('Location'), "Falsches Ziel für {$from}");
            $this->assertNull(config('redirects.exact')[$to] ?? null, "Redirect-Kette: {$to} leitet erneut weiter");
        }
    }

    public function test_suffix_redirects_for_legacy_php_legal_pages(): void
    {
        $this->get('/shop/bedingungen.php')->assertStatus(301)->assertHeader('Location', '/agb/');
        $this->get('/kursbuchung/widerruf.php')->assertStatus(301)->assertHeader('Location', '/widerruf/');
        $this->get('/irgendwo/datenschutz.php')->assertStatus(301)->assertHeader('Location', '/datenschutz/');
    }

    public function test_missing_trailing_slash_redirects_once_and_keeps_query(): void
    {
        $this->get('/schmiedekurse')->assertStatus(301)->assertHeader('Location', '/schmiedekurse/');
        $this->get('/schmiedekurse/buchen?kurs=damast&teilnehmer=2')
            ->assertStatus(301)
            ->assertHeader('Location', '/schmiedekurse/buchen/?kurs=damast&teilnehmer=2');
    }

    public function test_legacy_redirect_with_slash_target_needs_no_second_hop(): void
    {
        $this->get('/shop.html')->assertHeader('Location', '/shop/');
        $this->get('/shop/')->assertOk();
    }

    public function test_all_inventory_urls_end_with_slash_and_are_lowercase_ascii(): void
    {
        foreach (array_keys(config('pages')) as $path) {
            $this->assertMatchesRegularExpression('#^/([a-z0-9-]+/)*$#', $path, "Ungültige URL im Inventar: {$path}");
        }
    }

    public function test_slash_url_is_served_directly(): void
    {
        $this->get('/')->assertOk();
        $this->get('/schmiedekurse/')->assertOk(); // Vorschau-Platzhalter, in Produktion nicht registriert
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_robots_allows_crawling_and_names_sitemap_when_indexable(): void
    {
        config(['seo.indexable' => true]);

        $body = $this->get('/robots.txt')->getContent();
        $this->assertStringContainsString('Allow: /', $body);
        $this->assertStringContainsString('Sitemap: https://messermanufaktur-ulbrich.de/sitemap.xml', $body);
        $this->assertStringNotContainsString('verwaltung', $body, 'Admin-Pfad darf nicht in robots.txt stehen');
    }

    public function test_sitemap_lists_only_live_indexable_pages(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>https://messermanufaktur-ulbrich.de/</loc>', $xml);
        $this->assertStringNotContainsString('/impressum/', $xml);
        $this->assertStringNotContainsString('/schmiedekurse/', $xml, 'Noch nicht umgesetzte Seiten gehören nicht in die Sitemap');
    }

    public function test_admin_requires_login_and_is_noindex(): void
    {
        $this->get('/verwaltung')->assertRedirect('/verwaltung/login');
        $this->get('/verwaltung/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
