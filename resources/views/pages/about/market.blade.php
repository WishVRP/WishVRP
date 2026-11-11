@php
    $m = $c['market'];
    $ms = \App\Support\Market::settings();
    $stands = \App\Support\Market::stands();
    $status = \App\Support\Market::statusMap();
    $free = count(array_filter($status, fn ($s) => $s === 'frei'));
    $faqs = \App\Support\Market::faqs();
    $stateLabel = ['frei' => 'frei', 'reserviert' => 'reserviert', 'belegt' => 'vergeben', 'mein' => 'deiner'];
    $standBtn = function (int $n) use ($stands, $status, $ms, $m, $stateLabel) {
        $st = $stands[$n];
        $s = $status[$n];
        return view('pages.partials.market-stand', ['st' => $st, 's' => $s, 'open' => $ms['open'], 'side' => $m['layout'][$n] ?? null, 'stateLabel' => $stateLabel])->render();
    };
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Kunst- & Handwerkermarkt in Stein am Kocher" kicker="Landkreis Heilbronn · {{ $m['days'] }}" tile="Ein Wochenende für echtes Handwerk" tileLabel="18 Stände"
        lead="Zwei Tage, 18 Stände, nur Selbstgemachtes: Der Kunst- & Handwerkermarkt in Halle und Hof der Messermanufaktur Ulbrich in Stein am Kocher bei Neuenstadt. Für Besucher ein Wochenende voller Unikate – für Kunsthandwerker die Chance auf einen Stand.">
        <x-slot:actions>
            <x-ui.button href="#standplan" variant="straw" size="lg">Stand wählen & bewerben</x-ui.button>
            <x-ui.button href="#termin" variant="secondary" size="lg">Termin erfahren</x-ui.button>
        </x-slot:actions>
        <dl class="page-facts">
            <div><dt>Termin</dt><dd>@if($ms['date']){{ $ms['date'] }}@else<span class="page-facts__small">wird bekannt gegeben</span>@endif</dd></div>
            <div><dt>Stände frei</dt><dd>{{ $free }} / 18</dd></div>
            <div><dt>ab € für 2 Tage</dt><dd>{{ min(array_column($m['stands'], 'price')) }}</dd></div>
        </dl>
    </x-page.hero>

    {{-- Für Besucher --}}
    <x-page.section title="Was dich auf dem Markt erwartet" kicker="Für Besucher">
        <div class="split">
            <div class="prose reveal">
                <p>An einem Wochenende verwandeln sich Halle und Außengelände der Messermanufaktur Ulbrich in einen Markt für <strong>echtes Kunsthandwerk</strong>. Zugelassen sind ausschließlich Waren, die die Ausstellerinnen und Aussteller selbst hergestellt, gestaltet oder wesentlich von Hand bearbeitet haben – keine Handels- und keine Massenware.</p>
                <p>Acht Stände stehen in der Halle, zehn auf dem Außenplatz an der Wannenstraße. {{ $m['catering'] }} Nebenan liegt die Werkstatt, in der Messerschmied {{ $c['owner'] }} seine <a href="/klappmesser/">Damast-Klappmesser</a> fertigt und <a href="/schmiedekurse/">Schmiedekurse</a> gibt.</p>
                <ul class="market-tags" role="list" aria-label="Sortiment">
                    @foreach ($m['categories'] as $cat)<li>{{ $cat }}</li>@endforeach
                </ul>
            </div>
            <div class="address-card reveal reveal-delay-1">
                <x-ui.icon name="pin" class="address-card__icon" />
                <p><strong>Ort</strong><br>{{ $m['venue'] }}<br>{{ $m['place'] }}</p>
                <p class="mt-4"><strong>Markttage</strong><br>{{ $m['days'] }} · {{ $ms['date'] ?: 'Termin wird bekannt gegeben' }}</p>
                <p class="mt-4"><strong>Anreise</strong><br>{{ $m['parking'] }} Mehr zur Lage im <a href="/schmiedekurse/heilbronn-region/">Raum Heilbronn und Neuenstadt</a>.</p>
            </div>
        </div>
    </x-page.section>

    {{-- Termin-Benachrichtigung --}}
    <section class="block block--tight block--sunken" id="termin" aria-labelledby="termin-title">
        <div class="container market-notify">
            <div>
                <x-ui.kicker>Termin</x-ui.kicker>
                <h2 id="termin-title" class="h-section">{{ $ms['date'] ? 'Der nächste Markt: '.$ms['date'] : 'Der nächste Termin wird bald bekannt gegeben' }}</h2>
                <p class="lead mt-3">{{ $ms['date'] ? 'Trag dich ein, dann erinnern wir dich rechtzeitig und du erfährst als Erstes vom übernächsten Markt.' : 'Trag dich ein – wir sagen dir per E-Mail Bescheid, sobald das Datum feststeht.' }}</p>
            </div>
            @if (session('market_notify'))
                <p class="note note--ok" role="status">Fast geschafft: Bitte bestätige deine E-Mail-Adresse über den Link, den wir dir gerade geschickt haben.</p>
            @else
                <form method="post" action="{{ route('market.notify') }}" class="market-notify__form">
                    @csrf
                    <input class="hp" type="text" name="website_hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <label class="field"><span class="field__label">E-Mail-Adresse</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="{{ old('email') }}">@error('email')<span class="field__error">{{ $message }}</span>@enderror</label>
                    <label class="check"><input type="checkbox" name="newsletter_consent" value="1" required> <span>{{ \App\Models\NewsletterSubscriber::CONSENT }} Darin informieren wir auch über den Markttermin. <a href="/datenschutz/">Datenschutz</a></span></label>
                    @error('newsletter_consent')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                    <button type="submit" class="btn btn--primary mt-4">Benachrichtigen</button>
                </form>
            @endif
        </div>
    </section>

    {{-- Standplan --}}
    <x-page.section title="Der Standplan: Wähle deinen Platz" kicker="Für Aussteller · Online-Standplan" id="standplan">
        <p class="lead">
            @if ($ms['open'])
                Tippe auf einen freien Stand – er ist dann <b>{{ \App\Support\Market::HOLD_MINUTES }} Minuten</b> für deine Bewerbung reserviert. Noch <b data-market-free>{{ $free }}</b> von 18 Ständen frei.
            @else
                Die Bewerbungsphase ist gerade geschlossen. Fragen zu künftigen Terminen: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>
            @endif
        </p>
        <div class="market-layout" data-market data-stands-url="{{ route('market.stands') }}" data-reserve-url="{{ route('market.reserve') }}"
             data-fee-ad="{{ $m['fees'][0]['price'] }}" data-fee-light="{{ $m['fees'][1]['price'] }}" @unless($ms['open']) data-closed @endunless>
            <div class="market-maps">
                {{-- Halle (Seite 1 des Standplans) --}}
                <figure class="market-map market-map--halle" aria-labelledby="map-halle">
                    <figcaption id="map-halle" class="market-map__title"><span>Halle · Innenbereich</span><small>Stände 1–8 · je {{ $m['stands'][0]['size'] }} · {{ $m['stands'][0]['price'] }} €</small></figcaption>
                    <div class="market-map__ground market-map__ground--halle">
                        <span class="market-map__mark market-map__mark--exit">Ausgang / Rettungsweg</span>
                        <span class="market-map__path market-map__path--halle">Freier Besucher- und Rettungsweg</span>
                        @foreach ([8, 7, 5, 6, 1, 2, 3, 4] as $n){!! $standBtn($n) !!}@endforeach
                        <span class="market-map__mark market-map__mark--entry">Haupteingang</span>
                    </div>
                </figure>
                {{-- Außenplatz (Seite 2 des Standplans) --}}
                <figure class="market-map market-map--hof" aria-labelledby="map-hof">
                    <figcaption id="map-hof" class="market-map__title"><span>Außenplatz</span><small>Stände 9–18 · je {{ $m['stands'][1]['size'] }} · {{ $m['stands'][1]['price'] }} €</small></figcaption>
                    <div class="market-map__ground market-map__ground--hof">
                        <span class="market-map__street market-map__street--top">Wannenstraße</span>
                        <span class="market-map__street market-map__street--side">Straßenseite</span>
                        @foreach ([9, 10, 11, 12, 13, 15, 16, 17, 18, 14] as $n){!! $standBtn($n) !!}@endforeach
                        <span class="market-map__path market-map__path--hof">Freier Besucher- und Rettungsweg</span>
                        <span class="market-map__food">Wurst &amp; Getränke<small>Stand des Veranstalters</small></span>
                        <a class="market-map__hall" href="#map-halle">Halle<small>Stände 1–8</small></a>
                        <span class="market-map__mark market-map__mark--entry">Eingang</span>
                    </div>
                </figure>
                <p class="market-map__note">Schematischer Standplan, nicht maßstabsgerecht · <a href="/downloads/kunsthandwerkermarkt-standplan.pdf">Standplan als PDF</a></p>
            </div>

            <aside class="market-panel" aria-live="polite">
                <p class="market-panel__kicker">Dein Stand</p>
                <p class="market-panel__no" data-panel-no>–</p>
                <p class="market-panel__meta" data-panel-meta>Tippe im Plan auf einen freien Stand.</p>
                <dl class="market-panel__facts">
                    <div><dt>Größe</dt><dd data-panel-size>–</dd></div>
                    <div><dt>Verkaufsseite</dt><dd data-panel-side>–</dd></div>
                    <div><dt>Standgebühr</dt><dd data-panel-price>–</dd></div>
                    <div><dt>+ Werbepauschale</dt><dd>{{ $m['fees'][0]['price'] }} €</dd></div>
                </dl>
                <p class="market-panel__timer" data-panel-timer></p>
                <a class="btn btn--primary market-panel__cta" href="#bewerbung" data-panel-cta hidden>Jetzt bewerben</a>
                <ul class="market-legend" role="list">
                    <li><span class="market-dot is-frei"></span> frei</li>
                    <li><span class="market-dot is-mein"></span> deine Wahl</li>
                    <li><span class="market-dot is-reserviert"></span> reserviert / in Prüfung</li>
                    <li><span class="market-dot is-belegt"></span> vergeben</li>
                </ul>
                <p class="market-plan__msg" data-market-msg></p>
            </aside>
        </div>
    </x-page.section>

    {{-- Preise --}}
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

    {{-- Ablauf und Regeln für Aussteller --}}
    <x-page.section title="So wirst du Aussteller" kicker="Ablauf">
        <ol class="big-steps" role="list">
            <li class="reveal"><span class="big-steps__num">01</span><h3>Stand wählen & bewerben</h3><p>Freien Stand im Plan antippen, Bewerbung mit Fotos deines Stands und deiner Arbeiten absenden – du bekommst sofort deinen Aussteller-Code.</p></li>
            <li class="reveal reveal-delay-1"><span class="big-steps__num">02</span><h3>Prüfung</h3><p>Wir prüfen, ob dein Angebot selbst hergestellt ist und zum Markt passt. Ein Anspruch auf Zulassung oder einen bestimmten Platz besteht nicht.</p></li>
            <li class="reveal reveal-delay-2"><span class="big-steps__num">03</span><h3>Vertrag & Zahlung</h3><p>Bei Zusage bekommst du Standmietvertrag, Marktordnung und Standplan per E-Mail. Mit unterschriebenem Vertrag und Zahlung ist dein Stand fest.</p></li>
            <li class="reveal reveal-delay-3"><span class="big-steps__num">04</span><h3>Aufbau</h3><p>{{ $m['setup'] }} {{ $m['teardown'] }}</p></li>
        </ol>
        <div class="split mt-6">
            <ul class="check-list reveal" role="list">
                @foreach ($m['rules'] as $r)<li>{{ $r }}</li>@endforeach
            </ul>
            <div class="market-downloads reveal reveal-delay-1">
                <h3>Unterlagen zum Download</h3>
                <ul role="list">
                    @foreach ($m['downloads'] as $d)
                        <li><a href="{{ $d['file'] }}" download><x-ui.icon name="download" class="market-downloads__icon" /><span>{{ $d['label'] }}<small>PDF · {{ $d['size'] }}</small></span></a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-page.section>

    {{-- Bewerbung --}}
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
                    <label class="field field--wide"><span class="field__label">Fotos: aktuelles Bild deines Verkaufsstands und deiner Arbeiten * (1–5 Bilder, je max. 6 MB)</span><input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>@error('photos')<span class="field__error">{{ $message }}</span>@enderror @error('photos.*')<span class="field__error">{{ $message }}</span>@enderror</label>
                </div>
                <fieldset class="fields market-form__tables" data-market-tables hidden>
                    <legend class="field__label">Biertische (nur Innenstand)</legend>
                    <label class="check"><input type="radio" name="tables" value="1" @checked(old('tables', '1') === '1')> <span>1 Biertisch</span></label>
                    <label class="check"><input type="radio" name="tables" value="2" @checked(old('tables') === '2')> <span>2 Biertische</span></label>
                </fieldset>
                <label class="check"><input type="checkbox" name="lighting" value="1" @checked(old('lighting')) data-market-light> <span>Beleuchtung für beide Tage gewünscht (+ {{ $m['fees'][1]['price'] }} €, nach Absprache)</span></label>
                <label class="check"><input type="checkbox" name="food" value="1" @checked(old('food'))> <span>Ich möchte Lebensmittel oder Getränke anbieten (Freigabe nötig)</span></label>
                <label class="check"><input type="checkbox" name="demo" value="1" @checked(old('demo'))> <span>Ich plane eine Vorführung (Freigabe nötig)</span></label>
                <p class="market-form__sum" data-market-sum></p>
                <label class="check"><input type="checkbox" name="handmade" value="1" required @checked(old('handmade'))> <span>Ich stelle meine Waren selbst her (keine Handels- oder Massenware).</span></label>
                @error('handmade')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <label class="check"><input type="checkbox" name="rules" value="1" required @checked(old('rules'))> <span>Ich habe die <a href="/downloads/kunsthandwerkermarkt-marktordnung.pdf" target="_blank" rel="noopener">Marktordnung</a> gelesen.</span></label>
                @error('rules')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <label class="check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> <span>Ich bin einverstanden, dass meine Angaben und Fotos zur Prüfung meiner Bewerbung gespeichert werden (<a href="/datenschutz/">Datenschutz</a>).</span></label>
                @error('consent')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn--primary btn--lg mt-5">Bewerbung senden</button>
                <p class="muted mt-4">Die Bewerbung ist noch keine Zusage. Verbindlich wird dein Stand mit dem unterschriebenen Standmietvertrag und der Zahlung.</p>
            </form>
        @endif
    </x-page.section>

    {{-- FAQ (identisch mit FAQPage-Daten) --}}
    <x-page.section title="Häufige Fragen zum Kunst- & Handwerkermarkt" kicker="FAQ">
        @include('pages.partials.faq', ['faqs' => $faqs])
    </x-page.section>

    <x-page.cta title="Die Manufaktur hinter dem Markt" text="Der Markt gehört zur Messermanufaktur Ulbrich. Entdecke handgefertigte Messer oder schmiede selbst im Kurs." />
</x-layouts.site>
