# Testumgebung auf einer Subdomain

Ziel: Die neue Website läuft unter einer eigenen Unteradresse, zum Beispiel `test.messermanufaktur-ulbrich.de`.

- Die bestehende Website und die Hauptdomain bleiben unverändert.
- Die ganze Testseite ist passwortgeschützt (Browser-Anmeldefenster).
- Für Google und andere Suchmaschinen ist sie gesperrt (`robots.txt` Disallow und `X-Robots-Tag: noindex`).

## Ihr macht beim Hoster (Zugangsdaten nie in den Chat)

1. Subdomain `test` anlegen.
2. Als Zielverzeichnis einen **neuen, leeren Ordner** wählen, z. B. `ulbrich-test/public`. Auf keinen Fall den Ordner der bestehenden Website verwenden.
3. Für die Subdomain **PHP 8.3** einstellen.
4. **SSL** (Let's Encrypt) für die Subdomain aktivieren.
5. Falls vorhanden, **SSH-Zugang** aktivieren. Das macht Einrichten und spätere Updates viel einfacher.

## Einrichtung mit SSH

```bash
# Repo ist privat: vorher einen GitHub-Deploy-Key (nur Lesen) für den Server einrichten
git clone -b claude/ulbrich-website-relaunch-iiwtf0 https://github.com/WishVRP/WishVRP.git ulbrich-test
cd ulbrich-test
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
# .env bearbeiten (siehe unten), dann:
touch database/database.sqlite
php artisan migrate --force --seed
php artisan storage:link
npm ci && npm run build      # oder: build-Ordner hochladen, falls kein Node auf dem Server
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan make:filament-user   # Admin-Konto, danach Authenticator einrichten
```

## .env der Testumgebung

```
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://test.messermanufaktur-ulbrich.de
STAGING_USER=ulbrich
STAGING_PASSWORD=<eigenes langes Passwort>
MAIL_MAILER=log          # keine echten E-Mails, solange getestet wird
XMAS_VERIFICATION=email
NEWSLETTER_AUTO=false
# STRIPE_* leer lassen (oder nur Stripe-TEST-Schlüssel)
```

- `ADMIN_MFA_REQUIRED` bleibt in der Testumgebung **true**: Die Seite ist öffentlich erreichbar, deshalb gilt die 2-Faktor-Anmeldung.
- Mit `MAIL_MAILER=log` zeigt die Weihnachtsaktion den Spiel-Link direkt auf der Seite an. So lässt sich ohne Postfach testen.

## Ohne SSH (nur FTP)

Ein fertiges Upload-Paket mit allen Abhängigkeiten wird gebaut: `vendor`, `public/build` und eine vorbereitete Datenbank. Es wird per FTP in den Ordner `ulbrich-test` hochgeladen. Schlüssel und Passwörter kommen nicht ins Paket. Die `.env` wird direkt beim Hoster angelegt.

## Wieder abschalten

Subdomain im Hoster-Menü löschen und den Ordner `ulbrich-test` entfernen. Die Hauptseite bleibt davon unberührt.
