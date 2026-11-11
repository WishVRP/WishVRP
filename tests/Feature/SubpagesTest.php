<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubpagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_every_inventory_page_is_implemented(): void
    {
        foreach (config('pages') as $path => $page) {
            $this->assertSame('live', $page['status'], "Seite noch nicht umgesetzt: {$path}");
        }
    }

    public function test_every_subpage_renders_with_one_h1_meta_and_canonical(): void
    {
        foreach (config('pages') as $path => $page) {
            if ($path === '/') {
                continue;
            }
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<h1'), "Genau eine H1 auf {$path}");
            $this->assertStringContainsString('<link rel="canonical" href="https://messermanufaktur-ulbrich.de'.$path.'">', $html, "Canonical {$path}");
            $len = mb_strlen($page['description']);
            $this->assertTrue($len >= 140 && $len <= 160, "Description {$path}: {$len} Zeichen");
            $this->assertStringContainsString('"BreadcrumbList"', $html, "Breadcrumb-Schema {$path}");
            $this->assertStringNotContainsString('[FEHLT', $html);
            $this->assertStringNotContainsString('Bildplatz', $html, "Kein sichtbarer Bildplatz auf {$path}");
            if (($page['index'] ?? true) === false) {
                $this->assertStringContainsString('noindex', $html, "{$path} muss noindex sein");
            }
        }
    }

    public function test_internal_links_on_all_pages_point_to_known_urls(): void
    {
        $known = array_keys(config('pages'));
        foreach (array_keys(config('pages')) as $path) {
            $html = $this->get($path)->getContent();
            preg_match_all('/href="(\/[^"#?]*)/', $html, $m);
            foreach (array_unique($m[1]) as $href) {
                if (str_starts_with($href, '/build/') || str_starts_with($href, '/images/') || preg_match('/\.(ico|svg|png|webmanifest)$/', $href)) {
                    continue;
                }
                $this->assertContains($href, $known, "Toter interner Link {$href} auf {$path}");
            }
        }
    }

    public function test_booking_preselects_course_and_participants_from_query(): void
    {
        $html = $this->get('/schmiedekurse/buchen/?kurs=damast-messerkurs&teilnehmer=3')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/value="damast-messerkurs"[^>]*checked/s', $html);
        $this->assertStringContainsString('value="3"', $html);
        $this->assertStringContainsString('Buchung anfragen', $html);
        $this->assertStringNotContainsString('Weiter zur Zahlung', $html, 'Keine Online-Zahlung ohne Freigabe');
    }

    public function test_edition_page_has_product_schema_with_real_price_only(): void
    {
        $html = $this->get('/klappmesser/')->getContent();
        $this->assertStringContainsString('"@type": "Product"', $html);
        $this->assertStringContainsString('"price": "699"', $html);
        $this->assertStringNotContainsString('availability', $html);
        $this->assertStringNotContainsString('AggregateRating', $html);
    }

    public function test_course_overview_lists_every_course_with_anchor(): void
    {
        $html = $this->get('/schmiedekurse/')->getContent();
        foreach (['grundlagenkurs', 'klingenkurs', 'damast-messerkurs', 'damast-premium-kurs', 'manager-schmiedekurs'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html);
        }
        $this->assertStringContainsString('"@type": "Course"', $html);
    }
}
