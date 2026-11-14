<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wartungsmodus ohne SSH (MAINTENANCE=true in der .env): Besucher sehen nur den Wartungshinweis (HTTP 503,
 * damit Google die Seite nicht als neu/leer bewertet). Weiter erreichbar: Verwaltung, Impressum, Datenschutz.
 * Ansehen trotz Wartung: in der Verwaltung angemeldet sein oder einmal /?vorschau=MAINTENANCE_PASSWORD öffnen
 * (setzt ein Cookie für 30 Tage).
 */
class MaintenanceScreen
{
    private const COOKIE = 'ulbrich_preview';

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('ulbrich_admin.maintenance') || $request->is(...self::open())) {
            return $next($request);
        }

        $password = (string) config('ulbrich_admin.maintenance_password');
        $token = $password !== '' ? hash_hmac('sha256', 'vorschau', $password.config('app.key')) : null;

        if ($token && is_string($request->query('vorschau')) && hash_equals($password, $request->query('vorschau'))) {
            return redirect($request->url())->withCookie(cookie(self::COOKIE, $token, 60 * 24 * 30, '/', null, null, true, false, 'lax'));
        }
        if (($token && hash_equals($token, (string) $request->cookie(self::COOKIE))) || auth()->check()) {
            $response = $next($request);
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

            return $response;
        }

        return response()->view('maintenance', ['c' => config('ulbrich')], 503, [
            'Retry-After' => '86400',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** @return array<int, string> */
    private static function open(): array
    {
        $admin = trim((string) config('ulbrich_admin.path'), '/');

        return [$admin, $admin.'/*', 'livewire/*', 'livewire-*/*', 'filament/*', 'up', 'stripe/webhook', 'stripe/webhook/',
            'impressum', 'impressum/', 'datenschutz', 'datenschutz/'];
    }
}
