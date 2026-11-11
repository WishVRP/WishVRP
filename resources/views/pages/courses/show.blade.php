@php
    $content = json_decode(file_get_contents(resource_path('content/schmiedekurse__'.$course->slug.'.json')), true);
    $skip = ['', 'Das ist vorgesehen.', 'Ein eigenes Projekt'];
    $sections = collect($content['sections'])->reject(fn ($s) => in_array($s['h'], $skip, true));
    $others = $courses->reject(fn ($c) => $c->is($course));
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$page['h1'] ?? $content['h1']" :kicker="$course->categoryLabel().' · '.$course->duration_label"
        :tile="$course->name" :tileLabel="$course->categoryLabel()" class="course--{{ $course->category }}"
        :lead="implode(' ', array_slice($content['lead'], -1))">
        <x-slot:actions>
            @if ($course->price_from)
                <x-ui.button :href="$course->bookingUrl()" variant="straw" size="lg">Termin wählen</x-ui.button>
                <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="secondary" size="lg">Als Gutschein verschenken</x-ui.button>
            @else
                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">Anfragen: {{ $c['phone']['display'] }}</x-ui.button>
                <x-ui.button href="/erlebnisse/teamevent/" variant="secondary" size="lg">Teamevent</x-ui.button>
            @endif
        </x-slot:actions>
    </x-page.hero>

    <section class="block course-facts course--{{ $course->category }}" aria-labelledby="facts-h">
        <div class="container split">
            <div class="reveal">
                <x-ui.kicker>Auf einen Blick</x-ui.kicker>
                <h2 id="facts-h" class="h-section">{{ $course->name }}</h2>
                <dl class="spec mt-5">
                    <div class="spec__row"><dt>Dauer</dt><dd>{{ $course->duration_label }}@if($course->hours_label) · {{ $course->hours_label }}@endif</dd></div>
                    <div class="spec__row"><dt>Dein Projekt</dt><dd>{{ $course->project }}</dd></div>
                    @if ($course->total_length_cm)
                        <div class="spec__row"><dt>Gesamtlänge</dt><dd><x-ui.dimension :cm="$course->total_length_cm" :max="$maxBlade" /></dd></div>
                    @endif
                    <div class="spec__row"><dt>Angebot</dt><dd><x-ui.price :amount="$course->price_from" :from="true" /> @if($course->price_from)<span class="muted">pro Person</span>@endif</dd></div>
                </dl>
                <p class="tech muted mt-4">Verbindlich sind Preis, Leistungen und Treffpunkt beim ausgewählten Termin · <x-ui.tax-note /></p>
            </div>
            <div class="course-facts__incl reveal reveal-delay-1">
                <h3>Das ist vorgesehen</h3>
                <ul class="check-list mt-4" role="list">
                    @foreach ($course->inclusions ?? [] as $inc)<li>{{ $inc }}</li>@endforeach
                </ul>
            </div>
        </div>
    </section>

    <article class="block block--sunken article" aria-label="Kursbeschreibung">
        <div class="container article__grid">
            <div></div>
            <div class="prose article__body">
                @foreach ($sections as $i => $s)
                    <h2>{{ $s['h'] }}</h2>
                    @foreach ($s['items'] as $item)
                        @if ($item['t'] === 'h3')<h3>{{ $item['x'] }}</h3>@elseif($item['t'] === 'li')<ul><li>{!! \App\Support\Inline::html($item['x']) !!}</li></ul>@else<p>{!! \App\Support\Inline::html($item['x']) !!}</p>@endif
                    @endforeach
                @endforeach
                @if (count($content['faq']))
                    <h2 id="faq">Deine Fragen, kurz beantwortet</h2>
                    @include('pages.partials.faq', ['faqs' => $content['faq']])
                @endif
                <div class="btn-row mt-6">
                    @if ($course->price_from)
                        <x-ui.button :href="$course->bookingUrl()" size="lg">{{ $course->name }} buchen</x-ui.button>
                    @endif
                    <a class="link-arrow" href="/schmiedekurse/vergleich/">Alle Kurse vergleichen <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </article>

    <x-page.section title="Weitere Kursformate" kicker="Vergleichen">
        <div class="path-cards path-cards--three">
            @foreach ($others->take(3) as $o)
                <a class="path-card course--{{ $o->category }} reveal" href="{{ $o->url() }}">
                    <p class="path-card__label">{{ $o->categoryLabel() }} · {{ $o->duration_label }}</p>
                    <h3>{{ $o->name }}</h3>
                    <p>{{ $o->project }}</p>
                    <span class="link-arrow"><x-ui.price :amount="$o->price_from" :from="true" /> <span aria-hidden="true">→</span></span>
                </a>
            @endforeach
        </div>
    </x-page.section>

    <x-page.cta title="Fragen zum {{ $course->name }}?" />
</x-layouts.site>
