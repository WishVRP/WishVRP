<?php

/*
|--------------------------------------------------------------------------
| Unternehmensdaten Messermanufaktur Ulbrich – einzige Quelle der Wahrheit
|--------------------------------------------------------------------------
|
| Alle öffentlich sichtbaren Fakten (NAP, Steuerstatus, Buchungsregeln)
| werden ausschließlich hier gepflegt und von Templates, JSON-LD und
| Tests gelesen. So kann eine Angabe nie an zwei Stellen unterschiedlich
| stehen (Problem der Altseite: Ort, E-Mail, Steuerhinweis).
|
| Stand: 10.11.2026, Quelle: Projektskill „ulbrich-website“ (Impressum,
| Google-Profil). Die Live-Seite war aus der Entwicklungsumgebung nicht
| erreichbar – vor Go-live jede Angabe gegen Impressum/Profil prüfen.
|
| `null` bedeutet: Angabe fehlt. Templates zeigen dann einen sichtbaren
| [FEHLT]-Marker, JSON-LD lässt das Feld weg (keine Platzhalter im Schema).
*/

return [

    'name' => 'Messermanufaktur Ulbrich',
    'owner' => 'Andreas Ulbrich',
    'owner_role' => 'Messerschmied',
    'chamber' => 'Handwerkskammer Heilbronn-Franken',

    /*
    | Familientradition (Angabe Auftraggeber im Chat, 10.11.2026):
    | Schmiedehandwerk seit 1934 in der Familie, Andreas Ulbrich in 3. Generation.
    | Namen/Stationen der 1. und 2. Generation fehlen noch → null (werden nicht angezeigt).
    | Bewusst KEIN foundingDate der Firma im Schema: 1934 ist das Jahr der Familientradition.
    */
    'heritage' => [
        'since' => 1934,
        'generation' => 3,
        'generations' => [
            ['no' => 1, 'year' => 1934, 'name' => null, 'text' => 'Das Schmiedehandwerk kommt in die Familie.'],
            ['no' => 2, 'year' => null, 'name' => null, 'text' => 'Die zweite Generation führt das Handwerk fort.'],
            ['no' => 3, 'year' => null, 'name' => 'Andreas Ulbrich', 'text' => 'Messerschmied, Messermanufaktur und Schmiedekurse in Stein am Kocher.'],
        ],
    ],

    'address' => [
        'street' => 'Wannenstraße 33',
        'postal_code' => '74196',
        'locality' => 'Neuenstadt am Kocher',
        'district' => 'Stein am Kocher',
        'region' => 'Baden-Württemberg',
        'country' => 'DE',
    ],

    // Schreibweise „Neuenstadt-Stein“ / „Stein am Kocher“ / „Möckmühl“ ist
    // laut Projektskill noch nicht entschieden. Bis dahin: amtliche
    // Gemeinde laut Impressum + Ortsteil. Eine Änderung hier wirkt überall.
    'place_label' => 'Neuenstadt am Kocher',
    'place_decided' => false,

    'phone' => [
        'display' => '0179 4079218',
        'e164' => '+491794079218',
    ],

    // info@… oder gmx-Adresse – noch nicht entschieden.
    'email' => null,

    // Öffnungszeiten laut Google-Unternehmensprofil (Stand 10.11.2026).
    // Mit Andreas abgleichen, ob das Werkstatt-/Telefonzeiten sind.
    'opening_hours' => [
        ['days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'label' => 'Mo–Fr', 'opens' => '09:00', 'closes' => '17:00'],
        ['days' => ['Saturday'], 'label' => 'Sa', 'opens' => '09:00', 'closes' => '14:00'],
    ],
    'opening_hours_verified' => false,

    // Google-Profil: nur als sichtbarer Hinweis mit Datum, NIE als
    // AggregateRating-Schema (keine eigenen, auf der Seite gesammelten
    // Bewertungen). Auf null setzen, um den Hinweis auszublenden.
    'google_rating' => [
        'value' => '5,0',
        'count' => 10,
        'as_of' => '10.11.2026',
        'profile_url' => null, // [FEHLT] Link zum Google-Profil
    ],

    'same_as' => [
        // 'https://www.instagram.com/…'  [FEHLT] Instagram-URL
    ],

    /*
    | Steuerstatus. Die Altseite nennt § 19 UStG im Impressum und an anderen
    | Stellen „inkl. MwSt.“ – das schließt sich aus. Werte:
    |   'pending'          → sichtbarer Prüfhinweis an jedem Preis
    |   'kleinunternehmer' → „Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.“
    |   'regular'          → „inkl. 19 % MwSt.“
    | Entscheidung durch Steuerberater, danach hier umstellen.
    */
    'tax_mode' => env('ULBRICH_TAX_MODE', 'pending'),

    'booking_rules' => [
        'Kurse werden ausschließlich online gebucht.',
        'Umbuchen ist bis 7 Tage vor dem Kurstermin möglich.',
        'Ein Gutschein reserviert noch keinen Kursplatz – den Termin wählt die beschenkte Person bei der Buchung.',
    ],

    'edition' => [
        'name' => 'Damast-Klappmesser Edition 100',
        'url' => '/klappmesser/',
        'total' => 100,
        'regular' => 90,
        'special' => 10,
        'price' => 699,
        'steel' => null,    // [FEHLT]
        'dimensions' => null, // [FEHLT]
        'handle' => null,   // [FEHLT]
    ],

    /*
    | Bewertungstexte: nur wörtlich aus dem Google-Profil, mit Vorname/Initial
    | und Datum, nach Freigabe. Leer = Bereich zeigt nur die Gesamtbewertung.
    | Format: ['text' => '…', 'author' => 'Vorname N.', 'date' => '2026-05-01', 'source' => 'Google']
    */
    'reviews' => [],

    /*
    | Presse: die 11 datierten Quellen von /presse/ übernehmen.
    | Format: ['outlet' => 'Heilbronner Stimme', 'title' => '…', 'date' => '2025-03-14', 'url' => 'https://…']
    | Leer = in der Vorschau gestaltete Platzhalter, in Produktion nur der Link.
    */
    'press' => [],

    /*
    | Ratgeber-Artikel, die es auf der Live-Seite gibt (laut Projektskill:
    | Kosten, Vorbereitung). Pfade noch nicht bekannt → Übersicht.
    */
    'guides' => [
        ['title' => 'Was kostet ein Schmiedekurs?', 'topic' => 'Kosten', 'path' => '/ratgeber/'],
        ['title' => 'So bereitest du dich auf deinen Schmiedekurs vor', 'topic' => 'Vorbereitung', 'path' => '/ratgeber/'],
    ],

    /*
    | Messer-Bereiche = bestehende Seiten der Live-Website.
    */
    'knife_areas' => [
        ['title' => 'Damast-Klappmesser Edition 100', 'path' => '/klappmesser/', 'text' => '100 nummerierte Stücke'],
        ['title' => 'Messer-Unikate', 'path' => '/messer-unikate/', 'text' => 'Einzelstücke aus der Werkstatt'],
        ['title' => 'Sonderanfertigungen', 'path' => '/sonderanfertigungen/', 'text' => 'Ein Messer nach deinen Vorstellungen'],
        ['title' => 'Messerprojekte', 'path' => '/messerprojekte/', 'text' => 'Bisher entstandene Arbeiten'],
        ['title' => 'Shop', 'path' => '/shop/', 'text' => 'Alle verfügbaren Messer'],
    ],
];
