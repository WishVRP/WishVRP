<section class="block block--sunken" aria-labelledby="dates-title">
    <div class="container dates__grid">
        <div class="reveal">
            <x-ui.kicker>Kurstermine</x-ui.kicker>
            <h2 id="dates-title" class="h-section">Nächste freie Termine</h2>
            <div class="calendar mt-5">
                <div class="calendar__head">
                    <span>Termin · Kurs · freie Plätze</span>
                    <x-ui.icon name="calendar" width="20" height="20" />
                </div>
                @if ($dates->isNotEmpty())
                    <ul class="calendar__list" role="list">
                        @foreach ($dates as $date)
                            <li>
                                <span class="calendar__date"><b>{{ $date->starts_on->format('d') }}</b>{{ $date->starts_on->locale('de')->isoFormat('MMM YYYY') }}</span>
                                <span>{{ $date->course->name }}<br><small class="tech muted">{{ $date->seatsLeftLabel() }}</small></span>
                                <x-ui.button href="{{ $date->course->bookingUrl() }}">Buchen<span class="visually-hidden"> am {{ $date->starts_on->format('d.m.Y') }}</span></x-ui.button>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="calendar__empty">
                        <x-ui.missing silent ask="aus dem Buchungssystem übernehmen">Kurstermine</x-ui.missing>
                        <p>Freie Plätze und alle Termine siehst du tagesaktuell im Buchungskalender.</p>
                        <x-ui.button href="/schmiedekurse/buchen/" size="lg">Zum Buchungskalender</x-ui.button>
                    </div>
                @endif
            </div>
        </div>
        <aside class="reveal reveal-delay-1" aria-labelledby="rules-title">
            <h3 id="rules-title" class="kicker">Gut zu wissen vor der Buchung</h3>
            <ul class="rules" role="list">
                @php $icons = ['online', 'swap', 'gift']; @endphp
                @foreach ($c['booking_rules'] as $i => $rule)
                    <li><x-ui.icon :name="$icons[$i] ?? 'online'" /><span>{{ $rule }}</span></li>
                @endforeach
            </ul>
            <p class="mt-4"><a class="link-arrow" href="/kursbedingungen/">Alle Kursbedingungen <span aria-hidden="true">→</span></a></p>
        </aside>
    </div>
</section>
