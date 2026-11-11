<?php

namespace Tests\Feature;

use App\Support\Inline;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_inline_links_only_for_known_internal_pages_and_text_is_escaped(): void
    {
        $this->assertSame('Im <a href="/schmiedekurse/grundlagenkurs/">Grundlagenkurs</a> &lt;b&gt;', Inline::html('Im [Grundlagenkurs](/schmiedekurse/grundlagenkurs/) <b>'));
        $this->assertSame('Seite gibt es nicht', Inline::html('[Seite gibt es nicht](/gibt-es-nicht/)'));
        $this->assertSame('Im Grundlagenkurs', Inline::plain('Im [Grundlagenkurs](/schmiedekurse/grundlagenkurs/)'));
    }

    public function test_no_raw_link_markup_is_visible_and_schema_text_is_plain(): void
    {
        $this->seed(CourseSeeder::class);
        foreach (config('pages') as $path => $page) {
            if ($page['status'] !== 'live' || ! isset($page['view'])) {
                continue;
            }
            $html = $this->get($path)->getContent();
            $this->assertDoesNotMatchRegularExpression('/\]\(\/[a-z0-9\/-]*\)/', $html, "Rohes Link-Markup auf {$path}");
        }
    }

    public function test_every_content_link_target_exists(): void
    {
        $pages = config('pages');
        foreach (glob(resource_path('content/{,extra/}*.json'), GLOB_BRACE) as $file) {
            preg_match_all('/\]\((\/[^)]*)\)/', file_get_contents($file), $m);
            foreach ($m[1] as $target) {
                $this->assertTrue(isset($pages[$target]), basename($file)." verlinkt unbekannte Seite {$target}");
            }
        }
    }
}
