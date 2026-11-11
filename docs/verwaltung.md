# Verwaltung (Admin-Bereich) – Anleitung

Stand 11.11.2026. Der Bereich läuft unter einem eigenen, nicht verlinkten Pfad (`ADMIN_PATH`, Standard `/verwaltung/`). Die Anmeldung braucht Passwort **und** einen Code aus einer Authenticator-App (Pflicht).

## Konten anlegen (Andreas und Jeremy)
```
php artisan make:filament-user
```
Name, E-Mail und Passwort eingeben. Beim ersten Login führt die Verwaltung durch die Einrichtung der Authenticator-App (z. B. Google Authenticator, Microsoft Authenticator, 1Password) und zeigt Wiederherstellungscodes. Diese sicher aufbewahren. Konten mit Standardpasswort gibt es bewusst nicht.

## Was es gibt

| Bereich | Inhalt |
|---|---|
| **Dashboard** | Zu erledigen (offene Vorgänge), Notizen fürs Team, nächste Kurstermine mit Belegung; Besuche heute, 7 und 30 Tage; Seitenaufrufe; neue Vorgänge; bezahlte Summe (30 Tage); Klicks auf Telefon, E-Mail, „Kurs buchen“ und Gutschein; Besucherkurve 30 Tage; beliebteste Seiten, Herkunft (Suchmaschine, Instagram, Facebook, KI-Assistenten, direkt), Geräte, verweisende Websites |
| **Bestellungen & Buchungen** | Alle Kursbuchungen, Gutschein-, Messer- und Serviceanfragen von der Website. Filter nach Art und Status, Suche. Status per Klick: *Zahlungsdaten gesendet → Zahlung erhalten → Bestätigt → Versendet/übergeben → Abgeschlossen* oder *Storniert*. Telefonische Buchungen lassen sich selbst anlegen. Zu jedem Vorgang gibt es interne Notizen und eine zuständige Person. |
| **Notizen** | Pinnwand für alle Konten, z. B. „Herr M. hat wegen Rückerstattung angerufen, meldet sich morgen“. Anheften, einem Vorgang zuordnen, als erledigt markieren. Mit Autor und Zeitpunkt. |
| **Kurstermine** | Anlegen, **freischalten/verbergen** (auch mehrere auf einmal), kopieren (z. B. gleicher Kurs, neues Datum), Belegung mit Ampel. Freigeschaltete Termine erscheinen sofort auf Startseite, Kursseite und Buchungsseite und als Veranstaltung in den Google-Strukturdaten. |
| **Kurse** | Name, Preis ab, Dauer, Gesamtlänge, Beschreibung, Reihenfolge, sichtbar ja/nein |

## Abläufe
- **Kursbuchung über die Website:** Die Person wählt Kurs, Teilnehmerzahl und Termin (oder einen Wunschtermin) und sendet ihre Daten. Es entsteht ein Vorgang „Neu“, die Manufaktur bekommt eine E-Mail, die Kundschaft eine Eingangsbestätigung. Plätze werden **erst bei „Bestätigt“** vom Termin abgezogen und bei „Storniert“ wieder freigegeben. So blockieren unbezahlte Anfragen keine Plätze.
- **Gutschein, Edition 100, Sonderanfertigung, Unikate, Shop-Kategorien, Ringe, Schärfen, Reparatur, Gravur, Firmengeschenke, Teamevent:** jeweils ein Anfrageformular auf der Seite. Daraus wird ein Vorgang mit passender Art. Bei Gutschein und Edition 100 steht der Richtbetrag gleich dabei.
- **Keine Online-Zahlung.** Bezahlt wird per Überweisung oder vor Ort (bar/Karte). Eine Zahlungsschnittstelle kommt nur nach ausdrücklicher Freigabe.

## Statistik und Datenschutz
- Die Zählung läuft auf dem eigenen Server (`App\Http\Middleware\TrackPageView`, Klicks über `/e/`). Gespeichert werden Seite, Datum, Herkunftsart mit verweisender Domain und Geräteart. **Keine IP-Adressen, keine Browserkennung, keine Cookies.** Do Not Track und Global Privacy Control werden respektiert, Bots ignoriert.
- „Besuche“ = Aufrufe, die von außen kommen (Einstiege); „Seitenaufrufe“ = alle Aufrufe. Ohne Cookies sind das keine eindeutigen Personen.
- Löschung nach 13 Monaten (`php artisan model:prune`, täglich per Scheduler; auf dem Server Cronjob `* * * * * php artisan schedule:run` einrichten).
- Datenschutzerklärung: Abschnitte „Besucherstatistik“ und „Formulare“ sind ergänzt und für die anwaltliche Prüfung markiert.
- Abschalten: `ANALYTICS_ENABLED=false`.

## Vor dem Go-live
- E-Mail-Versand einrichten (`MAIL_MAILER`, SMTP-Zugang, Absender `MAIL_FROM_ADDRESS` = info@…). Lokal landen Mails nur im Log.
- Konten anlegen (siehe oben), Authenticator einrichten.
- Cronjob für den Scheduler.
- Zahlungsdaten (IBAN) für die Bestätigungsmail festlegen. Bisher steht dort „Die Zahlungsdaten bekommst du von uns“.
- Prüfen, ob der Ablauf „Anfrage → Zahlungsdaten → Zahlung → Bestätigung“ so passt.

## Edition 100 (Raster, Wunschnummer, Käufernamen)
- **Farben im Raster** (Startseite und `/klappmesser/`): **Rot** = verkauft · **Grün** = verkauft und Name freigegeben (antippen zeigt „Nr. 20 · gehört Erika M.“) · **schraffiert** = reserviert · **dunkel** = frei · **Gold** = Sonderstücke 91–100 (nach Absprache).
- **19 verkaufte Stücke** sind vorläufig als **Nr. 1–19** eingetragen. Welche Nummern es wirklich sind, ist noch offen. Bitte im Menü **Edition 100** korrigieren und, falls bekannt, Namen nur mit Zustimmung eintragen.
- **Wunschnummer:** Auf `/klappmesser/` ein freies Feld antippen oder im Bestellformular wählen. Auf der Startseite führt das Antippen zur Bestellung mit vorausgewählter Nummer. Die Nummer wird sofort **reserviert**, eine zweite Bestellung derselben Nummer ist nicht möglich.
- **Name auf der Website:** Häkchen „Mein Name soll auf der Website bei meiner Nummer erscheinen“ plus Anzeigename. Ohne Häkchen speichern wir keinen Namen.
- **Automatik:** Vorgang auf *Zahlung erhalten* oder *Bestätigt* → Nummer **verkauft**, mit Zustimmung **grün mit Namen**. *Storniert* → Nummer wieder **frei**.

## Weihnachtsaktion „Schneeballschlacht“
- Seite `/weihnachtsaktion/`, Banner auf der Startseite und auf `/geschenke/weihnachten/`. Spielbar bis 24.12.2026 (`ulbrich.xmas.ends_on`), **Codes einlösbar bis 31.12.2026** (`valid_until`).
- **Anspruchsvoll:** langsame Schneebälle (vorhalten), wechselnder Wind mit Anzeige, Wolken fangen Bälle ab und verstecken den Weihnachtsmann. Er weicht aus, wechselt unvorhersehbar Richtung und Höhe und wird schneller. Nachladezeit 0,45 s, höchstens 2 Bälle gleichzeitig. **Keine Übungsrunde:** Es gibt genau einen Versuch.
- **Kalibrierung:** Automatische Testspieler mit pixelgenauem Zielen schafften 5–6 Treffer pro Runde.
- **Ablauf:** E-Mail eingeben und Teilnahmebedingungen bestätigen → 30 Sekunden spielen → Treffer werden gezählt → Rabatt und persönlicher Code erscheinen und kommen per E-Mail.
- **Rabattstufen (`config/ulbrich.php → xmas.tiers`):** 0–3 Treffer = kein Rabatt, 4–9 Treffer = **5 %**, ab 10 Treffern = **10 %**. **Höchstens 10 %** ist fest eingebaut (`max_percent`).
- **Nur einmal pro Person – kostenlose Lösung (Standard, `XMAS_VERIFICATION=email`):**
  1. **Spiel nur über den persönlichen Link per E-Mail.** Der Link funktioniert genau einmal; eine Teilnahme je E-Mail-Adresse (Schreibvarianten wie `anna+1@…` zählen mit). Wegwerf-Adressen sind gesperrt. Mit „Jetzt spielen“ zählt die Teilnahme, auch bei Abbruch.
  2. **Höchstens 3 Anmeldungen pro Internetanschluss und Tag** (IP nur als Hash, 24 h im Cache).
  3. **Einlösen nur einmal pro Person bzw. Haushalt:** Der Code gilt nur mit derselben E-Mail-Adresse, die Telefonnummer ist Pflicht. Abgelehnt wird, wenn schon ein Weihnachtsrabatt eingelöst wurde mit **gleicher Telefonnummer**, **gleichem Namen + PLZ** oder **gleicher Straße + PLZ** („Hauptstr. 5“ = „Hauptstraße 5“).
  4. **Warnhinweis in der Verwaltung:** Einlösungen mit gleichem Namen, gleicher Straße oder gleicher Telefonnummer wie eine frühere Rabatt-Bestellung bekommen „⚠ prüfen“ in der Liste und einen Hinweis oben im Vorgang. Da jede Bestellung erst nach Zahlungseingang von euch bestätigt wird, entscheidet ihr vor dem Versand.
  - **Wirkung:** Mehrfach **spielen** mit mehreren E-Mail-Adressen ist möglich, mehrfach **sparen** praktisch nicht. Rabatt höchstens 10 %.
- **Optional strenger: SMS-Code (`XMAS_VERIFICATION=sms`):** Dann braucht jede Teilnahme eine per SMS-Code bestätigte Handynummer (eine Nummer = eine Teilnahme), und der Code ist nur mit dieser Nummer einlösbar. Dafür ist ein SMS-Anbieter nötig (eingebaut: seven.io, wenige Cent pro SMS; `SMS_DRIVER=seven`, `SEVEN_API_KEY` nur in der `.env` des Servers). Ohne Anbieter ist die SMS-Anmeldung in der Produktion gesperrt.
- **Testen in der Entwicklung:** Es werden keine E-Mails oder SMS verschickt; der Spiel-Link bzw. SMS-Code erscheint als „Testmodus“ direkt auf der Seite. IP-Limit zurücksetzen: `php artisan cache:clear`.
- **Gegen Schummeln:** Die Wertung passiert auf dem Server. Wer vor Ablauf der 30 Sekunden ein Ergebnis schickt, bekommt 0 Treffer; mehr als 60 Treffer werden auf 60 begrenzt. Der Rabatt übersteigt nie 10 %.
- **Code einlösen:** Feld „Rabattcode“ in allen Bestell- und Buchungsformularen (solange die Codes gelten, bis 31.12.2026, `valid_until`). Jeder Code ist **einmalig**: Danach gilt er als eingelöst und ist dem Vorgang zugeordnet; der Betrag im Vorgang ist schon reduziert.
- **Verwaltung → Weihnachtsaktion:** Teilnahmen, Treffer, Rabatt, Code, eingelöst ja/nein, Vorgang. Einlösung lässt sich manuell setzen oder zurücknehmen, etwa bei telefonischen Bestellungen. Auf dem Dashboard steht die Zahl der Teilnahmen und Codes.
- **Teilnahmebedingungen** stehen auf der Aktionsseite. Gültigkeitszeitraum und Bedingungen bestätigen und anwaltlich prüfen lassen (intern markiert). Die Datenschutzerklärung hat einen Abschnitt „Weihnachtsaktion“.

## Newsletter (Double-Opt-in)
- **Woher kommen die Adressen?** Beim Weihnachtsspiel gibt es ein **freiwilliges, nicht vorausgewähltes** Häkchen „Ja, ich möchte den Newsletter … erhalten (freiwillig, jederzeit abbestellbar)“. Das Spiel funktioniert auch ohne. Teilnahme-Adressen ohne Häkchen dürfen **nicht** für Werbung genutzt werden (§ 7 UWG).
- **Double-Opt-in:** Nach dem Häkchen kommt eine Bestätigungs-Mail. Erst nach dem Klick auf „Ja, Newsletter bestätigen“ ist die Adresse aktiv. Das bloße Öffnen des Links reicht nicht, damit E-Mail-Scanner nicht versehentlich bestätigen.
- **Nachweis:** Gespeichert werden Zeitpunkt der Einwilligung, Quelle (z. B. `weihnachtsaktion`), Wortlaut und Zeitpunkt der Bestätigung.
- **Abmelden:** Jeder Empfänger hat einen persönlichen Abmeldelink (`/newsletter/abmelden/?t=…`). Er muss in **jeder** Werbe-E-Mail stehen. Widerruf per Telefon oder Mail: in der Verwaltung auf „Abmelden“ klicken.
- **Verwaltung → Newsletter:** Liste mit Status (bestätigt / wartet / abgemeldet), Filter und **Export „Bestätigte exportieren (CSV)“** mit E-Mail, Bestätigungsdatum, Quelle und Abmeldelink, z. B. zum Import in ein Newsletter-Programm. Eintragen von Hand ist bewusst nicht möglich, weil die Einwilligung von der Person selbst kommen muss.
- **Bestandskunden:** Wer bei euch gekauft hat, dürfte unter engen Voraussetzungen auch ohne Häkchen Werbung für ähnliche Produkte bekommen (§ 7 Abs. 3 UWG: Hinweis bei der Bestellung, Widerspruchsmöglichkeit in jeder Mail). Das ist noch nicht eingebaut; bei Bedarf mit dem Anwalt klären.

## Newsletter-Automatik
- **Wöchentlich automatisch:** Jeden **Dienstag um 18:30 Uhr** (`config/ulbrich.php → newsletter`) entsteht eine Ausgabe und geht an alle **bestätigten** Empfänger. Den Inhalt bilden nur **echte Daten**: nächste Kurstermine mit freien Plätzen, freie Nummern der Edition 100, ein wechselnder „Tipp der Woche“ aus den Ratgebern und ein Gruß von Andreas. Höchstens eine Auto-Ausgabe pro Woche; ohne bestätigte Empfänger wird nichts angelegt.
- **Alle 3 Monate mit Rabattcode:** Ist seit 90 Tagen kein Code verschickt worden, bekommt die nächste Ausgabe für **jeden Empfänger einen eigenen Code**: 10 % (höchstens 10 %, fest eingebaut), 30 Tage gültig, einmal einlösbar, nur mit der Empfänger-E-Mail. Eingelöst wird im Feld „Rabattcode“ jedes Bestell- und Buchungsformulars; der Betrag im Vorgang ist dann schon reduziert.
- **Eigene Ausgaben:** Verwaltung → **Newsletter-Ausgaben** → „Eigene Ausgabe schreiben“: Betreff, Vorschauzeile, Text (fett, Links, Listen), optional Rabattcode 5 % oder 10 %, Versandzeitpunkt. Vorher „Vorschau“ und „Testmail an mich“, dann „Planen“ oder „Jetzt an alle senden“. Leerer Text = automatischer Inhalt.
- **Alles im Blick:** je Ausgabe Status, Zeitpunkt, Empfänger, Fehler, Code, Anzahl Einlösungen; **Versandprotokoll** (wer wann welche Ausgabe mit welchem Code bekam) und **Rabattcodes dieser Ausgabe** (eingelöst ja/nein, bei welchem Vorgang). Menü **Rabattcodes** listet alle Newsletter-Codes, bei Empfängern steht, wie viele Newsletter sie bekommen haben. Auf dem Dashboard: Ausgaben der letzten 30 Tage und nächster geplanter Versand. Versendete Ausgaben bleiben zur Dokumentation unverändert.
- **Rechtssicher eingebaut:** Versand nur an bestätigte Adressen; in jeder Mail Grund des Empfangs, **Abmeldelink**, Name, Anschrift, Telefon, E-Mail, Impressum- und Datenschutzlink. Dazu **List-Unsubscribe** mit One-Click-Abmeldung (der „Abmelden“-Knopf in Gmail, Apple Mail usw.). **Kein Öffnungs- oder Klick-Tracking.**
- **Auf dem Server einrichten (Pflicht, sonst läuft nichts automatisch):**
  - Cronjob: `* * * * * cd /pfad/zur/website && php artisan schedule:run >> /dev/null 2>&1`
  - E-Mail-Versand: SMTP-Zugang in der `.env` (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS=info@…`, `MAIL_FROM_NAME="Messermanufaktur Ulbrich"`). Bei vielen Empfängern das Versandlimit des Mail-Anbieters beachten, sonst einen Versanddienst nutzen (Auftragsverarbeitung!).
  - Ausschalten: `NEWSLETTER_AUTO=false` (eigene Ausgaben gehen weiterhin).
  - Von Hand: `php artisan newsletter:weekly` (Ausgabe anlegen), `php artisan newsletter:send-due` (fällige senden).

## Buchungskalender
- **Jeder Tag ist buchbar**, frühestens übermorgen und bis 5 Jahre im Voraus (`config/ulbrich.php → booking`). **Ein Kurs pro Tag:** Sobald ein Tag gebucht ist, ist er belegt. Mehrtägige Kurse (Damast 2 Tage, Premium bis 3) blockieren alle Kurstage. Termine von Hand anlegen ist nicht mehr nötig.
- Online buchbar: Grundlagenkurs, Klingenkurs, Klingenkurs „Alternativ“, Damast-Messerkurs, Damast Premium-Kurs. Der Manager-Schmiedekurs bleibt auf Anfrage (kein fester Preis).
- **Preise:** Der bisherige „ab“-Preis wird als Preis pro Person berechnet. Bitte bestätigen. Höchstzahl Personen vorläufig 6 (`max_persons`).
- **Verwaltung → Kalender & Sperrtage:** einzelne Tage oder einen **Zeitraum sperren** (Urlaub, Markt), freigeben. Oben stehen die nächsten Kurstage; auf dem Dashboard „Nächste Kurstage“. Jede Buchung ist ein Vorgang unter „Bestellungen & Buchungen“ mit Kurstag(en).
- **Ablauf mit Stripe:** Kurs → freier Tag → Personen → Daten → „Zahlungspflichtig buchen“ → Stripe-Zahlung. Der Tag ist 30 Minuten reserviert; mit der Zahlung ist die Buchung **bestätigt** (Mail an Kunde und Manufaktur). Bricht jemand ab oder läuft die Zeit ab, wird der Tag automatisch wieder frei.
- **Ohne Stripe** (so lange keine Schlüssel eingetragen sind): „Buchung anfragen“. Der Tag ist vorgemerkt, die Zahlungsdaten gehen per Mail raus; in der Verwaltung „Zahlung erhalten“ klicken. Storniert = Tag wieder frei.
- Die alte Verwaltung „Kurstermine“ ist ausgeblendet; der Kalender ersetzt sie.

## Gutscheine
- **Kaufen:** Auf `/geschenke/schmiedekurs-gutschein/` Kurs, Personen und optional den Namen der beschenkten Person wählen, dann bezahlen. Nach der Zahlung kommt der **Gutscheincode (GS-…)** automatisch per E-Mail. Bei Überweisung: in der Verwaltung „Zahlung erhalten“ → Code geht raus.
- **Einlösen:** im Buchungskalender im Feld „Gutscheincode“. Deckt der Gutschein alles ab, ist die Buchung sofort bestätigt; sonst wird nur der Rest über Stripe bezahlt. Jeder Code einmal.
- **Gültigkeit:** 3 Jahre zum Jahresende (gesetzliche Verjährung, vom Anwalt bestätigen lassen).
- **Verwaltung → Gutscheine:** Code, Kurs, Wert, Status (Zahlung offen / gültig / eingelöst), Kauf- und Einlöse-Vorgang, offener Gutscheinwert gesamt.

## Shop-Produkte
- **Verwaltung → Produkte → „Produkt anlegen“:** Name (so, wie Kunden suchen), Kategorie, Preis, Bestand (Unikat = 1), Kurzbeschreibung, Beschreibung, bis zu 8 Fotos (erstes = Titelbild, werden auf max. 1600 px verkleinert), technische Daten (nur echte Angaben). Status **„Im Shop“** schaltet das Produkt frei.
- **Produktseite** unter `/shop/{kategorie}/{name}/` mit Galerie, Preis, Verfügbarkeit, Daten, Garantie-Hinweis und **Kaufen** (Versand Deutschland 7,99 € oder Abholung; Ausland auf Anfrage). Während der Online-Zahlung ist das Stück reserviert; nach der Zahlung sinkt der Bestand. Bei 0 heißt es „Verkauft“ (bleibt als Referenz sichtbar, ohne Kaufen-Knopf).
- **Bei Google:**
  - jede Produktseite mit Strukturdaten `Product` (Preis, Verfügbarkeit, Versand, Zustand neu, Garantie, Bilder, Merkmale)
  - in der **Sitemap**
  - im **Produkt-Feed `https://messermanufaktur-ulbrich.de/produkte.xml`**: im **Google Merchant Center** einmal als Feed mit täglichem Abruf hinterlegen, dann erscheinen die Messer kostenlos im Shopping-Tab und in der Google-Suche (Merchant-Center-Konto nötig, dort Versand und Rückgabe angeben)
  - in der Shop-Übersicht und der passenden Kategorie
  - in llms.txt für KI-Assistenten
- **Edition 100:** Mit Stripe wird die Edition direkt bezahlt (Wunschnummer, Versand oder Abholung). Die Nummer ist während der Zahlung reserviert, nach Zahlung verkauft (mit Namen, falls gewünscht), bei Abbruch wieder frei.

## Stripe einrichten (vor Go-live)
1. Konto bei stripe.com anlegen, Firma verifizieren, Zahlungsarten wählen (Karte, Apple/Google Pay, Klarna, SEPA …).
2. API-Schlüssel (geheim) und unter „Webhooks“ einen Endpunkt **`https://messermanufaktur-ulbrich.de/stripe/webhook/`** anlegen mit den Ereignissen `checkout.session.completed`, `checkout.session.expired`, `checkout.session.async_payment_succeeded`, `checkout.session.async_payment_failed`. Den Signing-Secret kopieren.
3. Nur in der `.env` des Servers: `STRIPE_SECRET=sk_live_…` und `STRIPE_WEBHOOK_SECRET=whsec_…`. Zum Testen zuerst die Test-Schlüssel (`sk_test_…`) nutzen.
4. Sobald beide Werte da sind, zeigt die Website „Zahlungspflichtig buchen/bestellen“ und leitet zu Stripe weiter; vorher bleibt alles bei Anfrage und Überweisung.
- **Rechtlich prüfen lassen:** AGB, Widerrufsbelehrung (Kurse mit festem Termin sind meist vom Widerruf ausgenommen, Sonderanfertigungen auch, Fertigware nicht), Steuerhinweis (Kleinunternehmer oder MwSt.), Pflichtangaben im Bestellprozess.

## Bewertungen (Google) – automatisch

Die Website holt Gesamtbewertung, Anzahl und Bewertungstexte **direkt von Google**:

- Kommt eine neue Bewertung dazu, erscheint sie spätestens nach einer Stunde von selbst (`GOOGLE_REVIEWS_REFRESH_MINUTES`, Standard 60).
- Der Abruf läuft auf dem Server. Besucher laden nichts von Google: keine Cookies, kein Google-Skript, keine Autorenfotos.
- **Grenze von Google:** Die Schnittstelle liefert höchstens **5 Bewertungstexte**, die Google selbst auswählt. Gesamtwert und Anzahl stimmen immer.

### Einmalig einrichten (ca. 15 Minuten)

1. In der [Google Cloud Console](https://console.cloud.google.com/) ein Projekt anlegen, z. B. „Ulbrich Website“. Ein Rechnungskonto mit Karte ist nötig.
   - Nach heutigem Preismodell bleibt der stündliche Abruf (ca. 730 Abrufe im Monat) im kostenlosen Kontingent. Zur Sicherheit unter „Budgets & Benachrichtigungen“ ein Budget von z. B. 5 € mit Benachrichtigung anlegen.
2. „APIs & Dienste“ → „Bibliothek“ → **Places API (New)** aktivieren.
3. „APIs & Dienste“ → „Anmeldedaten“ → „API-Schlüssel erstellen“. Danach den Schlüssel einschränken:
   - API-Einschränkung: nur „Places API (New)“
   - Anwendungseinschränkung: IP-Adresse des Servers
4. Den Schlüssel auf dem Server in die `.env` eintragen: `GOOGLE_PLACES_KEY=…`. Niemals in einen Chat oder eine E-Mail schreiben.
5. Verwaltung → **Bewertungen** → **„Google verbinden“**: Unternehmen suchen, Treffer auswählen, fertig.

Danach zeigt die Seite oben „Automatisch mit Google verbunden“ mit dem Zeitpunkt des letzten Abrufs. **„Jetzt von Google laden“** holt sofort neu.

### Von Hand gepflegte Bewertungen

- **„Bewertung hinzufügen“** ergänzt die 5 Google-Texte, z. B. ältere Google-Bewertungen. Immer wörtlich übernehmen. Steht derselbe Name schon in den Google-Texten, wird der Eintrag nicht doppelt gezeigt.
- Ohne Google-Verbindung (oder wenn Google gerade nicht antwortet) zeigt die Seite automatisch diese Einträge. Die Gesamtbewertung lässt sich dann über **„Gesamtbewertung von Hand“** setzen.

### Wo sichtbar

- Startseite: ganz oben die Gesamtbewertung, im Abschnitt „Bewertungen“ die ersten 4 Texte
- `/schmiedekurse/erfahrungen/`: alle Texte
- Namen und „Google“ sind mit der Bewertung bei Google verlinkt. Das verlangt Google als Quellenangabe.

Bewusst ohne Bewertungs-Sterne im Google-Code (Schema): Google zeigt für selbst eingebundene Bewertungen eines Unternehmens keine Sterne in den Suchergebnissen an. Die Sterne in der Google-Suche kommen direkt aus dem Google-Unternehmensprofil. Mehr Bewertungen dort sind der wirksamste Hebel: nach jedem Kurs um eine Bewertung bitten.

## Nach dem Kauf: Status-E-Mails an Kunden

Alles läuft über **Bestellungen & Buchungen → Vorgang → „Status ändern“**. Jede E-Mail wird als Notiz am Vorgang festgehalten (automatische Mails: Verfasser „System“). Über **„E-Mail-Vorschau“** siehst du jede Mail vorher, ohne dass etwas verschickt wird.

| Schritt | Was du tust | Was der Kunde bekommt |
|---|---|---|
| **In Arbeit** | Fertigungszeit eintragen (z. B. „ca. 3–4 Wochen“), optional eine persönliche Nachricht | „Wir haben mit deiner Bestellung begonnen“ mit Fertigungszeit und Fortschrittsanzeige |
| **Versendet / abholbereit** | Versanddienst (DHL, DPD, Hermes, GLS, UPS, Post, andere) und Sendungsnummer eintragen. Der Tracking-Link wird automatisch gebaut. Bei Abholung „Abholung“ wählen. | „Dein Paket ist unterwegs“ mit Sendungsnummer und Knopf „Sendung verfolgen“, bei Abholung „Abholbereit“ |
| **Zugestellt / abgeholt** | Klick, oder bei DHL automatisch (siehe unten) | „Dein Paket ist angekommen“ mit Pflegetipp und, falls eingewilligt, Bitte um Google-Bewertung mit direktem Link |
| **Kurs** | nichts: geht automatisch am Abend (19 Uhr) des letzten Kurstags raus, nur wenn eingewilligt | „Danke für deinen Kurstag“ mit Bitte um Google-Bewertung. Der Vorgang wird „Abgeschlossen“. |

- Das Häkchen „Kunde per E-Mail informieren“ lässt sich abschalten. Dann ändert sich nur der Status.
- **„Bitte um Google-Bewertung senden“** schickt die Bewertungsbitte von Hand, z. B. bei Telefonbuchungen. Der Knopf erscheint nur mit Einwilligung.

### Einwilligung (wichtig)

Eine Bitte um Bewertung per E-Mail gilt in Deutschland ohne Einwilligung als Werbung (BGH, Urteil vom 10.07.2018 – VI ZR 225/17). Deshalb gilt:

- Bei jeder Buchung und Bestellung gibt es die freiwillige, nicht vorausgewählte Checkbox „Nach dem Kurs bzw. der Lieferung darf ich um eine Bewertung gebeten werden“.
- Ohne Häkchen bekommt der Kunde die Status-Mails, aber keine Bewertungsbitte. Nach dem Kurs geht dann gar keine Mail raus.
- Bei Telefonbuchungen im Vorgang „Einverstanden mit Bitte um Bewertung“ nur ankreuzen, wenn die Person ausdrücklich zugestimmt hat.

### Link „Bewertung schreiben“

Verwaltung → Bewertungen → **„Link ‚Bewertung schreiben‘“**: den Link aus dem Google-Unternehmensprofil eintragen („Bewertungen anfordern“, sieht aus wie `https://g.page/r/…/review`). Ohne eigenen Link wird er automatisch aus der Google-Verbindung gebaut.

### DHL-Zustellung automatisch erkennen (optional)

1. Auf developer.dhl.com kostenlos registrieren, eine App mit der API **„Shipment Tracking – Unified“** anlegen und den API-Key kopieren.
2. In die `.env` auf dem Server eintragen: `DHL_API_KEY=…`

Danach prüft die Seite DHL-Sendungen alle 2 Stunden (8–21 Uhr). Ist ein Paket zugestellt, springt der Status auf „Zugestellt“ und die „Angekommen“-Mail geht raus. Andere Versanddienste setzt du von Hand auf „Zugestellt“.

**Voraussetzung für alle Automatiken:** Der Cron-Job `php artisan schedule:run` läuft jede Minute auf dem Server (siehe Go-live).
