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
    ],

    'suffix' => [
        '/bedingungen.php' => '/agb/',
        '/widerruf.php' => '/widerruf/',
        '/datenschutz.php' => '/datenschutz/',
    ],
];
