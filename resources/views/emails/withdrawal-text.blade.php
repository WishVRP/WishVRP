@switch($kind)
@case('angenommen')
Hallo {{ $w->name }},

wir haben deinen Widerruf{{ $w->order_number ? ' zu Vorgang '.$w->order_number : '' }} angenommen und den Vertrag storniert.
@if ($w->decision_note)

{{ $w->decision_note }}
@endif

Bereits gezahlte Beträge erstatten wir innerhalb von 14 Tagen über dasselbe Zahlungsmittel, das du verwendet hast.
@break
@case('abgelehnt')
Hallo {{ $w->name }},

danke für deine Nachricht. Für deinen Vertrag{{ $w->order_number ? ' (Vorgang '.$w->order_number.')' : '' }} besteht nach unserer Prüfung kein gesetzliches Widerrufsrecht:

{{ $w->decision_note }}

Wenn du Fragen hast oder eine andere Lösung suchst (z. B. Umbuchung), antworte einfach auf diese E-Mail.
@break
@default
Hallo {{ $w->name }},

hiermit bestätigen wir den Eingang deines Widerrufs.

Eingegangen am: {{ $w->created_at->timezone('Europe/Berlin')->format('d.m.Y') }} um {{ $w->created_at->timezone('Europe/Berlin')->format('H:i') }} Uhr
Vorgangsnummer: {{ $w->order_number ?: 'nicht angegeben' }}
Betrifft: {{ $w->items ?: 'den gesamten Vertrag' }}
Name: {{ $w->name }}
E-Mail: {{ $w->email }}
@if ($w->message)
Deine Nachricht: {{ $w->message }}
@endif

Wir prüfen den Widerruf und melden uns bei dir. Bereits gezahlte Beträge erstatten wir bei wirksamem Widerruf innerhalb von 14 Tagen.
@endswitch

Viele Grüße
{{ $c['owner'] }}
{{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}
Telefon {{ $c['phone']['display'] }} · {{ $c['email'] }}
