<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PendingPageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\XmasController;
use Illuminate\Support\Facades\Route;

/*
| URLs mit Schrägstrich am Ende wie auf der Altseite. Laravel matcht beide
| Varianten; EnsureTrailingSlash leitet die Variante ohne Slash per 301 um.
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

/*
| Formulare (Buchung, Bestellung/Anfrage) und Klick-Zählung. Keine Online-Zahlung.
*/
Route::post('/schmiedekurse/buchen/', [OrderController::class, 'booking'])->middleware('throttle:forms')->name('booking.store');
Route::post('/anfrage/', [OrderController::class, 'inquiry'])->middleware('throttle:forms')->name('inquiry.store');
Route::post('/weihnachtsaktion/anmelden/', [XmasController::class, 'register'])->middleware('throttle:xmas')->name('xmas.register');
Route::post('/weihnachtsaktion/code/', [XmasController::class, 'verify'])->middleware('throttle:xmas')->name('xmas.verify');
Route::post('/weihnachtsaktion/start/', [XmasController::class, 'start'])->middleware('throttle:xmas')->name('xmas.start');
Route::post('/weihnachtsaktion/ergebnis/', [XmasController::class, 'finish'])->middleware('throttle:xmas')->name('xmas.finish');
Route::post('/newsletter/bestaetigen/', [NewsletterController::class, 'confirm'])->middleware('throttle:forms')->name('newsletter.confirm');
Route::post('/newsletter/abmelden/', [NewsletterController::class, 'unsubscribe'])->middleware('throttle:forms')->name('newsletter.unsubscribe');
Route::post('/newsletter/abmelden/sofort/', [NewsletterController::class, 'oneClick'])->middleware('throttle:events')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)->name('newsletter.oneclick');
// E-Mail-Vorschau für die Verwaltung (nur angemeldet; eigene CSP, weil E-Mails Inline-Styles brauchen)
Route::get('/'.trim(config('ulbrich_admin.path'), '/').'/newsletter-vorschau/{issue}', function (\App\Models\NewsletterIssue $issue) {
    abort_unless(auth()->check(), 403);
    $sub = new \App\Models\NewsletterSubscriber(['email' => 'vorschau@example.org', 'unsubscribe_token' => str_repeat('x', 48)]);
    $sub->confirmed_at = now();
    $code = $issue->discount() ? new \App\Models\DiscountCode(['code' => 'NEWS-BEISPIEL', 'percent' => $issue->discount(), 'valid_until' => now()->addDays($issue->discount_valid_days ?: 30)]) : null;

    return response((new \App\Mail\NewsletterMail($issue, $sub, $code))->render())
        ->header('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'self'")
        ->header('X-Robots-Tag', 'noindex, nofollow');
})->middleware('web')->name('newsletter.preview');

Route::post('/e/', EventController::class)->middleware('throttle:events')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)->name('event');

/*
| Umgesetzte Unterseiten aus dem Seitenregister (config/pages.php).
*/
foreach (config('pages') as $path => $page) {
    if ($page['status'] === 'live' && isset($page['view'])) {
        Route::get($path, PageController::class)->defaults('pagePath', $path);
    }
}

/*
| Noch nicht umgesetzte Bestandsseiten (config/pages.php, status 'planned'):
| In Vorschau/Staging eine „In Arbeit“-Seite (noindex), damit Navigation
| und interne Links prüfbar sind. In Produktion nicht registriert.
*/
if (! app()->isProduction()) {
    foreach (config('pages') as $path => $page) {
        if ($page['status'] === 'planned') {
            Route::get($path, PendingPageController::class)->defaults('pagePath', $path);
        }
    }
}
