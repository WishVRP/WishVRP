@php $a = $c['address']; $route = 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($a['street'].', '.$a['postal_code'].' '.$a['locality']); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmiedekurs in der Region Heilbronn" kicker="Landkreis Heilbronn" foto="klappmesser-geschlossen"
        lead="Die Werkstatt der Messermanufaktur Ulbrich liegt in {{ $a['district'] }}, einem Ortsteil von {{ $a['locality'] }} im Landkreis Heilbronn. Hier finden die Schmiedekurse statt.">
        <x-slot:actions>
            <x-ui.button href="/schmiedekurse/" variant="straw" size="lg">Alle Kurse</x-ui.button>
            <x-ui.button :href="$route" variant="secondary" size="lg" target="_blank" rel="noopener">Route planen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>
    <x-ui.missing silent>Kursort = Werkstatt bestätigen</x-ui.missing>

    <x-page.section title="So findest du die Werkstatt" kicker="Anfahrt">
        <div class="split">
            <div class="address-card reveal">
                <x-ui.icon name="pin" class="address-card__icon" />
                <address>
                    <strong>{{ $c['name'] }}</strong><br>
                    {{ $a['street'] }}<br>
                    {{ $a['postal_code'] }} {{ $a['locality'] }}<br>
                    <span class="muted">Ortsteil {{ $a['district'] }} · Landkreis Heilbronn</span>
                </address>
                <div class="btn-row mt-4">
                    <x-ui.button :href="$route" target="_blank" rel="noopener">Route in Google Maps</x-ui.button>
                    <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="secondary" :arrow="false">{{ $c['phone']['display'] }}</x-ui.button>
                </div>
            </div>
            <div class="reveal reveal-delay-1">
                <dl class="spec">
                    @foreach ($c['opening_hours'] as $h)
                        <div class="spec__row"><dt>{{ $h['label'] }}</dt><dd>{{ $h['opens'] }}–{{ $h['closes'] }} Uhr</dd></div>
                    @endforeach
                </dl>
                @unless ($c['opening_hours_verified'])<x-ui.missing silent>Öffnungszeiten bestätigen</x-ui.missing>@endunless
                <x-ui.missing silent>Parken, Anfahrt mit ÖPNV</x-ui.missing>
            </div>
        </div>
    </x-page.section>

    @include('pages.home.04-finder')

    <x-page.cta title="Termin in der Werkstatt?" />
</x-layouts.site>
