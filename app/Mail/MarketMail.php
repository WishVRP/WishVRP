<?php

namespace App\Mail;

use App\Models\MarketApplication;
use App\Support\Market;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Mails an Aussteller: eingang | angenommen (mit Zahlungsdaten) | abgelehnt | bezahlt */
class MarketMail extends Mailable
{
    use Queueable;

    public const KINDS = [
        'eingang' => 'Deine Bewerbung ist angekommen',
        'angenommen' => 'Du bist dabei – bitte Standgebühr überweisen',
        'abgelehnt' => 'Deine Bewerbung für den Markt',
        'bezahlt' => 'Dein Stand ist fest gebucht',
    ];

    public function __construct(public MarketApplication $app, public string $kind, public ?string $personal = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: self::KINDS[$this->kind].' – '.config('ulbrich.market.name').' ('.$this->app->code.')', replyTo: [config('ulbrich.email')]);
    }

    public function content(): Content
    {
        $stand = Market::stands()[$this->app->stand] ?? null;

        return new Content(view: 'emails.market', text: 'emails.market-text', with: [
            'c' => config('ulbrich'), 'm' => config('ulbrich.market'), 'a' => $this->app, 'kind' => $this->kind,
            'title' => self::KINDS[$this->kind], 'personal' => $this->personal, 'stand' => $stand,
            'date' => Market::settings()['date'], 'bank' => array_filter(config('ulbrich.bank', [])),
            'firstName' => trim(strtok((string) $this->app->name, ' ')) ?: 'du',
        ]);
    }

    /** Bei Zusage: Standmietvertrag, Marktordnung und Standplan als PDF anhängen. */
    public function attachments(): array
    {
        if ($this->kind !== 'angenommen') {
            return [];
        }

        return collect(config('ulbrich.market.downloads', []))
            ->filter(fn ($d) => is_file(public_path(ltrim($d['file'], '/'))))
            ->map(fn ($d) => Attachment::fromPath(public_path(ltrim($d['file'], '/')))->as(basename($d['file']))->withMime('application/pdf'))
            ->values()->all();
    }
}
