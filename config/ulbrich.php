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
    // Bankverbindung für Überweisungen (nur in der .env pflegen). Leer = Mail sagt „Zahlungsdaten folgen“.
    'bank' => [
        'holder' => env('BANK_HOLDER'),
        'iban' => env('BANK_IBAN'),
        'bic' => env('BANK_BIC'),
        'name' => env('BANK_NAME'),
    ],

    // Link „Bewertung schreiben“ (Google-Unternehmensprofil → „Bewertungen anfordern“), sonst aus der Place-ID gebaut.
    'google_review_url' => env('GOOGLE_REVIEW_URL'),

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
    // Laut Betreiber (Launchpaket 12.11.2026): Regelbesteuerung, USt-IdNr. DE328830313
    'tax_mode' => env('ULBRICH_TAX_MODE', 'regular'),
    'vat_id' => 'DE328830313',

    'booking_rules' => [
        'Kurse werden ausschließlich online gebucht.',
        'Zwei Umbuchungen sind kostenlos – bis 48 Stunden vor dem Kurstermin, online über den Link in deiner Buchungsbestätigung oder per E-Mail. Der gebuchte Preis bleibt dabei gleich.',
        'Eine freiwillige Stornierung gegen Rückzahlung bieten wir nicht an; der bezahlte Buchungswert bleibt zehn Jahre ab Zahlungseingang für den gebuchten Kurs verwendbar. Gesetzliche Rechte bleiben unberührt.',
        'Einzelbuchung für eine Person, gemeinsame Buchungen bis vier Personen – größere Gruppen nach Absprache.',
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
        'shipping_de_eur' => 7.99,   // Anzeige „ab“ in Deutschland; berechnet wird über Verwaltung → Versandkosten
        // Standard fürs Versandgewicht/Paketmaß, wenn beim Produkt nichts eingetragen ist (Annahme – bitte prüfen)
        'default_parcel_g' => 1500,
        'default_parcel_cm' => [35, 25, 10],
        'shipping_de' => '7,99 € Versand pro Artikel innerhalb Deutschlands',
        'international' => 'Versand weltweit mit DHL: Die Versandkosten richten sich nach Land und Gewicht und werden im Bestellformular berechnet, sobald du dein Land wählst. Ist für ein Land noch kein Preis hinterlegt, nennen wir dir die Kosten per E-Mail, bevor du zahlst. Alternativ holst du dein Messer in der Manufaktur ab.',
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
    | Buchungskalender (Inhaber, 11.11.2026): jeder Tag buchbar, ein Kurs pro Tag,
    | mehrtägige Kurse blockieren alle Kurstage. Bezahlung direkt online (Stripe).
    */
    'booking' => [
        'min_days_ahead' => 2,        // frühestens übermorgen (Vorschlag)
        'years_ahead' => 5,
        'free_rebookings' => 2,       // kostenlose Umbuchungen je Buchung (Kursbedingungen 12.11.2026)
        'rebook_hours_before' => 48,  // mindestens 48 Stunden vor Beginn des jeweils vereinbarten Termins
        'course_start_time' => '09:00', // Annahme für die 48-h-Frist, solange keine Kurszeiten gepflegt sind – bitte bestätigen
        'value_years' => 10,          // bezahlter Buchungswert 10 Jahre ab Zahlungseingang verwendbar (Preis bleibt)
        'hold_minutes' => 30,         // Tag ist während der Online-Zahlung reserviert
        'online_courses' => ['grundlagenkurs', 'klingenkurs', 'klingenkurs-alternativ', 'damast-messerkurs', 'damast-premium-kurs'],
        'max_persons' => 4,           // laut Kursbedingungen 12.11.2026: gemeinsame Buchung bis 4, mehr nach Absprache
    ],

    /*
    | Gutscheine: gesetzliche Verjährung 3 Jahre zum Jahresende (§§ 195, 199 BGB) – vom Anwalt bestätigen.
    */
    'voucher_years' => 3,

    /*
    | Newsletter-Automatik (Inhaber, 11.11.2026): wöchentlich an bestätigte Empfänger,
    | alle 3 Monate mit persönlichem Rabattcode (höchstens 10 %). Inhalt der Auto-Ausgabe
    | nur aus echten Daten (Kurstermine, Edition-Nummern, Ratgeber) – nichts erfunden.
    */
    'newsletter' => [
        'auto_weekly' => env('NEWSLETTER_AUTO', true),
        'weekday' => 2,                 // 1 = Montag … 7 = Sonntag (Dienstag)
        'time' => '18:30',
        'discount_every_days' => 90,    // alle 3 Monate
        'discount_percent' => 10,
        'discount_valid_days' => 30,
        'max_percent' => 10,
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
        // Bestätigung vor dem Spiel: 'email' (kostenlos, persönlicher Link) oder 'sms' (Code per SMS, Anbieter nötig)
        'verification' => env('XMAS_VERIFICATION', 'email'),
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
        // Launchpaket 12.11.2026: freiwillige lebenslange Schliff-Garantie + gesetzliche Mängelrechte (statt bisher „2 Jahre Garantie“)
        'name' => 'Lebenslange Schliff-Garantie',
        'short' => 'Lebenslang kostenlos nachschärfen',
        'text' => 'Messer aus unserer Werkstatt – gekauft oder im Kurs selbst geschmiedet – schärfen wir kostenlos nach, so oft es nötig ist, solange die Manufaktur besteht. Versandkosten für das Einschicken trägst du.',
        'statutory_note' => 'Deine gesetzlichen Mängelrechte bleiben davon unberührt.',
        'included' => ['Pflegehinweise als kleines Heft', 'ein Pflegemittel, kostenlos'],
    ],

    /*
    | Kunst- & Handwerkermarkt – veranstaltet von der Messermanufaktur (laut Live-Seite /kunsthandwerkermarkt/, 11.11.2026).
    */
    /*
    | Ausfuhr/Einfuhr: Messerarten mit Zolltarifnummer (Kapitel 82.11 – vor Nutzung mit dem Zoll abgleichen).
    | Regeln je Land und Messerart pflegt der Inhaber in der Verwaltung (Einfuhr-Regeln) nach rechtlicher Prüfung.
    | Ohne Regel gilt 'default_action' (vorsichtig: prüfen – Bestellung kommt als Anfrage ohne Zahlung).
    */
    'export' => [
        'default_action' => 'pruefen',
        'knife_types' => [
            'kuechenmesser' => ['label' => 'Küchenmesser (feststehende Klinge)', 'hs' => '8211 92 00'],
            'feststehend' => ['label' => 'Jagd-, Outdoor- oder Gürtelmesser (feststehend)', 'hs' => '8211 92 00'],
            'klappmesser' => ['label' => 'Klapp- oder Taschenmesser', 'hs' => '8211 93 00'],
            'einhandmesser' => ['label' => 'Einhandmesser (feststellbare Klinge, mit einer Hand zu öffnen)', 'hs' => '8211 93 00'],
            'sonstiges' => ['label' => 'Sonstiges Messer / Klinge', 'hs' => '8211 99 00'],
            'kein_messer' => ['label' => 'Kein Messer (Ringe, Zubehör, Pflegemittel)', 'hs' => null],
        ],
        'edition_type' => 'klappmesser',   // Edition 100 – bitte prüfen, ob einhändig zu öffnen (dann: einhandmesser)
        'messages' => [
            'gesperrt' => 'Leider dürfen wir dieses Messer nicht in dein Land verschicken. Gern kannst du es nach Absprache in unserer Manufaktur abholen.',
            'pruefen' => 'Für den Versand in dein Land prüfen wir die Einfuhrbestimmungen persönlich. Du bekommst nach der Prüfung Bescheid – erst dann zahlst du.',
        ],
    ],

    'market' => [
        // Quellen: Live-Seite (11.11.2026), Marktordnung, Standplan und Standmietvertrag (PDF vom Inhaber, 11.11.2026)
        'name' => 'Kunst- & Handwerkermarkt',
        'date' => null, // Termin wird bekannt gegeben – in der Verwaltung pflegbar (Markt-Einstellungen)
        'days' => 'Samstag und Sonntag',
        'place' => 'Wannenstraße 33, 74196 Neuenstadt am Kocher (Stein am Kocher)',
        'venue' => 'Halle und Außengelände der Messermanufaktur Ulbrich',
        'stands' => [
            ['label' => 'Innenstand', 'size' => '2 × 2,5 m', 'numbers' => '1–8', 'price' => 80, 'text' => 'Abteil in der Halle für Samstag und Sonntag, je nach gebuchter Variante mit ein oder zwei Biertischen. Die Halle wird nachts verschlossen.'],
            ['label' => 'Außenstand', 'size' => '3 × 3 m', 'numbers' => '9–18', 'price' => 70, 'text' => 'Fläche auf dem Außengelände für Samstag und Sonntag. Eigener, gegen Wind und Wetter gesicherter Pavillon erforderlich.'],
        ],
        // Lage und Verkaufsseite laut Standplan-PDF (schematisch, nicht maßstabsgerecht)
        'layout' => [
            1 => '← links', 2 => '← links', 3 => '← links', 4 => '← links', 5 => null, 6 => null, 7 => '↓ unten', 8 => '→ rechts',
            9 => '↓ zum Weg', 10 => '↓ zum Weg', 11 => '↓ zum Weg', 12 => '↓ zum Weg', 13 => '↓ zum Weg', 14 => '↓ zum Eingang',
            15 => '→ zum Weg', 16 => '→ zum Weg', 17 => '→ zum Weg', 18 => '→ zum Weg',
        ],
        'catering' => 'Wurst und Getränke gibt es am eigenen Stand des Veranstalters auf dem Außengelände.',
        'fees' => [
            ['label' => 'Werbepauschale (verpflichtend je Stand)', 'price' => 10],
            ['label' => 'Beleuchtung für beide Tage (optional, nach Absprache)', 'price' => 10],
        ],
        'setup' => 'Aufbau Freitag ab 14:00 Uhr oder Samstagmorgen; Samstag spätestens 9:00 Uhr vollständig aufgebaut.',
        'teardown' => 'Abbau nach Marktende am Sonntag oder nach Absprache am Montag.',
        'parking' => 'Parken auf dem Veranstaltungsgelände ist nicht erlaubt – für Besucher und Aussteller. Zufahrt nur zum Be- und Entladen.',
        'rules' => [
            'Nur selbst hergestellte, gestaltete oder wesentlich handwerklich bearbeitete Waren – keine Handels- oder Massenware.',
            'Aufbau Freitag ab 14:00 Uhr oder Samstagmorgen; Samstag spätestens um 9:00 Uhr vollständig aufgebaut.',
            'Kein offenes Feuer, keine Kerzen oder Fackeln; bei brennbaren Stoffen einen geprüften Feuerlöscher bereithalten.',
            'Lebensmittel, Getränke und Vorführungen mit Maschinen, Hitze oder Lärm nur nach vorheriger Anmeldung und Freigabe.',
            'Die Bewerbung ist noch keine Zusage. Verbindlich wird der Stand mit dem unterschriebenen Standmietvertrag und der Zahlung.',
            'Stornierung: bis 30 Tage vor dem ersten Markttag kostenlos, 15–29 Tage vorher 25 %, 8–14 Tage 50 %, ab 7 Tage oder bei Nichterscheinen 100 % der Standgebühr (Nachweis eines geringeren Schadens bleibt möglich).',
        ],
        'categories' => ['Holzarbeiten', 'Metall & Schmuck', 'Keramik', 'Textil & Leder', 'Kunst & Illustration', 'Naturprodukte', 'Selbst hergestellte Lebensmittel', 'Sonstiges Kunsthandwerk'],
        'downloads' => [
            ['label' => 'Marktordnung', 'file' => '/downloads/kunsthandwerkermarkt-marktordnung.pdf', 'size' => '4 Seiten'],
            ['label' => 'Standplan (Halle und Außenplatz)', 'file' => '/downloads/kunsthandwerkermarkt-standplan.pdf', 'size' => '2 Seiten'],
            ['label' => 'Standmietvertrag (ausfüllbar)', 'file' => '/downloads/kunsthandwerkermarkt-standmietvertrag.pdf', 'size' => '4 Seiten'],
        ],
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
