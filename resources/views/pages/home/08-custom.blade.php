<section class="block" aria-labelledby="custom-title">
    <div class="container custom__grid">
        <x-ui.media foto="griff-detail" format="portrait" class="reveal" sizes="(min-width: 60rem) 45vw, 100vw" />
        <div class="reveal reveal-delay-1">
            <x-ui.kicker color="brown">Sonderanfertigungen</x-ui.kicker>
            <h2 id="custom-title" class="h-section">Ein Messer nach deinen Vorstellungen</h2>
            <p class="lead mt-5">Klingenform, Länge, Griff: Wenn du ein bestimmtes Messer im Kopf hast, fertigt {{ $c['owner'] }} es auf Anfrage.</p>
            <ol class="steps" role="list">
                <li><span><b>Du beschreibst dein Messer</b><span>Verwendung, Form, Material – Skizzen und Fotos helfen.</span></span></li>
                <li><span><b>Gemeinsame Abstimmung</b><span>Machbarkeit, Details und Angebot werden besprochen.</span></span></li>
                <li><span><b>Das Messer entsteht in der Werkstatt</b><span>Von Hand geschmiedet und fertiggestellt.</span></span></li>
            </ol>
            <x-ui.missing silent>Ablauf/Lieferzeit/Preisrahmen der Anfertigung bestätigen</x-ui.missing>
            <div class="btn-row mt-5">
                <x-ui.button href="/sonderanfertigungen/" variant="brown">Anfertigung anfragen</x-ui.button>
                <a class="link-arrow" href="/messerprojekte/">Bisherige Projekte ansehen <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </div>
</section>
