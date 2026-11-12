<?php

namespace App\Http\Controllers;

use App\Mail\WithdrawalMail;
use App\Models\Note;
use App\Models\Order;
use App\Models\Withdrawal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Elektronische Widerrufsfunktion („Vertrag widerrufen“, Pflicht seit 19.06.2026):
 * Schritt 1 Angaben → Schritt 2 „Widerruf bestätigen“ → sofortige Eingangsbestätigung per E-Mail.
 * Jeder Widerruf wird angenommen und gespeichert – ob er wirksam ist (z. B. Ausnahmen für Kurse mit festem Termin
 * oder Anfertigungen nach Kundenwunsch), entscheidet die Verwaltung danach.
 */
class WithdrawalController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'order_number' => ['nullable', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** Schritt 1 → Bestätigungsseite */
    public function review(Request $request): RedirectResponse
    {
        if (filled($request->input('website_hp'))) {
            return back();
        }
        $data = $request->validate($this->rules(), ['name.required' => 'Bitte gib deinen Namen an.', 'email.required' => 'Bitte gib deine E-Mail-Adresse an – dorthin schicken wir die Bestätigung.']);

        return redirect('/widerrufen/#bestaetigen')->with('withdraw_review', $data)->withInput();
    }

    /** Schritt 2: „Widerruf bestätigen“ */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules() + ['confirm' => ['accepted']]);
        $number = strtoupper(trim((string) ($data['order_number'] ?? ''))) ?: null;
        $order = $number ? Order::query()->where('number', $number)->first() : null;
        if ($order && strcasecmp($order->email, $data['email']) !== 0) {
            $order = null;   // Vorgang nur verknüpfen, wenn die E-Mail passt (kein Fremdzugriff)
        }
        $w = Withdrawal::query()->create([
            'order_id' => $order?->id, 'order_number' => $number, 'name' => $data['name'], 'email' => $data['email'],
            'message' => $data['message'] ?? null, 'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);
        if ($order) {
            Note::query()->create(['order_id' => $order->id, 'body' => 'WIDERRUF eingegangen am '.now('Europe/Berlin')->format('d.m.Y H:i').' – bitte unter „Widerrufe“ prüfen.', 'is_pinned' => true]);
        }
        try {
            Mail::to($w->email, $w->name)->send(new WithdrawalMail($w, 'eingang'));
            Mail::raw("Widerruf eingegangen: {$w->name} <{$w->email}>, Vorgang ".($number ?: '–').($order ? '' : ($number ? ' (nicht zugeordnet – E-Mail oder Nummer passt nicht)' : ''))."\n\n".($w->message ?? '')."\n\nPrüfen in der Verwaltung → Widerrufe.",
                fn ($m) => $m->to(config('ulbrich.email'))->replyTo($w->email, $w->name)->subject('Widerruf eingegangen '.($number ?: '')));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect('/widerrufen/#bestaetigt')->with('withdraw_done', $w->created_at->timezone('Europe/Berlin')->format('d.m.Y, H:i'));
    }
}
