<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sicherheits-Header für die öffentliche Website.
 * Strikte CSP ohne 'unsafe-inline' für Skripte; JSON-LD und das kleine
 * Inline-Skript zur JS-Erkennung tragen eine Nonce.
 * Die Verwaltung (Filament/Livewire) bekommt eine eigene, gelockerte CSP
 * in AdminSecurityHeaders, weil Alpine.js Inline-Auswertung benötigt.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $nonce = Vite::cspNonce();

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);

        // Vite-Dev-Server (npm run dev) liefert von einem anderen Port aus.
        // Eine Antwort darf eine eigene CSP mitbringen (z. B. E-Mail-Vorschau in der Verwaltung).
        if (! Vite::isRunningHot() && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Alles außer Produktion ist nie indexierbar (Staging, lokale Vorschau).
        if (! config('seo.indexable')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
