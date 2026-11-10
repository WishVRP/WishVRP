<div class="edition__count">
    <ul class="serials reveal" role="img" aria-label="{{ $e['regular'] }} reguläre Stücke und {{ $e['special'] }} Sonderstücke">
        @for ($i = 1; $i <= $e['total']; $i++)
            <li @class(['is-special' => $i > $e['regular']])></li>
        @endfor
    </ul>
    <p class="serials-legend" aria-hidden="true"><span>{{ $e['regular'] }} reguläre Stücke à {{ number_format($e['price'], 0, ',', '.') }} €</span><span>{{ $e['special'] }} Sonderstücke</span></p>
</div>
