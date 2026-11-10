<section class="block is-dark textured edition" aria-labelledby="edition-title">
    <div class="container edition__grid">
        <div>
            <div class="edition__big" aria-hidden="true">100</div>
            <x-ui.media foto="klappmesser-offen" format="square" pos="upper" class="reveal" sizes="(min-width: 60rem) 40vw, 100vw" />
        </div>
        <div class="reveal reveal-delay-1">
            <x-ui.punze size="lg">Nº ___ / {{ $e['total'] }}</x-ui.punze>
            <h2 id="edition-title" class="h-section">{{ $e['name'] }}</h2>
            <p class="edition__lead">Eine Auflage von {{ $e['total'] }} Damast-Klappmessern, jedes mit eigener Nummer: {{ $e['regular'] }} reguläre Stücke und {{ $e['special'] }} Sonderstücke.</p>

            <div class="edition__count">
                <ul class="serials reveal" role="img" aria-label="{{ $e['regular'] }} reguläre Stücke und {{ $e['special'] }} Sonderstücke">
                    @for ($i = 1; $i <= $e['total']; $i++)
                        <li @class(['is-special' => $i > $e['regular']])></li>
                    @endfor
                </ul>
                <p class="serials-legend" aria-hidden="true"><span>{{ $e['regular'] }} reguläre Stücke à {{ number_format($e['price'], 0, ',', '.') }} €</span><span>{{ $e['special'] }} Sonderstücke</span></p>
            </div>

            <dl class="spec">
                <div class="spec__row"><dt>Preis</dt><dd>{{ number_format($e['price'], 0, ',', '.') }} € (reguläre Stücke)</dd></div>
                <div class="spec__row"><dt>Stahl</dt><dd>@if($e['steel']){{ $e['steel'] }}@else<x-ui.missing>Stahlsorte/Lagen Edition 100</x-ui.missing>@endif</dd></div>
                <div class="spec__row"><dt>Maße</dt><dd>@if($e['dimensions']){{ $e['dimensions'] }}@else<x-ui.missing>Klingen-/Gesamtlänge, Gewicht</x-ui.missing>@endif</dd></div>
                <div class="spec__row"><dt>Griff</dt><dd>@if($e['handle']){{ $e['handle'] }}@else<x-ui.missing>Griffmaterial</x-ui.missing>@endif</dd></div>
            </dl>
            <div class="btn-row">
                <x-ui.button href="{{ $e['url'] }}" variant="straw" size="lg">Edition 100 ansehen</x-ui.button>
                <x-ui.button href="/shop/" variant="secondary" size="lg">Zum Shop</x-ui.button>
            </div>
        </div>
    </div>
</section>
