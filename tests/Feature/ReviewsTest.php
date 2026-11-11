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
        $this->assertStringContainsString('Jonas K. · Damastkurs · Juni 2026 · Google', preg_replace('/\s+/', ' ', $home));
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

    private function fakeGoogle(int $status = 200): void
    {
        \Illuminate\Support\Facades\Http::fake(['places.googleapis.com/v1/places/*' => \Illuminate\Support\Facades\Http::response($status === 200 ? [
            'rating' => 4.9, 'userRatingCount' => 27, 'googleMapsUri' => 'https://maps.google.com/?cid=123',
            'reviews' => [
                ['rating' => 5, 'text' => ['text' => 'Neue Google-Bewertung: großartiger Kurs!'], 'publishTime' => '2026-11-02T10:00:00Z',
                    'authorAttribution' => ['displayName' => 'Max Mustermann', 'uri' => 'https://www.google.com/maps/contrib/1', 'photoUri' => 'https://lh3.googleusercontent.com/x'],
                    'googleMapsUri' => 'https://www.google.com/maps/reviews/abc'],
                ['rating' => 5, 'publishTime' => '2026-10-01T10:00:00Z', 'authorAttribution' => ['displayName' => 'Nur Sterne']],
                ['rating' => 4, 'text' => ['text' => '<script>alert(1)</script>'], 'publishTime' => '2026-09-01T10:00:00Z',
                    'authorAttribution' => ['displayName' => 'Böse', 'uri' => 'javascript:alert(1)']],
            ],
        ] : ['error' => ['message' => 'API key not valid']], $status)]);
    }

    public function test_reviews_come_directly_from_google_when_connected(): void
    {
        config(['services.google_places.key' => 'test-key', 'services.google_places.place_id' => 'ChIJtest']);
        $this->fakeGoogle();
        $this->seed(CourseSeeder::class);
        Review::create(['author' => 'Max Mustermann', 'rating' => 5, 'text' => 'Doppelt von Hand', 'reviewed_on' => '2026-06-01']);
        Review::create(['author' => 'Lea M.', 'rating' => 5, 'text' => 'Ergänzung von Hand', 'reviewed_on' => '2026-06-01']);

        $html = preg_replace('/\s+/', ' ', $this->get('/')->assertOk()->getContent());
        $this->assertStringContainsString('Neue Google-Bewertung: großartiger Kurs!', $html);
        $this->assertStringContainsString('4,9 · 27 Bewertungen', $html);
        $this->assertStringContainsString('href="https://maps.google.com/?cid=123"', $html);
        $this->assertStringContainsString('href="https://www.google.com/maps/contrib/1"', $html);
        $this->assertStringNotContainsString('Doppelt von Hand', $html);          // gleicher Name wie bei Google
        $this->assertStringContainsString('Ergänzung von Hand', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);  // escaped
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('googleusercontent', $html);        // keine Fotos von Google laden

        // zweiter Aufruf aus dem Zwischenspeicher – kein neuer Abruf
        $this->get('/');
        \Illuminate\Support\Facades\Http::assertSentCount(1);
        \Illuminate\Support\Facades\Http::assertSent(fn ($r) => $r->hasHeader('X-Goog-Api-Key', 'test-key') && str_contains($r->url(), 'places/ChIJtest'));
    }

    public function test_falls_back_to_admin_entries_when_google_fails(): void
    {
        config(['services.google_places.key' => 'bad', 'services.google_places.place_id' => 'ChIJtest']);
        $this->fakeGoogle(403);
        $this->seed(CourseSeeder::class);
        Review::create(['author' => 'Lea M.', 'rating' => 5, 'text' => 'Fallback-Text', 'reviewed_on' => '2026-06-01']);

        $this->get('/')->assertOk()->assertSee('Fallback-Text');
    }

    public function test_place_id_can_be_found_and_saved_in_admin(): void
    {
        config(['ulbrich_admin.mfa_required' => false, 'services.google_places.key' => 'test-key']);
        \Illuminate\Support\Facades\Http::fake([
            'places.googleapis.com/v1/places:searchText' => \Illuminate\Support\Facades\Http::response(['places' => [['id' => 'ChIJfound', 'displayName' => ['text' => 'Messermanufaktur Ulbrich'], 'formattedAddress' => 'Stein am Kocher']]]),
            'places.googleapis.com/v1/places/*' => \Illuminate\Support\Facades\Http::response(['rating' => 5, 'userRatingCount' => 10, 'reviews' => []]),
        ]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageReviews::class)
            ->callAction('verbinden', ['query' => 'Messermanufaktur Ulbrich', 'place_id' => 'ChIJfound'])
            ->assertHasNoActionErrors();
        $this->assertSame('ChIJfound', Setting::read('google_place_id'));
        $this->assertTrue(\App\Support\GoogleReviews::enabled());
    }
}
