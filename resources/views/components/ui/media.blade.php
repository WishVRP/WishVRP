@props(['motif', 'tone' => 'forge', 'format' => null, 'tag' => true])
{{--
    Bildfläche. Bis echte Werkstattfotos vorliegen: gestaltete Fläche (Damast-Textur + Anlassfarben-Licht),
    unten ein dezentes Etikett mit dem geplanten Motiv. Ersetzen durch <img> (AVIF/WebP, srcset, width/height),
    gleiche Klassen behalten. Keine Stock- oder KI-Fotos.
    tone: forge | steel | violet | brown   format: portrait | tall | wide | cinema | square | fill
--}}
<figure {{ $attributes->class(['media', 'media--'.$tone, 'media--'.$format => $format]) }} data-placeholder="{{ $motif }}" aria-hidden="true">
    @if ($tag)
        <figcaption class="media__tag">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><rect x="1.5" y="3.5" width="13" height="10" rx="1"/><circle cx="8" cy="8.5" r="2.6"/><path d="M5.5 3.5 6.5 2h3l1 1.5"/></svg>
            <span>Bildplatz · {{ $motif }}</span>
        </figcaption>
    @endif
</figure>
