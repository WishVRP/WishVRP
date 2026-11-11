<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Support\Faqs;
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
        $maxBlade = $courses->max('total_length_cm') ?: 40;

        $meta = new PageMeta(
            // Vor Go-live mit dem aktuellen Live-Title abgleichen (10,15 % CTR nicht gefährden).
            title: 'Schmiedekurse & Messer seit 1934 | Messermanufaktur Ulbrich',
            description: 'Schmiedekurse bei Heilbronn: Schmiede dein eigenes Messer bei Messerschmied '.config('ulbrich.owner').' – Schmiedehandwerk seit '.config('ulbrich.heritage.since').' in der Familie, in '.config('ulbrich.heritage.generation').'. Generation.',
            path: '/',
        );

        $faqs = Faqs::courses($courses);

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
}
