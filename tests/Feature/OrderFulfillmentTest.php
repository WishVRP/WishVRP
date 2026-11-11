<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Mail\CustomerUpdateMail;
use App\Models\Course;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Support\OrderAutomation;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class OrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ulbrich_admin.mfa_required' => false]);
        Mail::fake();
        Setting::write('google_place_id', 'ChIJplace');
    }

    private function knife(array $extra = []): Order
    {
        return Order::create($extra + ['type' => 'messer', 'status' => 'bezahlt', 'name' => 'Max Mustermann', 'email' => 'max@example.org', 'item' => 'Damast-Kochmesser', 'shipping_method' => 'versand', 'review_consent' => true]);
    }

    public function test_full_knife_flow_in_progress_shipped_delivered_sends_three_mails(): void
    {
        $this->actingAs(User::factory()->create());
        $order = $this->knife();

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('in_arbeit', ['production_time' => 'ca. 4 Wochen', 'personal' => 'Danke für deine Geduld!', 'notify' => true])
            ->assertHasNoActionErrors();
        $order->refresh();
        $this->assertSame('in_arbeit', $order->status);
        $this->assertNotNull($order->in_progress_at);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'in_arbeit' && $m->hasTo('max@example.org') && $m->personal === 'Danke für deine Geduld!');

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('versendet', ['method' => 'versand', 'tracking_carrier' => 'dhl', 'tracking_number' => '0034 0434 1610', 'notify' => true])
            ->assertHasNoActionErrors();
        $order->refresh();
        $this->assertSame('versendet', $order->status);
        $this->assertSame('003404341610', $order->tracking_number);
        $this->assertSame('https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?piececode=003404341610', $order->trackingLink());
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'versendet');

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('zugestellt', ['notify' => true])->assertHasNoActionErrors();
        $order->refresh();
        $this->assertSame('zugestellt', $order->status);
        $this->assertNotNull($order->delivered_at);
        $this->assertNotNull($order->review_requested_at);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'zugestellt');

        $this->assertSame(3, $order->notes()->count(), 'Jede E-Mail steht als Notiz am Vorgang');
    }

    public function test_mails_render_with_tracking_and_google_review_link(): void
    {
        $order = $this->knife(['status' => 'versendet', 'tracking_carrier' => 'dpd', 'tracking_number' => '01234567890123', 'production_time' => 'ca. 2 Wochen']);

        $shipped = (new CustomerUpdateMail($order, 'versendet'))->render();
        $this->assertStringContainsString('Sendung verfolgen', $shipped);
        $this->assertStringContainsString('https://tracking.dpd.de/status/de_DE/parcel/01234567890123', $shipped);
        $this->assertStringContainsString('Hallo Max,', $shipped);

        $delivered = (new CustomerUpdateMail($order, 'zugestellt'))->render();
        $this->assertStringContainsString('https://search.google.com/local/writereview?placeid=ChIJplace', $delivered);
        $this->assertStringContainsString('Jetzt bei Google bewerten', $delivered);

        Setting::write('google_review_url', 'https://g.page/r/abc/review');
        $this->assertStringContainsString('https://g.page/r/abc/review', (new CustomerUpdateMail($order, 'zugestellt'))->render());

        $progress = (new CustomerUpdateMail($order, 'in_arbeit', '<b>xss</b>'))->render();
        $this->assertStringContainsString('ca. 2 Wochen', $progress);
        $this->assertStringNotContainsString('<b>xss</b>', $progress);
    }

    public function test_pickup_sends_ready_for_pickup_mail_without_tracking(): void
    {
        $this->actingAs(User::factory()->create());
        $order = $this->knife(['status' => 'in_arbeit', 'shipping_method' => 'abholung']);

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('versendet', ['method' => 'abholung', 'notify' => true])->assertHasNoActionErrors();
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'abholbereit');
        $this->assertNull($order->refresh()->tracking_number);
    }

    public function test_notify_off_changes_status_without_mail(): void
    {
        $this->actingAs(User::factory()->create());
        $order = $this->knife();
        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->callAction('in_arbeit', ['production_time' => '1 Woche', 'notify' => false])->assertHasNoActionErrors();
        $this->assertSame('in_arbeit', $order->refresh()->status);
        Mail::assertNothingSent();
    }

    public function test_course_review_mail_goes_out_on_the_evening_of_the_last_course_day_only_once(): void
    {
        $this->seed(CourseSeeder::class);
        $course = Course::query()->first();
        $today = Carbon::parse('2026-11-14', 'Europe/Berlin');
        $done = Order::create(['type' => 'kurs', 'status' => 'bestaetigt', 'name' => 'Lea M', 'email' => 'lea@example.org', 'course_id' => $course->id, 'course_start' => '2026-11-13', 'course_end' => '2026-11-14', 'participants' => 1, 'review_consent' => true]);
        $noConsent = Order::create(['type' => 'kurs', 'status' => 'bestaetigt', 'name' => 'Kai', 'email' => 'kai@example.org', 'course_id' => $course->id, 'course_start' => '2026-11-14', 'participants' => 1]);
        $future = Order::create(['type' => 'kurs', 'status' => 'bezahlt', 'name' => 'Tom', 'email' => 'tom@example.org', 'course_id' => $course->id, 'course_start' => '2026-11-20', 'participants' => 1]);
        $cancelled = Order::create(['type' => 'kurs', 'status' => 'storniert', 'name' => 'Ina', 'email' => 'ina@example.org', 'course_id' => $course->id, 'course_start' => '2026-11-14', 'participants' => 1]);
        $unpaid = Order::create(['type' => 'kurs', 'status' => 'neu', 'name' => 'Ben', 'email' => 'ben@example.org', 'course_id' => $course->id, 'course_start' => '2026-11-14', 'participants' => 1]);

        $this->assertSame(1, OrderAutomation::courseReviews($today));
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'kurs' && $m->hasTo('lea@example.org'));
        Mail::assertSent(CustomerUpdateMail::class, 1);
        $this->assertSame('abgeschlossen', $done->refresh()->status);
        $this->assertSame('abgeschlossen', $noConsent->refresh()->status, 'ohne Einwilligung: abgeschlossen, aber keine Mail');
        Mail::assertNotSent(CustomerUpdateMail::class, fn ($m) => $m->hasTo('kai@example.org'));
        $this->assertStringContainsString($course->name, (new CustomerUpdateMail($done, 'kurs'))->render());

        $this->assertSame(0, OrderAutomation::courseReviews($today), 'nicht doppelt');
        $this->assertSame(0, OrderAutomation::courseReviews(Carbon::parse('2026-11-25')), 'zu alt für Nachholen');
    }

    public function test_dhl_delivery_is_detected_automatically(): void
    {
        config(['services.dhl.key' => 'dhl-test']);
        Http::fake(['api-eu.dhl.com/*' => Http::sequence()
            ->push(['shipments' => [['status' => ['statusCode' => 'transit']]]])
            ->push(['shipments' => [['status' => ['statusCode' => 'delivered', 'timestamp' => '2026-11-12T10:15:00+01:00']]]])]);
        $order = $this->knife(['status' => 'versendet', 'tracking_carrier' => 'dhl', 'tracking_number' => '00340434', 'shipped_at' => now()]);

        $this->assertSame(0, OrderAutomation::trackDeliveries());
        $this->assertSame('versendet', $order->refresh()->status);
        $this->assertSame(0, OrderAutomation::trackDeliveries(), 'erst nach Pause erneut prüfen');

        $this->travel(2)->hours();
        $this->assertSame(1, OrderAutomation::trackDeliveries());
        $this->assertSame('zugestellt', $order->refresh()->status);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'zugestellt');
        Http::assertSent(fn ($r) => $r->hasHeader('DHL-API-Key', 'dhl-test'));
    }

    public function test_mail_preview_requires_login_and_renders(): void
    {
        $order = $this->knife();
        $this->get('/verwaltung/mail-vorschau/'.$order->id.'/versendet')->assertForbidden();
        $this->actingAs(User::factory()->create());
        $this->get('/verwaltung/mail-vorschau/'.$order->id.'/versendet')->assertOk()->assertSee('Sendung verfolgen');
        Mail::assertNothingSent();
    }

    public function test_without_consent_delivered_mail_has_no_review_request(): void
    {
        $order = $this->knife(['review_consent' => false, 'status' => 'versendet']);
        $html = (new CustomerUpdateMail($order, 'zugestellt'))->render();
        $this->assertStringContainsString('Dein Paket ist angekommen', $html);
        $this->assertStringNotContainsString('writereview', $html);
        $this->assertStringNotContainsString('Jetzt bei Google bewerten', $html);
    }

    public function test_consent_checkbox_on_website_form_is_stored(): void
    {
        $this->seed(CourseSeeder::class);
        $html = $this->get('/schmiedekurse/buchen/')->getContent();
        $this->assertStringContainsString('name="review_consent"', $html);
        $this->assertDoesNotMatchRegularExpression('/name="review_consent"[^>]*checked/', $html, 'nie vorausgewählt');

        $this->app['request']->merge(['review_consent' => '1']);
        $this->assertTrue(Order::create(['type' => 'anfrage', 'name' => 'A', 'email' => 'a@example.org'])->review_consent);
        $this->app['request']->replace([]);
        $this->assertFalse(Order::create(['type' => 'anfrage', 'name' => 'B', 'email' => 'b@example.org'])->refresh()->review_consent);
    }

    public function test_order_confirmation_on_receipt_and_payment_confirmation(): void
    {
        $order = $this->knife(['status' => 'neu', 'amount' => 289.00, 'shipping_amount' => 7.99, 'street' => 'Musterweg 1', 'postal_code' => '12345', 'city' => 'Musterstadt', 'payment_method' => 'ueberweisung']);

        \App\Support\OrderMail::received($order);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'eingang' && $m->hasTo('max@example.org'));

        config(['ulbrich.bank' => ['holder' => 'Andreas Ulbrich', 'iban' => 'DE02120300000000202051', 'bic' => 'BYLADEM1001']]);
        $html = preg_replace('/\s+/', ' ', (new CustomerUpdateMail($order, 'eingang'))->render());
        $this->assertStringContainsString('Danke für deine Bestellung', $html);
        $this->assertStringContainsString('296,99 €', $html);                // 289,00 + 7,99 Versand
        $this->assertStringContainsString('DE02 1203 0000 0000 2020 51', $html);
        $this->assertStringContainsString('Verwendungszweck', $html);
        $this->assertStringContainsString('Musterweg 1', $html);
        $this->assertStringContainsString('/widerruf/', $html);

        \App\Support\OrderPaid::paid($order, 'ueberweisung');
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'bezahlt');
        $paid = (new CustomerUpdateMail($order->refresh(), 'bezahlt'))->render();
        $this->assertStringContainsString('Zahlung erhalten', $paid);
        $this->assertStringNotContainsString('Zahlung per Überweisung', $paid);
    }

    public function test_course_booking_confirmation_shows_date_and_place_and_no_bank_without_config(): void
    {
        $this->seed(CourseSeeder::class);
        $course = Course::query()->first();
        $order = Order::create(['type' => 'kurs', 'status' => 'neu', 'name' => 'Lea M', 'email' => 'lea@example.org', 'course_id' => $course->id, 'course_start' => '2026-12-05', 'participants' => 2, 'amount' => 600, 'payment_method' => 'ueberweisung']);
        $html = preg_replace('/\s+/', ' ', (new CustomerUpdateMail($order, 'eingang'))->render());
        $this->assertStringContainsString('Danke für deine Buchung', $html);
        $this->assertStringContainsString('Samstag, 5. Dezember 2026', $html);
        $this->assertStringContainsString('2 Personen', $html);
        $this->assertStringContainsString('Wannenstraße 33', $html);
        $this->assertStringContainsString('Die Zahlungsdaten schicken wir dir in Kürze', $html);
        $this->assertStringContainsString('/kursbedingungen/', $html);
    }
}
