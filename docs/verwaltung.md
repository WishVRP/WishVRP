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
- Seite `/weihnachtsaktion/`, Banner auf der Startseite und auf `/geschenke/weihnachten/`. Spielbar bis 24.12.2026 (`ulbrich.xmas.ends_on`).
- **Ablauf:** E-Mail eingeben und Teilnahmebedingungen bestätigen → 30 Sekunden spielen → Treffer werden gezählt → Rabatt und persönlicher Code erscheinen und kommen per E-Mail.
- **Rabattstufen (Vorschlag, in `config/ulbrich.php → xmas.tiers` änderbar):** 1–11 Treffer = **5 %**, ab 12 Treffern = **10 %**, 0 Treffer = kein Rabatt. **Höchstens 10 %** ist fest eingebaut (`max_percent`).
- **Nur einmal pro Person:** Die Teilnahme hängt an der E-Mail-Adresse; Schreibvarianten wie `Anna.Test+x@googlemail.com` = `annatest@gmail.com` zählen als dieselbe. Mit dem Start zählt die Teilnahme, auch bei Abbruch oder Neuladen. Wer erneut startet, sieht nur sein erstes Ergebnis.
- **Gegen Schummeln:** Die Wertung passiert auf dem Server. Wer vor Ablauf der 30 Sekunden ein Ergebnis schickt, bekommt 0 Treffer; mehr als 80 Treffer werden auf 80 begrenzt. Der Rabatt übersteigt nie 10 %.
- **Code einlösen:** Feld „Rabattcode“ in allen Bestell- und Buchungsformularen (solange die Codes gelten, bis 24.12.2026, `valid_until`). Jeder Code ist **einmalig**: Danach gilt er als eingelöst und ist dem Vorgang zugeordnet; der Betrag im Vorgang ist schon reduziert.
- **Verwaltung → Weihnachtsaktion:** Teilnahmen, Treffer, Rabatt, Code, eingelöst ja/nein, Vorgang. Einlösung lässt sich manuell setzen oder zurücknehmen, etwa bei telefonischen Bestellungen. Auf dem Dashboard steht die Zahl der Teilnahmen und Codes.
- **Teilnahmebedingungen** stehen auf der Aktionsseite. Gültigkeitszeitraum und Bedingungen bestätigen und anwaltlich prüfen lassen (intern markiert). Die Datenschutzerklärung hat einen Abschnitt „Weihnachtsaktion“.
