# Messermanufaktur Ulbrich – Website-Relaunch

Neue Website für die Messermanufaktur Ulbrich (Andreas Ulbrich, Messerschmied, Neuenstadt am Kocher).
Laravel 13 · Filament 5 · Blade · eigenes Designsystem · PHP 8.3+

> Unabhängiges Entwicklungsprojekt. Es ist **nicht** mit der Live-Domain oder dem Live-Server verbunden.

## Lokal starten

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # legt die 6 Kursformate an, kein Admin-Konto
npm install && npm run build
php artisan filament:assets
php artisan serve                   # http://127.0.0.1:8000
```

Verwaltung: `php artisan make:filament-user`, dann `http://127.0.0.1:8000/verwaltung` – beim ersten Login muss 2FA (Authenticator-App) eingerichtet werden.

## Prüfen

```bash
php artisan test                    # Meta, Schema, Redirects, Sicherheit
php artisan ulbrich:audit           # listet alle [FEHLT]/[FOTO]-Platzhalter (Server muss laufen)
```

## Wo was liegt

| Pfad | Inhalt |
| --- | --- |
| `config/ulbrich.php` | **einzige Quelle** für Firmendaten, Steuerstatus, Buchungsregeln |
| `config/pages.php` | Inventar aller Bestands-URLs mit Status und GSC-Klicks |
| `config/redirects.php` | 301-Map (Dubletten/Technik-URLs) |
| `resources/css/` | Designsystem (Tokens, Basis, Komponenten, Bereiche) |
| `resources/views/components/` | Blade-Komponenten |
| `app/Support/` | PageMeta (SEO), Schema (JSON-LD) |
| `app/Http/Middleware/` | Redirects, Trailing Slash, Sicherheits-Header |
| `docs/` | ADRs, Designsystem, SEO-Plan, Status, Livesystem-Sofortkorrekturen |

## Regeln

Siehe `CLAUDE.md`. Kurz: nichts erfinden, URLs erhalten, keine Änderungen an Live-System/Domain ohne ausdrückliche Freigabe.
