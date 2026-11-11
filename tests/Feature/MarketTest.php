<?php

namespace Tests\Feature;

use App\Filament\Resources\MarketApplications\Pages\ManageMarketApplications;
use App\Mail\MarketMail;
use App\Models\MarketApplication;
use App\Models\Setting;
use App\Models\User;
use App\Support\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MarketTest extends TestCase
{
    use RefreshDatabase;

    private function form(array $extra = []): array
    {
        return $extra + [
            'stand' => 3, 'name' => 'Holz Hanna', 'company' => 'Hannas Drechslerei', 'email' => 'hanna@example.org', 'phone' => '0170 123',
            'street' => 'Weg 1', 'postal_code' => '74196', 'city' => 'Neuenstadt', 'category' => 'Holzarbeiten',
            'description' => 'Ich drechsle Schalen und Brettchen aus heimischem Holz, alles in meiner Werkstatt von Hand.',
            'photos' => [UploadedFile::fake()->image('schale.jpg', 800, 600)], 'handmade' => '1', 'consent' => '1', 'rules' => '1', 'lighting' => '1', 'tables' => '2', 'food' => '1',
        ];
    }

    public function test_page_shows_stand_plan_with_18_stands(): void
    {
        $html = $this->get('/kunsthandwerkermarkt/')->assertOk()->getContent();
        $this->assertSame(18, substr_count($html, 'data-stand="'));
        $this->assertStringContainsString('Zeig uns dein Handwerk', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
    }

    public function test_reservation_holds_stand_for_one_visitor_only(): void
    {
        $this->postJson('/kunsthandwerkermarkt/reservieren/', ['stand' => 5])->assertOk()->assertJson(['ok' => true, 'minutes' => 15]);
        $this->assertSame('mein', $this->getJson('/kunsthandwerkermarkt/staende/')->json('stands.5'));

        // anderer Besucher (neue Sitzung)
        $this->flushSession();
        $this->assertSame('reserviert', $this->getJson('/kunsthandwerkermarkt/staende/')->json('stands.5'));
        $this->postJson('/kunsthandwerkermarkt/reservieren/', ['stand' => 5])->assertStatus(409);

        // nach 15 Minuten wieder frei
        $this->travel(16)->minutes();
        $this->postJson('/kunsthandwerkermarkt/reservieren/', ['stand' => 5])->assertOk();
    }

    public function test_application_with_photo_creates_code_mails_and_blocks_stand(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form())->assertRedirect('/kunsthandwerkermarkt/#bewerbung')->assertSessionHas('market_code');
        $this->get('/kunsthandwerkermarkt/')->assertSee('Bewerbung eingegangen')->assertSee(MarketApplication::query()->value('code'), false);

        $a = MarketApplication::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^KHM-[A-Z0-9]{6}$/', $a->code);
        $this->assertSame('100.00', $a->amount);            // Innenstand 80 + Werbung 10 + Licht 10
        Storage::disk('local')->assertExists($a->photos[0]);
        $this->assertStringStartsWith('markt/', $a->photos[0]);
        Mail::assertSent(MarketMail::class, fn ($m) => $m->kind === 'eingang' && $m->hasTo('hanna@example.org'));
        $this->assertSame('reserviert', Market::statusMap()[3]);

        // zweite Bewerbung auf denselben Stand scheitert
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form(['email' => 'zwei@example.org']))->assertSessionHasErrors('stand');
    }

    public function test_validation_requires_handmade_photo_and_real_category(): void
    {
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form(['handmade' => null, 'photos' => [], 'category' => 'Handyhüllen']))
            ->assertSessionHasErrors(['handmade', 'photos', 'category']);
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form(['photos' => [UploadedFile::fake()->create('virus.php', 10, 'application/x-php')]]))
            ->assertSessionHasErrors('photos.0');
    }

    public function test_closed_applications_and_new_season_resets_plan(): void
    {
        Setting::write('market', ['season' => '2027', 'date' => null, 'open' => false]);
        $this->postJson('/kunsthandwerkermarkt/reservieren/', ['stand' => 2])->assertStatus(423);
        $this->get('/kunsthandwerkermarkt/')->assertSee('Die Bewerbungsphase ist gerade geschlossen');

        MarketApplication::create(['code' => 'KHM-AAAAAA', 'season' => '2027', 'stand' => 2, 'status' => 'bezahlt', 'name' => 'A', 'email' => 'a@example.org', 'category' => 'Keramik', 'description' => 'x', 'amount' => 80]);
        $this->assertSame('belegt', Market::statusMap()[2]);
        Setting::write('market', ['season' => '2028', 'date' => '12.–13. September 2028', 'open' => true]);
        $this->assertSame('frei', Market::statusMap()[2]);
        $this->get('/kunsthandwerkermarkt/')->assertSee('12.–13. September 2028');
    }

    public function test_admin_accepts_and_marks_paid_with_mails(): void
    {
        Mail::fake();
        config(['ulbrich_admin.mfa_required' => false, 'ulbrich.bank' => ['holder' => 'Andreas Ulbrich', 'iban' => 'DE02120300000000202051']]);
        $this->actingAs(User::factory()->create());
        $a = MarketApplication::create(['code' => 'KHM-BBBBBB', 'season' => Market::season(), 'stand' => 12, 'status' => 'eingegangen', 'name' => 'Lea Leder', 'email' => 'lea@example.org', 'category' => 'Textil & Leder', 'description' => 'x', 'amount' => 80]);

        Livewire::test(ManageMarketApplications::class)->callTableAction('annehmen', $a, ['personal' => 'Schön, dass du dabei bist!'])->assertHasNoTableActionErrors();
        $this->assertSame('angenommen', $a->refresh()->status);
        Mail::assertSent(MarketMail::class, fn ($m) => $m->kind === 'angenommen' && $m->personal === 'Schön, dass du dabei bist!');
        $html = (new MarketMail($a, 'angenommen'))->render();
        $this->assertStringContainsString('DE02 1203 0000 0000 2020 51', $html);
        $this->assertStringContainsString('KHM-BBBBBB', $html);

        Livewire::test(ManageMarketApplications::class)->callTableAction('bezahlt', $a, [])->assertHasNoTableActionErrors();
        $this->assertSame('bezahlt', $a->refresh()->status);
        $this->assertNotNull($a->paid_at);
        $this->assertSame('belegt', Market::statusMap()[12]);
    }

    public function test_photos_only_for_logged_in_admin(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('markt/KHM-CCCCCC/1.jpg', 'bild');
        $a = MarketApplication::create(['code' => 'KHM-CCCCCC', 'season' => Market::season(), 'stand' => 1, 'name' => 'A', 'email' => 'a@example.org', 'category' => 'Keramik', 'description' => 'x', 'amount' => 90, 'photos' => ['markt/KHM-CCCCCC/1.jpg']]);
        $this->get('/verwaltung/markt-foto/'.$a->id.'/0')->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/verwaltung/markt-foto/'.$a->id.'/0')->assertOk();
        $this->assertFalse(is_file(public_path('storage/markt/KHM-CCCCCC/1.jpg')));
    }

    public function test_seo_faq_event_and_downloads(): void
    {
        $html = $this->get('/kunsthandwerkermarkt/')->getContent();
        $this->assertStringContainsString('<title>Kunsthandwerkermarkt bei Heilbronn: Stand mieten', $html);
        $this->assertStringContainsString('"@type": "FAQPage"', $html);
        $this->assertStringContainsString('Wann findet der Kunst- &amp; Handwerkermarkt statt?', $html);
        $this->assertStringNotContainsString('"@type": "Event"', $html);   // ohne festes Datum kein Event-Schema
        foreach (config('ulbrich.market.downloads') as $d) {
            $this->assertStringContainsString('href="'.$d['file'].'"', $html);
            $this->assertFileExists(public_path(ltrim($d['file'], '/')));
        }
        $this->assertStringContainsString('Wurst &amp; Getränke', $html);

        Setting::write('market', ['season' => Market::season(), 'date' => '12.–13. September 2027', 'open' => true, 'start' => '2027-09-11', 'end' => '2027-09-12']);
        $html = $this->get('/kunsthandwerkermarkt/')->getContent();
        $this->assertStringContainsString('"@type": "Event"', $html);
        $this->assertStringContainsString('"startDate": "2027-09-11"', $html);
        $this->assertStringContainsString('"endDate": "2027-09-12"', $html);
        $this->assertStringContainsString('am 12.–13. September 2027 statt', $html);
    }

    public function test_application_stores_tables_food_and_requires_market_rules(): void
    {
        Storage::fake('local');
        Mail::fake();
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form(['rules' => null]))->assertSessionHasErrors('rules');
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form())->assertSessionHasNoErrors();
        $a = MarketApplication::query()->firstOrFail();
        $this->assertSame(2, $a->tables);       // Innenstand 3
        $this->assertTrue($a->food);
        $this->post('/kunsthandwerkermarkt/bewerben/', $this->form(['stand' => 12, 'email' => 'aussen@example.org']))->assertSessionHasNoErrors();
        $this->assertNull(MarketApplication::query()->where('stand', 12)->value('tables'));   // Außenstand ohne Tische
    }

    public function test_acceptance_mail_attaches_contract_rules_and_plan(): void
    {
        $a = MarketApplication::create(['code' => 'KHM-DDDDDD', 'season' => Market::season(), 'stand' => 4, 'status' => 'angenommen', 'name' => 'A', 'email' => 'a@example.org', 'category' => 'Keramik', 'description' => 'x', 'amount' => 90, 'tables' => 1]);
        $mail = new MarketMail($a, 'angenommen');
        $names = collect($mail->attachments())->map(fn ($att) => $att->as)->all();
        $this->assertContains('kunsthandwerkermarkt-standmietvertrag.pdf', $names);
        $this->assertContains('kunsthandwerkermarkt-marktordnung.pdf', $names);
        $this->assertContains('kunsthandwerkermarkt-standplan.pdf', $names);
        $this->assertSame([], (new MarketMail($a, 'eingang'))->attachments());
        $this->assertStringContainsString('Standmietvertrag', $mail->render());
    }

    public function test_notify_signup_uses_double_opt_in(): void
    {
        Mail::fake();
        $this->post('/kunsthandwerkermarkt/termin/', ['email' => 'fan@example.org', 'newsletter_consent' => '1'])->assertRedirect('/kunsthandwerkermarkt/#termin');
        $sub = \App\Models\NewsletterSubscriber::query()->where('email', 'fan@example.org')->firstOrFail();
        $this->assertSame('markt', $sub->source);
        $this->assertNull($sub->confirmed_at);
        $this->post('/kunsthandwerkermarkt/termin/', ['email' => 'x@example.org'])->assertSessionHasErrors('newsletter_consent');
    }
}
