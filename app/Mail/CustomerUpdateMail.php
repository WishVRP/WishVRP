<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\Reviews;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Status-Mails an Kundinnen und Kunden:
 *  in_arbeit   – Wir haben mit deinem Messer begonnen (Fertigungszeit)
 *  versendet   – Paket ist unterwegs (Versanddienst, Sendungsnummer, Tracking-Link)
 *  abholbereit – liegt zur Abholung in der Manufaktur bereit
 *  zugestellt  – angekommen, Pflegehinweis, Bitte um Google-Bewertung
 *  kurs        – Danke für den Kurstag, Bitte um Google-Bewertung
 */
class CustomerUpdateMail extends Mailable
{
    use Queueable;

    public const KINDS = [
        'eingang' => 'Eingangsbestätigung',
        'bezahlt' => 'Auftragsbestätigung',
        'umgebucht' => 'Dein Kurs ist umgebucht',
        'in_arbeit' => 'Dein Messer ist in Arbeit',
        'versendet' => 'Dein Paket ist unterwegs',
        'abholbereit' => 'Abholbereit in der Manufaktur',
        'zugestellt' => 'Dein Paket ist angekommen',
        'kurs' => 'Danke für deinen Kurstag',
    ];

    public static function title(Order $o, string $kind): string
    {
        return match ($kind) {
            'eingang' => match ($o->type) {
                'kurs' => 'Danke für deine Buchung', 'anfrage', 'service' => 'Danke für deine Anfrage', default => 'Danke für deine Bestellung',
            },
            'bezahlt' => $o->type === 'kurs' ? 'Dein Kurstag ist fest gebucht' : 'Zahlung erhalten',
            default => self::KINDS[$kind],
        };
    }

    public function __construct(public Order $order, public string $kind, public ?string $personal = null) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->kind) {
            'eingang' => match ($this->order->type) {
                // AGB § 2: Die automatische Mail bestätigt nur den Eingang; Annahme = Auftragsbestätigung nach Zahlungseingang
                'kurs' => 'Eingangsbestätigung deiner Kursbuchung', 'gutschein' => 'Eingangsbestätigung deiner Gutscheinbestellung', 'anfrage', 'service' => 'Eingangsbestätigung deiner Anfrage', default => 'Eingangsbestätigung deiner Bestellung',
            },
            'bezahlt' => 'Auftragsbestätigung – '.($this->order->type === 'kurs' ? 'Zahlung erhalten, dein Kurstag ist fest gebucht' : 'Zahlung erhalten, vielen Dank'),
            'umgebucht' => 'Umbuchung bestätigt: neuer Kurstag',
            'in_arbeit' => 'Wir haben mit deiner Bestellung begonnen',
            'versendet' => 'Dein Paket ist unterwegs',
            'abholbereit' => 'Deine Bestellung liegt zur Abholung bereit',
            'zugestellt' => 'Dein Paket ist angekommen – alles gut?',
            'kurs' => 'Danke für deinen Tag an der Esse',
        };

        return new Envelope(subject: $subject.' ('.$this->order->number.')', replyTo: [config('ulbrich.email')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.customer', text: 'emails.customer-text', with: [
            'c' => config('ulbrich'),
            'o' => $this->order,
            'kind' => $this->kind,
            'title' => self::title($this->order, $this->kind),
            'bank' => array_filter(config('ulbrich.bank', [])),
            'personal' => $this->personal,
            // Bitte um Bewertung nur mit Einwilligung (BGH VI ZR 225/17)
            'reviewUrl' => in_array($this->kind, ['zugestellt', 'kurs'], true) && $this->order->review_consent ? Reviews::writeUrl() : null,
            'firstName' => trim(strtok((string) $this->order->name, ' ')) ?: 'du',
            // Fortschritt nur bei Messern/Service (und Anfertigungen ab „In Arbeit“), nicht bei Kurs und Gutschein
            'steps' => in_array($this->order->type, ['kurs', 'gutschein'], true) || ($this->order->type === 'anfrage' && in_array($this->kind, ['eingang', 'bezahlt'], true)) ? [] : [
                ['key' => 'bestellt', 'label' => 'Bestellt'],
                ['key' => 'in_arbeit', 'label' => 'In Arbeit'],
                ['key' => 'versendet', 'label' => $this->order->isPickup() ? 'Abholbereit' : 'Versendet'],
                ['key' => 'zugestellt', 'label' => $this->order->isPickup() ? 'Abgeholt' : 'Zugestellt'],
            ],
            'stepIndex' => ['eingang' => 0, 'bezahlt' => 0, 'in_arbeit' => 1, 'versendet' => 2, 'abholbereit' => 2, 'zugestellt' => 3][$this->kind] ?? 0,
        ]);
    }
}
