<?php

namespace Tests\Feature;

use App\Filament\Resources\DiscountCodes\Pages\ListDiscountCodes;
use App\Mail\CustomerUpdateMail;
use App\Mail\DiscountCodeMail;
use App\Models\Course;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Invoice;
use App\Support\OrderPaid;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private array $contact = ['name' => 'Erika Muster', 'email' => 'erika@example.org', 'consent' => '1', 'agb' => '1'];

    private array $address = ['versand' => 'versand', 'country_code' => 'DE', 'street' => 'Musterweg 1', 'postal_code' => '74196', 'city' => 'Neuenstadt'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['ulbrich.tax_mode' => 'regular']);
        $this->seed(CourseSeeder::class);
    }

    private function product(string $name, float $price, int $stock = 3): Product
    {
        return Product::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'category' => 'kueche', 'status' => 'aktiv', 'price' => $price, 'stock' => $stock, 'weight_g' => 800]);
    }

    private function courseDay(int $days = 20): string
    {
        return now()->addDays($days)->toDateString();
    }

    public function test_product_into_cart_and_checkout_by_bank_transfer(): void
    {
        $a = $this->product('Kochmesser Eiche', 200);
        $b = $this->product('Brotmesser Nuss', 150);
        $this->get($a->url())->assertSee('In den Warenkorb');
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $a->id, 'menge' => 2])->assertRedirect('/warenkorb/');
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $b->id]);
        $this->get('/warenkorb/')->assertOk()->assertSee('Kochmesser Eiche')->assertSee('550,00 €')->assertSee('Zur Kasse');
        $this->get('/')->assertSee('cart-link__count', false);

        $this->get('/kasse/')->assertOk()->assertSee('Zahlungspflichtig bestellen')->assertSee('bald verfügbar');
        $this->post('/kasse/', $this->contact + $this->address)->assertRedirect('/kasse/');

        $o = Order::query()->sole();
        $this->assertSame('messer', $o->type);
        $this->assertSame('ueberweisung', $o->payment_method);
        $this->assertSame('550.00', $o->amount);
        $this->assertSame(3, (int) $o->quantity);
        $this->assertCount(2, $o->items);
        $this->assertNull($o->product_id);
        $this->assertGreaterThan(0, (float) $o->shipping_amount);
        Mail::assertSent(CustomerUpdateMail::class, fn ($m) => $m->kind === 'eingang');
        $this->assertSame([], session('cart', []));
        $this->get('/kasse/')->assertSee('Danke für deine Bestellung');   // Flash noch einmal sichtbar nach Redirect
        $html = (new CustomerUpdateMail($o, 'eingang'))->render();
        $this->assertStringContainsString('Brotmesser Nuss', $html);
        $this->assertStringContainsString('Kochmesser Eiche</b> × 2', $html);

        // Zahlung erhalten → Lager je Artikel, Rechnung je Position
        OrderPaid::paid($o->fresh(), 'ueberweisung');
        $this->assertSame(1, $a->fresh()->stock);
        $this->assertSame(2, $b->fresh()->stock);
        $doc = Invoice::data($o->fresh());
        $this->assertSame(['Kochmesser Eiche', 'Brotmesser Nuss'], array_slice(array_column($doc['lines'], 'text'), 0, 2));
        $this->assertEqualsWithDelta(550 + (float) $o->shipping_amount, $doc['gross'], 0.01);
    }

    public function test_course_goes_through_cart_and_creates_its_own_order(): void
    {
        $knife = $this->product('Kochmesser Eiche', 200);
        $day = $this->courseDay();
        $this->post('/warenkorb/hinzufuegen/', ['type' => 'kurs', 'kurs' => 'grundlagenkurs', 'datum' => $day, 'teilnehmer' => 2])->assertRedirect('/warenkorb/');
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $knife->id]);
        $this->get('/kasse/')->assertSee('Kursbedingungen', false)->assertSee('Kurs-Gutscheincode');

        $code = DiscountCode::create(['code' => 'ALLE10', 'type' => 'percent', 'percent' => 10, 'scope' => 'alles', 'max_uses' => 1]);
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'alle10'])->assertSessionHasNoErrors();

        $kurs = Order::query()->where('type', 'kurs')->sole();
        $shop = Order::query()->where('type', 'messer')->sole();
        $this->assertSame($day, $kurs->course_start->toDateString());
        $this->assertSame(2, (int) $kurs->participants);
        $this->assertSame('540.00', $kurs->amount);   // 2 × 300 − 10 %
        $this->assertSame('180.00', $shop->amount);   // 200 − 10 %
        $this->assertSame(1, $code->fresh()->uses, 'eine Bestellung = eine Nutzung');
        $this->assertNotNull($code->fresh()->redeemed_at);

        // Tag ist jetzt belegt: zweiter Versuch landet mit Hinweis im Warenkorb
        $this->post('/warenkorb/hinzufuegen/', ['type' => 'kurs', 'kurs' => 'grundlagenkurs', 'datum' => $day, 'teilnehmer' => 1])->assertSessionHasErrors('datum');
    }

    public function test_euro_code_applies_once_and_scope_and_minimum_are_checked(): void
    {
        $knife = $this->product('Kochmesser Eiche', 200);
        DiscountCode::create(['code' => 'KURS20', 'type' => 'percent', 'percent' => 20, 'scope' => 'kurse', 'max_uses' => null]);
        DiscountCode::create(['code' => 'MIN500', 'type' => 'amount', 'amount' => 50, 'scope' => 'alles', 'min_order' => 500, 'max_uses' => null]);
        $euro = DiscountCode::create(['code' => 'EURO25', 'type' => 'amount', 'amount' => 25, 'scope' => 'alles', 'max_uses' => null]);

        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $knife->id]);
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'KURS20'])->assertSessionHasErrors('discount_code');
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'MIN500'])->assertSessionHasErrors('discount_code');
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'EURO25'])->assertSessionHasNoErrors();

        $o = Order::query()->sole();
        $this->assertSame('175.00', $o->amount);
        $this->assertSame('25.00', $o->discount_amount);
        $this->assertNull($o->discount_percent);
        $this->assertSame(1, $euro->fresh()->uses);
        $this->assertNull($euro->fresh()->redeemed_at, 'unbegrenzt einlösbar');
        $lines = array_column(Invoice::data($o)['lines'], 'text');
        $this->assertContains('Rabatt (Code EURO25)', $lines);
    }

    public function test_code_bound_to_email_and_used_up_codes(): void
    {
        $knife = $this->product('Kochmesser Eiche', 200);
        DiscountCode::create(['code' => 'NUR-ERIKA', 'percent' => 15, 'email' => 'Erika@Example.org', 'max_uses' => 1, 'uses' => 0]);
        DiscountCode::create(['code' => 'WEG', 'percent' => 15, 'max_uses' => 1, 'uses' => 1]);
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $knife->id]);
        $this->post('/kasse/', array_merge($this->contact, ['email' => 'jemand@example.org']) + $this->address + ['discount_code' => 'NUR-ERIKA'])->assertSessionHasErrors('discount_code');
        $this->assertSame(0, Order::query()->count());
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'NUR-ERIKA'])->assertSessionHasNoErrors();
        $this->assertSame('170.00', Order::query()->sole()->amount);
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $knife->id]);
        $this->post('/kasse/', $this->contact + $this->address + ['discount_code' => 'WEG'])->assertSessionHasErrors('discount_code');
    }

    public function test_cart_quantity_change_remove_and_unavailable_items(): void
    {
        $knife = $this->product('Kochmesser Eiche', 200, 2);
        $this->post('/warenkorb/hinzufuegen/', ['produkt' => $knife->id, 'menge' => 5]);
        $this->assertSame(2, session('cart')['p'.$knife->id]['qty'], 'nie mehr als Lagerbestand');
        $this->post('/warenkorb/aendern/', ['key' => 'p'.$knife->id, 'menge' => 1]);
        $this->assertSame(1, session('cart')['p'.$knife->id]['qty']);
        $knife->update(['stock' => 0]);
        $this->get('/warenkorb/')->assertSee('Leider nicht mehr verfügbar');
        $this->post('/kasse/', $this->contact + $this->address)->assertRedirect('/warenkorb/');
        $this->assertSame(0, Order::query()->count());
        $this->post('/warenkorb/aendern/', ['key' => 'p'.$knife->id, 'menge' => 0]);
        $this->get('/warenkorb/')->assertSee('Dein Warenkorb ist noch leer');
    }

    public function test_admin_creates_generates_and_sends_codes(): void
    {
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());

        Livewire::test(ListDiscountCodes::class)
            ->callAction('create', ['code' => 'sommer-25', 'max_uses' => null, 'type' => 'amount', 'amount' => 25, 'scope' => 'shop', 'label' => 'Sommeraktion'])
            ->assertHasNoActionErrors();
        $c = DiscountCode::query()->where('code', 'SOMMER-25')->sole();
        $this->assertSame('verwaltung', $c->source);
        $this->assertSame('25,00 €', $c->valueLabel());
        $this->assertNull($c->max_uses);

        Livewire::test(ListDiscountCodes::class)
            ->callAction('mehrere', ['count' => 5, 'prefix' => 'GEWINN', 'type' => 'percent', 'percent' => 15, 'scope' => 'alles', 'label' => 'Gewinnspiel'])
            ->assertHasNoActionErrors();
        $this->assertSame(5, DiscountCode::query()->where('code', 'like', 'GEWINN-%')->where('max_uses', 1)->where('percent', 15)->count());

        Livewire::test(ListDiscountCodes::class)
            ->callTableAction('senden', $c, ['to' => 'gewinner@example.org', 'name' => 'Max', 'note' => 'Glückwunsch!'])
            ->assertHasNoTableActionErrors();
        Mail::assertSent(DiscountCodeMail::class, fn ($m) => $m->hasTo('gewinner@example.org'));
        $this->assertNotNull($c->fresh()->sent_at);
        $text = (new DiscountCodeMail($c, 'Max', 'Glückwunsch!'))->render();
        $this->assertStringContainsString('SOMMER-25', $text);
        $this->assertStringContainsString('Nur Shop', $text);
    }
}
