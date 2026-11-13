{{ $title }} – {{ $o->number }}

Hallo {{ $firstName }},

@switch($kind)
@case('eingang')
@case('bezahlt')
{{ $kind === 'eingang' ? 'vielen Dank! Hier ist die Übersicht deiner '.($o->type === 'kurs' ? 'Buchung' : (in_array($o->type, ['anfrage', 'service'], true) ? 'Anfrage' : 'Bestellung')).'.' : 'deine Zahlung ist eingegangen, vielen Dank!'.($o->type === 'kurs' ? ' Dein Kurstag ist damit fest gebucht.' : '') }}

Vorgangsnummer: {{ $o->number }}
@if ($o->type === 'kurs')
Kurs: {{ $o->course?->name ?? 'Schmiedekurs' }}
Kurstag: {{ $o->course_start ? $o->course_start->locale('de')->isoFormat('dddd, D. MMMM YYYY').($o->course_end && ! $o->course_end->isSameDay($o->course_start) ? ' – '.$o->course_end->locale('de')->isoFormat('dddd, D. MMMM YYYY') : '') : ($o->preferred_date ? 'Wunsch: '.$o->preferred_date : 'wird abgestimmt') }}
Teilnehmer: {{ $o->participants }}
Ort: {{ $c['name'] }}, {{ $c['address']['street'] }}, {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}
@else
Artikel: {{ $o->item ?? $o->typeLabel() }}{{ $o->quantity > 1 ? ' × '.$o->quantity : '' }}
@endif
@if ((float) $o->amount > 0)
{{ $kind === 'bezahlt' ? 'Bezahlt' : 'Gesamt' }}: {{ number_format($o->totalDue(), 2, ',', '.') }} €{{ (float) $o->shipping_amount > 0 ? ' (inkl. Versand '.number_format((float) $o->shipping_amount, 2, ',', '.').' €)' : '' }}
@endif
@if ($kind === 'eingang' && $o->totalDue() > 0 && $o->payment_method !== 'stripe' && ! in_array($o->type, ['anfrage', 'service'], true))

Zahlung per Überweisung:
@if (! empty($bank['iban']))
Kontoinhaber: {{ $bank['holder'] ?? $c['owner'] }}
IBAN: {{ $bank['iban'] }}
@if (! empty($bank['bic']))BIC: {{ $bank['bic'] }}
@endif
Betrag: {{ number_format($o->totalDue(), 2, ',', '.') }} €
Verwendungszweck: {{ $o->number }}
@else
Die Zahlungsdaten schicken wir dir in Kürze. Verwendungszweck: {{ $o->number }}
@endif
@endif

@if ($o->type === 'kurs' && $o->course_start && ! $o->rebookBlocker())
Umbuchen ({{ config('ulbrich.booking.free_rebookings') }}× kostenlos bis {{ $o->rebookDeadline()->format('d.m.Y, H:i').' Uhr' }}): {{ $o->manageUrl() }}
@endif
@if ($kind === 'bezahlt' && $o->invoice_number)
Rechnung {{ $o->invoice_number }} – als PDF im Anhang
@endif
@if ($o->type === 'kurs' && $o->valueValidUntil())
Buchungswert verwendbar bis {{ $o->valueValidUntil()->format('d.m.Y') }}
@endif
@if (\App\Support\Tax::note()){{ \App\Support\Tax::note() }}
@endif
AGB: https://messermanufaktur-ulbrich.de/agb/ · Widerrufsbelehrung: https://messermanufaktur-ulbrich.de/widerruf/ · Vertrag widerrufen: https://messermanufaktur-ulbrich.de/widerrufen/
@break
@case('umgebucht')
dein Schmiedekurs ist umgebucht – der Preis bleibt gleich.
Neuer Kurstag: {{ $o->course_start?->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}
Kostenlose Umbuchungen: noch {{ $o->freeRebookingsLeft() }} von {{ config('ulbrich.booking.free_rebookings') }}
@if (! $o->rebookBlocker())Termin ansehen oder erneut umbuchen: {{ $o->manageUrl() }}
@endif
@break
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
@case('erinnerung')
in wenigen Tagen stehst du bei uns an der Esse – wir freuen uns auf dich! Den Termin kannst du über den Anhang in deinen Kalender übernehmen.

Kurs: {{ $o->course?->name }} · {{ $o->participants }} {{ $o->participants == 1 ? 'Person' : 'Personen' }}
Kurstag: {{ $o->course_start?->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}@if ($o->course_end && $o->course_start && ! $o->course_end->isSameDay($o->course_start)) bis {{ $o->course_end->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}@endif

Beginn: {{ config('ulbrich.booking.course_start_time') }} Uhr
Ort: {{ $c['name'] }}, {{ $c['address']['street'] }}, {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }} ({{ $c['address']['district'] }})
Route: https://www.google.com/maps/dir/?api=1&destination={{ rawurlencode($c['name'].', '.$c['address']['street'].', '.$c['address']['postal_code'].' '.$c['address']['locality']) }}
@foreach (['parking' => 'Parken', 'transit' => 'Mit Bus & Bahn', 'clothing' => 'Kleidung', 'bring' => 'Bitte mitbringen'] as $k => $label)
@if (! empty(config('ulbrich.course_info.'.$k)))
{{ $label }}: {{ config('ulbrich.course_info.'.$k) }}
@endif
@endforeach

Du kommst später, bist krank oder hast eine Frage? Ruf an: {{ $c['phone']['display'] }} – oder antworte auf diese E-Mail.
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
