{{-- Kasse: Kontaktdaten, Lieferung (nur bei Messern), Rabatt-/Gutscheincode, Zahlung per Überweisung. Online-Zahlung folgt. --}}
@php
    $done = session('checkout_done');
    $lines = $done ? [] : \App\Support\Cart::lines();
    $hasProducts = collect($lines)->contains('type', 'product');
    $hasCourses = collect($lines)->contains('type', 'kurs');
    $subtotal = \App\Support\Cart::subtotal($lines);
    $problems = collect($lines)->whereNotNull('problem')->isNotEmpty();
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$done ? 'Danke für deine Bestellung!' : 'Kasse'" kicker="{{ $done ? 'Bestellung eingegangen' : 'Bestellung abschließen' }}" tone="straw" />

    <section class="block" aria-label="Kasse">
        <div class="container">
            @if ($done)
                <div class="form-success checkout-done" role="status">
                    <h2>Deine Bestellung ist angekommen.</h2>
                    <p>{{ count($done['numbers']) > 1 ? 'Vorgangsnummern' : 'Vorgangsnummer' }}: <strong>{{ implode(', ', $done['numbers']) }}</strong></p>
                    @if ($done['shipping_open'])
                        <p>Für dein Land prüfen wir Versand und Einfuhr persönlich und melden uns mit den Versandkosten. Erst danach zahlst du.</p>
                    @elseif ($done['total'] > 0)
                        <p>Bitte überweise <b>{{ number_format($done['total'], 2, ',', '.') }} €</b>. Die Bankdaten und den Verwendungszweck findest du in der Bestätigung, die wir gerade an <b>{{ $done['email'] }}</b> geschickt haben. @if (count($done['numbers']) > 1) Bei mehreren Vorgängen bekommst du je Vorgang eine E-Mail mit dem jeweiligen Betrag. @endif</p>
                    @else
                        <p>Alles ist bereits durch deinen Gutschein bzw. Rabatt bezahlt. Die Bestätigung kommt per E-Mail an <b>{{ $done['email'] }}</b>.</p>
                    @endif
                    <p class="mt-4">Fragen? Ruf an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a> oder antworte auf die E-Mail.</p>
                </div>
                <div class="btn-row mt-5">
                    <x-ui.button href="/shop/" variant="secondary">Zum Shop</x-ui.button>
                    <x-ui.button href="/schmiedekurse/" variant="secondary">Zu den Schmiedekursen</x-ui.button>
                </div>
            @elseif (! $lines)
                <p class="lead">Dein Warenkorb ist leer.</p>
                <div class="btn-row mt-5"><x-ui.button href="/shop/" variant="primary">Messer ansehen</x-ui.button><x-ui.button href="/schmiedekurse/buchen/" variant="secondary">Schmiedekurs buchen</x-ui.button></div>
            @elseif ($problems)
                <p class="field__error" role="alert">Mindestens eine Position in deinem Warenkorb ist nicht mehr verfügbar.</p>
                <x-ui.button href="/warenkorb/" variant="primary">Warenkorb prüfen</x-ui.button>
            @else
                <div class="checkout">
                    <form class="checkout__form inquiry__form" id="checkout-form" method="post" action="{{ route('cart.checkout') }}">
                        @csrf
                        <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

                        <h2 class="checkout__h">1. Deine Daten</h2>
                        @include('pages.partials.contact-fields')

                        @if ($hasProducts)
                            <h2 class="checkout__h">2. Lieferung</h2>
                            @include('pages.partials.shipping-address', ['cartLines' => $lines])
                        @endif

                        <h2 class="checkout__h">{{ $hasProducts ? '3.' : '2.' }} Gutschein</h2>
                        @if ($hasCourses)
                            <label class="field">
                                <span class="field__label">Kurs-Gutscheincode (optional)</span>
                                <input type="text" name="gutschein_code" maxlength="20" autocomplete="off" value="{{ old('gutschein_code') }}" placeholder="GS-…">
                                @error('gutschein_code')<span class="field__error" role="alert">{{ $message }}</span>@enderror
                            </label>
                        @endif
                        <p class="tech muted">Rabattcodes trägst du oben bei „Deine Daten“ ein.</p>

                        <h2 class="checkout__h">{{ $hasProducts ? '4.' : '3.' }} Zahlung</h2>
                        <fieldset class="fields pay-options">
                            <legend class="visually-hidden">Zahlungsart</legend>
                            <label class="check pay-option">
                                <input type="radio" name="zahlung" value="ueberweisung" checked>
                                <span><b>Überweisung</b> – du bekommst die Bankdaten per E-Mail. {{ $hasProducts ? 'Wir verschicken nach Zahlungseingang.' : '' }}{{ $hasCourses ? ' Dein Kurstag ist ab sofort für dich vorgemerkt.' : '' }}</span>
                            </label>
                            <label class="check pay-option is-soon" aria-disabled="true">
                                <input type="radio" name="zahlung" value="online" disabled>
                                <span><b>Online bezahlen</b> (Karte, Apple Pay, Google Pay …) – <span class="soon-badge">bald verfügbar</span></span>
                            </label>
                        </fieldset>

                        <label class="check">
                            <input type="checkbox" name="agb" value="1" required @checked(old('agb'))>
                            <span>Ich habe die <a href="/agb/">AGB</a>{!! $hasCourses ? ', die <a href="/kursbedingungen/">Kursbedingungen</a>' : '' !!} und die <a href="/widerruf/">Widerrufsbelehrung</a> gelesen.</span>
                        </label>
                        @error('agb')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                    </form>

                    <aside class="checkout__summary booking__summary" aria-labelledby="sum-title">
                        <h2 id="sum-title">Deine Bestellung</h2>
                        <ul class="checkout__lines" role="list">
                            @foreach ($lines as $l)
                                <li>
                                    <span><b>{{ $l['name'] }}</b><br><span class="tech muted">{{ $l['type'] === 'kurs' ? $l['detail'].' · '.$l['qty'].' '.($l['qty'] == 1 ? 'Person' : 'Personen') : $l['qty'].' × '.number_format($l['unit'], 2, ',', '.').' €' }}</span></span>
                                    <span>{{ number_format($l['total'], 2, ',', '.') }} €</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="booking__total"><span>{{ $hasProducts ? 'Zwischensumme' : 'Gesamt' }}</span><strong>{{ number_format($subtotal, 2, ',', '.') }} €</strong></p>
                        <p class="tech muted"><x-ui.tax-note />@if ($hasProducts) · Versand siehe „Lieferung“@endif · Rabatt- und Gutscheincodes werden beim Absenden verrechnet.</p>
                        <button type="submit" form="checkout-form" class="btn btn--primary btn--lg booking__pay">Zahlungspflichtig bestellen</button>
                        <p class="tech muted mt-3"><a href="/warenkorb/">Warenkorb ändern</a> · <a href="/versand/#bedingungen">Versand & Zahlung</a></p>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
