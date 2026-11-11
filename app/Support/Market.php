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
            'start' => $s['start'] ?? null,   // Y-m-d, für Google (Veranstaltung)
            'end' => $s['end'] ?? null,
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

    /** @return array<int, array{q: string, a: string}> sichtbare FAQ = FAQPage-Daten */
    public static function faqs(): array
    {
        $m = config('ulbrich.market');
        $s = self::settings();
        $st = $m['stands'];
        $fees = $m['fees'];

        return [
            ['q' => 'Wann findet der Kunst- & Handwerkermarkt statt?', 'a' => $s['date']
                ? 'Der nächste Markt findet am '.$s['date'].' statt – an beiden Tagen ('.$m['days'].').'
                : 'Der Markt findet an einem Wochenende ('.$m['days'].') statt. Der nächste Termin wird noch bekannt gegeben – trag dich auf dieser Seite ein, dann sagen wir dir per E-Mail Bescheid.'],
            ['q' => 'Wo ist der Markt?', 'a' => 'In der '.$m['venue'].': '.$m['place'].', im Landkreis Heilbronn. '.$m['parking']],
            ['q' => 'Was gibt es auf dem Markt?', 'a' => 'Ausschließlich selbst hergestellte Kunst und Handwerk: '.implode(', ', $m['categories']).'. '.$m['catering']],
            ['q' => 'Wie viele Stände gibt es?', 'a' => '18 Ausstellerstände: '.$st[0]['numbers'].' in der Halle ('.$st[0]['size'].') und '.$st[1]['numbers'].' auf dem Außenplatz ('.$st[1]['size'].').'],
            ['q' => 'Was kostet ein Stand?', 'a' => $st[0]['label'].' '.$st[0]['price'].' €, '.$st[1]['label'].' '.$st[1]['price'].' € – jeweils für beide Markttage, zuzüglich '.$fees[0]['price'].' € Werbepauschale. Beleuchtung kostet nach Absprache '.$fees[1]['price'].' € für beide Tage.'],
            ['q' => 'Wie bewerbe ich mich als Aussteller?', 'a' => 'Im Online-Standplan einen freien Stand antippen – er ist dann '.self::HOLD_MINUTES.' Minuten für dich reserviert – und die Bewerbung mit Fotos deines Stands und deiner Arbeiten absenden. Du bekommst sofort deinen Aussteller-Code. Nach der Prüfung erhältst du den Standmietvertrag; verbindlich wird der Stand mit Vertrag und Zahlung.'],
            ['q' => 'Wann ist Aufbau und Abbau?', 'a' => $m['setup'].' '.$m['teardown']],
            ['q' => 'Was muss ich als Aussteller mitbringen?', 'a' => 'Aufbau, Verkaufs- und Dekorationsmaterial bringst du selbst mit. In der Halle stehen je nach Variante ein oder zwei Biertische bereit; draußen brauchst du einen eigenen Pavillon, der gegen Wind und Wetter gesichert ist.'],
            ['q' => 'Darf ich Lebensmittel verkaufen oder vorführen?', 'a' => 'Ja, aber nur nach vorheriger Anmeldung und Freigabe – das kannst du in der Bewerbung angeben. Offenes Feuer, Kerzen und Fackeln sind auf dem gesamten Gelände verboten.'],
            ['q' => 'Was passiert, wenn ich absagen muss?', 'a' => 'Bis 30 Tage vor dem ersten Markttag ist die Stornierung kostenlos, 15 bis 29 Tage vorher fallen 25 %, 8 bis 14 Tage vorher 50 % und ab 7 Tage vorher oder bei Nichterscheinen 100 % der Standgebühr an. Den Nachweis eines geringeren Schadens kannst du erbringen.'],
        ];
    }
}
