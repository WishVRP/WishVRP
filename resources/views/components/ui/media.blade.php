@props(['motif' => null, 'tone' => 'forge', 'format' => null, 'tag' => true, 'foto' => null, 'pos' => null, 'eager' => false, 'sizes' => '(min-width: 64rem) 50vw, 100vw', 'decorative' => false])
{{--
    Bildfläche.
    Mit foto="key" (config/fotos.php): echtes Foto als <picture> (WebP + JPEG, srcset, width/height, lazy).
    Ohne foto: gestaltete Fläche (Damast-Textur + Anlassfarben-Licht) mit dezentem Etikett „Bildplatz · Motiv“,
    bis ein Foto vorliegt. Keine Stock-Fotos.
    tone: forge | steel | violet | brown   format: portrait | tall | wide | cinema | square | fill
    pos: top | upper | bottom | left | right   (Bildausschnitt, als Klasse – CSP erlaubt keine style-Attribute)
--}}
@php $img = $foto ? config('fotos.'.$foto) : null; @endphp
@if ($img)
    @php
        $base = '/images/fotos/'.$foto.'-';
        $set = fn (string $ext) => collect($img['widths'])->map(fn ($w) => $base.$w.'.'.$ext.' '.$w.'w')->implode(', ');
        $fallback = $base.(collect($img['widths'])->first(fn ($w) => $w >= 800) ?? last($img['widths'])).'.jpg';
    @endphp
    <figure {{ $attributes->class(['media', 'media--photo', 'media--'.$tone, 'media--'.$format => $format, 'media--pos-'.$pos => $pos]) }}>
        <picture>
            <source type="image/webp" srcset="{{ $set('webp') }}" sizes="{{ $sizes }}">
            <img src="{{ $fallback }}" srcset="{{ $set('jpg') }}" sizes="{{ $sizes }}"
                 width="{{ $img['w'] }}" height="{{ $img['h'] }}"
                 alt="{{ $decorative ? '' : $img['alt'] }}"
                 @if($eager) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        </picture>
    </figure>
@else
    <figure {{ $attributes->class(['media', 'media--'.$tone, 'media--'.$format => $format]) }} data-placeholder="{{ $motif }}" aria-hidden="true">
        @if ($tag)
            <figcaption class="media__tag">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><rect x="1.5" y="3.5" width="13" height="10" rx="1"/><circle cx="8" cy="8.5" r="2.6"/><path d="M5.5 3.5 6.5 2h3l1 1.5"/></svg>
                <span>Bildplatz · {{ $motif }}</span>
            </figcaption>
        @endif
    </figure>
@endif
