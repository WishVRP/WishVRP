<?php

namespace Tests\Feature;

use App\Mail\CustomerUpdateMail;
use App\Mail\ResharpenMail;
use App\Mail\VoucherMail;
use App\Models\Course;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Voucher;
use App\Support\CourseIcs;
use App\Support\Invoice;
use App\Support\OrderAutomation;
use App\Support\OrderPaid;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Rechnungen, Gutschein-PDF + Wunschtermin, Kurs-Erinnerung, Nachschärfen, Galerie, Pflegeanleitung. */
class DocumentsAndServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['ulbrich.tax_mode' => 'regular']);
    }

    private function knifeOrder(array $extra = []): Order
    {
        return Order::create($extra + ['type' => 'messer', 'status' => 'zahlung_offen', 'name' => 'Erika Muster', 'email' => 'erika@example.org',
            'street' => 'Hauptstraße 5', 'postal_code' => '74196', 'city' => 'Neuenstadt', 'country' => 'Deutschland',
            'item' => 'Damast-Kochmesser', 'quantity' => 1, 'amount' => 111.01, 'shipping_amount' => 7.99, 'shipping_method' => 'versand']);
    }

    // ---------- Rechnungen ----------

    public function test_payment_creates_numbered_invoice_attached_to_order_confirmation(): void
    {
        Mail::fake();
        $o = $this->knifeOrder();
        OrderPaid::paid($o, 'ueberweisung');
        $o->refresh();

        $this->assertSame('RE-'.now()->year.'-0001', $o->invoice_number);
        Storage::disk('local')->assertExists(Invoice::path($o->invoice_number));
        $this->assertStringStartsWith('%PDF', Invoice::pdf($o));

        $doc = Invoice::data($o);
        $this->assertSame(119.0, $doc['gross']);
        $this->assertSame(19.0, $doc['vat']);
        $this->assertSame(100.0, $doc['net']);

        Mail::assertSent(CustomerUpdateMail::class, function (CustomerUpdateMail $m) use ($o) {
            $names = collect($m->attachments())->map(fn ($a) => $a->as)->all();

            return $m->kind === 'bezahlt' && in_array(Invoice::filename($o->invoice_number), $names, true);
        });
        $this->assertStringContainsString($o->invoice_number.' – als PDF im Anhang', (new CustomerUpdateMail($o, 'bezahlt'))->render());

        // zweite Zahlung desselben Vorgangs: keine neue Nummer; nächster Vorgang: fortlaufend
        OrderPaid::paid($o->refresh(), 'ueberweisung');
        $this->assertSame('RE-'.now()->year.'-0001', $o->refresh()->invoice_number);
        $o2 = $this->knifeOrder(['email' => 'zwei@example.org']);
        OrderPaid::paid($o2, 'ueberweisung');
        $this->assertSame('RE-'.now()->year.'-0002', $o2->refresh()->invoice_number);
    }

    public function test_cancellation_creates_storno_invoice_and_small_business_has_no_vat(): void
    {
        Mail::fake();
        $o = $this->knifeOrder();
        OrderPaid::paid($o, 'ueberweisung');
        $o->refresh()->update(['status' => 'storniert']);
        $o->refresh();

        $this->assertSame('RE-'.now()->year.'-0002', $o->cancel_invoice_number);
        $this->assertSame(-119.0, Invoice::data($o, true)['gross']);
        Storage::disk('local')->assertExists(Invoice::path($o->cancel_invoice_number));

        config(['ulbrich.tax_mode' => 'kleinunternehmer']);
        $this->assertSame(0.0, Invoice::data($o)['vat']);
    }

    public function test_requests_without_amount_get_no_invoice(): void
    {
        $o = Order::create(['type' => 'anfrage', 'status' => 'neu', 'name' => 'A', 'email' => 'a@example.org']);
        $this->assertNull(Invoice::assign($o));
        $this->assertNull($o->refresh()->invoice_number);
    }

    // ---------- Gutschein-PDF und Versand zum Wunschtermin ----------

    public function test_voucher_pdf_goes_to_buyer_and_to_recipient_on_chosen_day(): void
    {
        Mail::fake();
        $this->seed(CourseSeeder::class);
        $day = now()->addDays(5)->toDateString();
        $this->post('/gutschein/kaufen/', ['kurs' => 'grundlagenkurs', 'personen' => 2, 'empfaenger' => 'Max', 'name' => 'Schenkerin', 'email' => 's@example.org',
            'consent' => '1', 'agb' => '1', 'geschenk_email' => 'max@example.org', 'geschenk_datum' => $day, 'geschenk_nachricht' => 'Frohe Weihnachten!'])
            ->assertSessionHasNoErrors();
        $v = Voucher::sole();
        $this->assertSame(['max@example.org', $day, 'Frohe Weihnachten!'], [$v->recipient_email, $v->send_on->toDateString(), $v->gift_message]);

        OrderPaid::paid(Order::sole(), 'ueberweisung');
        Mail::assertSent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'buyer' && $m->hasTo('s@example.org') && count($m->attachments()) === 1);
        Mail::assertNotSent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'recipient');
        $this->assertStringStartsWith('%PDF', VoucherMail::pdf($v->refresh()));

        $this->artisan('vouchers:send-gifts')->assertSuccessful();
        Mail::assertNotSent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'recipient');

        $this->travelTo(now()->addDays(5)->setTime(7, 0));
        $this->artisan('vouchers:send-gifts')->assertSuccessful();
        $this->artisan('vouchers:send-gifts')->assertSuccessful();
        Mail::assertSent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'recipient' && $m->hasTo('max@example.org'));
        $this->assertSame(1, Mail::sent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'recipient')->count(), 'genau einmal');
        $this->assertNotNull($v->refresh()->sent_to_recipient_at);
    }

    public function test_voucher_without_date_goes_to_recipient_right_after_payment(): void
    {
        Mail::fake();
        $this->seed(CourseSeeder::class);
        $this->post('/gutschein/kaufen/', ['kurs' => 'grundlagenkurs', 'personen' => 1, 'name' => 'S', 'email' => 's@example.org', 'consent' => '1', 'agb' => '1', 'geschenk_email' => 'max@example.org'])
            ->assertSessionHasNoErrors();
        OrderPaid::paid(Order::sole(), 'ueberweisung');
        Mail::assertSent(VoucherMail::class, fn (VoucherMail $m) => $m->audience === 'recipient' && $m->hasTo('max@example.org'));

        $this->post('/gutschein/kaufen/', ['kurs' => 'grundlagenkurs', 'personen' => 1, 'name' => 'S', 'email' => 's@example.org', 'consent' => '1', 'agb' => '1',
            'geschenk_email' => 'x@example.org', 'geschenk_datum' => now()->subDay()->toDateString()])->assertSessionHasErrors('geschenk_datum');
    }

    // ---------- Kurs-Erinnerung ----------

    public function test_course_reminder_three_days_before_once_with_calendar_entry(): void
    {
        Mail::fake();
        $this->seed(CourseSeeder::class);
        $course = Course::query()->orderBy('sort')->first();
        $make = fn (int $days, array $x = []) => Order::create($x + ['type' => 'kurs', 'status' => 'bestaetigt', 'name' => 'Lea', 'email' => 'lea'.$days.'@example.org',
            'course_id' => $course->id, 'course_start' => now()->addDays($days)->startOfDay(), 'course_end' => now()->addDays($days)->startOfDay(), 'participants' => 2, 'amount' => 600,
            'created_at' => now()->subDays(10)]);
        $due = $make(3);
        $make(10);                                          // zu früh
        $make(3, ['status' => 'storniert', 'email' => 'storno@example.org']);
        $fresh = $make(2, ['email' => 'heute@example.org']);
        $fresh->forceFill(['created_at' => now()])->saveQuietly();   // heute gebucht → Bestätigung reicht

        $this->assertSame(1, OrderAutomation::courseReminders());
        $this->assertSame(0, OrderAutomation::courseReminders(), 'nur einmal');
        $this->assertNotNull($due->refresh()->reminder_sent_at);
        Mail::assertSent(CustomerUpdateMail::class, fn (CustomerUpdateMail $m) => $m->kind === 'erinnerung' && $m->hasTo('lea3@example.org')
            && collect($m->attachments())->contains(fn ($a) => str_ends_with($a->as, '.ics')));

        $html = (new CustomerUpdateMail($due, 'erinnerung'))->render();
        $this->assertStringContainsString(config('ulbrich.booking.course_start_time').' Uhr', $html);
        $this->assertStringContainsString('Route in Google Maps', $html);

        $ics = CourseIcs::make($due);
        $this->assertStringContainsString('DTSTART;VALUE=DATE:'.$due->course_start->format('Ymd'), $ics);
        $this->assertStringContainsString('DTEND;VALUE=DATE:'.$due->course_start->copy()->addDay()->format('Ymd'), $ics);
        $this->assertStringContainsString("BEGIN:VEVENT\r\n", $ics);
    }

    // ---------- Nachschärfen einschicken ----------

    public function test_resharpen_form_creates_service_order_and_sends_address(): void
    {
        Mail::fake();
        $original = $this->knifeOrder(['status' => 'zugestellt']);
        $this->get('/nachschaerfen/')->assertOk()->assertSee('Vorgangsnummer anfordern');
        $this->get('/garantie/')->assertSee('href="/nachschaerfen/"', false);

        $base = ['name' => 'Erika Muster', 'email' => 'erika@example.org', 'origin' => 'gekauft', 'knives' => 'Damast-Kochmesser', 'count' => 1, 'consent' => '1'];
        $this->post('/nachschaerfen/', $base + ['delivery' => 'einsenden'])->assertSessionHasErrors(['street', 'postal_code', 'city']);

        $this->post('/nachschaerfen/', $base + ['delivery' => 'einsenden', 'order_number' => strtolower($original->number), 'street' => 'Hauptstraße 5', 'postal_code' => '74196', 'city' => 'Neuenstadt'])
            ->assertRedirect('/nachschaerfen/#gesendet')->assertSessionHas('resharpen_ok');
        $o = Order::query()->latest('id')->first();
        $this->assertSame(['service', 'versand', 'Hauptstraße 5'], [$o->type, $o->shipping_method, $o->street]);
        $this->assertStringContainsString('Schliff-Garantie', $o->item);
        $this->assertTrue($o->notes()->where('body', 'like', '%E-Mail passt%')->exists());
        Mail::assertSent(ResharpenMail::class, fn ($m) => $m->hasTo('erika@example.org'));
        $text = (new ResharpenMail($o))->render();
        $this->assertStringContainsString(config('ulbrich.address.street'), $text);
        $this->assertStringContainsString($o->number, $text);

        // vorbeibringen: keine Anschrift nötig; fremde Nummer wird markiert
        $this->post('/nachschaerfen/', ['delivery' => 'vorbeibringen', 'email' => 'fremd@example.org', 'order_number' => $original->number] + $base)->assertSessionHasNoErrors();
        $last = Order::query()->latest('id')->first();
        $this->assertSame('abholung', $last->shipping_method);
        $this->assertTrue($last->notes()->where('body', 'like', '%bitte prüfen%')->exists());
    }

    // ---------- Galerie und Pflegeanleitung ----------

    public function test_gallery_shows_only_consented_photos_and_is_indexed_from_six(): void
    {
        config(['seo.indexable' => true]);
        $this->get('/galerie/')->assertOk()->assertSee('noindex', false)->assertSee('bald Messer aus unseren Kursen');
        GalleryItem::create(['image' => 'galerie/ohne.jpg', 'alt' => 'Ohne Einwilligung', 'consent' => false]);
        foreach (range(1, 6) as $i) {
            GalleryItem::create(['image' => "galerie/bild-$i.jpg", 'alt' => "Messer $i", 'consent' => true, 'published' => true]);
        }
        $html = $this->get('/galerie/')->assertOk()->getContent();
        $this->assertStringContainsString('alt="Messer 1"', $html);
        $this->assertStringNotContainsString('Ohne Einwilligung', $html);
        $this->assertStringNotContainsString('noindex', $html);
        $this->assertStringContainsString('/galerie/', $this->get('/sitemap.xml')->getContent());
    }

    public function test_care_guide_pdf_exists_and_is_linked(): void
    {
        $file = public_path('downloads/pflegeanleitung-messermanufaktur-ulbrich.pdf');
        $this->assertFileExists($file);
        $this->assertStringStartsWith('%PDF', (string) file_get_contents($file, false, null, 0, 4));
        $this->get('/ratgeber/messer-pflege/')->assertSee('/downloads/pflegeanleitung-messermanufaktur-ulbrich.pdf', false);
    }
}
