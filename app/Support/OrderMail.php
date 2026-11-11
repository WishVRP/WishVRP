<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;

/** E-Mails zu neuen Vorgängen: Hinweis an die Manufaktur, Eingangsbestätigung an die Kundschaft. */
final class OrderMail
{
    public static function received(Order $order): void
    {
        $c = config('ulbrich');
        $lines = [
            "Neuer Vorgang {$order->number}: {$order->typeLabel()}",
            $order->summary(),
            $order->amount ? 'Richtbetrag: '.number_format((float) $order->amount, 2, ',', '.').' € (ohne Versand)'.($order->discount_percent ? " – nach {$order->discount_percent} % Weihnachtsrabatt ({$order->discount_code})" : '') : null,
            '',
            "Name: {$order->name}",
            "E-Mail: {$order->email}",
            $order->phone ? "Telefon: {$order->phone}" : null,
            $order->street ? "Adresse: {$order->street}, {$order->postal_code} {$order->city}, {$order->country}" : null,
            $order->public_name ? "Anzeigename im Edition-Raster (Zustimmung erteilt): {$order->public_name}" : null,
            $order->message ? "\nNachricht:\n{$order->message}" : null,
            '',
            'Bearbeiten in der Verwaltung → Bestellungen & Buchungen.',
        ];
        try {
            Mail::raw(implode("\n", array_filter($lines, fn ($l) => $l !== null)), function ($m) use ($order, $c) {
                $m->to($c['email'])->replyTo($order->email, $order->name)->subject("Neu: {$order->typeLabel()} {$order->number}");
            });
        } catch (\Throwable $e) {
            report($e);
        }
        // Bestell- bzw. Buchungsbestätigung an die Kundschaft (gestaltet, mit Übersicht und Zahlungsdaten)
        CustomerMail::send($order, 'eingang');
    }
}
