<?php

namespace Tests\Feature;

use App\Mail\CustomerUpdateMail;
use App\Models\Order;
use App\Support\CourseCalendar;
use App\Support\OrderPaid;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingOpensTest extends TestCase
{
    use RefreshDatabase;

    public function test_courses_bookable_from_opening_day_only(): void
    {
        $this->seed(CourseSeeder::class);
        $open = now()->addDays(40)->startOfDay();
        config(['ulbrich.booking.opens_on' => $open->toDateString()]);
        $this->assertTrue(CourseCalendar::first()->equalTo($open));
        $this->get('/schmiedekurse/buchen/')->assertSee('data-first="'.$open->toDateString().'"', false);
        $this->post('/warenkorb/hinzufuegen/', ['type' => 'kurs', 'kurs' => 'grundlagenkurs', 'datum' => now()->addDays(10)->toDateString(), 'teilnehmer' => 1])->assertSessionHasErrors('datum');
        $this->post('/warenkorb/hinzufuegen/', ['type' => 'kurs', 'kurs' => 'grundlagenkurs', 'datum' => $open->toDateString(), 'teilnehmer' => 1])->assertSessionHasNoErrors();
        config(['ulbrich.booking.opens_on' => now()->subYear()->toDateString()]);
        $this->assertTrue(CourseCalendar::first()->equalTo(now()->startOfDay()->addDays(2)), 'vergangenes Datum = ab übermorgen');
    }

    public function test_status_changes_send_the_right_mails(): void
    {
        Mail::fake();
        $this->seed(CourseSeeder::class);
        $course = \App\Models\Course::query()->where('slug', 'grundlagenkurs')->first();
        $o = Order::create(['type' => 'kurs', 'status' => 'neu', 'name' => 'Erika', 'email' => 'e@example.org', 'course_id' => $course->id,
            'course_start' => now()->addDays(20), 'course_end' => now()->addDays(20), 'participants' => 2, 'amount' => 600, 'payment_method' => 'ueberweisung']);
        Mail::assertNothingSent();

        OrderPaid::paid($o, 'ueberweisung');
        $this->assertSame('bezahlt', $o->fresh()->status, 'Kurs bleibt nach Zahlung „bezahlt“ bis zur Bestätigung');
        $this->assertNotNull($o->fresh()->invoice_number);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'bezahlt');

        $o->fresh()->update(['status' => 'bestaetigt']);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'bestaetigt');
        $html = (new CustomerUpdateMail($o->fresh(), 'bestaetigt'))->render();
        $this->assertStringContainsString('dein Kurs ist bestätigt', $html);
        $this->assertStringContainsString('Sicherheitsschuhe', $html);   // Kleidung aus config course_info
        $this->assertNotEmpty((new CustomerUpdateMail($o->fresh(), 'bestaetigt'))->attachments(), 'Kalendereintrag');

        $o->fresh()->update(['status' => 'storniert']);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'storniert');
        $this->assertNotNull($o->fresh()->cancel_invoice_number);
        $storno = new CustomerUpdateMail($o->fresh(), 'storniert');
        $this->assertStringContainsString('erstatten wir dir', $storno->render());
        $this->assertCount(1, array_filter($storno->attachments(), fn ($a) => str_ends_with($a->as ?? '', '.pdf')));

        // Ohne Mail (Häkchen aus): kein weiterer Versand
        $count = count(Mail::sent(CustomerUpdateMail::class));
        $o2 = Order::create(['type' => 'messer', 'status' => 'neu', 'name' => 'Max', 'email' => 'm@example.org', 'item' => 'Messer', 'amount' => 100]);
        \App\Support\OrderStatusFlow::quietly(fn () => $o2->update(['status' => 'storniert']));
        $this->assertCount($count, Mail::sent(CustomerUpdateMail::class));
    }
}
