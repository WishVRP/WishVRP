<?php

namespace Tests\Feature;

use App\Filament\Resources\Withdrawals\Pages\ListWithdrawals;
use App\Mail\CustomerUpdateMail;
use App\Mail\WithdrawalMail;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\CourseCalendar;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutRulesTest extends TestCase
{
    use RefreshDatabase;

    private function booking(array $extra = []): Order
    {
        $this->seed(CourseSeeder::class);
        $course = Course::query()->orderBy('sort')->first();   // eintägig
        $start = now()->addDays(30)->startOfDay();

        return Order::create($extra + ['type' => 'kurs', 'status' => 'bestaetigt', 'name' => 'Lea M', 'email' => 'lea@example.org', 'course_id' => $course->id,
            'course_start' => $start, 'course_end' => $start->copy()->addDays(CourseCalendar::days($course) - 1), 'participants' => 2, 'amount' => 600]);
    }

    // ---------- Zwei kostenlose Umbuchungen, Frist, Preisbindung ----------

    public function test_two_free_rebookings_keep_price_and_send_mail_then_stop(): void
    {
        Mail::fake();
        $o = $this->booking();
        $o->course->update(['price_from' => 999]);   // Preisänderung nach Buchung darf nicht greifen

        $d1 = now()->addDays(40)->toDateString();
        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start' => $d1])->assertSessionHasNoErrors()->assertSessionHas('rebooked');
        $o->refresh();
        $this->assertSame($d1, $o->course_start->toDateString());
        $this->assertSame(1, $o->rebook_count);
        $this->assertSame('600.00', $o->amount);   // Preisbindung
        $this->assertNotNull($o->original_course_start);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'umgebucht');

        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start_custom' => now()->addDays(50)->toDateString()])->assertSessionHasNoErrors();
        $this->assertSame(2, $o->refresh()->rebook_count);
        $this->assertSame(0, $o->freeRebookingsLeft());

        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start' => now()->addDays(60)->toDateString()])->assertSessionHasErrors('start');
        $this->get('/buchung/umbuchen/?t='.$o->manage_token)->assertSee('aufgebraucht');
    }

    public function test_rebooking_deadline_and_occupied_days(): void
    {
        $o = $this->booking();
        // anderer Kurs belegt Tag +45
        Order::create(['type' => 'kurs', 'status' => 'bezahlt', 'name' => 'X', 'email' => 'x@example.org', 'course_id' => $o->course_id,
            'course_start' => now()->addDays(45)->startOfDay(), 'course_end' => now()->addDays(45)->startOfDay(), 'participants' => 1, 'amount' => 300]);
        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start' => now()->addDays(45)->toDateString()])->assertSessionHasErrors('start');
        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start' => now()->addDay()->toDateString()])->assertSessionHasErrors('start');   // Buchungsfrist (frühestens übermorgen)

        $this->travelTo(Carbon::parse($o->course_start)->subHours(10));   // weniger als 48 Stunden vor Kursbeginn
        $this->assertNotNull($o->refresh()->rebookBlocker());
        $this->post('/buchung/umbuchen/', ['t' => $o->manage_token, 'start' => now()->addDays(20)->toDateString()])->assertSessionHasErrors('start');
    }

    public function test_rebooking_link_in_confirmation_and_invalid_token(): void
    {
        $o = $this->booking();
        $html = (new CustomerUpdateMail($o, 'bezahlt'))->render();
        $this->assertStringContainsString('/buchung/umbuchen/?t='.$o->manage_token, $html);
        $this->assertStringContainsString('Termin online umbuchen', $html);
        $this->get('/buchung/umbuchen/?t=ungueltig')->assertOk()->assertSee('Dieser Link ist ungültig');
    }

    public function test_booking_deadline_min_two_days_ahead(): void
    {
        $this->seed(CourseSeeder::class);
        $course = Course::query()->orderBy('sort')->first();
        $this->assertFalse(CourseCalendar::available($course, now()->addDay()));
        $this->assertTrue(CourseCalendar::available($course, now()->addDays(config('ulbrich.booking.min_days_ahead'))));
    }

    // ---------- Widerrufsfunktion ----------

    public function test_withdrawal_two_steps_receipt_mail_and_order_link_only_with_matching_email(): void
    {
        Mail::fake();
        $o = Order::create(['type' => 'messer', 'status' => 'bezahlt', 'name' => 'Max', 'email' => 'max@example.org', 'item' => 'Messer', 'amount' => 300]);
        $this->get('/')->assertSee('href="/widerrufen/"', false);   // im Footer jeder Seite
        $this->get('/widerrufen/')->assertOk()->assertSee('Vertrag widerrufen');

        $this->post('/widerrufen/pruefen/', ['name' => 'Max', 'email' => 'max@example.org', 'order_number' => strtolower($o->number)])
            ->assertRedirect('/widerrufen/#bestaetigen')->assertSessionHas('withdraw_review');
        $this->assertSame(0, Withdrawal::query()->count());   // erst nach Bestätigung

        $this->post('/widerrufen/', ['name' => 'Max', 'email' => 'max@example.org', 'order_number' => strtolower($o->number), 'confirm' => '1'])->assertSessionHas('withdraw_done');
        $w = Withdrawal::query()->firstOrFail();
        $this->assertSame($o->id, $w->order_id);
        Mail::assertSent(WithdrawalMail::class, fn ($m) => $m->kind === 'eingang' && $m->hasTo('max@example.org'));
        $this->assertStringContainsString('Eingegangen am', (new WithdrawalMail($w))->render());
        $this->assertTrue($o->notes()->where('body', 'like', 'WIDERRUF%')->exists());

        // fremde E-Mail: Erklärung wird angenommen, aber nicht mit dem Vorgang verknüpft
        $this->post('/widerrufen/', ['name' => 'Fremd', 'email' => 'fremd@example.org', 'order_number' => $o->number, 'confirm' => '1']);
        $this->assertNull(Withdrawal::query()->latest('id')->first()->order_id);
        // ohne Vorgangsnummer möglich, Grund nicht nötig
        $this->post('/widerrufen/', ['name' => 'Ohne', 'email' => 'ohne@example.org', 'confirm' => '1'])->assertSessionHasNoErrors();
        $this->assertStringContainsString('Betrifft: den gesamten Vertrag', (new WithdrawalMail(Withdrawal::query()->latest('id')->first()))->render());
        // Teilwiderruf: betroffene Positionen
        $this->post('/widerrufen/', ['name' => 'Teil', 'email' => 'teil@example.org', 'items' => 'Kochmesser 21 cm', 'confirm' => '1'])->assertSessionHasNoErrors();
        $w = Withdrawal::query()->latest('id')->first();
        $this->assertSame('Kochmesser 21 cm', $w->items);
        $this->assertStringContainsString('Betrifft: Kochmesser 21 cm', (new WithdrawalMail($w))->render());
    }

    public function test_admin_accepts_withdrawal_cancels_order_and_frees_course_day(): void
    {
        Mail::fake();
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());
        $o = $this->booking();
        $w = Withdrawal::create(['order_id' => $o->id, 'order_number' => $o->number, 'name' => 'Lea', 'email' => 'lea@example.org']);
        $this->assertFalse(CourseCalendar::available($o->course, $o->course_start));

        Livewire::test(ListWithdrawals::class)->callTableAction('annehmen', $w, ['note' => null])->assertHasNoTableActionErrors();
        $this->assertSame('storniert', $o->refresh()->status);
        $this->assertSame('angenommen', $w->refresh()->status);
        $this->assertTrue(CourseCalendar::available($o->course, $o->course_start));
        Mail::assertSent(WithdrawalMail::class, fn ($m) => $m->kind === 'angenommen');

        $w2 = Withdrawal::create(['name' => 'B', 'email' => 'b@example.org']);
        Livewire::test(ListWithdrawals::class)->callTableAction('ablehnen', $w2, ['note' => ''])->assertHasTableActionErrors(['note']);
    }

    // ---------- Steuern ----------

    public function test_tax_lines_in_confirmation_by_mode(): void
    {
        $o = Order::create(['type' => 'messer', 'status' => 'neu', 'name' => 'Max', 'email' => 'max@example.org', 'item' => 'Messer', 'amount' => 119, 'shipping_method' => 'versand', 'shipping_amount' => 0]);
        config(['ulbrich.tax_mode' => 'regular']);
        $html = preg_replace('/\s+/', ' ', (new CustomerUpdateMail($o, 'eingang'))->render());
        $this->assertStringContainsString('darin enthalten 19 % MwSt.', $html);
        $this->assertStringContainsString('19,00 €', $html);
        config(['ulbrich.tax_mode' => 'kleinunternehmer']);
        $this->assertStringContainsString('§ 19 UStG', (new CustomerUpdateMail($o, 'eingang'))->render());
        config(['ulbrich.tax_mode' => 'pending']);
        $html = (new CustomerUpdateMail($o, 'eingang'))->render();
        $this->assertStringNotContainsString('MwSt.', $html);
        $this->assertStringNotContainsString('§ 19', $html);
    }

    // ---------- Benachrichtigungen ----------

    public function test_mail_test_command_sends_all_templates(): void
    {
        Mail::fake();
        $this->seed(CourseSeeder::class);
        $this->artisan('ulbrich:mail-test', ['email' => 'ich@example.org'])->assertSuccessful();
        Mail::assertSent(CustomerUpdateMail::class, 11);
        Mail::assertSent(\App\Mail\MarketMail::class, 4);
        Mail::assertSent(WithdrawalMail::class, 3);
        Mail::assertSent(\App\Mail\VoucherMail::class, 2);
        Mail::assertSent(\App\Mail\ResharpenMail::class, 1);
    }
}
