<span {{ $attributes->class(['rating__stars']) }} aria-hidden="true">
    @for ($i = 0; $i < 5; $i++)<x-ui.icon name="star" />@endfor
</span>
