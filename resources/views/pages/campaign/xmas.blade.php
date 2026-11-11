@php
    $x = $c['xmas'];
    $s = $x['surprise'];
    $active = $x['enabled'] && now()->lte(\Illuminate\Support\Carbon::parse($x['ends_on'])->endOfDay());
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schneeballschlacht mit dem Weihnachtsmann" kicker="Weihnachtsaktion" tone="blue"
        lead="Der Weihnachtsmann fliegt über die Werkstatt. Triff ihn in {{ $x['seconds'] }} Sekunden so oft du kannst mit Schneebällen – am Ende wartet eine Überraschung auf dich." />

    <section class="block xmas" aria-labelledby="xmas-h">
        <div class="container">
            <h2 id="xmas-h" class="sr-only">Spiel</h2>
            @if ($active)
                <div class="xmas__game" data-xmas-game data-seconds="{{ $x['seconds'] }}">
                    <canvas class="xmas__canvas" width="960" height="600" aria-label="Spielfeld: Weihnachtsmann mit Schneebällen treffen" role="img"></canvas>
                    <div class="xmas__hud" aria-live="polite">
                        <span>Treffer: <b data-xmas-score>0</b></span>
                        <span>Zeit: <b data-xmas-time>{{ $x['seconds'] }}</b> s</span>
                        <span>Rekord: <b data-xmas-best>–</b></span>
                    </div>
                    <div class="xmas__overlay" data-xmas-start>
                        <p class="xmas__title">Bereit?</p>
                        <p>Tippe oder klicke dorthin, wo der Weihnachtsmann gleich ist. Mit der Tastatur: ← → zielen, Leertaste werfen.</p>
                        <button type="button" class="btn btn--primary btn--lg" data-xmas-play>Spiel starten</button>
                    </div>
                    <div class="xmas__overlay" data-xmas-end hidden>
                        <p class="xmas__title">Ho ho ho! <span data-xmas-final>0</span> Treffer</p>
                        <div class="xmas__surprise">
                            <p class="xmas__kicker">Deine Überraschung</p>
                            @if ($s['title'])
                                <p class="xmas__prize">{{ $s['title'] }}</p>
                                @if ($s['text'])<p>{{ $s['text'] }}</p>@endif
                                @if ($s['code'])<p class="xmas__code">Code: <b>{{ $s['code'] }}</b></p>@endif
                            @else
                                <p class="xmas__prize"><x-ui.missing label="Die Überraschung wird bald verraten" ask="Überraschung der Weihnachtsaktion festlegen (config ulbrich.xmas.surprise)">Weihnachtsaktion</x-ui.missing></p>
                            @endif
                        </div>
                        <div class="btn-row">
                            <button type="button" class="btn btn--secondary" data-xmas-play>Nochmal spielen</button>
                            <x-ui.button href="/geschenke/weihnachten/" variant="straw">Zu den Weihnachtsgeschenken</x-ui.button>
                        </div>
                    </div>
                </div>
                <noscript><p class="mt-4">Für das Spiel brauchst du JavaScript. Die <a href="/geschenke/weihnachten/">Weihnachtsgeschenke</a> findest du auch so.</p></noscript>
            @else
                <x-page.note title="Die Weihnachtsaktion ist vorbei">Danke fürs Mitspielen! Geschenkideen findest du bei den <a href="/geschenke/weihnachten/">Weihnachtsgeschenken</a>.</x-page.note>
            @endif

            <div class="prose mt-8">
                <h2>So funktioniert die Weihnachtsaktion</h2>
                <ul>
                    <li>Du hast {{ $x['seconds'] }} Sekunden. Jeder Treffer zählt.</li>
                    <li>Mit jedem Treffer wird der Weihnachtsmann ein bisschen schneller.</li>
                    <li>Nach dem Spiel siehst du deine Überraschung – egal, wie oft du getroffen hast.</li>
                    <li>Die Aktion läuft bis zum {{ \Illuminate\Support\Carbon::parse($x['ends_on'])->locale('de')->isoFormat('D. MMMM YYYY') }}.</li>
                </ul>
                <p>Noch auf der Suche nach einem Geschenk? Ein <a href="/klappmesser/">Damast-Klappmesser der Edition 100</a>, ein <a href="/geschenke/schmiedekurs-gutschein/">Schmiedekurs-Gutschein</a> oder alle Ideen bei den <a href="/geschenke/weihnachten/">Weihnachtsgeschenken</a>.</p>
            </div>
        </div>
    </section>

    <x-page.cta title="Fragen zu deinem Weihnachtsgeschenk?" />
</x-layouts.site>
