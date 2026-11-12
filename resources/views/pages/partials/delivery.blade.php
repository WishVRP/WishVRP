{{-- Versand, Abholung, Zahlung – alle Angaben aus config/ulbrich.php → sales --}}
@php
    $s = config('ulbrich.sales');
    $w = config('ulbrich.warranty');
@endphp
<div class="delivery reveal" aria-label="Versand, Abholung und Zahlung">
    <div class="delivery__item">
        <h3 class="delivery__title">Versand mit DHL – weltweit</h3>
        <p>{{ \App\Support\Shipping::domesticLabel() }}. {{ $s['delivery'] }}</p>
    </div>
    <div class="delivery__item">
        <h3 class="delivery__title">Abholung vor Ort</h3>
        <p>{{ $s['pickup_note'] }}. Vor Ort ist {{ implode(' oder ', $s['payment_onsite']) }} möglich – oder du zahlst vorab per Überweisung.</p>
    </div>
    <div class="delivery__item">
        <h3 class="delivery__title">Weltweit</h3>
        <p>{{ $s['international'] }} {{ $s['customs'] }}</p>
    </div>
    <div class="delivery__item">
        <h3 class="delivery__title">{{ $w['name'] }}</h3>
        <p>{{ $w['text'] }} {{ $w['statutory_note'] }} Dazu bekommst du {{ implode(' und ', $w['included']) }}. <a href="/garantie/">Garantiebedingungen</a></p>
    </div>
    <p class="delivery__more"><a href="/versand/">Alles zu Versand, Abholung und Zahlung <span aria-hidden="true">→</span></a></p>
</div>
