{{-- Footer der englischen Seiten (/en/…). Rechtstexte gibt es nur auf Deutsch – so gekennzeichnet. --}}
@php
    $c = config('ulbrich');
    $a = $c['address'];
@endphp
<footer class="site-footer is-dark" lang="en">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <x-ui.wordmark-mark class="site-footer__logo" />
                <p class="site-footer__claim">Fire, steel and handwork from {{ $a['district'] }}, Germany.</p>
            </div>
            <div class="btn-row">
                <x-ui.button href="mailto:{{ $c['email'] }}" variant="straw">Send an enquiry</x-ui.button>
                <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="secondary" :arrow="false">{{ $c['phone']['intl'] }}</x-ui.button>
            </div>
        </div>
        <div class="site-footer__grid">
            <div>
                <h2>Contact</h2>
                <address>
                    {{ $c['name'] }}<br>
                    {{ $c['owner'] }}, bladesmith<br>
                    {{ $a['street'] }}<br>
                    {{ $a['postal_code'] }} {{ $a['locality'] }}, Germany<br>
                    <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>
                </address>
            </div>
            <nav aria-labelledby="footer-en-knives">
                <h2 id="footer-en-knives">Knives</h2>
                <ul role="list">
                    <li><a href="/en/knives/">Handmade knives</a></li>
                    <li><a href="/en/folding-knife/">Edition 100 folding knife</a></li>
                    <li><a href="/en/custom-knives/">Custom knives</a></li>
                </ul>
            </nav>
            <nav aria-labelledby="footer-en-info">
                <h2 id="footer-en-info">Information</h2>
                <ul role="list">
                    <li><a href="/en/about/">About the workshop</a></li>
                    <li><a href="/en/shipping/">Shipping, pickup &amp; payment</a></li>
                    <li><a href="/en/warranty/">Lifetime sharpening guarantee</a></li>
                    <li><a href="/en/">English home</a></li>
                </ul>
            </nav>
            <nav aria-labelledby="footer-en-de">
                <h2 id="footer-en-de">Deutsch</h2>
                <ul role="list" lang="de">
                    <li><a href="/" hreflang="de">Deutsche Website</a></li>
                    <li><a href="/schmiedekurse/" hreflang="de">Schmiedekurse</a></li>
                    <li><a href="/shop/" hreflang="de">Shop</a></li>
                </ul>
            </nav>
        </div>
        <div class="site-footer__legal">
            <span>© {{ date('Y') }} {{ $c['name'] }} · <span lang="de"><x-ui.tax-note /></span></span>
            <ul role="list">
                <li><a href="/impressum/" hreflang="de">Legal notice (German)</a></li>
                <li><a href="/datenschutz/" hreflang="de">Privacy (German)</a></li>
                <li><a href="/agb/" hreflang="de">Terms (German)</a></li>
                <li><a href="/widerruf/" hreflang="de">Withdrawal (German)</a></li>
            </ul>
        </div>
    </div>
</footer>
