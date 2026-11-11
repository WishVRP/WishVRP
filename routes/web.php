<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
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
Route::post('/schmiedekurse/buchen/', [OrderController::class, 'booking'])->middleware('throttle:8,1')->name('booking.store');
Route::post('/anfrage/', [OrderController::class, 'inquiry'])->middleware('throttle:8,1')->name('inquiry.store');
Route::post('/weihnachtsaktion/start/', [XmasController::class, 'start'])->middleware('throttle:10,1')->name('xmas.start');
Route::post('/weihnachtsaktion/ergebnis/', [XmasController::class, 'finish'])->middleware('throttle:10,1')->name('xmas.finish');
Route::post('/e/', EventController::class)->middleware('throttle:120,1')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class)->name('event');

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
