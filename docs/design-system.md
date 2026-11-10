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

8-px-Raster (`--space-1` … `--space-9`), Container 76 rem, Textbreite 40 rem. Radien 2–4 px („geschliffene Kante“). Touch-Ziele ≥ 44 px (Buttons 46 px, Footer-Links 44 px).

## Komponenten

| Blade | Zweck | Regel |
| --- | --- | --- |
| `<x-ui.button href variant>` | Aktion | primary · secondary · violet · brown · straw. Text sagt, was passiert. |
| `<x-ui.punze key>` | eingeschlagener Stempel | **nur echte Fakten** (Preis, Dauer, Kammer, Nº) |
| `<x-ui.missing ask>` | `[FEHLT: …]`-Marker | sichtbar, bis echte Daten vorliegen |
| `<x-ui.photo motif format>` | Foto-Platzhalter `[FOTO: …]` | feste Seitenverhältnisse → kein Layoutsprung |
| `<x-ui.price amount from>` | Preis im deutschen Format | `null` → „auf Anfrage“ |
| `<x-ui.tax-note>` | Steuerhinweis | aus `config('ulbrich.tax_mode')`, eine Quelle |
| `<x-ui.dimension cm max>` | Bemaßungslinie (SVG) | Klingenlänge maßstäblich |
| `<x-ui.temper-scale>` | Anlassfarben-Skala | einziger inszenierter Moment, einmalig, `prefers-reduced-motion` |
| `<x-ui.section-head title spec>` | Abschnittskopf | Spec-Zeile nur mit echter Information |
| `<x-site.header/footer/mobile-bar>` | Rahmen | Mobile-Leiste: Termine · Gutschein · Kontakt |
| `<x-seo.meta :meta>` | Title, Description, Canonical, OG, Twitter | aus `App\Support\PageMeta` |

## Bewegung

Nur die Anlassfarben-Skala animiert beim Laden (transform/filter, 1,6 s). Sonst nur Hover/Fokus-Reaktionen. `prefers-reduced-motion` schaltet alles ab.
