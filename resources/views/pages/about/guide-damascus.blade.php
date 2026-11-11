@php $damast = $courses->where('category', 'damast'); $faqsDamast = \App\Support\Faqs::damascus(); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Damaststahl erklärt: Herstellung, Muster und Pflege" kicker="Ratgeber · Material" foto="damast-detail"
        lead="Warum hat jede Damastklinge ihr eigenes Muster? Wie entsteht Damaststahl in der Esse – und was braucht eine Damastklinge an Pflege? Ein verständlicher Überblick aus dem Schmiedehandwerk." />

    <article class="block article" aria-labelledby="page-title">
        <div class="container article__grid">
            <nav class="article__toc" aria-label="Inhalt">
                <p class="article__toc-title">Inhalt</p>
                <ol role="list">
                    <li><a href="#was-ist-damaststahl">Was ist Damaststahl?</a></li>
                    <li><a href="#herstellung">So entsteht Damaststahl</a></li>
                    <li><a href="#muster">Woher kommt das Muster?</a></li>
                    <li><a href="#eigenschaften">Eigenschaften im Vergleich</a></li>
                    <li><a href="#pflege">Damast richtig pflegen</a></li>
                    <li><a href="#selbst-schmieden">Damast selbst schmieden</a></li>
                    <li><a href="#faq">Häufige Fragen</a></li>
                </ol>
            </nav>

            <div class="prose article__body">
                <p class="article__summary"><strong>Kurz gesagt:</strong> Damaststahl ist ein Verbund aus mindestens zwei Stahlsorten, die in der Esse verschweißt, ausgeschmiedet und gefaltet werden. Erst das Ätzen macht die Lagen als Muster sichtbar – deshalb gleicht keine Damastklinge der anderen.</p>

                <h2 id="was-ist-damaststahl">Was ist Damaststahl?</h2>
                <p>Mit Damaststahl ist heute meist <strong>Schweißverbundstahl</strong> gemeint – auch Schweißdamast genannt. Er besteht aus mehreren Lagen unterschiedlicher Stähle, die der Schmied bei hoher Temperatur dauerhaft miteinander verbindet. Das Ergebnis ist ein einziges Stück Stahl, in dem die Lagen erhalten bleiben.</p>
                <p>Davon zu unterscheiden ist der historische <em>Wootz</em>- oder Tiegelstahl, dessen Muster aus seiner Kristallstruktur entsteht und nicht aus verschweißten Lagen. Wenn heute von Damastmessern die Rede ist, geht es fast immer um Schweißverbundstahl.</p>

                <h2 id="herstellung">So entsteht Damaststahl – Schritt für Schritt</h2>
                <ol class="numbered">
                    <li><strong>Paket schichten:</strong> Bleche aus unterschiedlichen Stahlsorten werden abwechselnd aufeinandergelegt.</li>
                    <li><strong>Feuerverschweißen:</strong> In der Esse wird das Paket auf Schweißhitze gebracht und mit Hammerschlägen verbunden, bis die Lagen nicht mehr trennbar sind.</li>
                    <li><strong>Ausschmieden und Falten:</strong> Der Block wird gestreckt, geteilt und erneut aufeinandergeschweißt. Mit jedem Falten vervielfacht sich die Zahl der Lagen.</li>
                    <li><strong>Muster gestalten:</strong> Verdrehen (Torsion), Einschneiden oder Stauchen verändern den Verlauf der Lagen und damit das spätere Bild.</li>
                    <li><strong>Klinge schmieden und härten:</strong> Aus dem Damastblock wird die Klinge geformt, gehärtet und angelassen – wie bei jeder geschmiedeten Klinge.</li>
                    <li><strong>Schleifen und Ätzen:</strong> Nach dem Schliff wird die Klinge geätzt. Jetzt erst zeigt sich das Muster.</li>
                </ol>
                <p>Wie diese Schritte zusammenhängen, zeigt auch der allgemeine Ablauf <a href="/manufaktur/">vom Stahl zum Messer</a> auf der Manufaktur-Seite.</p>

                <h2 id="muster">Woher kommt das Muster?</h2>
                <p>Die unterschiedlichen Stähle reagieren unterschiedlich stark auf die Ätzlösung. Die einen Lagen werden dunkel, die anderen bleiben hell – so entstehen Linien, Wellen oder Rosen auf der Klinge.</p>
                <h3>Was das Muster bestimmt</h3>
                <ul>
                    <li><strong>Lagenzahl:</strong> wenige Lagen ergeben breite, kräftige Linien, viele Lagen ein feines Muster.</li>
                    <li><strong>Technik:</strong> Torsion erzeugt spiralige Bilder, Einschnitte und Stauchungen wilde oder rosenartige Formen.</li>
                    <li><strong>Schliff:</strong> Wo und wie tief die Klinge geschliffen wird, legt unterschiedliche Lagen frei.</li>
                </ul>
                <p>Weil jede dieser Entscheidungen von Hand fällt, ist jede Damastklinge ein Unikat – ein Grund, warum Damast für Einzelstücke wie die <a href="/klappmesser/">Damast-Klappmesser der Edition 100</a> so beliebt ist.</p>

                <h2 id="eigenschaften">Eigenschaften im Vergleich</h2>
                <div class="table-scroll" tabindex="0" role="region" aria-label="Vergleich Damast und Monostahl">
                    <table class="compare compare--compact">
                        <thead><tr><th scope="col">Merkmal</th><th scope="col">Damaststahl</th><th scope="col">Klinge aus einem Stahl</th></tr></thead>
                        <tbody>
                            <tr><th scope="row">Aufbau</th><td>mehrere verschweißte Stahlsorten</td><td>eine Stahlsorte</td></tr>
                            <tr><th scope="row">Optik</th><td>individuelles Muster, jede Klinge einzigartig</td><td>gleichmäßige Oberfläche</td></tr>
                            <tr><th scope="row">Schneidleistung</th><td>abhängig von Stählen und Wärmebehandlung</td><td>abhängig von Stahl und Wärmebehandlung</td></tr>
                            <tr><th scope="row">Aufwand</th><td>hoch: Schweißen, Falten, Ätzen</td><td>geringer</td></tr>
                            <tr><th scope="row">Pflege</th><td>oft nicht rostfrei → abtrocknen, einölen</td><td>je nach Stahl</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Wichtig: Damast ist nicht automatisch „schärfer“. Die Schneidleistung entsteht durch die gewählten Stähle und eine gute Wärmebehandlung – das Muster ist das sichtbare Zeichen der Handarbeit.</p>

                <h2 id="pflege">Damast richtig pflegen</h2>
                <p>Viele Damaststähle enthalten kohlenstoffreiche, nicht rostfreie Lagen. Mit wenigen Handgriffen bleibt die Klinge trotzdem lange schön:</p>
                <ul>
                    <li>Nach dem Schneiden mit warmem Wasser abspülen und <strong>sofort abtrocknen</strong>.</li>
                    <li><strong>Nie in die Spülmaschine</strong> – Hitze und Reiniger greifen Klinge und Griff an.</li>
                    <li>Die Klinge gelegentlich <strong>dünn einölen</strong>, vor allem vor längerer Lagerung.</li>
                    <li>Auf Holz oder Kunststoff schneiden, nicht auf Glas oder Stein.</li>
                </ul>
                <p>Mehr dazu im Ratgeber <a href="/ratgeber/messer-pflegen/">Messer pflegen: reinigen, schärfen, lagern</a>.</p>

                <h2 id="selbst-schmieden">Damast selbst schmieden</h2>
                <p>Am eindrucksvollsten versteht man Damast, wenn man ihn selbst an Esse und Amboss erlebt. In der Messermanufaktur Ulbrich – Schmiedehandwerk seit {{ $c['heritage']['since'] }}, heute in {{ $c['heritage']['generation'] }}. Generation – gibt es dafür zwei Kursformate:</p>
                <ul>
                    @foreach ($damast as $course)
                        <li><a href="/schmiedekurse/#{{ \Illuminate\Support\Str::slug($course->name) }}">{{ $course->name }}</a>: {{ $course->duration_label }}@if($course->blade_length_cm), Klinge bis {{ $course->blade_length_cm }} cm @endif, ab {{ number_format($course->price_from, 0, ',', '.') }} € pro Person</li>
                    @endforeach
                </ul>
                <div class="btn-row mt-5">
                    <x-ui.button href="/schmiedekurse/buchen/?kurs=damast-messerkurs" variant="straw">Damast-Kurs buchen</x-ui.button>
                    <x-ui.button href="/schmiedekurse/vergleich/" variant="secondary">Alle Kurse vergleichen</x-ui.button>
                </div>

                <h2 id="faq">Häufige Fragen zu Damaststahl</h2>
                @include('pages.partials.faq', ['faqs' => $faqsDamast])
            </div>
        </div>
    </article>

    <x-page.cta title="Lust auf deine eigene Damastklinge?" text="Im Damast-Messerkurs schmiedest du sie selbst – angeleitet von Messerschmied {{ $c['owner'] }}." />
</x-layouts.site>
