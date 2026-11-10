<?php

/*
|--------------------------------------------------------------------------
| Seitenregister und URL-Inventar
|--------------------------------------------------------------------------
|
| Alle bestehenden sprechenden URLs der Live-Seite (Projektskill, Abschnitt 4)
| plus Rechtsseiten. Jede URL bleibt 1:1 erhalten.
|
| status:
|   'live'    → in der neuen Anwendung umgesetzt, in sitemap.xml
|   'planned' → noch nicht umgesetzt. In der lokalen Vorschau/Staging zeigt
|               die Route eine „In Arbeit“-Seite (noindex), in Produktion 404.
|               Go-live erst, wenn keine 'planned'-URL mehr übrig ist oder
|               die Altseite sie weiter ausliefert.
|
| gsc_clicks: Google-Klicks 09.08.–08.11.2026 (laut Jeremy). null = nicht
|   genannt; vollständiger Search-Console-Export folgt.
|
| Die 6 Kursseiten unter /schmiedekurse/… und die Ratgeber-/Shop-Unterseiten
| sind auf der Live-Seite vorhanden, ihre genauen Pfade liegen hier noch
| nicht vor → [FEHLT: URL-Liste aus Sitemap/Search-Console-Export].
*/

return [
    '/' => ['title' => 'Startseite', 'status' => 'live', 'gsc_clicks' => 294],

    '/schmiedekurse/' => ['title' => 'Schmiedekurse', 'status' => 'planned', 'gsc_clicks' => 22],
    '/schmiedekurse/vergleich/' => ['title' => 'Schmiedekurse im Vergleich', 'status' => 'planned', 'gsc_clicks' => null],
    '/schmiedekurse/heilbronn-region/' => ['title' => 'Schmiedekurs Region Heilbronn', 'status' => 'planned', 'gsc_clicks' => null],
    '/schmiedekurse/buchen/' => ['title' => 'Schmiedekurs buchen', 'status' => 'planned', 'gsc_clicks' => null],

    '/shop/' => ['title' => 'Shop', 'status' => 'planned', 'gsc_clicks' => 5],
    '/klappmesser/' => ['title' => 'Damast-Klappmesser Edition 100', 'status' => 'planned', 'gsc_clicks' => 1],
    '/sonderanfertigungen/' => ['title' => 'Messer anfertigen lassen', 'status' => 'planned', 'gsc_clicks' => null],
    '/messer-unikate/' => ['title' => 'Messer-Unikate', 'status' => 'planned', 'gsc_clicks' => null],
    '/messerprojekte/' => ['title' => 'Messerprojekte', 'status' => 'planned', 'gsc_clicks' => null],

    '/geschenke/' => ['title' => 'Geschenke', 'status' => 'planned', 'gsc_clicks' => null],
    '/geschenke/schmiedekurs-gutschein/' => ['title' => 'Schmiedekurs-Gutschein', 'status' => 'planned', 'gsc_clicks' => 1],
    '/erlebnisse/' => ['title' => 'Erlebnisse', 'status' => 'planned', 'gsc_clicks' => null],

    '/ratgeber/' => ['title' => 'Ratgeber', 'status' => 'planned', 'gsc_clicks' => null],
    '/manufaktur/' => ['title' => 'Manufaktur', 'status' => 'planned', 'gsc_clicks' => null],
    '/presse/' => ['title' => 'Presse', 'status' => 'planned', 'gsc_clicks' => null],
    '/partner/' => ['title' => 'Partner', 'status' => 'planned', 'gsc_clicks' => null],
    '/kunsthandwerkermarkt/' => ['title' => 'Kunsthandwerkermarkt', 'status' => 'planned', 'gsc_clicks' => null],

    '/impressum/' => ['title' => 'Impressum', 'status' => 'planned', 'gsc_clicks' => null, 'index' => false],
    '/datenschutz/' => ['title' => 'Datenschutz', 'status' => 'planned', 'gsc_clicks' => null, 'index' => false],
    '/agb/' => ['title' => 'AGB', 'status' => 'planned', 'gsc_clicks' => null, 'index' => false],
    '/widerruf/' => ['title' => 'Widerrufsbelehrung', 'status' => 'planned', 'gsc_clicks' => null, 'index' => false],
    '/kursbedingungen/' => ['title' => 'Kursbedingungen', 'status' => 'planned', 'gsc_clicks' => null, 'index' => false],
];
