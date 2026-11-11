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
        $urls = collect(config('pages'))
            ->filter(fn (array $p) => $p['status'] === 'live' && ($p['index'] ?? true))
            ->keys()
            ->map(fn (string $path) => PageMeta::absolute($path));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
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
            "- Buchung: Kurse werden online gebucht; Umbuchen bis 7 Tage vor dem Termin",
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
            '- '.$c['sales']['shipping_de'],
            '- '.$c['sales']['pickup_note'],
            '- '.$c['sales']['international'],
            '- '.$c['sales']['customs'],
            '- '.$c['sales']['delivery'],
            '- Zahlung: '.implode(', ', $c['sales']['payment']).'; vor Ort: '.implode(', ', $c['sales']['payment_onsite']),
            '- Garantie: '.$c['warranty']['years'].' Jahre '.$c['warranty']['condition'].'; dazu '.implode(' und ', $c['warranty']['included']),
            '- Messer schärfen lassen: vorbeibringen oder per Post',
            '- English pages: '.PageMeta::absolute('/en/'),
            '', '## Seiten',
        ];
        foreach (config('pages') as $path => $page) {
            if (($page['index'] ?? true) && $page['status'] === 'live') {
                $lines[] = '- ['.$page['title'].']('.PageMeta::absolute($path).')';
            }
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
