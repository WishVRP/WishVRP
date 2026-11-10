<section class="hero hero--photo is-dark" aria-labelledby="hero-title">
    <x-ui.media class="hero__media" foto="andreas-esse" pos="upper" eager sizes="(min-width: 64rem) 62vw, 100vw" />
    <canvas class="hero__sparks" data-sparks aria-hidden="true"></canvas>
    <div class="container hero__inner">
        <div>
            <x-ui.kicker color="straw">{{ $c['name'] }} · {{ $c['place_label'] }}</x-ui.kicker>
            <h1 id="hero-title" class="display">
                Schmiedekurse und handgeschmiedete Messer
                <span class="display__sub">aus der Manufaktur von Messerschmied {{ $c['owner'] }}</span>
            </h1>
            <p class="hero__lead">
                Schmiede dein eigenes Messer an Esse und Amboss – an einem Tag oder in zwei bis drei Tagen aus Damaststahl.
                Oder wähle ein Messer, das in der Werkstatt von Hand entsteht.
            </p>
            <div class="btn-row">
                <x-ui.button href="#kursfinder" variant="straw" size="lg">Passenden Kurs finden</x-ui.button>
                <x-ui.button href="#messer" variant="secondary" size="lg">Messer entdecken</x-ui.button>
            </div>
        </div>
        <dl class="hero__facts" aria-label="Auf einen Blick">
            @if ($minPrice)
                <div><dt>Schmiedekurse</dt><dd>ab {{ number_format($minPrice, 0, ',', '.') }} € p. P.</dd></div>
            @endif
            <div><dt>Kursformate</dt><dd>{{ $courses->count() }} · 1 bis 3 Tage</dd></div>
            <div><dt>Edition 100</dt><dd>{{ $e['total'] }} nummerierte Klappmesser</dd></div>
            @if ($rating)
                <div><dt>Google</dt><dd><span class="stars" aria-hidden="true">★★★★★</span> {{ $rating['value'] }} · {{ $rating['count'] }} Bewertungen</dd></div>
            @endif
        </dl>
        <x-ui.temper-scale class="hero__temper" />
    </div>
</section>
