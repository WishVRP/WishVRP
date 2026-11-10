<section class="block" id="messer" aria-labelledby="knives-title">
    <div class="container">
        <div class="block-head">
            <div>
                <x-ui.kicker color="brown">Messer aus der Manufaktur</x-ui.kicker>
                <h2 id="knives-title" class="h-section">Handgefertigte Messer</h2>
            </div>
            <p class="block-head__intro">Nummerierte Edition, Einzelstücke und Anfertigungen nach Wunsch – jedes Messer entsteht in der Werkstatt von {{ $c['owner'] }}.</p>
        </div>
        @php $fotos = ['klappmesser-stehend', 'klappmesser-rotholz', null, 'messer-tisch', 'klappmesser-zebrano']; $tones = ['forge', 'brown', 'steel', 'forge', 'brown']; $formats = ['Klappmesser Edition 100, geöffnet, Damastmuster', 'Unikat auf Holz, Seitenansicht', 'Skizze und Rohling', 'Fertige Projekte nebeneinander', 'Messer in der Auslage']; @endphp
        <div class="knife-grid">
            @foreach ($c['knife_areas'] as $i => $area)
                <a class="knife-tile reveal {{ $i ? 'reveal-delay-'.min($i, 3) : '' }}" href="{{ $area['path'] }}">
                    <x-ui.media :foto="$fotos[$i] ?? null" :motif="$formats[$i] ?? $area['title']" :tone="$tones[$i] ?? 'forge'" decorative sizes="(min-width: 64rem) 33vw, (min-width: 40rem) 50vw, 100vw" />
                    <span class="knife-tile__body">
                        <span>
                            <h3>{{ $area['title'] }}</h3>
                            <p>{{ $area['text'] }}</p>
                        </span>
                        <span class="knife-tile__arrow" aria-hidden="true">→</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
