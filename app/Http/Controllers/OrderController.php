<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Order;
use App\Models\SiteEvent;
use App\Support\OrderMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Nimmt Kursbuchungen und Bestell-/Serviceanfragen aus der Website an.
 * Keine Online-Zahlung: Vorgang wird gespeichert, Bestätigung nach Zahlungseingang in der Verwaltung.
 */
class OrderController extends Controller
{
    private const CONTACT = [
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:190'],
        'phone' => ['nullable', 'string', 'max:40'],
        'message' => ['nullable', 'string', 'max:3000'],
        'consent' => ['accepted'],
    ];

    private const MESSAGES = [
        'consent.accepted' => 'Bitte bestätige die Datenschutzhinweise.',
        'bedingungen.accepted' => 'Bitte bestätige die Kursbedingungen.',
    ];

    private function isSpam(Request $request): bool
    {
        return filled($request->input('website'));   // Honeypot
    }

    public function booking(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('order_ok', 'U0000-0000');
        }
        $data = $request->validate(self::CONTACT + [
            'kurs' => ['required', 'string'],
            'teilnehmer' => ['required', 'integer', 'min:1', 'max:12'],
            'termin' => ['nullable', 'integer'],
            'wunschtermin' => ['nullable', 'string', 'max:120'],
            'bedingungen' => ['accepted'],
        ], self::MESSAGES);

        $course = Course::query()->published()->get()->first(fn ($c) => Str::slug($c->name) === $data['kurs']);
        if (! $course) {
            return back()->withErrors(['kurs' => 'Bitte wähle einen Kurs.'])->withInput();
        }
        $date = null;
        if (! empty($data['termin'])) {
            $date = CourseDate::query()->upcoming()->whereKey($data['termin'])->where('course_id', $course->id)->first();
            if (! $date || $date->seatsLeft() < $data['teilnehmer']) {
                return back()->withErrors(['termin' => 'Für diesen Termin sind nicht mehr genug Plätze frei. Bitte wähle einen anderen.'])->withInput();
            }
        } elseif (CourseDate::query()->upcoming()->where('course_id', $course->id)->exists()) {
            return back()->withErrors(['termin' => 'Bitte wähle einen Termin.'])->withInput();
        }

        $order = Order::create([
            'type' => 'kurs',
            'status' => 'neu',
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            'course_id' => $course->id,
            'course_date_id' => $date?->id,
            'participants' => $data['teilnehmer'],
            'preferred_date' => $date ? null : ($data['wunschtermin'] ?? null),
            'amount' => $course->price_from ? $course->price_from * $data['teilnehmer'] : null,
            'source_path' => '/schmiedekurse/buchen/',
        ]);
        SiteEvent::create(['name' => 'buchung', 'path' => '/schmiedekurse/buchen/', 'occurred_on' => now()->toDateString()]);
        OrderMail::received($order);

        return redirect('/schmiedekurse/buchen/#buchung-gesendet')->with('order_ok', $order->number);
    }

    public function inquiry(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('order_ok', 'U0000-0000');
        }
        $data = $request->validate(self::CONTACT + [
            'type' => ['required', Rule::in(['gutschein', 'messer', 'service', 'anfrage'])],
            'item' => ['nullable', 'string', 'max:190'],
            'kurs' => ['nullable', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'street' => ['nullable', 'string', 'max:160'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:190'],
            'show_name' => ['nullable', 'boolean'],
            'public_name' => ['nullable', 'string', 'max:80'],
        ], self::MESSAGES);
        $quantity = $data['quantity'] ?? 1;
        $item = $data['item'] ?? null;
        $amount = null;
        $course = null;

        if ($data['type'] === 'gutschein' && ! empty($data['kurs'])) {
            $course = Course::query()->published()->get()->first(fn ($c) => Str::slug($c->name) === $data['kurs']);
            $item = 'Gutschein: '.($course?->name ?? 'Schmiedekurs');
            $amount = $course?->price_from ? $course->price_from * $quantity : null;
        }
        if ($item === config('ulbrich.edition.name')) {
            $amount = config('ulbrich.edition.price') * $quantity;
        }

        $order = Order::create([
            'type' => $data['type'],
            'status' => 'neu',
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            'item' => $item ? Str::limit($item, 187) : null,
            'quantity' => $quantity,
            'course_id' => $course?->id,
            'amount' => $amount,
            'street' => $data['street'] ?? null, 'postal_code' => $data['postal_code'] ?? null,
            'city' => $data['city'] ?? null, 'country' => $data['country'] ?? null,
            'source_path' => isset(config('pages')[$data['source'] ?? '']) ? $data['source'] : null,
            // Anzeigename nur speichern, wenn der Veröffentlichung zugestimmt wurde
            'public_name' => ! empty($data['show_name']) ? (trim($data['public_name'] ?? '') ?: null) : null,
        ]);
        SiteEvent::create(['name' => 'anfrage', 'path' => $order->source_path, 'occurred_on' => now()->toDateString()]);
        OrderMail::received($order);

        return redirect(($order->source_path ?? '/').'#anfrage-gesendet')->with('order_ok', $order->number);
    }
}
