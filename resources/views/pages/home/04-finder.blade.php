<section class="block finder" id="kursfinder" aria-labelledby="finder-title">
    <div class="container">
        <div class="block-head">
            <div>
                <x-ui.kicker>Kurs-Finder</x-ui.kicker>
                <h2 id="finder-title" class="h-section">Welcher Schmiedekurs passt zu dir?</h2>
            </div>
            <p class="block-head__intro">Dauer, Klingenlänge und Einstiegspreis aller Kursformate. Ablauf, Ergebnis und Voraussetzungen stehen auf der jeweiligen Kursseite.</p>
        </div>

        <fieldset class="finder__filters" data-finder-filters>
            <legend>Was suchst du?</legend>
            <label class="chip"><input type="radio" name="kursart" value="alle" checked><span>Alle Kurse</span></label>
            @foreach (\App\Models\Course::CATEGORIES as $key => $label)
                @if ($courses->contains('category', $key))
                    <label class="chip"><input type="radio" name="kursart" value="{{ $key }}"><span>{{ $label }}</span></label>
                @endif
            @endforeach
        </fieldset>

        <ul class="course-grid" role="list" data-finder-list>
            @foreach ($courses as $course)
                <li class="course course--{{ $course->category }} reveal" data-category="{{ $course->category }}">
                    <x-ui.media class="course__media" :foto="['einstieg' => 'werkstatt-amboss', 'klinge' => 'messer-kuechenmesser', 'damast' => 'damast-detail', 'gruppe' => 'werkstatt-halle'][$course->category] ?? 'werkstatt-amboss'" format="cinema" decorative sizes="(min-width: 64rem) 25vw, (min-width: 40rem) 45vw, 90vw" />
                    <div class="course__top">
                        <span class="course__cat">{{ $course->categoryLabel() }}</span>
                        <span>{{ $course->duration_label ?? '' }}</span>
                    </div>
                    <h3>{{ $course->name }}</h3>
                    <dl class="course__specs">
                        <div>
                            <dt>Dauer</dt>
                            <dd>@if($course->duration_label){{ $course->duration_label }}@else<x-ui.missing>Dauer {{ $course->name }}</x-ui.missing>@endif</dd>
                        </div>
                        @if ($course->blade_length_cm)
                            <div><dt>Klinge</dt><dd><x-ui.dimension :cm="$course->blade_length_cm" :max="$maxBlade" /></dd></div>
                        @endif
                    </dl>
                    <p class="course__price">
                        <x-ui.price :amount="$course->price_from" :from="true" />
                        <small>{{ $course->price_from ? 'pro Person' : 'Preis und Termin nach Absprache' }}</small>
                    </p>
                    <div class="course__actions">
                        <a class="link-arrow" href="{{ $course->url() }}">Details<span class="visually-hidden"> zum {{ $course->name }}</span> <span aria-hidden="true">→</span></a>
                        @if ($course->price_from)
                            <x-ui.button href="{{ $course->bookingUrl() }}">Termin wählen<span class="visually-hidden"> für {{ $course->name }}</span></x-ui.button>
                        @else
                            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="brown" :arrow="false">Anfragen<span class="visually-hidden">: {{ $course->name }}</span></x-ui.button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="finder__foot">
            <span data-finder-count aria-live="polite">{{ $courses->count() }} Kurse</span>
            <span>„ab“-Preise pro Person · <x-ui.tax-note /></span>
            <a class="link-arrow" href="/schmiedekurse/vergleich/">Ausführlicher Kursvergleich <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>
