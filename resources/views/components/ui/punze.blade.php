@props(['key' => null, 'size' => null])
{{-- Punze: eingeschlagener Stempel. Nur für echte, belegte Fakten verwenden. --}}
<span {{ $attributes->class(['punze', 'punze--lg' => $size === 'lg']) }}>
    @if ($key)<span class="punze__key">{{ $key }}</span>@endif
    <span>{{ $slot }}</span>
</span>
