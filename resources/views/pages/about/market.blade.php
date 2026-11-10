<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Kunsthandwerkermarkt" kicker="Vor Ort" foto="klappmesser-zebrano"
        lead="Die Messermanufaktur Ulbrich auf dem Kunsthandwerkermarkt: Messer ansehen und mit dem Messerschmied ins Gespräch kommen." />
    <x-page.section title="Termine und Infos" kicker="Markt">
        <p class="lead"><x-ui.missing label="Termine folgen">Markt: Name, Ort, Datum, Uhrzeit – von der Live-Seite übernehmen</x-ui.missing></p>
        <div class="btn-row mt-5">
            <x-ui.button href="/shop/" variant="brown">Messer im Shop</x-ui.button>
            <x-ui.button href="/manufaktur/" variant="secondary">Zur Manufaktur</x-ui.button>
        </div>
    </x-page.section>
    <x-page.cta />
</x-layouts.site>
