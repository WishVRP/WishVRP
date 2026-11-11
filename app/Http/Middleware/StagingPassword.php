<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Testumgebung (z. B. test.messermanufaktur-ulbrich.de): ganze Website hinter einem Passwort.
 * Aktiv nur, wenn STAGING_PASSWORD gesetzt ist und nicht in Produktion. Stripe-Webhook bleibt erreichbar.
 */
class StagingPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $password = (string) config('ulbrich_admin.staging_password');
        if ($password === '' || app()->isProduction() || $request->is('stripe/webhook', 'stripe/webhook/', 'up')) {
            return $next($request);
        }
        $user = (string) config('ulbrich_admin.staging_user');
        if (hash_equals($user, (string) $request->getUser()) && hash_equals($password, (string) $request->getPassword())) {
            $response = $next($request);
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

            return $response;
        }

        return response('Testumgebung – bitte anmelden.', 401, [
            'WWW-Authenticate' => 'Basic realm="Testumgebung Ulbrich", charset="UTF-8"',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'no-store',
        ]);
    }
}
