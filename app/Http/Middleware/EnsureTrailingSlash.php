<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Die Altseite nutzt URLs mit Schrägstrich am Ende (/schmiedekurse/).
 * Laravel behandelt /schmiedekurse und /schmiedekurse/ als dieselbe Route –
 * ohne diese Middleware gäbe es zwei indexierbare Varianten.
 * Varianten ohne Schrägstrich werden per 301 auf die kanonische Form
 * umgeleitet; Query-Parameter (?kurs=, ?teilnehmer=) bleiben erhalten.
 */
class EnsureTrailingSlash
{
    /** Pfade, die NICHT angefasst werden (Technik, Verwaltung, Dateien). */
    private const EXCLUDED_PREFIXES = ['/livewire', '/filament', '/build', '/storage', '/fonts', '/up'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = $request->getPathInfo();

        if ($path === '/' || str_ends_with($path, '/') || $this->isExcluded($path)) {
            return $next($request);
        }

        // Dateien (robots.txt, sitemap.xml, favicon.ico …) nie umleiten.
        if (str_contains(basename($path), '.')) {
            return $next($request);
        }

        $query = $request->getQueryString();

        // Relativer Location-Header: kein Host-Header-Einfluss, Schrägstrich bleibt
        // erhalten (redirect()->to() würde ihn entfernen).
        return new RedirectResponse($path.'/'.($query ? '?'.$query : ''), 301);
    }

    private function isExcluded(string $path): bool
    {
        $prefixes = [...self::EXCLUDED_PREFIXES, '/'.trim(config('ulbrich_admin.path', 'verwaltung'), '/')];

        foreach ($prefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return true;
            }
        }

        return false;
    }
}
