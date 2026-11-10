<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Geschenke aus der Manufaktur" kicker="Geschenkideen" tone="violet" foto="klappmesser-zebrano"
        lead="Ein Erlebnis an Esse und Amboss oder ein Messer, das bleibt: zwei Wege, jemandem eine Freude aus der Werkstatt zu machen." />

    <x-page.section title="Zwei Geschenkideen" kicker="Auswahl" kicker-color="violet">
        <div class="gift-duo">
            <a class="gift-card gift-card--violet reveal" href="/geschenke/schmiedekurs-gutschein/" data-spotlight>
                <x-ui.media foto="klappmesser-hell-detail" format="wide" decorative sizes="(min-width: 60rem) 45vw, 100vw" />
                <span class="gift-card__body">
                    <span class="gift-card__label">Erlebnis</span>
                    <span class="gift-card__title">Schmiedekurs-Gutschein</span>
                    <span class="gift-card__text">Die beschenkte Person wählt Kurs und Termin später selbst und bucht online.</span>
                    <span class="link-arrow">Gutschein ansehen <span aria-hidden="true">→</span></span>
                </span>
            </a>
            <a class="gift-card gift-card--brown reveal reveal-delay-1" href="/klappmesser/" data-spotlight>
                <x-ui.media foto="edition-messer" format="wide" decorative sizes="(min-width: 60rem) 45vw, 100vw" />
                <span class="gift-card__body">
                    <span class="gift-card__label">Messer</span>
                    <span class="gift-card__title">{{ $e['name'] }}</span>
                    <span class="gift-card__text">{{ $e['total'] }} nummerierte Stücke · reguläre Stücke {{ number_format($e['price'], 0, ',', '.') }} €</span>
                    <span class="link-arrow">Edition ansehen <span aria-hidden="true">→</span></span>
                </span>
            </a>
        </div>
        <p class="mt-6"><a class="link-arrow" href="/erlebnisse/">Schmieden als Erlebnis – auch für Teams <span aria-hidden="true">→</span></a></p>
    </x-page.section>

    <x-page.cta title="Hilfe bei der Geschenkwahl?" />
</x-layouts.site>
