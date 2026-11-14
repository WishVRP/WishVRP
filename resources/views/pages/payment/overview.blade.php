{{-- Zahlungsübersicht nach der Bestellung: je Vorgang Betrag, Bankdaten und Verwendungszweck. Zugang: Sitzung direkt nach der Kasse oder persönlicher Link (?t=…) aus der E-Mail. --}}
@php
    $tokens = collect(session('payment_tokens', []));
    if (is_string(request('t')) && strlen(request('t')) === 48) {
        $tokens->push(request('t'));
    }
    $orders = $tokens->isEmpty() ? collect() : \App\Models\Order::query()->whereIn('manage_token', $tokens->unique()->all())->with('course')->orderBy('id')->get();
    $bank = array_filter(config('ulbrich.bank', []));
    $open = $orders->filter(fn ($o) => ! in_array($o->status, ['storniert'], true));
    $total = $open->filter(fn ($o) => ! in_array($o->status, \App\Support\OrderStatusFlow::PAID, true))->sum(fn ($o) => $o->totalDue());
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Zahlungsübersicht" kicker="Überweisung" tone="straw" />

    <section class="block" aria-label="Zahlungsübersicht">
        <div class="container pay-overview">
            @if ($open->isEmpty())
                <p class="lead">Diese Zahlungsübersicht ist nicht (mehr) verfügbar. Den Link findest du in deiner Bestellbestätigung per E-Mail.</p>
                <p>Fragen? Ruf an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
            @else
                <p class="lead">Bitte überweise den Betrag mit dem angegebenen Verwendungszweck. Sobald die Zahlung bei uns eingegangen ist, bekommst du die Bestätigung{{ $open->contains('type', 'kurs') ? ' – dein Kurs ist dann verbindlich gebucht' : '' }} und die Rechnung per E-Mail.</p>

                @foreach ($open as $o)
                    @php $paid = in_array($o->status, \App\Support\OrderStatusFlow::PAID, true); @endphp
                    <article class="pay-card @if ($paid) pay-card--paid @endif">
                        <header class="pay-card__head">
                            <h2>{{ $o->type === 'kurs' ? 'Kursbuchung' : ($o->type === 'gutschein' ? 'Gutschein' : 'Bestellung') }} {{ $o->number }}</h2>
                            <span class="pay-card__status">{{ $paid ? 'Bezahlt – danke!' : 'Zahlung offen' }}</span>
                        </header>
                        <dl class="spec">
                            @if ($o->type === 'kurs')
                                <div class="spec__row"><dt>Kurs</dt><dd>{{ $o->course?->name }} · {{ $o->participants }} {{ $o->participants == 1 ? 'Person' : 'Personen' }}</dd></div>
                                <div class="spec__row"><dt>Kurstag</dt><dd>{{ $o->course_start?->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}@if ($o->course_end && $o->course_start && ! $o->course_end->isSameDay($o->course_start)) – {{ $o->course_end->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}@endif</dd></div>
                            @elseif ($o->items)
                                @foreach ($o->items as $it)
                                    <div class="spec__row"><dt>{{ $loop->first ? 'Artikel' : '' }}</dt><dd>{{ $it['qty'] }} × {{ $it['name'] }} – {{ number_format((float) $it['price'] * (int) $it['qty'], 2, ',', '.') }} €</dd></div>
                                @endforeach
                            @else
                                <div class="spec__row"><dt>Artikel</dt><dd>{{ $o->summary() }}</dd></div>
                            @endif
                            @if ($o->discountOff() > 0)<div class="spec__row"><dt>Rabatt{{ $o->discount_code ? ' ('.$o->discount_code.')' : '' }}</dt><dd>− {{ number_format($o->discountOff(), 2, ',', '.') }} €</dd></div>@endif
                            @if ((float) $o->shipping_amount > 0)<div class="spec__row"><dt>Versand</dt><dd>{{ number_format((float) $o->shipping_amount, 2, ',', '.') }} €</dd></div>@endif
                            @if ((float) $o->customs_amount > 0)<div class="spec__row"><dt>Zoll & Einfuhrabgaben</dt><dd>{{ number_format((float) $o->customs_amount, 2, ',', '.') }} €</dd></div>@endif
                            @if ((float) $o->voucher_amount > 0)<div class="spec__row"><dt>Gutschein</dt><dd>− {{ number_format((float) $o->voucher_amount, 2, ',', '.') }} €</dd></div>@endif
                        </dl>
                        @if ($o->shipping_method === 'versand' && $o->shipping_amount === null)
                            <p class="pay-card__note">Die Versandkosten in dein Land nennen wir dir per E-Mail – bitte erst danach überweisen.</p>
                        @elseif (! $paid)
                            <div class="pay-card__transfer">
                                <p class="booking__total"><span>Zu überweisen</span><strong>{{ number_format($o->totalDue(), 2, ',', '.') }} €</strong></p>
                                @if ($bank)
                                    <dl class="spec pay-card__bank">
                                        <div class="spec__row"><dt>Empfänger</dt><dd>{{ $bank['holder'] ?? $c['owner'] }}</dd></div>
                                        @if (! empty($bank['iban']))<div class="spec__row"><dt>IBAN</dt><dd class="tech">{{ $bank['iban'] }}</dd></div>@endif
                                        @if (! empty($bank['bic']))<div class="spec__row"><dt>BIC</dt><dd class="tech">{{ $bank['bic'] }}</dd></div>@endif
                                        @if (! empty($bank['name']))<div class="spec__row"><dt>Bank</dt><dd>{{ $bank['name'] }}</dd></div>@endif
                                        <div class="spec__row"><dt>Verwendungszweck</dt><dd class="tech"><b>{{ $o->number }}</b></dd></div>
                                    </dl>
                                @else
                                    <p class="pay-card__note">Die Bankverbindung schicken wir dir per E-Mail. Verwendungszweck: <b>{{ $o->number }}</b></p>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach

                @if ($open->count() > 1 && $total > 0)
                    <p class="booking__total pay-overview__total"><span>Insgesamt offen</span><strong>{{ number_format($total, 2, ',', '.') }} €</strong></p>
                    <p class="tech muted">Bitte je Vorgang eine eigene Überweisung mit dem jeweiligen Verwendungszweck – so können wir alles schnell zuordnen.</p>
                @endif
                <p class="tech muted mt-4"><x-ui.tax-note /> · {{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}</p>
                <div class="btn-row mt-5 no-print">
                    <button type="button" class="btn btn--primary" data-print>Drucken / als PDF speichern</button>
                    <x-ui.button href="/" variant="secondary">Zur Startseite</x-ui.button>
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
