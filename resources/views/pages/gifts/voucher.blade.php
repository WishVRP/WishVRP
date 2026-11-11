@php $first = $courses->firstWhere('price_from', '!=', null); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schmiedekurs-Gutschein" kicker="Gutschein verschenken" tone="violet"
        lead="Einen Schmiedekurs verschenken: Die beschenkte Person sucht sich Kurs und Termin später selbst aus und bucht online.">
        <x-slot:actions>
            <x-ui.button href="#gutschein-gestalten" variant="violet" size="lg">Gutschein zusammenstellen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <section class="block" id="gutschein-gestalten" aria-labelledby="vc-h">
        <div class="container voucher-builder" data-voucher>
            <div class="voucher-builder__form">
                <x-ui.kicker color="violet">Zusammenstellen</x-ui.kicker>
                <h2 id="vc-h" class="h-section">Für welchen Kurs?</h2>
                <fieldset class="voucher-builder__choices">
                    <legend class="visually-hidden">Kurs für den Gutschein</legend>
                    @foreach ($courses->whereNotNull('price_from') as $course)
                        <label class="booking-option course--{{ $course->category }}">
                            <input type="radio" name="gutschein-kurs" value="{{ \Illuminate\Support\Str::slug($course->name) }}" data-name="{{ $course->name }}" data-price="{{ $course->price_from }}" data-duration="{{ $course->duration_label }}" @checked($course->is($first))>
                            <span class="booking-option__body">
                                <span class="booking-option__name">{{ $course->name }}</span>
                                <span class="booking-option__meta">{{ $course->duration_label ?? 'Dauer folgt' }} · <x-ui.price :amount="$course->price_from" :from="true" /> p. P.</span>
                            </span>
                        </label>
                    @endforeach
                    <label class="booking-option">
                        <input type="radio" name="gutschein-kurs" value="frei" data-name="Kurs nach Wahl" data-price="" data-duration="">
                        <span class="booking-option__body">
                            <span class="booking-option__name">Kurs nach Wahl</span>
                            <span class="booking-option__meta">Die beschenkte Person entscheidet</span>
                        </span>
                    </label>
                </fieldset>
                <label class="field mt-5">
                    <span class="field__label">Für (erscheint auf der Vorschau)</span>
                    <input type="text" maxlength="40" placeholder="Name der beschenkten Person" data-voucher-name autocomplete="off">
                </label>
                <x-ui.missing silent>Gutscheinwerte, Gültigkeit, Versand (PDF/Post), Teileinlösung</x-ui.missing>
            </div>

            <div class="voucher-builder__preview">
                <div class="voucher voucher--big" aria-live="polite">
                    <p class="voucher__kind">GUTSCHEIN</p>
                    <p class="voucher__title" data-vp-course>{{ $first?->name }}</p>
                    <p class="voucher__meta" data-vp-meta>{{ $first?->duration_label }}</p>
                    <p class="voucher__for">für <span data-vp-name>…</span></p>
                    <p class="voucher__brand">{{ $c['name'] }} · {{ $c['place_label'] }}</p>
                    <span class="voucher__stamp">Nº<br>___</span>
                </div>
                <p class="voucher-builder__price">Kurspreis <strong data-vp-price><x-ui.price :amount="$first?->price_from" :from="true" /></strong> <span class="muted">p. P. · <x-ui.tax-note /></span></p>
                <button type="button" class="btn btn--violet btn--lg" disabled aria-describedby="vc-note">In den Warenkorb</button>
                <x-page.note id="vc-note" title="Online-Kauf folgt">
                    Bis der Gutschein-Shop freigeschaltet ist, bestellst du telefonisch: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.
                </x-page.note>
            </div>
        </div>
    </section>

    <x-page.section title="So funktioniert der Gutschein" kicker="Ablauf" kicker-color="violet" sunken>
        <ol class="big-steps" role="list">
            <li class="reveal"><span class="big-steps__num">01</span><h3>Gutschein bestellen</h3><p>Aktuell telefonisch, bald direkt hier online.</p></li>
            <li class="reveal reveal-delay-1"><span class="big-steps__num">02</span><h3>Verschenken</h3><p>Der Gutschein reserviert noch keinen Kursplatz.</p></li>
            <li class="reveal reveal-delay-2"><span class="big-steps__num">03</span><h3>Termin selbst wählen</h3><p>Die beschenkte Person bucht Kurs und Termin online.</p></li>
        </ol>
    </x-page.section>

    <x-page.section title="Fragen zum Gutschein" kicker="FAQ" kicker-color="violet">
        @include('pages.partials.faq', ['faqs' => \App\Support\Faqs::voucher()])
    </x-page.section>

    <x-page.cta title="Gutschein telefonisch bestellen" />
</x-layouts.site>
