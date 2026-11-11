#!/usr/bin/env bash
# Vollpaket für FTP-Hosting (Testumgebung oder IONOS-Produktion): alles, was der Server braucht.
# Aufruf: bash scripts/vollpaket.sh ZIELORDNER [staging|production]
#   staging    → .env-Vorlage mit Passwortschutz, Mails nur im Log
#   production → .env-Vorlage für IONOS (MySQL, AUTO_SETUP, SEO_INDEXABLE)
# Enthält: Code, vendor (ohne Dev-Pakete), gebaute Designdateien, Filament-Dateien der Verwaltung.
# Enthält NICHT: Datenbank, Schlüssel, Passwörter, Testdaten.
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p "$1"; OUT=$(cd "$1" && pwd); MODE=${2:-staging}
TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
D="$TMP/ulbrich-$([[ $MODE == production ]] && echo live || echo test)"
mkdir -p "$D"
git archive HEAD | tar -x -C "$D"
rm -rf "$D"/{tests,docs,scripts,.github,.claude} "$D"/CLAUDE.md "$D"/AGENTS.md "$D"/phpunit.xml "$D"/database/database.sqlite
npm run build >/dev/null
cp -r public/build "$D/public/"
for d in css js fonts; do [[ -d public/$d/filament ]] && { mkdir -p "$D/public/$d"; cp -r "public/$d/filament" "$D/public/$d/"; }; done
mkdir -p "$D/public/storage"
(cd "$D" && composer install --no-dev --optimize-autoloader --no-interaction --no-scripts -q && composer dump-autoload -o --no-dev -q)
find "$D/vendor" -name .git -type d -prune -exec rm -rf {} +
rm -f "$D"/database/database.sqlite "$D"/storage/app/.app-key "$D"/storage/app/.migrated "$D"/storage/logs/*.log
cp "docs/env-$MODE.txt" "$D/.env"
# Root-.htaccess: falls die Domain auf den Projektordner statt auf /public zeigt
printf '<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteRule ^(?!public/)(.*)$ public/$1 [L]\n</IfModule>\n<FilesMatch "^\\.">\n    Require all denied\n</FilesMatch>\n' > "$D/.htaccess"

# Prüfen, dass nichts Wichtiges fehlt
for f in public/index.php public/build/manifest.json public/js/filament/filament/app.js public/css/filament/filament/app.css vendor/autoload.php artisan .env; do
    [[ -f "$D/$f" ]] || { echo "FEHLT im Paket: $f" >&2; exit 1; }
done
[[ ! -f "$D/database/database.sqlite" ]] || { echo "Datenbankdatei darf nicht ins Paket" >&2; exit 1; }

NAME="$(basename "$D")-$(date +%Y-%m-%d).zip"
(cd "$TMP" && zip -qr9 "$OUT/$NAME" "$(basename "$D")")
[[ $MODE == staging ]] && echo "$(git rev-parse HEAD) $(date +%Y-%m-%d) $NAME (Vollpaket)" >> docs/paket-stand.txt
echo "Erstellt: $OUT/$NAME ($(du -h "$OUT/$NAME" | cut -f1))"
