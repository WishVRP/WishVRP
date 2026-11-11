<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_newsletter_is_optional_and_unticked_in_the_game(): void
    {
        $html = $this->get('/weihnachtsaktion/')->getContent();
        $this->assertMatchesRegularExpression('/<input type="checkbox" name="newsletter" value="1">/', $html, 'nicht vorausgewählt, nicht Pflicht');

        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'ohne@example.org', 'conditions' => '1'])->assertOk();
        $this->assertSame(0, NewsletterSubscriber::count(), 'ohne Häkchen keine Newsletter-Adresse');
    }

    public function test_double_opt_in_proof_confirm_and_one_click_unsubscribe(): void
    {
        $this->postJson('/weihnachtsaktion/anmelden/', ['email' => 'mit@example.org', 'conditions' => '1', 'newsletter' => '1'])->assertOk();
        $sub = NewsletterSubscriber::sole();
        $this->assertSame(['weihnachtsaktion', NewsletterSubscriber::CONSENT], [$sub->source, $sub->consent_text]);
        $this->assertNotNull($sub->consented_at);
        $this->assertNull($sub->confirmed_at, 'erst nach Bestätigung aktiv');
        $this->assertSame(0, NewsletterSubscriber::query()->active()->count());

        // Öffnen des Links allein bestätigt nicht (Link-Scanner), erst der Knopf
        $this->get('/newsletter/bestaetigen/?t='.$sub->confirm_token)->assertOk()->assertSee('Ja, Newsletter bestätigen');
        $this->assertNull($sub->fresh()->confirmed_at);
        $this->followingRedirects()->post('/newsletter/bestaetigen/', ['t' => $sub->confirm_token])->assertSee('Deine Anmeldung ist bestätigt');
        $this->assertSame(1, NewsletterSubscriber::query()->active()->count());

        $this->get('/newsletter/abmelden/?t='.$sub->unsubscribe_token)->assertSee('Ja, abmelden');
        $this->followingRedirects()->post('/newsletter/abmelden/', ['t' => $sub->unsubscribe_token])->assertSee('Du bist abgemeldet');
        $this->assertSame(0, NewsletterSubscriber::query()->active()->count());
        $this->assertNotNull($sub->fresh()->unsubscribed_at);
    }

    public function test_invalid_tokens_change_nothing(): void
    {
        $this->post('/newsletter/bestaetigen/', ['t' => 'falsch'])->assertSessionHas('newsletter', 'invalid');
        $this->post('/newsletter/abmelden/', ['t' => 'falsch'])->assertSessionHas('newsletter', 'invalid');
    }
}
