# ADR 0001 – Laravel 13 + Filament 5 als Plattform

Status: angenommen · Datum: 10.11.2026

## Kontext

Der aktuelle Quellcode der Live-Seite liegt nur auf dem Server und war aus der Entwicklungsumgebung nicht zugänglich. Die Domain war im Netzwerk der Entwicklungsumgebung gesperrt. Deshalb ist das ein **neues, unabhängiges Projekt**. Das Livesystem bleibt unverändert.

Anforderungen: serverseitig gerenderte Seiten (SEO, Core Web Vitals), Verwaltung für Kurse, Termine, Gutscheine und Messer, Buchung mit gehostetem Checkout, Admin nur serverseitig mit 2FA, Betrieb auf bezahlbarem Hosting.

## Entscheidung

| Baustein | Wahl | Version (geprüft 10.11.2026) |
| --- | --- | --- |
| Framework | Laravel | 13.42 (PHP ≥ 8.3) |
| Verwaltung | Filament | 5.11.2 (PHP ≥ 8.2, MFA eingebaut) |
| Templates | Blade, anonyme Komponenten | – |
| CSS | eigenes, token-basiertes CSS ohne Framework | – |
| JS öffentlich | ~30 Zeilen Vanilla JS (Menü) | – |
| Build | Vite 8 + laravel-vite-plugin | – |
| Schriften | @fontsource (Source Serif 4, IBM Plex Mono), lokal gebündelt | – |
| Datenbank | MySQL/MariaDB in Produktion, SQLite lokal/Tests | – |
| Zahlung | gehosteter Checkout (Mollie oder Stripe) + Webhook | noch nicht umgesetzt |

Tailwind aus dem Laravel-Skelett wurde entfernt: Die Website braucht ein eigenes Erscheinungsbild, ~6 KB CSS (gzip) statt Utility-Klassen im Markup und keine Abhängigkeit vom Build-Tooling für Designänderungen. Filament bringt sein eigenes CSS mit.

## Folgen

- Fakten stehen an genau einer Stelle (`config/ulbrich.php`, Tabelle `courses`) und werden von Templates **und** JSON-LD gelesen. So können Seite und Schema nicht auseinanderlaufen.
- Kurspreise kommen aus der Datenbank und sind in Filament pflegbar.
- Mehr Komponenten, die selbst gepflegt werden müssen (dafür kein Template-Look).

## Hosting-Voraussetzungen (vom Hoster bestätigen lassen)

- PHP **8.3 oder 8.4** mit `pdo_mysql`, `mbstring`, `intl`, `openssl`, `fileinfo`, `gd` oder `imagick`, `zip`, `bcmath`, `sodium`
- Composer 2 auf dem Server **oder** Build-Artefakt per Deploy-Skript hochladen
- Document Root auf `public/` setzbar (nicht das Projektverzeichnis!)
- Apache mit `mod_rewrite` und `mod_headers` **oder** Nginx (Regeln aus `public/.htaccess` übertragen)
- Cronjob (`php artisan schedule:run` minütlich) und Queue (Cron-basiert reicht)
- SSH-Zugang für Deployment; HTTPS mit automatischer Verlängerung
- MySQL ≥ 8.0 oder MariaDB ≥ 10.6
- Staging-Subdomain mit Passwortschutz

Ohne SSH und ohne veränderbaren Document Root ist Laravel nur mit Workarounds betreibbar – dann Hosting-Wechsel einplanen.

In der Entwicklungsumgebung geprüft: PHP 8.3.6 mit allen genannten Erweiterungen, Composer 2.8, Node 22. **Das Hosting selbst ist nicht geprüft** (kein Zugang).
