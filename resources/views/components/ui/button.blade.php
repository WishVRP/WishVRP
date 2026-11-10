@props(['href', 'variant' => 'primary', 'arrow' => true, 'size' => null])
{{-- Varianten: primary (Anlassblau), secondary (Kontur), straw, violet, brown. Text sagt, was danach passiert. --}}
<a href="{{ $href }}" {{ $attributes->class(['btn', 'btn--'.$variant => $variant !== 'primary', 'btn--'.$size => $size]) }}>
    <span>{{ $slot }}</span>
    @if ($arrow)<span class="btn__arrow" aria-hidden="true">→</span>@endif
</a>
