{{-- Gutschein kaufen: Kurs + Personen → Zahlung (Stripe) → Code per E-Mail, einlösbar im Buchungskalender. --}}
@php
    $calendar = \App\Support\CourseCalendar::class;
    $buyable = $courses->filter(fn ($co) => $calendar::bookable($co))->values();
    $online = \App\Support\Stripe::enabled();
    $max = (int) config('ulbrich.booking.max_persons');
@endphp
<section class="block block--sunken" id="anfrage" aria-labelledby="gs-h">
    <div class="container inquiry">
        <div class="inquiry__intro">
            <h2 id="gs-h" class="h-section">Gutschein {{ $online ? 'kaufen' : 'bestellen' }}</h2>
            <p class="lead mt-4">Wähle Kurs und Personenzahl. {{ $online ? 'Nach der Zahlung bekommst du den Gutscheincode sofort per E-Mail.' : 'Du bekommst die Zahlungsdaten per E-Mail; nach Zahlungseingang schicken wir dir den Gutscheincode.' }} Die beschenkte Person sucht sich später im Buchungskalender einen freien Tag aus und löst den Code dort ein.</p>
            <p class="muted mt-4">Gültig bis Ende {{ \App\Models\Voucher::defaultValidUntil()->year }}.</p>
        </div>
        <div>
            @if (session('order_ok'))
                <div class="form-success" id="anfrage-gesendet" role="status">
                    <h3>Danke – deine Bestellung ist angekommen.</h3>
                    <p>Vorgangsnummer <strong>{{ session('order_ok') }}</strong>. Nach Zahlungseingang schicken wir dir den Gutscheincode per E-Mail.</p>
                </div>
            @endif
            <form class="inquiry__form" method="post" action="/gutschein/kaufen/">
                @csrf
                <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="fields">
                    <label class="field">
                        <span class="field__label">Gutschein für</span>
                        <select name="kurs">
                            @foreach ($buyable as $co)
                                <option value="{{ $co->slug }}" @selected(old('kurs') === $co->slug)>{{ $co->name }} – {{ number_format($co->price_from, 0, ',', '.') }} € p. P.</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span class="field__label">Personen</span>
                        <input type="number" name="personen" min="1" max="{{ $max }}" value="{{ old('personen', 1) }}">
                    </label>
                    <label class="field field--wide">
                        <span class="field__label">Für (Name auf dem Gutschein, optional)</span>
                        <input type="text" name="empfaenger" maxlength="120" value="{{ old('empfaenger') }}">
                    </label>
                </div>
                @include('pages.partials.contact-fields')
                <label class="check">
                    <input type="checkbox" name="agb" value="1" required @checked(old('agb'))>
                    <span>Ich habe die <a href="/agb/">AGB</a>, die <a href="/gutscheinbedingungen/">Gutscheinbedingungen</a> und die <a href="/widerruf/">Widerrufsbelehrung</a> gelesen.</span>
                </label>
                @error('agb')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                <button type="submit" class="btn btn--primary btn--lg mt-5">{{ $online ? 'Zahlungspflichtig bestellen' : 'Gutschein bestellen' }}</button>
            </form>
        </div>
    </div>
</section>
