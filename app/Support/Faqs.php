<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Häufige Fragen – nur solche, deren Antworten durch echte Angaben gedeckt sind.
 * Dieselben Texte werden sichtbar ausgegeben und als FAQPage-Schema.
 */
final class Faqs
{
    /** @return array<int, array{q: string, a: string}> */
    public static function courses(Collection $courses): array
    {
        $priced = $courses->whereNotNull('price_from');
        $min = $priced->min('price_from');
        $max = $priced->max('price_from');
        $fmt = fn (int $n) => number_format($n, 0, ',', '.').' €';

        $h = config('ulbrich.heritage');
        $faqs = [[
            'q' => 'Seit wann gibt es das Schmiedehandwerk in der Familie Ulbrich?',
            'a' => "Seit {$h['since']}. Messerschmied ".config('ulbrich.owner')." führt das Handwerk heute in {$h['generation']}. Generation fort – und gibt es in seinen Schmiedekursen weiter.",
        ]];

        if ($min && $max) {
            $cheapest = $priced->firstWhere('price_from', $min);
            $faqs[] = [
                'q' => 'Was kostet ein Schmiedekurs?',
                'a' => "Die Kurse beginnen bei {$fmt($min)} pro Person für den {$cheapest->name} ({$cheapest->duration_label}). Der Damast Premium-Kurs mit bis zu 40 cm Klinge kostet ab {$fmt($max)} pro Person. Der Manager-Schmiedekurs wird auf Anfrage kalkuliert.",
            ];
        }

        return [...$faqs,
            ['q' => 'Wie lange dauert ein Schmiedekurs?', 'a' => 'Grundlagenkurs und Klingenkurs dauern einen Tag, der Damast-Messerkurs zwei Tage, der Damast Premium-Kurs zwei bis drei Tage und der Manager-Schmiedekurs drei Tage.'],
            ['q' => 'Kann ich einen Schmiedekurs verschenken?', 'a' => 'Ja, mit einem Schmiedekurs-Gutschein. Der Gutschein reserviert noch keinen Kursplatz: Die beschenkte Person bucht ihren Termin später selbst online.'],
            ['q' => 'Kann ich meinen Kurstermin verschieben?', 'a' => 'Umbuchen ist bis 7 Tage vor dem Kurstermin möglich.'],
            ['q' => 'Wie buche ich einen Kurs?', 'a' => 'Kurse werden ausschließlich online gebucht. Im Buchungskalender wählst du Kurs, Termin und Teilnehmerzahl.'],
        ];
    }

    /** @return array<int, array{q: string, a: string}> */
    public static function voucher(): array
    {
        return [
            ['q' => 'Reserviert der Gutschein schon einen Kurstermin?', 'a' => 'Nein. Der Gutschein reserviert noch keinen Kursplatz – die beschenkte Person wählt Kurs und Termin später selbst und bucht online.'],
            ['q' => 'Kann der Kurstermin später noch verschoben werden?', 'a' => 'Ja, Umbuchen ist bis 7 Tage vor dem Kurstermin möglich.'],
        ];
    }
}
