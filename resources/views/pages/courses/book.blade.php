@php
    $selected = $courses->first(fn ($co) => in_array(request('kurs'), array_filter([$co->booking_key, \Illuminate\Support\Str::slug($co->name)]), true));
    $persons = max(1, min(12, (int) request('teilnehmer', 1)));
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmiedekurs buchen" kicker="Buchung" tone="straw"
        lead="Kurs wählen, Teilnehmerzahl angeben, Termin aussuchen. Der Gesamtpreis wird direkt berechnet." />

    <section class="block" aria-label="Buchungsformular">
        <div class="container booking">
            <form class="booking__form" data-booking novalidate>
                <fieldset class="booking__step">
                    <legend><span class="booking__num">1</span> Kurs wählen</legend>
                    <div class="booking__courses">
                        @foreach ($courses as $course)
                            <label class="booking-option course--{{ $course->category }}">
                                <input type="radio" name="kurs" value="{{ \Illuminate\Support\Str::slug($course->name) }}"
                                       data-name="{{ $course->name }}" data-price="{{ $course->price_from }}" data-duration="{{ $course->duration_label }}"
                                       @checked($selected ? $selected->is($course) : $loop->first)>
                                <span class="booking-option__body">
                                    <span class="booking-option__cat">{{ $course->categoryLabel() }}</span>
                                    <span class="booking-option__name">{{ $course->name }}</span>
                                    <span class="booking-option__meta">{{ $course->duration_label ?? 'Dauer folgt' }} · <x-ui.price :amount="$course->price_from" :from="true" /></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="booking__step">
                    <legend><span class="booking__num">2</span> Teilnehmer</legend>
                    <div class="stepper">
                        <button type="button" class="stepper__btn" data-step="-1" aria-label="Eine Person weniger">−</button>
                        <input type="number" name="teilnehmer" min="1" max="12" value="{{ $persons }}" inputmode="numeric" aria-label="Anzahl Teilnehmer" data-persons>
                        <button type="button" class="stepper__btn" data-step="1" aria-label="Eine Person mehr">+</button>
                    </div>
                    <x-ui.missing silent>Maximale Teilnehmerzahl je Kurs</x-ui.missing>
                </fieldset>

                <fieldset class="booking__step">
                    <legend><span class="booking__num">3</span> Termin</legend>
                    @if ($dates->isNotEmpty())
                        <ul class="booking__dates" role="list">
                            @foreach ($dates as $date)
                                <li data-date-course="{{ \Illuminate\Support\Str::slug($date->course->name) }}">
                                    <label class="date-option">
                                        <input type="radio" name="termin" value="{{ $date->id }}">
                                        <span><b>{{ $date->starts_on->locale('de')->isoFormat('dd, D. MMMM YYYY') }}</b> · {{ $date->seatsLeftLabel() }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="booking__empty">
                            <x-ui.icon name="calendar" />
                            <p>Die Kurstermine werden gerade aus dem Buchungskalender übernommen. Freie Termine nennt dir {{ $c['owner'] }} am Telefon.</p>
                        </div>
                        <x-ui.missing silent ask="aus dem Buchungssystem übernehmen">Kurstermine</x-ui.missing>
                    @endif
                </fieldset>
            </form>

            <aside class="booking__summary" aria-labelledby="summary-title" data-booking-summary>
                <h2 id="summary-title">Deine Auswahl</h2>
                <dl class="spec">
                    <div class="spec__row"><dt>Kurs</dt><dd data-sum-name>{{ ($selected ?? $courses->first())?->name }}</dd></div>
                    <div class="spec__row"><dt>Dauer</dt><dd data-sum-duration>{{ ($selected ?? $courses->first())?->duration_label ?? '–' }}</dd></div>
                    <div class="spec__row"><dt>Teilnehmer</dt><dd data-sum-persons>{{ $persons }}</dd></div>
                    <div class="spec__row"><dt>Preis p. P.</dt><dd data-sum-price>–</dd></div>
                </dl>
                <p class="booking__total"><span>Gesamt</span><strong data-sum-total>–</strong></p>
                <p class="tech muted"><x-ui.tax-note /></p>
                <button type="button" class="btn btn--primary btn--lg booking__pay" disabled aria-describedby="pay-note">Weiter zur Zahlung</button>
                <x-page.note id="pay-note" title="Online-Zahlung folgt">
                    Die Bezahlung über einen Zahlungsanbieter wird vor dem Start eingerichtet. Bis dahin buchst du telefonisch:
                    <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.
                </x-page.note>
                <ul class="booking__rules" role="list">
                    @foreach ($c['booking_rules'] as $rule)<li>{{ $rule }}</li>@endforeach
                </ul>
                <a class="link-arrow" href="/kursbedingungen/">Kursbedingungen <span aria-hidden="true">→</span></a>
            </aside>
        </div>
    </section>
</x-layouts.site>
