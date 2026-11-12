{{-- Elektronische Widerrufsfunktion: zweistufig („Vertrag widerrufen“ → „Widerruf bestätigen“) --}}
@php $review = session('withdraw_review'); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Vertrag widerrufen" kicker="Widerrufsfunktion" tone="steel"
        lead="Hier kannst du den Widerruf deines Vertrags mit der {{ $c['name'] }} online erklären. Du bekommst sofort eine Eingangsbestätigung per E-Mail. Die Angabe eines Grundes ist nicht nötig." />
    <section class="block">
        <div class="container prose">
            @if (session('withdraw_done'))
                <div class="note note--ok" id="bestaetigt" role="status">
                    <h3>Dein Widerruf ist eingegangen</h3>
                    <p>Eingegangen am {{ session('withdraw_done') }} Uhr. Die Eingangsbestätigung mit allen Angaben haben wir dir per E-Mail geschickt. Wir prüfen den Widerruf und melden uns bei dir.</p>
                </div>
            @elseif ($review)
                <div id="bestaetigen">
                    <h2>Bitte prüfe deine Angaben</h2>
                    <dl class="spec">
                        <div class="spec__row"><dt>Name</dt><dd>{{ $review['name'] }}</dd></div>
                        <div class="spec__row"><dt>E-Mail</dt><dd>{{ $review['email'] }}</dd></div>
                        <div class="spec__row"><dt>Vorgangsnummer</dt><dd>{{ $review['order_number'] ?: 'nicht angegeben' }}</dd></div>
                        @if (! empty($review['message']))<div class="spec__row"><dt>Nachricht</dt><dd>{{ $review['message'] }}</dd></div>@endif
                    </dl>
                    <form method="post" action="{{ route('withdraw.store') }}" class="mt-5">
                        @csrf
                        @foreach (['name', 'email', 'order_number', 'message'] as $f)<input type="hidden" name="{{ $f }}" value="{{ $review[$f] ?? '' }}">@endforeach
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn--primary btn--lg">Widerruf bestätigen</button>
                        <a href="/widerrufen/" class="btn btn--secondary btn--lg">Angaben ändern</a>
                    </form>
                </div>
            @else
                <form method="post" action="{{ route('withdraw.review') }}" class="inquiry__form">
                    @csrf
                    <input class="hp" type="text" name="website_hp" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="fields">
                        <label class="field"><span class="field__label">Name *</span><input type="text" name="name" required maxlength="120" autocomplete="name" value="{{ old('name') }}">@error('name')<span class="field__error">{{ $message }}</span>@enderror</label>
                        <label class="field"><span class="field__label">E-Mail-Adresse * (für die Bestätigung)</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="{{ old('email') }}">@error('email')<span class="field__error">{{ $message }}</span>@enderror</label>
                        <label class="field"><span class="field__label">Vorgangsnummer (steht in deiner Bestätigung, z. B. U2026-0042)</span><input type="text" name="order_number" maxlength="20" value="{{ old('order_number') }}"></label>
                        <label class="field field--wide"><span class="field__label">Nachricht (freiwillig)</span><textarea name="message" rows="3" maxlength="2000">{{ old('message') }}</textarea></label>
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg mt-4">Vertrag widerrufen</button>
                    <p class="muted mt-3">Im nächsten Schritt bestätigst du den Widerruf. Informationen zu Frist, Folgen und Ausnahmen findest du in der <a href="/widerruf/">Widerrufsbelehrung</a>.</p>
                </form>
            @endif
        </div>
    </section>
</x-layouts.site>
