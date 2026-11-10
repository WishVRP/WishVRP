@props(['title', 'label' => null, 'meta' => null])
{{-- Typografische Bildfläche: Name als „Bild“ (Damast-Textur, Anlassfarben-Kante). Dekorativ – der Text steht daneben auch als echter Inhalt. --}}
<div {{ $attributes->class(['type-tile']) }} aria-hidden="true">
    @if ($label)<span class="type-tile__label">{{ $label }}</span>@endif
    <span class="type-tile__title">{{ $title }}</span>
    @if ($meta)<span class="type-tile__meta">{{ $meta }}</span>@endif
</div>
