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

## Ohne SSH (nur FTP) – fertiges Paket `ulbrich-test.zip`

Das Paket enthält alles: Programmcode, Abhängigkeiten, gebaute Designdateien und eine vorbereitete Datenbank mit den Kursen. **Es enthält keine Passwörter oder Schlüssel.**

1. **Beim Hoster:**
   - Subdomain `test` anlegen
   - Zielordner `ulbrich-test/public` (neuer Ordner, nicht der der jetzigen Website)
   - PHP 8.3 einstellen (Erweiterungen `pdo_sqlite`, `intl`, `mbstring`, meist schon aktiv)
   - SSL einschalten
2. **Zip entpacken** auf dem eigenen Rechner. Den Ordner `ulbrich-test` komplett per FTP (z. B. FileZilla) hochladen, auf dieselbe Ebene, die beim Hoster als Ziel gewählt wurde.
   - Dauert je nach Leitung 10–30 Minuten (viele kleine Dateien).
   - In FileZilla vorher „Server → Anzeige versteckter Dateien erzwingen“ aktivieren, damit `.env` und `.htaccess` mit hochgeladen werden.
3. **Datei `ulbrich-test/.env` auf dem Server bearbeiten** (FileZilla: Rechtsklick → Ansehen/Bearbeiten). Nur die ersten zwei Zeilen ändern:
   - `APP_URL=` die echte Testadresse, z. B. `https://test.messermanufaktur-ulbrich.de`
   - `STAGING_PASSWORD=` ein eigenes langes Passwort. Ohne Passwort bleibt die Seite absichtlich gesperrt (Fehler 503).
4. **Schreibrechte:** Die Ordner `storage`, `bootstrap/cache`, `database` und `public/storage` brauchen Schreibrechte (FileZilla: Rechtsklick → Dateiberechtigungen → 775, „in Unterverzeichnisse“). Bei den meisten Hostern ist das schon so.
5. **Aufrufen:** Testadresse öffnen.
   - Benutzername `ulbrich`, Passwort = das aus Schritt 3
6. **Admin-Konto anlegen:** `https://test.…/verwaltung/register` öffnen, Name, E-Mail und Passwort eingeben, danach den QR-Code mit einer Authenticator-App scannen (Google Authenticator, Microsoft Authenticator o. Ä.). Die Wiederherstellungscodes sicher aufbewahren.
   - Danach ist die Registrierung automatisch geschlossen. Es kann sich niemand weiter registrieren.

Automatisch geregelt:

- Der geheime App-Schlüssel wird beim ersten Aufruf selbst erzeugt (`storage/app/.app-key`).
- Bild-Uploads funktionieren ohne Symlink.
- E-Mails werden nur protokolliert, nicht verschickt. Die Weihnachtsaktion zeigt den Spiel-Link deshalb direkt auf der Seite.
- Online-Zahlung ist aus.

Bei Fehler 500: Die Datei `storage/logs/laravel.log` herunterladen und den Inhalt (ohne Passwörter) an Claude geben.

## Wieder abschalten

Subdomain im Hoster-Menü löschen und den Ordner `ulbrich-test` entfernen. Die Hauptseite bleibt davon unberührt.
