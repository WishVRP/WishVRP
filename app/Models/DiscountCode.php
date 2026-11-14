<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Rabattcode: aus dem Newsletter (persönlich, einmalig) oder in der Verwaltung angelegt
 * (Prozent oder Euro, einmal oder mehrfach nutzbar, optional nur für Shop/Kurse/Gutscheine oder eine E-Mail-Adresse).
 */
class DiscountCode extends Model
{
    public const TYPES = ['percent' => 'Prozent', 'amount' => 'Euro-Betrag'];

    /** Bereich => [Bezeichnung, erlaubte Vorgangsarten] */
    public const SCOPES = [
        'alles' => ['Alles (Shop, Kurse, Gutscheine)', ['messer', 'kurs', 'gutschein']],
        'shop' => ['Nur Shop (Messer)', ['messer']],
        'kurse' => ['Nur Kursbuchungen', ['kurs']],
        'gutscheine' => ['Nur Kurs-Gutscheine', ['gutschein']],
    ];

    protected $fillable = ['code', 'type', 'percent', 'amount', 'min_order', 'scope', 'max_uses', 'uses', 'email', 'email_key', 'source', 'label',
        'newsletter_issue_id', 'valid_from', 'valid_until', 'redeemed_at', 'sent_at', 'order_id'];

    protected function casts(): array
    {
        return ['valid_from' => 'datetime', 'valid_until' => 'datetime', 'redeemed_at' => 'datetime', 'sent_at' => 'datetime',
            'amount' => 'decimal:2', 'min_order' => 'decimal:2', 'max_uses' => 'integer', 'uses' => 'integer', 'percent' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (DiscountCode $c) {
            $c->code = strtoupper(trim((string) $c->code));
            $c->source ??= 'verwaltung';
            $c->email_key = filled($c->email) ? XmasEntry::emailKey($c->email) : null;
            if ($c->type === 'amount') {
                $c->percent = null;
            } else {
                $c->type = 'percent';
                $c->amount = null;
            }
        });
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(NewsletterIssue::class, 'newsletter_issue_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function newCode(string $prefix = 'NEWS'): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $prefix) ?: 'CODE');
        do {
            $code = $prefix.'-'.strtoupper(Str::of(Str::random(14))->replaceMatches('/[^A-HJ-NP-Z2-9]/i', '')->substr(0, 6));
        } while (strlen($code) < strlen($prefix) + 7 || static::query()->where('code', $code)->exists() || XmasEntry::query()->where('code', $code)->exists());

        return $code;
    }

    /** Gültig, nicht aufgebraucht, im Zeitraum. */
    public static function redeemable(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }

        return static::query()->where('code', $code)
            ->where(fn ($q) => $q->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses'))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->first();
    }

    /** Gilt der Code für diese Vorgangsart (messer, kurs, gutschein)? */
    public function appliesTo(string $orderType): bool
    {
        return in_array($orderType, self::SCOPES[$this->scope ?? 'alles'][1] ?? [], true);
    }

    /** Rabatt in Euro auf einen Warenwert (ohne Versand). */
    public function discountFor(float $amount): float
    {
        $off = $this->type === 'amount' ? (float) $this->amount : $amount * (int) $this->percent / 100;

        return round(max(0, min($amount, $off)), 2);
    }

    /** „10 %“ oder „15,00 €“. */
    public function valueLabel(): string
    {
        return $this->type === 'amount' ? number_format((float) $this->amount, 2, ',', '.').' €' : (int) $this->percent.' %';
    }

    public function scopeLabel(): string
    {
        return self::SCOPES[$this->scope ?? 'alles'][0] ?? 'Alles';
    }

    /** Nach einer Bestellung: Nutzung zählen; aufgebraucht = eingelöst. */
    public function redeem(Order $order): void
    {
        $uses = $this->uses + 1;
        $this->forceFill([
            'uses' => $uses,
            'order_id' => $order->id,
            'redeemed_at' => $this->max_uses !== null && $uses >= $this->max_uses ? now() : $this->redeemed_at,
        ])->save();
    }
}
