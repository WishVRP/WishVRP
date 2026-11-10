# Änderungsprotokoll Redirect-Map (`config/redirects.php`)

| Datum | Alt | Neu | Begründung |
| --- | --- | --- | --- |
| 10.11.2026 | `/index.html`, `/index.php` | `/` | Dublette der Startseite (294 Klicks auf `/`) |
| 10.11.2026 | `/shop.html` | `/shop/` | Dublette, `/shop/` hat 5 Klicks |
| 10.11.2026 | `/rechtliches.html` | `/impressum/` | Technik-URL, Rechtsseiten neu strukturiert |
| 10.11.2026 | `/shop/sonderanfertigung/` | `/sonderanfertigungen/` | Dublette |
| 10.11.2026 | `/kursbuchung/regeln.php` | `/kursbedingungen/` | Technik-URL |
| 10.11.2026 | `*/bedingungen.php`, `/shop/bedingungen/` | `/agb/` | Dubletten |
| 10.11.2026 | `*/widerruf.php`, `/shop/widerruf/` | `/widerruf/` | Dubletten |
| 10.11.2026 | `*/datenschutz.php` | `/datenschutz/` | Dublette |

Grundlage: Projektskill, Abschnitt 4. Vor Go-live gegen den vollständigen Search-Console-Export prüfen (Impressionen der Alt-URLs).
