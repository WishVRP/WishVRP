<?php

namespace App\Http\Controllers;

use App\Models\XmasEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Weihnachtsaktion – „jede Person nur einmal“ in vier Stufen:
 * 1. Anmeldung mit E-Mail, Spiel nur über den persönlichen Link aus der E-Mail (keine Wegwerf-Adressen).
 * 2. Eine Teilnahme je (normalisierter) E-Mail; ein gestartetes Spiel zählt.
 * 3. Höchstens 3 Anmeldungen je Internetanschluss und Tag (IP nur als Hash, 24 h im Cache).
 * 4. Einlösung nur mit derselben E-Mail und nur einmal je Person/Haushalt (Telefon bzw. Name + PLZ) – siehe OrderController.
 * Die Wertung passiert auf dem Server (Mindestspieldauer, Obergrenze, höchstens max_percent).
 */
class XmasController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        abort_unless(XmasEntry::campaignActive(), 410, 'Die Aktion ist beendet.');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'conditions' => ['accepted'],
        ], ['conditions.accepted' => 'Bitte bestätige die Teilnahmebedingungen und den Datenschutzhinweis.', 'email.required' => 'Bitte gib deine E-Mail-Adresse ein.', 'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.']);

        if (XmasEntry::isDisposable($data['email'])) {
            return response()->json(['message' => 'Bitte verwende deine eigene, dauerhafte E-Mail-Adresse – Wegwerf-Adressen sind ausgeschlossen.'], 422);
        }

        $key = XmasEntry::emailKey($data['email']);
        $entry = XmasEntry::query()->where('email_key', $key)->first();
        if ($entry?->started_at) {
            return response()->json(['message' => 'Mit dieser E-Mail-Adresse wurde schon gespielt – die Aktion gibt es nur einmal pro Person.'], 422);
        }

        $limiter = 'xmas:'.hash('sha256', $request->ip().config('app.key'));
        if (! $entry && RateLimiter::tooManyAttempts($limiter, 3)) {
            return response()->json(['message' => 'Von diesem Internetanschluss gab es heute schon mehrere Anmeldungen. Bitte versuch es morgen wieder.'], 429);
        }

        if (! $entry) {
            RateLimiter::hit($limiter, 86400);
            $entry = XmasEntry::create(['email' => $data['email'], 'email_key' => $key, 'token' => Str::random(48), 'verify_token' => Str::random(48)]);
        }

        $link = rtrim(url('/'), '/').'/weihnachtsaktion/?t='.$entry->verify_token;
        $this->send($entry->email, 'Dein persönlicher Link zur Schneeballschlacht', [
            'Hallo,',
            '',
            'hier ist dein persönlicher Link zur Weihnachtsaktion der '.config('ulbrich.name').':',
            $link,
            '',
            'Du hast genau einen Versuch. Mit dem Start zählt deine Teilnahme – such dir also einen ruhigen Moment.',
            '',
            config('ulbrich.owner'),
        ]);

        return response()->json(array_filter([
            'status' => 'sent',
            'message' => 'Fast geschafft! Wir haben dir einen persönlichen Spiel-Link an '.$entry->email.' geschickt. Öffne ihn, um zu spielen.',
            // Nur in der Entwicklungsumgebung: Link direkt anzeigen (Mails landen dort im Log).
            'dev_link' => app()->isProduction() ? null : $link,
        ]));
    }

    public function start(Request $request): JsonResponse
    {
        abort_unless(XmasEntry::campaignActive(), 410, 'Die Aktion ist beendet.');
        $data = $request->validate(['t' => ['required', 'string', 'size:48']]);
        $entry = XmasEntry::query()->where('verify_token', $data['t'])->first();
        abort_unless($entry, 404, 'Dieser Link ist ungültig.');

        if ($entry->started_at) {
            if (! $entry->finished_at) {
                $entry->update(['finished_at' => now(), 'score' => 0, 'percent' => 0]);   // abgebrochen zählt
            }

            return response()->json(['status' => 'played', 'score' => $entry->score, 'percent' => $entry->percent, 'code' => $entry->code,
                'message' => 'Du hast schon gespielt – die Aktion gibt es nur einmal pro Person.']);
        }

        $entry->update(['verified_at' => $entry->verified_at ?? now(), 'started_at' => now()]);

        return response()->json(['status' => 'ok', 'token' => $entry->token, 'seconds' => (int) config('ulbrich.xmas.seconds')]);
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
                "Einlösbar einmalig bis {$until} bei einer Bestellung oder Kursbuchung über die Website – nur zusammen mit dieser E-Mail-Adresse ({$entry->email}). Pro Person bzw. Haushalt gilt ein Weihnachtsrabatt.",
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
