@extends('pdf.layout', ['pdfTitle' => ($doc['cancel'] ? 'Stornorechnung ' : 'Rechnung ').$doc['number'], 'showBank' => true])
@php $eur = fn ($n) => \App\Support\Pdf::eur($n); @endphp
@section('content')
<table width="100%" class="top"><tr>
    <td width="58%">
        <span class="small muted">{{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}</span><br><br>
        {{ $o->name }}<br>
        @if ($o->street){{ $o->street }}<br>@endif
        @if ($o->postal_code || $o->city){{ trim($o->postal_code.' '.$o->city) }}<br>@endif
        @if ($o->country && $o->country !== 'Deutschland'){{ $o->country }}<br>@endif
    </td>
    <td width="42%">
        <table width="100%" class="small">
            <tr><td class="muted">{{ $doc['cancel'] ? 'Stornorechnung' : 'Rechnung' }} Nr.</td><td class="r"><b>{{ $doc['number'] }}</b></td></tr>
            <tr><td class="muted">Datum</td><td class="r">{{ $doc['date']->format('d.m.Y') }}</td></tr>
            @if ($doc['refers_to'])<tr><td class="muted">zu Rechnung</td><td class="r">{{ $doc['refers_to'] }}</td></tr>@endif
            <tr><td class="muted">Vorgang</td><td class="r">{{ $o->number }}</td></tr>
            <tr><td class="muted">Leistungsdatum</td><td class="r">{{ $doc['service_date'] }}</td></tr>
        </table>
    </td>
</tr></table>

<h1>{{ $doc['cancel'] ? 'Stornorechnung' : 'Rechnung' }}</h1>
@if ($doc['cancel'])
    <p>Hiermit stornieren wir die Rechnung {{ $doc['refers_to'] }} vollständig.</p>
@else
    <p>Vielen Dank für dein Vertrauen. Wir berechnen dir:</p>
@endif

<table class="lines">
    <thead><tr><th>Pos.</th><th>Beschreibung</th><th class="r">Menge</th><th class="r">Betrag</th></tr></thead>
    <tbody>
    @foreach ($doc['lines'] as $i => $l)
        <tr><td>{{ $i + 1 }}</td><td>{{ $l['text'] }}</td><td class="r">{{ $l['qty'] }}</td><td class="r">{{ $eur($l['total']) }}</td></tr>
    @endforeach
    </tbody>
</table>

<table width="100%" class="sum"><tr><td width="50%"></td><td width="50%">
    <table width="100%">
        @if ($doc['mode'] === 'regular')
            <tr><td class="muted">Nettobetrag (19 %)</td><td class="r">{{ $eur($doc['net']) }}</td></tr>
            <tr><td class="muted">zzgl. 19 % Umsatzsteuer</td><td class="r">{{ $eur($doc['vat']) }}</td></tr>
        @endif
        <tr class="total"><td>Gesamtbetrag</td><td class="r">{{ $eur($doc['gross']) }}</td></tr>
    </table>
</td></tr></table>

@if ($doc['mode'] === 'kleinunternehmer')
    <p class="small">Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.</p>
@elseif ($doc['mode'] !== 'regular')
    <p class="small muted">Steuerhinweis folgt.</p>
@endif

@if (! $doc['cancel'])
    <p>@if ($doc['paid'])Der Betrag wurde bereits bezahlt{{ $doc['payment'] ? ' ('.$doc['payment'].')' : '' }} – vielen Dank.@else Bitte überweise den Betrag unter Angabe der Vorgangsnummer {{ $o->number }}.@endif</p>
@else
    <p>Bereits gezahlte Beträge erstatten wir auf dem ursprünglichen Zahlungsweg.</p>
@endif

<p class="small muted">Es gelten unsere AGB (messermanufaktur-ulbrich.de/agb/). Bitte bewahre diese Rechnung auf.</p>
@endsection
