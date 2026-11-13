@php $faqsCare = \App\Support\Faqs::care(); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Messer pflegen: reinigen, schärfen, richtig lagern" kicker="Ratgeber · Pflege" foto="klappmesser-geschlossen"
        lead="Ein handgefertigtes Messer begleitet dich viele Jahre – wenn du ein paar einfache Regeln beachtest. Hier sind die wichtigsten, kurz und praktisch." />

    <article class="block article" aria-labelledby="page-title">
        <div class="container article__grid">
            <nav class="article__toc" aria-label="Inhalt">
                <p class="article__toc-title">Inhalt</p>
                <ol role="list">
                    <li><a href="#reinigen">Richtig reinigen</a></li>
                    <li><a href="#schneidbrett">Das passende Schneidbrett</a></li>
                    <li><a href="#schaerfen">Abziehen und schärfen</a></li>
                    <li><a href="#lagern">Richtig lagern</a></li>
                    <li><a href="#griff">Griff und Klappmesser</a></li>
                    <li><a href="#faq">Häufige Fragen</a></li>
                </ol>
            </nav>

            <div class="prose article__body">
                <p><a class="btn btn--secondary" href="/downloads/pflegeanleitung-messermanufaktur-ulbrich.pdf" download>Pflegeanleitung als PDF (zum Ausdrucken)</a></p>
                <p class="article__summary"><strong>Die vier Grundregeln:</strong> von Hand spülen und sofort abtrocknen · auf Holz oder Kunststoff schneiden · regelmäßig abziehen, bei Bedarf auf dem Stein schärfen · so lagern, dass die Schneide nichts berührt.</p>

                <h2 id="reinigen">Messer richtig reinigen</h2>
                <ol class="numbered">
                    <li>Direkt nach dem Gebrauch mit warmem Wasser und etwas Spülmittel abspülen.</li>
                    <li>Mit einem Tuch vom Rücken zur Schneide hin abwischen – nie mit der Hand an der Schneide entlang.</li>
                    <li><strong>Sofort abtrocknen.</strong> Besonders bei nicht rostfreiem Stahl und Damast.</li>
                </ol>
                <p><strong>Keine Spülmaschine:</strong> Hitze, aggressive Reiniger und das Anschlagen an anderem Geschirr stumpfen die Schneide ab und können Holzgriffe aufquellen oder reißen lassen.</p>

                <h2 id="schneidbrett">Das passende Schneidbrett</h2>
                <div class="table-scroll" tabindex="0" role="region" aria-label="Schneidunterlagen im Vergleich">
                    <table class="compare compare--compact">
                        <thead><tr><th scope="col">Unterlage</th><th scope="col">Für die Schneide</th></tr></thead>
                        <tbody>
                            <tr><th scope="row">Holz (Stirnholz, Längsholz)</th><td>schonend – gut geeignet</td></tr>
                            <tr><th scope="row">Kunststoff</th><td>schonend – gut geeignet</td></tr>
                            <tr><th scope="row">Glas, Stein, Keramik</th><td>sehr hart – stumpft ab, Ausbrüche möglich</td></tr>
                            <tr><th scope="row">Teller, Metall</th><td>ungeeignet</td></tr>
                        </tbody>
                    </table>
                </div>

                <h2 id="schaerfen">Abziehen und schärfen</h2>
                <h3>Abziehen – zwischendurch</h3>
                <p>Beim Schneiden legt sich die feine Schneide mit der Zeit leicht um. Mit einem Wetzstab oder einem feinen Stein lässt sie sich wieder aufrichten. Das erhält die Schärfe, ersetzt aber kein Schleifen.</p>
                <h3>Schärfen – wenn das Messer nicht mehr greift</h3>
                <p>Rutscht die Klinge über eine Tomatenhaut, statt sofort zu greifen, ist Nachschärfen fällig. Am schonendsten geht das auf einem Schleifstein: zuerst eine gröbere Körnung, dann eine feine – mit gleichmäßigem Winkel und wenig Druck.</p>
                <p>Bei einem wertvollen Einzelstück oder einer Damastklinge ist es sinnvoll, beim Messerschmied nachzufragen, statt zu experimentieren: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.</p>

                <h2 id="lagern">Messer richtig lagern</h2>
                <ul>
                    <li><strong>Messerblock</strong> – der Klassiker für die Küche.</li>
                    <li><strong>Magnetleiste</strong> – Klinge vorsichtig mit dem Rücken zuerst ansetzen.</li>
                    <li><strong>Scheide oder Klingenschutz</strong> – für den Transport und die Schublade.</li>
                </ul>
                <p>Lose in der Schublade schlagen Klingen aneinander und werden schnell stumpf – und das Hineingreifen ist gefährlich.</p>

                <h2 id="griff">Griff und Klappmesser</h2>
                <p>Holzgriffe freuen sich über gelegentliches Einölen mit einem lebensmittelechten Öl. Bei <a href="/klappmesser/">Klappmessern</a> gehört Schmutz nicht ins Gelenk: nach dem Gebrauch abwischen, trocknen und die Achse bei Bedarf mit einem Tropfen Öl gangbar halten.</p>
                <p>Mehr über Klingen mit Muster erfährst du im Ratgeber <a href="/ratgeber/damastmesser/">Damaststahl erklärt</a>.</p>

                <h2 id="faq">Häufige Fragen zur Messerpflege</h2>
                @include('pages.partials.faq', ['faqs' => $faqsCare])
            </div>
        </div>
    </article>

    <x-page.cta title="Das Messer zur Pflege: am liebsten dein eigenes." text="Schmiede es selbst im Schmiedekurs – oder wähle ein Messer aus der Manufaktur." />
</x-layouts.site>
