# Designsystem Messermanufaktur Ulbrich

Prinzip: Qualität wird durch Material und Handwerk belegt, nicht durch Effekte. Gestaltungsquelle ist die Metallurgie.

Dateien: `resources/css/tokens.css` (alle Werte) · `base.css` · `components.css` · `sections.css` · Blade-Komponenten in `resources/views/components/`.

## Farben

| Rolle | Token | Wert | Verwendung |
| --- | --- | --- | --- |
| Fläche | `--steel-50` / `--steel-100` | #f2f3f4 / #e6e8ea | Seitengrund, abgesetzte Abschnitte |
| Text | `--scale-900` (Zunder) | #1c1e20 | Fließtext, dunkle Abschnitte |
| Kurse / Hauptaktion | `--temper-blue` | #2d5f93 | „Termin finden“, Kurs-Buttons |
| Messer | `--temper-brown(-ink)` | #a06d3e / #7a4f27 | Edition 100, Shop |
| Gutscheine | `--temper-violet` | #74558c | Gutschein-Wege |
| Punzen, Akzente auf Dunkel | `--temper-straw` | #d9b45f | Seriennummer, Buttons auf Zunder |
| Fehlende Daten | `--signal-missing` | #a1361f | nur `[FEHLT]`-Marker |

Bewusst vermieden: Schwarz + Feuer-Orange, Creme + Terrakotta.

### Kontraste (rechnerisch nach WCAG 2.x, 10.11.2026)

| Paar | Verhältnis |
| --- | --- |
| Text auf Stahl-50 | 15,1 : 1 |
| Sekundärtext #5b6269 auf Stahl-50 / -100 | 5,6 / 5,0 : 1 |
| Link #234c78 auf Stahl-50 | 8,0 : 1 |
| Weiß auf Blau / Violett / Braun-ink | 6,6 / 6,1 / 7,1 : 1 |
| Zunder auf Stroh-Button | 9,4 : 1 |
| Stroh auf Zunder | 8,5 : 1 |
| [FEHLT]-Rot auf Markergrund | 6,0 : 1 |

Alle ≥ 4,5 : 1 (AA für normalen Text). Ein Browser-Audit (axe/Lighthouse) ist **noch nicht** gelaufen.

## Typografie

- **Source Serif 4** (variabel, optische Größen) für Überschriften und Lesetext.
- **IBM Plex Mono** (400/500) für Navigation, Buttons, Spezifikationen, Punzen.
- Beide lokal über `@fontsource`, von Vite mit Hash gebündelt, `font-display: swap`, nur Latin-Subsets werden per `unicode-range` geladen.
- Keine kursiven/farbigen Einzelwörter in Überschriften, keine Versal-Labels über Abschnitten.

## Raster und Form

8-px-Raster (`--space-1` … `--space-9`), Container 82 rem, Textbreite 40 rem. Radien 2–4 px („geschliffene Kante“). Touch-Ziele ≥ 44 px (Buttons 46 px, Footer-Links 44 px).

## Visuelle Richtung (Version 2, 10.11.2026)

Dunkle „Schmiede“-Flächen (`.is-dark`, Zunder #0e0f10) wechseln mit hellen Stahlflächen. Eine generative **Damast-Textur** (`public/images/damast-textur.svg`, Grafik, kein Foto) liegt dezent auf dunklen Abschnitten und in den Bildflächen. Anlassfarben bleiben Funktionsfarben: Blau = Kurse, Braun = Messer, Violett = Gutscheine, Stroh = Akzent auf Dunkel.

Abwechselnde Layouts: Vollbild-Hero, Statement-Typografie, Bild-Collage, Kursraster mit Filter, Kalender, Editorial-Raster (Messer), Edition-Inszenierung mit 100er-Raster, Split-Layouts, Gutschein-Objekt, horizontale Prozess-Schritte, Sticky-Porträt, Vollbreiten-Banner (Teams), große Bewertungszahl.

## Bildflächen statt Fotos

`<x-ui.media motif tone format>` erzeugt eine gestaltete Fläche (Damast-Textur + Anlassfarben-Licht) mit dezentem Etikett „Bildplatz · Motiv“. Echte Fotos ersetzen sie 1:1 (gleiches Seitenverhältnis → kein Layoutsprung). Fehlende Angaben erscheinen öffentlich neutral als „Angabe folgt“ oder gar nicht (`silent`); intern bleiben sie über `data-missing` markiert (`php artisan ulbrich:audit`).

## Bewegung

- Anlassfarben-Skala im Hero läuft einmal ein.
- Scroll-Reveal (IntersectionObserver, 24 px / Deckkraft), Edition-Raster baut sich spaltenweise auf.
- Hero-Textur driftet beim Scrollen (CSS scroll-driven animation, nur wo unterstützt).
- Mikrointeraktionen: Lichtkante über Buttons, wachsende Unterstreichung, Pfeile, Kachel-Zoom, Gutschein hebt sich.
- Alles aus bei `prefers-reduced-motion`; ohne JavaScript ist alles sichtbar.

## Komponenten

| Blade | Zweck | Regel |
| --- | --- | --- |
| `<x-ui.button href variant>` | Aktion | primary · secondary · violet · brown · straw. Text sagt, was passiert. |
| `<x-ui.punze key>` | eingeschlagener Stempel | **nur echte Fakten** (Preis, Dauer, Kammer, Nº) |
| `<x-ui.missing ask label silent>` | fehlende Angabe | öffentlich „Angabe folgt“ oder unsichtbar, intern `data-missing` |
| `<x-ui.media motif tone format>` | Bildfläche bis Foto da ist | tone: forge · steel · violet · brown |
| `<x-ui.kicker color>` | technische Zeile über Überschrift | nicht über jedem Abschnitt, kein Versal |
| `<x-ui.icon name>` | Linien-Icons (ein Satz) | 1,5 px, 24er Raster |
| `<x-ui.stars>` | Sterne für echte Gesamtbewertung | nie ohne Quelle und Datum |
| `<x-ui.price amount from>` | Preis im deutschen Format | `null` → „auf Anfrage“ |
| `<x-ui.tax-note>` | Steuerhinweis | aus `config('ulbrich.tax_mode')`, eine Quelle |
| `<x-ui.dimension cm max>` | Bemaßungslinie (SVG) | Klingenlänge maßstäblich |
| `<x-ui.temper-scale>` | Anlassfarben-Skala | Hero, läuft einmal ein, `prefers-reduced-motion` |
| `<x-site.header/footer/mobile-bar>` | Rahmen | Mobile-Leiste: Termine · Gutschein · Kontakt |
| `<x-seo.meta :meta>` | Title, Description, Canonical, OG, Twitter | aus `App\Support\PageMeta` |

