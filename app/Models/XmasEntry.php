<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/** Teilnahme an der Weihnachtsaktion – genau eine je (normalisierter) E-Mail-Adresse. */
class XmasEntry extends Model
{
    protected $fillable = ['email', 'email_key', 'phone', 'phone_key', 'token', 'verify_token', 'verified_at', 'sms_code_hash', 'sms_sent_at', 'sms_count', 'sms_attempts', 'started_at', 'finished_at', 'score', 'percent', 'code', 'redeemed_at', 'order_id'];

    protected $hidden = ['token', 'verify_token', 'sms_code_hash'];

    protected function casts(): array
    {
        return ['sms_sent_at' => 'datetime', 'verified_at' => 'datetime', 'started_at' => 'datetime', 'finished_at' => 'datetime', 'redeemed_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Bekannte Wegwerf-Adressen (keine Teilnahme). */
    public const DISPOSABLE = [
        '10minutemail.com', '10minutemail.de', 'guerrillamail.com', 'guerrillamail.de', 'sharklasers.com', 'mailinator.com',
        'yopmail.com', 'yopmail.fr', 'temp-mail.org', 'tempmail.com', 'tempmail.de', 'trashmail.com', 'trashmail.de',
        'wegwerfemail.de', 'wegwerfmail.de', 'einrot.com', 'spambog.com', 'spambog.de', 'throwawaymail.com', 'getnada.com',
        'dispostable.com', 'maildrop.cc', 'mohmal.com', 'emailondeck.com', 'fakeinbox.com', 'mintemail.com', 'byom.de',
        'trash-mail.com', 'discard.email', 'mailnesia.com', 'tempr.email', 'emailfake.com', 'moakt.com', 'muellmail.com',
    ];

    public static function isDisposable(string $email): bool
    {
        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));

        return $domain === '' || in_array($domain, self::DISPOSABLE, true);
    }

    /**
     * Handynummer in E.164 (+49…). Deutsche Nummern müssen Mobilnummern sein (015x/016x/017x);
     * ausländische Nummern mit Ländervorwahl werden akzeptiert. null = ungültig.
     */
    public static function mobileE164(?string $phone): ?string
    {
        $raw = trim((string) $phone);
        $d = preg_replace('/\D/', '', $raw);
        if (str_starts_with($raw, '+')) {
            $e = '+'.$d;
        } elseif (str_starts_with($d, '00')) {
            $e = '+'.substr($d, 2);
        } elseif (str_starts_with($d, '0')) {
            $e = '+49'.substr($d, 1);
        } else {
            return null;
        }
        if (str_starts_with($e, '+49')) {
            return preg_match('/^\+491[567]\d{7,10}$/', $e) ? $e : null;
        }

        return preg_match('/^\+[1-9]\d{7,14}$/', $e) ? $e : null;
    }

    /** Telefonnummer vergleichbar machen (nur Ziffern, 0049/+49 → 0). */
    public static function phoneKey(?string $phone): ?string
    {
        $d = preg_replace('/\D/', '', (string) $phone);
        $d = preg_replace('/^(0049|49)/', '0', $d);

        return strlen($d) >= 6 ? $d : null;
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
