<section class="block block--sunken" aria-labelledby="dates-title">
    <div class="container dates__grid">
        <div class="reveal">
            <x-ui.kicker>Kurstermine</x-ui.kicker>
            <h2 id="dates-title" class="h-section">Nächste freie Termine</h2>
            <div class="calendar mt-5">
                <div class="calendar__head">
                    <span>Nächster freier Tag · Kurs</span>
                    <x-ui.icon name="calendar" width="20" height="20" />
                </div>
                @php $cal = \App\Support\CourseCalendar::class; @endphp
                <ul class="calendar__list" role="list">
                    @foreach ($courses->filter(fn ($co) => $cal::bookable($co)) as $co)
                        @php $next = $cal::nextFree($co, 1)[0] ?? null; @endphp
                        @if ($next)
                            @php $d = \Illuminate\Support\Carbon::parse($next); @endphp
                            <li>
                                <span class="calendar__date"><b>{{ $d->format('d') }}</b>{{ $d->locale('de')->isoFormat('MMM YYYY') }}</span>
                                <span>{{ $co->name }}<br><small class="tech muted">nächster freier Tag · {{ $co->duration_label }}</small></span>
                                <x-ui.button href="/schmiedekurse/buchen/?kurs={{ $co->slug }}">Buchen<span class="visually-hidden"> {{ $co->name }}</span></x-ui.button>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <p class="mt-4"><a class="link-arrow" href="/schmiedekurse/buchen/">Alle freien Tage im Kalender <span aria-hidden="true">→</span></a></p>
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
