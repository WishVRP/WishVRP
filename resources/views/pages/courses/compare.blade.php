<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmiedekurse im Vergleich" kicker="Kursvergleich" tile="1 – 3 Tage" tileLabel="{{ $courses->count() }} Kursformate"
        lead="Alle Kurse nebeneinander: Dauer, maximale Gesamtlänge und Einstiegspreis pro Person. Details zu jedem Kurs findest du in der Kursübersicht.">
        <x-slot:actions>
            <x-ui.button href="/schmiedekurse/buchen/" variant="straw" size="lg">Termin buchen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="Alle Kurse auf einen Blick" kicker="Tabelle">
        <div class="table-scroll" tabindex="0" role="region" aria-label="Kursvergleich, seitlich scrollbar">
            <table class="compare">
                <thead>
                    <tr>
                        <th scope="col">Kurs</th>
                        <th scope="col">Kategorie</th>
                        <th scope="col">Dauer</th>
                        <th scope="col">Gesamtlänge</th>
                        <th scope="col">Preis</th>
                        <th scope="col"><span class="visually-hidden">Aktion</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr class="course--{{ $course->category }}">
                            <th scope="row"><a href="{{ $course->url() }}">{{ $course->name }}</a></th>
                            <td><span class="compare__cat">{{ $course->categoryLabel() }}</span></td>
                            <td>@if($course->duration_label){{ $course->duration_label }}@else<x-ui.missing>Dauer</x-ui.missing>@endif</td>
                            <td>@if($course->total_length_cm)bis {{ $course->total_length_cm }} cm @else<span class="muted">–</span>@endif</td>
                            <td><x-ui.price :amount="$course->price_from" :from="true" /></td>
                            <td>
                                @if ($course->price_from)
                                    <a class="link-arrow" href="{{ $course->bookingUrl() }}">Buchen<span class="visually-hidden"> {{ $course->name }}</span> <span aria-hidden="true">→</span></a>
                                @else
                                    <a class="link-arrow" href="tel:{{ $c['phone']['e164'] }}">Anfragen <span aria-hidden="true">→</span></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="tech muted mt-4">„ab“-Preise pro Person · <x-ui.tax-note /> · Gesamtlänge (inkl. Griff) nur angegeben, wo sie bekannt ist.</p>
    </x-page.section>

    <x-page.section title="Welcher Kurs für wen?" kicker="Orientierung" sunken>
        <div class="path-cards">
            @foreach (\App\Models\Course::CATEGORIES as $key => $label)
                @php $group = $courses->where('category', $key); @endphp
                @if ($group->isNotEmpty())
                    <div class="path-card course--{{ $key }} reveal">
                        <p class="path-card__label">{{ $label }}</p>
                        <ul role="list">
                            @foreach ($group as $course)
                                <li><a href="{{ $course->url() }}">{{ $course->name }}</a> <span class="muted">{{ $course->duration_label }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    </x-page.section>

    <x-page.section title="Häufige Fragen" kicker="FAQ">
        @include('pages.partials.faq')
    </x-page.section>

    <x-page.cta />
</x-layouts.site>
