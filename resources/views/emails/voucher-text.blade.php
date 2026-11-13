@php $buyer = $v->order?->name; @endphp
@if ($audience === 'recipient')
Hallo{{ $v->recipient ? ' '.$v->recipient : '' }},

{{ $buyer ?: 'Jemand' }} schenkt dir einen Tag an der Esse bei der {{ $c['name'] }}!
@if ($v->gift_message)

„{{ $v->gift_message }}“
@endif

Deinen Gutschein findest du als PDF im Anhang – zum Ausdrucken oder einfach auf dem Handy.
@else
Hallo {{ $buyer }},

vielen Dank! Im Anhang ist dein Gutschein als PDF – zum Ausdrucken und Verschenken.
@if ($v->recipient_email && ! $v->sent_to_recipient_at)

Wie gewünscht schicken wir ihn außerdem am {{ $v->send_on ? $v->send_on->format('d.m.Y') : 'Tag der Zahlung' }} per E-Mail an {{ $v->recipient_email }}.
@elseif ($v->sent_to_recipient_at)

Wir haben ihn außerdem an {{ $v->recipient_email }} geschickt.
@endif
@endif

Gutscheincode: {{ $v->code }}
Für: {{ $v->course?->name ?? 'Schmiedekurs' }} · {{ $v->persons }} {{ $v->persons === 1 ? 'Person' : 'Personen' }}
Gültig bis: {{ $v->valid_until->locale('de')->isoFormat('D. MMMM YYYY') }}

So wird er eingelöst: Auf {{ rtrim(config('app.url'), '/') }}/schmiedekurse/buchen/ den Kurs und einen freien Tag wählen und den Gutscheincode eintragen.

Fragen? {{ $c['phone']['display'] }} · {{ $c['email'] }}

{{ $c['owner'] }}
{{ $c['name'] }}
