@php $priced = $courses->whereNotNull('price_from'); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Ratgeber Schmiedekurs" kicker="Wissen" tile="Kosten & Vorbereitung" tileLabel="Ratgeber"
        lead="Antworten vor der Buchung: was ein Schmiedekurs kostet und wie du dich vorbereitest." />

    <x-page.section title="Alle Artikel" kicker="Ratgeber">
        <div class="path-cards path-cards--three">
            <a class="path-card course--einstieg reveal" href="#kosten"><p class="path-card__label">Kosten</p><h3>Was kostet ein Schmiedekurs?</h3><p>Alle Kurspreise pro Person im Überblick.</p><span class="link-arrow">Lesen <span aria-hidden="true">→</span></span></a>
            <a class="path-card course--damast reveal reveal-delay-1" href="/ratgeber/damastmesser/"><p class="path-card__label">Material</p><h3>Damaststahl erklärt</h3><p>Herstellung, Muster und Pflege einer Damastklinge.</p><span class="link-arrow">Lesen <span aria-hidden="true">→</span></span></a>
            <a class="path-card course--klinge reveal reveal-delay-2" href="/ratgeber/messer-pflege/"><p class="path-card__label">Pflege</p><h3>Messer pflegen</h3><p>Reinigen, schärfen und richtig lagern.</p><span class="link-arrow">Lesen <span aria-hidden="true">→</span></span></a>
        </div>
    </x-page.section>

    <article class="block" id="kosten" aria-labelledby="kosten-h">
        <div class="container prose-grid">
            <div class="prose">
                <x-ui.kicker>Kosten</x-ui.kicker>
                <h2 id="kosten-h" class="h-section">Was kostet ein Schmiedekurs?</h2>
                <p class="lead">Die Preise der Messermanufaktur Ulbrich hängen vom Kursformat ab: von {{ number_format($priced->min('price_from'), 0, ',', '.') }} € für den eintägigen Grundlagenkurs bis {{ number_format($priced->max('price_from'), 0, ',', '.') }} € für den Damast Premium-Kurs, jeweils pro Person.</p>
                <p>Damastkurse dauern länger und es entstehen größere Messer – der Damast Premium-Kurs ermöglicht ein Messer bis {{ $maxBlade }} cm Gesamtlänge. Den Manager-Schmiedekurs für Teams kalkuliert {{ $c['owner'] }} auf Anfrage.</p>
            </div>
            <div class="table-scroll" tabindex="0" role="region" aria-label="Preisübersicht">
                <table class="compare compare--compact">
                    <thead><tr><th scope="col">Kurs</th><th scope="col">Dauer</th><th scope="col">Preis p. P.</th></tr></thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr class="course--{{ $course->category }}"><th scope="row">{{ $course->name }}</th><td>{{ $course->duration_label ?? '–' }}</td><td><x-ui.price :amount="$course->price_from" :from="true" /></td></tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="tech muted mt-3"><x-ui.tax-note /></p>
            </div>
        </div>
    </article>

    <article class="block block--sunken" id="vorbereitung" aria-labelledby="vorb-h">
        <div class="container prose">
            <x-ui.kicker>Vorbereitung</x-ui.kicker>
            <h2 id="vorb-h" class="h-section">So bereitest du dich auf deinen Schmiedekurs vor</h2>
            <p class="lead">Die wichtigsten Regeln zur Buchung kennst du schon: Kurse werden online gebucht, zwei Umbuchungen sind bis 48 Stunden vor dem Termin kostenlos.</p>
            @php $ci = config('ulbrich.course_info'); @endphp
            <ul>
                <li><strong>Kleidung und Schuhe:</strong> {{ $ci['clothing'] }}</li>
                <li><strong>Mitbringen und Verpflegung:</strong> {{ $ci['bring'] }}</li>
                <li><strong>Parken:</strong> {{ $ci['parking'] }}</li>
                <li><strong>Bus und Bahn:</strong> {{ $ci['transit'] }}</li>
                <li><strong>Beginn:</strong> {{ config('ulbrich.booking.course_start_time') }} Uhr in der Werkstatt, {{ $c['address']['street'] }}, {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }} ({{ $c['address']['district'] }}).</li>
            </ul>
            <p>Alles Weitere im Ratgeber <a href="/ratgeber/schmiedekurs-vorbereitung/">Schmiedekurs vorbereiten</a>. Drei Tage vor deinem Kurs bekommst du diese Infos außerdem per E-Mail.</p>
        </div>
    </article>

    <x-page.section title="Häufige Fragen" kicker="FAQ">
        @include('pages.partials.faq')
    </x-page.section>

    @include('pages.partials.subpages', ['prefix' => '/ratgeber/', 'title' => 'Alle Ratgeber-Artikel', 'kicker' => 'Ratgeber', 'sunken' => true])

    <x-page.cta />
</x-layouts.site>
