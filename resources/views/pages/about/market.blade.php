@php $m = $c['market']; @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Kunst- & Handwerkermarkt" kicker="Veranstaltet von der Messermanufaktur" tile="Ein Wochenende für echtes Handwerk" tileLabel="18 Stände"
        lead="Ein Wochenende für echte Unikate und ehrliches Handwerk – auf dem Gelände der Messermanufaktur Ulbrich in Stein am Kocher. Hier findest du die Infos zum Markt und zur Teilnahme als Aussteller.">
        <x-slot:actions>
            <x-ui.button href="mailto:{{ $c['email'] }}?subject=Bewerbung%20Kunst-%20%26%20Handwerkermarkt" variant="straw" size="lg">Als Aussteller bewerben</x-ui.button>
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

    <x-page.section title="So bewirbst du dich" kicker="Aussteller werden">
        <ol class="big-steps" role="list">
            <li class="reveal"><span class="big-steps__num">01</span><h3>Bewerbung schicken</h3><p>Per E-Mail an <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a> mit Kontaktdaten, Sortiment, Wunschstand (innen/außen) und Fotos von dir, deinem Stand und deinen Produkten.</p></li>
            <li class="reveal reveal-delay-1"><span class="big-steps__num">02</span><h3>Prüfung</h3><p>Wir prüfen, ob dein Angebot selbst hergestellt ist und zum Markt passt.</p></li>
            <li class="reveal reveal-delay-2"><span class="big-steps__num">03</span><h3>Vertrag und Zahlung</h3><p>Verbindlich wird dein Stand erst nach Vertrag und bestätigtem Zahlungseingang.</p></li>
        </ol>
        <p class="mt-5">Fragen zum Markt und zu künftigen Terminen: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
        <x-ui.missing silent>Online-Standplan mit Bewerbungsformular (live vorhanden) – Nachbau mit Backend, Marktordnung und Standmietvertrag als PDF</x-ui.missing>
    </x-page.section>

    <x-page.cta title="Die Manufaktur hinter dem Markt" text="Der Markt gehört zur Messermanufaktur Ulbrich. Entdecke handgefertigte Messer oder schmiede selbst im Kurs." />
</x-layouts.site>
