{{-- Rückkehr von Stripe: Danke bzw. Abbruch. Der Zahlungsstatus kommt verbindlich per Webhook. --}}
@php
    $ok = $page['payment_page'] === 'success';
    $order = request('o') ? \App\Models\Order::query()->where('number', (string) request('o'))->first() : null;
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$ok ? 'Danke für deine Zahlung!' : 'Zahlung abgebrochen'" kicker="{{ $order?->number ?? 'Zahlung' }}" tone="straw" />
    <section class="block">
        <div class="container prose">
            @if ($ok)
                <p class="lead">Deine Zahlung wird gerade bestätigt. Du bekommst gleich eine E-Mail mit allen Einzelheiten{{ $order?->type === 'gutschein' ? ' und deinem Gutscheincode' : '' }}.</p>
                @if ($order?->type === 'kurs' && $order->course_start)
                    <p>Dein Kurstag: <b>{{ $order->course_start->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}</b> – {{ $order->course?->name }}.</p>
                @endif
            @else
                <p class="lead">Es wurde nichts abgebucht. Deine Reservierung verfällt nach kurzer Zeit automatisch.</p>
                <p>Möchtest du es noch einmal versuchen? <a href="/schmiedekurse/buchen/">Kurs buchen</a> · <a href="/shop/">Shop</a> · Oder ruf an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
            @endif
            <p class="mt-6">Weiter zu den <a href="/schmiedekurse/">Schmiedekursen</a>, zum <a href="/shop/">Shop</a> oder zu den <a href="/geschenke/">Geschenkideen</a>.</p>
        </div>
    </section>
    <x-page.cta />
</x-layouts.site>
