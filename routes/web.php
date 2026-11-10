<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendingPageController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

/*
| URLs mit Schrägstrich am Ende wie auf der Altseite. Laravel matcht beide
| Varianten; EnsureTrailingSlash leitet die Variante ohne Slash per 301 um.
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

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
