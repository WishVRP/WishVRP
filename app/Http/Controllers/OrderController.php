<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\DiscountCode;
use App\Models\EditionPiece;
use App\Models\Order;
use App\Models\SiteEvent;
use App\Models\Voucher;
use App\Models\XmasEntry;
use App\Support\CourseCalendar;
use App\Support\OrderPaid;
use App\Support\Stripe;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    /** Kalenderdaten eines Monats für einen Kurs (frei | belegt | gesperrt). */
    public function availability(Request $request): JsonResponse
    {
        $course = Course::query()->published()->where('slug', (string) $request->query('kurs'))->first();
        abort_unless($course && CourseCalendar::bookable($course), 404);
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('monat')) ? Carbon::createFromFormat('Y-m-d', $request->query('monat').'-01') : CourseCalendar::first();

        return response()->json(['days' => CourseCalendar::month($course, $month->year, $month->month)])->header('Cache-Control', 'no-store');
    }

    public function booking(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('order_ok', 'U0000-0000');
        }
        $data = $request->validate(self::CONTACT + [
            'kurs' => ['required', 'string'],
            'teilnehmer' => ['required', 'integer', 'min:1', 'max:'.config('ulbrich.booking.max_persons')],
            'datum' => ['required', 'date_format:Y-m-d'],
            'gutschein_code' => ['nullable', 'string', 'max:20'],
            'bedingungen' => ['accepted'],
        ], self::MESSAGES + ['datum.required' => 'Bitte wähle im Kalender einen freien Tag.']);

        $course = Course::query()->published()->where('slug', $data['kurs'])->first();
        if (! $course || ! CourseCalendar::bookable($course)) {
            return back()->withErrors(['kurs' => 'Bitte wähle einen online buchbaren Kurs.'])->withInput();
        }
        [$disc] = $this->discount($data);
        $voucher = null;
        if (filled($data['gutschein_code'] ?? null)) {
            $voucher = Voucher::usable($data['gutschein_code']);
            $inUse = $voucher && Order::query()->where('voucher_id', $voucher->id)->where('status', '!=', 'storniert')->exists();
            if (! $voucher || $inUse) {
                return back()->withErrors(['gutschein_code' => 'Dieser Gutscheincode ist ungültig, abgelaufen oder schon eingelöst.'])->withInput();
            }
        }

        $start = Carbon::parse($data['datum'])->startOfDay();
        $end = $start->copy()->addDays(CourseCalendar::days($course) - 1);
        $online = Stripe::enabled();

        // Letzte Prüfung und Anlage in einem Schritt, damit kein Tag doppelt vergeben wird.
        $order = DB::transaction(function () use ($course, $start, $end, $data, $online) {
            if (! CourseCalendar::available($course, $start)) {
                return null;
            }

            return Order::create([
                'type' => 'kurs', 'status' => 'neu',
                'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
                'course_id' => $course->id, 'course_start' => $start, 'course_end' => $end,
                'participants' => $data['teilnehmer'],
                'amount' => $course->price_from * $data['teilnehmer'],
                'source_path' => '/schmiedekurse/buchen/',
                'payment_method' => $online ? 'stripe' : 'ueberweisung',
                'hold_until' => $online ? now()->addMinutes((int) config('ulbrich.booking.hold_minutes')) : null,
            ]);
        });
        if (! $order) {
            return back()->withErrors(['datum' => 'Dieser Tag ist leider gerade vergeben worden. Bitte wähle einen anderen.'])->withInput();
        }

        $this->applyDiscount($order, $disc);
        if ($voucher) {
            $order->forceFill(['voucher_id' => $voucher->id, 'voucher_amount' => min((float) $voucher->value, (float) $order->amount)])->save();
        }
        SiteEvent::create(['name' => 'buchung', 'path' => '/schmiedekurse/buchen/', 'occurred_on' => now()->toDateString()]);

        return $this->pay($order, [['name' => $course->name.' am '.$start->locale('de')->isoFormat('D. MMMM YYYY').' · '.$data['teilnehmer'].' P.', 'amount' => $this->due($order)]], '/schmiedekurse/buchen/#buchung-gesendet');
    }

    /** Für Produktkäufe: Rabattcode anwenden und Zahlung auslösen (Versand als eigene Position). */
    public function payOrder(Order $order, array $data, string $fallbackRedirect): RedirectResponse
    {
        [$disc] = $this->discount($data);
        $this->applyDiscount($order, $disc);
        $lines = [['name' => $order->item.($order->discount_percent ? " (−{$order->discount_percent} %)" : ''), 'amount' => (float) $order->amount]];
        if ((float) $order->shipping_amount > 0) {
            $lines[] = ['name' => 'Versand nach '.($order->country ?: 'Deutschland').($order->shipping_note ? ' ('.\Illuminate\Support\Str::before($order->shipping_note, ' ·').')' : ''), 'amount' => (float) $order->shipping_amount];
        }
        if ((float) $order->customs_amount > 0) {
            $lines[] = ['name' => 'Zoll und Einfuhrabgaben '.($order->country ?: '').' (im Voraus)', 'amount' => (float) $order->customs_amount];
        }

        return $this->pay($order, $lines, $fallbackRedirect);
    }

    /** Offener Betrag nach Rabatt und Gutschein (inkl. Versand). */
    private function due(Order $order): float
    {
        return $order->totalDue();
    }

    /**
     * Zahlung auslösen: 0 € (Gutschein deckt alles) → sofort bestätigt; Stripe aktiv → Checkout;
     * sonst Anfrage mit Überweisung (wie bisher).
     *
     * @param  array<int, array{name: string, amount: float}>  $lines
     */
    private function pay(Order $order, array $lines, string $fallbackRedirect): RedirectResponse
    {
        if ($this->due($order) <= 0) {
            OrderPaid::paid($order, $order->voucher_id ? 'gutschein' : 'rabatt');

            return redirect('/zahlung/danke/?o='.$order->number);
        }
        if (Stripe::enabled()) {
            try {
                $order->update(['status' => 'zahlung_offen']);

                return redirect()->away(Stripe::checkout($order, array_values(array_filter($lines, fn ($l) => $l['amount'] > 0))));
            } catch (\Throwable $e) {
                report($e);
                OrderPaid::expired($order);

                return back()->withErrors(['email' => 'Die Online-Zahlung ist gerade nicht erreichbar. Bitte versuch es gleich noch einmal oder ruf uns an.'])->withInput();
            }
        }
        if ($order->voucher_id) {
            Voucher::query()->whereKey($order->voucher_id)->update(['status' => 'eingeloest', 'redeemed_order_id' => $order->id, 'redeemed_at' => now()]);
        }
        OrderMail::received($order);

        return redirect($fallbackRedirect)->with('order_ok', $order->number);
    }

    /** Gutschein kaufen: Kurs × Personen, Code nach Zahlung per E-Mail. */
    public function voucher(Request $request): RedirectResponse
    {
        if ($this->isSpam($request)) {
            return back()->with('order_ok', 'U0000-0000');
        }
        $data = $request->validate(self::CONTACT + [
            'kurs' => ['required', 'string'],
            'personen' => ['required', 'integer', 'min:1', 'max:'.config('ulbrich.booking.max_persons')],
            'empfaenger' => ['nullable', 'string', 'max:120'],
            'geschenk_email' => ['nullable', 'email', 'max:190'],
            'geschenk_datum' => ['nullable', 'date', 'after_or_equal:today', 'before:'.Voucher::defaultValidUntil()->toDateString()],
            'geschenk_nachricht' => ['nullable', 'string', 'max:300'],
            'agb' => ['accepted'],
        ], self::MESSAGES + ['agb.accepted' => 'Bitte bestätige AGB und Widerrufsbelehrung.', 'geschenk_datum.after_or_equal' => 'Bitte wähle heute oder einen späteren Tag.']);
        $course = Course::query()->published()->where('slug', $data['kurs'])->first();
        if (! $course || ! CourseCalendar::bookable($course)) {
            return back()->withErrors(['kurs' => 'Bitte wähle einen Kurs.'])->withInput();
        }
        [$disc] = $this->discount($data);
        $value = $course->price_from * $data['personen'];

        $order = Order::create([
            'type' => 'gutschein', 'status' => 'neu',
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'message' => $data['message'] ?? null,
            'item' => 'Gutschein: '.$course->name.' · '.$data['personen'].' '.($data['personen'] == 1 ? 'Person' : 'Personen'),
            'course_id' => $course->id, 'quantity' => 1, 'amount' => $value,
            'source_path' => '/geschenke/schmiedekurs-gutschein/',
            'payment_method' => Stripe::enabled() ? 'stripe' : 'ueberweisung',
        ]);
        Voucher::create([
            'code' => Voucher::newCode(), 'course_id' => $course->id, 'persons' => $data['personen'], 'value' => $value,
            'status' => 'offen', 'recipient' => $data['empfaenger'] ?? null, 'order_id' => $order->id, 'valid_until' => Voucher::defaultValidUntil(),
            'recipient_email' => $data['geschenk_email'] ?? null,
            'send_on' => ! empty($data['geschenk_email']) ? ($data['geschenk_datum'] ?? null) : null,
            'gift_message' => $data['geschenk_nachricht'] ?? null,
        ]);
        $this->applyDiscount($order, $disc);
        SiteEvent::create(['name' => 'anfrage', 'path' => '/geschenke/schmiedekurs-gutschein/', 'occurred_on' => now()->toDateString()]);

        return $this->pay($order, [['name' => $order->item, 'amount' => $this->due($order)]], '/geschenke/schmiedekurs-gutschein/#anfrage-gesendet');
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
            'country_code' => ['nullable', Rule::in(array_keys(\App\Support\Shipping::countries()))],
            'versand' => ['nullable', 'in:versand,abholung'],
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
            'city' => $data['city'] ?? null,
            'country' => ! empty($data['country_code']) ? \App\Support\Shipping::countryName($data['country_code']) : ($data['country'] ?? null),
            'country_code' => $data['country_code'] ?? null,
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

        // Edition 100: Versand (Kosten nach Land) oder Abholung; mit Online-Zahlung bleibt die Nummer währenddessen reserviert.
        if ($item && str_starts_with($item, config('ulbrich.edition.name'))) {
            $pickup = ($data['versand'] ?? 'versand') === 'abholung';
            $q = $pickup ? null : \App\Support\Shipping::quote($data['country_code'] ?? 'DE', null, $data['postal_code'] ?? null, $data['city'] ?? null);
            $etype = config('ulbrich.export.edition_type');
            $export = $q ? \App\Support\ExportCheck::evaluate($q['country'], $etype) : null;
            if ($export && $export['action'] === 'gesperrt') {
                \App\Support\ExportCheck::logBlocked($export, $q['country'], $etype, $data + ['item' => $order->item, 'source_path' => $order->source_path]);
                $piece?->update(['status' => 'frei', 'order_id' => null, 'buyer_name' => null, 'show_name' => false]);
                $order->forceFill(['status' => 'storniert', 'export_note' => 'Gesperrt: '.$export['reason']])->save();

                return back()->withErrors(['country_code' => $export['message']])->withInput();
            }
            $customs = $q && $q['price'] !== null ? \App\Support\Customs::estimate($q['country'], (float) $order->amount, (float) $q['price']) : null;
            $order->forceFill([
                'shipping_method' => $pickup ? 'abholung' : 'versand',
                'shipping_amount' => $pickup ? 0 : $q['price'],
                'shipping_note' => $q ? ($q['price'] === null ? 'Versandkosten auf Anfrage' : $q['label'].(($customs['mode'] ?? null) === 'empfaenger' ? ' · zzgl. Zoll/Einfuhrabgaben im Zielland' : '')) : null,
                'country' => $q['country_name'] ?? $order->country, 'country_code' => $q['country'] ?? $order->country_code,
                'customs_amount' => ($customs['mode'] ?? null) === 'vorab' ? $customs['amount'] : null,
                'knife_type' => $etype,
                'export_note' => $export ? \App\Support\ExportCheck::note($export, (bool) $q['customs']) : null,
            ])->save();
            if (Stripe::enabled() && ($pickup || ($q['price'] !== null && $export['action'] === 'erlaubt'))) {
                $order->forceFill(['hold_until' => now()->addMinutes((int) config('ulbrich.booking.hold_minutes'))])->save();
                $lines = [['name' => $order->item, 'amount' => (float) $order->amount]];
                if (! $pickup && $q['price'] > 0) {
                    $lines[] = ['name' => 'Versand nach '.$q['country_name'].' ('.$q['label'].')', 'amount' => $q['price']];
                }
                if ((float) $order->customs_amount > 0) {
                    $lines[] = ['name' => 'Zoll und Einfuhrabgaben '.$q['country_name'].' (im Voraus)', 'amount' => (float) $order->customs_amount];
                }

                return $this->pay($order, $lines, '/klappmesser/#anfrage-gesendet');
            }
        }
        OrderMail::received($order);

        return redirect(($order->source_path ?? '/').'#anfrage-gesendet')->with('order_ok', $order->number);
    }
}
