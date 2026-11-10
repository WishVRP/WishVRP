<section class="block gifts" aria-labelledby="gifts-title">
    <div class="container gifts__grid">
        <div class="voucher-stage reveal" aria-hidden="true">
            <x-ui.media class="voucher-stage__photo" foto="klappmesser-zebrano" decorative sizes="(min-width: 60rem) 45vw, 100vw" />
            <div class="voucher voucher--back"></div>
            <div class="voucher">
                <p class="voucher__kind">GUTSCHEIN</p>
                <p class="voucher__title">Schmiedekurs-Gutschein</p>
                <p class="voucher__brand">{{ $c['name'] }} · {{ $c['place_label'] }}</p>
                <span class="voucher__stamp">Nº<br>___</span>
            </div>
        </div>
        <div class="reveal reveal-delay-1">
            <x-ui.kicker color="violet">Gutscheine & Geschenke</x-ui.kicker>
            <h2 id="gifts-title" class="h-section">Einen Schmiedekurs verschenken</h2>
            <p class="lead mt-5">
                Der Gutschein reserviert noch keinen Platz: Die beschenkte Person sucht sich Kurs und Termin später
                selbst aus und bucht online.
            </p>
            <x-ui.missing silent>Gutscheinwerte, Gültigkeit, Versandart (PDF/Post)</x-ui.missing>
            <div class="btn-row mt-5">
                <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="violet" size="lg">Gutschein auswählen</x-ui.button>
            </div>
            <nav class="gifts__links" aria-label="Geschenkideen">
                <a href="/geschenke/">Geschenkideen aus der Manufaktur <span class="tech">→</span></a>
                <a href="/erlebnisse/">Erlebnisse <span class="tech">→</span></a>
                <a href="/klappmesser/">Ein Messer der Edition 100 <span class="tech">→</span></a>
            </nav>
        </div>
    </div>
</section>
