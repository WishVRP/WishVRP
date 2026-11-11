<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeContentTest extends TestCase
{
    use RefreshDatabase;

    private function html(): string
    {
        $this->seed(CourseSeeder::class);

        return $this->get('/')->assertOk()->getContent();
    }

    /** Jede inhaltliche Bestands-URL ist von der Startseite aus verlinkt – kein Bereich geht verloren. */
    public function test_every_inventory_url_is_linked_from_home(): void
    {
        $html = $this->html();

        foreach (config('pages') as $path => $page) {
            $this->assertStringContainsString('href="'.$path.'"', $html, "Startseite verlinkt {$path} nicht");
        }
    }

    /** Interne Links zeigen nur auf bekannte URLs (Inventar), Anker oder die Buchung mit Parameter. */
    public function test_internal_links_only_target_known_urls(): void
    {
        preg_match_all('/href="(\/[^"#]*)"/', $this->html(), $m);
        $known = array_keys(config('pages'));

        foreach (array_unique($m[1]) as $href) {
            $path = strtok($href, '?');
            if (str_starts_with($path, '/build/') || in_array($path, ['/favicon.ico', '/favicon.svg', '/favicon-16.png', '/favicon-32.png', '/apple-touch-icon.png', '/site.webmanifest'], true)) {
                continue;
            }
            $this->assertContains($path, $known, "Unbekanntes Linkziel: {$href}");
        }
    }

    public function test_all_fifteen_sections_are_present(): void
    {
        $html = $this->html();

        foreach (['hero-title', 'intro-title', 'experience-title', 'finder-title', 'dates-title', 'knives-title',
            'edition-title', 'custom-title', 'gifts-title', 'process-title', 'maker-title', 'teams-title',
            'voices-title', 'guides-title', 'faq-title'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, "Abschnitt {$id} fehlt");
        }
    }

    public function test_placeholders_are_neutral_not_error_messages(): void
    {
        $html = $this->html();

        $this->assertStringNotContainsString('[FEHLT', $html);
        $this->assertStringNotContainsString('[FOTO', $html);
        $this->assertStringContainsString('data-missing=', $html, 'Fehlende Angaben müssen für ulbrich:audit markiert bleiben');
    }

    public function test_course_finder_lists_all_courses_with_categories(): void
    {
        $html = $this->html();

        $this->assertSame(6, substr_count($html, 'data-category="'));
        $this->assertStringContainsString('data-finder-filters', $html);
    }

    public function test_no_fake_reviews_or_press_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->seed(CourseSeeder::class);

        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('skeleton-line', $html);
        $this->assertStringNotContainsString('TT.MM.JJJJ', $html);
    }
}
