<?php

namespace App\Support;

use App\Models\CalendarBlock;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Buchungskalender: jeder Tag ist buchbar, ein Kurs pro Tag.
 * Belegt = gesperrter Tag oder Kurstag einer aktiven Buchung (auch mehrtägig).
 * Online-Zahlungen halten den Tag für die Haltezeit; danach wird er wieder frei.
 */
final class CourseCalendar
{
    public static function first(): Carbon
    {
        return now()->startOfDay()->addDays((int) config('ulbrich.booking.min_days_ahead'));
    }

    public static function last(): Carbon
    {
        return now()->startOfDay()->addYears((int) config('ulbrich.booking.years_ahead'));
    }

    /** Kurstage: „1 Tag“ → 1, „2 Tage“ → 2, „2–3 Tage“ → 3 (es wird die längste Dauer blockiert). */
    public static function days(Course $course): int
    {
        preg_match_all('/\d+/', (string) $course->duration_label, $m);

        return max(1, (int) (collect($m[0])->max() ?? 1));
    }

    public static function bookable(Course $course): bool
    {
        return in_array($course->slug, config('ulbrich.booking.online_courses'), true) && $course->price_from;
    }

    /** @return Collection<string, true> belegte Tage (Y-m-d) im Zeitraum */
    public static function busy(Carbon $from, Carbon $to, ?int $ignoreOrderId = null): Collection
    {
        $busy = collect();
        CalendarBlock::query()->whereDate('date', '>=', $from->toDateString())->whereDate('date', '<=', $to->toDateString())->pluck('date')
            ->each(fn ($d) => $busy->put(Carbon::parse($d)->toDateString(), true));

        Order::query()->where('type', 'kurs')->whereNotNull('course_start')
            ->where('status', '!=', 'storniert')
            ->where(fn ($q) => $q->whereNull('hold_until')->orWhere('hold_until', '>', now()))
            ->whereDate('course_start', '<=', $to->toDateString())
            ->whereDate('course_end', '>=', $from->toDateString())
            ->when($ignoreOrderId, fn ($q) => $q->whereKeyNot($ignoreOrderId))
            ->get(['course_start', 'course_end'])
            ->each(function (Order $o) use ($busy) {
                for ($d = $o->course_start->copy(); $d->lte($o->course_end); $d->addDay()) {
                    $busy->put($d->toDateString(), true);
                }
            });

        return $busy;
    }

    public static function available(Course $course, Carbon $start): bool
    {
        $start = $start->copy()->startOfDay();
        $end = $start->copy()->addDays(self::days($course) - 1);
        if (! self::bookable($course) || $start->lt(self::first()) || $end->gt(self::last())) {
            return false;
        }

        return self::busy($start, $end)->isEmpty();
    }

    /** Monatsansicht: Y-m-d => frei | belegt | gesperrt (außerhalb des Buchungsfensters). */
    public static function month(Course $course, int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfDay();
        $to = $from->copy()->endOfMonth()->startOfDay();
        $len = self::days($course);
        $busy = self::busy($from, $to->copy()->addDays($len));
        $first = self::first();
        $last = self::last();
        $out = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $end = $d->copy()->addDays($len - 1);
            if ($d->lt($first) || $end->gt($last)) {
                $out[$d->toDateString()] = 'gesperrt';
                continue;
            }
            $free = true;
            for ($x = $d->copy(); $x->lte($end); $x->addDay()) {
                if ($busy->has($x->toDateString())) {
                    $free = false;
                    break;
                }
            }
            $out[$d->toDateString()] = $free ? 'frei' : 'belegt';
        }

        return $out;
    }

    /** Nächste freie Starttage (für Startseite/Newsletter). @return array<int, string> */
    public static function nextFree(Course $course, int $count = 5): array
    {
        $out = [];
        $d = self::first();
        $busy = self::busy($d, $d->copy()->addDays(120));
        $len = self::days($course);
        for ($i = 0; $i < 120 && count($out) < $count; $i++, $d->addDay()) {
            $ok = true;
            for ($x = $d->copy(), $n = 0; $n < $len; $n++, $x->addDay()) {
                if ($busy->has($x->toDateString())) {
                    $ok = false;
                }
            }
            if ($ok) {
                $out[] = $d->toDateString();
            }
        }

        return $out;
    }
}
