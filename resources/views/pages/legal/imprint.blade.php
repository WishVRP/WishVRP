@extends('pages.legal.layout')
@section('legal')
    @php $a = $c['address']; @endphp
    <h2>Angaben gemäß § 5 DDG</h2>
    <p>{{ $c['name'] }}<br>Inhaber: {{ $c['owner'] }}<br>{{ $a['street'] }}<br>{{ $a['postal_code'] }} {{ $a['locality'] }}</p>

    <h2>Kontakt</h2>
    <p>Telefon: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a><br>
       E-Mail: @if($c['email'])<a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>@else<x-ui.missing ask="info@ oder gmx festlegen">E-Mail-Adresse</x-ui.missing>@endif</p>

    <h2>Berufsrechtliche Angaben</h2>
    <p>Berufsbezeichnung: {{ $c['owner_role'] }}<br>
       Zuständige Kammer: {{ $c['chamber'] }}<br>
       <x-ui.missing>Staat der Verleihung der Berufsbezeichnung, Eintragung Handwerksrolle</x-ui.missing></p>

    <h2>Umsatzsteuer</h2>
    <p>@switch($c['tax_mode'])
        @case('kleinunternehmer') Gemäß § 19 UStG wird keine Umsatzsteuer berechnet. @break
        @case('regular') <x-ui.missing>Umsatzsteuer-Identifikationsnummer</x-ui.missing> @break
        @default <x-ui.missing ask="mit Steuerberater klären">Steuerstatus (§ 19 UStG oder USt-IdNr.)</x-ui.missing>
    @endswitch</p>

    <h2>Verbraucherstreitbeilegung</h2>
    <p><x-ui.missing ask="Teilnahme ja/nein festlegen">Angabe zur Teilnahme an Streitbeilegungsverfahren (§ 36 VSBG)</x-ui.missing></p>

    <p class="tech muted">Stand: {{ now()->format('m/Y') }} · vor Veröffentlichung rechtlich prüfen lassen.</p>
@endsection
