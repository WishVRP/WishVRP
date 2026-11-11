{{-- Shop-Kategorie: eigene Texte (keine Vorlagen-Dublette), Bestellung aktuell per Anfrage --}}
@php
    $cats = [
        'damast' => ['h1' => 'Damastmesser aus der Manufaktur', 'kicker' => 'Damastmesser', 'foto' => 'damast-detail',
            'lead' => 'Damastmesser mit sichtbarer Klingenzeichnung – von Hand gefertigt von Messerschmied '.$c['owner'].'. Weil jedes Stück ein Einzelstück ist, klären wir Preis und Verfügbarkeit persönlich.',
            'points' => ['Klinge aus Damaststahl: das Muster entsteht aus verschweißten Stahllagen und macht jedes Messer einzigartig', 'Griff und Form unterscheiden sich von Stück zu Stück', 'Auch als limitiertes Damast-Klappmesser der Edition 100 erhältlich'],
            'links' => [['Was ist ein Damastmesser?', '/ratgeber/damastmesser/'], ['Echtes Damastmesser erkennen', '/ratgeber/echtes-damastmesser-erkennen/'], ['Edition 100', '/klappmesser/'], ['Damastmesser selbst schmieden', '/schmiedekurse/damast-messerkurs/']]],
        'kueche' => ['h1' => 'Küchen- und Kochmesser', 'kicker' => 'Küchenmesser', 'foto' => 'klappmesser-hell',
            'lead' => 'Handgefertigte Küchen- und Kochmesser aus der Messermanufaktur Ulbrich. Welche Form zu dir passt, hängt davon ab, was du schneidest – das klären wir gemeinsam vor dem Kauf.',
            'points' => ['Form und Klingenlänge nach Einsatz wählen – Gemüse, Fleisch, Brot', 'Pflege einplanen: von Hand spülen, sofort abtrocknen', 'Kein passendes Stück dabei? Eine Sonderanfertigung ist möglich'],
            'links' => [['Welches Kochmesser passt zu mir?', '/ratgeber/welches-kochmesser/'], ['Messerformen: Santoku, Gyuto, Nakiri', '/ratgeber/messerformen/'], ['Messer pflegen', '/ratgeber/messer-pflege/'], ['Sonderanfertigung', '/sonderanfertigungen/']]],
        'jagd' => ['h1' => 'Jagdmesser aus der Manufaktur', 'kicker' => 'Jagdmesser', 'foto' => 'klappmesser-stehend',
            'lead' => 'Jagdmesser aus der Messermanufaktur Ulbrich. Eigenschaften und Eignung für dein Vorhaben besprechen wir vor einer Bestellung persönlich.',
            'points' => ['Eignung, Form und Griff vor dem Kauf persönlich klären', 'Verfügbarkeit einzelner Stücke auf Anfrage', 'Ein Jagdmesser nach deinen Vorgaben lässt sich anfertigen'],
            'links' => [['Handgefertigtes Messer kaufen', '/ratgeber/messer-kaufen/'], ['Sonderanfertigung', '/sonderanfertigungen/'], ['Messer-Unikate', '/messer-unikate/']]],
        'einzel' => ['h1' => 'Messer-Einzelstücke mit eigener Handschrift', 'kicker' => 'Einzelstücke', 'foto' => 'klappmesser-rotholz',
            'lead' => 'Einzelstücke aus der Werkstatt von '.$c['owner'].': Klinge und Griff als Gesamtgestaltung, jedes Messer nur einmal. Verfügbarkeit und Preis erfährst du auf Anfrage.',
            'points' => ['Individuelle Gestaltung von Klinge und Griff', 'Jedes Stück gibt es nur einmal', 'Messer als Handwerkskunst – mehr über die Gestaltung erfährst du auf der Seite „Messer als Handwerkskunst“'],
            'links' => [['Messer als Handwerkskunst', '/messer-kunst/'], ['Messer-Unikate', '/messer-unikate/'], ['Messerprojekte', '/messerprojekte/']]],
    ];
    $cat = $cats[$page['category']];
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$cat['h1']" :kicker="$cat['kicker']" tone="brown" :foto="$cat['foto']" :lead="$cat['lead']">
        <x-slot:actions>
            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">Verfügbarkeit anfragen</x-ui.button>
            <x-ui.button href="/shop/" variant="secondary" size="lg">Alle Messer</x-ui.button>
        </x-slot:actions>
    </x-page.hero>

    <x-page.section title="Gut zu wissen" kicker="{{ $cat['kicker'] }}" kicker-color="brown">
        <div class="split">
            <ul class="check-list reveal" role="list">
                @foreach ($cat['points'] as $pt)<li>{{ $pt }}</li>@endforeach
            </ul>
            <div class="reveal reveal-delay-1">
                <x-page.note title="So bestellst du">
                    Einzelstücke haben keinen festen Listenpreis. Schreib an <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a> oder ruf an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>. Bezahlt wird per Überweisung, bei Abholung auch bar oder mit Karte; der Auftrag wird nach geprüftem Zahlungseingang bestätigt. Preise: <x-ui.tax-note />.
                </x-page.note>
                <div class="link-list mt-5">
                    @foreach ($cat['links'] as [$label, $href])<a href="{{ $href }}">{{ $label }} <span aria-hidden="true">→</span></a>@endforeach
                </div>
            </div>
        </div>
    </x-page.section>

    <section class="block block--sunken" aria-labelledby="lieferung-h">
        <div class="container">
            <h2 id="lieferung-h" class="h-section">Versand, Abholung &amp; Zahlung</h2>
            @include('pages.partials.delivery')
        </div>
    </section>

    <x-page.cta title="Ein Messer im Kopf?" text="Beschreib, wofür du es brauchst – {{ $c['owner'] }} sagt dir, welches Stück passt oder ob eine Anfertigung sinnvoll ist." />
</x-layouts.site>
