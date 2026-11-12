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
                'a' => "Die Kurse beginnen bei {$fmt($min)} pro Person für den {$cheapest->name} ({$cheapest->duration_label}). Der Damast Premium-Kurs für ein Damastmesser bis 40 cm Gesamtlänge kostet ab {$fmt($max)} pro Person. Der Manager-Schmiedekurs wird auf Anfrage kalkuliert.",
            ];
        }

        return [...$faqs,
            ['q' => 'Wie lange dauert ein Schmiedekurs?', 'a' => 'Grundlagenkurs und Klingenkurs dauern einen Tag, der Damast-Messerkurs zwei Tage, der Damast Premium-Kurs zwei bis drei Tage und der Manager-Schmiedekurs drei Tage.'],
            ['q' => 'Kann ich einen Schmiedekurs verschenken?', 'a' => 'Ja, mit einem Schmiedekurs-Gutschein. Der Gutschein reserviert noch keinen Kursplatz: Die beschenkte Person bucht ihren Termin später selbst online.'],
            ['q' => 'Kann ich meinen Kurstermin verschieben?', 'a' => 'Zwei Umbuchungen sind kostenlos – bis 48 Stunden vor dem Kurstermin, online oder per E-Mail. Der gebuchte Preis bleibt gleich.'],
            ['q' => 'Wie buche ich einen Kurs?', 'a' => 'Kurse werden ausschließlich online gebucht. Im Buchungskalender wählst du Kurs, Termin und Teilnehmerzahl.'],
        ];
    }

    /** @return array<int, array{q: string, a: string}> */
    public static function voucher(): array
    {
        return [
            ['q' => 'Reserviert der Gutschein schon einen Kurstermin?', 'a' => 'Nein. Der Gutschein reserviert noch keinen Kursplatz – die beschenkte Person wählt Kurs und Termin später selbst und bucht online.'],
            ['q' => 'Kann der Kurstermin später noch verschoben werden?', 'a' => 'Ja. Zwei Umbuchungen sind kostenlos – bis 48 Stunden vor dem Kurstermin, online oder per E-Mail. Der gebuchte Preis bleibt gleich.'],
        ];
    }

    /** Allgemeines Handwerkswissen zu Damaststahl – keine betriebsspezifischen Angaben. */
    public static function damascus(): array
    {
        return [
            ['q' => 'Was ist Damaststahl?', 'a' => 'Damaststahl – genauer Schweißverbundstahl – entsteht, indem mindestens zwei unterschiedliche Stahlsorten in der Esse miteinander verschweißt, ausgeschmiedet und mehrfach gefaltet oder verdreht werden. Das typische Muster wird erst durch Ätzen sichtbar.'],
            ['q' => 'Woher kommt das Muster im Damast?', 'a' => 'Die verschiedenen Stahllagen reagieren beim Ätzen unterschiedlich stark auf die Säure. Dadurch erscheinen helle und dunkle Linien. Wie das Muster aussieht, hängt davon ab, wie der Schmied die Lagen faltet, verdreht oder einschneidet.'],
            ['q' => 'Ist Damaststahl besser als normaler Messerstahl?', 'a' => 'Nicht automatisch. Die Schneidleistung hängt vor allem von den verwendeten Stählen und der Wärmebehandlung ab. Damast verbindet gute Gebrauchseigenschaften mit einer Klinge, deren Muster jedes Messer einzigartig macht.'],
            ['q' => 'Rostet Damaststahl?', 'a' => 'Viele Damaststähle enthalten nicht rostfreie, kohlenstoffreiche Lagen und können Flugrost bilden. Nach dem Gebrauch abwaschen, sofort abtrocknen und die Klinge gelegentlich dünn einölen.'],
        ];
    }

    /** Allgemeine Messerpflege. */
    public static function care(): array
    {
        return [
            ['q' => 'Darf ein gutes Messer in die Spülmaschine?', 'a' => 'Nein. Hitze, aggressive Reiniger und das Anschlagen an anderem Geschirr schaden Schneide und Griff. Handgefertigte Messer immer von Hand spülen und sofort abtrocknen.'],
            ['q' => 'Welches Schneidbrett schont die Klinge?', 'a' => 'Holz oder Kunststoff. Glas, Stein, Keramik und Metall stumpfen die Schneide schnell ab oder lassen sie ausbrechen.'],
            ['q' => 'Wie oft muss ich mein Messer schärfen?', 'a' => 'Das hängt davon ab, wie oft und worauf du schneidest. Ein Zeichen ist, wenn das Messer über eine Tomatenhaut rutscht, statt sofort zu greifen. Zwischendurch hilft Abziehen, richtig nachschärfen geht mit einem Schleifstein.'],
            ['q' => 'Wie lagere ich Messer richtig?', 'a' => 'So, dass die Schneide nichts berührt: im Messerblock, an einer Magnetleiste oder in einer Scheide bzw. einem Klingenschutz – nicht lose in der Schublade.'],
        ];
    }
}

