{{-- Galerie: Messer aus der Werkstatt, horizontal wischbar --}}
@php
    $gallery = [
        ['klappmesser-offen', 'Klappmesser · Damast, dunkles Holz'],
        ['klappmesser-rotholz', 'Klappmesser · rotbrauner Holzgriff'],
        ['klappmesser-hell-detail', 'Klappmesser · heller Griff'],
        ['klappmesser-stehend', 'Klappmesser · halb geöffnet'],
        ['edition-messer', 'Klappmesser · auf Stein'],
        ['klappmesser-zebrano', 'Klappmesser · heller Streifengriff'],
        ['punze-detail', 'Detail · Zeichen an der Stahlbacke'],
        ['klappmesser-geschlossen', 'Klappmesser · geschlossen'],
    ];
@endphp
<section class="block gallery is-dark" id="galerie" aria-labelledby="gallery-title">
    <div class="container gallery__head">
        <div>
            <x-ui.kicker color="straw">Aus der Werkstatt</x-ui.kicker>
            <h2 id="gallery-title" class="h-section">Messer, die hier entstanden sind</h2>
        </div>
        <div class="gallery__nav" data-gallery-nav hidden>
            <button type="button" class="gallery__btn" data-gallery-prev aria-label="Vorherige Bilder">←</button>
            <button type="button" class="gallery__btn" data-gallery-next aria-label="Nächste Bilder">→</button>
        </div>
    </div>
    <ul class="gallery__track" role="list" data-gallery tabindex="0" aria-label="Bildergalerie, mit Pfeiltasten oder Wischen blättern">
        @foreach ($gallery as $i => [$key, $caption])
            <li class="gallery__item">
                <x-ui.media :foto="$key" sizes="(min-width: 64rem) 30vw, 80vw" />
                <p class="gallery__caption"><span>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span> {{ $caption }}</p>
            </li>
        @endforeach
    </ul>
</section>
