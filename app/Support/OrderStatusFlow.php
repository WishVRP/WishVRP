<?php

namespace App\Support;

use App\Models\Order;

/**
 * Statuswechsel → automatische E-Mail an die Kundschaft (egal ob über Knopf oder Status-Feld in der Verwaltung):
 *   Zahlungsdaten gesendet → Zahlungsaufforderung · Zahlung erhalten → Zahlungsbestätigung + Rechnung
 *   Bestätigt (Kurs) → Kursbestätigung mit Anleitung + Kalendereintrag · In Arbeit / Versendet / Zugestellt (Messer)
 *   Storniert → Stornierung (+ Stornorechnung, Hinweis zur Rückerstattung)
 * Abläufe mit eigener Mail (z. B. Knöpfe mit persönlicher Nachricht, Widerruf) laufen über quietly().
 */
final class OrderStatusFlow
{
    private static bool $quiet = false;

    public const PAID = ['bezahlt', 'bestaetigt', 'in_arbeit', 'versendet', 'zugestellt', 'abgeschlossen'];

    /** Statuswechsel ohne automatische Mail (Folgen wie Lager/Rechnung laufen trotzdem). */
    public static function quietly(callable $fn): mixed
    {
        $prev = self::$quiet;
        self::$quiet = true;
        try {
            return $fn();
        } finally {
            self::$quiet = $prev;
        }
    }

    /** Welche Mail ein Status auslöst (null = keine). */
    public static function mailFor(Order $o, string $status): ?string
    {
        return match ($status) {
            'zahlung_offen' => 'eingang',
            'bestaetigt' => $o->type === 'kurs' ? 'bestaetigt' : null,
            'in_arbeit' => in_array($o->type, ['messer', 'anfrage', 'service'], true) ? 'in_arbeit' : null,
            'versendet' => in_array($o->type, ['messer', 'anfrage', 'service'], true) ? ($o->isPickup() ? 'abholbereit' : 'versendet') : null,
            'zugestellt' => in_array($o->type, ['messer', 'anfrage', 'service'], true) ? 'zugestellt' : null,
            'storniert' => 'storniert',
            default => null,   // bezahlt: Mail kommt aus OrderPaid (mit Rechnung)
        };
    }

    /** Aus dem saved-Hook des Vorgangs. */
    public static function changed(Order $order, ?string $from): void
    {
        $to = $order->status;
        if ($to === $from) {
            return;
        }
        // Zahlung erhalten: Lager, Gutschein, Rechnung, Zahlungsbestätigung – genau einmal
        if (in_array($to, self::PAID, true) && ! in_array($from, self::PAID, true)) {
            OrderPaid::effects($order, ! self::$quiet && $to === 'bezahlt');
        }
        if (self::$quiet || ! ($kind = self::mailFor($order, $to))) {
            return;
        }
        CustomerMail::send($order->fresh(), $kind);
    }
}
