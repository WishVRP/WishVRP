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
        'in_arbeit' => 'Dein Messer ist in Arbeit',
        'versendet' => 'Dein Paket ist unterwegs',
        'abholbereit' => 'Abholbereit in der Manufaktur',
        'zugestellt' => 'Dein Paket ist angekommen',
        'kurs' => 'Danke für deinen Kurstag',
    ];

    public function __construct(public Order $order, public string $kind, public ?string $personal = null) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->kind) {
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
            'title' => self::KINDS[$this->kind],
            'personal' => $this->personal,
            // Bitte um Bewertung nur mit Einwilligung (BGH VI ZR 225/17)
            'reviewUrl' => in_array($this->kind, ['zugestellt', 'kurs'], true) && $this->order->review_consent ? Reviews::writeUrl() : null,
            'firstName' => trim(strtok((string) $this->order->name, ' ')) ?: 'du',
            'steps' => $this->order->type === 'kurs' ? [] : [
                ['key' => 'bestellt', 'label' => 'Bestellt'],
                ['key' => 'in_arbeit', 'label' => 'In Arbeit'],
                ['key' => 'versendet', 'label' => $this->order->isPickup() ? 'Abholbereit' : 'Versendet'],
                ['key' => 'zugestellt', 'label' => $this->order->isPickup() ? 'Abgeholt' : 'Zugestellt'],
            ],
            'stepIndex' => ['in_arbeit' => 1, 'versendet' => 2, 'abholbereit' => 2, 'zugestellt' => 3][$this->kind] ?? 0,
        ]);
    }
}
