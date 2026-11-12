@extends('pages.legal.layout')
@section('legal')
    {!! \App\Support\LegalText::html($page['legal']) !!}
    @if (! empty($page['legal_extra']))@include($page['legal_extra'])@endif
    <p class="tech muted mt-6">Stand: 12.11.2026 · Arbeitsfassung, vor Veröffentlichung rechtlich prüfen lassen.</p>
@endsection
