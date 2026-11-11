<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseDate;
use App\Support\Faqs;
use App\Support\PageMeta;
use App\Support\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Alle Unterseiten aus config/pages.php (status 'live', mit 'view').
 * Meta, Breadcrumb und Grund-Schema kommen aus dem Register, seitenspezifisches
 * Schema (Kurse, Produkt, FAQ) wird hier ergänzt – nur mit sichtbaren Angaben.
 */
class PageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $path = $request->route()->defaults['pagePath'];
        $page = config('pages')[$path];

        $meta = new PageMeta(
            title: $page['meta_title'] ?? $page['title'],
            description: $page['description'],
            path: $path,
            index: $page['index'] ?? true,
            breadcrumb: $this->breadcrumb($path),
        );

        $c = config('ulbrich');
        $courses = Course::query()->published()->orderBy('sort')->get();
        $dates = CourseDate::query()->with('course')->upcoming()->get();
        $faqs = Faqs::courses($courses);

        $course = isset($page['course']) ? $courses->firstWhere('slug', $page['course']) : null;
        abort_if(isset($page['course']) && ! $course, 404);
        $content = isset($page['content']) ? json_decode(file_get_contents(resource_path('content/'.$page['content'].'.json')), true) : null;

        $extra = match (true) {
            $course !== null => [Schema::course($course), ($cf = json_decode(file_get_contents(resource_path('content/schmiedekurse__'.$course->slug.'.json')), true)['faq']) ? Schema::faq($cf) : null],
            ! empty($page['article']) => [Schema::article($page['h1'] ?? $content['h1'], $meta->canonical(), $page['description']), $content['faq'] ? Schema::faq($content['faq']) : null],
            $content !== null && ! empty($content['faq']) => [Schema::faq($content['faq'])],
            default => match ($path) {
            '/schmiedekurse/', '/schmiedekurse/vergleich/' => [Schema::courseList($courses), Schema::faq($faqs)],
            '/klappmesser/' => [Schema::product($c['edition'])],
            '/ratgeber/' => [
                Schema::article('Was kostet ein Schmiedekurs?', $meta->canonical().'#kosten', 'Preise aller Schmiedekurse der Messermanufaktur Ulbrich pro Person, vom Grundlagenkurs bis zum Damast Premium-Kurs.'),
                Schema::faq($faqs),
            ],
            '/ratgeber/damastmesser/' => [
                Schema::article('Damaststahl erklärt: Herstellung, Muster und Pflege', $meta->canonical(), $page['description']),
                Schema::faq(Faqs::damascus()),
            ],
            '/ratgeber/messer-pflege/' => [
                Schema::article('Messer pflegen: reinigen, schärfen, richtig lagern', $meta->canonical(), $page['description']),
                Schema::faq(Faqs::care()),
            ],
            '/sonderanfertigungen/' => [Schema::service('Messer-Sonderanfertigung', 'Messer nach Kundenwunsch: Klingenform, Länge und Griff werden abgestimmt und das Messer in der Werkstatt von Hand geschmiedet.', $meta->canonical())],
            '/geschenke/schmiedekurs-gutschein/' => [Schema::faq(Faqs::voucher())],
            '/schmiedekurse/heilbronn-region/', '/manufaktur/' => [Schema::localBusiness(), Schema::person()],
            default => [],
            },
        };

        $schema = Schema::graph([Schema::webPage($meta), Schema::breadcrumb($meta), ...$extra]);

        return view('pages.'.$page['view'], [
            ...compact('meta', 'schema', 'page', 'path', 'c', 'courses', 'dates', 'faqs', 'course'),
            'e' => $c['edition'],
            'rating' => $c['google_rating'],
            'minPrice' => $courses->whereNotNull('price_from')->min('price_from'),
            'maxBlade' => $courses->max('total_length_cm') ?: 40,
        ]);
    }

    /** @return array<int, array{name: string, path: string}> */
    private function breadcrumb(string $path): array
    {
        $pages = config('pages');
        $crumbs = [['name' => 'Startseite', 'path' => '/']];
        $acc = '/';
        foreach (array_filter(explode('/', $path)) as $segment) {
            $acc .= $segment.'/';
            if (isset($pages[$acc])) {
                $crumbs[] = ['name' => $pages[$acc]['title'], 'path' => $acc];
            }
        }

        return $crumbs;
    }
}
