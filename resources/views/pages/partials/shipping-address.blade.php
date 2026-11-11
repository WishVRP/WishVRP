{{-- Lieferung: Versand weltweit (Kosten live nach Land) oder Abholung. $productId optional (für das Versandgewicht). --}}
@php
    $countries = \App\Support\Shipping::countries();
    $sel = old('country_code', 'DE');
@endphp
<div class="shipping-box" data-shipping data-ship-url="{{ route('shipping.quote') }}" @isset($productId) data-ship-product="{{ $productId }}" @endisset>
    <fieldset class="fields">
        <legend class="field__label">Lieferung</legend>
        <label class="check"><input type="radio" name="versand" value="versand" @checked(old('versand', 'versand') === 'versand') data-ship-mode> <span>Versand mit DHL – Kosten je nach Land, werden unten berechnet</span></label>
        <label class="check"><input type="radio" name="versand" value="abholung" @checked(old('versand') === 'abholung') data-ship-mode> <span>Abholung in {{ config('ulbrich.address.district') }} (kostenlos, vor Ort bar oder Karte möglich)</span></label>
    </fieldset>
    @error('versand')<p class="field__error" role="alert">{{ $message }}</p>@enderror
    <div class="fields" data-ship-address>
        <label class="field field--wide"><span class="field__label">Land</span>
            <select name="country_code" autocomplete="country" data-ship-country>
                @foreach ($countries as $code => $name)<option value="{{ $code }}" @selected($sel === $code)>{{ $name }}</option>@endforeach
            </select>
            @error('country_code')<span class="field__error">{{ $message }}</span>@enderror
        </label>
        <label class="field field--wide"><span class="field__label">Straße und Hausnummer</span><input type="text" name="street" maxlength="160" autocomplete="street-address" value="{{ old('street') }}">@error('street')<span class="field__error">{{ $message }}</span>@enderror</label>
        <label class="field"><span class="field__label">PLZ</span><input type="text" name="postal_code" maxlength="20" autocomplete="postal-code" value="{{ old('postal_code') }}" data-ship-plz>@error('postal_code')<span class="field__error">{{ $message }}</span>@enderror</label>
        <label class="field"><span class="field__label">Ort</span><input type="text" name="city" maxlength="120" autocomplete="address-level2" value="{{ old('city') }}" data-ship-city>@error('city')<span class="field__error">{{ $message }}</span>@enderror</label>
    </div>
    <p class="shipping-box__quote" data-ship-quote aria-live="polite">
        @php $q = \App\Support\Shipping::quote($sel, isset($productId) ? \App\Models\Product::query()->whereKey($productId)->value('weight_g') : null); @endphp
        @if ($q['price'] !== null)
            Versand nach {{ $q['country_name'] }}: <b>{{ number_format($q['price'], 2, ',', '.') }} €</b> <span class="muted">({{ $q['label'] }})</span>
        @else
            Versand nach {{ $q['country_name'] }}: <b>auf Anfrage</b>
        @endif
    </p>
</div>
