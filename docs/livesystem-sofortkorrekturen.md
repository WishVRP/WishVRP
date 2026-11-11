# Livesystem messermanufaktur-ulbrich.de – priorisierte Sofortkorrekturen

Stand 10.11.2026. **Am Livesystem wurde nichts verändert.**

**Wichtig zur Datengrundlage:** Die Live-Domain war aus der Entwicklungsumgebung gesperrt (Netzwerk-Allowlist), und der aktuelle Quellcode liegt nur auf dem Server. Diese Liste beruht auf den Befunden aus dem freigegebenen Relaunch-Konzept und dem Projektwissen (Impressum, Google-Profil, Kursseiten). Jeder Punkt hat deshalb einen Prüfschritt. Admin-Bereiche wurden bewusst **nicht** abgetastet.

Rechtliche Punkte sind Prüfpunkte für Steuerberater/Anwalt, keine Rechtsberatung.

## Priorität A – sofort (Sicherheit, Geld, Recht)

### A1 Admin-Authentifizierung serverseitig absichern
**Risiko:** Wenn Admin-Funktionen über Markup, JavaScript oder versteckte Formulare im öffentlichen HTML erreichbar sind oder ein Passwort im Browser geprüft wird, kann jeder Besucher sie umgehen (Quelltext ansehen, Request nachbauen) und Preise, Buchungen oder Gutscheine manipulieren.
**Prüfen (am Server, durch Jeremy):**
1. Quelltext jeder öffentlichen Seite nach `admin`, `login`, `password`, `passwort`, `token`, `apikey`, `edit`, `delete` durchsuchen (Browser → Seitenquelltext; im Code: `grep -ri` im Webroot).
2. Jede PHP-Datei, die schreibt (Preise, Termine, Buchungen, Gutscheine, Uploads), muss **vor jeder Aktion** eine serverseitige Session prüfen (`session_start()` + Prüfung `$_SESSION['admin']` o. ä.). Ein JavaScript-Check oder ein Passwort im HTML zählt nicht.
3. Passwörter nur als `password_hash()`/`password_verify()`, nicht im Klartext oder in JS.

**Sofortmaßnahme (vor jedem Code-Umbau möglich):** Admin-Verzeichnis per Webserver schützen – `.htaccess` mit HTTP-Basic-Auth (`AuthType Basic` + `.htpasswd` außerhalb des Webroots) oder IP-Beschränkung. Danach alle Admin-Passwörter ändern.
**Danach:** Admin-Markup aus öffentlichen Templates entfernen; CSRF-Token in Admin-Formularen; Login-Rate-Limit; Zugriffslogs der letzten Wochen auf ungewöhnliche POST-Requests prüfen.

### A2 Widersprüchliche Steuerangabe (§ 19 UStG vs. „inkl. MwSt.“)
**Problem:** Das Impressum nennt die Kleinunternehmerregelung (§ 19 UStG), an Preisen steht teils „inkl. MwSt.“. Beides zugleich ist widersprüchlich; wer als Kleinunternehmer Umsatzsteuer ausweist, schuldet sie ggf. trotzdem (§ 14c UStG).
**Maßnahme:** Mit Steuerberater den tatsächlichen Status klären (Umsatzgrenzen beachten), dann **eine** Formulierung überall: Kleinunternehmer → „Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.“; regelbesteuert → „inkl. 19 % MwSt.“ + USt-IdNr. im Impressum. Shop, Kursseiten, Gutscheine, Rechnungen, Bestätigungs-Mails, AGB prüfen.

### A3 Widersprüchliche Preis- und Zahlungsangaben
**Problem:** Preise/Zahlungsarten sind laut Konzept an verschiedenen Stellen unterschiedlich angegeben (z. B. Kurspreise in Übersicht vs. Kursseite vs. Buchung; Zahlungsarten in AGB vs. Checkout).
**Prüfen:** Tabelle anlegen: je Kurs, Gutschein, Messer → Preis auf Übersicht, Detailseite, Buchungsstrecke, AGB, Bestätigungs-Mail. Ebenso Zahlungsarten in AGB, Checkout, FAQ.
**Maßnahme:** Der in der Buchungsstrecke berechnete Preis ist verbindlich; alle anderen Stellen daran angleichen. Nur tatsächlich angebotene Zahlungsarten nennen. „ab“-Preise klar als solche kennzeichnen (PAngV: Gesamtpreis vor Bestellung).

### A4 Buchungen und Gutscheine serverseitig prüfen
**Prüfen:** Wird der Preis serverseitig berechnet (nicht aus einem Formularfeld übernommen)? Wird ein Platz erst nach bestätigter Zahlung fest gebucht? Sind Gutscheincodes zufällig (≥ 12 Zeichen) und serverseitig gegen Datenbank + Restwert geprüft, mit Rate-Limit?
**Maßnahme:** Falls nein: Preisfelder aus dem Formular ignorieren, serverseitig aus Kurs-ID berechnen; Gutscheinprüfung serverseitig.

## Priorität B – diese Woche (Recht, Vertrauen)

| # | Punkt | Maßnahme |
| --- | --- | --- |
| B1 | Link/Hinweis auf EU-OS-Plattform | Entfernen (Plattform seit 20.07.2025 abgeschaltet). |
| B2 | Uneinheitliche Ortsangabe („Neuenstadt-Stein“, „Stein am Kocher“, teils „Möckmühl“) | Eine Schreibweise festlegen, überall identisch (Impressum, Footer, Kontakt, Google-Profil, Schema). |
| B3 | Zwei E-Mail-Adressen (info@ / gmx) | Eine festlegen; die andere nur weiterleiten. |
| B4 | Countdown-/Rabatt-Aktionen | Nur mit echtem Enddatum; abgelaufene entfernen. Keine künstliche Knappheit. |
| B5 | Checkout-Button | Muss „zahlungspflichtig bestellen“ (o. ä. eindeutig) lauten; Pflichtinfos davor. |
| B6 | Widerrufsrecht bei terminierten Kursen | § 312g Abs. 2 Nr. 9 BGB anwaltlich prüfen; Belehrung und Kursbedingungen angleichen. |
| B7 | Messerversand | Altersprüfung beim Versand und Hinweis zu § 42a WaffG prüfen lassen. |
| B8 | Gutscheinbedingungen | Gültigkeit (gesetzlich 3 Jahre zum Jahresende), Teileinlösung, Restguthaben schriftlich festlegen. |
| B9 | Biografie-Angaben ohne Beleg (z. B. Ritterorden, Eiserne Hand) | Bis zum Beleg entfernen. |

## Priorität C – mit dem Relaunch / laufend

| # | Punkt | Maßnahme |
| --- | --- | --- |
| C1 | Google-Unternehmensprofil-Kategorien falsch (u. a. Möbelgeschäft) | Kategorien korrigieren (z. B. Messerschmied/Kurse), NAP angleichen. |
| C2 | Sicherheits-Header | HSTS, X-Content-Type-Options, X-Frame-Options/CSP frame-ancestors, Referrer-Policy am Server setzen. |
| C3 | Dubletten-URLs (`/index.html`, `/shop.html`, `*.php`-Rechtsseiten) | Erst mit dem Relaunch per 301 umleiten (Map in `config/redirects.php`), nicht vorher einzeln. |
| C4 | Backups | Vor jeder Änderung: Dateien + Datenbank sichern, Wiederherstellung einmal testen. |

## Neu bestätigt durch Prüfung der öffentlichen Live-Seite (11.11.2026)

Geprüft wurden nur öffentliche, in Sitemap oder Footer verlinkte Seiten. Admin-Pfade wurden **nicht** aufgerufen, und am Server wurde nichts verändert.

| # | Befund (öffentlich sichtbar) | Priorität | Maßnahme |
| --- | --- | --- | --- |
| L1 | Die Startseite verlinkt im HTML auf `/kursbuchung/admin.php` und enthält Texte eines Admin-Logins („Admin Login“, „Falsches Passwort“) | **A** | Link und Login-Markup aus öffentlichen Seiten entfernen, Admin nur serverseitig geschützt (siehe A1) |
| L2 | **Steuer widersprüchlich:** Impressum „Gemäß § 19 UStG wird keine Umsatzsteuer berechnet“, Edition-100-Seite „Gesamtpreise inklusive gesetzlicher Umsatzsteuer“ | **A** | Mit dem Steuerberater klären, dann überall einheitlich (siehe A2) |
| L3 | **Zwei E-Mail-Adressen:** Impressum info@messermanufaktur-ulbrich.de, Startseite zusätzlich eine gmx-Adresse | B | Eine Adresse festlegen. Die neue Website nutzt info@ |
| L4 | **Ortsschreibweise uneinheitlich:** „74196 Neuenstadt-Stein“ (Impressum), „74196 Stein am Kocher“ (Markt), „Neuenstadt am Kocher“ (Google/Postanschrift) | B | Eine Schreibweise wählen und überall gleich verwenden (NAP) |
| L5 | Produktseite `/shop/messer/damast-warm/` zeigt das Bild `messer-anime-gravur.jpg` (Messer mit One-Piece-Motiv) | B | Bild ersetzen, da lizenzpflichtiges Motiv |
| L6 | Impressum verweist noch auf **§ 5 TMG** und die **EU-OS-Plattform** | B | TMG wurde durch das DDG ersetzt. Die OS-Plattform wurde 2025 eingestellt, der Link ist entbehrlich. Anwaltlich prüfen |
| L7 | Datenschutz nennt **Google Fonts** (IP-Übertragung an Google) und **Formspree (USA)** | B | Schriften lokal einbinden (die neue Website tut das), Formulardienst prüfen |
| L8 | Viele Meta-Descriptions sind nach 155 Zeichen **mitten im Satz abgeschnitten**, einige Titles sind sehr kurz („Ein Zeichen für euch. | Ulbrich“) | C | Mit dem Relaunch gelöst (eigene Titles/Descriptions, Tests) |

## Was ich für A1–A4 von dir brauche

- ~~Freigabe der Domain~~ erledigt (11.11.2026). Für A1/A4 (serverseitige Prüfung) weiterhin: Quellcode-Export ohne Zugangsdaten.
- Deine ausdrückliche Freigabe, bevor irgendetwas am Server geändert wird.
