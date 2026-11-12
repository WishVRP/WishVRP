{{-- Kurstermin umbuchen (persönlicher Link aus der Buchungsbestätigung) --}}
@php
    $order = request('t') ? \App\Models\Order::query()->where('manage_token', (string) request('t'))->with('course')->first() : null;
    $blocker = $order?->rebookBlocker();
    $free = $order && ! $blocker && $order->course ? \App\Support\CourseCalendar::nextFree($order->course, 12, $order->id, 400) : [];
    $fmt = fn ($d) => \Illuminate\Support\Carbon::parse($d)->locale('de')->isoFormat('dd, D. MMM YYYY');
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Kurstermin umbuchen" kicker="{{ $order?->number ?? 'Buchung' }}" tone="straw" />
    <section class="block">
        <div class="container prose">
            @if (! $order)
                <p class="lead">Dieser Link ist ungültig. Bitte nutze den Link aus deiner Buchungsbestätigung oder ruf uns an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.</p>
            @else
                @if (session('rebooked'))
                    <p class="note note--ok" role="status"><b>Umgebucht!</b> Dein neuer Kurstag ist {{ $order->course_start->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}. Die Bestätigung haben wir dir per E-Mail geschickt.</p>
                @endif
                <dl class="spec">
                    <div class="spec__row"><dt>Kurs</dt><dd>{{ $order->course?->name }} · {{ $order->participants }} {{ $order->participants == 1 ? 'Person' : 'Personen' }}</dd></div>
                    <div class="spec__row"><dt>Aktueller Kurstag</dt><dd><b>{{ $order->course_start?->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}</b></dd></div>
                    <div class="spec__row"><dt>Kostenlose Umbuchungen</dt><dd>noch {{ $order->freeRebookingsLeft() }} von {{ config('ulbrich.booking.free_rebookings') }}</dd></div>
                    @if ($order->rebookDeadline())<div class="spec__row"><dt>Umbuchen möglich bis</dt><dd>{{ $order->rebookDeadline()->format('d.m.Y') }} ({{ config('ulbrich.booking.rebook_days_before') }} Tage vor dem Kurstag)</dd></div>@endif
                    <div class="spec__row"><dt>Preis</dt><dd>bleibt gleich (Preisbindung): {{ number_format((float) $order->amount, 2, ',', '.') }} €</dd></div>
                </dl>
                @error('start')<p class="field__error mt-4" role="alert">{{ $message }}</p>@enderror
                @if ($blocker)
                    <p class="lead mt-5">{{ $blocker }}</p>
                @else
                    <form method="post" action="{{ route('rebook.store') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="t" value="{{ $order->manage_token }}">
                        <fieldset class="fields">
                            <legend class="field__label">Nächste freie Starttage</legend>
                            <div class="rebook-days">
                                @foreach ($free as $d)
                                    <label class="check"><input type="radio" name="start" value="{{ $d }}" @checked(old('start') === $d)> <span>{{ $fmt($d) }}</span></label>
                                @endforeach
                            </div>
                        </fieldset>
                        <label class="field mt-4"><span class="field__label">Oder Wunschtag eingeben</span>
                            <input type="date" name="start_custom" min="{{ \App\Support\CourseCalendar::first()->toDateString() }}" max="{{ \App\Support\CourseCalendar::last()->toDateString() }}" data-rebook-date></label>
                        <button type="submit" class="btn btn--primary btn--lg mt-5">Kostenlos umbuchen</button>
                        <p class="muted mt-3">Ein Kurs pro Tag – wir prüfen beim Absenden, ob der Tag noch frei ist.</p>
                    </form>
                @endif
            @endif
        </div>
    </section>
</x-layouts.site>
