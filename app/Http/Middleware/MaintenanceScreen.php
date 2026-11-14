<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wartungsmodus ohne SSH (MAINTENANCE=true in der .env): Besucher sehen nur den Wartungshinweis (HTTP 503,
 * damit Google die Seite nicht als neu/leer bewertet). Weiter erreichbar: Verwaltung, Impressum, Datenschutz.
 * Läuft ganz vorne (vor Session, Cookie-Verschlüsselung und Datenbank): Die Wartungsseite erscheint auch dann,
 * wenn Datenbank oder App-Schlüssel noch nicht eingerichtet sind.
 * Ansehen trotz Wartung: einmal /?vorschau=MAINTENANCE_PASSWORD öffnen (Cookie für 30 Tage).
 */
class MaintenanceScreen
{
    public const COOKIE = 'ulbrich_preview';

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('ulbrich_admin.maintenance') || $request->is(...self::open())) {
            return $next($request);
        }

        $password = (string) config('ulbrich_admin.maintenance_password');
        $token = $password !== '' ? hash_hmac('sha256', 'ulbrich-vorschau', $password) : null;
        $given = $request->query('vorschau');

        if ($token && is_string($given) && hash_equals($password, $given)) {
            $response = redirect($request->url() ?: '/');
            $response->headers->setCookie(Cookie::create(self::COOKIE, $token, now()->addDays(30), '/', null, $request->isSecure(), true, false, Cookie::SAMESITE_LAX));

            return $response;
        }
        if ($token && hash_equals($token, (string) $request->cookies->get(self::COOKIE))) {
            $response = $next($request);
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        $headers = ['Retry-After' => '86400', 'Cache-Control' => 'no-store', 'Content-Type' => 'text/html; charset=UTF-8'];
        try {
            return response(view('maintenance', ['c' => config('ulbrich')])->render(), 503, $headers);
        } catch (\Throwable) {
            return response(self::fallback(), 503, $headers);   // z. B. storage nicht beschreibbar
        }
    }

    /** @return array<int, string> */
    private static function open(): array
    {
        $admin = trim((string) config('ulbrich_admin.path'), '/');

        return [$admin, $admin.'/*', 'livewire/*', 'livewire-*/*', 'filament/*', 'up', 'stripe/webhook', 'stripe/webhook/',
            'impressum', 'impressum/', 'datenschutz', 'datenschutz/'];
    }

    private static function fallback(): string
    {
        $c = config('ulbrich');
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>Wir bauen um | '.$e($c['name']).'</title></head><body><main><h1>Wir bauen gerade um.</h1>'
            .'<p>Unsere neue Website ist in Kürze wieder für dich da. Bis dahin erreichst du uns telefonisch unter '
            .'<a href="tel:'.$e($c['phone']['e164']).'">'.$e($c['phone']['display']).'</a> oder per E-Mail an '
            .'<a href="mailto:'.$e($c['email']).'">'.$e($c['email']).'</a>.</p>'
            .'<p>'.$e($c['name']).' · '.$e($c['address']['street']).' · '.$e($c['address']['postal_code']).' '.$e($c['address']['locality']).'</p>'
            .'<p><a href="/impressum/">Impressum</a> · <a href="/datenschutz/">Datenschutz</a></p></main></body></html>';
    }
}
