# SEO-Migrationsplan

## Ausgangswert (vor jeder Änderung gesichert)

Google Search Console, 09.08.–08.11.2026 (laut Jeremy): **341 Klicks · 3.361 Impressionen · CTR 10,15 %**

| URL | Klicks | Anteil | Status im neuen Projekt |
| --- | ---: | ---: | --- |
| `/` | 294 | 86 % | umgesetzt (Vorschau) |
| `/schmiedekurse/` | 22 | 6 % | Platzhalter, nächster Schritt |
| `/shop/` | 5 | 1,5 % | Platzhalter |
| `/klappmesser/` | 1 | – | Platzhalter |
| `/geschenke/schmiedekurs-gutschein/` | 1 | – | Platzhalter |

Die Startseite trägt fast die gesamte Sichtbarkeit. Ihre Suchanfragen sind wahrscheinlich überwiegend Marken-/Ortsanfragen – **das ist erst mit dem vollständigen Export (Tab „Suchanfragen“) bewertbar**. Bis dahin keine Annahmen über Keywords.

## Umgesetzt (Startseite)

- Eindeutiger Title (Thema vorne, „| Ulbrich“ hinten), Description 140–160 Zeichen (Test), genau eine H1 (Test), Canonical auf Produktionsdomain mit Slash, OG + Twitter Card.
- JSON-LD `@graph`: LocalBusiness (NAP, Öffnungszeiten laut Google-Profil, ohne E-Mail solange offen), Person (Andreas Ulbrich, Messerschmied, Mitglied HWK Heilbronn-Franken), WebSite, WebPage, FAQPage (identisch mit sichtbaren Fragen – Test). **Kein** AggregateRating/Review (Test).
- Google-Bewertung nur als sichtbarer Text mit Datum und Quelle.
- Kursdaten serverseitig als HTML-Tabelle (kein JavaScript nötig), Preise aus der Datenbank.
- Interne Links von der Startseite auf alle Bestandsseiten der Hauptthemen (Kurse, Vergleich, Region, Buchung, Gutschein, Edition 100, Shop, Anfertigung, Unikate, Projekte, Manufaktur, Presse, Ratgeber).
- robots.txt (Produktion: Allow + Sitemap; sonst Disallow), sitemap.xml nur mit umgesetzten, indexierbaren Seiten.
- Redirect-Map + Slash-Normalisierung (ADR 0002), alles getestet.
- Performance-Basis: ~6 KB CSS gzip, 0,3 KB JS, Schriften lokal, Bildplatzhalter mit festem Seitenverhältnis (kein CLS). **Nicht gemessen** (kein Lighthouse/Feld-Daten-Lauf).

## Vor Go-live zwingend

1. Search-Console-Export auswerten: Suchanfragen je URL, Seiten mit Impressionen, aber ohne Klicks.
2. **Live-Title und -Description der Startseite übernehmen bzw. bewusst vergleichen** – die CTR von 10,15 % ist gut; ein neuer Title ist das größte Einzelrisiko.
3. Vollständige URL-Liste (Live-Sitemap + GSC „Seiten“) gegen `config/pages.php`; jede URL mit Impressionen braucht 200 oder genau einen 301.
4. Alle Seiten mit Klicks inhaltlich mindestens gleichwertig migrieren (Texte nicht kürzen, Kursfakten „Auf einen Blick“).
5. Kein `[FEHLT]`-Marker mehr (`php artisan ulbrich:audit`).
6. Nach Umstellung: Sitemap einreichen, Crawling-Fehler 14 Tage täglich prüfen, Vergleich der Klicks je URL nach 4 und 8 Wochen.

## Geplant je Seitentyp

| Seite | Hauptintention | Schema |
| --- | --- | --- |
| `/schmiedekurse/` + 6 Kursseiten | kommerziell/transaktional, lokal | Course + CourseInstance (nur mit echtem Termin, Ort, Preis), BreadcrumbList |
| `/schmiedekurse/vergleich/` | kommerziell | ItemList, Tabelle als HTML |
| `/schmiedekurse/heilbronn-region/` | lokal | – (eigener regionaler Inhalt, keine Städteliste) |
| `/klappmesser/`, Shop | transaktional | Product + Offer (Preis, Verfügbarkeit – nur echte Daten) |
| `/geschenke/schmiedekurs-gutschein/` | transaktional | Product + Offer |
| `/ratgeber/…` | informational | Article, Autor = Person |
| `/manufaktur/`, `/presse/` | navigational/Vertrauen | Person, sameAs nur mit echten Profilen |

Themencluster (Volumen nur aus GSC/Keyword-Tool): Schmiedekurs (Einsteiger, Damast, Region, Kosten, Dauer) · Geschenk/Gutschein · Teamevent · Damastmesser kaufen · Messer anfertigen lassen · Damast-Ringe.

## Entität

Überall „Messermanufaktur Ulbrich“ + Ort (Namenskollision mit dem US-Stahlkonzern „Ulbrich“). Google-Profil: Kategorien korrigieren (u. a. „Möbelgeschäft“ ist falsch), NAP exakt wie `config/ulbrich.php`.
