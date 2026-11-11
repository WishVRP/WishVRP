<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_home_renders_with_exactly_one_h1(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
    }

    public function test_seo_meta_is_complete(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="https://messermanufaktur-ulbrich.de/">', $html);
        $this->assertMatchesRegularExpression("/<title>[^<]+Ulbrich<\\/title>/", $html);

        preg_match('/<meta name="description" content="([^"]+)"/', $html, $m);
        $length = mb_strlen(html_entity_decode($m[1]));
        $this->assertGreaterThanOrEqual(140, $length, "Description zu kurz: {$length}");
        $this->assertLessThanOrEqual(160, $length, "Description zu lang: {$length}");

        foreach (['og:title', 'og:description', 'og:image', 'og:url', 'og:type', 'og:site_name'] as $og) {
            $this->assertStringContainsString("property=\"{$og}\"", $html);
        }
        $this->assertStringContainsString('name="twitter:card" content="summary_large_image"', $html);
    }

    public function test_non_production_is_never_indexable(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $response->getContent());
    }

    public function test_structured_data_is_valid_and_contains_no_ratings_or_placeholders(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);

        $data = json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
        $types = array_column($data['@graph'], '@type');

        $this->assertContains('LocalBusiness', $types);
        $this->assertContains('FAQPage', $types);
        $this->assertStringNotContainsString('AggregateRating', $m[1]);
        $this->assertStringNotContainsString('Review', $m[1]);
        $this->assertStringNotContainsString('FEHLT', $m[1]);

        $business = collect($data['@graph'])->firstWhere('@type', 'LocalBusiness');
        $this->assertSame('Wannenstraße 33', $business['address']['streetAddress']);
        $this->assertSame('+491794079218', $business['telephone']);
        $this->assertSame(config('ulbrich.email'), $business['email'] ?? null, 'E-Mail im Schema = E-Mail aus der Konfiguration');
        $this->assertSame(config('ulbrich.same_as'), $business['sameAs'] ?? null);
    }

    public function test_faq_schema_matches_visible_questions(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $m);
        $faq = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'FAQPage');

        foreach ($faq['mainEntity'] as $q) {
            $this->assertStringContainsString('<summary>'.e($q['name']).'</summary>', $html);
        }
    }

    public function test_prices_come_from_database_not_template(): void
    {
        $this->get('/')
            ->assertSee('ab 300 €')
            ->assertSee('ab 1.300 €')
            ->assertSee('auf Anfrage');
    }

    public function test_public_html_contains_no_admin_markup(): void
    {
        $html = strtolower($this->get('/')->getContent());

        foreach (['type="password"', '/verwaltung', 'livewire', 'login'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "Öffentliches HTML enthält „{$needle}“");
        }
        // „admin“ als eigenes Wort/Pfad – externe Quellen wie „…/fileadmin/…“ (TYPO3) sind kein Admin-Markup
        $this->assertDoesNotMatchRegularExpression('/(?<![a-z])admin/', $html, 'Öffentliches HTML enthält „admin“');
    }

    public function test_security_headers_are_set(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringNotContainsString("'unsafe-inline'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_no_inline_style_attributes_because_of_csp(): void
    {
        $this->assertDoesNotMatchRegularExpression('/\sstyle="/', $this->get('/')->getContent());
    }

    public function test_no_eu_odr_platform_link(): void
    {
        $this->assertStringNotContainsString('ec.europa.eu/consumers/odr', $this->get('/')->getContent());
    }
}
