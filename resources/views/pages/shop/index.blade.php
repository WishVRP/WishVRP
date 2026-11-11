<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Shop" kicker="Messer & Gutscheine" tone="brown" foto="klappmesser-offen"
        lead="Handgefertigte Messer aus der Werkstatt von {{ $c['owner'] }}: die nummerierte Edition 100, Unikate, Anfertigungen nach Wunsch – und Gutscheine für Schmiedekurse.">
        <x-slot:actions>
            <x-ui.button href="/klappmesser/" variant="straw" size="lg">Edition 100</x-ui.button>
            <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="secondary" size="lg">Gutscheine</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="Aus der Manufaktur" kicker="Sortiment" kicker-color="brown">
        <div class="product-grid">
            @include('pages.partials.product-card', ['foto' => 'edition-messer', 'title' => $e['name'], 'text' => $e['total'].' nummerierte Stücke · '.$e['regular'].' regulär, '.$e['special'].' Sonderstücke', 'price' => $e['price'], 'href' => $e['url'], 'cta' => 'Ansehen'])
            @include('pages.partials.product-card', ['foto' => 'klappmesser-rotholz', 'title' => 'Messer-Unikate', 'text' => 'Einzelstücke aus der Werkstatt', 'price' => 'Preis auf Anfrage', 'href' => '/messer-unikate/', 'cta' => 'Unikate'])
            @include('pages.partials.product-card', ['foto' => 'klappmesser-griff', 'title' => 'Sonderanfertigung', 'text' => 'Ein Messer nach deinen Vorstellungen', 'price' => 'Angebot nach Absprache', 'href' => '/sonderanfertigungen/', 'cta' => 'Anfragen'])
            @include('pages.partials.product-card', ['foto' => 'klappmesser-hell-detail', 'title' => 'Messerprojekte', 'text' => 'Bisher entstandene Arbeiten', 'price' => 'Galerie', 'href' => '/messerprojekte/', 'cta' => 'Ansehen'])
            @include('pages.partials.product-card', ['foto' => 'klappmesser-zebrano', 'title' => 'Schmiedekurs-Gutschein', 'text' => 'Kurs und Termin wählt die beschenkte Person', 'price' => 'Kurse ab '.number_format($minPrice, 0, ',', '.').' €', 'href' => '/geschenke/schmiedekurs-gutschein/', 'cta' => 'Verschenken'])
        </div>
        <x-page.note class="mt-6" title="So kaufst du aktuell">
            Der Online-Kauf mit Warenkorb und Zahlung wird vor dem Start eingerichtet. Bis dahin bestellst du telefonisch bei {{ $c['owner'] }}:
            <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>. Preise: <x-ui.tax-note />.
        </x-page.note>
        <x-ui.missing silent>Versandbedingungen, Versandkosten, Altersprüfung beim Messerversand</x-ui.missing>
    </x-page.section>

    @include('pages.home.07b-gallery')

    @include('pages.partials.subpages', ['prefix' => '/shop/', 'title' => 'Messer nach Art', 'kicker' => 'Kategorien', 'sunken' => true])

    <x-page.cta title="Frage zu einem Messer?" text="Verfügbarkeit, Details und Bestellung klärst du direkt mit dem Messerschmied." />
</x-layouts.site>
