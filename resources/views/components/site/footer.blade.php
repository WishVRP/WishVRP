@php
    $c = config('ulbrich');
    $a = $c['address'];
@endphp
<footer class="site-footer is-dark">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <x-ui.wordmark-mark class="site-footer__logo" />
                <p class="site-footer__claim">Feuer, Stahl und Handarbeit aus {{ $c['address']['district'] }}.</p>
            </div>
            <div class="btn-row">
                <x-ui.button href="/schmiedekurse/buchen/" variant="straw">Kurstermin finden</x-ui.button>
                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="secondary" :arrow="false">{{ $c['phone']['display'] }}</x-ui.button>
            </div>
        </div>
        <div class="site-footer__grid">
            <div>
                <h2>Kontakt</h2>
                <address>
                    {{ $c['name'] }}<br>
                    {{ $c['owner'] }}, {{ $c['owner_role'] }}<br>
                    {{ $a['street'] }}<br>
                    {{ $a['postal_code'] }} {{ $a['locality'] }}<br>
                    @if ($c['email'])
                        <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>
                    @else
                        <x-ui.missing silent ask="info@ oder gmx festlegen">E-Mail-Adresse</x-ui.missing>
                    @endif
                </address>
            </div>
            <nav aria-labelledby="footer-kurse">
                <h2 id="footer-kurse">Schmiedekurse</h2>
                <ul role="list">
                    <li><a href="/schmiedekurse/">Alle Schmiedekurse</a></li>
                    <li><a href="/schmiedekurse/vergleich/">Kurse vergleichen</a></li>
                    <li><a href="/schmiedekurse/heilbronn-region/">Schmiedekurs Region Heilbronn</a></li>
                    <li><a href="/schmiedekurse/buchen/">Termin buchen</a></li>
                    <li><a href="/geschenke/schmiedekurs-gutschein/">Kurs-Gutschein</a></li>
                    <li><a href="/erlebnisse/">Erlebnisse</a></li>
                    <li><a href="/erlebnisse/teamevent/">Firmen &amp; Gruppen</a></li>
                    <li><a href="/schmiedekurse/erfahrungen/">Kurserfahrungen</a></li>
                </ul>
            </nav>
            <nav aria-labelledby="footer-messer">
                <h2 id="footer-messer">Messer</h2>
                <ul role="list">
                    <li><a href="/klappmesser/">Edition 100</a></li>
                    <li><a href="/shop/">Shop</a></li>
                    <li><a href="/sonderanfertigungen/">Messer anfertigen lassen</a></li>
                    <li><a href="/messer-unikate/">Messer-Unikate</a></li>
                    <li><a href="/messerprojekte/">Messerprojekte</a></li>
                    <li><a href="/messer-kunst/">Messer als Handwerkskunst</a></li>
                    <li><a href="/shop/ringe/">Ringe</a></li>
                    <li><a href="/messer-gravur/">Messer mit Gravur</a></li>
                    <li><a href="/firmengeschenke/">Firmengeschenke</a></li>
                    <li><a href="/messer-schaerfen-lassen/">Messer schärfen lassen</a></li>
                    <li><a href="/messer-reparieren-lassen/">Messer reparieren lassen</a></li>
                    <li><a href="/versand/">Versand &amp; Abholung</a></li>
                    <li><a href="/garantie/">2 Jahre Garantie</a></li>
                </ul>
            </nav>
            <nav aria-labelledby="footer-manufaktur">
                <h2 id="footer-manufaktur">Manufaktur</h2>
                <ul role="list">
                    <li><a href="/manufaktur/">Über die Manufaktur</a></li>
                    <li><a href="/presse/">Presse</a></li>
                    <li><a href="/ratgeber/">Ratgeber</a></li>
                    <li><a href="/geschenke/">Geschenke</a></li>
                    <li><a href="/partner/">Partner</a></li>
                    <li><a href="/kunsthandwerkermarkt/">Kunsthandwerkermarkt</a></li>
                    <li><a href="/en/" hreflang="en" lang="en">English</a></li>
                </ul>
            </nav>
        </div>
        <div class="site-footer__legal">
            <span>© {{ date('Y') }} {{ $c['name'] }} · <x-ui.tax-note /></span>
            <ul role="list">
                <li><a href="/impressum/">Impressum</a></li>
                <li><a href="/datenschutz/">Datenschutz</a></li>
                <li><a href="/agb/">AGB</a></li>
                <li><a href="/widerruf/">Widerrufsbelehrung</a></li>
                <li><a href="/widerrufen/">Vertrag widerrufen</a></li>
                <li><a href="/kursbedingungen/">Kursbedingungen</a></li>
            </ul>
        </div>
    </div>
</footer>
