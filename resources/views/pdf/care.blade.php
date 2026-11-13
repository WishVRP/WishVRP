@extends('pdf.layout', ['pdfTitle' => 'Pflegeanleitung – '.$c['name']])
@section('content')
<h1>Pflegeanleitung für dein Messer</h1>
<p>Ein handgefertigtes Messer begleitet dich viele Jahre – wenn du ein paar einfache Regeln beachtest.</p>
<p><b>Die vier Grundregeln:</b> von Hand spülen und sofort abtrocknen · auf Holz oder Kunststoff schneiden · regelmäßig abziehen, bei Bedarf auf dem Stein schärfen · so lagern, dass die Schneide nichts berührt.</p>

<h2>1. Richtig reinigen</h2>
<ol>
    <li>Direkt nach dem Gebrauch mit warmem Wasser und etwas Spülmittel abspülen.</li>
    <li>Mit einem Tuch vom Rücken zur Schneide hin abwischen – nie mit der Hand an der Schneide entlang.</li>
    <li><b>Sofort abtrocknen.</b> Besonders bei nicht rostfreiem Stahl und Damast.</li>
</ol>
<p><b>Keine Spülmaschine:</b> Hitze, aggressive Reiniger und das Anschlagen an anderem Geschirr stumpfen die Schneide ab und können Holzgriffe aufquellen oder reißen lassen.</p>

<h2>2. Das passende Schneidbrett</h2>
<table class="lines">
    <thead><tr><th>Unterlage</th><th>Für die Schneide</th></tr></thead>
    <tbody>
        <tr><td>Holz (Stirnholz, Längsholz)</td><td>schonend – gut geeignet</td></tr>
        <tr><td>Kunststoff</td><td>schonend – gut geeignet</td></tr>
        <tr><td>Glas, Stein, Keramik</td><td>sehr hart – stumpft ab, Ausbrüche möglich</td></tr>
        <tr><td>Teller, Metall</td><td>ungeeignet</td></tr>
    </tbody>
</table>

<h2>3. Abziehen und schärfen</h2>
<p><b>Abziehen – zwischendurch:</b> Beim Schneiden legt sich die feine Schneide mit der Zeit leicht um. Mit einem Wetzstab oder einem feinen Stein lässt sie sich wieder aufrichten. Das erhält die Schärfe, ersetzt aber kein Schleifen.</p>
<p><b>Schärfen – wenn das Messer nicht mehr greift:</b> Rutscht die Klinge über eine Tomatenhaut, statt sofort zu greifen, ist Nachschärfen fällig. Am schonendsten geht das auf einem Schleifstein: zuerst eine gröbere Körnung, dann eine feine – mit gleichmäßigem Winkel und wenig Druck.</p>

<h2>4. Richtig lagern</h2>
<ul>
    <li><b>Messerblock</b> – der Klassiker für die Küche.</li>
    <li><b>Magnetleiste</b> – Klinge vorsichtig mit dem Rücken zuerst ansetzen.</li>
    <li><b>Scheide oder Klingenschutz</b> – für den Transport und die Schublade.</li>
</ul>
<p>Lose in der Schublade schlagen Klingen aneinander und werden schnell stumpf – und das Hineingreifen ist gefährlich.</p>

<h2>5. Griff und Klappmesser</h2>
<p>Holzgriffe freuen sich über gelegentliches Einölen mit einem lebensmittelechten Öl. Bei Klappmessern gehört Schmutz nicht ins Gelenk: nach dem Gebrauch abwischen, trocknen und die Achse bei Bedarf mit einem Tropfen Öl gangbar halten.</p>

<h2>{{ $c['warranty']['name'] }}</h2>
<p>{{ $c['warranty']['text'] }} {{ $c['warranty']['statutory_note'] }}</p>
<p><b>Nachschärfen lassen:</b> Formular auf messermanufaktur-ulbrich.de/nachschaerfen/ ausfüllen – du bekommst eine Vorgangsnummer und die Einsendeadresse. Alle Bedingungen: messermanufaktur-ulbrich.de/garantie/</p>

<h2>Fragen?</h2>
<p>Bei einem wertvollen Einzelstück oder einer Damastklinge frag lieber beim Messerschmied nach, statt zu experimentieren: Tel. {{ $c['phone']['display'] }} · {{ $c['email'] }}</p>
<p class="small muted">Stand {{ now()->format('m/Y') }} · ausführlich online: messermanufaktur-ulbrich.de/ratgeber/messer-pflege/</p>
@endsection
