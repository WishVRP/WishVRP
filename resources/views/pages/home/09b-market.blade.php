{{-- Kunst- & Handwerkermarkt auf dem Gelände der Manufaktur (Termin und „Bewerbung offen“ aus den Markt-Einstellungen) --}}
@php
    $m = config('ulbrich.market');
    $ms = \App\Support\Market::settings();
    $standCount = count(\App\Support\Market::stands());
    $free = collect(\App\Support\Market::statusMap())->filter(fn ($s) => $s === 'frei')->count();
    $minPrice = collect($m['stands'])->min('price');
@endphp
<section class="block market-home" id="kunsthandwerkermarkt" aria-labelledby="market-home-title">
    <div class="container">
        <div class="market-home__inner reveal">
            <x-ui.kicker color="violet">{{ $m['name'] }}</x-ui.kicker>
            <h2 id="market-home-title" class="h-section">Kunst und Handwerk in unserer Halle – nur Selbstgemachtes</h2>
            <p class="lead mt-3">
                An zwei Markttagen – {{ $m['days'] }} – verwandeln sich Halle und Außengelände der Manufaktur in einen Markt für Kunst und Handwerk –
                mit {{ $standCount }} Ständen für Menschen, die ihre Waren selbst herstellen.
            </p>
            <ul class="punze-row" role="list">
                <li><x-ui.punze key="Termin">{{ $ms['date'] ?: 'wird bekannt gegeben' }}</x-ui.punze></li>
                <li><x-ui.punze key="Ort">Stein am Kocher</x-ui.punze></li>
                <li><x-ui.punze key="Stände">{{ $ms['open'] ? $free.' von '.$standCount.' frei' : $standCount }}</x-ui.punze></li>
                <li><x-ui.punze key="Stand">ab {{ $minPrice }} €</x-ui.punze></li>
            </ul>
            <div class="btn-row mt-5">
                @if ($ms['open'])
                    <x-ui.button href="/kunsthandwerkermarkt/#standplan" variant="violet" size="lg">Stand aussuchen und bewerben</x-ui.button>
                @endif
                <x-ui.button href="/kunsthandwerkermarkt/" variant="secondary">Alles zum Markt</x-ui.button>
            </div>
        </div>
    </div>
</section>
