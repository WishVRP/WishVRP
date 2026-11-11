<?php

namespace App\Support;

use App\Models\ShippingZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Versandkosten nach Zielland und Gewicht.
 *  1. Preistabelle aus der Verwaltung (DHL-Paket-Preise je Ländergruppe und Gewicht)
 *  2. Falls für das Land kein Tabellenpreis existiert und DHL Express verbunden ist: Live-Preis von DHL Express
 *  3. Sonst: „Versandkosten auf Anfrage“ (Bestellung als Anfrage, wir nennen die Kosten)
 * Kundenpreis immer aufgerundet auf ,99 (7,69 → 7,99; 7,00 → 7,99; 24,10 → 24,99).
 */
final class Shipping
{
    public static function roundUp(float $price): float
    {
        return round(floor(round($price, 2)) + 0.99, 2);
    }

    /** @return array<string, string> ISO-Code → deutscher Ländername, Deutschland zuerst */
    public static function countries(): array
    {
        $codes = ['AD','AE','AL','AR','AT','AU','BA','BE','BG','BR','CA','CH','CL','CN','CO','CY','CZ','DK','EE','EG','ES','FI','FO','FR','GB','GI','GR','HK','HR','HU','ID','IE','IL','IN','IS','IT','JP','KR','LI','LT','LU','LV','MA','MC','MD','ME','MK','MT','MX','MY','NL','NO','NZ','PE','PH','PL','PT','QA','RO','RS','SA','SE','SG','SI','SK','SM','TH','TR','TW','UA','US','UY','VA','VN','ZA'];
        $list = [];
        foreach ($codes as $c) {
            $list[$c] = class_exists(\Locale::class) ? (\Locale::getDisplayRegion('-'.$c, 'de') ?: $c) : $c;
        }
        asort($list, SORT_LOCALE_STRING);

        return ['DE' => 'Deutschland'] + $list;
    }

    public static function countryName(string $code): string
    {
        return self::countries()[strtoupper($code)] ?? strtoupper($code);
    }

    public static function zoneFor(string $country): ?ShippingZone
    {
        $country = strtoupper($country);
        $zones = ShippingZone::query()->where('active', true)->with('rates')->orderBy('sort')->get();

        return $zones->first(fn ($z) => in_array($country, $z->countries ?? [], true))
            ?? $zones->first(fn ($z) => $z->rest_of_world);
    }

    /**
     * @return array{price: ?float, label: string, source: ?string, customs: bool, country: string, country_name: string}
     */
    public static function quote(string $country, ?int $weightG = null, ?string $postalCode = null, ?string $city = null): array
    {
        $country = strtoupper(trim($country)) ?: 'DE';
        $weightG = max(1, $weightG ?: (int) config('ulbrich.sales.default_parcel_g', 1500));
        $zone = self::zoneFor($country);
        $base = ['country' => $country, 'country_name' => self::countryName($country), 'customs' => (bool) $zone?->customs];

        $rate = $zone?->rates->first(fn ($r) => $r->max_weight_g >= $weightG);
        if ($rate) {
            return $base + ['price' => $rate->customerPrice(), 'label' => $rate->label ?: 'DHL Paket', 'source' => 'tabelle'];
        }
        if ($country !== 'DE' && ($live = self::express($country, $weightG, $postalCode, $city)) !== null) {
            return $base + ['price' => self::roundUp($live), 'label' => 'DHL Express', 'source' => 'dhl_express'];
        }

        return $base + ['price' => null, 'label' => 'Versandkosten auf Anfrage', 'source' => null];
    }

    /** DHL Express (MyDHL API) – Live-Preis in EUR, nur mit Zugangsdaten. Zwischengespeichert 6 Stunden. */
    public static function express(string $country, int $weightG, ?string $postalCode, ?string $city): ?float
    {
        $cfg = config('services.dhl_express');
        if (empty($cfg['key']) || empty($cfg['secret']) || empty($cfg['account'])) {
            return null;
        }
        $key = 'dhlx:'.md5(implode('|', [$country, $weightG, $postalCode, $city]));

        return Cache::remember($key, now()->addHours(6), function () use ($cfg, $country, $weightG, $postalCode, $city) {
            $a = config('ulbrich.address');
            [$l, $w, $h] = config('ulbrich.sales.default_parcel_cm', [35, 25, 10]);
            try {
                $res = Http::timeout(8)->acceptJson()->withBasicAuth($cfg['key'], $cfg['secret'])
                    ->get('https://express.api.dhl.com/mydhlapi'.(! empty($cfg['test']) ? '/test' : '').'/rates', array_filter([
                        'accountNumber' => $cfg['account'],
                        'originCountryCode' => 'DE', 'originCityName' => $a['locality'], 'originPostalCode' => $a['postal_code'],
                        'destinationCountryCode' => $country, 'destinationCityName' => $city ?: null, 'destinationPostalCode' => $postalCode ?: null,
                        'weight' => round($weightG / 1000, 2), 'length' => $l, 'width' => $w, 'height' => $h,
                        'plannedShippingDate' => now()->addWeekday()->toDateString(),
                        'isCustomsDeclarable' => self::zoneFor($country)?->customs ? 'true' : 'false',
                        'unitOfMeasurement' => 'metric',
                    ], fn ($v) => $v !== null));
                if (! $res->successful()) {
                    Log::warning('DHL Express Rates', ['status' => $res->status(), 'detail' => $res->json('detail')]);

                    return null;
                }
                $prices = collect($res->json('products', []))->flatMap(fn ($p) => $p['totalPrice'] ?? [])
                    ->filter(fn ($t) => ($t['priceCurrency'] ?? '') === 'EUR' && ($t['price'] ?? 0) > 0);
                $billing = $prices->where('currencyType', 'BILLC');

                return ($billing->isNotEmpty() ? $billing : $prices)->min('price');
            } catch (\Throwable $e) {
                Log::warning('DHL Express Rates: '.$e->getMessage());

                return null;
            }
        });
    }

    /** Inlandspreis fürs Standardgewicht (Anzeige „ab …“, Google-Daten). Fallback: config. */
    public static function domestic(): float
    {
        try {
            return self::quote('DE')['price'] ?? (float) config('ulbrich.sales.shipping_de_eur');
        } catch (\Throwable) {
            return (float) config('ulbrich.sales.shipping_de_eur');
        }
    }

    public static function domesticLabel(): string
    {
        return 'ab '.number_format(self::domestic(), 2, ',', '.').' € Versand innerhalb Deutschlands (DHL, je nach Gewicht); weltweit je nach Land – die Kosten siehst du im Bestellformular';
    }
}
