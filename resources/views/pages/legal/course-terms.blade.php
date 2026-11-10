@extends('pages.legal.layout')
@section('legal')
    <h2>Buchung und Termine</h2>
    <ul>
        @foreach ($c['booking_rules'] as $rule)<li>{{ $rule }}</li>@endforeach
    </ul>
    <p><x-ui.missing>Weitere Kursbedingungen: Stornierung, Ausfall durch Krankheit, Mindestteilnehmer, Haftung/Sicherheit, Mindestalter</x-ui.missing></p>
    <p class="tech muted">Stand: {{ now()->format('m/Y') }} · vor Veröffentlichung rechtlich prüfen lassen.</p>
@endsection
