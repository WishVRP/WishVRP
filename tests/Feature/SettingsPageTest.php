<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use App\Support\RuntimeSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function form(array $over = []): array
    {
        return $over + [
            'mail_host' => 'smtp.ionos.de', 'mail_port' => 587, 'mail_scheme' => 'smtp', 'mail_username' => 'info@messermanufaktur-ulbrich.de',
            'mail_password' => 'Geheim123', 'mail_from_address' => 'info@messermanufaktur-ulbrich.de', 'mail_from_name' => 'Messermanufaktur Ulbrich',
            'bank_holder' => 'Andreas Ulbrich', 'bank_iban' => 'de02120300000000202051', 'bank_bic' => 'BYLADEM1001', 'bank_name' => 'DKB',
        ];
    }

    public function test_admin_saves_mail_bank_and_stripe_without_ftp(): void
    {
        config(['ulbrich_admin.mfa_required' => false, 'mail.default' => 'log']);
        $this->actingAs(User::factory()->create());
        $this->get('/verwaltung/einstellungen')->assertOk()->assertSee('E-Mail-Versand')->assertSee('Bankverbindung')->assertSee('Stripe');

        Livewire::test(Settings::class)->fillForm($this->form(['stripe_secret' => 'sk_test_abc123', 'stripe_webhook_secret' => 'whsec_xyz789']))
            ->call('save')->assertHasNoFormErrors();

        $raw = Setting::read(RuntimeSettings::KEY);
        $this->assertNotSame('Geheim123', $raw['mail_password'], 'verschlüsselt gespeichert');
        $this->assertSame('DE02 1203 0000 0000 2020 51', $raw['bank_iban']);
        $this->assertSame('Geheim123', RuntimeSettings::all()['mail_password']);

        RuntimeSettings::apply();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('Geheim123', config('mail.mailers.smtp.password'));
        $this->assertSame('DE02 1203 0000 0000 2020 51', config('ulbrich.bank.iban'));
        $this->assertNull(config('services.stripe.secret'), 'Stripe bleibt aus, bis es freigeschaltet wird');

        // Passwort wird nie angezeigt; leeres Feld behält den gespeicherten Wert
        Livewire::test(Settings::class)->assertFormSet(['mail_password' => null])
            ->fillForm($this->form(['mail_password' => '', 'bank_holder' => 'A. Ulbrich']))->call('save')->assertHasNoFormErrors();
        $this->assertSame('Geheim123', RuntimeSettings::all()['mail_password']);
        $this->assertSame('sk_test_abc123', RuntimeSettings::all()['stripe_secret']);
        $this->assertSame('A. Ulbrich', RuntimeSettings::all()['bank_holder']);
    }

    public function test_invalid_iban_and_stripe_key_are_rejected(): void
    {
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());
        Livewire::test(Settings::class)->fillForm($this->form(['bank_iban' => 'DE00123456789012345678', 'stripe_secret' => 'pk_live_falsch']))
            ->call('save')->assertHasFormErrors(['bank_iban', 'stripe_secret']);
        $this->assertNull(Setting::read(RuntimeSettings::KEY));
    }

    public function test_test_mail_button_reports_errors(): void
    {
        config(['ulbrich_admin.mfa_required' => false]);
        $this->actingAs(User::factory()->create());
        Mail::fake();
        Livewire::test(Settings::class)->callAction('testmail', ['to' => 'ich@example.org'])->assertNotified('Test-E-Mail an ich@example.org verschickt');
    }

    public function test_bank_from_settings_appears_in_order_mail(): void
    {
        RuntimeSettings::save(['bank_holder' => 'Andreas Ulbrich', 'bank_iban' => 'DE02 1203 0000 0000 2020 51']);
        RuntimeSettings::apply();
        $o = \App\Models\Order::create(['type' => 'messer', 'name' => 'Erika', 'email' => 'e@example.org', 'item' => 'Messer', 'amount' => 100, 'payment_method' => 'ueberweisung']);
        $this->assertStringContainsString('DE02 1203 0000 0000 2020 51', (new \App\Mail\CustomerUpdateMail($o, 'eingang'))->render());
    }
}
