<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Kurs-Gutschein: für einen Kurs und eine Personenzahl, online bezahlt, bei der Buchung einlösbar. */
class Voucher extends Model
{
    public const STATUSES = ['offen' => 'Zahlung offen', 'bezahlt' => 'Gültig', 'eingeloest' => 'Eingelöst', 'storniert' => 'Storniert'];

    protected $fillable = ['code', 'course_id', 'persons', 'value', 'status', 'recipient', 'order_id', 'redeemed_order_id', 'redeemed_at', 'valid_until'];

    protected function casts(): array
    {
        return ['value' => 'decimal:2', 'valid_until' => 'date', 'redeemed_at' => 'datetime'];
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
