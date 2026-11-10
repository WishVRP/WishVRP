@php $priced = $courses->whereNotNull('price_from'); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Ratgeber Schmiedekurs" kicker="Wissen" tile="Kosten & Vorbereitung" tileLabel="Ratgeber"
        lead="Antworten vor der Buchung: was ein Schmiedekurs kostet und wie du dich vorbereitest." />

    <article class="block" id="kosten" aria-labelledby="kosten-h">
        <div class="container prose-grid">
            <div class="prose">
                <x-ui.kicker>Kosten</x-ui.kicker>
                <h2 id="kosten-h" class="h-section">Was kostet ein Schmiedekurs?</h2>
                <p class="lead">Die Preise der Messermanufaktur Ulbrich hängen vom Kursformat ab: von {{ number_format($priced->min('price_from'), 0, ',', '.') }} € für den eintägigen Grundlagenkurs bis {{ number_format($priced->max('price_from'), 0, ',', '.') }} € für den Damast Premium-Kurs, jeweils pro Person.</p>
                <p>Damastkurse dauern länger und es entstehen größere Klingen – der Damast Premium-Kurs erlaubt bis zu {{ $maxBlade }} cm Klingenlänge. Den Manager-Schmiedekurs für Teams kalkuliert {{ $c['owner'] }} auf Anfrage.</p>
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
            <p class="lead">Die wichtigsten Regeln zur Buchung kennst du schon: Kurse werden online gebucht, Umbuchen geht bis 7 Tage vor dem Termin.</p>
            <p><x-ui.missing label="Inhalt folgt">Artikel „Vorbereitung“ von der Live-Seite übernehmen (Kleidung, Mitbringen, Verpflegung)</x-ui.missing></p>
        </div>
    </article>

    <x-page.section title="Häufige Fragen" kicker="FAQ">
        @include('pages.partials.faq')
    </x-page.section>

    <x-page.cta />
</x-layouts.site>
