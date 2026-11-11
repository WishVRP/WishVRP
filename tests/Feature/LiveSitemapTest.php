<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Migrationsschutz: Jede URL der Live-Sitemap (abgerufen 11.11.2026, docs/live-sitemap-2026-11-11.txt)
 * ist auf der neuen Website erreichbar (200) oder per genau einem 301 auf eine erreichbare Seite umgeleitet.
 */
class LiveSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_live_url_is_kept_or_redirected_once(): void
    {
        $this->seed(CourseSeeder::class);
        $urls = array_filter(array_map('trim', file(base_path('docs/live-sitemap-2026-11-11.txt'))));
        $this->assertGreaterThanOrEqual(59, count($urls));

        foreach ($urls as $url) {
            $response = $this->get($url);
            if ($response->isRedirect()) {
                $this->assertSame(301, $response->getStatusCode(), "{$url} muss 301 sein");
                $target = $response->headers->get('Location');
                $this->get($target)->assertOk();
                continue;
            }
            $this->assertSame(200, $response->getStatusCode(), "Live-URL fehlt: {$url}");
        }
    }
}
