{{-- Produkt-Raster aus der Verwaltung ($products) --}}
@if ($products->isNotEmpty())
    <section class="block" aria-labelledby="products-h">
        <div class="container">
            <h2 id="products-h" class="h-section">{{ $title ?? 'Verfügbare Messer' }}</h2>
            <div class="product-grid mt-6">
                @foreach ($products as $p)
                    <a class="shop-card" href="{{ $p->url() }}">
                        @if ($img = $p->imageUrls()[0] ?? null)
                            <img class="shop-card__img" src="{{ $img }}" alt="{{ $p->imageAlt() }}" width="600" height="600" loading="lazy" decoding="async">
                        @else
                            <x-ui.type-tile class="shop-card__img" :title="$p->name" :label="$p->categoryName()" />
                        @endif
                        <span class="shop-card__body">
                            <span class="shop-card__name">{{ $p->name }}</span>
                            <span class="shop-card__price">{{ number_format((float) $p->price, 0, ',', '.') }} €@unless ($p->available()) · verkauft @endunless</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
