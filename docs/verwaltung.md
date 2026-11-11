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
