<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Messerprojekte" kicker="Arbeiten" tone="brown" foto="klappmesser-hell-detail"
        lead="Ein Einblick in bisher entstandene Arbeiten: Klappmesser mit Damastklinge, verschiedene Griffe und Details aus der Werkstatt.">
        <x-slot:actions>
            <x-ui.button href="/sonderanfertigungen/" variant="straw" size="lg">Eigenes Projekt anfragen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="Details, die zählen" kicker="Projekte" kicker-color="brown">
        <div class="mosaic">
            <x-ui.media foto="klappmesser-hell" format="cinema" class="mosaic__wide reveal" sizes="100vw" />
            <x-ui.media foto="damast-detail" class="reveal" sizes="(min-width: 60rem) 33vw, 50vw" />
            <x-ui.media foto="punze-detail" class="reveal reveal-delay-1" sizes="(min-width: 60rem) 33vw, 50vw" />
            <x-ui.media foto="klappmesser-griff" class="reveal reveal-delay-2" sizes="(min-width: 60rem) 33vw, 50vw" />
        </div>
        <x-ui.missing silent>Projektbeschreibungen (Kunde/Anlass, Material, Jahr) – nur mit Freigabe</x-ui.missing>
    </x-page.section>

    @include('pages.home.07b-gallery')

    <x-page.cta title="Selbst ein Projekt im Kopf?" />
</x-layouts.site>
