<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Automatische Schritte nach Kauf und Kurs (läuft über den Zeitplan, siehe routes/console.php). */
final class OrderAutomation
{
    /**
     * Abends nach dem (letzten) Kurstag: Danke + Bitte um Google-Bewertung, Vorgang wird abgeschlossen.
     * Holt bis zu 3 Tage nach, falls der Zeitplan einmal nicht lief. Stornierte und unbezahlte Buchungen bekommen nichts.
     */
    public static function courseReviews(?Carbon $today = null): int
    {
        // Vergleich nur nach Kalendertag (Y-m-d), unabhängig von Zeitzonen
        $todayStr = ($today ?? now('Europe/Berlin'))->format('Y-m-d');
        $today = Carbon::parse($todayStr);
        $sent = 0;
        Order::query()->with(['course', 'courseDate'])
            ->where('type', 'kurs')->whereNull('review_requested_at')
            ->whereIn('status', ['bezahlt', 'bestaetigt'])
            ->where('created_at', '>=', $today->copy()->subYears(6))
            ->each(function (Order $order) use ($today, &$sent) {
                $last = $order->courseLastDay();
                $day = $last ? Carbon::parse($last->format('Y-m-d')) : null;
                if (! $day || $day->gt($today) || $day->lt($today->copy()->subDays(3))) {
                    return;
                }
                if (! $order->review_consent) {
                    $order->forceFill(['review_requested_at' => now()])->saveQuietly();   // ohne Einwilligung keine Mail
                    $order->update(['status' => 'abgeschlossen']);

                    return;
                }
                if (CustomerMail::send($order, 'kurs')) {
                    $order->update(['status' => 'abgeschlossen']);
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * Erinnerung vor dem Kurs (Standard: 3 Tage vorher, morgens) mit allen Infos und Kalendereintrag – genau einmal.
     * Wer kurzfristig bucht (weniger als 3 Tage vorher), bekommt sie am nächsten Morgen, sofern der Kurs noch nicht begonnen hat.
     */
    public static function courseReminders(?Carbon $today = null): int
    {
        $todayStr = ($today ?? now('Europe/Berlin'))->format('Y-m-d');
        $until = Carbon::parse($todayStr)->addDays((int) config('ulbrich.booking.reminder_days_before', 3))->format('Y-m-d');
        $sent = 0;
        Order::query()->with('course')->where('type', 'kurs')->whereNull('reminder_sent_at')
            ->whereIn('status', ['bezahlt', 'bestaetigt'])->whereNotNull('course_start')
            ->whereDate('course_start', '>', $todayStr)->whereDate('course_start', '<=', $until)
            ->whereDate('created_at', '<', $todayStr)   // am Buchungstag reicht die Bestätigung
            ->each(function (Order $order) use (&$sent) {
                if (CustomerMail::send($order, 'erinnerung')) {
                    $order->forceFill(['reminder_sent_at' => now()])->saveQuietly();
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * DHL-Sendungen automatisch auf „Zugestellt“ setzen (DHL Shipment Tracking API, Schlüssel DHL_API_KEY).
     * Andere Versanddienste: in der Verwaltung von Hand auf „Zugestellt“ setzen.
     */
    public static function trackDeliveries(): int
    {
        $key = config('services.dhl.key');
        if (! $key) {
            return 0;
        }
        $done = 0;
        Order::query()->where('status', 'versendet')->where('tracking_carrier', 'dhl')->whereNotNull('tracking_number')
            ->where('shipped_at', '>=', now()->subDays(30))
            ->where(fn ($q) => $q->whereNull('tracking_checked_at')->orWhere('tracking_checked_at', '<=', now()->subMinutes(100)))
            ->limit(40)   // Kontingent der kostenlosen DHL-Schnittstelle schonen
            ->get()
            ->each(function (Order $order) use ($key, &$done) {
                $order->forceFill(['tracking_checked_at' => now()])->saveQuietly();
                try {
                    $res = Http::timeout(8)->acceptJson()->withHeaders(['DHL-API-Key' => $key])
                        ->get('https://api-eu.dhl.com/track/shipments', ['trackingNumber' => $order->tracking_number, 'language' => 'de']);
                    if (! $res->successful()) {
                        return;
                    }
                    $status = $res->json('shipments.0.status');
                } catch (\Throwable $e) {
                    Log::warning('DHL-Tracking: '.$e->getMessage());

                    return;
                }
                if (($status['statusCode'] ?? null) !== 'delivered') {
                    return;
                }
                $order->update(['status' => 'zugestellt', 'delivered_at' => isset($status['timestamp']) ? Carbon::parse($status['timestamp']) : now()]);
                CustomerMail::send($order, 'zugestellt');
                $done++;
            });

        return $done;
    }
}
