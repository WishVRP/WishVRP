@props(['amount', 'from' => false])
{{-- Preis in Euro, deutsches Format. Steuerhinweis separat über <x-ui.tax-note>. --}}
@if ($amount === null)
<span>auf Anfrage</span>
@else
<span>{{ $from ? 'ab ' : '' }}{{ number_format($amount, 0, ',', '.') }} €</span>
@endif
