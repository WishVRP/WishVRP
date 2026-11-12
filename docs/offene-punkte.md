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
- [ ] **Versandkosten Ausland** eintragen: Verwaltung → Versandkosten → EU / Europa / Welt, Preise aus der DHL-Preisliste (dhl.de) oder eurem DHL-Vertrag. Bis dahin: „auf Anfrage“. Inlandspreise (5 kg 7,69 €, 10 kg 10,49 €) ebenfalls kurz mit dhl.de abgleichen.
- [ ] **Versandgewicht** bei jedem Produkt eintragen (inkl. Verpackung). Ohne Angabe gelten 1.500 g.
- [ ] Optional **DHL-Express-Konto** für echte Live-Preise ins Ausland (`DHL_EXPRESS_*`)
- [ ] **Einfuhr-Regeln** (Anwalt/Zoll): für die Länder, in die ihr verschicken wollt, je Messerart klären, ob **erlaubt / gesperrt** – in Verwaltung → Einfuhr-Regeln eintragen (mit Quelle und Datum). Ohne Regel landet jede Auslandsbestellung als „prüfen“-Anfrage. Ausfuhr aus Deutschland (Messer i. d. R. ohne Genehmigung) und DHL-Beförderungsbedingungen mitprüfen.
- [ ] **Zoll**: je Ländergruppe festlegen, ob der Empfänger zahlt (Standard) oder ihr vorab berechnet – dann Zollsatz, Einfuhrumsatzsteuer und Pauschale eintragen. Zolltarifnummern (8211 92 00 / 8211 93 00) mit dem Zoll abgleichen.
- [ ] **Messerart** bei jedem Produkt wählen; bei Edition 100 bestätigen, ob Klapp- oder Einhandmesser
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

## 4. Rechtstexte (Launchpaket vom 12.11.2026 eingebaut)

Eingebaut sind: Impressum, AGB, Kursbedingungen, Gutscheinbedingungen, Versand & Zahlung (`/versand/#bedingungen`), Widerrufsbelehrung mit Muster-Formular, Online-Widerruf (`/widerrufen/`), lebenslange Schliff-Garantie, Datenschutzerklärung (an die echte Technik angepasst) und der Newsletter-Einwilligungstext. Die Texte liegen in `resources/legal/*.md`. Offene Angaben stehen auf der Seite als „Angabe folgt“.

**Noch auszufüllen (in den Texten markiert):**
- [ ] **Impressum:** Handelsregister (falls eingetragen) bzw. bestätigen, dass kein Eintrag besteht
- [ ] **Datenschutz:** Speicherfrist der Server-Logs bei IONOS (im IONOS-Vertrag bzw. Control-Center nachsehen)
- [ ] **Datenschutz:** Wie lange der Newsletter-Anmeldenachweis nach der Abmeldung aufbewahrt wird (Vorschlag: 3 Jahre)
- [ ] **Datenschutz:** Speicherdauer der Teilnahmedaten der Weihnachtsaktion (Vorschlag: bis 31.03.2027)
- [ ] **Datenschutz:** Speicherdauer abgelehnter und abgeschlossener Markt-Bewerbungen
- [ ] **Datenschutz:** Kanäle für Kursfotos (Website, Instagram, Facebook …?) und Löschfristen
- [ ] **Datenschutz:** Übermittlungsmechanismen der Zahlungsdienstleister (Stripe/SumUp: EU-US Data Privacy Framework bzw. Standardvertragsklauseln) bestätigen
- [ ] **Weihnachtsaktion:** Beginn der Aktion, Mindestalter und teilnahmeberechtigte Länder (`config/ulbrich.php` → `xmas`). Die Vorlage 10 aus dem Paket wurde nicht unverändert veröffentlicht; die Bedingungen auf `/weihnachtsaktion/` folgen ihrem Aufbau und lesen die Fakten aus der Konfiguration.

**Bitte entscheiden bzw. bestätigen:**
- [ ] **Kursbeginn 09:00 Uhr** angenommen. Davon hängt die 48-Stunden-Umbuchungsfrist ab (`booking.course_start_time`).
- [ ] Aus den Kursbedingungen habe ich den Satz **„Ein eigenes Angebot für Gruppenevents besteht nicht“** entfernt, weil es den Manager-Schmiedekurs und die Team-Seite gibt. Soll er doch rein, müssen diese Seiten angepasst werden.
- [ ] **Garantie:** Die bisherige „2 Jahre Garantie“ ist überall durch die **lebenslange Schliff-Garantie** ersetzt (wie im Paket). Bitte bestätigen.
- [ ] **Umbuchung:** überall von „bis 7 Tage vorher“ auf **„2× kostenlos bis 48 Stunden vor Kursbeginn“** umgestellt; **maximal 4 Personen** pro gemeinsamer Buchung (vorher 6). Bezahlter Buchungswert 10 Jahre verwendbar.
- [ ] **Widerruf** für Gutscheine, Kurse und Sonderanfertigungen: Das Paket enthält die Widerrufsbelehrung für Waren. Ob für Kurse (fester Termin) und Anfertigungen nach Kundenwunsch eigene Hinweise im Bestellablauf nötig sind, bitte anwaltlich bestätigen.
- [ ] Ein **Cookie-Banner** ist nicht eingebaut und nicht nötig: Die Seite setzt nur zwei technisch notwendige Cookies, hat keine Statistik mit Cookies und lädt nichts von fremden Servern (Datenschutz Abschnitt 7).
- [ ] Optionale **Foto-/Video-Einwilligung** bei der Kursbuchung: wird eingebaut, sobald die Kanäle feststehen.

**Weiterhin offen:**
- [ ] **Steuer:** Standard ist jetzt „inkl. 19 % MwSt.“ (USt-IdNr. DE328830313 aus dem Paket). Wenn doch Kleinunternehmer: `ULBRICH_TAX_MODE=kleinunternehmer` in die `.env`.
- [ ] Nach § 312f BGB: AGB und Widerrufsbelehrung als dauerhafter Datenträger zur Auftragsbestätigung (derzeit Links in jeder Bestätigungsmail) – ggf. als PDF-Anhang
- [ ] Text der Checkbox „Bitte um Bewertung per E-Mail“ prüfen lassen (BGH VI ZR 225/17)
- [ ] Nach Einrichtung des Mailservers: `php artisan ulbrich:mail-test …` an eigene Adresse und alle Mails durchsehen
- [ ] Newsletter an Bestandskunden ohne Einwilligung (§ 7 Abs. 3 UWG): nur nach Prüfung
- [ ] Messerversand: Altersprüfung (ab 18) bei Versand, Hinweise zum Waffengesetz (§ 42a WaffG Führverbot)
- [ ] Englische Rechtstexte (Imprint, Privacy, Terms) – bisher nur Deutsch

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
- [x] Maximal 4 Personen pro gemeinsamer Buchung (Launchpaket)
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
- [x] Kunst- & Handwerkermarkt: Online-Standplan mit Bewerbung (wie auf der alten Seite) – umgesetzt
- [ ] Markt: **Termin** eintragen (Verwaltung → Markt-Bewerbungen → „Markt-Einstellungen“: Text **und** erster/letzter Markttag – dann erscheint der Markt bei Google als Veranstaltung) sowie **Öffnungszeiten** der Markttage
- [ ] Markt-PDFs: Die Marktordnung enthält unten den Kasten „Wichtiger Hinweis … vor Veröffentlichung prüfen“, der Standmietvertrag einen Muster-Hinweis – vor dem Go-live rechtlich prüfen lassen und diese Hinweise in der Endfassung entfernen; Vertrag: Feld „Veranstalter“ vorausfüllen
- [ ] Datenschutzerklärung: Abschnitt Markt-Bewerbungen (Kontaktdaten, Fotos, Speicherdauer)
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
