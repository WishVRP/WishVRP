<?php

namespace Tests\Feature;

use App\Models\CalendarBlock;
use App\Models\Course;
use App\Models\Order;
use App\Models\Voucher;
use App\Support\CourseCalendar;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookingCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    private function book(string $course, string $day, array $extra = [])
    {
        return $this->post('/schmiedekurse/buchen/', $extra + ['kurs' => $course, 'teilnehmer' => 1, 'datum' => $day, 'bedingungen' => '1',
            'name' => 'Erika Muster', 'email' => 'erika@example.org', 'consent' => '1']);
    }

    private function stripeOn(): void
    {
        config(['services.stripe.secret' => 'sk_test_x', 'services.stripe.webhook_secret' => 'whsec_test']);
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_123'])]);
    }

    private function webhook(string $type, string $session = 'cs_test_123', string $paid = 'paid')
    {
        $payload = json_encode(['type' => $type, 'data' => ['object' => ['id' => $session, 'payment_status' => $paid]]]);
        $t = time();
        $sig = hash_hmac('sha256', $t.'.'.$payload, 'whsec_test');

        return $this->call('POST', '/stripe/webhook/', [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$t},v1={$sig}", 'CONTENT_TYPE' => 'application/json'], $payload);
    }

    public function test_every_day_is_bookable_within_five_years_but_not_tomorrow(): void
    {
        $c = Course::where('slug', 'grundlagenkurs')->first();
        $this->assertTrue(CourseCalendar::available($c, now()->addDays(2)));
        $this->assertFalse(CourseCalendar::available($c, now()->addDay()), 'frühestens übermorgen');
        $this->assertTrue(CourseCalendar::available($c, now()->addYears(5)->subDay()));
        $this->assertFalse(CourseCalendar::available($c, now()->addYears(5)->addDay()));
        $this->assertFalse(CourseCalendar::bookable(Course::where('slug', 'manager-schmiedekurs')->first()), 'Manager-Kurs auf Anfrage');
    }

    public function test_one_course_per_day_and_multi_day_courses_block_all_days(): void
    {
        $day = now()->addDays(20);
        $this->book('damast-messerkurs', $day->toDateString())->assertSessionHasNoErrors();   // 2 Tage
        $this->assertSame($day->copy()->addDay()->toDateString(), Order::sole()->course_end->toDateString());

        $this->book('grundlagenkurs', $day->toDateString())->assertSessionHasErrors('datum');
        $this->book('grundlagenkurs', $day->copy()->addDay()->toDateString())->assertSessionHasErrors('datum');
        $this->book('damast-messerkurs', $day->copy()->subDay()->toDateString())->assertSessionHasErrors('datum');   // würde in Tag 1 hineinragen
        $this->book('grundlagenkurs', $day->copy()->addDays(2)->toDateString())->assertSessionHasNoErrors();

        $json = $this->getJson('/schmiedekurse/verfuegbarkeit/?kurs=grundlagenkurs&monat='.$day->format('Y-m'))->assertOk()->json('days');
        $this->assertSame('belegt', $json[$day->toDateString()]);
        $this->assertSame('belegt', $json[$day->copy()->addDay()->toDateString()]);
    }

    public function test_blocked_days_and_cancelled_bookings(): void
    {
        $day = now()->addDays(30);
        CalendarBlock::create(['date' => $day, 'reason' => 'Urlaub']);
        $this->book('grundlagenkurs', $day->toDateString())->assertSessionHasErrors('datum');

        $other = $day->copy()->addDays(3)->toDateString();
        $this->book('grundlagenkurs', $other)->assertSessionHasNoErrors();
        Order::sole()->update(['status' => 'storniert']);
        $this->book('grundlagenkurs', $other)->assertSessionHasNoErrors();   // nach Storno wieder frei
    }

    public function test_stripe_checkout_holds_day_and_webhook_confirms_or_releases(): void
    {
        config(['services.stripe.secret' => 'sk_test_x', 'services.stripe.webhook_secret' => 'whsec_test']);
        Http::fake(['api.stripe.com/*' => Http::sequence()
            ->push(['id' => 'cs_test_123', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_123'])
            ->push(['id' => 'cs_test_456', 'url' => 'https://checkout.stripe.com/x'])
            ->push(['id' => 'cs_test_789', 'url' => 'https://checkout.stripe.com/y'])]);
        $day = now()->addDays(15)->toDateString();
        $this->book('klingenkurs', $day, ['teilnehmer' => 2])->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');
        $order = Order::sole();
        $this->assertSame(['zahlung_offen', 'stripe', 'cs_test_123'], [$order->status, $order->payment_method, $order->stripe_session_id]);
        Http::assertSent(fn ($r) => $r->url() === 'https://api.stripe.com/v1/checkout/sessions' && $r['line_items[0][price_data][unit_amount]'] == 88000);
        $this->assertSame('belegt', CourseCalendar::month(Course::where('slug', 'klingenkurs')->first(), now()->addDays(15)->year, now()->addDays(15)->month)[$day]);

        $this->post('/stripe/webhook/', [], ['Stripe-Signature' => 't=1,v1=falsch'])->assertStatus(400);
        $this->webhook('checkout.session.completed')->assertOk();
        $this->assertSame('bestaetigt', $order->fresh()->status);
        $this->assertNull($order->fresh()->hold_until);

        // abgelaufene Zahlung gibt den Tag frei
        $day2 = now()->addDays(40)->toDateString();
        $this->book('grundlagenkurs', $day2);
        $this->webhook('checkout.session.expired', 'cs_test_456')->assertOk();
        $this->assertSame('storniert', Order::where('stripe_session_id', 'cs_test_456')->value('status'));
        $this->book('grundlagenkurs', $day2)->assertRedirect('https://checkout.stripe.com/y');   // Tag wieder buchbar
    }

    public function test_hold_expires_without_webhook(): void
    {
        $this->stripeOn();
        $day = now()->addDays(12)->toDateString();
        $this->book('grundlagenkurs', $day);
        $this->travel(31)->minutes();
        $this->assertTrue(CourseCalendar::available(Course::where('slug', 'grundlagenkurs')->first(), now()->addDays(12)->subMinutes(31)->startOfDay()));
    }

    public function test_voucher_purchase_and_full_redemption_in_calendar(): void
    {
        $this->post('/gutschein/kaufen/', ['kurs' => 'grundlagenkurs', 'personen' => 2, 'empfaenger' => 'Max', 'name' => 'Schenkerin', 'email' => 's@example.org', 'consent' => '1', 'agb' => '1'])
            ->assertSessionHasNoErrors();
        $purchase = Order::sole();
        $voucher = Voucher::sole();
        $this->assertSame(['gutschein', '600.00', 'offen'], [$purchase->type, $voucher->value, $voucher->status]);
        $this->assertNull(Voucher::usable($voucher->code), 'erst nach Zahlung gültig');

        \App\Support\OrderPaid::paid($purchase, 'ueberweisung');   // z. B. „Zahlung erhalten“ in der Verwaltung
        $this->assertSame('bezahlt', $voucher->fresh()->status);

        $day = now()->addDays(25)->toDateString();
        $this->book('grundlagenkurs', $day, ['teilnehmer' => 2, 'gutschein_code' => strtolower($voucher->code)])->assertRedirect('/zahlung/danke/?o='.Order::latest('id')->value('number'));
        $booking = Order::latest('id')->first();
        $this->assertSame(['bestaetigt', 'gutschein', '600.00'], [$booking->status, $booking->payment_method, $booking->voucher_amount]);
        $this->assertSame('eingeloest', $voucher->fresh()->status);

        $this->book('grundlagenkurs', now()->addDays(26)->toDateString(), ['gutschein_code' => $voucher->code])->assertSessionHasErrors('gutschein_code');
    }

    public function test_partial_voucher_goes_to_stripe_for_the_rest(): void
    {
        $v = Voucher::create(['code' => 'GS-TEST2345', 'course_id' => null, 'persons' => 1, 'value' => 300, 'status' => 'bezahlt', 'valid_until' => now()->addYear()]);
        $this->stripeOn();
        $this->book('klingenkurs', now()->addDays(18)->toDateString(), ['gutschein_code' => 'GS-TEST2345'])->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');
        Http::assertSent(fn ($r) => $r['line_items[0][price_data][unit_amount]'] == 14000);   // 440 − 300
        $this->webhook('checkout.session.completed');
        $this->assertSame('eingeloest', $v->fresh()->status);
    }

    public function test_booking_page_shows_calendar_and_payment_button_wording(): void
    {
        $html = $this->get('/schmiedekurse/buchen/')->assertOk()->getContent();
        $this->assertStringContainsString('data-cal-grid', $html);
        $this->assertStringContainsString('Buchung anfragen', $html, 'ohne Stripe: Anfrage');
        $this->assertStringNotContainsString('value="manager-schmiedekurs"', $html);
        $this->stripeOn();
        $this->assertStringContainsString('Zahlungspflichtig buchen', $this->get('/schmiedekurse/buchen/')->getContent(), '§ 312j BGB');
    }
}
