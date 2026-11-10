{{-- $foto, $title, $text, $price (int|null|string), $href, $cta, $tone --}}
<article class="product-card reveal" data-spotlight>
    <a class="product-card__link" href="{{ $href }}">
        <x-ui.media :foto="$foto" format="portrait" decorative sizes="(min-width: 64rem) 30vw, (min-width: 40rem) 45vw, 100vw" />
        <span class="product-card__body">
            <span class="product-card__title">{{ $title }}</span>
            <span class="product-card__text">{{ $text }}</span>
            <span class="product-card__foot">
                <span class="product-card__price">@if(is_int($price)){{ number_format($price, 0, ',', '.') }} €@else{{ $price }}@endif</span>
                <span class="product-card__cta">{{ $cta }} <span aria-hidden="true">→</span></span>
            </span>
        </span>
    </a>
</article>
