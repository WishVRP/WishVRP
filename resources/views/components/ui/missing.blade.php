@props(['ask' => 'bei Andreas erfragen'])
{{-- Sichtbarer Marker für fehlende echte Daten. Go-live erst, wenn keiner mehr gerendert wird (php artisan ulbrich:audit). --}}
<span class="missing" data-missing>[FEHLT: {{ $slot }}@if($ask) – {{ $ask }}@endif]</span>
