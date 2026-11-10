@props(['color' => null])
<p {{ $attributes->class(['kicker', 'kicker--'.$color => $color]) }}>{{ $slot }}</p>
