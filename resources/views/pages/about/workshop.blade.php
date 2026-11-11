<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Die Manufaktur" kicker="Messerschmied {{ $c['owner'] }}" foto="andreas-portrait" pos="top"
        lead="Schmiedehandwerk seit {{ $c['heritage']['since'] }} in der Familie, heute in {{ $c['heritage']['generation'] }}. Generation. Eine Werkstatt in {{ $c['address']['district'] }} bei {{ $c['address']['locality'] }}, ein Messerschmied und drei Wege zum Messer: selbst schmieden, ein fertiges Stück wählen oder eines anfertigen lassen." />

    @include('pages.home.01b-heritage')

    <x-page.section title="{{ $c['owner'] }}" kicker="Der Messerschmied">
        <div class="split">
            <div class="reveal">
                <ul class="punze-row" role="list">
                    <li><x-ui.punze key="Beruf">{{ $c['owner_role'] }}</x-ui.punze></li>
                    <li><x-ui.punze key="Mitglied">{{ $c['chamber'] }}</x-ui.punze></li>
                    <li><x-ui.punze key="Werkstatt">{{ $c['address']['district'] }}</x-ui.punze></li>
                </ul>
                <p class="lead mt-5">In der Werkstatt entstehen die Messer der Manufaktur – von der nummerierten Edition 100 bis zur Einzelanfertigung. Hier finden auch die Schmiedekurse statt.</p>
                <x-ui.missing silent>Kurzbiografie, Ausbildungsweg, seit wann selbstständig – nur belegte Angaben</x-ui.missing>
                            </div>
            <x-ui.media foto="klappmesser-hell" format="wide" class="reveal reveal-delay-1" sizes="(min-width: 60rem) 45vw, 100vw" />
        </div>
    </x-page.section>

    <x-page.section title="Was in der Werkstatt entsteht" kicker="Angebot" sunken>
        <div class="path-cards path-cards--three">
            <a class="path-card course--einstieg reveal" href="/schmiedekurse/"><p class="path-card__label">Erleben</p><h3>Schmiedekurse</h3><p>{{ $courses->count() }} Kursformate, 1 bis 3 Tage.</p><span class="link-arrow">Kurse <span aria-hidden="true">→</span></span></a>
            <a class="path-card course--klinge reveal reveal-delay-1" href="/klappmesser/"><p class="path-card__label">Besitzen</p><h3>{{ $e['name'] }}</h3><p>{{ $e['total'] }} nummerierte Damast-Klappmesser.</p><span class="link-arrow">Edition <span aria-hidden="true">→</span></span></a>
            <a class="path-card course--damast reveal reveal-delay-2" href="/sonderanfertigungen/"><p class="path-card__label">Anfertigen</p><h3>Sonderanfertigung</h3><p>Ein Messer nach deinen Vorstellungen.</p><span class="link-arrow">Anfragen <span aria-hidden="true">→</span></span></a>
        </div>
    </x-page.section>

    @include('pages.partials.fact-profile')

    @include('pages.home.10-process')

    @php $quote = collect($c['press'])->firstWhere('quote'); $book = $c['book'] ?? null; @endphp
    @if ($quote)
        <section class="block is-dark textured" aria-label="Zitat">
            <div class="container">
                <figure class="big-quote reveal">
                    <blockquote><p>„{{ $quote['quote'] }}“</p></blockquote>
                    <figcaption>{{ $c['owner'] }} · <a href="{{ $quote['url'] }}" rel="noopener" target="_blank">{{ $quote['outlet'] }}, {{ \Illuminate\Support\Carbon::parse($quote['date'])->format('d.m.Y') }}</a></figcaption>
                </figure>
            </div>
        </section>
    @endif

    @if ($book)
        <x-page.section title="Ein gemeinsames Buch" kicker="Auch abseits der Werkbank">
            <div class="split">
                <x-ui.type-tile class="book-tile reveal" :title="$book['title']" :label="$book['format']" :meta="$book['authors']" />
                <div class="reveal reveal-delay-1">
                    <p class="lead">{{ $book['authors'] }} haben gemeinsam ein Buch veröffentlicht: „{{ $book['title'] }}“.</p>
                    <p class="mt-4">Buchbeschreibung, aktueller Preis und Leseoptionen stehen bei Amazon.</p>
                    <div class="btn-row mt-5"><x-ui.button :href="$book['url']" variant="secondary" target="_blank" rel="noopener">Zum Buch auf Amazon</x-ui.button></div>
                </div>
            </div>
        </x-page.section>
    @endif

    <x-page.section title="Mehr über die Manufaktur" kicker="Weiterlesen">
        <div class="link-list">
            <a href="/presse/">Presse <span aria-hidden="true">→</span></a>
            <a href="/kunsthandwerkermarkt/">Kunsthandwerkermarkt <span aria-hidden="true">→</span></a>
            <a href="/partner/">Partner <span aria-hidden="true">→</span></a>
            <a href="/schmiedekurse/heilbronn-region/">Anfahrt und Region <span aria-hidden="true">→</span></a>
        </div>
    </x-page.section>

    <x-page.cta />
</x-layouts.site>
