<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Partner" kicker="Zusammenarbeit" tile="Partner" tileLabel="Region Heilbronn"
        lead="Betriebe und Initiativen, mit denen die Messermanufaktur Ulbrich zusammenarbeitet." />
    <x-page.section title="Unsere Partner" kicker="Netzwerk">
        <p class="lead"><x-ui.missing label="Partnerliste folgt">Partner mit Name, Link und kurzer Beschreibung von der Live-Seite übernehmen</x-ui.missing></p>
        <p class="mt-5">Interesse an einer Zusammenarbeit? Ruf an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
    </x-page.section>
    <x-page.cta />
</x-layouts.site>
