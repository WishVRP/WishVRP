<?php

namespace App\Mail;

use App\Models\Voucher;
use App\Support\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Gutschein als PDF zum Ausdrucken – an die Käuferin/den Käufer oder (zum Wunschtermin) an die beschenkte Person. */
class VoucherMail extends Mailable
{
    use Queueable;

    public function __construct(public Voucher $voucher, public string $audience = 'buyer') {}

    public function envelope(): Envelope
    {
        $buyer = $this->voucher->order?->name;

        return new Envelope(
            subject: $this->audience === 'recipient' ? 'Ein Geschenk für dich'.($buyer ? ' von '.$buyer : '').' – Gutschein der Messermanufaktur Ulbrich' : 'Dein Gutschein '.$this->voucher->code.' zum Ausdrucken',
            replyTo: [config('ulbrich.email')],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'emails.voucher-text', with: ['c' => config('ulbrich'), 'v' => $this->voucher, 'audience' => $this->audience]);
    }

    public function attachments(): array
    {
        return [Attachment::fromData(fn () => self::pdf($this->voucher), 'Gutschein-'.$this->voucher->code.'.pdf')->withMime('application/pdf')];
    }

    public static function pdf(Voucher $v): string
    {
        return Pdf::render('pdf.voucher', ['v' => $v->loadMissing('course')], 'a4', 'landscape');
    }
}
