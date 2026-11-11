@php
    $x = $c['xmas'];
    $active = \App\Models\XmasEntry::campaignActive();
    $until = \App\Models\XmasEntry::validUntil()->locale('de')->isoFormat('D. MMMM YYYY');
    $tiers = collect($x['tiers'])->map(fn ($t) => ['min' => $t['min'], 'percent' => min($t['percent'], $x['max_percent'])]);
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Schneeballschlacht mit dem Weihnachtsmann" kicker="Weihnachtsaktion" tone="blue"
        lead="Der Weihnachtsmann fliegt über die Werkstatt – und er ist flink. Triff ihn in {{ $x['seconds'] }} Sekunden so oft du kannst: Wind, Wolken und Ausweichmanöver machen es dir nicht leicht. Je mehr Treffer, desto mehr Rabatt, bis zu {{ $x['max_percent'] }} %. Du hast genau einen Versuch – ohne Probelauf." />

    <section class="block xmas" aria-labelledby="xmas-h">
        <div class="container">
            <h2 id="xmas-h" class="sr-only">Spiel</h2>
            @if ($active)
                <div class="xmas__game" data-xmas-game data-seconds="{{ $x['seconds'] }}" data-register="/weihnachtsaktion/anmelden/" data-verify="/weihnachtsaktion/code/" data-start="/weihnachtsaktion/start/" data-finish="/weihnachtsaktion/ergebnis/" data-until="{{ $until }}">
                    <canvas class="xmas__canvas" width="960" height="600" aria-label="Spielfeld: Weihnachtsmann mit Schneebällen treffen" role="img"></canvas>
                    <div class="xmas__hud" aria-live="polite">
                        <span>Treffer: <b data-xmas-score>0</b></span>
                        <span>Zeit: <b data-xmas-time>{{ $x['seconds'] }}</b> s</span>
                        <span>Rekord: <b data-xmas-best>–</b></span>
                    </div>
                    <div class="xmas__overlay" data-xmas-start>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" data-xmas-csrf>
                        <div data-xmas-step="1">
                            <p class="xmas__title">Einmal spielen, Rabatt sichern</p>
                            <form class="xmas__form" data-xmas-form novalidate>
                                <label class="field">
                                    <span class="field__label">E-Mail-Adresse (für deinen Rabattcode)</span>
                                    <input type="email" name="email" required maxlength="190" autocomplete="email">
                                </label>
                                <label class="field">
                                    <span class="field__label">Handynummer (du bekommst einen SMS-Code)</span>
                                    <input type="tel" name="phone" required maxlength="40" autocomplete="tel" placeholder="0171 1234567">
                                </label>
                                <label class="check">
                                    <input type="checkbox" name="conditions" value="1" required>
                                    <span>Ich akzeptiere die <a href="#teilnahmebedingungen">Teilnahmebedingungen</a> (nur eine Teilnahme pro Person) und den <a href="/datenschutz/">Datenschutzhinweis</a>.</span>
                                </label>
                                <button type="submit" class="btn btn--primary btn--lg">SMS-Code anfordern</button>
                            </form>
                        </div>
                        <div data-xmas-step="2" hidden>
                            <p class="xmas__title">Code aus der SMS</p>
                            <p data-xmas-sms-text></p>
                            <form class="xmas__form" data-xmas-code-form novalidate>
                                <label class="field">
                                    <span class="field__label">6-stelliger Code</span>
                                    <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required class="xmas__codeinput">
                                </label>
                                <button type="submit" class="btn btn--primary btn--lg">Bestätigen</button>
                            </form>
                            <p class="xmas__hint" data-xmas-dev hidden>Testmodus (nur Entwicklung, keine echte SMS): Code <b data-xmas-dev-code></b></p>
                            <p class="xmas__hint"><button type="button" class="xmas__link" data-xmas-resend>Neuen Code anfordern</button></p>
                        </div>
                        <div data-xmas-step="3" hidden>
                            <p class="xmas__title">Bereit für deinen einen Versuch?</p>
                            <p>{{ $x['seconds'] }} Sekunden, so viele Treffer wie möglich. Die Schneebälle fliegen langsam – wirf dorthin, wo der Weihnachtsmann gleich sein wird. Achte auf den Wind, Wolken fangen Bälle ab.</p>
                            <button type="button" class="btn btn--primary btn--lg" data-xmas-go>Jetzt spielen – nur 1 Versuch</button>
                            <p class="xmas__hint">Mit dem Klick zählt deine Teilnahme. Tastatur: ← → zielen, Leertaste werfen.</p>
                        </div>
                        <p class="xmas__error" data-xmas-error role="alert" hidden></p>
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
                    <li><b>Jede Person kann nur einmal teilnehmen.</b> Die Teilnahme setzt eine Handynummer voraus, die du mit einem SMS-Code bestätigst; jede Handynummer und jede E-Mail-Adresse kann nur einmal teilnehmen. Wegwerf-Adressen sind ausgeschlossen. Mit dem Start des Spiels zählt deine Teilnahme, auch wenn du abbrichst oder die Seite neu lädst.</li>
                    <li><b>Ein Weihnachtsrabatt pro Person bzw. Haushalt.</b> Der Code ist nur zusammen mit der E-Mail-Adresse und der Handynummer einlösbar, mit denen gespielt wurde. Beim Einlösen wird geprüft, ob unter derselben Telefonnummer oder demselben Namen mit Postleitzahl schon ein Weihnachtsrabatt eingelöst wurde; dann ist keine weitere Einlösung möglich. Mehrfachteilnahmen sind nicht erlaubt; so erzielte Codes können wir ablehnen.</li>
                    <li>Je Internetanschluss sind höchstens drei Anmeldungen pro Tag möglich.</li>
                    <li>Das Spiel dauert {{ $x['seconds'] }} Sekunden. Gewertet wird die Zahl der Treffer. Es gibt keine Probe- oder Übungsrunde.</li>
                    <li>Der Rabatt beträgt je nach Treffern {{ $tiers->pluck('percent')->unique()->implode(' oder ') }} %, <b>höchstens {{ $x['max_percent'] }} %</b>.</li>
                    <li>Du bekommst einen persönlichen Code, der <b>einmal</b> bei einer Bestellung oder Kursbuchung über diese Website eingelöst werden kann – einfach im Formular ins Feld „Rabattcode“ eintragen. Gültig bis {{ $until }}. Bei der Einlösung gibst du dieselbe Handynummer an.</li>
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
