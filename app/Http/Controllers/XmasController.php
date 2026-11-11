<?php

namespace App\Http\Controllers;

use App\Models\XmasEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Weihnachtsaktion: Teilnahme starten (E-Mail, einmalig) und Ergebnis werten.
 * Die Wertung passiert auf dem Server: Mindestspieldauer, Plausibilitätsgrenze, Rabatt höchstens max_percent.
 */
class XmasController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        abort_unless(XmasEntry::campaignActive(), 410, 'Die Aktion ist beendet.');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'conditions' => ['accepted'],
        ], ['conditions.accepted' => 'Bitte bestätige die Teilnahmebedingungen und den Datenschutzhinweis.', 'email.required' => 'Bitte gib deine E-Mail-Adresse ein.', 'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.']);

        $key = XmasEntry::emailKey($data['email']);
        if ($entry = XmasEntry::query()->where('email_key', $key)->first()) {
            // Jede Person nur einmal: auch ein abgebrochenes Spiel zählt als Teilnahme.
            if (! $entry->finished_at) {
                $entry->update(['finished_at' => now(), 'score' => 0, 'percent' => 0]);
            }

            return response()->json(['status' => 'played', 'score' => $entry->score, 'percent' => $entry->percent, 'code' => $entry->code,
                'message' => 'Mit dieser E-Mail-Adresse wurde schon gespielt – die Aktion gibt es nur einmal pro Person.']);
        }

        $entry = XmasEntry::create(['email' => $data['email'], 'email_key' => $key, 'token' => Str::random(48), 'started_at' => now()]);

        return response()->json(['status' => 'ok', 'token' => $entry->token, 'seconds' => (int) config('ulbrich.xmas.seconds')]);
    }

    public function finish(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'size:48'], 'score' => ['required', 'integer', 'min:0', 'max:1000']]);
        $entry = XmasEntry::query()->where('token', $data['token'])->first();
        abort_unless($entry, 404);

        if ($entry->finished_at) {
            return response()->json(['status' => 'played', 'score' => $entry->score, 'percent' => $entry->percent, 'code' => $entry->code]);
        }

        $x = config('ulbrich.xmas');
        $elapsed = $entry->started_at->diffInSeconds(now(), true);
        // Zu früh beendet oder unrealistisch viele Treffer → keine Wertung über die Grenze hinaus.
        $score = $elapsed < $x['seconds'] - 3 ? 0 : min((int) $data['score'], (int) $x['max_score']);
        $percent = XmasEntry::percentFor($score);

        $entry->update([
            'finished_at' => now(),
            'score' => $score,
            'percent' => $percent,
            'code' => $percent > 0 ? XmasEntry::newCode() : null,
        ]);

        if ($entry->code) {
            $this->mail($entry);
        }

        return response()->json(['status' => 'done', 'score' => $score, 'percent' => $percent, 'code' => $entry->code,
            'valid_until' => XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY')]);
    }

    private function mail(XmasEntry $entry): void
    {
        $c = config('ulbrich');
        try {
            Mail::raw(implode("\n", [
                'Ho ho ho!',
                '',
                "Du hast den Weihnachtsmann {$entry->score}-mal getroffen und {$entry->percent} % Rabatt gewonnen.",
                "Dein persönlicher Code: {$entry->code}",
                'Einlösbar einmalig bis '.XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY').' bei einer Bestellung oder Kursbuchung über die Website – einfach im Formular ins Feld „Rabattcode“ eintragen.',
                '',
                "Frohe Weihnachten wünscht {$c['owner']}",
                $c['name'],
            ]), fn ($m) => $m->to($entry->email)->subject("Dein Weihnachtsrabatt: {$entry->percent} % ({$entry->code})"));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
