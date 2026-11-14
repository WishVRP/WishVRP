{{--
    Buchung: Kurs → freier Tag im Kalender (ein Kurs pro Tag, bis 5 Jahre im Voraus) → Personen → Warenkorb → Kasse (Überweisung).
--}}
@php
    $calendar = \App\Support\CourseCalendar::class;
    $bookable = $courses->filter(fn ($co) => $calendar::bookable($co))->values();
    $selected = $bookable->first(fn ($co) => in_array(request('kurs'), array_filter([$co->booking_key, \Illuminate\Support\Str::slug($co->name), $co->slug]), true)) ?? $bookable->first();
    $max = (int) config('ulbrich.booking.max_persons');
    $persons = max(1, min($max, (int) old('teilnehmer', request('teilnehmer', 1))));
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmiedekurs buchen" kicker="Buchung" tone="straw"
        lead="Kurs wählen, freien Tag im Kalender aussuchen, Personen angeben und in den Warenkorb legen. Pro Tag findet nur ein Kurs statt – dein Tag gehört dir." />

    <section class="block" aria-label="Buchungsformular">
        <div class="container booking">
            @if (session('order_ok'))
                <div class="form-success" id="buchung-gesendet" role="status">
                    <h2>Danke – deine Buchungsanfrage ist angekommen.</h2>
                    <p>Vorgangsnummer <strong>{{ session('order_ok') }}</strong>. Du bekommst eine Bestätigung per E-Mail und von uns die Zahlungsdaten. Dein Tag ist für dich vorgemerkt.</p>
                </div>
            @endif
            <form class="booking__form" id="booking-form" method="post" action="{{ route('cart.add') }}" data-booking
                  data-availability="/schmiedekurse/verfuegbarkeit/" data-first="{{ $calendar::first()->toDateString() }}" data-last="{{ $calendar::last()->toDateString() }}">
                @csrf
                <input type="hidden" name="type" value="kurs">

                <fieldset class="booking__step">
                    <legend><span class="booking__num">1</span> Kurs wählen</legend>
                    <div class="booking__courses">
                        @foreach ($bookable as $course)
                            <label class="booking-option course--{{ $course->category }}">
                                <input type="radio" name="kurs" value="{{ $course->slug }}" data-name="{{ $course->name }}" data-price="{{ $course->price_from }}"
                                       data-duration="{{ $course->duration_label }}" data-days="{{ $calendar::days($course) }}" @checked(old('kurs', $selected?->slug) === $course->slug)>
                                <span class="booking-option__body">
                                    <span class="booking-option__cat">{{ $course->categoryLabel() }}</span>
                                    <span class="booking-option__name">{{ $course->name }}</span>
                                    <span class="booking-option__meta">{{ $course->duration_label }} · <x-ui.price :amount="$course->price_from" /> p. P.</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="tech muted mt-3">Manager-Schmiedekurs und Teamevents planen wir individuell: <a href="/erlebnisse/teamevent/">anfragen</a>.</p>
                    @error('kurs')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="booking__step">
                    <legend><span class="booking__num">2</span> Tag wählen</legend>
                    @if ($calendar::first()->gt(now()->startOfDay()->addDays((int) config('ulbrich.booking.min_days_ahead'))))
                        <p class="tech booking__opens">Kurstage sind ab <b>{{ $calendar::first()->locale('de')->isoFormat('D. MMMM YYYY') }}</b> buchbar – du kannst dir deinen Tag schon jetzt sichern.</p>
                    @endif
                    <div class="cal" data-cal>
                        <div class="cal__head">
                            <button type="button" class="cal__nav" data-cal-prev aria-label="Vorheriger Monat">‹</button>
                            <p class="cal__title" data-cal-title aria-live="polite">…</p>
                            <button type="button" class="cal__nav" data-cal-next aria-label="Nächster Monat">›</button>
                        </div>
                        <div class="cal__weekdays" aria-hidden="true"><span>Mo</span><span>Di</span><span>Mi</span><span>Do</span><span>Fr</span><span>Sa</span><span>So</span></div>
                        <div class="cal__grid" data-cal-grid role="grid" aria-label="Kalender"></div>
                        <p class="cal__legend"><span class="cal__dot cal__dot--free"></span> frei <span class="cal__dot cal__dot--busy"></span> belegt</p>
                        <p class="cal__chosen" data-cal-chosen>Noch kein Tag gewählt.</p>
                    </div>
                    <input type="hidden" name="datum" value="{{ old('datum') }}" data-cal-input>
                    <noscript><label class="field mt-4"><span class="field__label">Wunschtag (TT.MM.JJJJ)</span><input type="date" name="datum" min="{{ $calendar::first()->toDateString() }}"></label></noscript>
                    @error('datum')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                </fieldset>

                <fieldset class="booking__step">
                    <legend><span class="booking__num">3</span> Teilnehmer</legend>
                    <div class="stepper">
                        <button type="button" class="stepper__btn" data-step="-1" aria-label="Eine Person weniger">−</button>
                        <input type="number" name="teilnehmer" min="1" max="{{ $max }}" value="{{ $persons }}" inputmode="numeric" aria-label="Anzahl Teilnehmer" data-persons data-max="{{ $max }}">
                        <button type="button" class="stepper__btn" data-step="1" aria-label="Eine Person mehr">+</button>
                    </div>
                    <x-ui.missing silent>Maximale Teilnehmerzahl je Kurs (vorläufig {{ $max }})</x-ui.missing>
                </fieldset>

            </form>

            <aside class="booking__summary" aria-labelledby="summary-title" data-booking-summary>
                <h2 id="summary-title">Deine Auswahl</h2>
                <dl class="spec">
                    <div class="spec__row"><dt>Kurs</dt><dd data-sum-name>{{ $selected?->name }}</dd></div>
                    <div class="spec__row"><dt>Dauer</dt><dd data-sum-duration>{{ $selected?->duration_label ?? '–' }}</dd></div>
                    <div class="spec__row"><dt>Tag</dt><dd data-sum-date>–</dd></div>
                    <div class="spec__row"><dt>Teilnehmer</dt><dd data-sum-persons>{{ $persons }}</dd></div>
                    <div class="spec__row"><dt>Preis p. P.</dt><dd data-sum-price>–</dd></div>
                </dl>
                <p class="booking__total"><span>Gesamt</span><strong data-sum-total>–</strong></p>
                <p class="tech muted">Gutschein- und Rabattcodes löst du an der Kasse ein. <x-ui.tax-note /></p>
                <button type="submit" form="booking-form" class="btn btn--primary btn--lg booking__pay">In den Warenkorb</button>
                <x-page.note id="pay-note" title="So geht es weiter">
                    Im Warenkorb kannst du weitere Kurse oder Messer dazulegen. An der Kasse gibst du deine Daten ein und zahlst per Überweisung – die Bankdaten kommen per E-Mail, dein Tag ist ab der Bestellung für dich vorgemerkt. Online-Zahlung folgt bald. Lieber telefonisch? <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>
                </x-page.note>
                <ul class="booking__rules" role="list">
                    @foreach ($c['booking_rules'] as $rule)<li>{{ $rule }}</li>@endforeach
                </ul>
                <a class="link-arrow" href="/kursbedingungen/">Kursbedingungen <span aria-hidden="true">→</span></a>
            </aside>
        </div>
    </section>
</x-layouts.site>
