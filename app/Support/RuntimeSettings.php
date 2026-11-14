<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

/**
 * Einstellungen aus der Verwaltung (Seite „Einstellungen“): E-Mail-Versand, Bankverbindung, Stripe.
 * Ausgefüllte Werte haben Vorrang vor der .env. Passwörter und Schlüssel liegen verschlüsselt (APP_KEY) in der Datenbank.
 */
final class RuntimeSettings
{
    public const KEY = 'runtime';

    public const SECRETS = ['mail_password', 'stripe_secret', 'stripe_webhook_secret'];

    /** @return array<string, mixed> gespeicherte Werte, Geheimnisse entschlüsselt */
    public static function all(): array
    {
        $raw = (array) Setting::read(self::KEY, []);
        foreach (self::SECRETS as $k) {
            if (filled($raw[$k] ?? null)) {
                try {
                    $raw[$k] = Crypt::decryptString($raw[$k]);
                } catch (\Throwable) {
                    $raw[$k] = null;   // z. B. nach Wechsel des App-Schlüssels
                }
            }
        }

        return $raw;
    }

    /** Speichern; leere Geheimnis-Felder behalten den bisherigen Wert. */
    public static function save(array $data): void
    {
        $old = (array) Setting::read(self::KEY, []);
        foreach (self::SECRETS as $k) {
            $data[$k] = filled($data[$k] ?? null) ? Crypt::encryptString(trim((string) $data[$k])) : ($old[$k] ?? null);
        }
        Setting::write(self::KEY, array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data));
    }

    public static function forget(string $secret): void
    {
        $old = (array) Setting::read(self::KEY, []);
        $old[$secret] = null;
        Setting::write(self::KEY, $old);
    }

    public static function has(string $key): bool
    {
        return filled(((array) Setting::read(self::KEY, []))[$key] ?? null);
    }

    /** Beim Start jeder Anfrage: gespeicherte Werte in die Konfiguration übernehmen. */
    public static function apply(): void
    {
        $s = self::all();
        if (! $s) {
            return;
        }
        $set = fn (string $configKey, ?string $settingKey) => filled($s[$settingKey] ?? null) ? config([$configKey => $s[$settingKey]]) : null;

        // E-Mail-Versand (SMTP)
        if (filled($s['mail_host'] ?? null) && filled($s['mail_username'] ?? null) && filled($s['mail_password'] ?? null)) {
            config(['mail.default' => 'smtp', 'mail.mailers.smtp.url' => null]);
            $set('mail.mailers.smtp.host', 'mail_host');
            $set('mail.mailers.smtp.port', 'mail_port');
            $set('mail.mailers.smtp.scheme', 'mail_scheme');
            $set('mail.mailers.smtp.username', 'mail_username');
            $set('mail.mailers.smtp.password', 'mail_password');
        }
        $set('mail.from.address', 'mail_from_address');
        $set('mail.from.name', 'mail_from_name');

        // Bankverbindung (Bestellbestätigung, Rechnung, Markt)
        foreach (['holder', 'iban', 'bic', 'name'] as $k) {
            $set('ulbrich.bank.'.$k, 'bank_'.$k);
        }

        // Kurse: erster buchbarer Kurstag
        $set('ulbrich.booking.opens_on', 'booking_opens_on');

        // Stripe: Schlüssel werden gespeichert; aktiv erst, wenn „Online-Zahlung aktiv“ eingeschaltet ist
        if (! empty($s['stripe_enabled'])) {
            $set('services.stripe.secret', 'stripe_secret');
            $set('services.stripe.webhook_secret', 'stripe_webhook_secret');
        }
    }
}
