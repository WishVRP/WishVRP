@props(['motif', 'format' => null, 'ratio' => null])
{{--
    Foto-Platzhalter. Ersetzen durch <x-ui.picture> mit echtem Werkstattfoto
    (AVIF/WebP, srcset, width/height). Keine Stock- oder KI-Bilder.
    format: portrait (4:5) | wide (16:9) | square | null (4:3)
--}}
<figure {{ $attributes->class(['photo', 'photo--'.$format => $format]) }} role="img" aria-label="Platzhalter für Foto: {{ $motif }}" data-missing>
    <figcaption class="photo__label">
        <strong>[FOTO: {{ $motif }}]</strong>
        <span>Platzhalter – echtes Foto aus der Werkstatt folgt</span>
    </figcaption>
</figure>
