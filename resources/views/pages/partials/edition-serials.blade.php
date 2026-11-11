{{-- 100er-Raster der Edition: jedes Feld = ein Messer. Hover/Tippen zeigt Nummer, Status und – nur mit Zustimmung – den Namen. --}}
@php
    $pieces = \App\Models\EditionPiece::grid();
    $taken = $pieces->where('status', 'vergeben')->count();
    $reserved = $pieces->where('status', 'reserviert')->count();
@endphp
<div class="edition__count" data-serials>
    <ul class="serials reveal" aria-label="Die {{ $e['total'] }} Messer der Edition – Feld antippen für Details">
        @foreach ($pieces as $p)
            <li @class(['is-special' => $p->isSpecial(), 'is-taken' => $p->status === 'vergeben', 'is-reserved' => $p->status === 'reserviert'])>
                <button type="button" class="serial" data-tip="{{ $p->publicLabel() }}" aria-label="{{ $p->publicLabel() }}"></button>
            </li>
        @endforeach
    </ul>
    <div>
        <p class="serials-legend" aria-hidden="true">
            <span>{{ $e['regular'] }} reguläre Stücke à {{ number_format($e['price'], 0, ',', '.') }} €</span>
            <span class="serials-legend__special">{{ $e['special'] }} Sonderstücke</span>
            @if ($taken)<span class="serials-legend__taken">{{ $taken }} vergeben</span>@endif
            @if ($reserved)<span class="serials-legend__reserved">{{ $reserved }} reserviert</span>@endif
        </p>
        <p class="serials-out" data-serial-out aria-live="polite">Fahr über ein Feld oder tipp es an.</p>
    </div>
</div>
