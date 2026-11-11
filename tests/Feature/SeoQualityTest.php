<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoQualityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_indexable_titles_are_unique_and_50_to_60_chars(): void
    {
        $seen = [];
        foreach (config('pages') as $path => $page) {
            if (($page['index'] ?? true) === false) {
                continue;
            }
            preg_match('/<title>(.*?)<\/title>/', $this->get($path)->getContent(), $m);
            $title = html_entity_decode($m[1]);
            $len = mb_strlen($title);
            $this->assertTrue($len >= 50 && $len <= 60, "Title {$path}: {$len} Zeichen – {$title}");
            $this->assertNotContains($title, $seen, "Doppelter Title {$title}");
            $seen[] = $title;
        }
    }

    public function test_every_indexable_subpage_has_three_contextual_links(): void
    {
        foreach (config('pages') as $path => $page) {
            if ($path === '/' || ($page['index'] ?? true) === false) {
                continue;
            }
            $html = $this->get($path)->getContent();
            preg_match('/<main.*<\/main>/s', $html, $main);
            preg_match_all('/href="(\/[^"#?]*)/', $main[0], $m);
            $targets = array_unique(array_filter($m[1], fn ($l) => $l !== $path && isset(config('pages')[$l])));
            $this->assertGreaterThanOrEqual(3, count($targets), "Zu wenige interne Links im Inhalt von {$path}");
        }
    }

    public function test_page_specific_schema(): void
    {
        $this->assertStringContainsString('"@type": "Article"', $this->get('/ratgeber/')->getContent());
        $this->assertStringContainsString('"@type": "Service"', $this->get('/sonderanfertigungen/')->getContent());
        $voucher = $this->get('/geschenke/schmiedekurs-gutschein/')->getContent();
        $this->assertStringContainsString('"@type": "FAQPage"', $voucher);
        $this->assertStringContainsString('Reserviert der Gutschein schon einen Kurstermin?', $voucher, 'FAQ-Schema nur mit sichtbaren Fragen');
    }

    public function test_custom_404_is_helpful_and_noindex(): void
    {
        $html = $this->get('/gibt-es-nicht/')->assertNotFound()->getContent();
        $this->assertStringContainsString('noindex', $html);
        $this->assertStringContainsString('href="/schmiedekurse/"', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_ai_crawlers_allowed_and_entity_is_unambiguous(): void
    {
        config(['seo.indexable' => true]);
        $robots = $this->get('/robots.txt')->getContent();
        foreach (['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'] as $bot) {
            $this->assertStringContainsString("User-agent: {$bot}", $robots);
        }
        $this->assertStringNotContainsString('Disallow: /', $robots);

        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('"disambiguatingDescription"', $home);

        $profile = $this->get('/manufaktur/')->getContent();
        $this->assertStringContainsString('Die Messermanufaktur Ulbrich auf einen Blick', $profile);
        $this->assertStringContainsString('Schmiedehandwerk seit 1934', $profile);
    }
}
