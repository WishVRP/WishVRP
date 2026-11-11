<?php

namespace Tests\Feature;

use App\Mail\NewsletterMail;
use App\Models\Course;
use App\Models\CourseDate;
use App\Models\DiscountCode;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Support\NewsletterSender;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsletterAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        Mail::fake();
    }

    private function subscriber(string $email, bool $confirmed = true, bool $unsubscribed = false): NewsletterSubscriber
    {
        return NewsletterSubscriber::create([
            'email' => $email, 'email_key' => strtolower($email), 'source' => 'test', 'consent_text' => NewsletterSubscriber::CONSENT, 'consented_at' => now(),
            'confirm_token' => Str::random(48), 'unsubscribe_token' => Str::random(48),
            'confirmed_at' => $confirmed ? now() : null, 'unsubscribed_at' => $unsubscribed ? now() : null,
        ]);
    }

    public function test_weekly_issue_goes_only_to_confirmed_and_is_logged(): void
    {
        $this->subscriber('a@example.org');
        $this->subscriber('b@example.org');
        $this->subscriber('offen@example.org', confirmed: false);
        $this->subscriber('ab@example.org', unsubscribed: true);

        $this->artisan('newsletter:weekly')->assertSuccessful();
        $this->artisan('newsletter:send-due')->assertSuccessful();

        $issue = NewsletterIssue::sole();
        $this->assertSame(['auto', 'versendet', 2, 0], [$issue->kind, $issue->status, $issue->recipients_count, $issue->failed_count]);
        Mail::assertSent(NewsletterMail::class, 2);
        Mail::assertNotSent(NewsletterMail::class, fn ($m) => in_array($m->subscriber->email, ['offen@example.org', 'ab@example.org'], true));
        $this->assertSame(['a@example.org', 'b@example.org'], NewsletterDelivery::orderBy('email')->pluck('email')->all());

        // keine zweite Auto-Ausgabe in derselben Woche, kein Doppelversand
        $this->artisan('newsletter:weekly')->expectsOutput('Diese Woche gibt es schon eine Ausgabe.');
        $this->artisan('newsletter:send-due');
        Mail::assertSent(NewsletterMail::class, 2);
    }

    public function test_discount_code_every_three_months_personal_and_max_ten_percent(): void
    {
        $this->subscriber('a@example.org');
        $this->subscriber('b@example.org');

        $first = NewsletterSender::createWeekly();
        NewsletterSender::send($first);
        $this->assertSame(10, $first->discount_percent, 'erste Ausgabe mit Code (noch nie einer verschickt)');
        $codes = DiscountCode::all();
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0]->code, $codes[1]->code);
        $this->assertSame(['a@example.org', 'b@example.org'], $codes->pluck('email')->sort()->values()->all());

        $this->travel(7)->days();
        $this->assertNull(NewsletterSender::createWeekly()->discount_percent, 'eine Woche später ohne Code');

        $this->travel(84)->days();
        $this->assertSame(10, NewsletterSender::createWeekly()->discount_percent, 'nach 3 Monaten wieder mit Code');

        config(['ulbrich.newsletter.discount_percent' => 25]);
        $this->travel(91)->days();
        $this->assertSame(10, NewsletterSender::createWeekly()->discount_percent, 'nie mehr als 10 %');
    }

    public function test_mail_has_unsubscribe_link_header_imprint_and_real_data(): void
    {
        $course = Course::where('slug', 'grundlagenkurs')->first();
        CourseDate::create(['course_id' => $course->id, 'starts_on' => now()->addDays(12), 'seats_total' => 6, 'seats_booked' => 1, 'is_published' => true]);
        $sub = $this->subscriber('a@example.org');
        $issue = NewsletterSender::createWeekly();
        $code = DiscountCode::create(['code' => 'NEWS-TEST12', 'percent' => 10, 'email' => 'a@example.org', 'email_key' => 'a@example.org', 'source' => 'newsletter', 'valid_until' => now()->addDays(30)]);

        $mail = new NewsletterMail($issue, $sub, $code);
        $html = $mail->render();
        $this->assertStringContainsString($sub->unsubscribeUrl(), $html);
        $this->assertStringContainsString('Wannenstraße 33', $html);
        $this->assertStringContainsString('Impressum', $html);
        $this->assertStringContainsString('NEWS-TEST12', $html);
        $this->assertStringContainsString('Grundlagenkurs', $html, 'echter Kurstermin');
        $this->assertStringContainsString('5 Plätze frei', $html);
        $this->assertStringContainsString('71 Nummern frei', $html, 'echte Edition-Daten (90 − 19)');

        $headers = $mail->headers()->text;
        $this->assertStringContainsString($sub->oneClickUrl(), $headers['List-Unsubscribe']);
        $this->assertSame('List-Unsubscribe=One-Click', $headers['List-Unsubscribe-Post']);
    }

    public function test_one_click_unsubscribe_without_csrf(): void
    {
        $sub = $this->subscriber('a@example.org');
        $this->post('/newsletter/abmelden/sofort/?t='.$sub->unsubscribe_token, ['List-Unsubscribe' => 'One-Click'])->assertOk();
        $this->assertNotNull($sub->fresh()->unsubscribed_at);
    }

    public function test_newsletter_code_redeems_once_only_with_its_email_and_until_expiry(): void
    {
        $code = DiscountCode::create(['code' => 'NEWS-ABC234', 'percent' => 10, 'email' => 'kunde@example.org', 'email_key' => 'kunde@example.org', 'source' => 'newsletter', 'valid_until' => now()->addDays(30)]);
        $order = ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'name' => 'K', 'consent' => '1', 'source' => '/klappmesser/'];

        $this->post('/anfrage/', $order + ['email' => 'fremd@example.org', 'discount_code' => 'NEWS-ABC234'])->assertSessionHasErrors('discount_code');
        $this->post('/anfrage/', $order + ['email' => 'kunde@example.org', 'discount_code' => 'news-abc234'])->assertSessionHasNoErrors();
        $this->assertSame(['10', '629.10'], [(string) Order::sole()->discount_percent, Order::sole()->amount]);
        $this->assertSame(Order::sole()->id, $code->fresh()->order_id);
        $this->post('/anfrage/', $order + ['email' => 'kunde@example.org', 'discount_code' => 'NEWS-ABC234'])->assertSessionHasErrors('discount_code');

        DiscountCode::create(['code' => 'NEWS-OLD234', 'percent' => 10, 'email' => 'kunde@example.org', 'email_key' => 'kunde@example.org', 'source' => 'newsletter', 'valid_until' => now()->subDay()]);
        $this->post('/anfrage/', $order + ['email' => 'kunde@example.org', 'discount_code' => 'NEWS-OLD234'])->assertSessionHasErrors('discount_code');
    }

    public function test_manual_issue_markdown_and_auto_switch_off(): void
    {
        $sub = $this->subscriber('a@example.org');
        $issue = NewsletterIssue::create(['kind' => 'manuell', 'subject' => 'Adventsaktion', 'body' => "**Hallo!**\n\n<script>alert(1)</script>Neue Messer sind da.", 'status' => 'geplant', 'scheduled_at' => now()->subMinute()]);
        $html = (new NewsletterMail($issue, $sub))->render();
        $this->assertStringContainsString('<strong>Hallo!</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html, 'kein HTML aus dem Text');

        $this->artisan('newsletter:send-due');
        $this->assertSame('versendet', $issue->fresh()->status);

        config(['ulbrich.newsletter.auto_weekly' => false]);
        $this->artisan('newsletter:weekly')->expectsOutput('Automatischer Wochen-Newsletter ist ausgeschaltet.');
    }

    public function test_preview_requires_login(): void
    {
        $issue = NewsletterIssue::create(['subject' => 'X', 'status' => 'entwurf']);
        $this->get(route('newsletter.preview', $issue))->assertForbidden();
        $this->actingAs(\App\Models\User::factory()->create())->get(route('newsletter.preview', $issue))->assertOk()->assertSee('Vom Newsletter abmelden');
    }
}
