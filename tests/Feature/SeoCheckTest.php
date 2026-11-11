<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Schnellcheck je Seite: eindeutige Descriptions, Überschriften ohne Sprünge, Bilder mit alt und Maßen, Canonical, OG/Twitter. */
class SeoCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_indexable_page_passes_the_quick_seo_check(): void
    {
        $this->seed(CourseSeeder::class);
        $descriptions = [];
        foreach (['/' => []] + config('pages') as $path => $page) {
            if ($path !== '/' && ($page['status'] !== 'live' || ! isset($page['view']) || ($page['index'] ?? true) === false)) {
                continue;
            }
            $html = $this->get($path)->assertOk()->getContent();

            preg_match('/<meta name="description" content="([^"]*)"/', $html, $d);
            $this->assertNotContains($d[1], $descriptions, "Doppelte Description auf {$path}");
            $descriptions[] = $d[1];

            preg_match_all('/<h([1-6])[\s>]/', $html, $hs);
            $prev = 0;
            foreach ($hs[1] as $level) {
                $this->assertLessThanOrEqual($prev + 1, (int) $level, "Überschrift springt auf {$path} (h{$prev} → h{$level})");
                $prev = (int) $level;
            }

            preg_match_all('/<img\b[^>]*>/', $html, $imgs);
            foreach ($imgs[0] as $img) {
                $this->assertMatchesRegularExpression('/\balt="/', $img, "Bild ohne alt auf {$path}");
                $this->assertMatchesRegularExpression('/width="\d+" height="\d+"/', $img, "Bild ohne Maße auf {$path}");
            }

            $this->assertStringContainsString('<link rel="canonical" href="https://messermanufaktur-ulbrich.de'.$path.'">', $html);
            foreach (['og:title', 'og:description', 'og:image', 'og:url'] as $og) {
                $this->assertStringContainsString("property=\"{$og}\"", $html, "{$og} fehlt auf {$path}");
            }
            $this->assertStringContainsString('name="twitter:card"', $html);
        }
    }
}
