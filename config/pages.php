<?php

/*
|--------------------------------------------------------------------------
| Seitenregister und URL-Inventar
|--------------------------------------------------------------------------
|
| Alle bestehenden sprechenden URLs der Live-Seite plus Rechtsseiten.
| Jede URL bleibt 1:1 erhalten.
|
| status: 'live' → umgesetzt, Route aktiv, in sitemap.xml (wenn index ≠ false)
|         'planned' → nur Vorschau-Platzhalter außerhalb der Produktion
| view:   Blade-View unter resources/views/pages/
| meta:   Title (ohne Suffix) und Description (140–160 Zeichen, per Test geprüft)
| gsc_clicks: Google-Klicks 09.08.–08.11.2026 (laut Jeremy), null = nicht genannt.
|
| Die 6 einzelnen Kursseiten und die Ratgeber-/Shop-Unterseiten der Live-Seite
| haben unbekannte Pfade. Bis die URL-Liste (Sitemap/Search Console) vorliegt,
| stehen die Kurse als Anker auf /schmiedekurse/ (#grundlagenkurs …) –
| es werden bewusst keine neuen, geratenen URLs angelegt.
*/

return [
    '/' => ['title' => 'Startseite', 'status' => 'live', 'gsc_clicks' => 294],

    '/schmiedekurse/' => [
        'title' => 'Schmiedekurse', 'status' => 'live', 'gsc_clicks' => 22, 'view' => 'courses.index',
        'meta_title' => 'Schmiedekurse bei Heilbronn – Messer schmieden',
        'description' => 'Sechs Schmiedekurse in der Messermanufaktur Ulbrich: vom eintägigen Grundlagenkurs bis zum Damast Premium-Kurs. Preise, Dauer und Klingenlänge im Überblick.',
    ],
    '/schmiedekurse/vergleich/' => [
        'title' => 'Schmiedekurse im Vergleich', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.compare',
        'meta_title' => 'Schmiedekurse im Vergleich – Dauer, Klinge, Preis',
        'description' => 'Alle Schmiedekurse der Messermanufaktur Ulbrich nebeneinander: Dauer, maximale Klingenlänge und Einstiegspreis. So findest du den Kurs, der zu dir passt.',
    ],
    '/schmiedekurse/heilbronn-region/' => [
        'title' => 'Schmiedekurs Region Heilbronn', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.region',
        'meta_title' => 'Schmiedekurs Region Heilbronn: Werkstatt, Anfahrt',
        'description' => 'Schmiedekurs im Landkreis Heilbronn: Die Werkstatt der Messermanufaktur Ulbrich liegt in Stein am Kocher bei Neuenstadt. Kurse, Anfahrt und Kontakt.',
    ],
    '/schmiedekurse/buchen/' => [
        'title' => 'Schmiedekurs buchen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.book', 'index' => false,
        'meta_title' => 'Schmiedekurs buchen',
        'description' => 'Schmiedekurs bei der Messermanufaktur Ulbrich buchen: Kurs wählen, Teilnehmerzahl angeben und Termin aussuchen. Der Gesamtpreis wird direkt berechnet.',
    ],

    '/shop/' => [
        'title' => 'Shop', 'status' => 'live', 'gsc_clicks' => 5, 'view' => 'shop.index',
        'meta_title' => 'Shop – handgefertigte Messer und Gutscheine',
        'description' => 'Handgefertigte Messer aus der Messermanufaktur Ulbrich: Damast-Klappmesser der Edition 100, Unikate und Anfertigungen sowie Gutscheine für Schmiedekurse.',
    ],
    '/klappmesser/' => [
        'title' => 'Damast-Klappmesser Edition 100', 'status' => 'live', 'gsc_clicks' => 1, 'view' => 'shop.edition',
        'meta_title' => 'Damast-Klappmesser Edition 100 – limitiert',
        'description' => 'Damast-Klappmesser der Edition 100: limitiert auf 100 nummerierte Stücke, 90 reguläre Messer zu je 699 € und 10 Sonderstücke. Handgefertigt von Andreas Ulbrich.',
    ],
    '/sonderanfertigungen/' => [
        'title' => 'Messer anfertigen lassen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.custom',
        'meta_title' => 'Messer anfertigen lassen – Sonderanfertigung',
        'description' => 'Ein Messer nach deinen Vorstellungen: Messerschmied Andreas Ulbrich fertigt Klingenform, Länge und Griff auf Anfrage. So läuft eine Sonderanfertigung ab.',
    ],
    '/messer-unikate/' => [
        'title' => 'Messer-Unikate', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.unique',
        'meta_title' => 'Messer-Unikate – Einzelstücke aus der Werkstatt',
        'description' => 'Messer-Unikate der Messermanufaktur Ulbrich: handgefertigte Einzelstücke mit Damastklinge und eigenem Griff. Verfügbare Stücke und Preise gibt es auf Anfrage.',
    ],
    '/messerprojekte/' => [
        'title' => 'Messerprojekte', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.projects',
        'meta_title' => 'Messerprojekte – Arbeiten aus der Manufaktur',
        'description' => 'Messerprojekte aus der Messermanufaktur Ulbrich: Klappmesser mit Damastklinge, verschiedene Griffe und Details. Ein Einblick in bisher entstandene Arbeiten.',
    ],

    '/geschenke/' => [
        'title' => 'Geschenke', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'gifts.index',
        'meta_title' => 'Geschenke für Messerfreunde und Selbermacher',
        'description' => 'Geschenkideen aus der Messermanufaktur Ulbrich: ein Schmiedekurs-Gutschein zum Selberschmieden oder ein nummeriertes Damast-Klappmesser der Edition 100.',
    ],
    '/geschenke/schmiedekurs-gutschein/' => [
        'title' => 'Schmiedekurs-Gutschein', 'status' => 'live', 'gsc_clicks' => 1, 'view' => 'gifts.voucher',
        'meta_title' => 'Schmiedekurs-Gutschein – Messer selbst schmieden',
        'description' => 'Einen Schmiedekurs verschenken: Mit dem Gutschein der Messermanufaktur Ulbrich wählt die beschenkte Person Kurs und Termin später selbst und bucht online.',
    ],
    '/erlebnisse/' => [
        'title' => 'Erlebnisse', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'gifts.experiences',
        'meta_title' => 'Schmiede-Erlebnisse für dich, Freunde und Teams',
        'description' => 'Ein Tag an Esse und Amboss: Schmiedekurse als Erlebnis für dich, als Geschenk oder als dreitägiger Manager-Schmiedekurs für Teams und Gruppen bei Heilbronn.',
    ],

    '/ratgeber/' => [
        'title' => 'Ratgeber', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.guides',
        'meta_title' => 'Ratgeber Schmiedekurs – Kosten und Vorbereitung',
        'description' => 'Was kostet ein Schmiedekurs und wie bereitest du dich vor? Antworten und Preisübersicht aus der Messermanufaktur Ulbrich – vom Grundlagenkurs bis Damast.',
    ],
    '/manufaktur/' => [
        'title' => 'Manufaktur', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.workshop',
        'meta_title' => 'Manufaktur seit 1934 – Messerschmied Andreas Ulbrich',
        'description' => 'Die Messermanufaktur Ulbrich in Stein am Kocher: Messerschmied Andreas Ulbrich, Mitglied der Handwerkskammer Heilbronn-Franken, und seine Werkstatt.',
    ],
    '/presse/' => [
        'title' => 'Presse', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.press',
        'meta_title' => 'Presse & Berichte über die Messermanufaktur Ulbrich',
        'description' => 'Pressespiegel der Messermanufaktur Ulbrich: Berichte über Messerschmied Andreas Ulbrich, seine Schmiedekurse und die Werkstatt in Stein am Kocher.',
    ],
    '/partner/' => [
        'title' => 'Partner', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.partners',
        'meta_title' => 'Partner & Kooperationen der Messermanufaktur Ulbrich',
        'description' => 'Partner der Messermanufaktur Ulbrich: Betriebe und Initiativen, mit denen Messerschmied Andreas Ulbrich in der Region Heilbronn zusammenarbeitet.',
    ],
    '/kunsthandwerkermarkt/' => [
        'title' => 'Kunsthandwerkermarkt', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.market',
        'meta_title' => 'Kunsthandwerkermarkt: Messer von Messermanufaktur Ulbrich',
        'description' => 'Die Messermanufaktur Ulbrich auf dem Kunsthandwerkermarkt: Messer, Handwerk und Gespräche mit Messerschmied Andreas Ulbrich. Termine und Infos auf dieser Seite.',
    ],

    '/impressum/' => [
        'title' => 'Impressum', 'status' => 'live', 'gsc_clicks' => null, 'index' => false, 'view' => 'legal.imprint',
        'description' => 'Impressum der Messermanufaktur Ulbrich, Andreas Ulbrich, Wannenstraße 33, 74196 Neuenstadt am Kocher. Angaben gemäß § 5 DDG und zuständige Kammer.',
    ],
    '/datenschutz/' => [
        'title' => 'Datenschutz', 'status' => 'live', 'gsc_clicks' => null, 'index' => false, 'view' => 'legal.privacy',
        'description' => 'Datenschutzerklärung der Messermanufaktur Ulbrich: welche Daten beim Besuch der Website, bei Anfragen und Buchungen verarbeitet werden und welche Rechte du hast',
    ],
    '/agb/' => [
        'title' => 'AGB', 'status' => 'live', 'gsc_clicks' => null, 'index' => false, 'view' => 'legal.terms',
        'description' => 'Allgemeine Geschäftsbedingungen der Messermanufaktur Ulbrich für den Kauf von Messern und Gutscheinen sowie für Bestellungen über diese Website.',
    ],
    '/widerruf/' => [
        'title' => 'Widerrufsbelehrung', 'status' => 'live', 'gsc_clicks' => null, 'index' => false, 'view' => 'legal.withdrawal',
        'description' => 'Widerrufsbelehrung der Messermanufaktur Ulbrich: Informationen zu deinem Widerrufsrecht beim Kauf von Messern und Gutscheinen über diese Website.',
    ],
    '/kursbedingungen/' => [
        'title' => 'Kursbedingungen', 'status' => 'live', 'gsc_clicks' => null, 'index' => false, 'view' => 'legal.course-terms',
        'description' => 'Kursbedingungen der Messermanufaktur Ulbrich: Buchung, Umbuchung bis 7 Tage vor dem Termin und Einlösung von Gutscheinen für alle Schmiedekurse.',
    ],
];
