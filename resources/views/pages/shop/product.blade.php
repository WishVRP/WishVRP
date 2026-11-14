{{-- Produktseite (Daten aus der Verwaltung). Kauf über Warenkorb und Kasse. --}}
@php
    $images = collect($product->images ?? [])->map(function ($p) {
        $file = storage_path('app/public/'.$p);
        [$w, $h] = is_file($file) ? (getimagesize($file) ?: [1200, 900]) : [1200, 900];
        return ['url' => '/storage/'.ltrim($p, '/'), 'w' => $w, 'h' => $h];
    });
    $available = $product->available();
    $html = $product->description ? \Illuminate\Support\Str::markdown($product->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) : null;
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <section class="block product" aria-labelledby="page-title">
        <div class="container">
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <ol role="list">
                    @foreach ($meta->breadcrumb as $crumb)
                        <li>@if(! $loop->last)<a href="{{ $crumb['path'] }}">{{ $crumb['name'] }}</a>@else<span aria-current="page">{{ $crumb['name'] }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            <div class="product__grid">
                <div class="product__gallery" data-gallery-product>
                    @forelse ($images as $i => $img)
                        <img src="{{ $img['url'] }}" width="{{ $img['w'] }}" height="{{ $img['h'] }}" alt="{{ $product->imageAlt($i) }}"
                             @class(['product__img', 'product__img--main' => $i === 0]) loading="{{ $i ? 'lazy' : 'eager' }}" decoding="async">
                    @empty
                        <x-ui.type-tile class="product__img product__img--main" :title="$product->name" :label="$product->categoryName()" />
                    @endforelse
                </div>
                <div class="product__info">
                    <x-ui.kicker color="brown">{{ $product->categoryName() }}</x-ui.kicker>
                    <h1 id="page-title" class="product__title">{{ $product->name }}</h1>
                    @if ($product->short)<p class="lead mt-3">{{ $product->short }}</p>@endif
                    <p class="product__price">{{ number_format((float) $product->price, 2, ',', '.') }} €</p>
                    <p class="tech muted">zzgl. Versand (Deutschland ab {{ number_format(\App\Support\Shipping::domestic(), 2, ',', '.') }} €, weltweit je nach Land) oder kostenlose Abholung · <x-ui.tax-note /></p>
                    <p @class(['product__stock', 'is-out' => ! $available])>{{ $available ? ($product->stock === 1 ? 'Einzelstück – sofort verfügbar' : 'Verfügbar') : 'Verkauft' }}</p>
                    @if (! empty($product->specs))
                        <dl class="spec mt-5">
                            @foreach ($product->specs as $s)<div class="spec__row"><dt>{{ $s['label'] }}</dt><dd>{{ $s['value'] }}</dd></div>@endforeach
                        </dl>
                    @endif
                    <ul class="check-list mt-5" role="list">
                        <li>Von Hand gefertigt von Messerschmied {{ $c['owner'] }}</li>
                        <li>{{ $c['warranty']['name'] }}: {{ $c['warranty']['short'] }} – <a href="/garantie/">Bedingungen</a></li>
                        <li>Pflegeheft und Pflegemittel kostenlos dabei</li>
                    </ul>
                    @if ($available)
                        <form class="add-to-cart mt-5" method="post" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="produkt" value="{{ $product->id }}">
                            @if ($product->stock > 1)
                                <label class="add-to-cart__qty"><span class="field__label">Menge</span>
                                    <input type="number" name="menge" min="1" max="{{ min(20, $product->stock) }}" value="1" inputmode="numeric">
                                </label>
                            @endif
                            <button type="submit" class="btn btn--primary btn--lg">In den Warenkorb</button>
                        </form>
                        @error('menge')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                        <p class="tech muted mt-3">Zahlung per Überweisung · Versand weltweit oder Abholung · Online-Zahlung bald verfügbar</p>
                    @else
                        <p class="tech mt-5"><b>Verkauft</b> – ähnliche Stücke fertigen wir auf Anfrage: <a href="/sonderanfertigungen/">Sonderanfertigung</a></p>
                    @endif
                </div>
            </div>
            @if ($html)<div class="prose mt-8 product__desc">{!! $html !!}</div>@endif
        </div>
    </section>


    <x-page.cta title="Fragen zu diesem Messer?" text="{{ $c['owner'] }} beantwortet deine Fragen zu Form, Stahl und Pflege persönlich." />
</x-layouts.site>
