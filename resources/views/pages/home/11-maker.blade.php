<section class="block maker" aria-labelledby="maker-title">
    <div class="container maker__grid">
        <div class="maker__portrait reveal">
            <x-ui.media foto="andreas-portrait" format="portrait" pos="top" sizes="(min-width: 60rem) 35vw, 100vw" />
            <p class="maker__name">{{ $c['owner'] }}<br>{{ $c['owner_role'] }}</p>
        </div>
        <div class="maker__text reveal reveal-delay-1">
            <x-ui.kicker>Der Messerschmied</x-ui.kicker>
            <h2 id="maker-title" class="h-section">{{ $c['owner'] }} und seine Werkstatt</h2>
            <ul class="punze-row" role="list">
                <li><x-ui.punze key="Beruf">{{ $c['owner_role'] }}</x-ui.punze></li>
                <li><x-ui.punze key="Mitglied">{{ $c['chamber'] }}</x-ui.punze></li>
                <li><x-ui.punze key="Werkstatt">{{ $c['address']['district'] }}</x-ui.punze></li>
            </ul>
            <p class="lead">
                In der Werkstatt in {{ $c['address']['district'] }} bei {{ $c['address']['locality'] }} entstehen die Messer
                der Manufaktur – von der nummerierten Edition bis zur Einzelanfertigung.
            </p>
            <x-ui.missing silent>Kurzbiografie, Ausbildungsweg, seit wann selbstständig – nur belegte Angaben</x-ui.missing>
            <div class="maker__links">
                <a class="link-arrow" href="/manufaktur/">Mehr über die Manufaktur <span aria-hidden="true">→</span></a>
                <a class="link-arrow" href="/presse/">Presseberichte <span aria-hidden="true">→</span></a>
                <a class="link-arrow" href="/kunsthandwerkermarkt/">Kunsthandwerkermarkt <span aria-hidden="true">→</span></a>
            </div>
            <x-ui.media foto="werkstatt-halle" format="wide" sizes="(min-width: 60rem) 55vw, 100vw" />
        </div>
    </div>
</section>
