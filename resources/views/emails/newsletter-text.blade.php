{{ $issue->subject }}
@if ($code)

DEIN PERSÖNLICHER RABATTCODE: {{ $code->percent }} % – {{ $code->code }}
Einmal einlösbar bis {{ $code->valid_until->locale('de')->isoFormat('D. MMMM YYYY') }} bei einer Bestellung oder Kursbuchung auf unserer Website (Feld „Rabattcode“, mit dieser E-Mail-Adresse). Ohne Versandkosten, nicht kombinierbar, keine Barauszahlung.
@endif

@if ($issue->body)
{{ $issue->body }}
@else
Hallo,

hier ist das Neueste aus der Werkstatt in {{ $c['address']['district'] }}.

NÄCHSTE SCHMIEDEKURSE
@forelse ($b['dates'] ?? [] as $d)
- {{ $d['date'] }} · {{ $d['course'] }} · {{ $d['seats'] }} – {{ $d['url'] }}
@empty
Aktuell sind keine Termine online – ruf einfach an: {{ $c['phone']['display'] }}
@endforelse

EDITION 100
Noch {{ $b['edition']['free'] ?? '–' }} von {{ $b['edition']['regular'] ?? 90 }} Nummern frei: {{ $b['edition']['url'] ?? '' }}
@if (! empty($b['tip']))

TIPP DER WOCHE: {{ $b['tip']['title'] }}
{{ $b['tip']['text'] }} {{ $b['tip']['url'] }}
@endif

Viele Grüße aus der Werkstatt
{{ $c['owner'] }}
@endif

--
Du bekommst diese E-Mail, weil du dich{{ $since ? ' am '.$since : '' }} für den Newsletter angemeldet und die Anmeldung bestätigt hast.
Abmelden: {{ $unsubscribe }}
{{ $c['name'] }} · {{ $c['owner'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }} · {{ $c['phone']['display'] }} · {{ $c['email'] }}
