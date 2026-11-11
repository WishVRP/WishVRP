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
        'opened_on' => '2024-03-01', // Eröffnung Messermanufaktur Stein am Kocher (HGV Möckmühl 2/2024)
        'generations' => [
            // Großvater: laut Live-Seite „prägte den Zugang zum Schmieden“ (Werkstattporträt Schwäbische Post, 12.04.2021)
            ['no' => 1, 'year' => 1934, 'name' => 'Der Großvater', 'text' => 'Mit ihm kommt das Schmiedehandwerk in die Familie – er prägt Andreas’ Zugang zum Schmieden.'],
            ['no' => 2, 'year' => null, 'name' => null, 'text' => 'Die zweite Generation führt das Handwerk fort.'],
            // Eröffnung laut HGV Möckmühl „Von uns zu Euch“ 2/2024, S. 3; frühere Werkstatt in Aalen (Schwäbische Post 2021)
            ['no' => 3, 'year' => 2024, 'name' => 'Andreas Ulbrich', 'text' => 'Nach seiner Werkstatt in Aalen eröffnet er am 1. März 2024 die Messermanufaktur in Stein am Kocher – mit Schmiedekursen und handgefertigten Messern.'],
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
        'intl' => '+49 179 4079218',
    ],

    // info@… oder gmx-Adresse – noch nicht entschieden.
    // laut Live-Impressum (11.11.2026). Achtung: Live-Startseite nennt zusätzlich eine gmx-Adresse → dort vereinheitlichen.
    'email' => 'info@messermanufaktur-ulbrich.de',

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

    // Öffentliche Profile laut Live-Website (11.11.2026)
    'same_as' => [
        'https://www.instagram.com/messermanufaktur_ulbrich_/',
        'https://www.facebook.com/100084188314538',
    ],
    'social' => [
        'instagram' => 'https://www.instagram.com/messermanufaktur_ulbrich_/',
        'facebook' => 'https://www.facebook.com/100084188314538',
    ],

    // Buch (laut Live-Seite /manufaktur/)
    'book' => [
        'title' => 'Die Staatsanwaltschaft macht keine Fehler',
        'authors' => 'Andreas Ulbrich und Jeremy Monka',
        'format' => 'Kindle-eBook',
        'url' => 'https://www.amazon.de/Die-Staatsanwaltschaft-macht-keine-Fehler/dp/B0GXS7W5P5',
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
        'steel' => null,    // [FEHLT] (Live-Seite nennt nur „Damastklinge“)
        'dimensions' => null, // [FEHLT]
        'handle' => null,   // [FEHLT]
        // laut Live-Seite /klappmesser/ (11.11.2026)
        'special_note' => 'Die ersten zehn Stücke der Edition sind für Sonderanfertigungen reserviert; Ausführung und höherer Preis werden persönlich vereinbart.',
        'shipping' => '7,99 € Versand pro Artikel innerhalb Deutschlands',
        'delivery' => 'voraussichtlich 7–14 Werktage ab Bestellung bei zeitnaher Zahlung (5–10 Werktage Bearbeitung, 2–4 Werktage Versand)',
        'payment' => 'Banküberweisung; Auftragsbestätigung nach geprüftem Zahlungseingang',
        'serial_note' => 'Deine Seriennummer suchst du dir aus den freien Nummern 1–90 aus (Sonderstücke 91–100 nach Absprache).',
    ],

    /*
    | Verkauf, Versand, Zahlung – laut Inhaber (Chat 11.11.2026).
    | Auslandsversand nur, wenn der Kunde alle anfallenden Kosten trägt;
    | sonst weltweit per Selbstabholung. Zoll kommt, falls er anfällt, auf den Preis.
    */
    'sales' => [
        'area_served' => ['Deutschland', 'weltweit'],
        'pickup' => true,
        'pickup_note' => 'Selbstabholung in der Manufaktur in Stein am Kocher nach Absprache',
        'shipping_de_eur' => 7.99,
        'shipping_de' => '7,99 € Versand pro Artikel innerhalb Deutschlands',
        'international' => 'Versand ins Ausland ist möglich, wenn du alle anfallenden Kosten übernimmst (Versand, Versicherung, Gebühren). Alternativ holst du dein Messer in der Manufaktur ab.',
        'customs' => 'Fällt Zoll oder Einfuhrsteuer an, kommt dieser Betrag zum Preis hinzu.',
        'delivery' => 'Die Lieferzeit hängt davon ab, ob ein fertiges Messer oder eine Sonderanfertigung bestellt wird; sie wird bei der Bestellung genannt.',
        'payment' => ['Überweisung'],
        'payment_onsite' => ['Barzahlung', 'Kartenzahlung'],
        'payment_online' => null, // geplant, noch nicht verfügbar – erst nach Freigabe aktivieren
        'sharpening_by_post' => true, // laut Inhaber 11.11.2026
        'services' => ['Sonderanfertigung', 'Gravur', 'Messer schärfen', 'Messer reparieren', 'Neuer Griff'],
        'knife_forms' => ['Kochmesser', 'Santoku', 'Gyuto', 'Nakiri', 'Officemesser', 'Brotmesser', 'Filetiermesser', 'Ausbeinmesser', 'Jagdmesser', 'Klappmesser'],
    ],

    /*
    | Garantie – laut Inhaber (Chat 11.11.2026). Freiwillige Herstellergarantie,
    | zusätzlich zur gesetzlichen Gewährleistung. Formulierung vor Go-live
    | anwaltlich prüfen lassen (Garantieerklärung, § 479 BGB).
    */
    /*
    | Weihnachtsaktion „Schneeballschlacht“ (Inhaber, 11.11.2026): 30 Sekunden,
    | Treffer zählen, Rabatt je nach Treffern, höchstens 10 %, jede Person nur
    | einmal (eine Teilnahme je E-Mail-Adresse, Code einmalig einlösbar).
    | Anspruchsvoll (Wind, Wolken, Ausweichen, Nachladezeit). Codes gültig bis 31.12. (Inhaber).
    */
    'xmas' => [
        'enabled' => true,
        'ends_on' => '2026-12-24',        // letzter Spieltag
        'valid_until' => '2026-12-31',    // Code einlösbar bis (laut Inhaber)
        'seconds' => 30,
        'max_percent' => 10,              // Obergrenze laut Inhaber
        'max_score' => 60,                // Plausibilitätsgrenze (bei 0,45 s Nachladezeit max. ~66 Würfe)
        'tiers' => [                      // ab X Treffern → Y % (aufsteigend), kalibriert mit Testspielern: 5–6 Treffer bei pixelgenauem Zielen
            ['min' => 4, 'percent' => 5],
            ['min' => 10, 'percent' => 10],
        ],
    ],

    'warranty' => [
        'years' => 2,
        'condition' => 'bei Pflege nach den mitgelieferten Pflegehinweisen',
        'remedy' => 'Geht ein Messer trotz Pflege nach den Pflegehinweisen kaputt, richten wir es wieder her oder erstatten den Kaufpreis.',
        'excluded' => 'Schäden durch falsche Behandlung – zum Beispiel Rost nach dem Spülen in der Spülmaschine – sind Eigenverschulden und von der Garantie ausgenommen; im Einzelfall ist Kulanz möglich.',
        'included' => ['Pflegehinweise als kleines Heft', 'Pflegemittel, kostenlos'],
        'statutory_note' => 'Deine gesetzlichen Gewährleistungsrechte bleiben von der Garantie unberührt.',
    ],

    /*
    | Kunst- & Handwerkermarkt – veranstaltet von der Messermanufaktur (laut Live-Seite /kunsthandwerkermarkt/, 11.11.2026).
    */
    'market' => [
        'name' => 'Kunst- & Handwerkermarkt',
        'date' => null, // „Datum wird noch bekannt gegeben“
        'place' => 'Wannenstraße 33, 74196 Neuenstadt am Kocher (Stein am Kocher)',
        'stands' => [
            ['label' => 'Innenstand', 'size' => '2 × 2,5 m', 'numbers' => '1–8', 'price' => 80, 'text' => 'Standplatz in der verschlossenen Halle für Samstag und Sonntag.'],
            ['label' => 'Außenstand', 'size' => '3 × 3 m', 'numbers' => '9–18', 'price' => 70, 'text' => 'Außenfläche für Samstag und Sonntag. Eigener wetterfester Aufbau erforderlich.'],
        ],
        'fees' => [
            ['label' => 'Werbepauschale (verpflichtend je Stand)', 'price' => 10],
            ['label' => 'Beleuchtung für beide Tage (optional, nach Absprache)', 'price' => 10],
        ],
        'rules' => [
            'Nur Handgemachtes – keine Handels- oder industrielle Massenware.',
            'Aufbau Freitag ab 14 Uhr, Samstag spätestens 9 Uhr fertig.',
            'Kein offenes Feuer.',
            'Die Bewerbung ist noch keine Zusage. Verbindlich wird der Stand erst nach Prüfung, Vertrag und bestätigtem Zahlungseingang.',
            'Bei einer Absage weniger als 72 Stunden vor Veranstaltungsbeginn besteht grundsätzlich kein Anspruch auf Erstattung der Standgebühr; zwingende gesetzliche Rechte bleiben unberührt.',
        ],
        'categories' => ['Holzarbeiten', 'Metall & Schmuck', 'Keramik', 'Textil & Leder', 'Kunst & Illustration', 'Naturprodukte', 'Selbst hergestellte Lebensmittel', 'Sonstiges Kunsthandwerk'],
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
    'press' => [
        ['outlet' => 'Schwäbische Post', 'kind' => 'Werkstattporträt', 'craft' => true, 'date' => '2021-04-12', 'title' => 'In der Tofa glüht der Stahl', 'url' => 'https://www.schwaebische-post.de/ostalb/aalen/stadt-aalen/in-der-tofa-glueht-der-stahl-90419043.html', 'summary' => 'Porträt von Andreas Ulbrich und seiner damaligen Werkstatt in Aalen: individuell gestaltete Messer und der Einfluss seines Großvaters auf das Handwerk.', 'quote' => 'Schmieden können ist für mich ein Privileg.'],
        ['outlet' => 'Schwäbische Post', 'kind' => 'Veranstaltungsbericht', 'craft' => true, 'date' => '2026-09-06', 'title' => 'Handwerkskunst beim Kunsthandwerkermarkt Untergröningen', 'url' => 'https://www.schwaebische-post.de/ostalb/kochertal/untergroeningen/kunsthandwerkermarkt-untergroeningen-begeistert-mit-unikaten-94477610.html', 'summary' => 'Bericht über den Kunsthandwerkermarkt; Andreas Ulbrich wird mit einem dort gezeigten handgefertigten Schwert erwähnt.'],
        ['outlet' => 'Schwäbische Post', 'kind' => 'Erwähnung', 'craft' => true, 'date' => '2024-09-23', 'title' => 'Fürstenzug und Kunsthandwerk in Untergröningen', 'url' => 'https://www.schwaebische-post.de/ostalb/kochertal/untergroeningen/in-untergroeningen-lebt-das-erbe-der-fuerstin-weiter-93315026.html', 'summary' => 'Im Bericht zum historischen Fürstenzug und zum begleitenden Markt wird die Messermanufaktur Ulbrich als Aussteller genannt.'],
        ['outlet' => 'SWR Aktuell', 'kind' => 'Interview', 'craft' => false, 'date' => '2026-03-03', 'title' => 'Andreas Ulbrich über Zivilcourage und Ersthilfe', 'url' => 'https://www.swr.de/swraktuell/baden-wuerttemberg/heilbronn/zivilcourage-rettet-menschenleben-ersthelfer-unfall-wolpertshausen-a6-100.html', 'summary' => 'Andreas Ulbrich kommt als ausgebildeter Rettungsassistent und Ersthelfer zu Wort – über das Helfen bei einem Unfall.'],
        ['outlet' => 'BILD', 'kind' => 'Archiv', 'craft' => false, 'date' => '2020-10-28', 'title' => 'BILD berichtet über Andreas Ulbrich und seine Messer', 'url' => 'https://www.bild.de/regional/stuttgart/stuttgart-aktuell/sie-wollen-seine-messer-ex-soldat-verklagt-sekte-aus-heilbronn-73637654.bild.html', 'summary' => 'Bericht über einen damaligen Rechtsstreit um Messer und Werkstattausrüstung; dokumentiert den Stand von 2020, nicht den heutigen Stand des Verfahrens.'],
        ['outlet' => 'Campus Founders', 'kind' => 'Projektbericht', 'craft' => true, 'date' => '2020-05-06', 'title' => 'Erfolgsgeschichte »Gemeinsam für Morgen«', 'url' => 'https://campusfounders.de/de/community-de/erfolgsgeschichte-gemeinsam-fuer-morgen/', 'summary' => 'Projektbericht über digitale Unterstützung regionaler Unternehmen; nennt die damalige Arbeit am Online-Auftritt der Manufaktur.'],
        ['outlet' => 'HGV Möckmühl / Heimatkundlicher Arbeitskreis', 'kind' => 'Regionale Mitteilung (PDF)', 'craft' => true, 'date' => '2024-03-16', 'title' => 'VON UNS ZU EUCH · Ausgabe 2/2024', 'url' => 'https://www.hgv-moeckmuehl.de/fileadmin/user_upload/blaetterkatalog/catalogs/Von_uns_zu_Euch_2024-02/pdf/complete.pdf#page=3', 'summary' => 'Seite 3: Bericht über die Unterstützung bei der Eröffnung der Manufaktur am 1. März 2024.'],
        ['outlet' => 'Insel Mainau', 'kind' => 'Ausstellerverzeichnis (PDF)', 'craft' => true, 'date' => '2025-10-02', 'date_label' => '02.–05.10.2025', 'title' => 'Gräfliches Schlossfest · Kunsthandwerk auf Schloss Mainau', 'url' => 'https://www.mainau.de/sites/default/files/2025-09/2025-flyer-graefliches-schlossfest.pdf', 'summary' => 'Der offizielle Veranstalterflyer führt die Messermanufaktur Ulbrich im Ausstellerverzeichnis auf.'],
        ['outlet' => 'Stadt Möckmühl', 'kind' => 'Bildergalerie', 'craft' => true, 'date' => '2018-11-01', 'date_label' => '2018', 'title' => 'Schmiedehandwerk beim Martinimarkt 2018', 'url' => 'https://www.moeckmuehl.de/unsere-stadt/infothek/bildergalerie/14/martinimarkt-2018', 'summary' => 'Die städtische Rückschau nennt Andy Ulbrich bei der Vorstellung seines Schmiedehandwerks.'],
        ['outlet' => 'HGV Möckmühl', 'kind' => 'Veranstaltungsbericht', 'craft' => true, 'date' => '2024-11-03', 'date_label' => '2024', 'title' => 'Die Manufaktur beim Möckmühler Martinimarkt', 'url' => 'https://www.hgv-moeckmuehl.de/aktuelles/mama2022-1-1/', 'summary' => 'Rückblick zum verkaufsoffenen Sonntag am 3. November; die Messermanufaktur Ulbrich ist unter den teilnehmenden Betrieben.'],
        ['outlet' => 'Stadt Öhringen', 'kind' => 'Ausstellerverzeichnis (PDF)', 'craft' => true, 'date' => '2025-11-01', 'date_label' => '2025', 'title' => 'Handgeschmiedete Messer auf dem Öhringer Weihnachtsmarkt', 'url' => 'https://www.oehringen.de/fileadmin/Dateien/Cross7/Startseite/Freizeit_%26_Tourismus/Unsere_Veranstaltungen/Ohringer_Weihnachtsmarkt/Ausstellerliste-1762257379.pdf', 'summary' => 'Die offizielle Ausstellerliste nennt Andreas Ulbrich und die Messermanufaktur mit handgeschmiedeten Messern.'],
    ],

    /*
    | Ratgeber-Artikel, die es auf der Live-Seite gibt (laut Projektskill:
    | Kosten, Vorbereitung). Pfade noch nicht bekannt → Übersicht.
    */
    'guides' => [
        ['title' => 'Was kostet ein Schmiedekurs?', 'topic' => 'Kosten', 'path' => '/ratgeber/'],
        ['title' => 'Damaststahl erklärt: Herstellung, Muster, Pflege', 'topic' => 'Damaststahl', 'path' => '/ratgeber/damastmesser/'],
        ['title' => 'Messer pflegen: reinigen, schärfen, lagern', 'topic' => 'Messerpflege', 'path' => '/ratgeber/messer-pflege/'],
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
