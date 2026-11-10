<section class="block" aria-labelledby="guides-title">
    <div class="container">
        <div class="block-head">
            <div>
                <x-ui.kicker>Ratgeber</x-ui.kicker>
                <h2 id="guides-title" class="h-section">Wissen rund ums Schmieden</h2>
            </div>
            <p class="block-head__intro">Antworten vor der Buchung: was ein Kurs kostet und wie du dich vorbereitest.</p>
        </div>
        <div class="guides__grid">
            @foreach ($c['guides'] as $i => $guide)
                <a class="guide media-link reveal {{ $i ? 'reveal-delay-1' : '' }}" href="{{ $guide['path'] }}">
                    <x-ui.type-tile class="guide__tile" :title="$guide['topic']" label="Ratgeber" />
                    <span class="guide__topic">{{ $guide['topic'] }}</span>
                    <h3>{{ $guide['title'] }}</h3>
                </a>
            @endforeach
            <a class="guide guide--all reveal reveal-delay-2" href="/ratgeber/">
                <span class="guide__topic">Ratgeber</span>
                <h3>Alle Artikel lesen →</h3>
            </a>
        </div>
    </div>
</section>
