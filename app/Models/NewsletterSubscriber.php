<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** Newsletter-Empfänger mit Double-Opt-in. Werbung nur an bestätigte, nicht abgemeldete Adressen. */
class NewsletterSubscriber extends Model
{
    public const CONSENT = 'Ja, ich möchte den Newsletter der Messermanufaktur Ulbrich mit Neuigkeiten, Kursterminen und Angeboten per E-Mail erhalten (freiwillig, jederzeit abbestellbar).';

    protected $fillable = ['email', 'email_key', 'source', 'consent_text', 'consented_at', 'confirm_token', 'confirmed_at', 'unsubscribe_token', 'unsubscribed_at'];

    protected $hidden = ['confirm_token', 'unsubscribe_token'];

    protected function casts(): array
    {
        return ['consented_at' => 'datetime', 'confirmed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    /** Einwilligung erfassen und Bestätigungs-Mail schicken (bestehende bestätigte Adressen bleiben unverändert). */
    public static function request(string $email, string $source): self
    {
        $key = XmasEntry::emailKey($email);
        $sub = static::query()->where('email_key', $key)->first();
        if ($sub && $sub->confirmed_at && ! $sub->unsubscribed_at) {
            return $sub;
        }
        $sub = $sub ?? new static(['email_key' => $key, 'unsubscribe_token' => Str::random(48)]);
        $sub->fill([
            'email' => $email, 'source' => $source, 'consent_text' => self::CONSENT, 'consented_at' => now(),
            'confirm_token' => Str::random(48), 'confirmed_at' => null, 'unsubscribed_at' => null,
        ])->save();
        $sub->sendConfirmation();

        return $sub;
    }

    public function confirmUrl(): string
    {
        return rtrim(url('/'), '/').'/newsletter/bestaetigen/?t='.$this->confirm_token;
    }

    public function unsubscribeUrl(): string
    {
        return rtrim(url('/'), '/').'/newsletter/abmelden/?t='.$this->unsubscribe_token;
    }

    public function sendConfirmation(): void
    {
        $c = config('ulbrich');
        try {
            Mail::raw(implode("\n", [
                'Hallo,', '',
                "bitte bestätige, dass du den Newsletter der {$c['name']} erhalten möchtest:",
                $this->confirmUrl(), '',
                'Erst nach deiner Bestätigung schicken wir dir Newsletter. Wenn du das nicht angefordert hast, ignoriere diese E-Mail einfach – dann passiert nichts.', '',
                $c['owner'], $c['name'],
            ]), fn ($m) => $m->to($this->email)->subject("Bitte bestätige deinen Newsletter der {$c['name']}"));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
