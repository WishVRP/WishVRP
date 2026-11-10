# Fotos – Stand 10.11.2026

**Entscheidung des Auftraggebers (10.11.2026):** Auf der Website erscheinen nur die Studiobilder. Auf ihnen sind die Messer echt, aus eigener Fertigung und vor Ort vorhanden; nur die Hintergründe sind KI-generiert. Handyfotos aus Werkstatt und Kursen werden nicht verwendet. Ihre Originale sind aus dem Projekt entfernt: Andreas an der Esse, die Werkstatt mit Gruppe, der Messertisch und das Messer mit Gravur.

Originale liegen in `resources/fotos/original/`. Die Web-Fassungen erzeugt `bash scripts/fotos-erzeugen.sh`. Das Skript schneidet zu, gleicht die Bilder im einheitlichen Look an (leichte Vignette, Sättigung angeglichen) und speichert sie ohne Metadaten als WebP und JPEG in mehreren Breiten. Die Metadaten stehen in `config/fotos.php`.

## Verwendung auf der Startseite

| Abschnitt | Bild |
| --- | --- |
| 1 Hero | `klappmesser-offen` + Funken-Animation |
| 2 Intro | `klappmesser-geschlossen` |
| 3 Schmiedekurs-Erlebnis | `klappmesser-hell-detail`, `damast-detail`, `punze-detail` |
| 4 Kurs-Finder | **keine Fotos**: Kursname als typografische Grafik (`<x-ui.type-tile>`) in der Farbe der Kategorie |
| 6 Messer-Raster | `klappmesser-stehend`, `-rotholz`, `-griff`, `-hell`, `-zebrano` |
| 7 Edition 100 | `edition-messer` (Ausschnitt aus dem Banner, ohne eingebrannten Text) |
| 7b Galerie | 8 Studiobilder |
| 8 Sonderanfertigungen | `klappmesser-griff` |
| 9 Gutscheine | `klappmesser-zebrano` |
| 10 Herstellungsprozess | ohne Fotos (Icons und Schritte) |
| 11 Andreas Ulbrich | `andreas-portrait` (echtes Foto, auf Wunsch), darunter `klappmesser-hell` |
| 12 Schmieden im Team | Damast-Textur statt Foto |
| 14 Ratgeber | Thema als typografische Grafik |
| og:image | `public/images/og-startseite.jpg` (aus der Studio-Collage) |

## Offen

- **Holzscheiben-Bilder** (`klappmesser-rotholz`, `klappmesser-zebrano`): Ist der Hintergrund KI-generiert oder ist es ein echtes Foto? Ist es ein echtes Foto, ersetzen wir sie, damit nur KI-Bilder bleiben. Der Punkt steht im Audit.
- Für ein Porträt von Andreas oder Bilder aus den Kursen gilt dieselbe Regel: nur verwenden, wenn ausdrücklich gewünscht.
- Regel für neue Bilder: Die KI darf den Hintergrund verändern. Messer, Damastmuster, Griff und Proportionen müssen dem Original entsprechen.
- Bewusst nicht verwendet: Text im Bild (Banner), der Freisteller in zu geringer Auflösung (653 px) und das Logo aus der Collage (ersetzt kein SVG-Logo).
