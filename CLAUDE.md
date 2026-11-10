# Projektregeln – Website Messermanufaktur Ulbrich

Laravel 13 + Filament 5. Projektstandards: Skill `ulbrich-website`, ergänzend `ultimate-seo-geo-engine`.

## Grenzen
- Keine Änderungen an Live-Website, Domain, DNS, Hosting, echten Buchungen oder Zahlungen ohne ausdrückliche Freigabe im aktuellen Chat.
- Keine Admin-Bereiche oder nicht verlinkten Pfade der Live-Seite abtasten.
- Nichts erfinden (Bewertungen, Zahlen, Stahlsorten, Maße, Biografie, Termine). Fehlendes mit `<x-ui.missing>` markieren.

## Code
- Fakten nur in `config/ulbrich.php` bzw. Datenbank; Templates und JSON-LD lesen von dort.
- Jede Seite bekommt ein `App\Support\PageMeta` (Title, Description 140–160 Zeichen, Pfad mit Slash am Ende).
- Neue URLs: klein, ohne Umlaute, mit `/` am Ende; in `config/pages.php` eintragen. Redirects nur über `config/redirects.php` + Begründung in `docs/redirects.md`.
- Keine `style`-Attribute (CSP), keine externen Skripte/Fonts, kein Admin-Markup in öffentlichen Views.
- Nach Änderungen: `php artisan test`, `npm run build`.
