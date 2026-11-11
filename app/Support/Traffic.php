<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Einordnung eines Aufrufs ohne personenbezogene Daten zu speichern. */
final class Traffic
{
    private const BOTS = '/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|facebookexternalhit|embedly|http-client|okhttp|go-http/i';

    public static function shouldTrack(Request $request): bool
    {
        if (! config('analytics.enabled')) {
            return false;
        }
        $ua = (string) $request->userAgent();

        return $ua !== ''
            && ! preg_match(self::BOTS, $ua)
            && $request->header('DNT') !== '1'
            && $request->header('Sec-GPC') !== '1'
            && ! in_array(strtolower((string) ($request->header('Sec-Purpose') ?? $request->header('Purpose'))), ['prefetch', 'prerender'], true);
    }

    /** @return array{source: string, host: ?string, entry: bool} */
    public static function source(Request $request): array
    {
        $ref = (string) $request->headers->get('referer');
        $host = $ref ? strtolower((string) parse_url($ref, PHP_URL_HOST)) : null;
        $own = strtolower($request->getHost());
        $canonical = strtolower((string) parse_url((string) config('seo.canonical_host'), PHP_URL_HOST));
        $host = $host ? preg_replace('/^(www\.|m\.|l\.|lm\.)/', '', $host) : null;

        if ($host && ($host === preg_replace('/^www\./', '', $own) || $host === preg_replace('/^www\./', '', $canonical))) {
            return ['source' => 'intern', 'host' => null, 'entry' => false];
        }
        $source = match (true) {
            ! $host => 'direkt',
            (bool) preg_match('/(^|\.)(google|bing|duckduckgo|ecosia|yahoo|startpage|qwant)\./', $host) => 'suche',
            str_contains($host, 'instagram') => 'instagram',
            str_contains($host, 'facebook') => 'facebook',
            (bool) preg_match('/chatgpt|openai|perplexity|claude|gemini|copilot/', $host) => 'ki',
            default => 'andere',
        };

        return ['source' => $source, 'host' => $host, 'entry' => true];
    }

    public static function device(Request $request): string
    {
        $ua = (string) $request->userAgent();

        return match (true) {
            (bool) preg_match('/iPad|Tablet|Nexus 7|Nexus 10|SM-T/i', $ua) => 'tablet',
            (bool) preg_match('/Mobi|Android|iPhone/i', $ua) => 'mobil',
            default => 'desktop',
        };
    }
}
