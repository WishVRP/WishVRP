<?php

namespace App\Mail;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Widerruf: Eingangsbestätigung (sofort, dauerhafter Datenträger) bzw. Entscheidung (angenommen/abgelehnt). */
class WithdrawalMail extends Mailable
{
    use Queueable;

    public function __construct(public Withdrawal $w, public string $kind = 'eingang') {}

    public function envelope(): Envelope
    {
        $subject = match ($this->kind) {
            'angenommen' => 'Dein Widerruf ist bestätigt',
            'abgelehnt' => 'Zu deinem Widerruf',
            default => 'Eingangsbestätigung deines Widerrufs',
        };

        return new Envelope(subject: $subject.($this->w->order_number ? ' ('.$this->w->order_number.')' : ''), replyTo: [config('ulbrich.email')]);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.withdrawal-text', with: ['c' => config('ulbrich'), 'w' => $this->w, 'kind' => $this->kind]);
    }
}
