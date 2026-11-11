<section class="block is-dark textured" aria-labelledby="experience-title">
    <div class="container experience__grid">
        <div class="collage reveal">
            <x-ui.media foto="klappmesser-hell-detail" sizes="(min-width: 60rem) 35vw, 70vw" />
            <x-ui.media foto="damast-detail" sizes="(min-width: 60rem) 25vw, 50vw" />
            <x-ui.media foto="punze-detail" sizes="(min-width: 60rem) 15vw, 35vw" />
        </div>
        <div class="experience__text reveal reveal-delay-1">
            <x-ui.kicker>Schmiedekurse</x-ui.kicker>
            <h2 id="experience-title" class="h-section">Am Amboss entsteht dein eigenes Messer</h2>
            <p>
                Du arbeitest selbst an Esse und Amboss und formst den Stahl mit dem Hammer – vom eintägigen
                Grundlagenkurs bis zum Damast Premium-Kurs über zwei bis drei Tage.
            </p>
            <dl class="figures">
                <div><dt>Kursformate</dt><dd data-count="{{ $courses->count() }}">{{ $courses->count() }}</dd></div>
                <div><dt>Tage Kursdauer</dt><dd>1–3</dd></div>
                <div><dt>cm Messer im Premium-Kurs</dt><dd data-count="{{ $maxBlade }}">{{ $maxBlade }}</dd></div>
            </dl>
            <div class="btn-row">
                <x-ui.button href="#kursfinder" variant="straw">Kurse vergleichen</x-ui.button>
                <x-ui.button href="/schmiedekurse/" variant="secondary">Alle Schmiedekurse</x-ui.button>
            </div>
        </div>
    </div>
</section>
