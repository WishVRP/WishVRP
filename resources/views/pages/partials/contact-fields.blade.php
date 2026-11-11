{{-- Kontaktfelder für Buchung und Anfrage (gemeinsam genutzt) --}}
<div class="fields">
    <label class="field">
        <span class="field__label">Name *</span>
        <input type="text" name="name" required maxlength="120" autocomplete="name" value="{{ old('name') }}">
        @error('name')<span class="field__error" role="alert">{{ $message }}</span>@enderror
    </label>
    <label class="field">
        <span class="field__label">E-Mail *</span>
        <input type="email" name="email" required maxlength="190" autocomplete="email" value="{{ old('email') }}">
        @error('email')<span class="field__error" role="alert">{{ $message }}</span>@enderror
    </label>
    <label class="field">
        <span class="field__label">Telefon</span>
        <input type="tel" name="phone" maxlength="40" autocomplete="tel" value="{{ old('phone') }}">
    </label>
    <label class="field">
        <span class="field__label">Rabattcode (optional)</span>
        <input type="text" name="discount_code" maxlength="20" autocomplete="off" value="{{ old('discount_code') }}" placeholder="z. B. aus Newsletter oder Weihnachtsaktion">
        @error('discount_code')<span class="field__error" role="alert">{{ $message }}</span>@enderror
    </label>
    <label class="field field--wide">
        <span class="field__label">Nachricht</span>
        <textarea name="message" rows="4" maxlength="3000">{{ old('message') }}</textarea>
    </label>
</div>
<label class="check">
    <input type="checkbox" name="consent" value="1" required @checked(old('consent'))>
    <span>Ich bin einverstanden, dass meine Angaben zur Bearbeitung meiner Anfrage gespeichert werden (<a href="/datenschutz/">Datenschutz</a>).</span>
</label>
@error('consent')<p class="field__error" role="alert">{{ $message }}</p>@enderror
