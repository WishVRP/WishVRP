@php
    $meta = new \App\Support\PageMeta(
        title: 'Seite nicht gefunden',
        description: 'Diese Seite gibt es nicht (mehr). Hier geht es weiter zu den Schmiedekursen, zum Shop und zu den Gutscheinen der Messermanufaktur Ulbrich.',
        path: '/',
        index: false,
    );
@endphp
<x-layouts.site :meta="$meta">
    <section class="page-hero is-dark textured error-page" aria-labelledby="page-title">
        <div class="container">
            <p class="error-page__code" aria-hidden="true">404</p>
            <h1 id="page-title" class="page-hero__title">Diese Seite ist nicht (mehr) da.</h1>
            <p class="page-hero__lead mt-4">Vielleicht wurde sie verschoben. Hier geht es weiter:</p>
            <div class="btn-row mt-5">
                <x-ui.button href="/schmiedekurse/" variant="straw" size="lg">Zu den Schmiedekursen</x-ui.button>
                <x-ui.button href="/" variant="secondary" size="lg">Zur Startseite</x-ui.button>
            </div>
            <ul class="link-list link-list--dark mt-6" role="list">
                <li><a href="/schmiedekurse/buchen/">Schmiedekurs buchen <span aria-hidden="true">→</span></a></li>
                <li><a href="/shop/">Shop und Edition 100 <span aria-hidden="true">→</span></a></li>
                <li><a href="/geschenke/schmiedekurs-gutschein/">Schmiedekurs-Gutschein <span aria-hidden="true">→</span></a></li>
                <li><a href="/#kontakt">Kontakt und Anfahrt <span aria-hidden="true">→</span></a></li>
            </ul>
        </div>
    </section>
</x-layouts.site>
