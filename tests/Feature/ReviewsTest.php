<?php

namespace Tests\Feature;

use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviews_from_admin_appear_on_home_and_erfahrungen_without_review_schema(): void
    {
        $this->seed(CourseSeeder::class);
        Review::create(['author' => 'Jonas K.', 'rating' => 5, 'text' => 'Toller Kurs, sehr geduldig erklärt.', 'reviewed_on' => '2026-06-01', 'topic' => 'Damastkurs']);
        Review::create(['author' => 'Versteckt X.', 'rating' => 4, 'text' => 'Nicht freigegeben', 'reviewed_on' => '2026-05-01', 'published' => false]);

        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Toller Kurs, sehr geduldig erklärt.', $home);
        $this->assertStringContainsString('Jonas K. · Damastkurs · Juni 2026 · Google', $home);
        $this->assertStringNotContainsString('Nicht freigegeben', $home);
        $this->assertStringNotContainsString('"AggregateRating"', $home);
        $this->assertStringNotContainsString('"Review"', $home);

        $page = $this->get('/schmiedekurse/erfahrungen/')->assertOk()->getContent();
        $this->assertStringContainsString('Toller Kurs, sehr geduldig erklärt.', $page);
        $this->assertStringContainsString('aria-label="5 von 5 Sternen"', $page);
    }

    public function test_google_total_rating_and_profile_link_from_settings(): void
    {
        $this->seed(CourseSeeder::class);
        Setting::write('google_rating', ['value' => 4.9, 'count' => 23, 'as_of' => '11.11.2026', 'profile_url' => 'https://maps.app.goo.gl/abc123']);

        $home = $this->get('/')->getContent();
        $this->assertStringContainsString('4,9 · 23 Bewertungen', $home);
        $this->assertStringContainsString('href="https://maps.app.goo.gl/abc123"', $home);
    }

    public function test_admin_can_manage_reviews_and_total(): void
    {
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageReviews::class)
            ->callAction('gesamt', ['value' => 5, 'count' => 12, 'profile_url' => 'https://maps.app.goo.gl/xyz'])
            ->assertHasNoActionErrors();
        $this->assertSame(12, Setting::read('google_rating')['count']);

        Livewire::test(ManageReviews::class)
            ->callAction('gesamt', ['value' => 5, 'count' => 12, 'profile_url' => 'https://evil.example/x'])
            ->assertHasActionErrors(['profile_url']);

        Livewire::test(ManageReviews::class)
            ->callAction('create', ['author' => 'Lea M.', 'rating' => 5, 'text' => 'Super!', 'reviewed_on' => '2026-10-01', 'source' => 'Google', 'published' => true])
            ->assertHasNoActionErrors();
        $this->assertDatabaseHas('reviews', ['author' => 'Lea M.']);
    }
}
