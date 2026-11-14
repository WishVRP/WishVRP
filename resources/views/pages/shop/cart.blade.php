{{-- Warenkorb: Messer und Kursbuchungen. Preise und Verfügbarkeit prüft der Server bei jedem Aufruf neu. --}}
@php
    $lines = \App\Support\Cart::lines();
    $subtotal = \App\Support\Cart::subtotal($lines);
    $problems = collect($lines)->whereNotNull('problem')->isNotEmpty();
    $hasProducts = collect($lines)->contains('type', 'product');
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Dein Warenkorb" kicker="Warenkorb" tone="straw" />

    <section class="block" aria-label="Warenkorb">
        <div class="container cart">
            @if (session('cart_added'))
                <p class="form-success" role="status"><b>{{ session('cart_added') }}</b> liegt jetzt in deinem Warenkorb.</p>
            @endif
            @error('cart')<p class="field__error cart__alert" role="alert">{{ $message }}</p>@enderror

            @if (! $lines)
                <div class="cart__empty">
                    <p class="lead">Dein Warenkorb ist noch leer.</p>
                    <div class="btn-row mt-5">
                        <x-ui.button href="/shop/" variant="primary">Messer ansehen</x-ui.button>
                        <x-ui.button href="/schmiedekurse/buchen/" variant="secondary">Schmiedekurs buchen</x-ui.button>
                    </div>
                </div>
            @else
                <ul class="cart__list" role="list">
                    @foreach ($lines as $l)
                        <li class="cart-line @if ($l['problem']) cart-line--problem @endif">
                            <div class="cart-line__main">
                                <span class="cart-line__kind tech">{{ $l['type'] === 'kurs' ? 'Schmiedekurs' : $l['detail'] }}</span>
                                <a class="cart-line__name" href="{{ $l['url'] }}">{{ $l['name'] }}</a>
                                @if ($l['type'] === 'kurs')<span class="cart-line__detail">{{ $l['detail'] }}</span>@endif
                                <span class="cart-line__unit tech">{{ number_format($l['unit'], 2, ',', '.') }} € {{ $l['type'] === 'kurs' ? 'pro Person' : 'pro Stück' }}</span>
                                @if ($l['problem'])<span class="field__error" role="alert">{{ $l['problem'] }}</span>@endif
                            </div>
                            <form class="cart-line__qty" method="post" action="{{ route('cart.update') }}">
                                @csrf
                                <input type="hidden" name="key" value="{{ $l['key'] }}">
                                <label><span class="visually-hidden">{{ $l['type'] === 'kurs' ? 'Personen' : 'Menge' }}</span>
                                    <input type="number" name="menge" min="1" max="{{ $l['type'] === 'kurs' ? config('ulbrich.booking.max_persons') : max(1, $l['product']->stock) }}" value="{{ $l['qty'] }}" inputmode="numeric">
                                </label>
                                <span class="tech muted">{{ $l['type'] === 'kurs' ? ($l['qty'] == 1 ? 'Person' : 'Personen') : 'Stück' }}</span>
                                <button type="submit" class="btn btn--secondary btn--sm">Ändern</button>
                            </form>
                            <p class="cart-line__total">{{ number_format($l['total'], 2, ',', '.') }} €</p>
                            <form method="post" action="{{ route('cart.update') }}" class="cart-line__remove">
                                @csrf
                                <input type="hidden" name="key" value="{{ $l['key'] }}">
                                <input type="hidden" name="menge" value="0">
                                <button type="submit" class="link-button">Entfernen</button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <div class="cart__summary">
                    <p class="booking__total"><span>Zwischensumme</span><strong>{{ number_format($subtotal, 2, ',', '.') }} €</strong></p>
                    <p class="tech muted"><x-ui.tax-note />@if ($hasProducts) · Versandkosten und Rabattcodes an der Kasse @else · Rabatt- und Gutscheincodes an der Kasse @endif</p>
                    <div class="btn-row mt-5">
                        @if ($problems)
                            <span class="btn btn--primary btn--lg is-disabled" aria-disabled="true">Zur Kasse</span>
                        @else
                            <x-ui.button href="/kasse/" variant="primary" size="lg">Zur Kasse</x-ui.button>
                        @endif
                        <x-ui.button href="/shop/" variant="secondary">Weiter einkaufen</x-ui.button>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
