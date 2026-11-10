{{-- Anlassfarben-Skala: der eine inszenierte Moment der Startseite (läuft einmal, respektiert prefers-reduced-motion). --}}
<figure {{ $attributes->class(['temper']) }}>
    <div class="temper__bar" aria-hidden="true"></div>
    <ol class="temper__ticks" role="list" aria-label="Anlassfarben von Stahl mit ungefähren Temperaturen">
        <li><b>Strohgelb</b>ca. 220 °C</li>
        <li><b>Braun</b>ca. 240 °C</li>
        <li><b>Violett</b>ca. 280 °C</li>
        <li><b>Blau</b>ca. 300 °C</li>
    </ol>
    <figcaption class="temper__caption">{{ $slot }}</figcaption>
</figure>
