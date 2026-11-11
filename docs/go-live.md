# Go-live-Plan – messermanufaktur-ulbrich.de

Stand: 11.11.2026. Reihenfolge = Abhängigkeiten.
**Am Livesystem wird nichts ohne ausdrückliche Freigabe im Chat geändert.**
Details zu jedem Punkt: `docs/offene-punkte.md` und `docs/verwaltung.md`.

---

## Phase 1 – Muss vor dem Start erledigt sein (blockierend)

### 1. Rechtliches (Anwalt oder Rechtstexte-Dienst, z. B. IT-Recht Kanzlei / Händlerbund)
- [ ] **Steuer klären** (Steuerberater): Kleinunternehmer nach § 19 UStG oder „inkl. MwSt.“? Davon hängen alle Preisangaben und Rechnungen ab.
- [ ] **Impressum** aktualisieren: DDG statt TMG, Hinweis auf die EU-OS-Plattform entfernen
- [ ] **Datenschutzerklärung** ergänzen um:
  - Besucherstatistik ohne Cookies
  - Formulare
  - Newsletter
  - Weihnachtsaktion
  - Google-Bewertungen
  - Stripe
  - E-Mail-Versand
- [ ] **AGB**, **Widerrufsbelehrung** (inkl. Ausnahmen für Kurse mit festem Termin und für Anfertigungen nach Kundenwunsch), **Versand- und Rückgabebedingungen**, **Kursbedingungen**
- [ ] **Teilnahmebedingungen der Weihnachtsaktion**
- [ ] **Garantieerklärung** „2 Jahre bei richtiger Pflege“ (§ 479 BGB)
- [ ] **Messerversand**: Altersprüfung, Hinweise zum Waffengesetz
- [ ] Wortlaut der **Checkbox für die Bitte um Bewertung** prüfen lassen
- [ ] Eure fertigen Texte gebt ihr an Claude. Claude baut sie ein und hängt AGB und Widerruf an die Bestellbestätigung an (§ 312f BGB).

### 2. Fakten, die nur ihr kennt (an Claude schicken)
- [ ] Endgültige **Kurspreise** und **maximale Teilnehmerzahl** pro Tag
- [ ] **Öffnungszeiten**, Parken und Anfahrt
- [ ] **Edition 100**: Welche Nummern sind wirklich verkauft? Dazu Maße, Stahl und Griff
- [ ] **Preise für Leistungen**: Schärfen, Reparatur, Gravur, Anfertigung (darf als Spanne „ab …“ angegeben werden)
- [ ] **Einheitlicher Ortsname** und **eine E-Mail-Adresse** (info@ oder GMX?)
- [ ] Optional sofort, sonst später: neue Fotos, Bio von Andreas

### 3. Hosting und E-Mail (ihr, beim Hoster)
- [ ] Hoster bestätigt:
  - **PHP 8.3** für die Hauptdomain
  - **Cron-Job** möglich (Aufgabe jede Minute)
  - **SSL**
  - Bonus: **SSH-Zugang**
- [ ] **Postfach info@** für den Versand (SMTP-Zugangsdaten) und im DNS **SPF, DKIM, DMARC** eintragen, damit Mails nicht im Spam landen
- [ ] **Bankdaten** für die Bestellbestätigung bereitlegen (kommen nur in die `.env`)

---

## Phase 2 – Technischer Umzug (Claude bereitet vor, ihr führt aus, nach Freigabe)

### 4. Vorbereitung durch Claude
- [ ] **Produktionspaket** bauen (Vollpaket, ohne Testzugang und Testdaten)
- [ ] **Produktionsmodus für FTP-Hosting**: Datenbank-Updates und Schlüssel ohne SSH, aber abgesichert. Bisher gibt es das nur für die Testumgebung.
- [ ] **Weiterleitungen** der alten URLs ein letztes Mal gegen die Live-Sitemap prüfen (`config/redirects.php`)
- [ ] Eingegangene Rechtstexte und Fakten einbauen, Tests, abschließender SEO-Check

### 5. Umzug (ihr, Schritt für Schritt mit Anleitung)
1. [ ] **Sicherung der alten Website** komplett herunterladen (Dateien und ggf. Datenbank). Das ist euer Rückweg.
2. [ ] Produktionspaket in einen **neuen Ordner** hochladen, z. B. `ulbrich-live`. Die alte Seite läuft währenddessen weiter.
3. [ ] **`.env`** für die echte Seite anlegen (Vorlage kommt von Claude). Einträge:
   - `APP_ENV=production`, `APP_URL=https://messermanufaktur-ulbrich.de`
   - **`SEO_INDEXABLE=true`**, sonst bleibt die Seite für Google unsichtbar
   - `MAIL_*` (SMTP), `BANK_*`
   - `ADMIN_PATH=` eigener, nicht erratbarer Pfad
   - optional: `STRIPE_*`, `GOOGLE_PLACES_KEY`, `DHL_API_KEY`
4. [ ] Beim Hoster das **Zielverzeichnis der Hauptdomain** auf `ulbrich-live/public` umstellen. Das ist der eigentliche Wechsel und dauert Sekunden. Rückweg: Verzeichnis wieder auf die alte Seite stellen.
5. [ ] **Cron-Job** anlegen: `php artisan schedule:run` jede Minute
6. [ ] **Admin-Konten** für Andreas und dich anlegen, **Authenticator** einrichten. Den Testzugang gibt es auf der echten Seite nicht.

### 6. Abnahme direkt nach dem Wechsel (ca. 30 Minuten)
- [ ] Startseite, Kurse, Shop, Gutschein, Edition 100 und Weihnachtsaktion öffnen, auch am Handy
- [ ] **Testbuchung** mit eigener E-Mail: Bestellbestätigung kommt an, Bankdaten stimmen, Vorgang erscheint im Adminbereich
- [ ] Im Adminbereich „Zahlung erhalten“ → Zahlungsbestätigung kommt an. Danach den Testvorgang stornieren.
- [ ] Weihnachtsaktion mit eigener E-Mail: Spiel-Link kommt per Mail
- [ ] `https://messermanufaktur-ulbrich.de/robots.txt` zeigt `Allow: /`, `/sitemap.xml` öffnet sich
- [ ] 3–4 **alte Adressen** aufrufen und prüfen, dass sie auf die neuen weiterleiten
- [ ] Eine Seite auf `noindex` prüfen: Im Quelltext darf **kein** `noindex` stehen

---

## Phase 3 – Direkt nach dem Start (erste Woche)

- [ ] **Google Search Console**: Domain bestätigen, `sitemap.xml` einreichen, Indexierung der Startseite anfordern
- [ ] **Bing Webmaster Tools**: aus der Search Console importieren
- [ ] **Google-Unternehmensprofil**: Website-Link, Öffnungszeiten und Schreibweisen an die Website angleichen
- [ ] Im Adminbereich unter Bewertungen den **Bewertungslink** eintragen bzw. „Google verbinden“
- [ ] **Newsletter**: Automatik einschalten (`NEWSLETTER_AUTO=true`), sobald die Datenschutzerklärung steht
- [ ] Erste **echte Produkte** mit Fotos im Adminbereich anlegen
- [ ] Nach 1–2 Wochen: In der Search Console nach 404-Fehlern schauen und fehlende Weiterleitungen an Claude geben

## Phase 4 – Kann später kommen

- [ ] **Stripe** (Online-Zahlung). Start ist auch nur mit Überweisung möglich.
- [ ] **Google Merchant Center** (Produkte in Google Shopping), braucht die Rückgabebedingungen
- [ ] **Google-Bewertungen automatisch** (Places-API-Schlüssel), **DHL-Zustellung automatisch**
- [ ] Neue Fotos, Startseite am Handy kürzen, weitere Ratgeberartikel
- [ ] Test-Subdomain nach dem Start löschen oder passwortgeschützt als Testumgebung behalten

---

## Zeitkritisch

Die **Weihnachtsaktion** läuft bis zum 24.12.2026. Jede Woche später live heißt eine Woche weniger Aktion und weniger Newsletter-Anmeldungen. Der Engpass sind fast immer die **Rechtstexte**, deshalb diese zuerst beauftragen.
