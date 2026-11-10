<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Support\PageMeta;
use App\Support\Schema;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $courses = Course::query()->published()->orderBy('sort')->get();

        $dates = CourseDate::query()
            ->with('course')
            ->upcoming()
            ->limit(4)
            ->get();

        $minPrice = $courses->whereNotNull('price_from')->min('price_from');
        $maxBlade = $courses->max('blade_length_cm') ?: 40;

        $meta = new PageMeta(
            // Vor Go-live mit dem aktuellen Live-Title abgleichen (10,15 % CTR nicht gefährden).
            title: 'Schmiedekurse & Messer aus Neuenstadt am Kocher',
            description: 'Messer selbst schmieden – vom Grundlagenkurs bis zum Damast-Kurs – oder ein handgeschmiedetes Messer kaufen. Messermanufaktur Ulbrich bei Heilbronn.',
            path: '/',
        );

        $faqs = $this->faqs($courses, $minPrice);

        $schema = Schema::graph([
            Schema::localBusiness(),
            Schema::person(),
            Schema::website(),
            Schema::webPage($meta),
            Schema::faq($faqs),
        ]);

        $c = config('ulbrich');

        return view('pages.home', [
            ...compact('meta', 'schema', 'courses', 'dates', 'minPrice', 'maxBlade', 'faqs', 'c'),
            'e' => $c['edition'],
            'rating' => $c['google_rating'],
            'reviews' => $c['reviews'],
            'press' => $c['press'],
            // Gestaltete Platzhalter für Bewertungen/Presse nur außerhalb der Produktion.
            'isPreview' => ! app()->isProduction(),
        ]);
    }

    /**
     * Nur Fragen, deren Antworten durch echte Angaben gedeckt sind.
     * Dieselben Texte werden sichtbar ausgegeben und als FAQPage-Schema.
     *
     * @return array<int, array{q: string, a: string}>
     */
    private function faqs($courses, ?int $minPrice): array
    {
        $priced = $courses->whereNotNull('price_from');
        $max = $priced->max('price_from');
        $fmt = fn (int $n) => number_format($n, 0, ',', '.').' €';

        $faqs = [];

        if ($minPrice && $max) {
            $cheapest = $priced->firstWhere('price_from', $minPrice);
            $faqs[] = [
                'q' => 'Was kostet ein Schmiedekurs?',
                'a' => "Die Kurse beginnen bei {$fmt($minPrice)} pro Person für den {$cheapest->name} ({$cheapest->duration_label}). Der Damast Premium-Kurs mit bis zu 40 cm Klinge kostet ab {$fmt($max)} pro Person. Der Manager-Schmiedekurs wird auf Anfrage kalkuliert.",
            ];
        }

        $faqs[] = [
            'q' => 'Wie lange dauert ein Schmiedekurs?',
            'a' => 'Grundlagenkurs und Klingenkurs dauern einen Tag, der Damast-Messerkurs zwei Tage, der Damast Premium-Kurs zwei bis drei Tage und der Manager-Schmiedekurs drei Tage.',
        ];

        $faqs[] = [
            'q' => 'Kann ich einen Schmiedekurs verschenken?',
            'a' => 'Ja, mit einem Schmiedekurs-Gutschein. Der Gutschein reserviert noch keinen Kursplatz: Die beschenkte Person bucht ihren Termin später selbst online.',
        ];

        $faqs[] = [
            'q' => 'Kann ich meinen Kurstermin verschieben?',
            'a' => 'Umbuchen ist bis 7 Tage vor dem Kurstermin möglich.',
        ];

        $faqs[] = [
            'q' => 'Wie buche ich einen Kurs?',
            'a' => 'Kurse werden ausschließlich online gebucht. Im Buchungskalender wählst du Kurs, Termin und Teilnehmerzahl.',
        ];

        return $faqs;
    }
}
