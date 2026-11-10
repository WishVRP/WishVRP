@props(['href', 'variant' => 'primary', 'arrow' => true])
{{-- Varianten: primary (Anlassblau), secondary (Kontur), violet (Gutschein), brown (Messer), straw (auf dunklem Grund) --}}
<a href="{{ $href }}" {{ $attributes->class(['btn', 'btn--'.$variant => $variant !== 'primary']) }}>
    <span>{{ $slot }}</span>
    @if ($arrow)<span class="btn__arrow" aria-hidden="true">→</span>@endif
</a>
