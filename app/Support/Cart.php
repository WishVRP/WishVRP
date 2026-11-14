<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Product;
use Illuminate\Support\Carbon;

/**
 * Warenkorb in der Session: Messer aus dem Shop und Kursbuchungen (Kurs + Tag + Personen).
 * Preise, Verfügbarkeit und freie Tage werden bei jeder Anzeige und an der Kasse neu vom Server geprüft.
 */
final class Cart
{
    private const KEY = 'cart';

    /** @return array<string, array{type: string, id?: int, qty?: int, course?: string, date?: string, persons?: int}> */
    public static function raw(): array
    {
        return (array) session(self::KEY, []);
    }

    public static function addProduct(Product $product, int $qty): void
    {
        $cart = self::raw();
        $key = 'p'.$product->id;
        $cart[$key] = ['type' => 'product', 'id' => $product->id, 'qty' => min(max(1, ($cart[$key]['qty'] ?? 0) + $qty), max(1, (int) $product->stock))];
        session([self::KEY => $cart]);
    }

    public static function addCourse(Course $course, string $date, int $persons): void
    {
        $cart = self::raw();
        // ein Kurstag nur einmal im Warenkorb; gleicher Kurs + Tag = Personen aktualisieren
        $cart['k'.$course->id.'-'.$date] = ['type' => 'kurs', 'course' => $course->slug, 'date' => $date, 'persons' => $persons];
        session([self::KEY => $cart]);
    }

    public static function setQty(string $key, int $qty): void
    {
        $cart = self::raw();
        if (! isset($cart[$key])) {
            return;
        }
        if ($qty < 1) {
            unset($cart[$key]);
        } elseif ($cart[$key]['type'] === 'product') {
            $stock = (int) (Product::query()->whereKey($cart[$key]['id'])->value('stock') ?? 1);
            $cart[$key]['qty'] = min($qty, max(1, $stock));
        } else {
            $cart[$key]['persons'] = min($qty, (int) config('ulbrich.booking.max_persons'));
        }
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function count(): int
    {
        return count(self::raw());
    }

    /**
     * Aufgelöste Positionen mit aktuellen Preisen; nicht mehr verfügbare werden markiert (problem).
     *
     * @return array<int, array{key: string, type: string, name: string, detail: string, qty: int, unit: float, total: float, url: ?string, problem: ?string, product?: Product, course?: Course, start?: Carbon}>
     */
    public static function lines(): array
    {
        $raw = self::raw();
        $products = Product::query()->whereIn('id', collect($raw)->where('type', 'product')->pluck('id'))->get()->keyBy('id');
        $courses = Course::query()->published()->whereIn('slug', collect($raw)->where('type', 'kurs')->pluck('course'))->get()->keyBy('slug');
        $lines = [];
        foreach ($raw as $key => $row) {
            if ($row['type'] === 'product') {
                $p = $products[$row['id']] ?? null;
                if (! $p || ! in_array($p->status, ['aktiv', 'verkauft'], true)) {
                    continue;
                }
                $qty = max(1, (int) $row['qty']);
                $problem = ! $p->available() ? 'Leider nicht mehr verfügbar.' : ($qty > $p->stock ? 'Nur noch '.$p->stock.' Stück verfügbar.' : null);
                $lines[] = ['key' => $key, 'type' => 'product', 'name' => $p->name, 'detail' => $p->categoryName(), 'qty' => $qty,
                    'unit' => (float) $p->price, 'total' => round((float) $p->price * $qty, 2), 'url' => $p->url(), 'problem' => $problem, 'product' => $p];
            } else {
                $c = $courses[$row['course']] ?? null;
                if (! $c) {
                    continue;
                }
                $start = Carbon::parse($row['date'])->startOfDay();
                $persons = max(1, (int) $row['persons']);
                $problem = ! CourseCalendar::bookable($c) ? 'Dieser Kurs ist online gerade nicht buchbar.'
                    : ($start->lt(CourseCalendar::first()) || ! CourseCalendar::available($c, $start) ? 'Dieser Tag ist nicht mehr frei – bitte einen anderen wählen.' : null);
                $end = $start->copy()->addDays(CourseCalendar::days($c) - 1);
                $lines[] = ['key' => $key, 'type' => 'kurs', 'name' => $c->name,
                    'detail' => $start->locale('de')->isoFormat('dd., D. MMMM YYYY').($end->isSameDay($start) ? '' : ' – '.$end->locale('de')->isoFormat('dd., D. MMMM YYYY')).' · '.$c->duration_label,
                    'qty' => $persons, 'unit' => (float) $c->price_from, 'total' => round((float) $c->price_from * $persons, 2),
                    'url' => '/schmiedekurse/buchen/?kurs='.$c->slug, 'problem' => $problem, 'course' => $c, 'start' => $start];
            }
        }

        return $lines;
    }

    /** Summe der Warenwerte (ohne Versand). */
    public static function subtotal(array $lines, ?string $type = null): float
    {
        return round(collect($lines)->when($type, fn ($c) => $c->where('type', $type))->sum('total'), 2);
    }

    /** Versandgewicht aller Messer (ohne Angabe: Standardpaket je Stück). */
    public static function weight(array $lines): int
    {
        $default = (int) config('ulbrich.sales.default_parcel_g', 1500);

        return (int) collect($lines)->where('type', 'product')->sum(fn ($l) => ($l['product']->weight_g ?: $default) * $l['qty']);
    }

    /** Strengstes Einfuhr-Ergebnis über alle Messer (gesperrt > prüfen > erlaubt). */
    public static function export(array $lines, string $country): ?array
    {
        $rank = ['erlaubt' => 0, 'pruefen' => 1, 'gesperrt' => 2];
        $worst = null;
        foreach (collect($lines)->where('type', 'product') as $l) {
            $check = ExportCheck::evaluate($country, $l['product']->knife_type);
            if (! $worst || ($rank[$check['action']] ?? 0) > ($rank[$worst['action']] ?? 0)) {
                $worst = $check + ['knife_type' => $l['product']->knife_type];
            }
        }

        return $worst;
    }
}
