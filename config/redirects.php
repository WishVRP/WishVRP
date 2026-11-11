<?php

/*
|--------------------------------------------------------------------------
| 301-Weiterleitungen (Redirect-Map)
|--------------------------------------------------------------------------
|
| Regel: Bestehende sprechende URLs bleiben 1:1 erhalten. 301 nur für
| Dubletten und Technik-URLs, immer direkt aufs Ziel (keine Ketten).
| Jede Änderung hier im Änderungsprotokoll docs/redirects.md begründen
| (Search-Console-Daten). Test: tests/Feature/RedirectsTest.php
|
| 'exact'  → vollständiger Pfad
| 'suffix' → beliebiges Verzeichnis + Dateiname, z. B. /shop/bedingungen.php
*/

return [

    'exact' => [
        '/index.html' => '/',
        '/index.php' => '/',
        '/shop.html' => '/shop/',
        '/rechtliches.html' => '/impressum/',
        '/shop/sonderanfertigung/' => '/sonderanfertigungen/',
        '/shop/sonderanfertigung' => '/sonderanfertigungen/',
        '/kursbuchung/regeln.php' => '/kursbedingungen/',
        '/shop/bedingungen/' => '/agb/',
        '/shop/bedingungen' => '/agb/',
        '/shop/widerruf/' => '/widerruf/',
        '/shop/widerruf' => '/widerruf/',

        // Live-Sitemap 11.11.2026: dünne Dubletten und Einzelprodukte (Fotos keine Studiobilder) → passende Sammelseite
        '/shop/einblicke/' => '/messerprojekte/',
        '/shop/einblicke' => '/messerprojekte/',
        '/shop/gutscheine/' => '/geschenke/',
        '/shop/gutscheine' => '/geschenke/',
        '/shop/messer/damast-warm/' => '/shop/damastmesser/',
        '/shop/messer/damast-warm' => '/shop/damastmesser/',
        '/shop/messer/damast-farbiger-griff/' => '/shop/damastmesser/',
        '/shop/messer/damast-farbiger-griff' => '/shop/damastmesser/',
        '/shop/messer/damast-gemaserter-griff/' => '/shop/damastmesser/',
        '/shop/messer/damast-gemaserter-griff' => '/shop/damastmesser/',
        '/shop/messer/damast-heller-griff/' => '/shop/damastmesser/',
        '/shop/messer/damast-heller-griff' => '/shop/damastmesser/',
        '/shop/messer/brotmesser-wellenschliff/' => '/shop/kuechenmesser/',
        '/shop/messer/brotmesser-wellenschliff' => '/shop/kuechenmesser/',
        '/shop/messer/kochmesser-klare-linie/' => '/shop/kuechenmesser/',
        '/shop/messer/kochmesser-klare-linie' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-dunkel/' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-dunkel' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-schlanke-form/' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-schlanke-form' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-schmal/' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-schmal' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-strukturiert/' => '/shop/kuechenmesser/',
        '/shop/messer/kuechenmesser-strukturiert' => '/shop/kuechenmesser/',
        '/shop/messer/messer-gravierte-scheide/' => '/shop/einzelstuecke/',
        '/shop/messer/messer-gravierte-scheide' => '/shop/einzelstuecke/',
    ],

    'suffix' => [
        '/bedingungen.php' => '/agb/',
        '/widerruf.php' => '/widerruf/',
        '/datenschutz.php' => '/datenschutz/',
    ],
];
