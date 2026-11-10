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
    <p>Schriften, Bilder und Skripte werden vom eigenen Server geladen. Es werden keine Analyse-, Werbe- oder Social-Media-Dienste eingebunden. Der Link „Route planen“ öffnet Google Maps erst nach deinem Klick.</p>

    <h2>Anfragen per Telefon oder E-Mail</h2>
    <p>Wenn du uns kontaktierst, verarbeiten wir deine Angaben, um deine Anfrage zu beantworten.</p>

    <h2>Buchungen und Bestellungen</h2>
    <p><x-ui.missing>Abschnitt Buchung/Zahlung: Zahlungsanbieter, Rechtsgrundlage, Aufbewahrungsfristen – nach Einrichtung des Checkouts</x-ui.missing></p>

    <h2>Deine Rechte</h2>
    <p>Du hast das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch (Art. 15–21 DSGVO) sowie das Recht, dich bei einer Datenschutz-Aufsichtsbehörde zu beschweren.</p>

    <p class="tech muted">Stand: {{ now()->format('m/Y') }} · Entwurf, vor Veröffentlichung rechtlich prüfen lassen.</p>
@endsection
