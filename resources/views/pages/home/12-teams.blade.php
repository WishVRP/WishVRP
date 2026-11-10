<section class="block teams is-dark" aria-labelledby="teams-title">
    <x-ui.media foto="werkstatt-kurs" decorative sizes="(min-width: 60rem) 60vw, 100vw" />
    <div class="container">
        <div class="teams__inner reveal">
            <x-ui.kicker color="straw">Firmen & Gruppen</x-ui.kicker>
            <h2 id="teams-title" class="h-section">Schmieden im Team</h2>
            <p>
                Der Manager-Schmiedekurs dauert drei Tage. Termin und Preis werden auf Anfrage abgestimmt –
                ruf an und beschreibe, was ihr vorhabt.
            </p>
            <ul class="punze-row" role="list">
                <li><x-ui.punze key="Format">Manager-Schmiedekurs</x-ui.punze></li>
                <li><x-ui.punze key="Dauer">3 Tage</x-ui.punze></li>
                <li><x-ui.punze key="Preis">auf Anfrage</x-ui.punze></li>
            </ul>
            <x-ui.missing silent>Gruppengrößen, weitere Firmenformate</x-ui.missing>
            <div class="btn-row">
                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" :arrow="false">Gruppe anfragen: {{ $c['phone']['display'] }}</x-ui.button>
                <x-ui.button href="/erlebnisse/" variant="secondary">Erlebnisse ansehen</x-ui.button>
            </div>
        </div>
    </div>
</section>
