@php $h = $c['heritage']; $years = now()->year - $h['since']; @endphp
<section class="hero hero--photo hero--story is-dark" aria-labelledby="hero-title">
    <x-ui.media class="hero__media" foto="klappmesser-offen" eager sizes="(min-width: 64rem) 62vw, 100vw" />
    <canvas class="hero__sparks" data-sparks aria-hidden="true"></canvas>
    <p class="hero__year" aria-hidden="true">{{ $h['since'] }}</p>
    <div class="container hero__inner">
        <div>
            <x-ui.kicker color="straw">{{ $h['generation'] }}. Generation · Schmiedehandwerk seit {{ $h['since'] }}</x-ui.kicker>
            <h1 id="hero-title" class="display">
                Schmiedekurse und handgeschmiedete Messer
                <span class="display__sub">aus einer Familie, die seit {{ $h['since'] }} am Feuer steht</span>
            </h1>
            <p class="hero__lead">
                Stell dich an Esse und Amboss und schmiede dein eigenes Messer – angeleitet von Messerschmied {{ $c['owner'] }},
                der das Handwerk in {{ $h['generation'] }}. Generation weiterträgt.
            </p>
            <div class="btn-row">
                <x-ui.button href="/schmiedekurse/buchen/" variant="straw" size="lg">Schmiedekurs buchen</x-ui.button>
                <x-ui.button href="#kursfinder" variant="secondary" size="lg">Kurse vergleichen</x-ui.button>
            </div>
            @if ($minPrice)<p class="hero__microcopy">Kurse ab {{ number_format($minPrice, 0, ',', '.') }} € p. P. · Gutschein möglich · Umbuchen bis 7 Tage vorher</p>@endif
        </div>
        <dl class="hero__facts" aria-label="Auf einen Blick">
            <div><dt>Familientradition</dt><dd>seit {{ $h['since'] }} · {{ $years }} Jahre</dd></div>
            <div><dt>Generation</dt><dd>{{ $h['generation'] }}. · {{ $c['owner'] }}</dd></div>
            <div><dt>Kursformate</dt><dd>{{ $courses->count() }} · 1 bis 3 Tage</dd></div>
            @if ($rating)
                <div><dt>Google</dt><dd><span class="stars" aria-hidden="true">★★★★★</span> {{ $rating['value'] }} · {{ $rating['count'] }} Bewertungen</dd></div>
            @endif
        </dl>
        <x-ui.temper-scale class="hero__temper" />
    </div>
</section>
