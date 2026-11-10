<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 301 für Dubletten- und Technik-URLs der Altseite (config/redirects.php).
 * Läuft global vor dem Routing und vor EnsureTrailingSlash, damit jede
 * Alt-URL mit genau einem Sprung am Ziel landet.
 */
class LegacyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $target = self::resolve($request->getPathInfo());

        if ($target === null) {
            return $next($request);
        }

        $query = $request->getQueryString();

        // Relativer Location-Header: kein Host-Header-Einfluss, Schrägstrich bleibt
        // erhalten (redirect()->to() würde ihn entfernen).
        return new RedirectResponse($target.($query ? '?'.$query : ''), 301);
    }

    public static function resolve(string $path): ?string
    {
        $exact = config('redirects.exact', []);

        if (isset($exact[$path])) {
            return $exact[$path];
        }

        foreach (config('redirects.suffix', []) as $suffix => $target) {
            if (str_ends_with($path, $suffix)) {
                return $target;
            }
        }

        return null;
    }
}
