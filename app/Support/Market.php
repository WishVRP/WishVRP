<?php

namespace App\Support;

use App\Models\MarketApplication;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Kunst- & Handwerkermarkt: Stände aus config('ulbrich.market.stands'), Belegung aus den Bewerbungen der aktuellen Saison.
 * Termin, Saison und „Bewerbung offen“ sind in der Verwaltung änderbar (Settings), Fallback config.
 */
final class Market
{
    public const HOLD_MINUTES = 15;

    public static function settings(): array
    {
        $s = Setting::read('market', []);

        return [
            'season' => $s['season'] ?? (string) (now()->month >= 10 ? now()->year + 1 : now()->year),
            'date' => $s['date'] ?? config('ulbrich.market.date'),
            'open' => (bool) ($s['open'] ?? true),
        ];
    }

    public static function season(): string
    {
        return self::settings()['season'];
    }

    /** @return array<int, array{number: int, type: string, label: string, size: string, price: int}> */
    public static function stands(): array
    {
        $out = [];
        foreach (config('ulbrich.market.stands') as $i => $st) {
            [$from, $to] = array_map('intval', preg_split('/\D+/', $st['numbers']));
            for ($n = $from; $n <= ($to ?: $from); $n++) {
                $out[$n] = ['number' => $n, 'type' => $i === 0 ? 'innen' : 'aussen', 'label' => $st['label'], 'size' => $st['size'], 'price' => (int) $st['price']];
            }
        }
        ksort($out);

        return $out;
    }

    /** Gebühr: Standpreis + Werbepauschale (+ Beleuchtung). */
    public static function amount(int $stand, bool $lighting): float
    {
        $fees = config('ulbrich.market.fees');

        return (float) (self::stands()[$stand]['price'] + $fees[0]['price'] + ($lighting ? $fees[1]['price'] : 0));
    }

    /** @return array<int, string> Standnummer → frei | reserviert | belegt | mein */
    public static function statusMap(?string $token = null): array
    {
        $season = self::season();
        $map = array_fill_keys(array_keys(self::stands()), 'frei');
        foreach (MarketApplication::query()->where('season', $season)->whereIn('status', MarketApplication::BLOCKING)->get(['stand', 'status']) as $a) {
            if (isset($map[$a->stand])) {
                $map[$a->stand] = $a->status === 'eingegangen' ? 'reserviert' : 'belegt';
            }
        }
        foreach (DB::table('market_holds')->where('season', $season)->where('until', '>', now())->get() as $h) {
            if (($map[$h->stand] ?? null) === 'frei') {
                $map[$h->stand] = $token && hash_equals($h->token, $token) ? 'mein' : 'reserviert';
            }
        }

        return $map;
    }

    /** Freien Stand für 15 Minuten halten (eine Reservierung je Besucher). */
    public static function hold(int $stand, string $token): bool
    {
        $season = self::season();
        $state = self::statusMap($token)[$stand] ?? null;
        if (! in_array($state, ['frei', 'mein'], true)) {
            return false;
        }

        return DB::transaction(function () use ($season, $stand, $token) {
            DB::table('market_holds')->where('season', $season)->where('token', $token)->where('stand', '!=', $stand)->delete();
            DB::table('market_holds')->where('season', $season)->where('stand', $stand)->where('until', '<=', now())->delete();
            $existing = DB::table('market_holds')->where('season', $season)->where('stand', $stand)->first();
            if ($existing && ! hash_equals($existing->token, $token)) {
                return false;
            }
            DB::table('market_holds')->updateOrInsert(['season' => $season, 'stand' => $stand],
                ['token' => $token, 'until' => now()->addMinutes(self::HOLD_MINUTES), 'updated_at' => now(), 'created_at' => now()]);

            return true;
        });
    }

    public static function release(string $token): void
    {
        DB::table('market_holds')->where('token', $token)->delete();
    }
}
