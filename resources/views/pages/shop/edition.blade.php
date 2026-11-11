<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="{{ $e['name'] }}" kicker="Limitiert auf {{ $e['total'] }} Stück" tone="brown" foto="edition-messer"
        lead="Eine Auflage von {{ $e['total'] }} Damast-Klappmessern, jedes mit eigener Nummer: {{ $e['regular'] }} reguläre Stücke und {{ $e['special'] }} Sonderstücke.">
        <x-slot:actions>
            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">Bestellen: {{ $c['phone']['display'] }}</x-ui.button>
            <x-ui.button href="#galerie" variant="secondary" size="lg">Bilder ansehen</x-ui.button>
        </x-slot:actions>
        <p class="page-hero__price"><span class="page-hero__amount">{{ number_format($e['price'], 0, ',', '.') }} €</span> reguläre Stücke · <x-ui.tax-note /></p>
    </x-page.hero>

    <section class="block edition-page is-dark textured" aria-labelledby="ed-h">
        <div class="container edition__grid">
            <div class="reveal">
                <x-ui.punze size="lg">Nº ___ / {{ $e['total'] }}</x-ui.punze>
                <h2 id="ed-h" class="h-section mt-4">Hundert Messer, hundert Nummern</h2>
                <p class="lead mt-4">Jedes Messer der Edition trägt eine eigene Nummer. {{ $e['regular'] }} Stücke gehören zur regulären Auflage, {{ $e['special'] }} sind Sonderstücke.</p>
                @include('pages.partials.edition-serials')
            </div>
            <div class="reveal reveal-delay-1">
                <h3 class="spec-title">Technische Daten</h3>
                <dl class="spec">
                    <div class="spec__row"><dt>Auflage</dt><dd>{{ $e['total'] }} Stück ({{ $e['regular'] }} + {{ $e['special'] }})</dd></div>
                    <div class="spec__row"><dt>Preis</dt><dd>{{ number_format($e['price'], 0, ',', '.') }} € (reguläre Stücke)</dd></div>
                    <div class="spec__row"><dt>Sonderstücke</dt><dd>{{ $e['special_note'] }} <a href="/klappmesser/sonderedition/">Zu den Sonderstücken</a></dd></div>
                    <div class="spec__row"><dt>Klinge</dt><dd>Damaststahl @if($e['steel'])· {{ $e['steel'] }}@else<x-ui.missing silent>Stahlsorte/Lagen</x-ui.missing>@endif</dd></div>
                    <div class="spec__row"><dt>Maße</dt><dd>@if($e['dimensions']){{ $e['dimensions'] }}@else<x-ui.missing>Klingen-/Gesamtlänge, Gewicht</x-ui.missing>@endif</dd></div>
                    <div class="spec__row"><dt>Griff</dt><dd>@if($e['handle']){{ $e['handle'] }}@else<x-ui.missing>Griffmaterial</x-ui.missing>@endif</dd></div>
                    <div class="spec__row"><dt>Seriennummer</dt><dd>{{ $e['serial_note'] }}</dd></div>
                    <div class="spec__row"><dt>Versand</dt><dd>{{ $e['shipping'] }}</dd></div>
                    <div class="spec__row"><dt>Abholung</dt><dd>{{ config('ulbrich.sales.pickup_note') }}; vor Ort bar oder mit Karte</dd></div>
                    <div class="spec__row"><dt>Ausland</dt><dd>{{ config('ulbrich.sales.international') }} {{ config('ulbrich.sales.customs') }} <a href="/versand/">Versand &amp; Abholung</a></dd></div>
                    <div class="spec__row"><dt>Garantie</dt><dd>{{ config('ulbrich.warranty.years') }} Jahre, {{ config('ulbrich.warranty.condition') }} <a href="/garantie/">Zur Garantie</a></dd></div>
                    <div class="spec__row"><dt>Lieferumfang</dt><dd>Messer, {{ implode(', ', config('ulbrich.warranty.included')) }}</dd></div>
                    <div class="spec__row"><dt>Lieferzeit</dt><dd>{{ $e['delivery'] }}</dd></div>
                    <div class="spec__row"><dt>Zahlung</dt><dd>{{ $e['payment'] }}</dd></div>
                    <div class="spec__row"><dt>Hersteller</dt><dd>{{ $c['name'] }}, {{ $c['owner'] }}</dd></div>
                </dl>
                <p class="mt-4"><a class="link-arrow" href="/messerprojekte/damast-klappmesser-edition-100/">Das Messerprojekt hinter der Edition 100</a></p>
                <x-page.note class="mt-5" title="Bestellung">
                    Online-Bestellung folgt. Bis dahin reservierst du dein Messer telefonisch: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.
                </x-page.note>
            </div>
        </div>
    </section>

    <section class="block is-dark textured" id="galerie" aria-labelledby="edgal-h">
        <div class="container">
            <h2 id="edgal-h" class="h-section">Details</h2>
            <div class="mosaic mt-6">
                <x-ui.media foto="klappmesser-offen" class="reveal" sizes="(min-width: 60rem) 50vw, 100vw" />
                <x-ui.media foto="punze-detail" class="reveal reveal-delay-1" sizes="(min-width: 60rem) 25vw, 50vw" />
                <x-ui.media foto="klappmesser-geschlossen" class="reveal reveal-delay-2" sizes="(min-width: 60rem) 25vw, 50vw" />
                <x-ui.media foto="damast-detail" class="reveal reveal-delay-1" sizes="(min-width: 60rem) 25vw, 50vw" />
                <x-ui.media foto="klappmesser-stehend" class="reveal reveal-delay-2" sizes="(min-width: 60rem) 25vw, 50vw" />
            </div>
        </div>
    </section>

    @include('pages.partials.subpages', ['prefix' => '/klappmesser/', 'title' => 'Die zehn Sonderstücke', 'kicker' => 'Edition 100'])

    <x-page.cta title="Eines der hundert Messer sichern?" text="Verfügbare Nummern und Sonderstücke erfährst du direkt bei {{ $c['owner'] }}." />
</x-layouts.site>
