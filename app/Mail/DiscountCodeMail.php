<?php

namespace App\Mail;

use App\Models\DiscountCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Rabattcode aus der Verwaltung an eine Person schicken (Gewinnspiel, Aktion, Kulanz). */
class DiscountCodeMail extends Mailable
{
    use Queueable;

    public function __construct(public DiscountCode $discount, public ?string $firstName = null, public ?string $note = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dein Rabattcode über '.$this->discount->valueLabel().' – '.config('ulbrich.name'), replyTo: [config('ulbrich.email')]);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.discount-text', with: ['c' => config('ulbrich'), 'd' => $this->discount, 'firstName' => $this->firstName, 'note' => $this->note]);
    }
}
