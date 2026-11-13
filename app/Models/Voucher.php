<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Kurs-Gutschein: für einen Kurs und eine Personenzahl, online bezahlt, bei der Buchung einlösbar. */
class Voucher extends Model
{
    public const STATUSES = ['offen' => 'Zahlung offen', 'bezahlt' => 'Gültig', 'eingeloest' => 'Eingelöst', 'storniert' => 'Storniert'];

    protected $fillable = ['code', 'course_id', 'persons', 'value', 'status', 'recipient', 'order_id', 'redeemed_order_id', 'redeemed_at', 'valid_until', 'recipient_email', 'send_on', 'gift_message', 'sent_to_recipient_at'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'valid_until' => 'date', 'redeemed_at' => 'datetime', 'send_on' => 'date', 'sent_to_recipient_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function redeemedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'redeemed_order_id');
    }

    /** An die beschenkte Person schicken, wenn gewünscht und fällig (Wunschtermin erreicht oder keiner gesetzt). */
    public function sendToRecipientIfDue(): bool
    {
        if (! $this->recipient_email || $this->sent_to_recipient_at || $this->status !== 'bezahlt'
            || ($this->send_on && $this->send_on->format('Y-m-d') > now('Europe/Berlin')->format('Y-m-d'))) {
            return false;
        }
        try {
            \Illuminate\Support\Facades\Mail::to($this->recipient_email, $this->recipient)->send(new \App\Mail\VoucherMail($this, 'recipient'));
            $this->forceFill(['sent_to_recipient_at' => now()])->save();
            if ($this->order_id) {
                Note::query()->create(['order_id' => $this->order_id, 'body' => "Gutschein {$this->code} an {$this->recipient_email} geschickt (Wunschtermin).", 'done_at' => now()]);
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public static function newCode(): string
    {
        do {
            $code = 'GS-'.strtoupper(Str::of(Str::random(16))->replaceMatches('/[^A-HJ-NP-Z2-9]/i', '')->substr(0, 8));
        } while (strlen($code) < 11 || static::query()->where('code', $code)->exists());

        return $code;
    }

    /** Gesetzliche Verjährung: 3 Jahre zum Jahresende (Vorschlag, anwaltlich prüfen). */
    public static function defaultValidUntil(): \Illuminate\Support\Carbon
    {
        return now()->addYears((int) config('ulbrich.voucher_years', 3))->endOfYear();
    }

    public static function usable(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : static::query()->where('code', $code)->where('status', 'bezahlt')->whereDate('valid_until', '>=', now())->first();
    }
}
