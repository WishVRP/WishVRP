<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\XmasEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\Sms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Weihnachtsaktion – „jede Person nur einmal“:
 * 1. Bestätigung vor dem Spiel – kostenlos per persönlichem E-Mail-Link (Standard) oder per SMS-Code
 *    (ulbrich.xmas.verification = sms; dann eine Handynummer = eine Teilnahme).
 * 2. Zusätzlich eine Teilnahme je E-Mail-Adresse (normalisiert), keine Wegwerf-Adressen.
 * 3. Höchstens 3 Anmeldungen je Internetanschluss und Tag (IP nur als Hash, 24 h im Cache).
 * 4. Einlösung nur mit derselben Handynummer und E-Mail, einmal je Haushalt (siehe OrderController).
 * Die Wertung passiert auf dem Server (Mindestspieldauer, Obergrenze, höchstens max_percent).
 */
class XmasController extends Controller
{
    private const CODE_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const MAX_SMS = 3;

    public function register(Request $request): JsonResponse
    {
        abort_unless(XmasEntry::campaignActive(), 410, 'Die Aktion ist beendet.');
        if (! XmasEntry::usesSms()) {
            return $this->registerByEmail($request);
        }
        abort_unless(Sms::canSend(), 503, 'Die Anmeldung ist gerade nicht möglich. Bitte versuch es später noch einmal.');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'conditions' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ], ['conditions.accepted' => 'Bitte bestätige die Teilnahmebedingungen und den Datenschutzhinweis.', 'email.required' => 'Bitte gib deine E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.', 'phone.required' => 'Bitte gib deine Handynummer ein.']);

        if (XmasEntry::isDisposable($data['email'])) {
            return $this->fail('Bitte verwende deine eigene, dauerhafte E-Mail-Adresse – Wegwerf-Adressen sind ausgeschlossen.');
        }
        $this->newsletter($data);
        $phone = XmasEntry::mobileE164($data['phone']);
        if (! $phone) {
            return $this->fail('Bitte gib eine gültige Handynummer an (z. B. 0171 1234567) – an Festnetznummern können wir keine SMS schicken.');
        }

        $emailKey = XmasEntry::emailKey($data['email']);
        $byPhone = XmasEntry::query()->where('phone_key', $phone)->first();
        $byEmail = XmasEntry::query()->where('email_key', $emailKey)->first();
        if ($byPhone?->started_at || $byEmail?->started_at) {
            return $this->fail('Mit dieser Handynummer oder E-Mail-Adresse wurde schon gespielt – die Aktion gibt es nur einmal pro Person.');
        }
        if ($byPhone && $byEmail && ! $byPhone->is($byEmail)) {
            return $this->fail('Handynummer und E-Mail-Adresse gehören schon zu unterschiedlichen Anmeldungen.');
        }
        $entry = $byPhone ?? $byEmail;

        $limiter = 'xmas:'.hash('sha256', $request->ip().config('app.key'));
        if (! $entry && RateLimiter::tooManyAttempts($limiter, 3)) {
            return $this->fail('Von diesem Internetanschluss gab es heute schon mehrere Anmeldungen. Bitte versuch es morgen wieder.', 429);
        }
        if ($entry && $entry->sms_sent_at && $entry->sms_sent_at->gt(now()->subSeconds(60))) {
            return $this->fail('Wir haben dir gerade erst einen Code geschickt. Bitte warte eine Minute, bevor du einen neuen anforderst.', 429);
        }
        if ($entry && $entry->sms_count >= self::MAX_SMS) {
            return $this->fail('Für diese Anmeldung wurden schon zu viele Codes angefordert.', 429);
        }

        if (! $entry) {
            RateLimiter::hit($limiter, 86400);
            $entry = XmasEntry::create(['email' => $data['email'], 'email_key' => $emailKey, 'token' => Str::random(48), 'verify_token' => Str::random(48)]);
        }
        $code = (string) random_int(100000, 999999);
        $entry->update(['email' => $data['email'], 'email_key' => $emailKey, 'phone' => $data['phone'], 'phone_key' => $phone,
            'sms_code_hash' => Hash::make($code), 'sms_sent_at' => now(), 'sms_count' => $entry->sms_count + 1, 'sms_attempts' => 0]);

        try {
            Sms::send($phone, "Dein Code für die Schneeballschlacht der Messermanufaktur Ulbrich: {$code} (10 Minuten gültig)");
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Die SMS konnte nicht verschickt werden. Bitte prüfe die Nummer oder versuch es später noch einmal.', 502);
        }

        return response()->json(array_filter([
            'status' => 'sms',
            'ref' => $entry->verify_token,
            'message' => 'Wir haben dir einen 6-stelligen Code per SMS an '.$this->mask($phone).' geschickt.',
            // Nur in der Entwicklung (Treiber „log“): Code anzeigen, weil keine SMS verschickt wird.
            'dev_code' => config('services.sms.driver') === 'log' && ! app()->isProduction() ? $code : null,
        ]));
    }

    /** Kostenlose Variante: persönlicher Spiel-Link per E-Mail (Link funktioniert genau einmal). */
    private function registerByEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'conditions' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ], ['conditions.accepted' => 'Bitte bestätige die Teilnahmebedingungen und den Datenschutzhinweis.', 'email.required' => 'Bitte gib deine E-Mail-Adresse ein.', 'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.']);

        if (XmasEntry::isDisposable($data['email'])) {
            return $this->fail('Bitte verwende deine eigene, dauerhafte E-Mail-Adresse – Wegwerf-Adressen sind ausgeschlossen.');
        }
        $this->newsletter($data);
        $key = XmasEntry::emailKey($data['email']);
        $entry = XmasEntry::query()->where('email_key', $key)->first();
        if ($entry?->started_at) {
            return $this->fail('Mit dieser E-Mail-Adresse wurde schon gespielt – die Aktion gibt es nur einmal pro Person.');
        }
        $limiter = 'xmas:'.hash('sha256', $request->ip().config('app.key'));
        if (! $entry && RateLimiter::tooManyAttempts($limiter, 3)) {
            return $this->fail('Von diesem Internetanschluss gab es heute schon mehrere Anmeldungen. Bitte versuch es morgen wieder.', 429);
        }
        if ($entry && $entry->sms_sent_at && $entry->sms_sent_at->gt(now()->subSeconds(60))) {
            return $this->fail('Wir haben dir gerade erst einen Link geschickt. Bitte warte eine Minute.', 429);
        }
        if ($entry && $entry->sms_count >= self::MAX_SMS) {
            return $this->fail('Für diese Adresse wurden schon zu viele Links angefordert. Schau in deinem Postfach (auch im Spam-Ordner) nach.', 429);
        }
        if (! $entry) {
            RateLimiter::hit($limiter, 86400);
            $entry = XmasEntry::create(['email' => $data['email'], 'email_key' => $key, 'token' => Str::random(48), 'verify_token' => Str::random(48)]);
        }
        $entry->update(['sms_sent_at' => now(), 'sms_count' => $entry->sms_count + 1]);   // Zähler für versendete Links

        $link = rtrim(url('/'), '/').'/weihnachtsaktion/?t='.$entry->verify_token;
        $this->send($entry->email, 'Dein persönlicher Link zur Schneeballschlacht', [
            'Hallo,', '',
            'hier ist dein persönlicher Link zur Weihnachtsaktion der '.config('ulbrich.name').':',
            $link, '',
            'Der Link funktioniert genau einmal: Du hast einen Versuch. Mit dem Start zählt deine Teilnahme – such dir also einen ruhigen Moment.', '',
            config('ulbrich.owner'),
        ]);

        return response()->json(array_filter([
            'status' => 'sent',
            'message' => 'Fast geschafft! Wir haben dir deinen persönlichen Spiel-Link an '.$entry->email.' geschickt. Öffne ihn, um zu spielen.',
            'dev_link' => app()->isProduction() ? null : $link,
        ]));
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate(['ref' => ['required', 'string', 'size:48'], 'code' => ['required', 'string', 'max:10']]);
        $entry = XmasEntry::query()->where('verify_token', $data['ref'])->first();
        abort_unless($entry && $entry->sms_code_hash, 404, 'Anmeldung nicht gefunden.');

        if ($entry->started_at) {
            return $this->fail('Mit dieser Handynummer wurde schon gespielt.');
        }
        if ($entry->sms_attempts >= self::MAX_ATTEMPTS) {
            return $this->fail('Zu viele falsche Eingaben. Fordere einen neuen Code an.', 429);
        }
        if (! $entry->sms_sent_at || $entry->sms_sent_at->lt(now()->subMinutes(self::CODE_MINUTES))) {
            return $this->fail('Der Code ist abgelaufen. Fordere einen neuen an.');
        }
        if (! Hash::check(preg_replace('/\D/', '', $data['code']), $entry->sms_code_hash)) {
            $entry->increment('sms_attempts');

            return $this->fail('Der Code stimmt nicht. Noch '.(self::MAX_ATTEMPTS - $entry->sms_attempts).' Versuche.');
        }

        $entry->update(['verified_at' => now(), 'sms_code_hash' => null]);

        return response()->json(['status' => 'verified']);
    }

    public function start(Request $request): JsonResponse
    {
        abort_unless(XmasEntry::campaignActive(), 410, 'Die Aktion ist beendet.');
        $data = $request->validate(['ref' => ['required', 'string', 'size:48']]);
        $entry = XmasEntry::query()->where('verify_token', $data['ref'])->first();
        abort_unless($entry, 404, 'Anmeldung nicht gefunden.');
        abort_unless($entry->verified_at && (! XmasEntry::usesSms() || $entry->phone_key), 403, XmasEntry::usesSms() ? 'Bitte bestätige zuerst deine Handynummer.' : 'Bitte öffne den persönlichen Link aus deiner E-Mail.');

        if ($entry->started_at) {
            if (! $entry->finished_at) {
                $entry->update(['finished_at' => now(), 'score' => 0, 'percent' => 0]);   // abgebrochen zählt
            }

            return response()->json(['status' => 'played', 'score' => $entry->score, 'percent' => $entry->percent, 'code' => $entry->code,
                'message' => 'Du hast schon gespielt – die Aktion gibt es nur einmal pro Person.']);
        }

        $entry->update(['started_at' => now()]);

        return response()->json(['status' => 'ok', 'token' => $entry->token, 'seconds' => (int) config('ulbrich.xmas.seconds')]);
    }

    /** Freiwilliges Newsletter-Häkchen → Double-Opt-in-Mail (unabhängig vom Spiel). */
    private function newsletter(array $data): void
    {
        if (! empty($data['newsletter'])) {
            NewsletterSubscriber::request($data['email'], 'weihnachtsaktion');
        }
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    private function mask(string $phone): string
    {
        return substr($phone, 0, 5).str_repeat('•', max(0, strlen($phone) - 8)).substr($phone, -3);
    }

    public function finish(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'size:48'], 'score' => ['required', 'integer', 'min:0', 'max:1000']]);
        $entry = XmasEntry::query()->where('token', $data['token'])->whereNotNull('started_at')->first();
        abort_unless($entry, 404);

        if ($entry->finished_at) {
            return response()->json(['status' => 'played', 'score' => $entry->score, 'percent' => $entry->percent, 'code' => $entry->code]);
        }

        $x = config('ulbrich.xmas');
        $elapsed = $entry->started_at->diffInSeconds(now(), true);
        $score = $elapsed < $x['seconds'] - 3 ? 0 : min((int) $data['score'], (int) $x['max_score']);
        $percent = XmasEntry::percentFor($score);

        $entry->update(['finished_at' => now(), 'score' => $score, 'percent' => $percent, 'code' => $percent > 0 ? XmasEntry::newCode() : null]);

        if ($entry->code) {
            $until = XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY');
            $this->send($entry->email, "Dein Weihnachtsrabatt: {$entry->percent} % ({$entry->code})", [
                'Ho ho ho!',
                '',
                "Du hast den Weihnachtsmann {$entry->score}-mal getroffen und {$entry->percent} % Rabatt gewonnen.",
                "Dein persönlicher Code: {$entry->code}",
                "Einlösbar einmalig bis {$until} bei einer Bestellung oder Kursbuchung über die Website – nur zusammen mit dieser E-Mail-Adresse ({$entry->email})".($entry->phone_key ? ' und deiner bestätigten Handynummer' : '').'. Pro Person bzw. Haushalt gilt ein Weihnachtsrabatt.',
                '',
                'Frohe Weihnachten wünscht '.config('ulbrich.owner'),
                config('ulbrich.name'),
            ]);
        }

        return response()->json(['status' => 'done', 'score' => $score, 'percent' => $percent, 'code' => $entry->code,
            'valid_until' => XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY')]);
    }

    /** @param array<int, string> $lines */
    private function send(string $to, string $subject, array $lines): void
    {
        try {
            Mail::raw(implode("\n", $lines), fn ($m) => $m->to($to)->subject($subject));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
