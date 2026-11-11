{{--
    Bestell-/Anfrageformular. Parameter: $type (gutschein|messer|service|anfrage), $title, $item (optional, fest),
    $voucherCourses (optional, für Gutscheine), $shipping (Adressfelder zeigen), $submit (Button-Text)
--}}
@php $source = '/'.trim(request()->path(), '/').'/'; @endphp
<section class="block block--sunken" id="anfrage" aria-labelledby="anfrage-h">
    <div class="container inquiry">
        <div class="inquiry__intro">
            <h2 id="anfrage-h" class="h-section">{{ $title }}</h2>
            <p class="lead mt-4">{{ $intro ?? 'Schreib uns kurz, was du dir vorstellst. '.config('ulbrich.owner').' meldet sich persönlich mit allen Details.' }}</p>
            <p class="muted mt-4">Bezahlt wird per Überweisung, bei Abholung auch bar oder mit Karte. Es wird nichts automatisch abgebucht.</p>
        </div>
        <div>
            @if (session('order_ok'))
                <div class="form-success" id="anfrage-gesendet" role="status">
                    <h3>Danke – deine Nachricht ist angekommen.</h3>
                    <p>Vorgangsnummer <strong>{{ session('order_ok') }}</strong>. Du bekommst eine Bestätigung per E-Mail, und wir melden uns persönlich.</p>
                </div>
            @endif
            <form class="inquiry__form" method="post" action="/anfrage/">
                @csrf
                <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="source" value="{{ $source }}">
                @if (! empty($item))
                    <input type="hidden" name="item" value="{{ $item }}">
                @endif
                <div class="fields">
                    @if (! empty($voucherCourses))
                        <label class="field">
                            <span class="field__label">Gutschein für</span>
                            <select name="kurs">
                                @foreach ($voucherCourses as $co)
                                    <option value="{{ \Illuminate\Support\Str::slug($co->name) }}" @selected(old('kurs') === \Illuminate\Support\Str::slug($co->name))>{{ $co->name }}{{ $co->price_from ? ' – ab '.number_format($co->price_from, 0, ',', '.').' € p. P.' : '' }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    @if (! empty($editionNumbers))
                        <label class="field">
                            <span class="field__label">Wunschnummer</span>
                            <select name="number" id="edition-nummer">
                                <option value="">egal – nächste freie Nummer</option>
                                @foreach ($editionNumbers as $n)
                                    <option value="{{ $n }}" @selected((string) old('number', request('nummer')) === (string) $n)>Nr. {{ $n }}</option>
                                @endforeach
                            </select>
                            @error('number')<span class="field__error" role="alert">{{ $message }}</span>@enderror
                        </label>
                    @endif
                    @if (! empty($quantity))
                        <label class="field">
                            <span class="field__label">{{ $quantity }}</span>
                            <input type="number" name="quantity" min="1" max="20" value="{{ old('quantity', 1) }}">
                        </label>
                    @endif
                </div>
                @include('pages.partials.contact-fields')
                @if (! empty($publicName))
                    @include('pages.partials.shipping-address')
                @endif
                @if (! empty($publicName))
                    <fieldset class="inquiry__public">
                        <label class="check">
                            <input type="checkbox" name="show_name" value="1" @checked(old('show_name')) data-public-toggle>
                            <span>Mein Name soll auf der Website bei meiner Nummer erscheinen (grünes Feld im Raster; freiwillig, jederzeit widerrufbar).</span>
                        </label>
                        <label class="field mt-3">
                            <span class="field__label">So soll der Name erscheinen</span>
                            <input type="text" name="public_name" maxlength="80" value="{{ old('public_name') }}" placeholder="z. B. Erika M.">
                        </label>
                    </fieldset>
                @endif
                @if (! empty($shipping) && empty($publicName))
                    <details class="inquiry__shipping">
                        <summary>Lieferadresse angeben (optional)</summary>
                        <div class="fields mt-4">
                            <label class="field field--wide"><span class="field__label">Straße und Hausnummer</span><input type="text" name="street" maxlength="160" autocomplete="street-address" value="{{ old('street') }}"></label>
                            <label class="field"><span class="field__label">PLZ</span><input type="text" name="postal_code" maxlength="20" autocomplete="postal-code" value="{{ old('postal_code') }}"></label>
                            <label class="field"><span class="field__label">Ort</span><input type="text" name="city" maxlength="120" autocomplete="address-level2" value="{{ old('city') }}"></label>
                            <label class="field"><span class="field__label">Land</span><select name="country_code" autocomplete="country">@foreach (\App\Support\Shipping::countries() as $code => $cname)<option value="{{ $code }}" @selected(old('country_code', 'DE') === $code)>{{ $cname }}</option>@endforeach</select></label>
                        </div>
                    </details>
                @endif
                <button type="submit" class="btn btn--primary btn--lg mt-5">{{ ! empty($publicName) && \App\Support\Stripe::enabled() ? 'Zahlungspflichtig bestellen' : ($submit ?? 'Anfrage senden') }}</button>
            </form>
        </div>
    </div>
</section>
