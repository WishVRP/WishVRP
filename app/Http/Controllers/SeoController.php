<?php

namespace App\Http\Controllers;

use App\Support\PageMeta;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = config('seo.indexable')
            ? ['User-agent: *', 'Allow: /', '', 'Sitemap: '.PageMeta::absolute('/sitemap.xml')]
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
}
