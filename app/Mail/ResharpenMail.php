<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Bestätigung „Messer zum Nachschärfen“: Vorgangsnummer, Einsendeadresse bzw. Abgabe, Ablauf. */
class ResharpenMail extends Mailable
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nachschärfen: so schickst du dein Messer ein ('.$this->order->number.')', replyTo: [config('ulbrich.email')]);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.resharpen-text', with: ['c' => config('ulbrich'), 'o' => $this->order]);
    }
}
