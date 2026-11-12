<?php

namespace App\Support;

/**
 * Zoll und Einfuhrabgaben je Ländergruppe (Verwaltung → Versandkosten):
 *  - empfaenger (Standard, DAP): Empfänger zahlt bei Zustellung → nur Hinweis
 *  - vorab (DDP): wir berechnen Zoll + Einfuhrumsatzsteuer + Pauschale und schlagen sie auf
 * Sätze trägt der Inhaber nach Auskunft von Zoll/Versanddienst ein; Ergebnis aufgerundet auf ,99.
 */
final class Customs
{
    /** @return array{mode: string, amount: ?float, label: ?string} */
    public static function estimate(string $country, float $goods, float $shipping): array
    {
        $zone = Shipping::zoneFor($country);
        if (! $zone || ! $zone->customs) {
            return ['mode' => 'keine', 'amount' => null, 'label' => null];
        }
        if ($zone->customs_mode !== 'vorab') {
            return ['mode' => 'empfaenger', 'amount' => null, 'label' => 'Zoll und Einfuhrabgaben im Zielland zahlt der Empfänger bei Zustellung.'];
        }
        $duty = round($goods * (float) $zone->duty_percent / 100, 2);
        $vat = round(($goods + $shipping + $duty) * (float) $zone->vat_percent / 100, 2);
        $sum = $duty + $vat + (float) $zone->customs_fee;

        return ['mode' => 'vorab', 'amount' => $sum > 0 ? Shipping::roundUp($sum) : 0.0,
            'label' => 'Zoll und Einfuhrabgaben im Voraus bezahlt (Schätzung: Zoll '.self::pct($zone->duty_percent).', Einfuhrsteuer '.self::pct($zone->vat_percent).($zone->customs_fee ? ', Abwicklung '.number_format((float) $zone->customs_fee, 2, ',', '.').' €' : '').')'];
    }

    private static function pct($v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',').' %';
    }
}
