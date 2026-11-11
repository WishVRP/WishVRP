@extends('pages.legal.layout')
@section('legal')
    @php $a = $c['address']; @endphp
    <p class="lead">Diese Erklärung beschreibt, welche Daten beim Besuch dieser Website verarbeitet werden.</p>
    <x-page.note title="Entwurf">Die technischen Angaben unten beschreiben den tatsächlichen Stand der neuen Website. Der vollständige Text wird vor dem Start rechtlich geprüft und ergänzt.</x-page.note>

    <h2>Verantwortlicher</h2>
    <p>{{ $c['name'] }}, {{ $c['owner'] }}<br>{{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }}<br>
       Telefon: {{ $c['phone']['display'] }} · E-Mail: @if($c['email']){{ $c['email'] }}@else<x-ui.missing>E-Mail-Adresse</x-ui.missing>@endif</p>

    <h2>Hosting und Server-Logdateien</h2>
    <p>Beim Aufruf der Seiten verarbeitet der Webserver technisch notwendige Daten (z. B. IP-Adresse, Zeitpunkt, aufgerufene Seite, Browser) in Logdateien.</p>
    <p><x-ui.missing>Hosting-Anbieter, Serverstandort, Speicherdauer der Logdateien, AV-Vertrag</x-ui.missing></p>

    <h2>Cookies</h2>
    <p>Die Website setzt nur technisch notwendige Cookies: ein Sitzungs-Cookie und ein Sicherheits-Cookie zum Schutz von Formularen. Es gibt keine Werbe- oder Analyse-Cookies.</p>

    <h2>Keine Drittanbieter</h2>
    <p>Schriften, Bilder und Skripte werden vom eigenen Server geladen. Es werden keine externen Analyse-, Werbe- oder Social-Media-Dienste eingebunden. Der Link „Route planen“ öffnet Google Maps erst nach deinem Klick.</p>

    <h2>Anfragen per Telefon oder E-Mail</h2>
    <p>Wenn du uns kontaktierst, verarbeiten wir deine Angaben, um deine Anfrage zu beantworten.</p>

    <h2>Besucherstatistik ohne Cookies</h2>
    <p>Um zu sehen, welche Seiten gelesen werden, zählen wir Seitenaufrufe auf unserem eigenen Server. Gespeichert werden nur die aufgerufene Seite, das Datum, die Art der Herkunft (zum Beispiel „Suchmaschine“ oder „direkt“) mit dem Namen der verweisenden Website sowie die Geräteart (Handy, Tablet, Computer). Außerdem zählen wir Klicks auf Telefon-, E-Mail- und Buchungslinks. IP-Adressen, Browserkennungen und Cookies werden dafür nicht gespeichert; ein Rückschluss auf einzelne Personen ist nicht möglich. Hat dein Browser „Do Not Track“ oder „Global Privacy Control“ aktiviert, zählen wir nichts. Die Zähldaten werden nach 13 Monaten gelöscht.</p>
    <p><x-ui.missing>Rechtsgrundlage und Formulierung anwaltlich prüfen</x-ui.missing></p>

    <h2>Buchungs-, Bestell- und Anfrageformulare</h2>
    <p>Wenn du über die Website einen Kurs buchst, einen Gutschein oder ein Messer bestellst oder eine Anfrage sendest, speichern wir deine Angaben (Name, E-Mail-Adresse, optional Telefon, Lieferadresse und Nachricht) sowie die gewählte Leistung, um deine Buchung oder Anfrage zu bearbeiten. Du bekommst eine Eingangsbestätigung per E-Mail. Eine Online-Zahlung findet nicht statt; bezahlt wird per Überweisung oder bei Abholung vor Ort.</p>
    <p><x-ui.missing>Rechtsgrundlage, Aufbewahrungsfristen (handels- und steuerrechtlich), E-Mail-Versanddienst – anwaltlich ergänzen</x-ui.missing></p>

    <h2>Weihnachtsaktion</h2>
    @if (\App\Models\XmasEntry::usesSms())
        <p>Für die Teilnahme an der Weihnachtsaktion speichern wir deine E-Mail-Adresse, deine Handynummer, den Zeitpunkt, die Trefferzahl und gegebenenfalls deinen Rabattcode. Deine Handynummer bestätigst du mit einem Code, den wir dir per SMS schicken; dafür geben wir die Nummer und den Text der SMS an unseren SMS-Dienstleister weiter. Den Rabattcode schicken wir dir per E-Mail.</p>
    @else
        <p>Für die Teilnahme an der Weihnachtsaktion speichern wir deine E-Mail-Adresse, den Zeitpunkt, die Trefferzahl und gegebenenfalls deinen Rabattcode. Wir schicken dir einen persönlichen Spiel-Link und deinen Rabattcode per E-Mail.</p>
    @endif
    <p> Damit jede Person nur einmal teilnimmt, begrenzen wir die Anmeldungen je Internetanschluss: Dafür wird aus deiner IP-Adresse ein nicht zurückrechenbarer Prüfwert gebildet und höchstens 24 Stunden im Zwischenspeicher gehalten, nicht in der Datenbank. Beim Einlösen eines Codes vergleichen wir Telefonnummer bzw. Name und Postleitzahl mit früheren Einlösungen, damit pro Person bzw. Haushalt nur ein Weihnachtsrabatt gewährt wird. Die Daten werden nach Ende der Aktion und Ablauf der Einlösefrist gelöscht, soweit keine Aufbewahrungspflicht besteht.</p>
    <p><x-ui.missing>Rechtsgrundlage, Löschfrist und SMS-Dienstleister (Name, Sitz, Auftragsverarbeitungsvertrag) der Weihnachtsaktion anwaltlich prüfen</x-ui.missing></p>

    <h2>Newsletter</h2>
    <p>Wenn du dem Newsletter freiwillig zustimmst, schicken wir dir an deine E-Mail-Adresse Neuigkeiten, Kurstermine, Angebote und Aktionen der Messermanufaktur Ulbrich. Die Anmeldung wird erst wirksam, wenn du sie über den Link in unserer Bestätigungs-E-Mail bestätigst (Double-Opt-in). Zum Nachweis speichern wir Zeitpunkt, Quelle und Wortlaut deiner Einwilligung sowie den Zeitpunkt der Bestätigung. Du kannst die Einwilligung jederzeit über den Abmeldelink in jeder E-Mail oder formlos per E-Mail widerrufen; danach bekommst du keine Newsletter mehr. Die Teilnahme an Aktionen hängt nicht von der Newsletter-Anmeldung ab.</p>
    <p><x-ui.missing>Rechtsgrundlage, Versanddienst (falls genutzt) und Speicherdauer des Newsletters anwaltlich prüfen</x-ui.missing></p>

    <h2>Deine Rechte</h2>
    <p>Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch (Art. 15–21 DSGVO) sowie das Recht, dich bei einer Datenschutz-Aufsichtsbehörde zu beschweren.</p>

    <p class="tech muted">Stand: {{ now()->format('m/Y') }} · Entwurf, vor Veröffentlichung rechtlich prüfen lassen.</p>
@endsection
