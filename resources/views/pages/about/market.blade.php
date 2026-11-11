@php $m = $c['market']; $ms = \App\Support\Market::settings(); $m['date'] = $ms['date']; $stands = \App\Support\Market::stands(); $status = \App\Support\Market::statusMap(); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Kunst- & Handwerkermarkt" kicker="Veranstaltet von der Messermanufaktur" tile="Ein Wochenende für echtes Handwerk" tileLabel="18 Stände"
        lead="Ein Wochenende für echte Unikate und ehrliches Handwerk – auf dem Gelände der Messermanufaktur Ulbrich in Stein am Kocher. Hier findest du die Infos zum Markt und zur Teilnahme als Aussteller.">
        <x-slot:actions>
            <x-ui.button href="#standplan" variant="straw" size="lg">Stand wählen & bewerben</x-ui.button>
            <x-ui.button href="#preise" variant="secondary" size="lg">Standgebühren</x-ui.button>
        </x-slot:actions>
        <dl class="page-facts">
            <div><dt>Termin</dt><dd>@if($m['date']){{ $m['date'] }}@else<span class="page-facts__small">wird bekanntgegeben</span>@endif</dd></div>
            <div><dt>Stände</dt><dd>18</dd></div>
            <div><dt>ab € für 2 Tage</dt><dd>{{ min(array_column($m['stands'], 'price')) }}</dd></div>
        </dl>
    </x-page.hero>
    @unless ($m['date'])<x-ui.missing silent>Markttermin (Datum, Uhrzeiten)</x-ui.missing>@endunless

    <x-page.section title="Das müsst ihr wissen" kicker="Wichtige Informationen">
        <div class="split">
            <ul class="check-list reveal" role="list">
                @foreach ($m['rules'] as $r)<li>{{ $r }}</li>@endforeach
            </ul>
            <div class="address-card reveal reveal-delay-1">
                <x-ui.icon name="pin" class="address-card__icon" />
                <p><strong>Ort</strong><br>{{ $m['place'] }}</p>
                <p class="mt-4"><strong>Sortiment</strong><br>{{ implode(' · ', $m['categories']) }}</p>
            </div>
        </div>
    </x-page.section>

    <x-page.section title="Standgebühren für beide Markttage" kicker="Preise auf einen Blick" id="preise" sunken>
        <div class="path-cards path-cards--three">
            @foreach ($m['stands'] as $st)
                <div class="path-card reveal">
                    <p class="path-card__label">Stände {{ $st['numbers'] }} · {{ $st['size'] }}</p>
                    <h3>{{ $st['label'] }}: {{ $st['price'] }} €</h3>
                    <p>{{ $st['text'] }}</p>
                </div>
            @endforeach
            <div class="path-card reveal">
                <p class="path-card__label">Zuschläge</p>
                <ul role="list">@foreach ($m['fees'] as $f)<li>+ {{ $f['price'] }} € · {{ $f['label'] }}</li>@endforeach</ul>
            </div>
        </div>
        <p class="mt-5">Beispiele: Innenstand inklusive Werbepauschale {{ $m['stands'][0]['price'] + $m['fees'][0]['price'] }} € · Außenstand inklusive Werbepauschale {{ $m['stands'][1]['price'] + $m['fees'][0]['price'] }} € · mit Beleuchtung jeweils weitere {{ $m['fees'][1]['price'] }} €.</p>
    </x-page.section>

    <x-page.section title="Wähle deinen Stand" kicker="Online-Standplan" id="standplan">
        @if (! $ms['open'])
            <p class="lead">Die Bewerbungsphase ist gerade geschlossen. Fragen zu künftigen Terminen: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
        @else
            <p class="lead">Tippe auf einen freien Stand. Er wird dann <b>{{ \App\Support\Market::HOLD_MINUTES }} Minuten</b> für deine Bewerbung reserviert.</p>
        @endif
        <div class="market-plan" data-market data-stands-url="{{ route('market.stands') }}" data-reserve-url="{{ route('market.reserve') }}" data-fee-ad="{{ $m['fees'][0]['price'] }}" data-fee-light="{{ $m['fees'][1]['price'] }}" @unless($ms['open']) data-closed @endunless>
            @foreach (['innen' => 'Halle innen', 'aussen' => 'Außenplatz'] as $type => $areaLabel)
                @php $list = array_filter($stands, fn ($st) => $st['type'] === $type); $first = reset($list); @endphp
                <div class="market-plan__area market-plan__area--{{ $type }}">
                    <h3>{{ $areaLabel }} <span class="muted">· {{ $first['size'] }} · {{ $first['price'] }} € für beide Tage</span></h3>
                    <div class="market-plan__grid" role="group" aria-label="{{ $areaLabel }}">
                        @foreach ($list as $st)
                            <button type="button" class="market-stand is-{{ $status[$st['number']] }}" data-stand="{{ $st['number'] }}" data-price="{{ $st['price'] }}" data-label="{{ $st['label'] }}"
                                @disabled($status[$st['number']] !== 'frei' || ! $ms['open']) aria-label="Stand {{ $st['number'] }}, {{ $status[$st['number']] }}">
                                <span class="market-stand__no">{{ $st['number'] }}</span>
                                <span class="market-stand__state">{{ ['frei' => 'frei', 'reserviert' => 'reserviert', 'belegt' => 'belegt', 'mein' => 'deiner'][$status[$st['number']]] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <ul class="market-plan__legend" role="list">
                <li><span class="market-stand is-frei"></span> frei</li><li><span class="market-stand is-mein"></span> von dir gewählt</li>
                <li><span class="market-stand is-reserviert"></span> reserviert / in Prüfung</li><li><span class="market-stand is-belegt"></span> vergeben</li>
            </ul>
            <p class="market-plan__msg" data-market-msg aria-live="polite"></p>
        </div>
    </x-page.section>

    <x-page.section title="Zeig uns dein Handwerk" kicker="Bewerbung" id="bewerbung" sunken>
        @if (session('market_code'))
            <div class="note note--ok" role="status">
                <h3>Bewerbung eingegangen</h3>
                <p>Wir prüfen deine Angaben. Dein Aussteller-Code lautet: <b class="tech">{{ session('market_code') }}</b></p>
                <p>Der Code wird dir zusätzlich per E-Mail geschickt.</p>
            </div>
        @elseif ($ms['open'])
            <form class="inquiry__form market-form" method="post" action="{{ route('market.apply') }}" enctype="multipart/form-data" data-market-form>
                @csrf
                <input class="hp" type="text" name="website_hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="hidden" name="stand" value="{{ old('stand') }}" data-market-stand-input>
                <p class="market-form__stand" data-market-chosen>{{ old('stand') ? 'Gewählter Stand: Nr. '.old('stand') : 'Noch kein Stand gewählt – bitte oben im Standplan einen freien Stand antippen.' }}</p>
                @error('stand')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <div class="fields">
                    <label class="field"><span class="field__label">Name *</span><input type="text" name="name" required maxlength="120" autocomplete="name" value="{{ old('name') }}">@error('name')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="field"><span class="field__label">Werkstatt / Marke</span><input type="text" name="company" maxlength="120" value="{{ old('company') }}"></label>
                    <label class="field"><span class="field__label">E-Mail *</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="{{ old('email') }}">@error('email')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="field"><span class="field__label">Telefon *</span><input type="tel" name="phone" required maxlength="40" autocomplete="tel" value="{{ old('phone') }}">@error('phone')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="field field--wide"><span class="field__label">Straße und Hausnummer *</span><input type="text" name="street" required maxlength="160" autocomplete="street-address" value="{{ old('street') }}"></label>
                    <label class="field"><span class="field__label">PLZ *</span><input type="text" name="postal_code" required maxlength="20" autocomplete="postal-code" value="{{ old('postal_code') }}"></label>
                    <label class="field"><span class="field__label">Ort *</span><input type="text" name="city" required maxlength="120" autocomplete="address-level2" value="{{ old('city') }}"></label>
                    <label class="field field--wide"><span class="field__label">Sortiment *</span>
                        <select name="category" required><option value="">Bitte wählen</option>@foreach ($m['categories'] as $cat)<option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>@endforeach</select>
                        @error('category')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="field field--wide"><span class="field__label">Was stellst du her und wie? *</span><textarea name="description" rows="5" required minlength="40" maxlength="3000">{{ old('description') }}</textarea>@error('description')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="field field--wide"><span class="field__label">Website oder Instagram</span><input type="text" name="website" maxlength="190" value="{{ old('website') }}" placeholder="z. B. instagram.com/deine-werkstatt"></label>
                    <label class="field field--wide"><span class="field__label">Fotos deiner Arbeiten und deines Stands * (1–5 Bilder, je max. 6 MB)</span><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>@error('photos')<span class="field__error">{{ $message }}</span>@enderror @error('photos.*')<span class="field__error">{{ $message }}</span>@enderror</label>
                </div>
                <label class="check"><input type="checkbox" name="lighting" value="1" @checked(old('lighting')) data-market-light> <span>Beleuchtung für beide Tage gewünscht (+ {{ $m['fees'][1]['price'] }} €, nach Absprache)</span></label>
                <p class="market-form__sum" data-market-sum></p>
                <label class="check"><input type="checkbox" name="handmade" value="1" required @checked(old('handmade'))> <span>Ich stelle meine Waren selbst her (keine Handels- oder Massenware).</span></label>
                @error('handmade')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> <span>Ich bin einverstanden, dass meine Angaben und Fotos zur Prüfung meiner Bewerbung gespeichert werden (<a href="/datenschutz/">Datenschutz</a>).</span></label>
                @error('consent')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn--primary btn--lg mt-5">Bewerbung senden</button>
                <p class="muted mt-4">Die Bewerbung ist noch keine Zusage. Verbindlich wird dein Stand erst nach Prüfung, Vertrag und bestätigtem Zahlungseingang.</p>
            </form>
        @endif
        <x-ui.missing silent>Marktordnung und Standmietvertrag als PDF</x-ui.missing>
    </x-page.section>

    <x-page.cta title="Die Manufaktur hinter dem Markt" text="Der Markt gehört zur Messermanufaktur Ulbrich. Entdecke handgefertigte Messer oder schmiede selbst im Kurs." />
</x-layouts.site>
