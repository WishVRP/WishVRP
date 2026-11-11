<?php

namespace App\Http\Controllers;

use App\Mail\MarketMail;
use App\Models\MarketApplication;
use App\Support\Market;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/** Kunst- & Handwerkermarkt: Standplan (Belegung), Reservierung für 15 Minuten, Bewerbung mit Fotos. */
class MarketController extends Controller
{
    private function token(Request $request): string
    {
        // eigener Zufallsschlüssel je Besucher (bleibt auch bei neuer Sitzungs-ID erhalten)
        if (! $request->session()->has('market_token')) {
            $request->session()->put('market_token', \Illuminate\Support\Str::random(40));
        }

        return hash('sha256', $request->session()->get('market_token').config('app.key'));
    }

    public function stands(Request $request): JsonResponse
    {
        return response()->json(['stands' => Market::statusMap($this->token($request)), 'open' => Market::settings()['open']])
            ->header('Cache-Control', 'no-store');
    }

    public function reserve(Request $request): JsonResponse
    {
        abort_unless(Market::settings()['open'], 423, 'Bewerbungen sind gerade geschlossen.');
        $data = $request->validate(['stand' => ['required', 'integer', Rule::in(array_keys(Market::stands()))]]);
        $ok = Market::hold((int) $data['stand'], $this->token($request));

        return response()->json([
            'ok' => $ok,
            'stand' => (int) $data['stand'],
            'minutes' => Market::HOLD_MINUTES,
            'amount' => Market::amount((int) $data['stand'], false),
            'message' => $ok ? null : 'Dieser Stand ist leider gerade nicht frei. Bitte wähle einen anderen.',
            'stands' => Market::statusMap($this->token($request)),
        ], $ok ? 200 : 409);
    }

    public function apply(Request $request): RedirectResponse
    {
        if (filled($request->input('website_hp'))) {
            return back();   // Honeypot
        }
        if (! Market::settings()['open']) {
            return back()->withErrors(['stand' => 'Die Bewerbungsphase ist gerade geschlossen.'])->withInput();
        }
        $data = $request->validate([
            'stand' => ['required', 'integer', Rule::in(array_keys(Market::stands()))],
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'street' => ['required', 'string', 'max:160'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(config('ulbrich.market.categories'))],
            'description' => ['required', 'string', 'min:40', 'max:3000'],
            'website' => ['nullable', 'string', 'max:190'],
            'lighting' => ['nullable', 'boolean'],
            'tables' => ['nullable', 'in:1,2'],
            'food' => ['nullable', 'boolean'],
            'demo' => ['nullable', 'boolean'],
            'rules' => ['accepted'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'handmade' => ['accepted'],
            'consent' => ['accepted'],
        ], [
            'stand.required' => 'Bitte wähle im Standplan einen freien Stand.',
            'description.min' => 'Bitte beschreibe dein Handwerk etwas ausführlicher (mindestens 40 Zeichen).',
            'photos.required' => 'Bitte lade mindestens ein Foto deiner Arbeiten hoch.',
            'photos.max' => 'Höchstens 5 Fotos.',
            'photos.*.image' => 'Bitte nur Fotos (JPG, PNG oder WebP) hochladen.',
            'photos.*.max' => 'Ein Foto darf höchstens 6 MB groß sein.',
            'handmade.accepted' => 'Bitte bestätige, dass du deine Waren selbst herstellst.',
            'consent.accepted' => 'Bitte bestätige den Datenschutzhinweis.',
            'rules.accepted' => 'Bitte bestätige, dass du die Marktordnung gelesen hast.',
        ]);

        $token = $this->token($request);
        $stand = (int) $data['stand'];
        if (! in_array(Market::statusMap($token)[$stand] ?? null, ['frei', 'mein'], true)) {
            return back()->withErrors(['stand' => 'Dieser Stand ist inzwischen vergeben. Bitte wähle einen anderen freien Stand.'])->withInput();
        }

        $code = MarketApplication::newCode();
        $photos = [];
        foreach ($request->file('photos', []) as $i => $file) {
            $photos[] = $file->storeAs('markt/'.$code, ($i + 1).'.'.$file->extension(), 'local');   // privat, nicht öffentlich erreichbar
        }
        $app = MarketApplication::create([
            'code' => $code, 'season' => Market::season(), 'stand' => $stand, 'status' => 'eingegangen',
            'name' => $data['name'], 'company' => $data['company'] ?? null, 'email' => $data['email'], 'phone' => $data['phone'],
            'street' => $data['street'], 'postal_code' => $data['postal_code'], 'city' => $data['city'],
            'category' => $data['category'], 'description' => $data['description'], 'website' => $data['website'] ?? null,
            'lighting' => (bool) ($data['lighting'] ?? false), 'photos' => $photos,
            'tables' => (Market::stands()[$stand]['type'] ?? '') === 'innen' ? (int) ($data['tables'] ?? 1) : null,
            'food' => (bool) ($data['food'] ?? false), 'demo' => (bool) ($data['demo'] ?? false),
            'amount' => Market::amount($stand, (bool) ($data['lighting'] ?? false)),
        ]);
        Market::release($token);

        $flags = implode('', [$app->tables ? " · {$app->tables} Biertisch(e)" : '', $app->food ? ' · LEBENSMITTEL (Freigabe nötig)' : '', $app->demo ? ' · VORFÜHRUNG (Freigabe nötig)' : '']);
        try {
            Mail::to($app->email, $app->name)->send(new MarketMail($app, 'eingang'));
            Mail::raw("Neue Bewerbung Kunst- & Handwerkermarkt {$app->code}\nStand {$app->stand} · {$app->category}{$flags}\n{$app->name}".($app->company ? " ({$app->company})" : '')."\n{$app->email} · {$app->phone}\n\n{$app->description}\n\nPrüfen in der Verwaltung → Markt-Bewerbungen.",
                fn ($m) => $m->to(config('ulbrich.email'))->replyTo($app->email, $app->name)->subject("Markt-Bewerbung {$app->code} · Stand {$app->stand}"));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect('/kunsthandwerkermarkt/#bewerbung')->with('market_code', $app->code);
    }

    /** „Sag mir Bescheid, wenn der Termin feststeht“ – über den Newsletter mit Double-Opt-in. */
    public function notify(Request $request): RedirectResponse
    {
        if (filled($request->input('website_hp'))) {
            return back();
        }
        $data = $request->validate(['email' => ['required', 'email', 'max:190'], 'newsletter_consent' => ['accepted']],
            ['newsletter_consent.accepted' => 'Bitte bestätige die Einwilligung.']);
        \App\Models\NewsletterSubscriber::request($data['email'], 'markt');

        return redirect('/kunsthandwerkermarkt/#termin')->with('market_notify', true);
    }
}
