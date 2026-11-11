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
        'meta_title' => 'Schmiedekurse in Baden-Württemberg | Manufaktur Ulbrich',
        'description' => 'Sechs Schmiedekurse in der Messermanufaktur Ulbrich: vom eintägigen Grundlagenkurs bis zum Damast Premium-Kurs. Preise, Dauer und Gesamtlänge im Überblick.',
    ],
    '/schmiedekurse/vergleich/' => [
        'title' => 'Schmiedekurse im Vergleich', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.compare',
        'meta_title' => 'Schmiedekurse vergleichen: Dauer, Ergebnis & Preis',
        'description' => 'Alle Schmiedekurse der Messermanufaktur Ulbrich nebeneinander: Dauer, maximale Gesamtlänge und Einstiegspreis. So findest du den Kurs, der zu dir passt.',
    ],
    '/schmiedekurse/heilbronn-region/' => [
        'title' => 'Schmiedekurs Region Heilbronn', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.region',
        'meta_title' => 'Schmiedekurse im Raum Heilbronn & Neuenstadt',
        'description' => 'Schmiedekurs im Landkreis Heilbronn: Die Werkstatt der Messermanufaktur Ulbrich liegt in Stein am Kocher bei Neuenstadt. Kurse, Anfahrt und Kontakt.',
    ],
    '/schmiedekurse/buchen/' => [
        'title' => 'Schmiedekurs buchen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.book', 'index' => false,
        'meta_title' => 'Schmiedekurs buchen',
        'description' => 'Schmiedekurs bei der Messermanufaktur Ulbrich buchen: Kurs wählen, Teilnehmerzahl angeben und Termin aussuchen. Der Gesamtpreis wird direkt berechnet.',
    ],

    '/shop/' => [
        'title' => 'Shop', 'status' => 'live', 'gsc_clicks' => 5, 'view' => 'shop.index',
        'meta_title' => 'Messer kaufen: handgefertigte Kollektion',
        'description' => 'Handgefertigte Messer aus der Messermanufaktur Ulbrich: Damast-Klappmesser der Edition 100, Unikate und Anfertigungen sowie Gutscheine für Schmiedekurse.',
    ],
    '/klappmesser/' => [
        'title' => 'Damast-Klappmesser Edition 100', 'status' => 'live', 'gsc_clicks' => 1, 'view' => 'shop.edition',
        'meta_title' => 'Damast-Klappmesser · Edition 100, regulär 699 €',
        'description' => 'Damast-Klappmesser der Edition 100: limitiert auf 100 nummerierte Stücke, 90 reguläre Messer zu je 699 € und 10 Sonderstücke. Handgefertigt von Andreas Ulbrich.',
    ],
    '/sonderanfertigungen/' => [
        'title' => 'Messer anfertigen lassen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.custom',
        'meta_title' => 'Messer schmieden lassen | Sonderanfertigungen – Ulbrich',
        'description' => 'Ein Messer nach deinen Vorstellungen: Messerschmied Andreas Ulbrich fertigt Klingenform, Länge und Griff auf Anfrage. So läuft eine Sonderanfertigung ab.',
    ],
    '/messer-unikate/' => [
        'title' => 'Messer-Unikate', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.unique',
        'meta_title' => 'Messer-Unikate & handgefertigte Einzelstücke',
        'description' => 'Messer-Unikate der Messermanufaktur Ulbrich: handgefertigte Einzelstücke mit Damastklinge und eigenem Griff. Verfügbare Stücke und Preise gibt es auf Anfrage.',
    ],
    '/messerprojekte/' => [
        'title' => 'Messerprojekte', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.projects',
        'meta_title' => 'Messerprojekte – Arbeiten aus der Manufaktur',
        'description' => 'Messerprojekte aus der Messermanufaktur Ulbrich: Klappmesser mit Damastklinge, verschiedene Griffe und Details. Ein Einblick in bisher entstandene Arbeiten.',
    ],

    '/geschenke/' => [
        'title' => 'Geschenke', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'gifts.index',
        'meta_title' => 'Geschenkideen: Schmiedekurse, Messer & Gutscheine',
        'description' => 'Geschenkideen aus der Messermanufaktur Ulbrich: ein Schmiedekurs-Gutschein zum Selberschmieden oder ein nummeriertes Damast-Klappmesser der Edition 100.',
    ],
    '/geschenke/schmiedekurs-gutschein/' => [
        'title' => 'Schmiedekurs-Gutschein', 'status' => 'live', 'gsc_clicks' => 1, 'view' => 'gifts.voucher',
        'meta_title' => 'Schmiedekurs-Gutschein verschenken | Manufaktur Ulbrich',
        'description' => 'Einen Schmiedekurs verschenken: Mit dem Gutschein der Messermanufaktur Ulbrich wählt die beschenkte Person Kurs und Termin später selbst und bucht online.',
    ],
    '/erlebnisse/' => [
        'title' => 'Erlebnisse', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'gifts.experiences',
        'meta_title' => 'Besondere Erlebnisse in Baden-Württemberg',
        'description' => 'Ein Tag an Esse und Amboss: Schmiedekurse als Erlebnis für dich, als Geschenk oder als dreitägiger Manager-Schmiedekurs für Teams und Gruppen bei Heilbronn.',
    ],

    '/ratgeber/' => [
        'title' => 'Ratgeber', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.guides',
        'meta_title' => 'Ratgeber: Messerschmieden, Kurse & Messerkauf',
        'description' => 'Was kostet ein Schmiedekurs und wie bereitest du dich vor? Antworten und Preisübersicht aus der Messermanufaktur Ulbrich – vom Grundlagenkurs bis Damast.',
    ],
    '/ratgeber/damastmesser/' => [
        'title' => 'Damastmesser & Damaststahl', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.guide-damascus',
        'meta_title' => 'Was ist ein Damastmesser? Damaststahl erklärt',
        'description' => 'Was ist ein Damastmesser? Wie Damaststahl entsteht, woher das Muster kommt, worin er sich unterscheidet und wie du eine Damastklinge richtig pflegst.',
    ],
    '/ratgeber/messer-pflege/' => [
        'title' => 'Messer pflegen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.guide-care',
        'meta_title' => 'Handgefertigte Messer pflegen, schärfen, lagern',
        'description' => 'So bleibt ein handgefertigtes Messer lange scharf: richtig reinigen, das passende Schneidbrett wählen, schärfen und lagern. Die wichtigsten Regeln im Überblick.',
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
        'title' => 'Partner', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'partner', 'kicker' => 'Gastgeber · Region · Handwerk', 'tile' => 'Gästen ein besonderes Handwerk zeigen', 'tile_label' => 'Kooperationen', 'h1' => 'Regionale Kooperationen: Handwerk & Erlebnisse', 'actions' => [['Kooperation vorschlagen', 'mailto:info@messermanufaktur-ulbrich.de?subject=Kooperation'], ['Schmiedekurse', '/schmiedekurse/']],
        'meta_title' => 'Regionale Kooperationen: Handwerk für eure Gäste',
        'description' => 'Kooperation mit der Messermanufaktur Ulbrich: Schmiedekurse und Messerprojekte für Hotels, Gastgeber und touristische Einrichtungen in der Region Heilbronn.',
    ],
    '/kunsthandwerkermarkt/' => [
        'title' => 'Kunsthandwerkermarkt', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'about.market',
        'meta_title' => 'Kunst- & Handwerkermarkt in Stein am Kocher',
        'description' => 'Kunst- & Handwerkermarkt der Messermanufaktur in Stein am Kocher: 18 Stände für echtes Handwerk, Standgebühr ab 70 € für zwei Tage – Infos für Aussteller.',
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

    /* --- Übernommen von der Live-Sitemap (11.11.2026) --- */
    '/schmiedekurse/grundlagenkurs/' => ['title' => 'Grundlagenkurs', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'grundlagenkurs', 'meta_title' => 'Schmiedekurs für Anfänger: Grundlagenkurs (1 Tag)', 'description' => 'Grundlagenkurs bei Messerschmied Andreas Ulbrich: ein Tag am Feuer, etwa acht Stunden, ohne Vorwissen, eigenes Werkstück – ab 300 € pro Person inkl. Getränke.'],
    '/schmiedekurse/klingenkurs/' => ['title' => 'Klingenkurs', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'klingenkurs', 'meta_title' => 'Klingenkurs: zwei eigene Klingen schmieden (1 Tag)', 'description' => 'Klingenkurs bei der Messermanufaktur Ulbrich: ein Tag mit etwa acht Stunden, zwei selbstgeschmiedete Klingen, ab 440 € pro Person. Griff gegen Aufpreis.'],
    '/schmiedekurse/klingenkurs-alternativ/' => ['title' => 'Klingenkurs „Alternativ“', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'klingenkurs-alternativ', 'meta_title' => 'Klingenkurs „Alternativ“: eigenes Tagesformat', 'description' => 'Der Klingenkurs „Alternativ“ ist ein eigenes Tagesformat ab 500 € pro Person. Werkstück, Material und Griffumfang stehen beim jeweiligen Termin – vorher prüfen.'],
    '/schmiedekurse/damast-messerkurs/' => ['title' => 'Damast-Messerkurs', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'damast-messerkurs', 'meta_title' => 'Damastmesser selber schmieden: 2-Tage-Kurs', 'description' => 'Im Damast-Messerkurs schmiedest du an zwei Tagen dein Damastmesser bis 32 cm Gesamtlänge inklusive Griff – mit Übernachtung, ab 1.100 € pro Person.'],
    '/schmiedekurse/damast-premium-kurs/' => ['title' => 'Damast Premium-Kurs', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'damast-premium-kurs', 'meta_title' => 'Damast Premium-Kurs: 2 bis 3 Tage Damastschmieden', 'description' => 'Damast Premium-Kurs: zwei bis drei Tage für ein Damastmesser bis 40 cm Gesamtlänge, ein bis zwei Übernachtungen und Getränke, ab 1.300 € pro Person.'],
    '/schmiedekurse/manager-schmiedekurs/' => ['title' => 'Manager-Schmiedekurs', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'courses.show', 'course' => 'manager-schmiedekurs', 'meta_title' => 'Manager-Schmiedekurs: drei Tage Damast-Handwerk', 'description' => 'Manager-Schmiedekurs: drei Tage mit je etwa acht Stunden an einem Damastmesser bis 40 cm Gesamtlänge, Übernachtungen nach Angebot. Preis auf Anfrage.'],
    '/schmiedekurse/erfahrungen/' => ['title' => 'Erfahrungen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'schmiedekurse__erfahrungen', 'kicker' => 'Erfahrungen & Ergebnisse', 'tile' => 'Was entsteht?', 'tile_label' => 'Kursergebnisse', 'meta_title' => 'Schmiedekurs-Erfahrungen: Ergebnisse je Kursformat', 'description' => 'Was entsteht im Schmiedekurs? Die Ergebnisse der Kursformate im Vergleich – vom ersten Werkstück bis zum Damastmesser mit Griff – und worauf du achten kannst.', 'actions' => [['Kurse vergleichen', '/schmiedekurse/vergleich/'], ['Termin buchen', '/schmiedekurse/buchen/']]],
    '/erlebnisse/schmieden-zu-zweit/' => ['title' => 'Schmieden zu zweit', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'erlebnisse__schmieden-zu-zweit', 'kicker' => 'Erlebnis zu zweit', 'foto' => 'klappmesser-hell-detail', 'meta_title' => 'Schmiedekurs zu zweit: gemeinsam am Amboss stehen', 'description' => 'Schmiedekurs zu zweit: Bucht zwei Plätze im selben Termin und erlebt Feuer, Hammer und Stahl gemeinsam – als Paar, mit Freunden oder als Überraschung.', 'actions' => [['Zwei Plätze buchen', '/schmiedekurse/buchen/?teilnehmer=2'], ['Als Gutschein', '/geschenke/schmiedekurs-gutschein/']]],
    '/erlebnisse/teamevent/' => ['title' => 'Teamevent', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'erlebnisse__teamevent', 'kicker' => 'Firmen & Gruppen', 'tile' => 'Ein Team. Ein Werkstatttag.', 'tile_label' => 'Teamevent', 'meta_title' => 'Schmieden als Teamevent für Firmen und Gruppen', 'description' => 'Schmiede-Teamevent bei Heilbronn: Format, Zeitrahmen und Ergebnis werden nach Gruppengröße geplant. Unverbindlich anfragen – telefonisch oder per E-Mail.', 'actions' => [['Teamevent anfragen', 'tel:+491794079218'], ['Manager-Schmiedekurs', '/schmiedekurse/manager-schmiedekurs/']], 'inquiry' => ['type' => 'anfrage', 'item' => 'Teamevent', 'title' => 'Teamevent anfragen', 'intro' => 'Nennt Gruppengröße, Zeitrahmen und was ihr am Ende mitnehmen möchtet.', 'quantity' => 'Ungefähre Personenzahl', 'submit' => 'Anfrage senden']],
    '/geschenke/schmiedekurs-geburtstag/' => ['title' => 'Schmiedekurs zum Geburtstag', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'geschenke__schmiedekurs-geburtstag', 'kicker' => 'Geschenkidee', 'tone' => 'violet', 'foto' => 'klappmesser-zebrano', 'meta_title' => 'Schmiedekurs zum Geburtstag verschenken: so geht’s', 'description' => 'Ein Schmiedekurs zum Geburtstag: Kursformat nach Interesse wählen, Termin direkt buchen oder einen Gutschein anfragen – und das Geschenk persönlich überreichen.', 'actions' => [['Gutschein ansehen', '/geschenke/schmiedekurs-gutschein/'], ['Kurse ansehen', '/schmiedekurse/']]],
    '/geschenke/weihnachten/' => ['title' => 'Weihnachten', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'geschenke__weihnachten', 'kicker' => 'Weihnachtsgeschenke', 'tone' => 'violet', 'foto' => 'edition-messer', 'meta_title' => 'Weihnachtsgeschenke: Messer & Schmiedekurse | Ulbrich', 'description' => 'Weihnachtsgeschenke aus der Messermanufaktur Ulbrich: handgefertigte Messer, Edition 100, Schmiedekurse und Gutscheine – mit Gravur, Versand oder Abholung.', 'actions' => [['Edition 100 ansehen', '/klappmesser/'], ['Schmiedekurs verschenken', '/geschenke/schmiedekurs-gutschein/']], 'intro_view' => 'pages.partials.xmas-teaser'],
    '/klappmesser/sonderedition/' => ['title' => 'Sonderstücke', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'klappmesser__sonderedition', 'kicker' => 'Edition 100', 'tone' => 'brown', 'foto' => 'punze-detail', 'meta_title' => 'Edition 100: die zehn Sonderstücke im Überblick', 'description' => 'Zehn der 100 Damast-Klappmesser sind für Sonderausführungen reserviert. Ausführung, höherer Preis und Verfügbarkeit werden persönlich vereinbart.', 'actions' => [['Sonderstück anfragen', 'tel:+491794079218'], ['Reguläre Edition', '/klappmesser/']]],
    '/messerprojekte/damast-klappmesser-edition-100/' => ['title' => 'Projekt Edition 100', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'messerprojekte__damast-klappmesser-edition-100', 'kicker' => 'Messerprojekt', 'tone' => 'brown', 'foto' => 'klappmesser-stehend', 'meta_title' => 'Messerprojekt: Damast-Klappmesser Edition 100 | Ulbrich', 'description' => 'Das Projekt hinter der Edition 100: ein Damast-Klappmesser in begrenzter Auflage von 100 Stück mit Seriennummer – 90 reguläre Exemplare und zehn Sonderstücke.', 'actions' => [['Zur Edition 100', '/klappmesser/'], ['Alle Projekte', '/messerprojekte/']]],
    '/messer-kunst/' => ['title' => 'Messer als Handwerkskunst', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'messer-kunst', 'kicker' => 'Handwerkskunst', 'tone' => 'brown', 'foto' => 'damast-detail', 'meta_title' => 'Messer als Handwerkskunst: Andreas Ulbrich | Ulbrich', 'description' => 'Messer als Handwerkskunst: Für Andreas Ulbrich gehören Klinge und Griff zu einer Gesamtgestaltung. Werkstücke, Edition 100 und Berichte über seine Arbeit.', 'actions' => [['Messerprojekte', '/messerprojekte/'], ['Presse', '/presse/']]],
    '/shop/damastmesser/' => ['title' => 'Damastmesser', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.category', 'category' => 'damast', 'tone' => 'brown', 'meta_title' => 'Damastmesser kaufen: handgefertigt von Andreas Ulbrich', 'description' => 'Damastmesser aus der Messermanufaktur Ulbrich: handgefertigte Klingen mit sichtbarer Damastzeichnung. Preis und Verfügbarkeit einzelner Stücke auf Anfrage.'],
    '/shop/kuechenmesser/' => ['title' => 'Küchenmesser', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.category', 'category' => 'kueche', 'tone' => 'brown', 'meta_title' => 'Küchenmesser & Kochmesser aus der Manufaktur Ulbrich', 'description' => 'Handgefertigte Küchen- und Kochmesser der Messermanufaktur Ulbrich: Formen vergleichen, Verwendung klären und Preis sowie Verfügbarkeit persönlich anfragen.'],
    '/shop/jagdmesser/' => ['title' => 'Jagdmesser', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.category', 'category' => 'jagd', 'tone' => 'brown', 'meta_title' => 'Jagdmesser handgefertigt: Messermanufaktur Ulbrich', 'description' => 'Jagdmesser aus der Messermanufaktur Ulbrich: Eigenschaften, Eignung und Verfügbarkeit vor dem Kauf persönlich klären – oder ein Jagdmesser anfertigen lassen.'],
    '/shop/einzelstuecke/' => ['title' => 'Einzelstücke', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'shop.category', 'category' => 'einzel', 'tone' => 'brown', 'meta_title' => 'Messer-Einzelstücke mit eigener Handschrift | Ulbrich', 'description' => 'Messer-Einzelstücke aus der Messermanufaktur Ulbrich: individuelle Gestaltung von Klinge und Griff, jedes Stück nur einmal. Verfügbarkeit und Preis auf Anfrage.'],
    '/shop/ringe/' => ['title' => 'Ringe', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'shop__ringe', 'kicker' => 'Ringe', 'tone' => 'brown', 'tile' => 'Ein Zeichen für euch.', 'tile_label' => 'Ringpaar', 'h1' => 'Individuell gestaltete Ringe anfragen', 'lead' => 'Du interessierst dich für ein individuell gestaltetes Ringpaar aus der Messermanufaktur Ulbrich? Beschreibe deine Vorstellungen – für Ringe gelten gesonderte Absprachen zu Ausführung, Größe und Preis.', 'meta_title' => 'Ringe aus der Messermanufaktur Ulbrich anfragen', 'description' => 'Individuell gestaltete Ringe aus der Messermanufaktur Ulbrich: Ringpaar anfragen und Ausführung, Größe sowie Preis persönlich mit Andreas Ulbrich abstimmen.', 'actions' => [['Ringe anfragen', 'tel:+491794079218'], ['Sonderanfertigungen', '/sonderanfertigungen/']], 'inquiry' => ['type' => 'messer', 'item' => 'Ringe', 'title' => 'Ringe anfragen', 'intro' => 'Beschreibt eure Vorstellungen, Wunschtermin und – falls bekannt – eure Ringgrößen.', 'submit' => 'Anfrage senden']],
    '/ratgeber/messerschmieden-ablauf/' => ['title' => 'Wie funktioniert Messerschmieden?', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__messerschmieden-ablauf', 'kicker' => 'Ratgeber · Ablauf', 'tile' => 'Wie aus Stahl ein Messer wird', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Wie funktioniert Messerschmieden? Ablauf erklärt', 'description' => 'Wie wird aus einem Werkstoff ein Messer? Planung, Formgebung und Bearbeitung erklärt – und welcher Schmiedekurs welchen Teil des Ablaufs selbst erlebbar macht.'],
    '/ratgeber/schmiedekurs-anfaenger/' => ['title' => 'Schmiedekurs für Anfänger', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__schmiedekurs-anfaenger', 'kicker' => 'Ratgeber · Einstieg', 'tile' => 'Kein Vorwissen nötig', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Welcher Schmiedekurs ist für Anfänger geeignet?', 'description' => 'Schmiedekurs ohne Vorwissen: Für den Einstieg ist der Grundlagenkurs vorgesehen – ein Tag, ab 300 € pro Person, eigenes Werkstück. So wählst du nach Ziel.'],
    '/ratgeber/schmiedekurs-dauer/' => ['title' => 'Wie lange dauert ein Schmiedekurs?', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__schmiedekurs-dauer', 'kicker' => 'Ratgeber · Dauer', 'tile' => '1 bis 3 Tage', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Wie lange dauert ein Messerschmiedekurs? Überblick', 'description' => 'Wie lange dauert ein Schmiedekurs? Von einem Tag mit etwa acht Stunden bis zu drei Tagen: Was in einen Tag passt und wann mehrere Kurstage sinnvoll sind.'],
    '/ratgeber/schmiedekurs-kosten/' => ['title' => 'Was kostet ein Schmiedekurs?', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__schmiedekurs-kosten', 'kicker' => 'Ratgeber · Kosten', 'tile' => 'ab 300 € p. P.', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Was kostet ein Schmiedekurs? Preise im Vergleich', 'description' => 'Was kostet ein Schmiedekurs? Preise von 300 € bis 1.300 € pro Person, mögliche Zusatzkosten wie Griff-Fertigung und wie die Bezahlung per Überweisung abläuft.', 'outro_view' => 'pages.partials.price-table'],
    '/ratgeber/schmiedekurs-vorbereitung/' => ['title' => 'Schmiedekurs vorbereiten', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__schmiedekurs-vorbereitung', 'kicker' => 'Ratgeber · Vorbereitung', 'tile' => 'Gut vorbereitet an die Werkbank', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Schmiedekurs vorbereiten: Kleidung und Anreise', 'description' => 'Schmiedekurs vorbereiten: Was du über Treffpunkt, Zeiten, Kleidung und den Zahlungsablauf wissen solltest, bevor du deinen Platz online reservierst.'],
    '/ratgeber/messer-kaufen/' => ['title' => 'Handgefertigtes Messer kaufen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__messer-kaufen', 'kicker' => 'Ratgeber · Messerkauf', 'tile' => 'Worauf achten?', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Handgefertigtes Messer kaufen: worauf achten?', 'description' => 'Handgefertigtes Messer kaufen: Beginne beim Verwendungszweck und vergleiche Form, Maße, Material, Griff, Pflege und Preis. Drei Wege zum eigenen Messer.'],
    '/ratgeber/welches-kochmesser/' => ['title' => 'Welches Kochmesser passt zu mir?', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__welches-kochmesser', 'kicker' => 'Ratgeber · Kochmesser', 'tile' => 'Nach dem wählen, was du schneidest', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Welches Kochmesser passt zu mir? Checkliste', 'description' => 'Welches Kochmesser passt zu dir? Wähle nach Zutaten, Schneidtechnik, Größe und Pflege – mit Checkliste vor dem Kauf eines handgefertigten Küchenmessers.'],

    // Neue Seiten (11.11.2026): Verkauf, Service, Ratgeber; englischer Bereich /en/ mit hreflang ('alternate' = deutsches Gegenstück)
    '/versand/' => ['title' => 'Versand & Abholung', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'versand', 'kicker' => 'Service', 'tone' => 'brown', 'tile' => 'Deutschland · Abholung · weltweit', 'tile_label' => 'Versand', 'meta_title' => 'Versand, Abholung & Zahlung | Messermanufaktur Ulbrich', 'description' => 'Versand in ganz Deutschland für 7,99 € pro Artikel, Abholung in Stein am Kocher, weltweit bei Kostenübernahme. Zahlung per Überweisung, bar oder mit Karte.', 'actions' => [['Messer ansehen', '/shop/'], ['Edition 100', '/klappmesser/']]],
    '/messer-schaerfen-lassen/' => ['title' => 'Messer schärfen lassen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'messer-schaerfen-lassen', 'kicker' => 'Messerservice', 'tone' => 'blue', 'tile' => 'Wieder scharf.', 'tile_label' => 'Schärfservice', 'service' => 'Messer schärfen', 'meta_title' => 'Messer schärfen lassen beim Messerschmied | Ulbrich', 'description' => 'Messer schärfen lassen bei Messerschmied Andreas Ulbrich: Küchen-, Damast-, Taschen- und Jagdmesser. Vorbeibringen oder per Post schicken – so läuft es ab.', 'actions' => [['Schärfen anfragen', '@phone'], ['Messer reparieren', '/messer-reparieren-lassen/']], 'inquiry' => ['type' => 'service', 'item' => 'Messer schärfen', 'title' => 'Schärfen anfragen', 'intro' => 'Sag uns kurz, um welches Messer es geht und ob du es vorbeibringst oder per Post schickst.', 'submit' => 'Anfrage senden']],
    '/messer-reparieren-lassen/' => ['title' => 'Messer reparieren lassen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'messer-reparieren-lassen', 'kicker' => 'Messerservice', 'tone' => 'blue', 'tile' => 'Zweites Leben für dein Messer', 'tile_label' => 'Reparatur', 'service' => 'Messer reparieren', 'meta_title' => 'Messer reparieren lassen: Klinge & neuer Griff | Ulbrich', 'description' => 'Messer reparieren lassen: abgebrochene Spitze, Ausbrüche, lockerer Griff oder ein altes Erbstück. Andreas Ulbrich repariert Klinge und Griff in der Werkstatt.', 'actions' => [['Reparatur anfragen', '@phone'], ['Messer schärfen', '/messer-schaerfen-lassen/']], 'inquiry' => ['type' => 'service', 'item' => 'Messer reparieren', 'title' => 'Reparatur anfragen', 'intro' => 'Beschreib den Schaden – Fotos kannst du nach unserer Antwort per E-Mail schicken.', 'submit' => 'Anfrage senden']],
    '/messer-gravur/' => ['title' => 'Messer mit Gravur', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'messer-gravur', 'kicker' => 'Personalisierung', 'tone' => 'violet', 'tile' => 'Dein Name. Dein Datum.', 'tile_label' => 'Gravur', 'meta_title' => 'Messer mit Gravur: Name, Datum oder Logo | Ulbrich', 'description' => 'Ein handgefertigtes Messer mit Gravur: Name, Initialen, ein Datum oder ein Firmenlogo machen es zum persönlichen Geschenk. So stimmst du die Gravur mit uns ab.', 'actions' => [['Gravur anfragen', '@phone'], ['Geschenkideen', '/geschenke/']], 'inquiry' => ['type' => 'service', 'item' => 'Gravur', 'title' => 'Gravur anfragen', 'intro' => 'Schreib uns deinen Wunschtext, das Datum oder ob es um ein Logo geht – und zu welchem Termin du schenken möchtest.', 'submit' => 'Anfrage senden']],
    '/firmengeschenke/' => ['title' => 'Firmengeschenke', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'firmengeschenke', 'kicker' => 'Für Unternehmen', 'tone' => 'brown', 'tile' => 'Ein Geschenk, das bleibt.', 'tile_label' => 'Firmengeschenke', 'meta_title' => 'Messer als Firmengeschenk mit Logo-Gravur | Ulbrich', 'description' => 'Messer als Firmengeschenk: handgefertigt, auf Wunsch mit Logo-Gravur, für Kunden, Partner und Mitarbeitende. Edition 100, Sonderanfertigung oder Schmiedekurs.', 'actions' => [['Unverbindlich anfragen', '@phone'], ['Teamevent', '/erlebnisse/teamevent/']], 'inquiry' => ['type' => 'anfrage', 'item' => 'Firmengeschenk', 'title' => 'Firmengeschenk anfragen', 'intro' => 'Nenn uns Anlass, ungefähre Stückzahl, Budget und Wunschtermin – wir melden uns mit einem Vorschlag.', 'quantity' => 'Ungefähre Stückzahl', 'submit' => 'Anfrage senden']],
    '/ratgeber/messerformen/' => ['title' => 'Messerformen erklärt', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__messerformen', 'kicker' => 'Ratgeber · Messerformen', 'tone' => 'blue', 'tile' => 'Santoku, Gyuto, Nakiri', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Messerformen: Santoku, Gyuto, Nakiri & Co. erklärt', 'description' => 'Santoku, Gyuto, Nakiri oder klassisches Kochmesser? Die wichtigsten Messerformen für Küche, Jagd und Tasche erklärt – und welche Form zu deinem Alltag passt.'],
    '/ratgeber/echtes-damastmesser-erkennen/' => ['title' => 'Echtes Damastmesser erkennen', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__echtes-damastmesser-erkennen', 'kicker' => 'Ratgeber · Damast', 'tone' => 'blue', 'tile' => 'Echt oder nur Optik?', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Echtes Damastmesser erkennen: 6 Merkmale | Ulbrich', 'description' => 'Echter Damast oder nur Damast-Optik? Woran du ein echtes Damastmesser erkennst: Muster an Rücken und Schneide, Herstellerangaben, Preis und Herkunft.'],
    '/ratgeber/messer-verschenken/' => ['title' => 'Messer verschenken', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__messer-verschenken', 'kicker' => 'Ratgeber · Geschenk', 'tone' => 'violet', 'tile' => 'Mit Münze?', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Messer verschenken: Bringt das wirklich Unglück? | Ulbrich', 'description' => 'Bringt es Unglück, ein Messer zu verschenken? Woher der Brauch mit der Münze kommt, wie du ihn schön umsetzt und welche Messer sich als Geschenk eignen.'],
    '/ratgeber/handgeschmiedete-messer-preis/' => ['title' => 'Was kostet ein handgeschmiedetes Messer?', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'ratgeber__handgeschmiedete-messer-preis', 'kicker' => 'Ratgeber · Preis', 'tone' => 'blue', 'tile' => 'Was steckt im Preis?', 'tile_label' => 'Ratgeber', 'article' => true, 'meta_title' => 'Warum sind handgeschmiedete Messer so teuer? | Ulbrich', 'description' => 'Warum kostet ein handgeschmiedetes Messer mehr als eines aus der Fabrik? Arbeitsschritte, Material, Wärmebehandlung und Griff erklärt – mit Preisbeispielen.'],
    '/en/' => ['title' => 'English', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__home', 'alternate' => '/', 'kicker' => 'Bladesmith since 1934', 'tone' => 'straw', 'foto' => 'andreas-esse', 'meta_title' => 'Handmade German Damascus Knives since 1934 | Ulbrich', 'description' => 'Handmade damascus knives from Germany: bladesmith Andreas Ulbrich, third generation since 1934. Edition 100 folding knife, custom knives, worldwide shipping.', 'actions' => [['Edition 100', '/en/folding-knife/'], ['Custom knives', '/en/custom-knives/']]],
    '/en/folding-knife/' => ['title' => 'Edition 100 folding knife', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__folding-knife', 'alternate' => '/klappmesser/', 'kicker' => 'Edition 100', 'tone' => 'brown', 'foto' => 'klappmesser-offen', 'product' => true, 'meta_title' => 'Damascus Folding Knife, Limited Edition 100 | Ulbrich', 'description' => 'Damascus folding knife from Germany, limited to 100 numbered pieces: 90 regular knives at €699 and 10 special pieces. Handmade by bladesmith Andreas Ulbrich.', 'actions' => [['Enquire by email', '@email'], ['Custom knives', '/en/custom-knives/']]],
    '/en/knives/' => ['title' => 'Handmade knives', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__knives', 'alternate' => '/shop/', 'kicker' => 'Knives', 'tone' => 'brown', 'foto' => 'damast-detail', 'meta_title' => 'Handmade Knives from a German Bladesmith | Ulbrich', 'description' => 'Handmade knives from Germany: damascus, kitchen knives like gyuto, santoku and nakiri, hunting and folding knives. Made by Andreas Ulbrich, shipped worldwide.', 'actions' => [['Ask about a knife', '/en/custom-knives/'], ['Edition 100', '/en/folding-knife/']]],
    '/en/custom-knives/' => ['title' => 'Custom knives', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__custom-knives', 'alternate' => '/sonderanfertigungen/', 'kicker' => 'Made to order', 'tone' => 'violet', 'foto' => 'klappmesser-griff', 'service' => 'Custom knife making', 'meta_title' => 'Custom Knife Maker in Germany: Made to Order | Ulbrich', 'description' => 'Commission a custom knife from a German bladesmith: blade shape, length, damascus steel, handle and engraving made to your wishes – and how the process works.', 'actions' => [['Send an enquiry', '@email'], ['See our knives', '/en/knives/']]],
    '/en/about/' => ['title' => 'About the workshop', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__about', 'alternate' => '/manufaktur/', 'kicker' => 'Since 1934', 'tone' => 'straw', 'foto' => 'andreas-esse', 'about' => true, 'meta_title' => 'Andreas Ulbrich: German Bladesmith since 1934 | Ulbrich', 'description' => 'Messermanufaktur Ulbrich: bladesmith Andreas Ulbrich carries on his family’s forging tradition in the third generation in Stein am Kocher, Germany.', 'actions' => [['Our knives', '/en/knives/'], ['Custom knives', '/en/custom-knives/']]],
    '/en/shipping/' => ['title' => 'Shipping & pickup', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__shipping', 'alternate' => '/versand/', 'kicker' => 'Shipping', 'tone' => 'brown', 'tile' => 'Germany · pickup · worldwide', 'tile_label' => 'Shipping', 'meta_title' => 'Knife Shipping Worldwide, Pickup & Payment | Ulbrich', 'description' => 'Shipping in Germany for €7.99 per item, worldwide if you cover all costs, or pickup at the workshop. Customs added if due. Pay by transfer, cash or card.', 'actions' => [['Our knives', '/en/knives/'], ['Edition 100', '/en/folding-knife/']]],
    '/garantie/' => ['title' => 'Garantie & Pflege', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'garantie', 'kicker' => '2 Jahre Garantie', 'tone' => 'brown', 'tile' => '2 Jahre Garantie', 'tile_label' => 'Garantie', 'meta_title' => '2 Jahre Garantie auf handgefertigte Messer | Ulbrich', 'description' => '2 Jahre Garantie auf jedes Messer der Messermanufaktur Ulbrich – bei Pflege nach den Pflegehinweisen. Pflegeheft und Pflegemittel bekommst du kostenlos dazu.', 'actions' => [['Messer ansehen', '/shop/'], ['Pflegetipps', '/ratgeber/messer-pflege/']]],
    '/en/warranty/' => ['title' => 'Warranty & care', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'generic', 'content' => 'en__warranty', 'alternate' => '/garantie/', 'kicker' => '2-year warranty', 'tone' => 'brown', 'tile' => '2-year warranty', 'tile_label' => 'Warranty', 'meta_title' => '2-Year Warranty on Handmade Knives & Free Care Kit | Ulbrich', 'description' => 'Every knife from Messermanufaktur Ulbrich comes with a 2-year warranty when cared for as instructed – plus a care booklet and care product, free of charge.', 'actions' => [['Our knives', '/en/knives/'], ['Shipping', '/en/shipping/']]],
    '/weihnachtsaktion/' => ['title' => 'Weihnachtsaktion', 'status' => 'live', 'gsc_clicks' => null, 'view' => 'campaign.xmas', 'index' => false, 'meta_title' => 'Weihnachtsaktion: Schneeballschlacht mit Überraschung | Ulbrich', 'description' => 'Weihnachtsaktion der Messermanufaktur Ulbrich: Triff den Weihnachtsmann in 30 Sekunden mit Schneebällen und sichere dir bis zu 10 % Rabatt – einmal pro Person.'],
];
