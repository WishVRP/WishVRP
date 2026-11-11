{{--
    100er-Raster der Edition: jedes Feld = ein Messer.
    Rot = verkauft, Grün = verkauft und Name freigegeben (antippen zeigt den Namen), schraffiert = reserviert,
    Gold = Sonderstück, dunkel = frei (antippen wählt die Nummer im Bestellformular).
--}}
@php
    $pieces = \App\Models\EditionPiece::grid();
    $sold = $pieces->where('status', 'vergeben')->count();
    $named = $pieces->filter->hasPublicName()->count();
    $reserved = $pieces->where('status', 'reserviert')->count();
    $free = $pieces->filter->isSelectable()->count();
    $onEditionPage = request()->is('klappmesser');
@endphp
<div class="edition__count" data-serials data-select-target="{{ $onEditionPage ? '#edition-nummer' : '' }}">
    <ul class="serials reveal" aria-label="Die {{ $e['total'] }} Messer der Edition – Feld antippen für Details">
        @foreach ($pieces as $p)
            <li @class([
                'is-special' => $p->isSpecial(),
                'is-sold' => $p->status === 'vergeben' && ! $p->hasPublicName(),
                'is-named' => $p->hasPublicName(),
                'is-reserved' => $p->status === 'reserviert',
                'is-free' => $p->isSelectable(),
            ])>
                <button type="button" class="serial" data-tip="{{ $p->publicLabel() }}" aria-label="{{ $p->publicLabel() }}"
                    @if ($p->isSelectable()) data-number="{{ $p->number }}" @endif></button>
            </li>
        @endforeach
    </ul>
    <div>
        <p class="serials-legend" aria-hidden="true">
            <span class="serials-legend__free">{{ $free }} frei – Nummer selbst aussuchen</span>
            <span class="serials-legend__sold">{{ $sold - $named }} verkauft</span>
            @if ($named)<span class="serials-legend__named">{{ $named }} mit Namen – antippen</span>@endif
            @if ($reserved)<span class="serials-legend__reserved">{{ $reserved }} reserviert</span>@endif
            <span class="serials-legend__special">{{ $e['special'] }} Sonderstücke</span>
        </p>
        <p class="serials-out" data-serial-out aria-live="polite">Tipp ein Feld an: Wem gehört es – oder ist es noch frei?</p>
        @unless ($onEditionPage)
            <a class="link-arrow mt-3" href="/klappmesser/#anfrage">Wunschnummer reservieren</a>
        @endunless
    </div>
</div>
