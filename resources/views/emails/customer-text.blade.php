{{ $title }} – {{ $o->number }}

Hallo {{ $firstName }},

@switch($kind)
@case('in_arbeit')
gute Nachrichten aus der Werkstatt: Wir haben mit deiner Bestellung begonnen.
@if ($o->production_time)
Voraussichtliche Fertigung: {{ $o->production_time }}
@endif
@break
@case('versendet')
dein Paket ist unterwegs zu dir.
@if ($o->carrierName())Versand mit: {{ $o->carrierName() }}
@endif
@if ($o->tracking_number)Sendungsnummer: {{ $o->tracking_number }}
@endif
@if ($o->trackingLink())Sendung verfolgen: {{ $o->trackingLink() }}
@endif
@break
@case('abholbereit')
deine Bestellung liegt in der Manufaktur zur Abholung bereit. Ruf an ({{ $c['phone']['display'] }}) oder antworte auf diese E-Mail, dann stimmen wir einen Termin ab.
@break
@case('zugestellt')
laut Versanddienst ist dein Paket angekommen. Wir hoffen, alles ist heil bei dir gelandet und du hast Freude daran.
Tipp: von Hand spülen, gleich abtrocknen, die Klinge ab und zu leicht einölen.
@break
@default
danke, dass du heute bei uns an der Esse gestanden hast{{ $o->course ? ' – beim '.$o->course->name : '' }}. Wir hoffen, du hattest einen richtig guten Tag.
@endswitch
@if ($personal)

{{ $personal }}
@endif

Bestellung: {{ $o->summary() }}
@if ($reviewUrl)

Eine kurze Google-Bewertung hilft uns als kleiner Familienbetrieb enorm:
{{ $reviewUrl }}
(Du hast bei der Bestellung zugestimmt. Keine weiteren Anfragen gewünscht? Antworte einfach auf diese E-Mail.)
@endif

Herzliche Grüße aus der Werkstatt
{{ $c['owner'] }}
{{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}
Telefon {{ $c['phone']['display'] }} · {{ $c['email'] }}
