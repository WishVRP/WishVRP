#!/usr/bin/env bash
# Erzeugt aus resources/fotos/original/ die Web-Fassungen in public/images/fotos/
# (Zuschnitt, AVIF + WebP + JPEG in mehreren Breiten, ohne Metadaten).
# Voraussetzung: ImageMagick 6.9+/7 mit WebP-Unterstützung.
# AVIF zusätzlich, wenn avifenc vorhanden ist (apt-get install libavif-bin).
# Aufruf: bash scripts/fotos-erzeugen.sh   — danach config/fotos.php prüfen (Breiten/Maße).
set -euo pipefail
cd "$(dirname "$0")/.."
SRC=resources/fotos/original
OUT=public/images/fotos
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$OUT"

# Einheitlicher Look: warm, dunkel, Kontrast, abgedunkelte Ränder – Messer und Personen bleiben unverändert.
#   werkstatt = Handyfotos aus der Halle · tisch = Handyfotos auf hellem Tisch (stärker abgedunkelt)
#   studio    = Studiobilder (nur leichte Vignette, Sättigung angeglichen)
grade() {
    local file=$1 look=$2 w h
    w=$(identify -format '%w' "$file"); h=$(identify -format '%h' "$file")
    case $look in
        werkstatt) convert "$file" -modulate 92,82 -sigmoidal-contrast 3.5,45% -fill '#2b1a0b' -colorize 9% \( -size "${w}x${h}" radial-gradient:white-'#555555' \) -compose multiply -composite "$file" ;;
        tisch)     convert "$file" -modulate 90,80 -sigmoidal-contrast 3.5,50% -fill '#24160a' -colorize 10% \( -size "${w}x${h}" radial-gradient:white-'#404040' \) -compose multiply -composite "$file" ;;
        studio)    convert "$file" -modulate 100,90 \( -size "${w}x${h}" radial-gradient:white-'#7a7a7a' \) -compose multiply -composite "$file" ;;
    esac
}

# name | quelle | look | zuschnitt (Breite x Höhe + x + y, leer = ganzes Bild)
crop() {
    local name=$1 src=$2 look=$3 geo=${4:-}
    if [[ -n $geo ]]; then
        convert "$SRC/$src" -auto-orient -crop "$geo" +repage "$TMP/$name.png"
    else
        convert "$SRC/$src" -auto-orient "$TMP/$name.png"
    fi
    grade "$TMP/$name.png" "$look"
}

# Nur Studiobilder mit KI-Hintergrund (Messer echt, Entscheidung Auftraggeber 10.11.2026).
crop damast-detail           klappmesser-collage-logo.jpg studio    468x490+0+534
crop klappmesser-hell        klappmesser-collage-logo.jpg studio    1300x330+120+188  # ohne Logo-Schriftzug
crop klappmesser-hell-detail klappmesser-collage-logo.jpg studio    612x490+480+534
crop klappmesser-griff       klappmesser-collage-logo.jpg studio    436x490+1100+534
crop edition-messer          edition-banner.jpg           studio    816x658+720+0     # ohne eingebrannten Text
crop punze-detail            edition-banner.jpg           studio    400x300+760+240
crop klappmesser-geschlossen klappmesser-geschlossen.jpg  studio
crop klappmesser-offen       klappmesser-offen-diagonal.jpg studio
crop klappmesser-stehend     klappmesser-offen-stehend.jpg studio
crop klappmesser-rotholz     klappmesser-rotholz.jpg      studio
crop klappmesser-zebrano     klappmesser-zebrano.jpg      studio

# Ausnahme auf Wunsch des Auftraggebers (10.11.2026): echtes Foto von Andreas an der Esse
crop andreas-esse            andreas-esse-screenshot.jpg  werkstatt 1440x1920+0+600   # Instagram-Oberfläche entfernt
crop andreas-portrait        andreas-esse-screenshot.jpg  werkstatt 960x1200+60+640

for f in "$TMP"/*.png; do
    name=$(basename "$f" .png)
    w=$(identify -format '%w' "$f")
    sizes=""
    for size in 480 800 1200 1600; do (( size <= w )) && sizes+=" $size"; done
    (( w < 1200 && w > 480 && w != 800 )) && sizes+=" $w"
    [[ -z $sizes ]] && sizes=$w
    for size in $sizes; do
        convert "$f" -strip -resize "${size}x" -quality 80 "$OUT/$name-$size.jpg"
        convert "$f" -strip -resize "${size}x" -quality 78 "$OUT/$name-$size.webp"
        if command -v avifenc >/dev/null; then
            convert "$f" -strip -resize "${size}x" "$TMP/avif-src.png"
            avifenc -q 55 -s 6 --jobs all "$TMP/avif-src.png" "$OUT/$name-$size.avif" >/dev/null
        fi
    done
    echo "$name: $(identify -format '%wx%h' "$f")"
done

# Social-Vorschau 1200×630: Klinge mittig, Ränder aus unscharfer Kopie
convert "$SRC/klappmesser-collage-logo.jpg" -crop 1340x520+100+0 +repage "$TMP/og-band.png"
convert "$TMP/og-band.png" -resize 1340x704^ -gravity center -extent 1340x704 -blur 0x30 -modulate 60 "$TMP/og-bg.png"
convert "$TMP/og-bg.png" "$TMP/og-band.png" -gravity center -composite -resize 1200x630! -strip -quality 82 public/images/og-startseite.jpg
echo "og-startseite: 1200x630"
