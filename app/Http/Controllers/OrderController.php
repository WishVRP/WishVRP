<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\DiscountCode;
use App\Models\EditionPiece;
use App\Models\Order;
use App\Models\SiteEvent;
use App\Models\XmasEntry;
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
        'discount_code' => ['nullable', 'string', 'max:20'],
    ];

    private const MESSAGES = [
        'consent.accepted' => 'Bitte bestätige die Datenschutzhinweise.',
        'phone.required_with' => 'Für den Weihnachtsrabatt brauchen wir die Handynummer, mit der du gespielt hast.',
        'bedingungen.accepted' => 'Bitte bestätige die Kursbedingungen.',
    ];

    /**
     * Rabattcode prüfen: Weihnachtsaktion (einmal je Person/Haushalt) oder Newsletter (persönlich, einmalig, befristet).
     *
     * @return array{0: XmasEntry|DiscountCode|null, 1: int|null}
     */
    private function discount(array $data): array
    {
        if (blank($data['discount_code'] ?? null)) {
            return [null, null];
        }
        $fail = fn (string $msg) => throw \Illuminate\Validation\ValidationException::withMessages(['discount_code' => $msg]);

        if ($news = DiscountCode::redeemable($data['discount_code'])) {
            if ($news->email_key !== XmasEntry::emailKey($data['email'] ?? '')) {
                $fail('Dieser Rabattcode gilt nur zusammen mit der E-Mail-Adresse, an die er geschickt wurde.');
            }

            return [$news, $news->percent];
        }

        $entry = XmasEntry::redeemable($data['discount_code']);
        if (! $entry) {
            $fail('Dieser Rabattcode ist ungültig, abgelaufen oder wurde schon eingelöst.');
        }
        if (blank($data['phone'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['phone' => 'Für den Weihnachtsrabatt brauchen wir deine Telefonnummer (ein Rabatt pro Person/Haushalt).']);
        }
        if ($entry->email_key !== XmasEntry::emailKey($data['email'] ?? '')) {
            $fail('Der Rabattcode gilt nur zusammen mit der E-Mail-Adresse, mit der gespielt wurde.');
        }
        if ($entry->phone_key && $entry->phone_key !== XmasEntry::mobileE164($data['phone'] ?? '')) {
            $fail('Der Rabattcode gilt nur zusammen mit der Handynummer, mit der gespielt wurde.');
        }
        // Ein Weihnachtsrabatt je Person bzw. Haushalt: gleiche Telefonnummer, Name + PLZ oder Straße + PLZ
        $phone = XmasEntry::phoneKey($data['phone'] ?? null);
        $name = mb_strtolower(trim($data['name'] ?? ''));
        $postal = trim($data['postal_code'] ?? '');
        $street = Order::streetKey($data['street'] ?? null);
        $xmasCodes = XmasEntry::query()->whereNotNull('code')->pluck('code');
        $used = Order::query()->whereIn('discount_code', $xmasCodes)->where('status', '!=', 'storniert')
            ->get(['phone', 'name', 'postal_code', 'street'])
            ->contains(fn (Order $o) => ($phone && XmasEntry::phoneKey($o->phone) === $phone)
                || ($postal !== '' && $o->postal_code === $postal && mb_strtolower(trim($o->name)) === $name)
                || ($street && $postal !== '' && $o->postal_code === $postal && Order::streetKey($o->street) === $street));
        if ($used) {
            $fail('Pro Person bzw. Haushalt kann nur ein Weihnachtsrabatt eingelöst werden.');
        }

        return [$entry, $entry->percent];
    }

    private function applyDiscount(?Order $order, XmasEntry|DiscountCode|null $entry): void
    {
        if (! $order || ! $entry) {
            return;
        }
        $order->forceFill([
            'discount_code' => $entry->code,
            'discount_percent' => $entry->percent,
            'amount' => $order->amount !== null ? round((float) $order->amount * (100 - $entry->percent) / 100, 2) : null,
        ])->save();
        $entry->update(['redeemed_at' => now(), 'order_id' => $order->id]);
    }

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

        [$xmas] = $this->discount($data);
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
        $this->applyDiscount($order, $xmas);
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
            'number' => ['nullable', 'integer', 'min:1', 'max:'.config('ulbrich.edition.total')],
            'public_name' => ['nullable', 'string', 'max:80'],
        ], self::MESSAGES);
        [$xmas] = $this->discount($data);
        $quantity = $data['quantity'] ?? 1;
        $item = $data['item'] ?? null;
        $amount = null;
        $course = null;

        if ($data['type'] === 'gutschein' && ! empty($data['kurs'])) {
            $course = Course::query()->published()->get()->first(fn ($c) => Str::slug($c->name) === $data['kurs']);
            $item = 'Gutschein: '.($course?->name ?? 'Schmiedekurs');
            $amount = $course?->price_from ? $course->price_from * $quantity : null;
        }
        $piece = null;
        if ($item === config('ulbrich.edition.name')) {
            $quantity = 1;   // je Bestellung genau eine Nummer
            $amount = config('ulbrich.edition.price');
            if (! empty($data['number'])) {
                $piece = EditionPiece::query()->where('number', $data['number'])->first();
                if (! $piece?->isSelectable()) {
                    return back()->withErrors(['number' => 'Diese Nummer ist leider nicht mehr frei. Bitte wähle eine andere.'])->withInput();
                }
                $item .= ' · Nr. '.$piece->number;
            }
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
        $this->applyDiscount($order, $xmas);
        if ($piece) {
            // Nummer sofort reservieren; Name wird erst nach Zahlungsbestätigung grün angezeigt (Order::syncEditionPiece)
            $piece->update(['status' => 'reserviert', 'order_id' => $order->id, 'buyer_name' => $order->public_name, 'show_name' => filled($order->public_name)]);
        }
        SiteEvent::create(['name' => 'anfrage', 'path' => $order->source_path, 'occurred_on' => now()->toDateString()]);
        OrderMail::received($order);

        return redirect(($order->source_path ?? '/').'#anfrage-gesendet')->with('order_ok', $order->number);
    }
}
