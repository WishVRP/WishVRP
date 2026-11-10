@php $manager = $courses->firstWhere('category', 'gruppe'); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmieden als Erlebnis" kicker="Erlebnisse" tone="straw" foto="klappmesser-hell-detail"
        lead="Ein Tag an Esse und Amboss – für dich selbst, als Geschenk oder gemeinsam im Team." />

    <x-page.section title="Drei Wege ans Feuer" kicker="Erlebnisse">
        <div class="path-cards path-cards--three">
            <a class="path-card course--einstieg reveal" href="/schmiedekurse/">
                <p class="path-card__label">Für dich</p>
                <h3>Selbst schmieden</h3>
                <p>Vom eintägigen Grundlagenkurs bis zum Damast Premium-Kurs über zwei bis drei Tage.</p>
                <span class="link-arrow">Kurse ansehen <span aria-hidden="true">→</span></span>
            </a>
            <a class="path-card course--damast reveal reveal-delay-1" href="/geschenke/schmiedekurs-gutschein/">
                <p class="path-card__label">Zum Verschenken</p>
                <h3>Schmiedekurs-Gutschein</h3>
                <p>Kurs und Termin wählt die beschenkte Person später selbst.</p>
                <span class="link-arrow">Gutschein <span aria-hidden="true">→</span></span>
            </a>
            <a class="path-card course--gruppe reveal reveal-delay-2" href="#teams">
                <p class="path-card__label">Für Teams</p>
                <h3>{{ $manager?->name ?? 'Manager-Schmiedekurs' }}</h3>
                <p>{{ $manager?->duration_label ?? '3 Tage' }}, Termin und Preis nach Absprache.</p>
                <span class="link-arrow">Mehr erfahren <span aria-hidden="true">→</span></span>
            </a>
        </div>
    </x-page.section>

    <div id="teams">@include('pages.home.12-teams')</div>

    <x-page.cta />
</x-layouts.site>
