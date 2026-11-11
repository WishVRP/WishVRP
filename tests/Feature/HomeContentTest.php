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

    /** Keine verwaiste Seite: jede Bestands-URL ist von der Startseite aus in höchstens 3 Klicks erreichbar. */
    public function test_every_inventory_url_is_reachable_from_home_within_three_clicks(): void
    {
        $this->seed(CourseSeeder::class);
        // Seiten, die nur über Links in E-Mails erreicht werden (Newsletter bestätigen/abmelden), sind ausgenommen.
        $known = array_filter(config('pages'), fn (array $p) => empty($p['email_only']));
        $depth = ['/' => 0];
        $queue = ['/'];
        while ($queue) {
            $path = array_shift($queue);
            if ($depth[$path] >= 3) {
                continue;
            }
            preg_match_all('/href="(\/[^"#?]*)/', $this->get($path)->getContent(), $m);
            foreach (array_unique($m[1]) as $href) {
                if (isset($known[$href]) && ! isset($depth[$href])) {
                    $depth[$href] = $depth[$path] + 1;
                    $queue[] = $href;
                }
            }
        }
        foreach (array_keys($known) as $path) {
            $this->assertArrayHasKey($path, $depth, "Verwaist oder tiefer als 3 Klicks: {$path}");
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
