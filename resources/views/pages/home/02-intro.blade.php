<section class="block intro" aria-labelledby="intro-title">
    <div class="container">
        <div class="intro__grid">
            <div class="reveal">
                <x-ui.kicker>Die Manufaktur</x-ui.kicker>
                <h2 id="intro-title" class="statement">
                    Eine Werkstatt, ein Messerschmied und drei Wege zu deinem Messer: selbst schmieden,
                    ein fertiges Stück wählen oder eines nach deinen Vorstellungen anfertigen lassen.
                </h2>
                <p class="lead mt-5 measure">
                    Die Werkstatt liegt in {{ $c['address']['district'] }}, einem Ortsteil von {{ $c['address']['locality'] }} im Landkreis Heilbronn.
                    Hier entstehen die Messer der Manufaktur.
                    <x-ui.missing silent>Kursort = Werkstatt bestätigen</x-ui.missing>
                </p>
                <p class="mt-4"><a class="link-arrow" href="/manufaktur/">Mehr über die Manufaktur <span aria-hidden="true">→</span></a></p>
            </div>
            <div class="intro__aside reveal reveal-delay-1">
                <x-ui.media motif="Andreas Ulbrich mit Schürze vor der Esse" tone="steel" format="portrait" />
                <dl class="spec">
                    <div class="spec__row"><dt>Messerschmied</dt><dd>{{ $c['owner'] }}</dd></div>
                    <div class="spec__row"><dt>Mitglied</dt><dd>{{ $c['chamber'] }}</dd></div>
                    <div class="spec__row"><dt>Werkstatt</dt><dd>{{ $c['address']['district'] }}, {{ $c['address']['locality'] }}</dd></div>
                </dl>
            </div>
        </div>

        <nav class="ways" aria-label="Angebote">
            <a class="way reveal" href="/schmiedekurse/">
                <span class="way__label">Erleben</span>
                <span class="way__title">Schmiedekurse <span aria-hidden="true">→</span></span>
                <span class="way__text">Vom Grundlagenkurs bis zum Damast Premium-Kurs</span>
            </a>
            <a class="way way--knife reveal reveal-delay-1" href="/shop/">
                <span class="way__label">Besitzen</span>
                <span class="way__title">Messer <span aria-hidden="true">→</span></span>
                <span class="way__text">Edition 100, Unikate und Anfertigungen</span>
            </a>
            <a class="way way--gift reveal reveal-delay-2" href="/geschenke/schmiedekurs-gutschein/">
                <span class="way__label">Verschenken</span>
                <span class="way__title">Gutscheine <span aria-hidden="true">→</span></span>
                <span class="way__text">Einen Schmiedekurs als Geschenk</span>
            </a>
        </nav>
    </div>
</section>
