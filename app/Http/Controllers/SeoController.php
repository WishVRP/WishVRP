<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\PageMeta;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = config('seo.indexable')
            ? [
                'User-agent: *', 'Allow: /', '',
                // KI-Suche und -Assistenten ausdrücklich zulassen (GEO): sie sollen die Fakten korrekt zitieren können.
                '# KI-Crawler', 'User-agent: GPTBot', 'User-agent: OAI-SearchBot', 'User-agent: ChatGPT-User',
                'User-agent: ClaudeBot', 'User-agent: Claude-SearchBot', 'User-agent: PerplexityBot',
                'User-agent: Google-Extended', 'User-agent: Applebot-Extended', 'User-agent: CCBot', 'Allow: /', '',
                'Sitemap: '.PageMeta::absolute('/sitemap.xml'),
            ]
            // Staging/Vorschau: komplett gesperrt (zusätzlich X-Robots-Tag noindex).
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Nur umgesetzte, indexierbare Seiten – keine Weiterleitungen, keine Platzhalter. */
    public function sitemap(): Response
    {
        // Bild-Sitemap: Fotos je Seite (Google-Bildersuche). Startseite zeigt alle Manufaktur-Fotos.
        $photos = collect(config('fotos'))->keys()->map(fn (string $k) => PageMeta::absolute(\App\Support\Foto::largest($k)))->all();
        $urls = collect(config('pages'))
            ->filter(fn (array $p) => $p['status'] === 'live' && \App\Support\PageIndex::indexable($p))
            ->mapWithKeys(fn (array $p, string $path) => [PageMeta::absolute($path) => $path === '/' ? $photos : []])
            ->merge(\App\Models\Product::query()->visible()->get()->mapWithKeys(fn ($p) => [
                PageMeta::absolute($p->url()) => array_map(fn ($i) => PageMeta::absolute($i), $p->imageUrls()),
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Produkt-Feed für Google Merchant Center (kostenlose Einträge im Shopping-Tab).
     * In Merchant Center als geplanten Abruf hinterlegen: https://messermanufaktur-ulbrich.de/produkte.xml
     */
    public function merchantFeed(): Response
    {
        $items = \App\Models\Product::query()->visible()->get()->map(function (\App\Models\Product $p) {
            $desc = $p->short ?: \Illuminate\Support\Str::limit(strip_tags(\Illuminate\Support\Str::markdown((string) $p->description)), 4900);
            $img = $p->imageUrls();

            return [
                'id' => 'ULB-'.$p->id,
                'title' => mb_substr($p->name, 0, 150),
                'description' => $desc ?: $p->name,
                'link' => PageMeta::absolute($p->url()),
                'image' => $img ? PageMeta::absolute($img[0]) : null,
                'extra' => array_map(fn ($u) => PageMeta::absolute($u), array_slice($img, 1, 9)),
                'price' => number_format((float) $p->price, 2, '.', '').' EUR',
                'availability' => $p->available() ? 'in_stock' : 'out_of_stock',
                'type' => 'Messer > '.$p->categoryName(),
            ];
        })->filter(fn ($i) => $i['image']);   // Google verlangt ein Bild

        return response()->view('seo.merchant-feed', ['items' => $items, 'shipping' => number_format(\App\Support\Shipping::domestic(), 2, '.', '').' EUR'])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * llms.txt: kompakte, faktische Zusammenfassung für KI-Suchsysteme (GEO).
     * Nur Angaben aus config/ulbrich.php und der Kursdatenbank – nichts Zusätzliches.
     */
    public function llms(): Response
    {
        $c = config('ulbrich');
        $h = $c['heritage'];
        $a = $c['address'];
        $fmt = fn (?int $n) => $n ? 'ab '.number_format($n, 0, ',', '.').' € pro Person' : 'Preis auf Anfrage';

        $lines = [
            '# '.$c['name'],
            '',
            "> Messerschmied {$c['owner']} bietet Schmiedekurse und handgefertigte Messer in {$a['district']} ({$a['locality']}, Landkreis Heilbronn). Das Schmiedehandwerk ist seit {$h['since']} in der Familie; {$c['owner']} führt es in {$h['generation']}. Generation fort.",
            '',
            '## Fakten',
            "- Inhaber: {$c['owner']}, {$c['owner_role']}",
            "- Mitglied: {$c['chamber']}",
            "- Familientradition: seit {$h['since']}, {$h['generation']}. Generation",
            "- Adresse: {$a['street']}, {$a['postal_code']} {$a['locality']} (Ortsteil {$a['district']})",
            "- Telefon: {$c['phone']['display']}",
            ...($c['email'] ? ["- E-Mail: {$c['email']}"] : []),
            ...array_map(fn ($u) => "- Profil: {$u}", $c['same_as']),
            "- Buchung: Kurse werden online gebucht; zwei kostenlose Umbuchungen bis 48 Stunden vor dem Termin, Preis bleibt; gemeinsame Buchungen bis 4 Personen; bezahlter Buchungswert 10 Jahre verwendbar",
            '',
            '## Schmiedekurse',
        ];
        foreach (Course::query()->published()->orderBy('sort')->get() as $course) {
            $lines[] = '- '.$course->name.': '.collect([$course->duration_label, $course->total_length_cm ? "Messer bis {$course->total_length_cm} cm Gesamtlänge" : null, $fmt($course->price_from)])->filter()->implode(', ');
        }
        $e = $c['edition'];
        $lines = [...$lines, '', '## Messer',
            "- {$e['name']}: limitiert auf {$e['total']} nummerierte Stücke ({$e['regular']} regulär zu je {$e['price']} €, {$e['special']} Sonderstücke)",
            '- Messer-Unikate und Sonderanfertigungen auf Anfrage; Formen u. a. '.implode(', ', $c['sales']['knife_forms']),
            '- Leistungen: '.implode(', ', $c['sales']['services']),
            '', '## Versand, Abholung, Zahlung',
            '- '.\App\Support\Shipping::domesticLabel(),
            '- '.$c['sales']['pickup_note'],
            '- '.$c['sales']['international'],
            '- '.$c['sales']['customs'],
            '- '.$c['sales']['delivery'],
            '- Zahlung: '.implode(', ', $c['sales']['payment']).'; vor Ort: '.implode(', ', $c['sales']['payment_onsite']),
            '- '.$c['warranty']['name'].': '.$c['warranty']['text'].' '.$c['warranty']['statutory_note'].' Dazu: '.implode(' und ', $c['warranty']['included']),
            '- Messer schärfen lassen: vorbeibringen oder per Post',
            '- English pages: '.PageMeta::absolute('/en/'),
            ...(($products = \App\Models\Product::query()->where('status', 'aktiv')->get())->isNotEmpty() ? ['', '## Messer im Shop', ...$products->map(fn ($p) => '- ['.$p->name.']('.PageMeta::absolute($p->url()).'): '.number_format((float) $p->price, 2, ',', '.').' €')->all()] : []),
            '', '## Buchung', '- Schmiedekurse: jeder Tag bis 5 Jahre im Voraus buchbar, ein Kurs pro Tag, freie Tage im Kalender: '.PageMeta::absolute('/schmiedekurse/buchen/'),
            '', '## Kunst- & Handwerkermarkt',
            '- '.$c['market']['name'].' in der '.$c['market']['venue'].', '.$c['market']['place'].' (Landkreis Heilbronn): '.$c['market']['days'].', 18 Stände (8 in der Halle, 10 außen), nur selbst hergestellte Waren',
            '- Termin: '.(\App\Support\Market::settings()['date'] ?: 'wird bekannt gegeben'),
            '- Aussteller: Stand ab '.min(array_column($c['market']['stands'], 'price')).' € für beide Tage, Bewerbung über den Online-Standplan: '.PageMeta::absolute('/kunsthandwerkermarkt/'),
            '', '## Seiten',
        ];
        foreach (config('pages') as $path => $page) {
            if (\App\Support\PageIndex::indexable($page) && $page['status'] === 'live') {
                $lines[] = '- ['.$page['title'].']('.PageMeta::absolute($path).')';
            }
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
