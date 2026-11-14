{{-- Mobile Aktionsleiste: Termine · Gutschein · Kontakt (nur < 64rem) --}}
<nav class="mobilebar" aria-label="Schnellzugriff">
    <a href="/schmiedekurse/buchen/"><span class="mobilebar__swatch" aria-hidden="true"></span>Kurs buchen</a>
    <a href="/geschenke/schmiedekurs-gutschein/"><span class="mobilebar__swatch mobilebar__swatch--gift" aria-hidden="true"></span>Gutschein</a>
    @if ($n = \App\Support\Cart::count())
        <a href="/warenkorb/"><span class="mobilebar__swatch mobilebar__swatch--contact" aria-hidden="true"></span>Warenkorb ({{ $n }})</a>
    @else
        <a href="/#kontakt"><span class="mobilebar__swatch mobilebar__swatch--contact" aria-hidden="true"></span>Kontakt</a>
    @endif
</nav>
