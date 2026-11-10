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
- **Echte Fotos** (10 von 14 gelieferten Bildern) in 7 Abschnitten plus Social-Vorschau: WebP und JPEG, srcset, feste Maße, lazy; Bildpipeline `scripts/fotos-erzeugen.sh`, Details und offene Fragen in `docs/fotos.md`.
- `php artisan ulbrich:audit` listet alle fehlenden Daten, Bildplätze und Foto-Prüfpunkte.
- Geprüft in Chromium (Playwright): 1440 px und 375 px ohne horizontales Scrollen, keine Konsolenfehler.

## Vorbereitet (Gerüst vorhanden, Inhalt fehlt)

- 22 Bestands-URLs als Vorschau-Platzhalter (nur außerhalb Produktion, noindex).
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
| Logo als SVG | Kopf, Favicon, Schema | Andreas/Jeremy |
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
