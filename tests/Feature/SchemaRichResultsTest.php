<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseDate;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaRichResultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    private function graph(string $path)
    {
        preg_match('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $this->get($path)->assertOk()->getContent(), $m);

        return collect(json_decode($m[1], true, flags: JSON_THROW_ON_ERROR)['@graph']);
    }

    public function test_course_meets_google_course_info_requirements(): void
    {
        $course = $this->graph('/schmiedekurse/damast-messerkurs/')->firstWhere('@type', 'Course');

        $this->assertSame('Messermanufaktur Ulbrich', $course['provider']['name']);
        $this->assertSame('Paid', $course['offers']['category']);
        $this->assertSame('Onsite', $course['hasCourseInstance'][0]['courseMode']);
        $this->assertSame('P2D', $course['hasCourseInstance'][0]['courseWorkload']);
    }

    public function test_published_course_dates_become_events_and_instances(): void
    {
        $course = Course::where('slug', 'grundlagenkurs')->first();
        CourseDate::create(['course_id' => $course->id, 'starts_on' => now()->addDays(20), 'ends_on' => now()->addDays(20), 'seats_total' => 6, 'seats_booked' => 2, 'is_published' => true]);

        $graph = $this->graph('/schmiedekurse/grundlagenkurs/');
        $event = $graph->firstWhere('@type', 'EducationEvent');

        $this->assertSame(now()->addDays(20)->toDateString(), $event['startDate']);
        $this->assertSame('https://schema.org/InStock', $event['offers']['availability']);
        $this->assertSame('Wannenstraße 33', $event['location']['address']['streetAddress']);
        $this->assertSame(now()->addDays(20)->toDateString(), $graph->firstWhere('@type', 'Course')['hasCourseInstance'][0]['startDate']);
    }

    public function test_no_events_without_published_dates(): void
    {
        $this->assertNull($this->graph('/schmiedekurse/klingenkurs/')->firstWhere('@type', 'EducationEvent'));
    }

    public function test_articles_name_publisher_and_profile_pages_name_the_person(): void
    {
        $article = $this->graph('/ratgeber/messerformen/')->firstWhere('@type', 'Article');
        $this->assertSame('Messermanufaktur Ulbrich', $article['publisher']['name']);
        $this->assertArrayHasKey('logo', $article['publisher']);

        foreach (['/manufaktur/', '/en/about/'] as $path) {
            $page = $this->graph($path)->firstWhere('@type', 'ProfilePage');
            $this->assertNotNull($page, $path);
            $this->assertStringEndsWith('#andreas-ulbrich', $page['mainEntity']['@id']);
        }
    }

    public function test_product_has_condition_material_and_english_description_on_english_page(): void
    {
        $de = $this->graph('/klappmesser/')->firstWhere('@type', 'Product');
        $this->assertSame('https://schema.org/NewCondition', $de['offers']['itemCondition']);
        $this->assertSame('Damaststahl', $de['material']);

        $en = $this->graph('/en/folding-knife/')->firstWhere('@type', 'Product');
        $this->assertStringStartsWith('Damascus folding knife', $en['description']);
    }
}
