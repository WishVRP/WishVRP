#!/usr/bin/env bash
# Update-Paket für die Testumgebung (FTP): nur neue und geänderte Dateien seit dem letzten gelieferten Stand.
# Aufruf: bash scripts/update-paket.sh [ZIELORDNER] [BASIS-COMMIT]
#   Mit BASIS-COMMIT: Sammelpaket ab diesem Stand (z. B. mehrere Updates zusammen); der Paketstand bleibt unverändert.
#   Basis = Commit in docs/paket-stand.txt (letztes geliefertes Paket). Danach wird der Stand fortgeschrieben.
# Ergebnis: ulbrich-update-JJJJ-MM-TT-HHMM.zip mit
#   ulbrich-test/…            → per FTP über den bestehenden Ordner laden (überschreiben)
#   LOESCHEN.txt              → Dateien, die auf dem Server gelöscht werden sollen (falls vorhanden)
#   LIESMICH.txt              → Liste aller Dateien im Paket
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p "${1:-.}"; OUT=$(cd "${1:-.}" && pwd)
BASE=${2:-$(grep -oE '^[0-9a-f]{40}' docs/paket-stand.txt | tail -1)}
BASE=$(git rev-parse "$BASE")
HEAD=$(git rev-parse HEAD)
[[ "$BASE" == "$HEAD" ]] && { echo "Keine Änderungen seit dem letzten Paket."; exit 0; }

# Nur Dateien, die auf dem Server gebraucht werden
SKIP='^(tests/|docs/|scripts/|\.github/|\.claude/|CLAUDE\.md|README|phpunit\.xml|package(-lock)?\.json|vite\.config|\.env\.example|\.editorconfig|\.gitattributes|\.gitignore|resources/fotos/original/)'
CHANGED=$(git diff --name-only --diff-filter=ACMRT "$BASE" "$HEAD" | grep -Ev "$SKIP" || true)
DELETED=$(git diff --name-only --diff-filter=D "$BASE" "$HEAD" | grep -Ev "$SKIP" || true)

TMP=$(mktemp -d); trap 'rm -rf "$TMP"' EXIT
mkdir -p "$TMP/ulbrich-test"
for f in $CHANGED; do mkdir -p "$TMP/ulbrich-test/$(dirname "$f")"; git show "$HEAD:$f" > "$TMP/ulbrich-test/$f"; done

# Design/Skripte geändert → gebaute Dateien (public/build) neu
if git diff --name-only "$BASE" "$HEAD" | grep -qE '^(resources/(css|js)/|vite\.config|package-lock\.json)'; then
    npm run build >/dev/null
    mkdir -p "$TMP/ulbrich-test/public" && cp -r public/build "$TMP/ulbrich-test/public/"
fi
# Abhängigkeiten geändert → vendor komplett (selten)
# Nur neue/geänderte Pakete + Autoloader (vendor/composer, vendor/autoload.php) – nicht den ganzen vendor-Ordner.
if git diff --name-only "$BASE" "$HEAD" | grep -qE '^composer\.lock$'; then
    echo "composer.lock geändert – neue Pakete und Autoloader werden mitgeliefert."
    VT=$(mktemp -d)
    git show "$HEAD:composer.json" > "$VT/composer.json"; git show "$HEAD:composer.lock" > "$VT/composer.lock"
    mkdir -p "$VT/app" "$VT/database/seeders" "$VT/database/factories"
    composer install --no-dev --no-scripts --optimize-autoloader --no-interaction -q --working-dir="$VT"
    PKGS=$(python3 - "$BASE" "$HEAD" <<'PY'
import json, subprocess, sys
def lock(rev):
    try: return {p['name']: p['version'] for p in json.loads(subprocess.check_output(['git', 'show', rev + ':composer.lock']))['packages']}
    except subprocess.CalledProcessError: return {}
a, b = lock(sys.argv[1]), lock(sys.argv[2])
print('\n'.join(k for k, v in b.items() if a.get(k) != v))
PY
)
    mkdir -p "$TMP/ulbrich-test/vendor"
    cp -r "$VT/vendor/composer" "$VT/vendor/autoload.php" "$TMP/ulbrich-test/vendor/"
    for p in $PKGS; do mkdir -p "$TMP/ulbrich-test/vendor/$(dirname "$p")"; cp -r "$VT/vendor/$p" "$TMP/ulbrich-test/vendor/$p"; done
    rm -rf "$VT"
fi
# Dateien der Verwaltung (Filament) mitgeben, wenn Filament aktualisiert wurde oder FILAMENT_ASSETS=1
if git diff --name-only "$BASE" "$HEAD" | grep -qE '^composer\.lock$' || [[ "${FILAMENT_ASSETS:-0}" == 1 ]]; then
    for d in css js fonts; do [[ -d public/$d/filament ]] && { mkdir -p "$TMP/ulbrich-test/public/$d"; cp -r "public/$d/filament" "$TMP/ulbrich-test/public/$d/"; }; done
fi

# Neue Klassen in app/ brauchen keinen neuen Autoloader: Composer findet sie über PSR-4 (App\\ → app/).

[[ -n "$DELETED" ]] && echo "$DELETED" | sed 's#^#ulbrich-test/#' > "$TMP/LOESCHEN.txt"
{
    echo "Update für die Testumgebung – $(date +%d.%m.%Y)"
    echo "Den Ordner ulbrich-test per FTP über den bestehenden Ordner laden (Dateien überschreiben)."
    echo "Die .env auf dem Server bleibt unverändert. Die Datenbank aktualisiert sich beim nächsten Aufruf selbst."
    [[ -n "$DELETED" ]] && echo "Zusätzlich die Dateien aus LOESCHEN.txt auf dem Server löschen."
    echo; echo "Enthaltene Dateien:"; (cd "$TMP" && find ulbrich-test -type f | sort | grep -vE '^ulbrich-test/(vendor|public/(css|js|fonts)/filament)/'; [[ -d ulbrich-test/public/js/filament ]] && echo "ulbrich-test/public/{css,js,fonts}/filament/… (Dateien der Verwaltung, komplett)"; if [[ -d ulbrich-test/vendor ]]; then echo "ulbrich-test/vendor/… (neue Pakete: $(cd ulbrich-test/vendor && ls -d */*/ 2>/dev/null | grep -v '^composer' | tr '\n' ' '), dazu vendor/composer und vendor/autoload.php)"; fi)
} > "$TMP/LIESMICH.txt"

NAME="ulbrich-update-$(date +%Y-%m-%d-%H%M)${2:+-sammel}.zip"
(cd "$TMP" && zip -qr9 "$OUT/$NAME" .)
[[ -z "${2:-}" ]] && echo "$HEAD $(date +%Y-%m-%d) $NAME" >> docs/paket-stand.txt
echo "Erstellt: $OUT/$NAME ($(echo "$CHANGED" | grep -c . || true) Dateien geändert, $(echo "$DELETED" | grep -c . || true) gelöscht)"
