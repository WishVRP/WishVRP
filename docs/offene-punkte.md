# Offene Punkte – alles, was noch nachgetragen werden muss

Stand: 11.11.2026. Gesammelt aus dem gesamten Projektverlauf.
Abhaken mit `[x]`. Fehlende Angaben stehen auf der Website als neutrale Platzhalter (`<x-ui.missing>`); `php artisan ulbrich:audit` listet sie jederzeit auf.

---

## 1. Testen der Vorschau (jetzt)

**Wichtig:** Die Vorschau ist nur ein Entwicklungsstand. An der Live-Website wurde nichts verändert.

| Was | Wo | Hinweis |
|---|---|---|
| Startseite, alle Inhalte | `/` | Platzhalter = noch fehlende Angaben |
| Schneeballschlacht (Gewinnspiel) | `/weihnachtsaktion/` | Anmelden, dann erscheint in der Vorschau der Spiel-Link direkt auf der Seite (live kommt er per E-Mail). Pro E-Mail-Adresse nur 1 Versuch; pro Internetanschluss 3 Anmeldungen am Tag. |
| Kursbuchung mit Kalender | `/schmiedekurse/buchen/` | 1 Kurs pro Tag, ab 2 Tage im Voraus, 5 Jahre buchbar |
| Shop | `/shop/` und Unterkategorien | Erst sichtbar, wenn im Admin Produkte angelegt sind |
| Gutschein kaufen | `/geschenke/schmiedekurs-gutschein/` | |
| Edition 100 (Nummernraster) | `/klappmesser/sonderedition/` | 1–19 sind nur Platzhalter für „verkauft“ |
| Englische Seiten | `/en/` | |
| Adminbereich | `/verwaltung/` | Testzugang: `test@ulbrich.local`, Passwort per Chat. In der Vorschau ohne Authenticator, live ist die 2-Faktor-Anmeldung Pflicht. |

Online-Zahlung (Stripe) ist in der Vorschau aus. Buchungen und Käufe laufen deshalb als Anfrage oder Überweisung. E-Mails werden in der Vorschau nicht verschickt, sondern nur protokolliert.

---

## 2. Konten und Dienste anlegen (macht ihr selbst, Schlüssel nie in den Chat)

- [ ] **Stripe-Konto** (Online-Zahlung Kurse, Shop, Gutscheine)
  - Firmendaten, Bankkonto und Identität bei Stripe verifizieren
  - Zahlarten aktivieren: Karte, ggf. Apple Pay/Google Pay, Klarna, SEPA
  - Test-Schlüssel zum Ausprobieren, danach Live-Schlüssel
  - Webhook anlegen: `https://<domain>/stripe/webhook/`, Ereignisse:
    - `checkout.session.completed`
    - `checkout.session.async_payment_succeeded`
    - `checkout.session.async_payment_failed`
    - `checkout.session.expired`
  - Werte auf dem Server in `.env` eintragen: `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`. Erst dann ist die Online-Zahlung aktiv.
- [ ] **E-Mail-Postfach / SMTP** für info@: Zugangsdaten in die `.env` (`MAIL_*`), dazu SPF, DKIM und DMARC im DNS. Ohne das gehen weder Spiel-Links noch Newsletter oder Bestellbestätigungen raus.
- [ ] **Google Search Console** und **Bing Webmaster Tools**: Domain bestätigen, `sitemap.xml` einreichen
- [ ] **Google Unternehmensprofil**: Daten an die Website angleichen (Name, Adresse, Telefon, Öffnungszeiten, Link)
- [ ] **Google Merchant Center** (Produkte in der Google-Shopping-Suche)
  - Feed `https://<domain>/produkte.xml`
  - Rückgabe- und Versandrichtlinie hinterlegen
- [ ] Optional **seven.io** (nur wenn die SMS-Bestätigung statt E-Mail gewünscht ist; kostet pro SMS): `SEVEN_API_KEY`, `XMAS_VERIFICATION=sms`, AV-Vertrag abschließen
- [ ] **Bewertungslink** aus dem Google-Unternehmensprofil („Bewertungen anfordern“) in der Verwaltung → Bewertungen eintragen. Er steckt im Knopf der Mails nach Zustellung und nach dem Kurs.
- [ ] Optional **DHL-API-Key** (developer.dhl.com, „Shipment Tracking – Unified“, kostenlos) → `DHL_API_KEY`: Zustellungen werden automatisch erkannt.
- [ ] Optional **Brevo** o. Ä. als Newsletter-Versanddienst, falls die Mengen für das eigene Postfach zu groß werden

---

## 3. Server und Go-live (nur mit eurer ausdrücklichen Freigabe)

- [ ] Hosting mit PHP 8.3 auswählen, dann Domain und DNS umstellen
- [ ] `.env` auf dem Server: `APP_ENV=production`, `APP_KEY`, `APP_URL`, `MAIL_*`, `STRIPE_*`, `ADMIN_PATH` (eigenen, nicht erratbaren Admin-Pfad wählen), `NEWSLETTER_AUTO`, `XMAS_VERIFICATION=email`
- [ ] **`SEO_INDEXABLE=true`** in der `.env` der echten Seite setzen (sonst `noindex` auf allen Seiten). Auf der Test-Subdomain bleibt es aus.
- [ ] `php artisan migrate --force`, `php artisan storage:link`, `npm run build`
- [ ] Cron-Job jede Minute: `php artisan schedule:run` (wöchentlicher Newsletter, Rabattcodes, Ablauf von Reservierungen)
- [ ] Admin-Konten für Andreas und dich: `php artisan make:filament-user`, danach den Authenticator am Handy einrichten. Den Testzugang `test@ulbrich.local` vorher löschen.
- [ ] **IBAN und Kontoinhaber** für die Bestellbestätigung bei Überweisung in die `.env`: `BANK_HOLDER`, `BANK_IBAN`, `BANK_BIC`, `BANK_NAME`
- [ ] Weiterleitungen der alten URLs nach dem Umzug prüfen (`config/redirects.php`, `docs/redirects.md`)
- [ ] Ablauf einmal komplett durchspielen: Anfrage → Zahlungsinfo → Zahlung → Bestätigung. Zuerst mit Stripe-Testschlüssel, dann mit echtem Kleinbetrag.
- [ ] Sicherungen (Datenbank `database/database.sqlite` und `storage/app/public`) beim Hoster einrichten

---

## 4. Rechtstexte (Anwalt / IT-Recht-Kanzlei)

- [ ] **Steuer:** Kleinunternehmer (§ 19 UStG) oder „inkl. MwSt.“? Mit dem Steuerberater klären; davon hängen Preisangaben und Rechnungen ab.
- [ ] AGB für Shop, Kurse, Gutscheine und Sonderanfertigungen
- [ ] Widerrufsbelehrung inkl. Ausnahmen: Kurse mit festem Termin (§ 312g Abs. 2 Nr. 9 BGB), Anfertigungen nach Kundenwunsch
- [ ] Rückgabe- und Versandbedingungen (auch für das Merchant Center)
- [ ] Garantieerklärung „2 Jahre bei richtiger Pflege“ nach § 479 BGB formulieren (Ausschluss Spülmaschine, Kulanz)
- [ ] Teilnahmebedingungen der Weihnachtsaktion (Gewinnspiel, einmal pro Person, Codes gültig bis 31.12.2026)
- [ ] Datenschutzerklärung ergänzen um:
  - Besucherstatistik (ohne Cookies)
  - Formulare und Bestellungen
  - Weihnachtsaktion
  - Google-Bewertungen (Abruf über die Google Places API, serverseitig, keine Besucherdaten an Google)
  - Newsletter mit Double-Opt-in
  - Stripe
  - E-Mail-Hoster
  - ggf. seven.io oder Brevo
- [ ] Bestellbestätigung prüfen lassen: Nach § 312f BGB müssen Vertragsinhalt, AGB und Widerrufsbelehrung dem Kunden auf einem dauerhaften Datenträger vorliegen. Die Mail verlinkt sie bisher nur. Sobald die Texte stehen, als PDF-Anhang oder Volltext in die Mail aufnehmen.
- [ ] Text der Checkbox „Bitte um Bewertung per E-Mail“ und Hinweis in den Mails prüfen (BGH VI ZR 225/17). Status-Mails (In Arbeit, Versendet, Zugestellt) sind Vertragskommunikation.
- [ ] Newsletter an Bestandskunden ohne Einwilligung (§ 7 Abs. 3 UWG): nur nach Prüfung
- [ ] Messerversand: Altersprüfung (ab 18) bei Versand, Hinweise zum Waffengesetz (§ 42a WaffG Führverbot)
- [ ] Englische Rechtstexte (Imprint, Privacy, Terms)
- [ ] Impressum aktualisieren (alte TMG-Hinweise raus, Verweis auf die OS-Plattform entfernen)

---

## 5. Fakten und Inhalte, die nur ihr liefern könnt

### Edition 100 (Damast-Klappmesser)
- [ ] Stahlsorten bzw. Damast und Lagenzahl, Klingen- und Gesamtlänge, Gewicht, Griffmaterial
- [ ] Preis
- [ ] Welche 19 Nummern sind wirklich verkauft? (aktuell 1–19 als Platzhalter)
- [ ] Bei bisherigen Käufern Einwilligung holen, falls ihr Name im Raster erscheinen soll

### Über uns / Person
- [ ] Biografie und Werdegang von Andreas, nur belegte Angaben
- [ ] Name und Stationen der 2. Generation
- [ ] Echtes Foto von Andreas an der Esse

### Kurse und Buchung
- [ ] Kursort bestätigen (Werkstatt-Adresse)
- [ ] Endgültige Preise pro Person (aktuell „ab“-Preise)
- [ ] Maximale Teilnehmerzahl pro Tag (vorläufig 6)
- [ ] Dürfen andere Personen an einem schon gebuchten Tag dazukommen?
- [ ] Tagesablauf des Damastkurses, Uhrzeiten
- [ ] Mindestalter
- [ ] Kleidung und Sicherheit (feste Schuhe, Baumwolle …)
- [ ] Übernachtungstipps
- [ ] Öffnungszeiten (wie im Google-Profil)
- [ ] Parken und Anfahrt mit ÖPNV
- [ ] Außenfoto der Werkstatt

### Leistungen und Preise
- [ ] Preisrahmen für:
  - Schärfen (vor Ort und per Post)
  - Reparatur
  - Gravur
  - Sonderanfertigungen
- [ ] Lieferzeit und Anzahlung für Sonderanfertigungen
- [ ] Teamevents: Gruppengrößen, Formate, Preise
- [ ] Ringe (Eheringe):
  - Materialien
  - Preise
  - Ablauf
- [ ] Gutscheine:
  - Gültigkeit bestätigen (vorläufig 3 Jahre zum Jahresende)
  - Sind Teileinlösungen möglich?
- [ ] Markt/Event: Datum, Uhrzeiten, Standplan bzw. Anmeldung für Aussteller
- [ ] Partner: welche Kooperationen gibt es konkret?

### Bewertungen, Fotos, Referenzen
- [ ] **Google-Bewertungen automatisch:** API-Schlüssel für die „Places API (New)“ in der Google Cloud anlegen, in die `.env` (`GOOGLE_PLACES_KEY`) eintragen, dann Verwaltung → Bewertungen → „Google verbinden“. Schritt für Schritt in `docs/verwaltung.md`.
- [ ] Freigegebene Kursfotos und Projektbeschreibungen
- [ ] Zebrano- und Rotholz-Bilder: KI-Hintergrund oder echt? Falls KI, besser durch echte Fotos ersetzen.
- [ ] **Neue Fotos** für die Website liefern (Wunsch: Bilder überarbeiten). So geht's: `docs/fotos.md` → „Neue Fotos – so liefern“. Dateinamen, Alttexte und Bild-Sitemap werden dann automatisch passend angelegt.
- [ ] Echte Produkte im Admin anlegen: Fotos, Beschreibung, Preis, Bestand, Kategorie

### Einheitliche Schreibweisen (wichtig für Google und KI)
- [ ] Ortsname überall gleich schreiben (Name, Adresse, Telefon identisch auf Website, Google und Verzeichnissen)
- [ ] Eine E-Mail-Adresse festlegen: info@ oder die GMX-Adresse?

---

## 6. Live-Website (nur Hinweise, nichts geändert)

Details in `docs/livesystem-sofortkorrekturen.md`. Umsetzung nur mit Freigabe:

- [ ] L1: Admin-Link `/kursbuchung/admin.php` und Login-Texte aus dem öffentlichen HTML entfernen (dringend)
- [ ] L2: Steuer widersprüchlich (§ 19 UStG im Impressum, aber „inkl. MwSt.“ bei Edition 100)
- [ ] L3: zwei E-Mail-Adressen (info@ und GMX), auf eine festlegen
- [ ] L4: Ortsschreibweise uneinheitlich (Neuenstadt-Stein / Stein am Kocher)
- [ ] L5: Produktbild mit One-Piece-Motiv (Bildrechte) ersetzen
- [ ] L6: Impressum: § 5 TMG → DDG, Hinweis auf die EU-OS-Plattform entfernen
- [ ] L7: Google Fonts und Formspree (USA) in der Datenschutzerklärung bzw. auf der Seite
- [ ] L8: abgeschnittene Meta-Descriptions und sehr kurze Titles

---

## 7. Später / optional

- [ ] Newsletter an Bestandskunden (nach rechtlicher Prüfung)
- [ ] Anbindung Brevo, Twilio als SMS-Alternative
- [ ] Regelmäßig neue Ratgeberartikel und Projekte für die Sichtbarkeit bei Google und KI
- [ ] Nach dem Go-live: Search-Console-Daten nach 4–6 Wochen auswerten und Texte nachschärfen
