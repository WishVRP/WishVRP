@props(['ask' => null, 'label' => 'Angabe folgt', 'silent' => false])
{{--
    Fehlende echte Angabe. Öffentlich neutral („Angabe folgt“), nie als Fehlermeldung.
    Der Inhalt (Slot) beschreibt intern, was fehlt – php artisan ulbrich:audit listet alle data-missing.
    silent: nichts Sichtbares, nur der Prüfvermerk.
--}}
@php $detail = trim($slot.($ask ? ' – '.$ask : '')); @endphp
@if ($silent)
<span hidden data-missing="{{ $detail }}"></span>
@else
<span class="pending" data-missing="{{ $detail }}">{{ $label }}</span>
@endif
