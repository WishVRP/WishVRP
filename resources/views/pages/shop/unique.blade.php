<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Messer-Unikate" kicker="Einzelstücke" tone="brown" foto="klappmesser-rotholz"
        lead="Einzelstücke aus der Werkstatt: Damastklingen mit jeweils eigenem Griff. Welche Stücke gerade verfügbar sind, erfährst du direkt beim Messerschmied.">
        <x-slot:actions>
            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">Verfügbarkeit erfragen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="Aus der Werkstatt" kicker="Galerie" kicker-color="brown">
        @include('pages.partials.photo-grid', ['items' => [
            ['klappmesser-rotholz', 'Klappmesser · rotbrauner Holzgriff'],
            ['klappmesser-zebrano', 'Klappmesser · heller Streifengriff'],
            ['klappmesser-hell-detail', 'Klappmesser · heller Griff'],
            ['klappmesser-offen', 'Klappmesser · dunkles Holz'],
            ['klappmesser-stehend', 'Klappmesser · halb geöffnet'],
            ['klappmesser-geschlossen', 'Klappmesser · geschlossen'],
        ]])
        <x-page.note class="mt-6" title="Preise und Verfügbarkeit">Unikate gibt es jeweils nur einmal. Preis und Verfügbarkeit nennt dir {{ $c['owner'] }} auf Anfrage.</x-page.note>
        <x-ui.missing silent>Aktuell verfügbare Unikate mit Preis und Daten</x-ui.missing>
    </x-page.section>

    @include('pages.partials.content-sections', ['key' => 'messer-unikate', 'label' => 'Drei Wege zum Unikat'])

    @include('pages.partials.inquiry', ['type' => 'messer', 'item' => 'Unikat', 'title' => 'Nach einem Unikat fragen', 'submit' => 'Anfrage senden'])

    <x-page.cta title="Ein Unikat gefunden?" />
</x-layouts.site>
