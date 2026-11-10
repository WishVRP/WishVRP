#!/usr/bin/env bash
# Erzeugt aus resources/fotos/original/ die Web-Fassungen in public/images/fotos/
# (Zuschnitt, WebP + JPEG in mehreren Breiten, ohne Metadaten).
# Voraussetzung: ImageMagick 6.9+/7 mit WebP-Unterstützung.
# AVIF folgt, sobald auf dem Build-Rechner ein AVIF-Encoder vorhanden ist (hier nicht installiert).
# Aufruf: bash scripts/fotos-erzeugen.sh   — danach config/fotos.php prüfen (Breiten/Maße).
set -euo pipefail
cd "$(dirname "$0")/.."
SRC=resources/fotos/original
OUT=public/images/fotos
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT
mkdir -p "$OUT"

# name | quelle | zuschnitt (Breite x Höhe + x + y, leer = ganzes Bild)
crop() {
    local name=$1 src=$2 geo=${3:-}
    if [[ -n $geo ]]; then
        convert "$SRC/$src" -auto-orient -crop "$geo" +repage "$TMP/$name.png"
    else
        convert "$SRC/$src" -auto-orient "$TMP/$name.png"
    fi
}

crop andreas-esse           andreas-esse-screenshot.jpg  1440x1920+0+600   # Instagram-Oberfläche entfernt
crop werkstatt-kurs         werkstatt-kurs.jpg           1536x1150+0+700
crop messer-tisch           messer-tisch.jpg             1370x1195+0+0     # ohne One-Piece-Messer/-Scheide
crop damast-detail          klappmesser-collage-logo.jpg 468x490+0+534
crop klappmesser-geschlossen klappmesser-geschlossen.jpg
crop klappmesser-offen      klappmesser-offen-diagonal.jpg
crop klappmesser-stehend    klappmesser-offen-stehend.jpg
crop klappmesser-rotholz    klappmesser-rotholz.jpg
crop klappmesser-zebrano    klappmesser-zebrano.jpg

for f in "$TMP"/*.png; do
    name=$(basename "$f" .png)
    w=$(identify -format '%w' "$f")
    sizes=""
    for size in 480 800 1200 1600; do (( size <= w )) && sizes+=" $size"; done
    [[ -z $sizes ]] && sizes=$w
    for size in $sizes; do
        convert "$f" -strip -resize "${size}x" -quality 80 "$OUT/$name-$size.jpg"
        convert "$f" -strip -resize "${size}x" -quality 78 "$OUT/$name-$size.webp"
    done
    echo "$name: $(identify -format '%wx%h' "$f")"
done

# Social-Vorschau 1200×630: Klinge mittig, Ränder aus unscharfer Kopie
convert "$SRC/klappmesser-collage-logo.jpg" -crop 1340x520+100+0 +repage "$TMP/og-band.png"
convert "$TMP/og-band.png" -resize 1340x704^ -gravity center -extent 1340x704 -blur 0x30 -modulate 60 "$TMP/og-bg.png"
convert "$TMP/og-bg.png" "$TMP/og-band.png" -gravity center -composite -resize 1200x630! -strip -quality 82 public/images/og-startseite.jpg
echo "og-startseite: 1200x630"
