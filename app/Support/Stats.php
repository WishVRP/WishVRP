<?php

namespace App\Support;

use App\Models\Order;
use App\Models\PageView;
use App\Models\SiteEvent;
use Illuminate\Support\Carbon;

/** Kennzahlen für die Verwaltung (alle Zahlen aus der eigenen Datenbank). */
final class Stats
{
    public static function views(int $days, bool $entriesOnly = false): int
    {
        return PageView::query()->where('viewed_on', '>=', now()->subDays($days - 1)->toDateString())
            ->when($entriesOnly, fn ($q) => $q->where('is_entry', true))->count();
    }

    /** @return array<string, int> Datum => Anzahl, lückenlos */
    public static function daily(int $days, bool $entriesOnly = false): array
    {
        $rows = PageView::query()->selectRaw('viewed_on, count(*) as n')
            ->where('viewed_on', '>=', now()->subDays($days - 1)->toDateString())
            ->when($entriesOnly, fn ($q) => $q->where('is_entry', true))
            ->groupBy('viewed_on')->pluck('n', 'viewed_on')
            ->mapWithKeys(fn ($n, $d) => [Carbon::parse($d)->toDateString() => (int) $n]);
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $out[$d] = $rows[$d] ?? 0;
        }

        return $out;
    }

    /** @return array<string, int> */
    public static function topPages(int $days, int $limit = 10): array
    {
        return PageView::query()->selectRaw('path, count(*) as n')
            ->where('viewed_on', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('path')->orderByDesc('n')->limit($limit)->pluck('n', 'path')->map(fn ($n) => (int) $n)->all();
    }

    /** @return array<string, int> */
    public static function by(string $column, int $days, bool $entriesOnly = true): array
    {
        return PageView::query()->selectRaw("{$column} as k, count(*) as n")
            ->where('viewed_on', '>=', now()->subDays($days - 1)->toDateString())
            ->when($entriesOnly, fn ($q) => $q->where('is_entry', true))
            ->groupBy($column)->orderByDesc('n')->pluck('n', 'k')->map(fn ($n) => (int) $n)->all();
    }

    /** @return array<string, int> */
    public static function clicks(int $days): array
    {
        return SiteEvent::query()->selectRaw('name, count(*) as n')
            ->where('occurred_on', '>=', now()->subDays($days - 1)->toDateString())
            ->groupBy('name')->pluck('n', 'name')->map(fn ($n) => (int) $n)->all();
    }

    public static function revenue(int $days): float
    {
        return (float) Order::query()->whereNotNull('paid_at')->where('paid_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->whereNot('status', 'storniert')->sum('amount');
    }

    public const SOURCES = ['direkt' => 'Direkt / Lesezeichen', 'suche' => 'Suchmaschinen', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'ki' => 'KI-Assistenten', 'andere' => 'Andere Websites', 'intern' => 'Intern'];

    public const DEVICES = ['mobil' => 'Handy', 'desktop' => 'Computer', 'tablet' => 'Tablet'];
}
