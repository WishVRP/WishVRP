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
Route::get('/schmiedekurse/verfuegbarkeit/', [OrderController::class, 'availability'])->middleware('throttle:events')->name('booking.availability');
Route::post('/schmiedekurse/buchen/', [OrderController::class, 'booking'])->middleware('throttle:forms')->name('booking.store');
Route::post('/gutschein/kaufen/', [OrderController::class, 'voucher'])->middleware('throttle:forms')->name('voucher.buy');
Route::get('/versandkosten/', [\App\Http\Controllers\ShippingController::class, 'quote'])->middleware('throttle:events')->name('shipping.quote');
Route::get('/kunsthandwerkermarkt/staende/', [\App\Http\Controllers\MarketController::class, 'stands'])->middleware('throttle:events')->name('market.stands');
Route::post('/kunsthandwerkermarkt/reservieren/', [\App\Http\Controllers\MarketController::class, 'reserve'])->middleware('throttle:events')->name('market.reserve');
Route::post('/kunsthandwerkermarkt/termin/', [\App\Http\Controllers\MarketController::class, 'notify'])->middleware('throttle:forms')->name('market.notify');
Route::post('/kunsthandwerkermarkt/bewerben/', [\App\Http\Controllers\MarketController::class, 'apply'])->middleware('throttle:forms')->name('market.apply');
Route::post('/buchung/umbuchen/', [\App\Http\Controllers\RebookController::class, 'store'])->middleware('throttle:forms')->name('rebook.store');
Route::post('/widerrufen/pruefen/', [\App\Http\Controllers\WithdrawalController::class, 'review'])->middleware('throttle:forms')->name('withdraw.review');
Route::post('/widerrufen/', [\App\Http\Controllers\WithdrawalController::class, 'store'])->middleware('throttle:forms')->name('withdraw.store');
Route::post('/anfrage/', [OrderController::class, 'inquiry'])->middleware('throttle:forms')->name('inquiry.store');
Route::post('/weihnachtsaktion/anmelden/', [XmasController::class, 'register'])->middleware('throttle:xmas')->name('xmas.register');
Route::post('/weihnachtsaktion/code/', [XmasController::class, 'verify'])->middleware('throttle:xmas')->name('xmas.verify');
Route::post('/weihnachtsaktion/start/', [XmasController::class, 'start'])->middleware('throttle:xmas')->name('xmas.start');
Route::post('/weihnachtsaktion/ergebnis/', [XmasController::class, 'finish'])->middleware('throttle:xmas')->name('xmas.finish');
Route::post('/newsletter/bestaetigen/', [NewsletterController::class, 'confirm'])->middleware('throttle:forms')->name('newsletter.confirm');
Route::post('/newsletter/abmelden/', [NewsletterController::class, 'unsubscribe'])->middleware('throttle:forms')->name('newsletter.unsubscribe');
Route::post('/newsletter/abmelden/sofort/', [NewsletterController::class, 'oneClick'])->middleware('throttle:events')
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)->name('newsletter.oneclick');
// Fotos aus Markt-Bewerbungen (privat gespeichert, nur für angemeldete Verwaltung)
Route::get('/'.trim(config('ulbrich_admin.path'), '/').'/markt-foto/{application}/{index}', function (\App\Models\MarketApplication $application, int $index) {
    abort_unless(auth()->check(), 403);
    $path = $application->photos[$index] ?? null;
    abort_unless($path && \Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

    return \Illuminate\Support\Facades\Storage::disk('local')->response($path, null, ['X-Robots-Tag' => 'noindex', 'Cache-Control' => 'private, no-store']);
})->middleware('web')->whereNumber('index')->name('market.photo');

// Vorschau der Kunden-E-Mails zu einem Vorgang (nur angemeldet; nichts wird verschickt)
Route::get('/'.trim(config('ulbrich_admin.path'), '/').'/mail-vorschau/{order}/{kind}', function (\App\Models\Order $order, string $kind) {
    abort_unless(auth()->check(), 403);
    abort_unless(isset(\App\Mail\CustomerUpdateMail::KINDS[$kind]), 404);
    $preview = $order->replicate();
    $preview->number = $order->number;
    if ($kind === 'versendet' && ! $preview->tracking_number) {
        $preview->forceFill(['tracking_carrier' => 'dhl', 'tracking_number' => '00340434161094000000']);
    }
    $preview->production_time ??= 'ca. 3–4 Wochen';

    return response((new \App\Mail\CustomerUpdateMail($preview, $kind, request('nachricht')))->render())
        ->header('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'self'")
        ->header('X-Robots-Tag', 'noindex, nofollow');
})->middleware('web')->whereIn('kind', array_keys(\App\Mail\CustomerUpdateMail::KINDS))->name('order.mail-preview');

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

Route::post('/stripe/webhook/', \App\Http\Controllers\StripeWebhookController::class)
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)->name('stripe.webhook');

// Produktseiten unter den bestehenden Shop-Kategorien (Ausnahme vom statischen Seitenregister: Produkte kommen aus der Datenbank)
$shopCategories = 'damastmesser|kuechenmesser|jagdmesser|einzelstuecke|ringe';
Route::get('/shop/{category}/{slug}/', [\App\Http\Controllers\ProductController::class, 'show'])->where(['category' => $shopCategories, 'slug' => '[a-z0-9-]+'])->name('product.show');
Route::post('/shop/{category}/{slug}/', [\App\Http\Controllers\ProductController::class, 'buy'])->where(['category' => $shopCategories, 'slug' => '[a-z0-9-]+'])->middleware('throttle:forms')->name('product.buy');
Route::get('/produkte.xml', [SeoController::class, 'merchantFeed'])->name('merchant.feed');

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
