<section class="block block--sunken" aria-labelledby="faq-title">
    <div class="container contact__grid">
        <div class="reveal">
            <x-ui.kicker>Häufige Fragen</x-ui.kicker>
            <h2 id="faq-title" class="h-section">Gut zu wissen</h2>
            <div class="faq mt-5">
                @foreach ($faqs as $faq)
                    <details>
                        <summary>{{ $faq['q'] }}</summary>
                        <div class="faq__answer"><p>{{ $faq['a'] }}</p></div>
                    </details>
                @endforeach
            </div>
            <p class="mt-5"><a class="link-arrow" href="/ratgeber/">Weitere Antworten im Ratgeber <span aria-hidden="true">→</span></a></p>
        </div>

        <aside class="contact-card is-dark reveal reveal-delay-1" id="kontakt" aria-labelledby="kontakt-title">
            <x-ui.kicker color="straw">Kontakt & Anfahrt</x-ui.kicker>
            <h2 id="kontakt-title">Besuch in der Werkstatt</h2>
            <address>
                {{ $c['name'] }}<br>
                {{ $c['address']['street'] }}<br>
                {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}<br>
                <span class="muted">Ortsteil {{ $c['address']['district'] }}</span>
            </address>
            <dl class="spec">
                <div class="spec__row"><dt>Telefon</dt><dd><a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></dd></div>
                <div class="spec__row"><dt>E-Mail</dt><dd>@if($c['email'])<a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>@else<x-ui.missing ask="info@ oder gmx festlegen">E-Mail-Adresse</x-ui.missing>@endif</dd></div>
                @foreach ($c['opening_hours'] as $h)
                    <div class="spec__row"><dt>{{ $h['label'] }}</dt><dd>{{ $h['opens'] }}–{{ $h['closes'] }} Uhr</dd></div>
                @endforeach
            </dl>
            @unless ($c['opening_hours_verified'])<x-ui.missing silent>Öffnungszeiten (laut Google-Profil) von Andreas bestätigen</x-ui.missing>@endunless
            <div class="btn-row">
                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" :arrow="false">Anrufen</x-ui.button>
                <x-ui.button href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($c['address']['street'].', '.$c['address']['postal_code'].' '.$c['address']['locality']) }}" variant="secondary" rel="noopener" target="_blank">Route planen</x-ui.button>
            </div>
            <x-ui.missing silent>Foto Außenansicht/Zufahrt der Werkstatt (optional)</x-ui.missing>
            <x-ui.media foto="werkstatt-halle" format="wide" sizes="(min-width: 60rem) 40vw, 100vw" />
        </aside>
    </div>
</section>
