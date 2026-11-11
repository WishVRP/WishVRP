<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** Teilnahme an der Weihnachtsaktion – genau eine je (normalisierter) E-Mail-Adresse. */
class XmasEntry extends Model
{
    protected $fillable = ['email', 'email_key', 'token', 'started_at', 'finished_at', 'score', 'percent', 'code', 'redeemed_at', 'order_id'];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime', 'redeemed_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** klein, ohne Leerzeichen, ohne +Zusatz (anna+1@… = anna@…), bei Gmail ohne Punkte. */
    public static function emailKey(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', strtolower(trim($email)), 2), 2, '');
        $local = explode('+', $local)[0];
        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', $local);
            $domain = 'gmail.com';
        }

        return $local.'@'.$domain;
    }

    /** Rabatt in Prozent für eine Trefferzahl (höchstens max_percent). */
    public static function percentFor(int $score): int
    {
        $x = config('ulbrich.xmas');
        $percent = 0;
        foreach ($x['tiers'] as $tier) {
            if ($score >= $tier['min']) {
                $percent = $tier['percent'];
            }
        }

        return min($percent, (int) $x['max_percent']);
    }

    public static function newCode(): string
    {
        do {
            $code = 'XMAS-'.strtoupper(Str::of(Str::random(12))->replaceMatches('/[^A-HJ-NP-Z2-9]/i', '')->substr(0, 6));
        } while (strlen($code) < 11 || static::query()->where('code', $code)->exists());

        return $code;
    }

    public static function validUntil(): Carbon
    {
        return Carbon::parse(config('ulbrich.xmas.valid_until'))->endOfDay();
    }

    /** Gültiger, noch nicht eingelöster Code? */
    public static function redeemable(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || now()->gt(self::validUntil())) {
            return null;
        }

        return static::query()->where('code', $code)->whereNull('redeemed_at')->first();
    }

    public static function campaignActive(): bool
    {
        $x = config('ulbrich.xmas');

        return $x['enabled'] && now()->lte(Carbon::parse($x['ends_on'])->endOfDay());
    }
}
