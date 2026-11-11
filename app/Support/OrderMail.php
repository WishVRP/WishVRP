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
            $order->amount ? 'Richtbetrag: '.number_format((float) $order->amount, 2, ',', '.').' € (ohne Versand)' : null,
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
            Mail::raw(implode("\n", [
                "Hallo {$order->name},",
                '',
                "danke für deine Nachricht an die {$c['name']}. Wir haben deine {$order->typeLabel()} erhalten:",
                $order->summary(),
                '',
                "Vorgangsnummer: {$order->number}",
                '',
                $order->type === 'kurs'
                    ? 'Dein Platz ist verbindlich, sobald wir ihn dir nach Zahlungseingang bestätigt haben. Die Zahlungsdaten bekommst du von uns.'
                    : 'Wir melden uns persönlich bei dir, um alle Details, den Preis und die Lieferung zu klären.',
                '',
                "Fragen? Ruf an: {$c['phone']['display']}",
                '',
                "{$c['owner']}",
                $c['name'],
            ]), fn ($m) => $m->to($order->email, $order->name)->subject("Deine Anfrage bei der {$c['name']} ({$order->number})"));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
