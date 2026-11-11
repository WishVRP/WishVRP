<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Note;
use App\Models\Order;
use App\Models\PageView;
use App\Models\SiteEvent;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BackofficeTest extends TestCase
{
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        Mail::fake();
    }

    private function contact(): array
    {
        return ['name' => 'Erika Muster', 'email' => 'erika@example.org', 'phone' => '0171 000', 'consent' => '1'];
    }

    public function test_booking_with_date_creates_order_and_sends_mails(): void
    {
        $day = now()->addDays(10)->toDateString();
        $this->post('/schmiedekurse/buchen/', $this->contact() + ['kurs' => 'grundlagenkurs', 'teilnehmer' => 2, 'datum' => $day, 'bedingungen' => '1'])
            ->assertRedirect('/schmiedekurse/buchen/#buchung-gesendet')->assertSessionHas('order_ok');

        $order = Order::sole();
        $this->assertSame(['kurs', 'neu', 2, '600.00', $day], [$order->type, $order->status, $order->participants, $order->amount, $order->course_start->toDateString()]);
        $this->assertMatchesRegularExpression('/^U\d{4}-0001$/', $order->number);
        $this->assertSame(1, SiteEvent::where('name', 'buchung')->count());
    }

    public function test_booking_requires_consent_terms_and_a_free_day(): void
    {
        $this->post('/schmiedekurse/buchen/', ['kurs' => 'grundlagenkurs', 'teilnehmer' => 1, 'name' => 'X', 'email' => 'x@example.org'])
            ->assertSessionHasErrors(['consent', 'bedingungen', 'datum']);
        $this->assertSame(0, Order::count());
    }

    public function test_honeypot_creates_nothing(): void
    {
        $this->post('/anfrage/', $this->contact() + ['type' => 'anfrage', 'website' => 'spam'])->assertRedirect();
        $this->assertSame(0, Order::count());
    }

    public function test_voucher_and_edition_inquiries_get_amounts(): void
    {
        $this->post('/anfrage/', $this->contact() + ['type' => 'gutschein', 'kurs' => 'damast-messerkurs', 'quantity' => 2, 'source' => '/geschenke/schmiedekurs-gutschein/'])
            ->assertRedirect('/geschenke/schmiedekurs-gutschein/#anfrage-gesendet');
        $this->post('/anfrage/', $this->contact() + ['type' => 'messer', 'item' => config('ulbrich.edition.name'), 'quantity' => 1, 'source' => '/klappmesser/']);

        [$voucher, $edition] = Order::orderBy('id')->get()->all();
        $this->assertSame(['Gutschein: Damast-Messerkurs', '2200.00'], [$voucher->item, $voucher->amount]);
        $this->assertSame('699.00', $edition->amount);
    }

    public function test_confirming_counts_seats_once_and_cancelling_frees_them(): void
    {
        $course = Course::where('slug', 'grundlagenkurs')->first();
        $date = CourseDate::create(['course_id' => $course->id, 'starts_on' => now()->addDays(10), 'seats_total' => 6, 'seats_booked' => 1, 'is_published' => true]);
        $order = Order::create(['type' => 'kurs', 'name' => 'A', 'email' => 'a@example.org', 'course_id' => $course->id, 'course_date_id' => $date->id, 'participants' => 2]);

        $order->update(['status' => 'bezahlt']);
        $this->assertSame(1, $date->fresh()->seats_booked);
        $this->assertNotNull($order->paid_at);
        $order->update(['status' => 'bestaetigt']);
        $order->update(['status' => 'versendet']);
        $this->assertSame(3, $date->fresh()->seats_booked);
        $order->update(['status' => 'storniert']);
        $this->assertSame(1, $date->fresh()->seats_booked);
    }

    public function test_page_views_are_counted_without_personal_data_and_respect_dnt_and_bots(): void
    {
        $this->get('/klappmesser/', ['User-Agent' => self::UA, 'Referer' => 'https://www.google.de/'])->assertOk();
        $this->get('/klappmesser/', ['User-Agent' => 'Googlebot/2.1'])->assertOk();
        $this->get('/klappmesser/', ['User-Agent' => self::UA, 'DNT' => '1'])->assertOk();
        $this->get('/gibt-es-nicht/', ['User-Agent' => self::UA])->assertNotFound();

        $view = PageView::sole();
        $this->assertSame(['/klappmesser/', 'suche', 'google.de', 'desktop', true], [$view->path, $view->source, $view->referrer_host, $view->device, $view->is_entry]);
        $this->assertArrayNotHasKey('ip', $view->getAttributes());
    }

    public function test_click_beacon_only_stores_known_events(): void
    {
        $this->post('/e/', ['n' => 'telefon', 'p' => '/'], ['User-Agent' => self::UA])->assertNoContent();
        $this->post('/e/', ['n' => 'irgendwas', 'p' => '/'], ['User-Agent' => self::UA])->assertNoContent();
        $this->post('/e/', ['n' => 'extern', 'p' => '/', 't' => 'https://www.instagram.com/x'], ['User-Agent' => self::UA]);

        $this->assertSame(['telefon', 'extern'], SiteEvent::orderBy('id')->pluck('name')->all());
        $this->assertSame('www.instagram.com', SiteEvent::where('name', 'extern')->value('target'));
    }

    public function test_admin_requires_login_and_admin_traffic_is_not_counted(): void
    {
        $this->get('/'.config('ulbrich_admin.path'), ['User-Agent' => self::UA])->assertRedirect();
        $this->assertSame(0, PageView::count());
    }

    public function test_notes_belong_to_users_and_orders(): void
    {
        $user = User::factory()->create(['name' => 'Andreas']);
        $order = Order::create(['type' => 'anfrage', 'name' => 'B', 'email' => 'b@example.org']);
        $note = Note::create(['user_id' => $user->id, 'order_id' => $order->id, 'body' => 'Ruft wegen Rückerstattung an.']);

        $this->assertSame('Andreas', $note->user->name);
        $this->assertSame(1, $order->notes()->count());
        $this->assertSame(1, Note::query()->open()->count());
    }
}
