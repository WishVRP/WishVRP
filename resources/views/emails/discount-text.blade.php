Hallo{{ $firstName ? ' '.$firstName : '' }},

@if ($note)
{{ $note }}

@endif
hier ist dein Rabattcode für die {{ $c['name'] }}:

    {{ $d->code }}

Rabatt: {{ $d->valueLabel() }}{{ $d->type === 'amount' ? ' (einmal pro Bestellung, ohne Versand)' : '' }}
Gilt für: {{ $d->scopeLabel() }}
@if ($d->min_order)
Ab einem Bestellwert von {{ number_format((float) $d->min_order, 2, ',', '.') }} €
@endif
@if ($d->valid_from && $d->valid_from->isFuture())
Gültig ab: {{ $d->valid_from->format('d.m.Y') }}
@endif
@if ($d->valid_until)
Gültig bis: {{ $d->valid_until->format('d.m.Y') }}
@endif
@if ($d->email)
Der Code gilt nur zusammen mit der E-Mail-Adresse {{ $d->email }}.
@endif

So löst du ihn ein: Messer oder Schmiedekurs in den Warenkorb legen, zur Kasse gehen und den Code im Feld „Rabattcode“ eintragen.
@if ($d->scope !== 'kurse')
Shop: https://messermanufaktur-ulbrich.de/shop/
@endif
@if (in_array($d->scope, ['alles', 'kurse'], true))
Schmiedekurs buchen: https://messermanufaktur-ulbrich.de/schmiedekurse/buchen/
@endif
@if ($d->scope === 'gutscheine')
Kurs-Gutschein: https://messermanufaktur-ulbrich.de/geschenke/schmiedekurs-gutschein/
@endif

Herzliche Grüße aus der Werkstatt
{{ $c['owner'] }}
{{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}
Telefon {{ $c['phone']['display'] }} · {{ $c['email'] }}
