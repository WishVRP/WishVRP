<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * SMS-Versand. Treiber „log“ verschickt nichts (Entwicklung), „seven“ nutzt seven.io.
 * In der Produktion ist „log“ gesperrt, damit nie ein Spiel ohne echte SMS-Prüfung startet.
 */
final class Sms
{
    public static function canSend(): bool
    {
        $driver = config('services.sms.driver');

        return $driver === 'seven' ? filled(config('services.sms.seven_key')) : ! app()->isProduction();
    }

    public static function send(string $to, string $text): void
    {
        $driver = config('services.sms.driver');
        if ($driver === 'seven') {
            $res = Http::withHeaders(['X-Api-Key' => (string) config('services.sms.seven_key'), 'Accept' => 'application/json'])
                ->asForm()->timeout(10)
                ->post('https://gateway.seven.io/api/sms', ['to' => $to, 'text' => $text, 'from' => config('services.sms.from')]);
            if (! $res->successful() || (string) ($res->json('success') ?? '') !== '100') {
                throw new RuntimeException('SMS-Versand fehlgeschlagen: '.$res->status());
            }

            return;
        }
        if (app()->isProduction()) {
            throw new RuntimeException('Kein SMS-Anbieter eingerichtet.');
        }
        Log::info('SMS (nicht verschickt, Treiber log)', ['an' => $to, 'text' => $text]);
    }
}
