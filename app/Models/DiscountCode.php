<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Persönlicher Rabattcode aus dem Newsletter: einmalig, nur mit der Empfänger-E-Mail, befristet. */
class DiscountCode extends Model
{
    protected $fillable = ['code', 'percent', 'email', 'email_key', 'source', 'newsletter_issue_id', 'valid_until', 'redeemed_at', 'order_id'];

    protected function casts(): array
    {
        return ['valid_until' => 'datetime', 'redeemed_at' => 'datetime'];
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
        do {
            $code = $prefix.'-'.strtoupper(Str::of(Str::random(14))->replaceMatches('/[^A-HJ-NP-Z2-9]/i', '')->substr(0, 6));
        } while (strlen($code) < strlen($prefix) + 7 || static::query()->where('code', $code)->exists() || XmasEntry::query()->where('code', $code)->exists());

        return $code;
    }

    public static function redeemable(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? null : static::query()->where('code', $code)->whereNull('redeemed_at')->where('valid_until', '>=', now())->first();
    }
}
