# ADR 0003 – Sicherheit: Verwaltung, Header, Indexierung

Status: angenommen · Datum: 10.11.2026

## Entscheidung

**Verwaltung (Filament)**
- Eigener Pfad über `ADMIN_PATH` (Standard `verwaltung`), nirgends verlinkt, **nicht** in `robots.txt` (das würde ihn verraten), `X-Robots-Tag: noindex`, `Cache-Control: no-store`.
- Authentifizierung ausschließlich serverseitig (Laravel-Session, CSRF, Filament-Login mit eingebautem Rate-Limit).
- **2FA per Authenticator-App ist Pflicht** (`multiFactorAuthentication(..., isRequired: true)`), Wiederherstellungscodes verschlüsselt gespeichert.
- Konten nur per `php artisan make:filament-user`; der Seeder legt **kein** Konto an.
- Test stellt sicher: öffentliches HTML enthält kein `password`-Feld, kein `admin`, `login`, `livewire` oder den Admin-Pfad.

**Öffentliche Seiten**
- CSP ohne `unsafe-inline`/`unsafe-eval`; JSON-LD und das Inline-Skript zur JS-Erkennung tragen eine Nonce. Keine `style`-Attribute (Test).
- `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP; HSTS nur in Produktion über HTTPS.
- Keine Drittanbieter-Requests: Schriften lokal, keine eingebettete Karte (Link „Route planen“ statt Google-Maps-iframe), kein Tracking.

**Indexierung**
- `SEO_INDEXABLE=false` überall außer Produktion → `noindex` per Meta und Header, `robots.txt` mit `Disallow: /`.
- Platzhalterseiten für noch nicht migrierte URLs existieren nur außerhalb der Produktion.

## Noch umzusetzen

- Staging: HTTP-Basic-Auth auf Webserver-Ebene.
- Buchung/Gutscheine: Checkout nur gehostet, Freischaltung per signiertem Webhook, Gutscheincodes zufällig ≥ 12 Zeichen, serverseitig geprüft, Rate-Limit auf Einlösung.
- Formulare: CSRF (Standard), Honeypot + Rate-Limit statt Tracking-Captcha.
- Uploads (Filament): nur Bildformate, neu kodieren, kein SVG.
