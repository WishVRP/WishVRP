@php $press = collect($c['press'])->sortByDesc('date'); $quote = $press->firstWhere('quote'); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Presse & Erwähnungen" kicker="Originalquellen" tile="{{ $press->count() }} Berichte" tileLabel="Presse & Erwähnungen"
        lead="Presseberichte, Interviews und regionale Veröffentlichungen über Andreas Ulbrich und die Messermanufaktur Ulbrich – mit Herausgeber, Datum und einer kurzen Einordnung." />

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

    <x-page.section title="Originalveröffentlichungen" kicker="Pressespiegel" intro="Die Beiträge bleiben auf den Websites ihrer Herausgeber; manche verlangen eine Anmeldung oder ein Abo. Angaben zu früheren Werkstätten beziehen sich auf das jeweilige Veröffentlichungsdatum.">
        <ul class="press-cards" role="list">
            @foreach ($press as $item)
                <li class="press-card reveal">
                    <p class="press-card__meta"><span>{{ $item['kind'] }}</span> · {{ $item['outlet'] }} · {{ $item['date_label'] ?? \Illuminate\Support\Carbon::parse($item['date'])->format('d.m.Y') }}</p>
                    <h3 class="press-card__title">{{ $item['title'] }}</h3>
                    <p class="press-card__text">{{ $item['summary'] }}</p>
                    <a class="link-arrow" href="{{ $item['url'] }}" rel="noopener" target="_blank">Originalbeitrag öffnen<span class="visually-hidden">: {{ $item['title'] }}</span> <span aria-hidden="true">↗</span></a>
                </li>
            @endforeach
        </ul>
        <p class="mt-6 muted">Die Veröffentlichungen belegen keine bestimmte Verfügbarkeit, keinen Kursplatz und keine Bewertung des heutigen Angebots. Presseanfragen: <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a> · <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
    </x-page.section>

    <x-page.cta />
</x-layouts.site>
