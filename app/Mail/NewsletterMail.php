<?php

namespace App\Mail;

use App\Models\DiscountCode;
use App\Models\NewsletterIssue;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

/**
 * Newsletter-Mail: Impressum, Grund des Empfangs und Abmeldelink in jeder Mail,
 * dazu List-Unsubscribe (RFC 2369) und One-Click-Abmeldung (RFC 8058).
 */
class NewsletterMail extends Mailable
{
    use Queueable;

    public function __construct(
        public NewsletterIssue $issue,
        public NewsletterSubscriber $subscriber,
        public ?DiscountCode $code = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->issue->subject);
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscriber->oneClickUrl().'>, <mailto:'.config('ulbrich.email').'?subject=Newsletter%20abmelden>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            text: 'emails.newsletter-text',
            with: [
                'c' => config('ulbrich'),
                'b' => $this->issue->blocks ?? [],
                'bodyHtml' => $this->issue->body ? Str::markdown($this->issue->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) : null,
                'unsubscribe' => $this->subscriber->unsubscribeUrl(),
                'since' => $this->subscriber->confirmed_at?->locale('de')->isoFormat('D. MMMM YYYY'),
            ],
        );
    }
}
