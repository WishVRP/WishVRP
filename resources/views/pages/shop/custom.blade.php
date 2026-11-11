<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Messer anfertigen lassen" kicker="Sonderanfertigung" tone="brown" foto="klappmesser-griff"
        lead="Klingenform, Länge, Griff: Wenn du ein bestimmtes Messer im Kopf hast, fertigt {{ $c['owner'] }} es auf Anfrage.">
        <x-slot:actions>
            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">Anfrage: {{ $c['phone']['display'] }}</x-ui.button>
            <x-ui.button href="/messerprojekte/" variant="secondary" size="lg">Projekte ansehen</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="So entsteht deine Anfertigung" kicker="Ablauf" kicker-color="brown">
        <ol class="big-steps" role="list">
            <li class="reveal"><span class="big-steps__num">01</span><h3>Du beschreibst dein Messer</h3><p>Verwendung, Form, Material – Skizzen und Fotos helfen.</p></li>
            <li class="reveal reveal-delay-1"><span class="big-steps__num">02</span><h3>Gemeinsame Abstimmung</h3><p>Machbarkeit, Details und Angebot werden besprochen.</p></li>
            <li class="reveal reveal-delay-2"><span class="big-steps__num">03</span><h3>Das Messer entsteht in der Werkstatt</h3><p>Von Hand geschmiedet und fertiggestellt.</p></li>
        </ol>
        <x-ui.missing silent>Lieferzeit, Preisrahmen, Anzahlung bei Anfertigungen</x-ui.missing>
    </x-page.section>

    <x-page.section title="Was für die Anfrage hilft" kicker="Vorbereitung" sunken>
        <div class="split">
            <ul class="check-list reveal" role="list">
                <li>Wofür soll das Messer genutzt werden?</li>
                <li>Welche Klingenform und ungefähre Länge stellst du dir vor?</li>
                <li>Gibt es Wünsche zum Griff oder zum Material?</li>
                <li>Skizzen oder Fotos von Messern, die dir gefallen</li>
            </ul>
            <x-ui.media foto="klappmesser-hell-detail" class="reveal reveal-delay-1" sizes="(min-width: 60rem) 45vw, 100vw" />
        </div>
    </x-page.section>

    @include('pages.partials.inquiry', ['type' => 'messer', 'item' => 'Sonderanfertigung', 'title' => 'Sonderanfertigung anfragen', 'intro' => 'Beschreib, wofür du das Messer brauchst, welche Form, Länge und welchen Griff du dir vorstellst – gern mit Budget und Wunschtermin.', 'submit' => 'Anfrage senden'])

    <x-page.cta title="Dein Messer besprechen" text="Ruf an und beschreibe, was du vorhast – {{ $c['owner'] }} sagt dir, was möglich ist." />
</x-layouts.site>
