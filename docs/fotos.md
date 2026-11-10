# Fotos – Stand 10.11.2026

14 Bilder wurden im Chat geliefert. Zwei davon waren doppelt. Originale liegen unverändert in `resources/fotos/original/`. Die Web-Fassungen erzeugt `bash scripts/fotos-erzeugen.sh`: Sie werden zugeschnitten, ohne Metadaten als WebP und JPEG gespeichert, in den Breiten 480, 800 und 1200, und landen in `public/images/fotos/`. Die Metadaten (Maße, Alt-Text, offene Prüfpunkte) stehen in `config/fotos.php`.

Eingebunden werden sie mit `<x-ui.media foto="key" format pos sizes>`. Die Seitenverhältnisse bleiben wie bei den bisherigen Bildflächen, mit `width` und `height` gesetzt, sodass nichts springt. Bis auf das Hero-Bild laden alle Bilder verzögert (lazy).

## Verwendung auf der Startseite (Version 3)

| Abschnitt | Foto(s) |
| --- | --- |
| 1 Hero | `andreas-esse` + Funken-Animation |
| 2 Manufaktur-Intro | `klappmesser-geschlossen` |
| 3 Schmiedekurs-Erlebnis | `werkstatt-kurs`, `damast-detail`, `punze-detail` |
| 4 Kurs-Finder (Kartenkopf je Kategorie) | Einstieg `werkstatt-amboss` · Klinge `messer-kuechenmesser` · Damast `damast-detail` · Teams `werkstatt-halle` |
| 6 Messer-Raster | `klappmesser-stehend`, `klappmesser-rotholz`, `messer-kuechenmesser`, `messer-tisch`, `klappmesser-zebrano` |
| 7 Edition 100 | `klappmesser-offen` |
| 7b Galerie „Aus der Werkstatt“ | 8 Messerbilder, wischbar |
| 8 Sonderanfertigungen | `griff-detail` |
| 9 Gutscheine | `klappmesser-zebrano` hinter dem Gutschein |
| 10 Herstellungsprozess | Fotoband `esse-feuer`, `werkstatt-amboss`, `damast-detail`, `griff-detail` |
| 11 Andreas & Werkstatt | `andreas-portrait`, `werkstatt-halle` |
| 12 Schmieden im Team | `werkstatt-kurs` (Hintergrund) |
| 14 Ratgeber | `werkstatt-amboss`, `esse-feuer` |
| 15 Kontakt | `werkstatt-halle` |
| og:image | `public/images/og-startseite.jpg` |

Mehrere Bilder sind Ausschnitte aus demselben Foto: `andreas-esse` liefert auch `andreas-portrait` und `esse-feuer`, `werkstatt-kurs` liefert auch `werkstatt-halle` und `werkstatt-amboss`. Sobald es mehr Fotos gibt, ersetzen wir diese Ausschnitte. Es gibt keinen Bildplatz mehr. Ein Foto der Außenansicht ist optional und steht als stiller Prüfpunkt im Audit.

## Bewusst nicht verwendet

- **Messer mit One-Piece-Gravur und Scheide:** Die Figur und das Logo sind urheber- und markenrechtlich geschützt (Eiichiro Oda / Shueisha) und dürfen ohne Lizenz nicht in der Werbung verwendet werden. Im Tischfoto ist der untere Teil mit diesem Messer weggeschnitten.
- **Banner „Limitiert auf 100 Stück · 699 €“:** Der Text ist ins Bild eingebrannt, ist damit weder lesbar für Suchmaschinen noch barrierefrei, und der Preis wäre doppelt gepflegt. Die Aussagen stehen bereits als echter Text auf der Seite.
- **Messer mit Gravur:** Nur der Griff wird verwendet (`griff-detail`), die Klinge mit dem Motiv ist abgeschnitten.
- **Freisteller auf Weiß (PNG):** Die Auflösung ist zu gering (653 px).
- **Collage mit Logo:** nur als Ausschnitt für das Damastdetail und das Social-Bild verwendet. Das Logo darin ersetzt kein SVG-Logo.

## Geklärt

- **Studiobilder** (`klappmesser-geschlossen`, `-offen`, `-stehend`, `damast-detail`, og:image): Laut Auftraggeber am 10.11.2026 zeigen sie die tatsächlich gefertigten Messer, die vor Ort vorhanden sind; nur die Hintergründe sind KI-generiert. Damit ist das Produkt korrekt dargestellt. Regel für neue Bilder: Messer, Damastmuster, Griff und Proportionen dürfen nicht verändert werden, nur der Hintergrund. Eine Kennzeichnungspflicht nach Art. 50 KI-VO greift nach unserer Einschätzung nicht, weil keine Person, kein Ort und kein Ereignis täuschend echt dargestellt wird (keine Rechtsberatung, bei Zweifel kurz mit dem Anwalt abstimmen, der ohnehin AGB/Widerruf prüft).

## Vor Go-live zu klären (`php artisan ulbrich:audit` listet sie)

1. **Werkstattfoto mit Gruppe:** Die Personen sind erkennbar, dafür braucht es eine Einwilligung der Abgebildeten (DSGVO/KUG). Alternativ ein Foto, auf dem niemand erkennbar ist.
2. **Hero-Foto:** Andreas muss die Verwendung freigeben. Soll das Bild mit Zigarette das erste sein, was man sieht? Besser wäre eine Aufnahme in voller Auflösung von der Kamera statt eines Story-Screenshots.
3. **Edition-100-Zuordnung:** Welche der Klappmesser gehören zur Edition 100? Die Alt-Texte behaupten das bewusst nicht.
4. AVIF fehlt noch, weil auf diesem Rechner kein Encoder installiert ist. WebP und JPEG reichen für alle Browser.
