# ADR 0002 – URL-Erhalt, Schrägstrich am Ende, Redirect-Map

Status: angenommen · Datum: 10.11.2026

## Kontext

Die Live-Seite bekommt laut Search Console (09.08.–08.11.2026) 341 Klicks bei 3.361 Impressionen (CTR 10,15 %), davon 294 auf `/`, 22 auf `/schmiedekurse/`, 5 auf `/shop/`. Alle bestehenden URLs enden auf `/`.

Laravel liefert `/pfad` und `/pfad/` mit derselben Route aus. Die mitgelieferte `.htaccess` leitet außerdem `/pfad/` per 301 auf `/pfad` um, und `redirect()->to()` entfernt den Schrägstrich. Ohne Anpassung hätten **alle** Bestands-URLs einen zusätzlichen 301 bekommen.

## Entscheidung

1. Bestehende sprechende URLs bleiben 1:1 (`config/pages.php` = Inventar).
2. Kanonische Form ist **mit** Schrägstrich. `EnsureTrailingSlash` leitet die Variante ohne Slash per 301 um, Query-Parameter bleiben erhalten (`/schmiedekurse/buchen/?kurs=…&teilnehmer=…`).
3. Die Laravel-Regel „Trailing Slashes entfernen“ ist aus `public/.htaccess` entfernt.
4. Dubletten und Technik-URLs: `config/redirects.php`, ausgewertet von `LegacyRedirects` **vor** dem Routing und vor der Slash-Normalisierung → genau ein Sprung aufs Ziel.
5. Redirects setzen einen relativen `Location`-Header (kein Einfluss des Host-Headers).
6. Canonicals und Sitemap zeigen immer auf `SEO_CANONICAL_HOST`.

## Absicherung

`tests/Feature/UrlStructureTest.php` prüft für jede Map-Zeile: 301, Ziel korrekt, keine Kette; Slash-Normalisierung mit Query; robots/sitemap. Der Test-Client ist in `tests/TestCase.php` so angepasst, dass er den Schrägstrich nicht entfernt.

## Offen

- Vollständige URL-Liste der Live-Seite (Sitemap + Search-Console-Export „Seiten“) gegen `config/pages.php` abgleichen, insbesondere die 6 Kursseiten, Shop-Unterseiten und Ratgeber-Artikel.
- Jede weitere Redirect-Zeile in `docs/redirects.md` mit Search-Console-Daten begründen.
