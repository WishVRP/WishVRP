{{-- Messer zum Nachschärfen einschicken (lebenslange Schliff-Garantie) --}}
@php $a = $c['address']; @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Messer zum Nachschärfen einschicken" kicker="{{ $c['warranty']['name'] }}" tone="blue"
        lead="Messer aus unserer Werkstatt – gekauft oder im Schmiedekurs selbst geschmiedet – schärfen wir kostenlos nach, so oft es nötig ist. Formular ausfüllen, Vorgangsnummer bekommen, Messer einschicken oder vorbeibringen." />

    <section class="block">
        <div class="container inquiry">
            <div class="inquiry__intro">
                <h2 class="h-section">So läuft es ab</h2>
                <ol class="mt-4">
                    <li><b>Formular ausfüllen</b> – mit Bestell- oder Vorgangsnummer, falls du sie zur Hand hast.</li>
                    <li><b>Vorgangsnummer per E-Mail</b> – mit Einsendeadresse und Verpackungstipps.</li>
                    <li><b>Messer einschicken oder vorbeibringen</b> – Zettel mit der Vorgangsnummer beilegen.</li>
                    <li><b>Nachschliff in der Werkstatt</b> – danach geht das Messer an dich zurück oder liegt zur Abholung bereit.</li>
                </ol>
                <p class="muted mt-4">Das Nachschärfen ist kostenlos; die Versandkosten trägst du. Reparaturen von Schäden sind nicht enthalten – die besprechen wir vorher mit dir. <a href="/garantie/">Alle Garantiebedingungen</a></p>
                <p class="muted mt-4">Messer von anderen Herstellern? Dafür gibt es unseren <a href="/messer-schaerfen-lassen/">Schärfservice</a>.</p>
            </div>
            <div>
                @if (session('resharpen_ok'))
                    <div class="form-success" id="gesendet" role="status">
                        <h3>Danke – deine Vorgangsnummer: {{ session('resharpen_ok') }}</h3>
                        @if (session('resharpen_send'))
                            <p>Bitte leg einen Zettel mit der Vorgangsnummer bei und schick das sicher verpackte Messer an:<br>
                                <b>{{ $c['name'] }}, {{ $c['owner'] }}, {{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }}</b></p>
                        @else
                            <p>Bring dein Messer in die Manufaktur ({{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }}) – ruf am besten vorher kurz an: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>.</p>
                        @endif
                        <p>Alles steht auch in der E-Mail, die wir dir gerade geschickt haben.</p>
                    </div>
                @else
                    <form class="inquiry__form" method="post" action="{{ route('resharpen.store') }}">
                        @csrf
                        <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
                        <div class="fields">
                            <label class="field"><span class="field__label">Name *</span><input type="text" name="name" required maxlength="120" autocomplete="name" value="{{ old('name') }}">@error('name')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">E-Mail *</span><input type="email" name="email" required maxlength="190" autocomplete="email" value="{{ old('email') }}">@error('email')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">Telefon</span><input type="tel" name="phone" maxlength="40" autocomplete="tel" value="{{ old('phone') }}"></label>
                            <label class="field"><span class="field__label">Bestell- oder Vorgangsnummer (falls vorhanden, z. B. U2026-0042)</span><input type="text" name="order_number" maxlength="20" value="{{ old('order_number') }}"></label>
                        </div>
                        <fieldset class="fields">
                            <legend class="field__label">Woher stammt das Messer? *</legend>
                            <label class="check"><input type="radio" name="origin" value="gekauft" @checked(old('origin', 'gekauft') === 'gekauft')> <span>Bei der Messermanufaktur Ulbrich gekauft</span></label>
                            <label class="check"><input type="radio" name="origin" value="kurs" @checked(old('origin') === 'kurs')> <span>Im Schmiedekurs selbst geschmiedet</span></label>
                        </fieldset>
                        <div class="fields">
                            <label class="field field--wide"><span class="field__label">Welche Messer? *</span><textarea name="knives" rows="2" maxlength="1000" required placeholder="z. B. Damast-Kochmesser 21 cm, gekauft 2025">{{ old('knives') }}</textarea>@error('knives')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">Anzahl Messer *</span><input type="number" name="count" min="1" max="10" value="{{ old('count', 1) }}" required></label>
                        </div>
                        <fieldset class="fields">
                            <legend class="field__label">Wie kommt das Messer zu uns? *</legend>
                            <label class="check"><input type="radio" name="delivery" value="einsenden" @checked(old('delivery', 'einsenden') === 'einsenden')> <span>Ich schicke es per Post – bitte zurücksenden an:</span></label>
                            <label class="check"><input type="radio" name="delivery" value="vorbeibringen" @checked(old('delivery') === 'vorbeibringen')> <span>Ich bringe es vorbei und hole es wieder ab ({{ $a['district'] }})</span></label>
                        </fieldset>
                        <div class="fields">
                            <label class="field field--wide"><span class="field__label">Straße und Hausnummer (für die Rücksendung)</span><input type="text" name="street" maxlength="160" autocomplete="street-address" value="{{ old('street') }}">@error('street')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">PLZ</span><input type="text" name="postal_code" maxlength="20" autocomplete="postal-code" value="{{ old('postal_code') }}">@error('postal_code')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">Ort</span><input type="text" name="city" maxlength="120" autocomplete="address-level2" value="{{ old('city') }}">@error('city')<span class="field__error" role="alert">{{ $message }}</span>@enderror</label>
                            <label class="field"><span class="field__label">Land</span><input type="text" name="country" maxlength="80" autocomplete="country-name" value="{{ old('country', 'Deutschland') }}"></label>
                            <label class="field field--wide"><span class="field__label">Nachricht (optional)</span><textarea name="message" rows="3" maxlength="2000">{{ old('message') }}</textarea></label>
                        </div>
                        <label class="check">
                            <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
                            <span>Ich bin einverstanden, dass meine Angaben zur Bearbeitung gespeichert werden (<a href="/datenschutz/">Datenschutz</a>).</span>
                        </label>
                        @error('consent')<p class="field__error" role="alert">{{ $message }}</p>@enderror
                        <button type="submit" class="btn btn--primary btn--lg mt-5">Vorgangsnummer anfordern</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
