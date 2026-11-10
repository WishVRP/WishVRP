@php
    $c = config('ulbrich');
    $a = $c['address'];
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div>
                <p class="site-footer__name">{{ $c['name'] }}</p>
                <address>
                    {{ $c['owner'] }}, {{ $c['owner_role'] }}<br>
                    {{ $a['street'] }}<br>
                    {{ $a['postal_code'] }} {{ $a['locality'] }} ({{ $a['district'] }})<br>
                    Telefon <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a><br>
                    @if ($c['email'])
                        E-Mail <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>
                    @else
                        E-Mail <x-ui.missing ask="info@ oder gmx festlegen">E-Mail-Adresse</x-ui.missing>
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
                </ul>
            </nav>
            <nav aria-labelledby="footer-manufaktur">
                <h2 id="footer-manufaktur">Manufaktur</h2>
                <ul role="list">
                    <li><a href="/manufaktur/">Über die Manufaktur</a></li>
                    <li><a href="/presse/">Presse</a></li>
                    <li><a href="/ratgeber/">Ratgeber</a></li>
                    <li><a href="/geschenke/">Geschenke</a></li>
                    <li><a href="/erlebnisse/">Erlebnisse</a></li>
                    <li><a href="/partner/">Partner</a></li>
                    <li><a href="/kunsthandwerkermarkt/">Kunsthandwerkermarkt</a></li>
                </ul>
            </nav>
        </div>
        <div class="site-footer__legal">
            <x-ui.tax-note />
            <ul role="list">
                <li><a href="/impressum/">Impressum</a></li>
                <li><a href="/datenschutz/">Datenschutz</a></li>
                <li><a href="/agb/">AGB</a></li>
                <li><a href="/widerruf/">Widerruf</a></li>
                <li><a href="/kursbedingungen/">Kursbedingungen</a></li>
            </ul>
        </div>
    </div>
</footer>
