<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="{{ $page['h1'] ?? 'Schmiedekurse' }}" kicker="An Esse und Amboss" foto="klappmesser-stehend"
        lead="Schmiede dein eigenes Messer – an einem Tag im Grundlagenkurs oder in zwei bis drei Tagen mit Damaststahl. Sechs Kursformate, eine Werkstatt, ein Messerschmied.">
        <x-slot:actions>
            <x-ui.button href="#kursfinder" variant="straw" size="lg">Kurs finden</x-ui.button>
            <x-ui.button href="/schmiedekurse/buchen/" variant="secondary" size="lg">Termin buchen</x-ui.button>
        </x-slot:actions>
        <dl class="page-facts">
            <div><dt>Kursformate</dt><dd data-count="{{ $courses->count() }}">{{ $courses->count() }}</dd></div>
            @if ($minPrice)<div><dt>ab € p. P.</dt><dd data-count="{{ $minPrice }}">{{ $minPrice }}</dd></div>@endif
            <div><dt>Tage</dt><dd>1–3</dd></div>
            <div><dt>cm Gesamtlänge max.</dt><dd data-count="{{ $maxBlade }}">{{ $maxBlade }}</dd></div>
        </dl>
    </x-page.hero>

    @include('pages.home.04-finder')

    <x-page.section title="Die Kurse im Detail" kicker="Kursformate" intro="Dauer, Projekt und Preis je Kurs – Ablauf, Leistungen und Fragen stehen auf der jeweiligen Kursseite." sunken>
        <div class="course-details">
            @foreach ($courses as $course)
                <article class="course-detail course--{{ $course->category }} reveal" id="{{ \Illuminate\Support\Str::slug($course->name) }}">
                    <x-ui.type-tile class="course-detail__tile" :title="$course->name" :label="$course->categoryLabel()" :meta="$course->duration_label" />
                    <div class="course-detail__body">
                        <h3><a href="{{ $course->url() }}">{{ $course->name }}</a></h3>
                        <p class="muted">{{ $course->project }}</p>
                        <dl class="spec mt-3">
                            <div class="spec__row"><dt>Kategorie</dt><dd>{{ $course->categoryLabel() }}</dd></div>
                            <div class="spec__row"><dt>Dauer</dt><dd>@if($course->duration_label){{ $course->duration_label }}@else<x-ui.missing>Dauer {{ $course->name }}</x-ui.missing>@endif</dd></div>
                            @if ($course->total_length_cm)
                                <div class="spec__row"><dt>Gesamtlänge</dt><dd><x-ui.dimension :cm="$course->total_length_cm" :max="$maxBlade" /></dd></div>
                            @endif
                            <div class="spec__row"><dt>Preis</dt><dd><x-ui.price :amount="$course->price_from" :from="true" /> @if($course->price_from)<span class="muted">pro Person</span>@endif</dd></div>
                        </dl>
                        <p class="mt-3"><a class="link-arrow" href="{{ $course->url() }}">Alles zum {{ $course->name }} <span aria-hidden="true">→</span></a></p>
                        <div class="btn-row mt-4">
                            @if ($course->price_from)
                                <x-ui.button :href="$course->bookingUrl()">Termin wählen<span class="visually-hidden"> für {{ $course->name }}</span></x-ui.button>
                                <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="violet">Als Gutschein</x-ui.button>
                            @else
                                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="brown" :arrow="false">Anfragen: {{ $c['phone']['display'] }}</x-ui.button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="mt-5"><a class="link-arrow" href="/schmiedekurse/vergleich/">Alle Kurse in einer Tabelle vergleichen <span aria-hidden="true">→</span></a></p>
    </x-page.section>

    <x-page.section title="Gut zu wissen vor der Buchung" kicker="Buchungsregeln">
        <ul class="rule-cards" role="list">
            @foreach ($c['booking_rules'] as $i => $rule)
                <li class="reveal {{ $i ? 'reveal-delay-'.$i : '' }}"><x-ui.icon :name="['online', 'swap', 'gift'][$i] ?? 'online'" /><p>{{ $rule }}</p></li>
            @endforeach
        </ul>
        <p class="mt-5"><a class="link-arrow" href="/kursbedingungen/">Kursbedingungen lesen <span aria-hidden="true">→</span></a></p>
    </x-page.section>

    @include('pages.home.10-process')

    <x-page.section title="Häufige Fragen zu den Schmiedekursen" kicker="FAQ" sunken>
        @include('pages.partials.faq')
    </x-page.section>

    @include('pages.partials.subpages', ['prefix' => '/schmiedekurse/', 'title' => 'Vergleich, Region, Erfahrungen', 'kicker' => 'Schmiedekurse', 'sunken' => true])

    <x-page.cta title="Noch unsicher, welcher Kurs passt?" />
</x-layouts.site>
