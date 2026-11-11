{{ $title }} – {{ $m['name'] }} ({{ $a->code }})

Hallo {{ $firstName }},

@switch($kind)
@case('eingang')
danke für deine Bewerbung! Dein Wunschstand ist vorgemerkt, bis wir deine Bewerbung geprüft haben. Die Bewerbung ist noch keine Zusage.
@break
@case('angenommen')
du bist dabei! Bitte überweise die Standgebühr, damit dein Stand fest gebucht ist.
@if (! empty($bank['iban']))
Kontoinhaber: {{ $bank['holder'] ?? $c['owner'] }} · IBAN: {{ $bank['iban'] }}@if (! empty($bank['bic'])) · BIC: {{ $bank['bic'] }}@endif

@else
Die Zahlungsdaten schicken wir dir in Kürze.
@endif
Betrag: {{ number_format((float) $a->amount, 2, ',', '.') }} € · Verwendungszweck: {{ $a->code }}
@break
@case('abgelehnt')
leider können wir dir für diesen Markt keinen Stand anbieten. Danke für dein Interesse.
@break
@default
deine Standgebühr ist eingegangen – dein Stand ist fest gebucht.
@endswitch
@if ($personal)

{{ $personal }}
@endif
@if ($kind !== 'abgelehnt')

Aussteller-Code: {{ $a->code }}
Stand: Nr. {{ $a->stand }}{{ $stand ? ' · '.$stand['label'].' · '.$stand['size'] : '' }}
Termin: {{ $date ?: 'wird noch bekannt gegeben' }}
Ort: {{ $m['place'] }}
Gebühr: {{ number_format((float) $a->amount, 2, ',', '.') }} €
@endif

Fragen? {{ $c['phone']['display'] }} · {{ $c['email'] }}
{{ $c['owner'] }} · {{ $c['name'] }}
