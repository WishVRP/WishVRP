<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Order;
use App\Models\SiteEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/** „Messer zum Nachschärfen einschicken“ (Schliff-Garantie): legt einen Vorgang „Messerservice“ an und schickt Einsendeadresse + Ablauf. */
class ResharpenController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect('/nachschaerfen/#gesendet')->with('resharpen_ok', 'U0000-0000');   // Honeypot
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'order_number' => ['nullable', 'string', 'max:20'],
            'origin' => ['required', Rule::in(['gekauft', 'kurs'])],
            'knives' => ['required', 'string', 'max:1000'],
            'count' => ['required', 'integer', 'min:1', 'max:10'],
            'delivery' => ['required', Rule::in(['einsenden', 'vorbeibringen'])],
            'street' => ['required_if:delivery,einsenden', 'nullable', 'string', 'max:160'],
            'postal_code' => ['required_if:delivery,einsenden', 'nullable', 'string', 'max:20'],
            'city' => ['required_if:delivery,einsenden', 'nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ], [
            'knives.required' => 'Bitte beschreibe kurz, welches Messer du einschicken möchtest.',
            'street.required_if' => 'Für die Rücksendung brauchen wir deine Anschrift.',
            'postal_code.required_if' => 'Bitte gib die Postleitzahl an.',
            'city.required_if' => 'Bitte gib den Ort an.',
            'consent.accepted' => 'Bitte bestätige die Datenschutzhinweise.',
        ]);

        $number = strtoupper(trim((string) ($data['order_number'] ?? ''))) ?: null;
        $original = $number ? Order::query()->where('number', $number)->first() : null;
        $matches = $original && strcasecmp($original->email, $data['email']) === 0;
        $send = $data['delivery'] === 'einsenden';

        $order = Order::create([
            'type' => 'service', 'status' => 'neu',
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null,
            'item' => 'Nachschärfen (Schliff-Garantie) · '.$data['count'].' Messer',
            'quantity' => $data['count'],
            'street' => $send ? $data['street'] : null, 'postal_code' => $send ? $data['postal_code'] : null,
            'city' => $send ? $data['city'] : null, 'country' => $send ? (($data['country'] ?? '') ?: 'Deutschland') : null,
            'shipping_method' => $send ? 'versand' : 'abholung',
            'message' => trim(implode("\n", array_filter([
                'Messer: '.$data['knives'],
                'Herkunft: '.($data['origin'] === 'kurs' ? 'im Schmiedekurs selbst geschmiedet' : 'bei uns gekauft'),
                'Übergabe: '.($send ? 'wird eingeschickt, Rücksendung an die Anschrift' : 'wird persönlich vorbeigebracht und abgeholt'),
                $number ? 'Angegebene Bestell-/Vorgangsnummer: '.$number : null,
                ! empty($data['message']) ? 'Nachricht: '.$data['message'] : null,
            ]))),
            'source_path' => '/nachschaerfen/',
        ]);
        if ($number) {
            Note::query()->create(['order_id' => $order->id, 'is_pinned' => ! $matches, 'body' => $matches
                ? "Schliff-Garantie: Bezug auf Vorgang {$original->number} ({$original->summary()}) – E-Mail passt."
                : "Schliff-Garantie: Angegebene Nummer {$number} ".($original ? 'gehört zu einer anderen E-Mail-Adresse' : 'nicht gefunden').' – bitte prüfen.']);
        }
        SiteEvent::create(['name' => 'anfrage', 'path' => '/nachschaerfen/', 'occurred_on' => now()->toDateString()]);

        $c = config('ulbrich');
        try {
            Mail::to($order->email, $order->name)->send(new \App\Mail\ResharpenMail($order));
            Mail::raw("Nachschärfen (Schliff-Garantie): {$order->number} · {$order->name} · {$order->email}\n\n{$order->message}", fn ($m) => $m->to($c['email'])->subject("Nachschärfen: {$order->number}"));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect('/nachschaerfen/#gesendet')->with('resharpen_ok', $order->number)->with('resharpen_send', $send);
    }
}
