@php
    $x = $c['xmas'];
    $active = \App\Models\XmasEntry::campaignActive();
    $until = \App\Models\XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY');
    $tiers = collect($x['tiers'])->map(fn ($t) => ['min' => $t['min'], 'percent' => min($t['percent'], $x['max_percent'])]);
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schneeballschlacht mit dem Weihnachtsmann" kicker="Weihnachtsaktion" tone="blue"
        lead="Der Weihnachtsmann fliegt über die Werkstatt. Triff ihn in {{ $x['seconds'] }} Sekunden so oft du kannst mit Schneebällen – je mehr Treffer, desto mehr Rabatt: bis zu {{ $x['max_percent'] }} %. Jede Person kann einmal mitspielen." />

    <section class="block xmas" aria-labelledby="xmas-h">
        <div class="container">
            <h2 id="xmas-h" class="sr-only">Spiel</h2>
            @if ($active)
                <div class="xmas__game" data-xmas-game data-seconds="{{ $x['seconds'] }}" data-start="/weihnachtsaktion/start/" data-finish="/weihnachtsaktion/ergebnis/" data-until="{{ $until }}">
                    <canvas class="xmas__canvas" width="960" height="600" aria-label="Spielfeld: Weihnachtsmann mit Schneebällen treffen" role="img"></canvas>
                    <div class="xmas__hud" aria-live="polite">
                        <span>Treffer: <b data-xmas-score>0</b></span>
                        <span>Zeit: <b data-xmas-time>{{ $x['seconds'] }}</b> s</span>
                        <span>Rekord: <b data-xmas-best>–</b></span>
                    </div>
                    <div class="xmas__overlay" data-xmas-start>
                        <p class="xmas__title">Einmal spielen, Rabatt sichern</p>
                        <form class="xmas__form" data-xmas-form novalidate>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <label class="field">
                                <span class="field__label">Deine E-Mail-Adresse (dorthin schicken wir deinen Code)</span>
                                <input type="email" name="email" required maxlength="190" autocomplete="email">
                            </label>
                            <label class="check">
                                <input type="checkbox" name="conditions" value="1" required>
                                <span>Ich akzeptiere die <a href="#teilnahmebedingungen">Teilnahmebedingungen</a> (nur eine Teilnahme pro Person) und den <a href="/datenschutz/">Datenschutzhinweis</a>.</span>
                            </label>
                            <p class="xmas__error" data-xmas-error role="alert" hidden></p>
                            <button type="submit" class="btn btn--primary btn--lg">Spiel starten – {{ $x['seconds'] }} Sekunden</button>
                        </form>
                        <p class="xmas__hint">Tippe oder klicke dorthin, wo der Weihnachtsmann gleich ist. Tastatur: ← → zielen, Leertaste werfen. Mit dem Start zählt deine Teilnahme.</p>
                    </div>
                    <div class="xmas__overlay" data-xmas-end hidden>
                        <p class="xmas__title" data-xmas-headline>Ho ho ho!</p>
                        <div class="xmas__surprise">
                            <p class="xmas__kicker">Deine Überraschung</p>
                            <p class="xmas__prize" data-xmas-prize>…</p>
                            <p class="xmas__code" data-xmas-code hidden>Dein Code: <b></b></p>
                            <p data-xmas-note></p>
                        </div>
                        <div class="btn-row">
                            <x-ui.button href="/geschenke/weihnachten/" variant="straw">Zu den Weihnachtsgeschenken</x-ui.button>
                            <x-ui.button href="/klappmesser/" variant="secondary">Edition 100</x-ui.button>
                        </div>
                    </div>
                </div>
                <noscript><p class="mt-4">Für das Spiel brauchst du JavaScript. Die <a href="/geschenke/weihnachten/">Weihnachtsgeschenke</a> findest du auch so.</p></noscript>
            @else
                <x-page.note title="Die Weihnachtsaktion ist vorbei">Danke fürs Mitspielen! Geschenkideen findest du bei den <a href="/geschenke/weihnachten/">Weihnachtsgeschenken</a>.</x-page.note>
            @endif

            <div class="prose mt-8">
                <h2>So viel Rabatt gibt es</h2>
                <ul>
                    @foreach ($tiers as $i => $t)
                        @php $next = $tiers[$i + 1]['min'] ?? null; @endphp
                        <li>{{ $next ? $t['min'].'–'.($next - 1) : 'ab '.$t['min'] }} Treffer: <b>{{ $t['percent'] }} % Rabatt</b></li>
                    @endforeach
                    <li>Höchstens {{ $x['max_percent'] }} % – mehr gibt es nicht, egal wie viele Treffer.</li>
                </ul>

                <h2 id="teilnahmebedingungen">Teilnahmebedingungen</h2>
                <ul>
                    <li><b>Jede Person kann nur einmal teilnehmen.</b> Die Teilnahme ist an deine E-Mail-Adresse gebunden; mit dem Start des Spiels zählt sie, auch wenn du das Spiel abbrichst oder die Seite neu lädst. Mehrfachteilnahmen, etwa mit weiteren E-Mail-Adressen, sind nicht erlaubt; so erzielte Codes können wir ablehnen.</li>
                    <li>Das Spiel dauert {{ $x['seconds'] }} Sekunden. Gewertet wird die Zahl der Treffer.</li>
                    <li>Der Rabatt beträgt je nach Treffern {{ $tiers->pluck('percent')->unique()->implode(' oder ') }} %, <b>höchstens {{ $x['max_percent'] }} %</b>.</li>
                    <li>Du bekommst einen persönlichen Code, der <b>einmal</b> bei einer Bestellung oder Kursbuchung über diese Website eingelöst werden kann – einfach im Formular ins Feld „Rabattcode“ eintragen. Gültig bis {{ $until }}.</li>
                    <li>Der Rabatt gilt auf den Preis der Bestellung bzw. Buchung ohne Versandkosten, ist nicht mit anderen Rabatten kombinierbar und wird nicht bar ausgezahlt.</li>
                    <li>Die Aktion läuft bis zum {{ \Illuminate\Support\Carbon::parse($x['ends_on'])->locale('de')->isoFormat('D. MMMM YYYY') }}. Bei Manipulation oder technischem Missbrauch behalten wir uns vor, Codes nicht anzuerkennen.</li>
                </ul>
                <p><x-ui.missing silent>Teilnahmebedingungen und Gültigkeitszeitraum vom Inhaber bestätigen und anwaltlich prüfen</x-ui.missing></p>
                <p>Noch auf der Suche nach einem Geschenk? Ein <a href="/klappmesser/">Damast-Klappmesser der Edition 100</a>, ein <a href="/geschenke/schmiedekurs-gutschein/">Schmiedekurs-Gutschein</a> oder alle Ideen bei den <a href="/geschenke/weihnachten/">Weihnachtsgeschenken</a>.</p>
            </div>
        </div>
    </section>

    <x-page.cta title="Fragen zu deinem Weihnachtsgeschenk?" />
</x-layouts.site>
