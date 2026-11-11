<?php

namespace Tests\Feature;

use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeritageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_tells_the_family_tradition_from_config(): void
    {
        $this->seed(CourseSeeder::class);
        $html = $this->get('/')->assertOk()->getContent();
        $since = config('ulbrich.heritage.since');

        $this->assertStringContainsString("seit {$since}", $html);
        $this->assertStringContainsString(config('ulbrich.heritage.generation').'. Generation', $html);
        $this->assertStringContainsString('id="tradition"', $html);
        $this->assertStringContainsString('data-story', $html);
        $this->assertStringContainsString('Seit wann gibt es das Schmiedehandwerk', $html, 'FAQ zur Tradition');
        $this->assertStringNotContainsString('"foundingDate": "'.$since, $html, '1934 ist Familientradition, kein Firmengründungsdatum');
        $this->assertStringContainsString('"foundingDate": "'.config('ulbrich.heritage.opened_on').'"', $html, 'Gründung = Eröffnung der Manufaktur');
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="/schmiedekurse/buchen/"'), 'Mehrere Wege zur Buchung');
    }

    public function test_llms_txt_lists_facts_and_courses(): void
    {
        $this->seed(CourseSeeder::class);
        $txt = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

        $this->assertStringContainsString('# Messermanufaktur Ulbrich', $txt);
        $this->assertStringContainsString('seit 1934', $txt);
        $this->assertStringContainsString('Grundlagenkurs: 1 Tag, ab 300 € pro Person', $txt);
        $this->assertStringNotContainsString('/impressum/', $txt);
    }
}
