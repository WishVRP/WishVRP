# Projektstatus Relaunch – Stand 10.11.2026

## Umgesetzt

- Laravel-13-Projekt (PHP 8.3) mit Filament 5, Git, Architekturentscheidungen in `docs/adr/`.
- **Startseite Version 2** (Premium-Redesign) mit 15 Abschnitten: Hero · Manufaktur-Intro · Schmiedekurs-Erlebnis · Kurs-Finder mit Filter · Kurstermine · Messer-Raster · Edition 100 (100er-Raster) · Sonderanfertigungen · Gutscheine · Herstellungsprozess · Andreas Ulbrich & Werkstatt · Firmen & Gruppen · Bewertungen & Presse · Ratgeber · FAQ/Kontakt/Anfahrt. Vergleich mit der alten Website: `docs/vergleich-alt-neu.md`.
- Navigation laut Konzept (Schmiedekurse · Messer · Anfertigung · Gutscheine · Manufaktur · Ratgeber + „Termin finden“), mobiles Menü (Tastatur, Escape, ohne JS sichtbar), mobile Aktionsleiste Termine · Gutschein · Kontakt.
- Designsystem: Tokens, 11 Blade-Komponenten, Kontraste rechnerisch ≥ AA (`docs/design-system.md`).
- SEO-Grundlage: PageMeta, JSON-LD, robots, Sitemap, Redirect-Map, Slash-Normalisierung (`docs/seo-migrationsplan.md`).
- Sicherheit: CSP mit Nonce, Sicherheits-Header, noindex außerhalb Produktion, Verwaltung unter eigenem Pfad mit **Pflicht-2FA**.
- Verwaltung: Kurse und Kurstermine in Filament pflegbar.
- Einheitliche Faktenquelle `config/ulbrich.php` inkl. Steuer-Schalter (`ULBRICH_TAX_MODE`).
- 28 automatisierte Tests: alle Bestands-URLs von der Startseite verlinkt, keine toten internen Links, keine Platzhalter-Bewertungen in Produktion, Meta, H1, Schema, kein Admin-Markup, Header, Redirects, robots, Sitemap, Admin-Schutz – **alle grün**.
- **Version 3:** nur Studiobilder (Messer echt, Hintergrund KI) laut Entscheidung 10.11.2026; Kurse, Ratgeber und Porträt als typografische Grafiken; Effekte: Funken, Bild-Aufdecken, Parallaxe, Zähler, Lichtkegel, Lesefortschritt.
- **Echte Fotos** aus den gelieferten Bildern: WebP und JPEG, srcset, feste Maße, lazy; Bildpipeline `scripts/fotos-erzeugen.sh`, Details und offene Fragen in `docs/fotos.md`.
- `php artisan ulbrich:audit` listet alle fehlenden Daten, Bildplätze und Foto-Prüfpunkte.
- Geprüft in Chromium (Playwright): 1440 px und 375 px ohne horizontales Scrollen, keine Konsolenfehler.

## Version 6: Live-Seite übernommen (11.11.2026)

Die Domain wurde freigegeben, alle 59 öffentlichen Live-URLs sind abgeglichen (Test `LiveSitemapTest`).
- **51 Seiten** im Register: Neu sind die **6 Kursseiten unter den Live-Pfaden** (Dauer, Projekt, Gesamtlänge, Leistungen, FAQ, Course-Schema mit Offer), Kurserfahrungen, Schmieden zu zweit, Teamevent, Geburtstag, Weihnachten, Sonderstücke, Projekt Edition 100, Messer als Handwerkskunst, 4 Shop-Kategorien, Ringe und 8 Ratgeber (Live-Pfade). Die eigenen Ratgeber liegen jetzt auf `/ratgeber/damastmesser/` und `/ratgeber/messer-pflege/`.
- **15 Live-URLs per 301:** 11 Einzelprodukte auf ihre Kategorie, dazu `/shop/einblicke/`, `/shop/gutscheine/` und `/shop/sonderanfertigung/` (Begründung in `docs/redirects.md`).
- **Fakten übernommen:**
  - E-Mail info@, Instagram, Facebook
  - 11 Presseberichte mit Links
  - Großvater als 1. Generation, Eröffnung der Manufaktur am 1. März 2024, frühere Werkstatt in Aalen
  - Buch mit Jeremy Monka
  - Edition: Versand 7,99 €, Lieferzeit, Überweisung, Sonderstücke
  - Kunst- & Handwerkermarkt (von der Manufaktur veranstaltet, Standgebühren)
- **Korrigiert:** Die Kurslängen sind **Gesamtlängen inkl. Griff**, keine Klingenlängen. Der Markt ist eine eigene Veranstaltung der Manufaktur, kein Ausstellerauftritt.
- **Presse:** „Presse & Erwähnungen“-Leiste unter dem Hero, Presseseite mit Einordnung, Zitat „Schmieden können ist für mich ein Privileg.“
- **Titles** der Bestandsseiten an die Live-Titles angelehnt (CTR-Schutz). Descriptions neu, weil die Live-Descriptions oft abgeschnitten sind.
- **Test:** Jede Seite ist von der Startseite in höchstens 3 Klicks erreichbar.
- **Steuerstatus bleibt „offen“:** Die Live-Seite widerspricht sich selbst (§ 19 vs. „inkl. USt“).

## Version 5: Emotion, Tradition, Weg zur Buchung (10.11.2026)

- **Familientradition** laut Auftraggeber: Schmiedehandwerk seit 1934, Andreas Ulbrich in 3. Generation. Steht als eine Quelle in `config/ulbrich.php` → `heritage`.
- **Hero:** Kontur „1934“, Unterzeile „aus einer Familie, die seit 1934 am Feuer steht“, Hauptbutton „Schmiedekurs buchen“, Kleingedrucktes (ab-Preis, Gutschein, Umbuchen).
- **Neuer Abschnitt Tradition:** Zähler „92 Jahre“, Zeitstrahl 1. → 2. → 3. Generation → „Und dann: du.“ mit Buchungslink. Die Linie zeichnet sich beim Scrollen. Namen der 1. und 2. Generation fehlen noch.
- **Neuer Abschnitt Erlebnis-Geschichte:** „Die Glut. / Der erste Schlag. / Dein Messer.“ Das Bild bleibt beim Scrollen stehen und wechselt mit jedem Kapitel. Am Ende stehen „Schmiedekurs buchen“ und „Als Gutschein verschenken“.
- **Buchung überall:** Der Button im Kopf heißt jetzt „Kurs buchen“, ebenso die erste Aktion in der mobilen Leiste.
- **SEO/GEO:** Meta-Description mit Tradition; FAQ „Seit wann …“ (auch als FAQPage-Schema); Person- und LocalBusiness-Schema mit Beschreibung und `knowsAbout`. Bewusst **kein** `foundingDate`, weil 1934 die Familientradition ist und nicht die Firmengründung. Neu ist **`/llms.txt`** mit Fakten, Kursen, Preisen und Seiten für KI-Suchsysteme.
- Offen: Namen und Stationen der Generationen, alte Familienfotos, Pressespiegel (11 Artikel), Kooperationen, Screenshots der aktuellen Website für die Optik.

## Version 4: alle Seiten umgesetzt (10.11.2026)

Alle 23 Bestands-URLs sind als echte Seiten umgesetzt. Es gibt keine Vorschau-Platzhalter mehr. Alle sind getestet: genau eine H1, Canonical, Description mit 140–160 Zeichen, Breadcrumb-Schema und keine toten internen Links.

- **Kurse:** `/schmiedekurse/` mit Kurs-Finder, Detailkarten je Kurs (Anker `#grundlagenkurs` …), Buchungsregeln, Prozess, FAQ, Course-Schema. `/schmiedekurse/vergleich/` mit Tabelle und Orientierung. `/schmiedekurse/heilbronn-region/` mit Anfahrt. `/schmiedekurse/buchen/` mit Kurswahl, Teilnehmern (`?kurs=`, `?teilnehmer=`), Terminbereich und Live-Gesamtpreis. Die **Zahlung ist bewusst deaktiviert**, der Hinweis nennt die Telefonnummer.
- **Shop:** `/shop/` mit Produktkarten. `/klappmesser/` mit 100er-Raster, technischen Daten und Product-Schema (nur der echte Preis, keine Verfügbarkeit). Dazu Sonderanfertigungen, Unikate und Messerprojekte. Kein Warenkorb, Bestellung telefonisch.
- **Geschenke:** `/geschenke/`, `/geschenke/schmiedekurs-gutschein/` mit Gutschein-Gestalter (Kurs und Name, Live-Vorschau; Kauf deaktiviert), `/erlebnisse/`.
- **Manufaktur:** `/manufaktur/` und `/ratgeber/` (Kosten-Artikel aus echten Preisen). `/presse/`, `/partner/` und `/kunsthandwerkermarkt/` haben ein Gerüst, die Inhalte fehlen noch.
- **Recht:** Impressum mit allen bekannten Angaben. Datenschutz als Entwurf mit den tatsächlichen technischen Fakten (nur notwendige Cookies, keine Drittanbieter). AGB, Widerruf und Kursbedingungen als ehrliches Gerüst. Alle mit noindex, **anwaltliche Prüfung nötig**.
- **Neue Kursseiten-URLs werden bewusst nicht geraten**, solange die Live-Pfade fehlen (siehe `config/pages.php`).
- Vorschau: `python3 scripts/vorschau-erzeugen.py` erzeugt eine einzige Datei mit allen Seiten (Hash-Navigation, funktioniert offline am Handy).

## Vorbereitet (Gerüst vorhanden, Inhalt fehlt)

- Kurstermine: Tabelle + Verwaltung + Anzeige vorhanden, keine Daten.
- Felder für Kursseiten-Pfade und `?kurs=`-Schlüssel (Altseite) in der Kursverwaltung.

## Offen (nächste Schritte, empfohlene Reihenfolge)

1. `/schmiedekurse/` + 6 Kursseiten mit „Auf einen Blick“, Course/CourseInstance-Schema.
2. `/schmiedekurse/buchen/` mit `?kurs=`/`?teilnehmer=`, Platzreservierung nur im Checkout, gehosteter Checkout + Webhook.
3. Gutschein-Kauf und -Einlösung (serverseitig, Codes ≥ 12 Zeichen).
4. `/klappmesser/` und Shop (Product/Offer, Altersprüfung beim Versand).
5. Rechtsseiten (nach Klärung Steuer/Widerruf), Manufaktur, Presse (11 datierte Quellen), Ratgeber.
6. Restliche Fotos (Porträt, Anfertigung, Außenansicht), AVIF, echtes Logo/Favicon.
7. Staging mit Passwort, Deploy-Skript, Lighthouse/axe-Läufe, Feldmessung nach Go-live.

Nicht gemessen/geprüft: Lighthouse, Rich-Results-Test, Search Console, echtes Hosting.

## Fehlende echte Unternehmensdaten

| Angabe | Wo gebraucht | Von wem |
| --- | --- | --- |
| Ortsschreibweise (Neuenstadt am Kocher / Stein am Kocher / …) | überall, Schema, Google-Profil | Andreas |
| Eine E-Mail-Adresse | Kontakt, Footer, Schema | Andreas |
| Steuerstatus (§ 19 UStG oder regelbesteuert) | jeder Preis | Steuerberater |
| Öffnungszeiten bestätigen (Google: Mo–Fr 9–17, Sa 9–14) | Kontakt, Schema | Andreas |
| Exakte URLs der 6 Kursseiten, `?kurs=`-Werte | Links, Redirect-Sicherheit | Live-Sitemap / Server |
| Dauer „Klingenkurs Alternativ“ | Kurstabelle | Andreas |
| Kurstermine mit Plätzen | Termine, CourseInstance | Buchungssystem |
| Kursort = Werkstatt? Gruppengröße, Voraussetzungen, Ergebnis je Kurs | Kursseiten | Andreas |
| Gutscheine: Werte, Gültigkeit, Versand (PDF/Post), Teileinlösung | Gutscheinseite | Andreas + Anwalt |
| Edition 100: Stahl/Lagen, Maße, Gewicht, Griff, Preis Sonderstücke | Edition, Product-Schema | Andreas |
| Anfertigung: Ablauf, Lieferzeit, Preisrahmen | Anfertigung | Andreas |
| Kurzbiografie mit Belegen | Manufaktur, Person-Schema | Andreas |
| Link Google-Profil, Instagram-URL | sameAs, Hinweis Bewertung | Jeremy |
| ~~Logo als SVG~~ erledigt: Monogramm A/Ω vektorisiert (`public/images/logo-monogramm.svg`) – Original-Vektordatei vom Gestalter wäre noch besser | Kopf, Favicon, Schema | Andreas/Jeremy |
| Fotos: Porträt Andreas (ohne Zigarette, Kamera-Original), Anfertigung (Skizze/Rohling), Außenansicht; Herkunft der Studiobilder bestätigen; Einwilligung Personen im Werkstattfoto | restliche Bildplätze, Recht | Andreas |
| 11 Presse-Quellen mit Datum und Link | Presse | Live-Seite `/presse/` |
| Aktueller Live-Title/-Description der Startseite | CTR-Erhalt | Live-Seite |

## Benötigte Zugänge

| Zugang | Wofür | Dringlichkeit |
| --- | --- | --- |
| Freigabe `messermanufaktur-ulbrich.de` in den Netzwerkeinstellungen dieser Umgebung | öffentliche Seiten lesen (Fakten, Titles, Sitemap) | hoch |
| Quellcode-Export der Live-Seite (ohne Zugangsdaten) | Admin-Prüfung, URL-Liste, Texte | hoch |
| Search-Console-Export (Leistung: Suchanfragen + Seiten, 16 Monate) | Migration, Titles | hoch |
| Hosting-Daten: PHP-Version, SSH ja/nein, Document Root, DB-Typ | Hosting-Entscheidung | hoch |
| Staging-Subdomain mit Passwort | Abnahme | mittel |
| Mollie- oder Stripe-Testkonto (Test-API-Keys) | Checkout | mittel |
| EU-Maildienst (SMTP) | Bestätigungen | mittel |
| Google-Unternehmensprofil (Verwaltung) | Kategorien, NAP | niedrig |
