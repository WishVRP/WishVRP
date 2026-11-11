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

| 11.11.2026 | `/shop/einblicke/` | `/messerprojekte/` | Live-Seite mit 99 Wörtern, thematische Dublette der Messerprojekte |
| 11.11.2026 | `/shop/gutscheine/` | `/geschenke/` | Live-Seite mit 97 Wörtern; Gutscheine für Messer und Kurse sind auf der Geschenke-Seite gebündelt |
| 11.11.2026 | `/shop/messer/damast-*/` (4) | `/shop/damastmesser/` | Einzelprodukte „Preis auf Anfrage“ mit 175–180 Wörtern. Ihre Produktfotos sind keine KI-Studiobilder (Vorgabe Auftraggeber), eines zeigt ein lizenzpflichtiges Motiv. Kategorie bündelt die Anfrage |
| 11.11.2026 | `/shop/messer/kuechenmesser-*/`, `kochmesser-klare-linie/`, `brotmesser-wellenschliff/` (6) | `/shop/kuechenmesser/` | wie oben |
| 11.11.2026 | `/shop/messer/messer-gravierte-scheide/` | `/shop/einzelstuecke/` | wie oben |
| 11.11.2026 | `/shop/sonderanfertigung/` | `/sonderanfertigungen/` | bestätigt: Live-Seite hat 105 Wörter, Dublette der ausführlichen Seite (629 Wörter) |

**Hinweis:** Sobald echte Produktfotos (oder Studiobilder) der Einzelstücke vorliegen und ein Shop mit Bestellung freigegeben ist, können die Produkt-URLs wieder als eigene Seiten geführt werden. Dann diese Weiterleitungen entfernen.

Grundlage: Projektskill, Abschnitt 4. Vor Go-live gegen den vollständigen Search-Console-Export prüfen (Impressionen der Alt-URLs).
