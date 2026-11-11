# SEO-/GEO-Bericht – Stand 11.11.2026

Grundlage: Skill `ultimate-seo-geo-engine`, automatische Prüfung aller 23 Seiten (lokal) plus Tests (`php artisan test`, 40 Tests). **Nicht gemessen:** Lighthouse, Core Web Vitals im Feld, Rich-Results-Test, Search Console. Die Live-Domain ist aus dieser Umgebung gesperrt.

## 0. Neue Ratgeber-Inhalte (11.11.2026)
- **`/ratgeber/damaststahl/`** „Damaststahl erklärt: Herstellung, Muster, Pflege“. Suchintention informational, stützt die Damastkurse und die Edition 100. Kurzantwort oben, Inhaltsverzeichnis, 6-Schritte-Liste, Vergleichstabelle, Pflegeliste, 4 FAQ. Article- und FAQPage-Schema.
- **`/ratgeber/messer-pflegen/`** „Messer pflegen: reinigen, schärfen, lagern“. Suchintention informational, stützt Shop und Unikate. Grundregeln, Tabelle der Schneidunterlagen, Abziehen vs. Schärfen, Lagerung, 4 FAQ. Article- und FAQPage-Schema.
- **Inhaltlich:** allgemeines, anerkanntes Handwerkswissen ohne erfundene Firmendaten, Zahlen oder Studien. Kursdaten kommen live aus der Datenbank, die Tradition aus der Konfiguration.
- **Bewusst nicht** als eigene URL angelegt: „Kosten“ und „Vorbereitung“. Diese Artikel gibt es auf der Live-Seite unter unbekannten Pfaden, eine zweite URL würde doppelte Inhalte erzeugen. „Kosten“ steht als Abschnitt auf `/ratgeber/`.
- Verlinkt von Startseite, Ratgeber-Übersicht, Edition 100, Unikaten und gegenseitig. Automatisch in Sitemap und `llms.txt`.

## 1. Umgesetzte SEO-Maßnahmen
- **Titles:** alle 17 indexierbaren Seiten eindeutig und 50–60 Zeichen lang, Marke am Ende (per Test geprüft).
- **Descriptions:** jede Seite hat eine eigene mit 140–160 Zeichen (Test). Die Startseite nennt die Tradition „seit 1934, 3. Generation“.
- **H1:** genau eine je Seite (Test). Die H1 der Startseite enthält die Hauptbegriffe „Schmiedekurse“ und „handgeschmiedete Messer“.
- **Canonical** auf jeder Seite, mit Schrägstrich am Ende. Alte URLs werden per 301 umgeleitet, ohne Ketten (Test).
- **robots.txt und Sitemap:** Die Sitemap enthält nur indexierbare Seiten. Rechtsseiten, Buchung und 404 sind mit noindex markiert.
- **Interne Verlinkung:** neuer Block „Passend dazu“ (`config/related.php`). Jede indexierbare Seite hat mindestens 3 thematische Links im Inhalt (Test). Laut Prüfung haben alle Seiten mindestens 3 eingehende Links im Inhalt.
- **Brotkrumen** auf allen Unterseiten, sichtbar und als BreadcrumbList.
- **Eigene 404-Seite:** hilfreich, noindex, mit Links zu Kursen, Buchung, Shop, Gutschein und Kontakt (Test).

## 2. GEO / AEO
- **`/llms.txt`:** Fakten zu Inhaber, Tradition, Adresse, allen Kursen mit Dauer und Preis, Edition 100 und Seiten. Die Werte kommen aus der Konfiguration und der Datenbank, nicht doppelt gepflegt.
- **FAQ mit sichtbaren Antworten und FAQPage-Schema:** Startseite, Kurse, Vergleich, Ratgeber, neu auch der Gutschein (2 Fragen, nur aus den Buchungsregeln abgeleitet).
- **Zitierfähige Strukturen:** Vergleichstabelle der Kurse, Preistabelle im Ratgeber, Zeitstrahl der Generationen.
- **Entitäten verknüpft:** Person (Andreas Ulbrich, Messerschmied, 3. Generation, Handwerkskammer), LocalBusiness, Kurse, Produkt. Dazu `knowsAbout` (Messerschmieden, Damaststahl, Klappmesser, Schmiedekurse).

## 3. Schema (JSON-LD, nur sichtbare und echte Angaben)
LocalBusiness (mit Logo und Bild), Person, WebSite, WebPage, BreadcrumbList, FAQPage, ItemList/Course (Kurse), Product mit Offer 699 € (Edition 100, **ohne** Verfügbarkeit und ohne Bewertungen), Article (Ratgeber Kosten, **ohne** erfundenes Datum), Service (Sonderanfertigung). Bewusst nicht gesetzt: `foundingDate` (1934 ist die Familientradition, nicht die Firmengründung), AggregateRating, Geo-Koordinaten (nicht bestätigt).

## 4. Technik und Performance
- **Bilder:** AVIF (neu, rund 1 MB für alle Varianten), dazu WebP (1,2 MB) und JPEG (2,3 MB) als Rückfall. Mit srcset, width/height, lazy; das Hero-Bild mit fetchpriority. Im Browser geprüft: Chromium lädt AVIF.
- **Schriften** lokal mit Hash und `font-display: swap`. Keine externen Skripte, Schriften oder Tracker. CSP mit Nonce.
- **JS** ein Bundle von etwa 3 KB gzip. Animationen nur mit transform/opacity und reduced-motion respektiert.
- **Favicons:** SVG, ICO, PNG 16/32, Apple-Touch, Icons 192/512, Manifest, theme-color.

## 5. Local SEO
- NAP aus einer Quelle (`config/ulbrich.php`), auf allen Seiten gleich.
- Regionsseite `/schmiedekurse/heilbronn-region/` mit Adresse, Anfahrt per Routenlink und Öffnungszeiten. Keine Stadtlisten.
- **Offen:** Schreibweise des Orts (Neuenstadt am Kocher / Stein am Kocher) mit dem Google-Profil abgleichen. Link zum Google-Profil fehlt, Öffnungszeiten sind unbestätigt.

## 6. Offene Punkte / benötigte echte Daten (Priorität)
- **A:** Live-Domain freigeben (oder Search-Console-Export). Damit Live-Titles, alle URLs (6 Kursseiten), Pressespiegel und Kooperationen übernehmen. Das sichert die heutige CTR von 10,15 % beim Umzug.
- **A:** E-Mail-Adresse, Steuerstatus, Link zum Google-Profil, Instagram-URL (`sameAs`).
- **B:** Namen und Stationen der 1. und 2. Generation, alte Familienfotos.
- **B:** Kurstermine (CourseInstance-Schema wird möglich), Gruppengrößen, Ablauf je Kurs. Dann eigene Kursseiten mit den **Live-Pfaden**.
- **B:** Edition 100: Maße, Griff, Stahl, Preis der Sonderstücke, Verfügbarkeit. Dann vollständiges Product-Schema.
- **C:** Ratgeber „Vorbereitung“ (Text von der Live-Seite), weitere Ratgeber nach Suchanfragen aus der Search Console.

## 7. Empfohlene nächste Schritte
1. Domain-Freigabe → Abgleich alt/neu und Übernahme von Titles, URLs, Presse und Partnern.
2. Staging mit Passwort → Lighthouse- und axe-Läufe, Rich-Results-Test.
3. Go-live: Sitemap in der Search Console einreichen, Weiterleitungen beobachten, 4 Wochen Klicks und CTR mit der Baseline vergleichen (341 Klicks, 3.361 Impressionen, 10,15 % CTR).
4. Google-Profil: Kategorien „Schmiedekurs/Messerschmied“, Fotos, Beiträge zu Kursterminen. Bewertungen nach Kursen aktiv erbitten (nur echte).
