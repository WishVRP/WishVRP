@php
    $c = config('ulbrich');
    $e = $c['edition'];
    $rating = $c['google_rating'];
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" current="/">

    {{-- ============ Hero ============ --}}
    <section class="hero" aria-labelledby="hero-title">
        <div class="container">
            <div class="hero__grid">
                <div>
                    <p class="hero__maker">{{ $c['owner_role'] }} {{ $c['owner'] }} · {{ $c['place_label'] }}</p>
                    <h1 id="hero-title">Schmiedekurse und Damastmesser aus der Werkstatt von Andreas Ulbrich</h1>
                    <p class="lead">
                        Schmiede dein eigenes Messer – an einem Tag im Grundlagenkurs oder in zwei bis drei Tagen
                        aus Damaststahl. Oder wähle ein Messer, das in der Manufaktur von Hand entsteht.
                    </p>
                    <div class="btn-row">
                        <x-ui.button href="/schmiedekurse/">Schmiedekurse ansehen</x-ui.button>
                        <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="secondary">Kurs verschenken</x-ui.button>
                    </div>
                    <ul class="punze-row" role="list" aria-label="Fakten">
                        @if ($minPrice)
                            <li><x-ui.punze key="Kurse">ab {{ number_format($minPrice, 0, ',', '.') }} € p. P.</x-ui.punze></li>
                        @endif
                        <li><x-ui.punze key="Dauer">1–3 Tage</x-ui.punze></li>
                        <li><x-ui.punze key="Mitglied">{{ $c['chamber'] }}</x-ui.punze></li>
                        @if ($rating)
                            <li>
                                <x-ui.punze key="Google">{{ $rating['value'] }} ★ · {{ $rating['count'] }} Bewertungen</x-ui.punze>
                            </li>
                        @endif
                    </ul>
                    @if ($rating)
                        <p class="tech muted mt-2">
                            Google-Unternehmensprofil, Stand {{ $rating['as_of'] }}
                            @unless ($rating['profile_url']) · <x-ui.missing ask="">Link zum Profil</x-ui.missing>@endunless
                        </p>
                    @endif
                </div>
                <x-ui.photo motif="Andreas Ulbrich am Amboss, Klinge in der Zange, Hochformat" format="portrait" />
            </div>

            <x-ui.temper-scale class="hero__temper">
                Anlassfarben entstehen, wenn blanker Stahl erhitzt wird – an ihnen liest der Schmied die Temperatur ab.
                Auf dieser Website ordnen sie die drei Wege: Blau für Kurse, Braun für Messer, Violett für Gutscheine.
            </x-ui.temper-scale>
        </div>
    </section>

    {{-- ============ Drei Verkaufswege ============ --}}
    <section class="section section--sunken" aria-labelledby="wege-title">
        <div class="container">
            <x-ui.section-head title="Kurs buchen, Messer kaufen oder verschenken" id="wege-title" />

            <div class="path-grid">
                <article class="path-card" aria-labelledby="weg-kurse">
                    <p class="path-card__kind">Schmiedekurse</p>
                    <h3 id="weg-kurse">Dein eigenes Messer schmieden</h3>
                    <p class="path-card__body">
                        {{ $courses->count() }} Kursformate von einem bis drei Tagen – vom Grundlagenkurs
                        bis zum Damast Premium-Kurs mit bis zu 40 cm Klinge.
                    </p>
                    <ul class="punze-row" role="list">
                        @if ($minPrice)<li><x-ui.punze>ab {{ number_format($minPrice, 0, ',', '.') }} € p. P.</x-ui.punze></li>@endif
                        <li><x-ui.punze>Buchung online</x-ui.punze></li>
                    </ul>
                    <x-ui.button href="/schmiedekurse/">Kurse und Termine</x-ui.button>
                    <div class="path-card__links">
                        <a class="link-arrow" href="/schmiedekurse/vergleich/">Kurse vergleichen</a>
                    </div>
                </article>

                <article class="path-card path-card--knife" aria-labelledby="weg-messer">
                    <p class="path-card__kind">Messer</p>
                    <h3 id="weg-messer">{{ $e['name'] }}</h3>
                    <p class="path-card__body">
                        {{ $e['total'] }} nummerierte Stücke: {{ $e['regular'] }} reguläre Messer zu
                        {{ number_format($e['price'], 0, ',', '.') }} € und {{ $e['special'] }} Sonderstücke.
                    </p>
                    <ul class="punze-row" role="list">
                        <li><x-ui.punze>Nº ___ / {{ $e['total'] }}</x-ui.punze></li>
                        <li><x-ui.punze>{{ number_format($e['price'], 0, ',', '.') }} €</x-ui.punze></li>
                    </ul>
                    <x-ui.button href="{{ $e['url'] }}" variant="brown">Edition 100 ansehen</x-ui.button>
                    <div class="path-card__links">
                        <a class="link-arrow" href="/shop/">Zum Shop</a>
                        <a class="link-arrow" href="/sonderanfertigungen/">Messer anfertigen lassen</a>
                    </div>
                </article>

                <article class="path-card path-card--gift" aria-labelledby="weg-gutschein">
                    <p class="path-card__kind">Gutscheine</p>
                    <h3 id="weg-gutschein">Einen Schmiedekurs verschenken</h3>
                    <p class="path-card__body">
                        Der Gutschein reserviert noch keinen Platz – die beschenkte Person sucht sich
                        ihren Kurs und Termin später selbst aus.
                    </p>
                    <ul class="punze-row" role="list">
                        <li><x-ui.missing ask="">Gutscheinwerte, Gültigkeit, Versandart</x-ui.missing></li>
                    </ul>
                    <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="violet">Gutschein auswählen</x-ui.button>
                    <div class="path-card__links">
                        <a class="link-arrow" href="/geschenke/">Weitere Geschenkideen</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- ============ Kurse im Überblick ============ --}}
    <section class="section" aria-labelledby="kurse-title">
        <div class="container">
            <x-ui.section-head title="Schmiedekurse auf einen Blick" id="kurse-title" :spec="$courses->count().' Kursformate · Preise pro Person'">
                Dauer, Klingenlänge und Einstiegspreis aller Kursformate. Ablauf, Ergebnis und Voraussetzungen stehen auf der jeweiligen Kursseite.
            </x-ui.section-head>

            <table class="course-table">
                <caption>
                    „ab“-Preise pro Person. <x-ui.tax-note />
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Kurs</th>
                        <th scope="col">Dauer</th>
                        <th scope="col">Klingenlänge</th>
                        <th scope="col">Preis</th>
                        <th scope="col"><span class="visually-hidden">Link</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <th scope="row">{{ $course->name }}</th>
                            <td data-label="Dauer:">
                                @if ($course->duration_label)
                                    <span class="tech">{{ $course->duration_label }}</span>
                                @else
                                    <x-ui.missing ask="">Dauer</x-ui.missing>
                                @endif
                            </td>
                            <td>
                                @if ($course->blade_length_cm)
                                    <x-ui.dimension :cm="$course->blade_length_cm" :max="$maxBlade" />
                                @else
                                    <span class="tech muted">–</span>
                                @endif
                            </td>
                            <td class="course-table__price"><x-ui.price :amount="$course->price_from" :from="true" /></td>
                            <td>
                                <a class="link-arrow" href="{{ $course->url() }}">Details<span class="visually-hidden"> zum {{ $course->name }}</span> →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="courses__aside">
                <div class="dates" aria-labelledby="termine-title">
                    <h3 id="termine-title">Nächste Termine</h3>
                    @if ($dates->isNotEmpty())
                        <ul class="dates__list" role="list">
                            @foreach ($dates as $date)
                                <li>
                                    <span>{{ $date->starts_on->locale('de')->isoFormat('dd, D. MMM YYYY') }}</span>
                                    <span>{{ $date->course->name }}</span>
                                    <span>{{ $date->seatsLeftLabel() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="muted">
                            <x-ui.missing ask="aus dem Buchungssystem übernehmen">Aktuelle Kurstermine</x-ui.missing>
                        </p>
                    @endif
                    <div class="btn-row mt-4">
                        <x-ui.button href="/schmiedekurse/buchen/">Termin finden</x-ui.button>
                    </div>
                </div>

                <aside class="note" aria-labelledby="regeln-title">
                    <h3 id="regeln-title">Gut zu wissen vor der Buchung</h3>
                    <ul>
                        @foreach ($c['booking_rules'] as $rule)
                            <li>{{ $rule }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-3"><a href="/kursbedingungen/">Alle Kursbedingungen</a></p>
                </aside>
            </div>
        </div>
    </section>

    {{-- ============ Edition 100 ============ --}}
    <section class="section section--dark edition" aria-labelledby="edition-title">
        <div class="container">
            <div class="edition__grid">
                <x-ui.photo motif="Damast-Klappmesser Edition 100, geöffnet, Klinge mit Damastmuster, Seriennummer sichtbar" format="square" />
                <div>
                    <div class="edition__serial"><x-ui.punze size="lg">Nº ___ / {{ $e['total'] }}</x-ui.punze></div>
                    <h2 id="edition-title">{{ $e['name'] }}</h2>
                    <p class="lead">
                        Eine Auflage von {{ $e['total'] }} Damast-Klappmessern, jedes mit eigener Nummer.
                    </p>
                    <dl class="spec">
                        <div class="spec__row"><dt>Auflage</dt><dd>{{ $e['total'] }} Stück, nummeriert</dd></div>
                        <div class="spec__row"><dt>Reguläre Stücke</dt><dd>{{ $e['regular'] }} × {{ number_format($e['price'], 0, ',', '.') }} €</dd></div>
                        <div class="spec__row"><dt>Sonderstücke</dt><dd>{{ $e['special'] }} <x-ui.missing ask="">Preis/Unterschied</x-ui.missing></dd></div>
                        <div class="spec__row"><dt>Stahl</dt><dd>@if($e['steel']){{ $e['steel'] }}@else<x-ui.missing ask="">Stahlsorte/Lagen</x-ui.missing>@endif</dd></div>
                        <div class="spec__row"><dt>Maße</dt><dd>@if($e['dimensions']){{ $e['dimensions'] }}@else<x-ui.missing ask="">Klingen-/Gesamtlänge, Gewicht</x-ui.missing>@endif</dd></div>
                        <div class="spec__row"><dt>Griff</dt><dd>@if($e['handle']){{ $e['handle'] }}@else<x-ui.missing ask="">Griffmaterial</x-ui.missing>@endif</dd></div>
                    </dl>
                    <div class="btn-row">
                        <x-ui.button href="{{ $e['url'] }}" variant="straw">Zur Edition 100</x-ui.button>
                        <x-ui.button href="/shop/" variant="secondary">Alle Messer im Shop</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Anfertigung ============ --}}
    <section class="section" aria-labelledby="anfertigung-title">
        <div class="container split">
            <div>
                <h2 id="anfertigung-title">Ein Messer nach deinen Vorstellungen</h2>
                <p class="lead">
                    Klingenform, Länge, Griff: Wenn du ein bestimmtes Messer im Kopf hast, fertigt Andreas Ulbrich es auf Anfrage.
                </p>
                <p class="muted"><x-ui.missing ask="">Ablauf einer Anfertigung, typische Lieferzeit, Preisrahmen</x-ui.missing></p>
                <nav class="link-list" aria-label="Anfertigung und Projekte">
                    <a href="/sonderanfertigungen/">Messer anfertigen lassen <span class="tech">→</span></a>
                    <a href="/messer-unikate/">Messer-Unikate <span class="tech">→</span></a>
                    <a href="/messerprojekte/">Bisherige Messerprojekte <span class="tech">→</span></a>
                </nav>
            </div>
            <x-ui.photo motif="Skizze und Rohling einer Sonderanfertigung auf der Werkbank" format="wide" />
        </div>
    </section>

    {{-- ============ Manufaktur / Person ============ --}}
    <section class="section section--sunken" aria-labelledby="manufaktur-title">
        <div class="container split split--reverse">
            <div>
                <h2 id="manufaktur-title">{{ $c['owner'] }}, {{ $c['owner_role'] }}</h2>
                <ul class="punze-row mt-5" role="list">
                    <li><x-ui.punze key="Kammer">{{ $c['chamber'] }}</x-ui.punze></li>
                    <li><x-ui.punze key="Werkstatt">{{ $c['address']['district'] }}</x-ui.punze></li>
                </ul>
                <p>
                    Messerschmied in {{ $c['address']['district'] }}, {{ $c['address']['locality'] }} – Schmiedekurse, Damastmesser und Anfertigungen aus einer Werkstatt.
                    <x-ui.missing ask="">Kursort = Werkstatt bestätigen</x-ui.missing>
                </p>
                <p class="muted"><x-ui.missing>Kurzbiografie, Ausbildungsweg, seit wann selbstständig – nur belegte Angaben</x-ui.missing></p>
                <nav class="link-list" aria-label="Mehr über die Manufaktur">
                    <a href="/manufaktur/">Die Manufaktur <span class="tech">→</span></a>
                    <a href="/presse/">Presseberichte <span class="tech">→</span></a>
                </nav>
            </div>
            <x-ui.photo motif="Werkstatt in Stein am Kocher: Esse, Amboss, Schleifbock" format="wide" />
        </div>
    </section>

    {{-- ============ FAQ + Kontakt ============ --}}
    <section class="section" aria-labelledby="faq-title">
        <div class="container faq-contact">
            <div>
                <x-ui.section-head title="Häufige Fragen" id="faq-title" />
                <div class="faq">
                    @foreach ($faqs as $faq)
                        <details>
                            <summary>{{ $faq['q'] }}</summary>
                            <div class="faq__answer"><p>{{ $faq['a'] }}</p></div>
                        </details>
                    @endforeach
                </div>
                <p class="mt-5"><a class="link-arrow" href="/ratgeber/">Mehr Antworten im Ratgeber →</a></p>
            </div>

            <aside class="contact-card" id="kontakt" aria-labelledby="kontakt-title">
                <h2 id="kontakt-title">Kontakt und Anfahrt</h2>
                <address>
                    <strong>{{ $c['name'] }}</strong><br>
                    {{ $c['owner'] }}<br>
                    {{ $c['address']['street'] }}<br>
                    {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}<br>
                    Ortsteil {{ $c['address']['district'] }}
                </address>
                <dl class="spec">
                    <div class="spec__row"><dt>Telefon</dt><dd><a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></dd></div>
                    <div class="spec__row"><dt>E-Mail</dt><dd>@if($c['email'])<a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>@else<x-ui.missing ask="festlegen">E-Mail</x-ui.missing>@endif</dd></div>
                    @foreach ($c['opening_hours'] as $h)
                        <div class="spec__row"><dt>{{ $h['label'] }}</dt><dd class="tech">{{ $h['opens'] }}–{{ $h['closes'] }} Uhr</dd></div>
                    @endforeach
                </dl>
                @unless ($c['opening_hours_verified'])
                    <p class="tech muted mt-2">Zeiten laut Google-Profil – <x-ui.missing ask="">Bestätigung durch Andreas</x-ui.missing></p>
                @endunless
                <div class="btn-row">
                    <x-ui.button href="tel:{{ $c['phone']['e164'] }}" :arrow="false">Anrufen</x-ui.button>
                    <x-ui.button
                        href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($c['address']['street'].', '.$c['address']['postal_code'].' '.$c['address']['locality']) }}"
                        variant="secondary" rel="noopener" target="_blank">Route planen</x-ui.button>
                </div>
            </aside>
        </div>
    </section>

</x-layouts.site>
