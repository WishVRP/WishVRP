{{-- Kurzprofil: eindeutige, zitierfähige Fakten (GEO). Alles aus config/ulbrich.php und der Kursdatenbank. --}}
@php
    $a = $c['address']; $h = $c['heritage'];
    $min = $courses->whereNotNull('price_from')->min('price_from');
@endphp
<section class="block block--sunken" aria-labelledby="profil-h">
    <div class="container">
        <div class="fact-profile">
            <div>
                <x-ui.kicker>Kurzprofil</x-ui.kicker>
                <h2 id="profil-h" class="h-section">Die Messermanufaktur Ulbrich auf einen Blick</h2>
                <p class="lead mt-4">Die Messermanufaktur Ulbrich ist die Messerschmiede von {{ $c['owner'] }} in {{ $a['district'] }}, einem Ortsteil von {{ $a['locality'] }} im Landkreis Heilbronn. Sie bietet Schmiedekurse an und fertigt Messer von Hand – in einer Familie, in der das Schmiedehandwerk seit {{ $h['since'] }} weitergegeben wird.</p>
            </div>
            <dl class="spec fact-profile__list">
                <div class="spec__row"><dt>Name</dt><dd>{{ $c['name'] }}</dd></div>
                <div class="spec__row"><dt>Inhaber</dt><dd>{{ $c['owner'] }}, {{ $c['owner_role'] }}</dd></div>
                <div class="spec__row"><dt>Tradition</dt><dd>Schmiedehandwerk seit {{ $h['since'] }}, {{ $h['generation'] }}. Generation</dd></div>
                <div class="spec__row"><dt>Werkstatt</dt><dd>{{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }} (Ortsteil {{ $a['district'] }})</dd></div>
                <div class="spec__row"><dt>Region</dt><dd>Landkreis Heilbronn, {{ $a['region'] }}</dd></div>
                <div class="spec__row"><dt>Schmiedekurse</dt><dd>{{ $courses->count() }} Formate, X</dd></div>
                <div class="spec__row"><dt>Messer</dt><dd>{{ $c['edition']['name'] }} ({{ $c['edition']['total'] }} Stück), Unikate, Sonderanfertigungen</dd></div>
                <div class="spec__row"><dt>Mitglied</dt><dd>{{ $c['chamber'] }}</dd></div>
                <div class="spec__row"><dt>Telefon</dt><dd><a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></dd></div>
            </dl>
        </div>
    </div>
</section>
