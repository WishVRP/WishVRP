{{-- Status-Mail an Kundschaft (HTML). E-Mail-Programme brauchen Tabellen und Inline-Styles; die CSP der Website betrifft E-Mails nicht. --}}
@php
    $gold = '#c8a24a'; $goldDark = '#a8842f'; $ink = '#1b1d21'; $muted = '#5d6370'; $night = '#121316'; $line = '#e6e3dc';
    $a = $c['address'];
    $site = 'https://messermanufaktur-ulbrich.de';
    $p = 'margin:0 0 16px;';
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light only">
<title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#efede8;font-family:Georgia,'Times New Roman',serif;color:{{ $ink }};-webkit-text-size-adjust:100%;">
<span style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $title }} – {{ $o->number }} · {{ $c['name'] }}</span>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#efede8;">
<tr><td align="center" style="padding:28px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);">

    {{-- Kopf: dunkel wie die Schmiede, Anlassfarben-Linie --}}
    <tr><td style="background:{{ $night }};padding:26px 32px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
            <td style="color:#ffffff;font-size:21px;line-height:1.2;">{{ $c['name'] }}<br>
                <span style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1px;color:{{ $gold }};text-transform:uppercase;">Schmiedehandwerk seit {{ $c['heritage']['since'] }}</span></td>
            <td align="right" style="font-family:Menlo,Consolas,monospace;font-size:11px;color:#9aa0aa;white-space:nowrap;">Nr. {{ $o->number }}</td>
        </tr></table>
    </td></tr>
    <tr><td style="height:4px;line-height:4px;font-size:0;background:linear-gradient(90deg,#d9b45f,#a0612b,#6b4b8a,#2d5f93);background-color:{{ $gold }};">&nbsp;</td></tr>

    {{-- Überschrift --}}
    <tr><td style="padding:34px 32px 8px;">
        <div style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:{{ $goldDark }};">
            @switch($kind)
                @case('eingang') Eingangsbestätigung @break
                @case('bezahlt') Auftragsbestätigung @break
                @case('umgebucht') Umbuchung @break
                @case('in_arbeit') Bestellstatus · In Arbeit @break
                @case('versendet') Bestellstatus · Versendet @break
                @case('abholbereit') Bestellstatus · Abholbereit @break
                @case('zugestellt') Bestellstatus · Zugestellt @break
                @default Schmiedekurs
            @endswitch
        </div>
        <h1 style="margin:8px 0 0;font-size:30px;line-height:1.2;font-weight:normal;color:{{ $ink }};">{{ $title }}</h1>
    </td></tr>

    {{-- Fortschritt (nur Bestellungen) --}}
    @if ($steps)
    <tr><td style="padding:22px 32px 4px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
        @foreach ($steps as $i => $s)
            @php $done = $i <= $stepIndex; $now = $i === $stepIndex; @endphp
            <td width="25%" align="center" valign="top" style="font-family:Arial,Helvetica,sans-serif;font-size:11px;color:{{ $done ? $ink : '#a3a7ae' }};">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                    <td valign="middle" style="font-size:0;line-height:0;">@if ($i > 0)<div style="height:3px;font-size:0;line-height:0;background:{{ $done ? $gold : $line }};">&nbsp;</div>@endif</td>
                    <td width="26" align="center" style="width:26px;">
                        <div style="width:22px;height:22px;line-height:22px;border-radius:11px;margin:0 auto;font-size:12px;font-weight:bold;text-align:center;
                            background:{{ $now ? $gold : ($done ? $night : '#ffffff') }};color:{{ $now ? $night : ($done ? '#ffffff' : '#a3a7ae') }};border:2px solid {{ $done ? ($now ? $gold : $night) : $line }};">{{ $done && ! $now ? '✓' : $i + 1 }}</div>
                    </td>
                    <td valign="middle" style="font-size:0;line-height:0;">@if ($i < count($steps) - 1)<div style="height:3px;font-size:0;line-height:0;background:{{ $i < $stepIndex ? $gold : $line }};">&nbsp;</div>@endif</td>
                </tr></table>
                <div style="margin-top:6px;{{ $now ? 'font-weight:bold;' : '' }}">{{ $s['label'] }}</div>
            </td>
        @endforeach
        </tr></table>
    </td></tr>
    @endif

    {{-- Text --}}
    <tr><td style="padding:24px 32px 6px;font-size:16px;line-height:1.65;">
        <p style="{{ $p }}">Hallo {{ $firstName }},</p>

        @switch($kind)
            @case('eingang')
                @if ($o->type === 'kurs')
                    <p style="{{ $p }}">vielen Dank für deine Buchung bei uns! Hier ist die Übersicht. Dein Kurstag ist verbindlich für dich reserviert, sobald deine Zahlung eingegangen ist – dann bekommst du von uns die endgültige Bestätigung.</p>
                @elseif ($o->type === 'gutschein')
                    <p style="{{ $p }}">vielen Dank für deine Gutscheinbestellung! Sobald deine Zahlung eingegangen ist, schicken wir dir den Gutschein mit persönlichem Code per E-Mail.</p>
                @elseif (in_array($o->type, ['anfrage', 'service'], true))
                    <p style="{{ $p }}">vielen Dank für deine Anfrage – sie ist bei uns angekommen. Wir melden uns persönlich bei dir, um Details, Preis und Lieferzeit zu besprechen.</p>
                @else
                    <p style="{{ $p }}">vielen Dank für deine Bestellung! Wir haben sie erhalten und melden uns, sobald die Zahlung eingegangen ist und wir mit deinem Messer loslegen.</p>
                @endif
                @break
            @case('bezahlt')
                @if ($o->type === 'kurs')
                    <p style="{{ $p }}">deine Zahlung ist eingegangen – dein Kurstag ist damit fest gebucht. Wir freuen uns auf dich an der Esse!</p>
                @elseif ($o->type === 'gutschein')
                    <p style="{{ $p }}">deine Zahlung ist eingegangen, vielen Dank! Den Gutschein mit persönlichem Code bekommst du in einer eigenen E-Mail.</p>
                @else
                    <p style="{{ $p }}">deine Zahlung ist eingegangen, vielen Dank! {{ $o->isPickup() ? 'Wir melden uns, um einen Abholtermin in der Manufaktur abzustimmen.' : 'Wir melden uns, sobald dein Messer in Arbeit bzw. unterwegs ist.' }}</p>
                @endif
                @break
            @case('umgebucht')
                <p style="{{ $p }}">dein Schmiedekurs ist umgebucht. Der Preis bleibt wie gebucht – es entstehen dir keine Kosten.</p>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;border-top:2px solid {{ $night }};">
                    <tr><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:14px;color:{{ $muted }};width:42%;">Kurs</td><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:14px;">{{ $o->course?->name }} · {{ $o->participants }} {{ $o->participants == 1 ? 'Person' : 'Personen' }}</td></tr>
                    <tr><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:14px;color:{{ $muted }};">Neuer Kurstag</td><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:16px;"><b>{{ $o->course_start?->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}</b></td></tr>
                    <tr><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:14px;color:{{ $muted }};">Kostenlose Umbuchungen</td><td style="padding:9px 0;border-bottom:1px solid {{ $line }};font-family:Arial,Helvetica,sans-serif;font-size:14px;">noch {{ $o->freeRebookingsLeft() }} von {{ config('ulbrich.booking.free_rebookings') }}</td></tr>
                </table>
                @break
            @case('in_arbeit')
                <p style="{{ $p }}">gute Nachrichten aus der Werkstatt: Wir haben mit deiner Bestellung begonnen. Ab jetzt wird geschmiedet, geschliffen und von Hand montiert – mit der Sorgfalt, die ein Messer für viele Jahre braucht.</p>
                @break
            @case('versendet')
                <p style="{{ $p }}">dein Paket hat die Werkstatt verlassen und ist auf dem Weg zu dir. Über den Link unten kannst du jederzeit sehen, wo es gerade ist.</p>
                @break
            @case('abholbereit')
                <p style="{{ $p }}">deine Bestellung ist fertig und liegt in der Manufaktur für dich bereit. Ruf kurz an oder antworte auf diese E-Mail, dann stimmen wir einen Abholtermin ab.</p>
                @break
            @case('zugestellt')
                <p style="{{ $p }}">laut Versanddienst ist dein Paket angekommen. Wir hoffen, alles ist heil bei dir gelandet und du hast Freude an deinem neuen Stück aus unserer Werkstatt.</p>
                <p style="{{ $p }}">Ein Tipp für lange Freude: von Hand spülen, gleich abtrocknen und die Klinge ab und zu leicht einölen – alles Weitere steht im beiliegenden Pflegeheft.</p>
                @break
            @default
                <p style="{{ $p }}">danke, dass du heute bei uns an der Esse gestanden hast{{ $o->course ? ' – beim '.$o->course->name : '' }}. Wir hoffen, du hattest einen richtig guten Tag und bist mit deinem Werkstück zufrieden nach Hause gefahren.</p>
        @endswitch

        @if ($personal)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;"><tr>
                <td style="border-left:3px solid {{ $gold }};padding:4px 0 4px 16px;font-style:italic;color:{{ $ink }};">{!! nl2br(e($personal)) !!}</td>
            </tr></table>
        @endif
    </td></tr>

    {{-- Bestellübersicht (Bestell- und Zahlungsbestätigung) --}}
    @if (in_array($kind, ['eingang', 'bezahlt'], true))
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.').' €';
        $row = 'padding:9px 0;border-bottom:1px solid '.$line.';font-family:Arial,Helvetica,sans-serif;font-size:14px;';
        $lbl = $row.'color:'.$muted.';width:42%;vertical-align:top;';
        $courseDays = $o->type === 'kurs' && $o->course_start
            ? $o->course_start->locale('de')->isoFormat('dddd, D. MMMM YYYY').($o->course_end && ! $o->course_end->isSameDay($o->course_start) ? ' – '.$o->course_end->locale('de')->isoFormat('dddd, D. MMMM YYYY') : '')
            : null;
        $amount = (float) $o->amount;
        $before = $o->discount_percent ? round($amount / (1 - $o->discount_percent / 100), 2) : null;
    @endphp
    <tr><td style="padding:4px 32px 8px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid {{ $night }};">
            <tr><td style="{{ $lbl }}">Vorgangsnummer</td><td style="{{ $row }}font-family:Menlo,Consolas,monospace;">{{ $o->number }}</td></tr>
            <tr><td style="{{ $lbl }}">Datum</td><td style="{{ $row }}">{{ ($o->created_at ?? now())->locale('de')->isoFormat('D. MMMM YYYY') }}</td></tr>
            @if ($o->type === 'kurs')
                <tr><td style="{{ $lbl }}">Kurs</td><td style="{{ $row }}"><b>{{ $o->course?->name ?? 'Schmiedekurs' }}</b></td></tr>
                <tr><td style="{{ $lbl }}">Kurstag</td><td style="{{ $row }}">{{ $courseDays ?? ($o->preferred_date ? 'Wunsch: '.$o->preferred_date : 'wird abgestimmt') }}</td></tr>
                <tr><td style="{{ $lbl }}">Teilnehmer</td><td style="{{ $row }}">{{ $o->participants }} {{ $o->participants == 1 ? 'Person' : 'Personen' }}</td></tr>
                <tr><td style="{{ $lbl }}">Ort</td><td style="{{ $row }}">{{ $c['name'] }}, {{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }} ({{ $a['district'] }})</td></tr>
            @else
                <tr><td style="{{ $lbl }}">{{ in_array($o->type, ['anfrage', 'service'], true) ? 'Anliegen' : 'Artikel' }}</td><td style="{{ $row }}"><b>{{ $o->item ?? $o->typeLabel() }}</b>@if ($o->quantity > 1) × {{ $o->quantity }}@endif</td></tr>
            @endif
            @if ($amount > 0)
                @if ($before)
                    <tr><td style="{{ $lbl }}">Preis</td><td style="{{ $row }}">{{ $eur($before) }}</td></tr>
                    <tr><td style="{{ $lbl }}">Rabatt {{ $o->discount_percent }} %@if ($o->discount_code) ({{ $o->discount_code }})@endif</td><td style="{{ $row }}">− {{ $eur($before - $amount) }}</td></tr>
                @else
                    <tr><td style="{{ $lbl }}">Preis</td><td style="{{ $row }}">{{ $eur($amount) }}</td></tr>
                @endif
                @if ((float) $o->shipping_amount > 0)
                    <tr><td style="{{ $lbl }}">Versand{{ $o->country && $o->country_code !== 'DE' ? ' nach '.$o->country : '' }}</td><td style="{{ $row }}">{{ $eur($o->shipping_amount) }}@if ($o->shipping_note && str_contains($o->shipping_note, 'Zoll'))<br><span style="font-size:12px;color:{{ $muted }};">zzgl. Zoll/Einfuhrabgaben im Zielland</span>@endif</td></tr>
                @elseif ($o->shipping_method === 'versand' && $o->shipping_amount === null)
                    <tr><td style="{{ $lbl }}">Versand{{ $o->country ? ' nach '.$o->country : '' }}</td><td style="{{ $row }}"><b>wird dir per E-Mail genannt</b> – erst danach zahlst du</td></tr>
                @endif
                @if ((float) $o->customs_amount > 0)
                    <tr><td style="{{ $lbl }}">Zoll &amp; Einfuhrabgaben{{ $o->country ? ' '.$o->country : '' }}</td><td style="{{ $row }}">{{ $eur($o->customs_amount) }}<br><span style="font-size:12px;color:{{ $muted }};">im Voraus bezahlt, Schätzung</span></td></tr>
                @endif
                @if ((float) $o->voucher_amount > 0)
                    <tr><td style="{{ $lbl }}">Gutschein</td><td style="{{ $row }}">− {{ $eur($o->voucher_amount) }}</td></tr>
                @endif
                <tr><td style="{{ $row }}font-weight:bold;color:{{ $ink }};">{{ $kind === 'bezahlt' ? 'Bezahlt' : ($o->shipping_method === 'versand' && $o->shipping_amount === null ? 'Zwischensumme (ohne Versand)' : 'Gesamt') }}</td><td style="{{ $row }}font-size:18px;font-weight:bold;">{{ $eur($o->totalDue()) }}</td></tr>
                @if (\App\Support\Tax::included($o->totalDue()))
                    <tr><td style="{{ $lbl }}">darin enthalten {{ \App\Support\Tax::RATE }} % MwSt.</td><td style="{{ $row }}">{{ $eur(\App\Support\Tax::included($o->totalDue())) }}</td></tr>
                @elseif (\App\Support\Tax::mode() === 'kleinunternehmer')
                    <tr><td colspan="2" style="{{ $row }}font-size:12px;color:{{ $muted }};">{{ \App\Support\Tax::note() }}</td></tr>
                @endif
            @endif
            @if ($o->type === 'kurs' && $o->course_start && $o->manage_token && ! $o->rebookBlocker())
                <tr><td style="{{ $lbl }}">Umbuchen</td><td style="{{ $row }}">{{ config('ulbrich.booking.free_rebookings') }}× kostenlos bis {{ $o->rebookDeadline()->format('d.m.Y, H:i').' Uhr' }} – <a href="{{ $o->manageUrl() }}" style="color:#2d5f93;">Termin online umbuchen</a></td></tr>
            @endif
            @if ($o->type === 'kurs' && $o->valueValidUntil())
                <tr><td style="{{ $lbl }}">Buchungswert</td><td style="{{ $row }}">verwendbar bis {{ $o->valueValidUntil()->format('d.m.Y') }}</td></tr>
            @endif
            @if (! in_array($o->type, ['kurs', 'anfrage', 'service'], true) && $o->shipping_method)
                <tr><td style="{{ $lbl }}">Lieferung</td><td style="{{ $row }}">
                    @if ($o->isPickup()) Abholung in der Manufaktur ({{ $a['district'] }}), Termin nach Absprache
                    @else Versand an: {{ $o->name }}@if ($o->street), {{ $o->street }}, {{ $o->postal_code }} {{ $o->city }}@if ($o->country && ! in_array(mb_strtolower($o->country), ['de', 'deutschland', 'germany'], true)), {{ $o->country }}@endif @endif
                    @endif
                </td></tr>
            @endif
            @if ($kind === 'bezahlt' && $o->payment_method)
                <tr><td style="{{ $lbl }}">Zahlart</td><td style="{{ $row }}">{{ ['stripe' => 'Online (Karte & Co.)', 'ueberweisung' => 'Überweisung', 'bar' => 'Bar', 'karte' => 'Karte vor Ort', 'gutschein' => 'Gutschein', 'rabatt' => '–'][$o->payment_method] ?? $o->payment_method }}</td></tr>
            @endif
        </table>
        @if ($amount > 0)
            <div style="margin-top:6px;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:{{ $muted }};">Preise wie auf der Website angegeben.</div>
        @endif
    </td></tr>

    {{-- Zahlung per Überweisung --}}
    @php $exportReview = $o->export_note && str_contains($o->export_note, 'Einfuhr prüfen'); @endphp
    @if ($kind === 'eingang' && $exportReview)
    <tr><td style="padding:16px 32px 4px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-left:4px solid #b9852a;background:#fbf3e2;"><tr><td style="padding:14px 18px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;">
        <b>Wir prüfen den Versand in dein Land.</b> Für {{ $o->country }} klären wir die Einfuhrbestimmungen für Messer persönlich und melden uns bei dir. Erst danach zahlst du – vorher entstehen dir keine Kosten.
    </td></tr></table></td></tr>
    @endif
    @if ($kind === 'eingang' && ! $exportReview && $o->totalDue() > 0 && $o->payment_method !== 'stripe' && ! in_array($o->type, ['anfrage', 'service'], true) && ! ($o->shipping_method === 'versand' && $o->shipping_amount === null))
    <tr><td style="padding:16px 32px 4px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed {{ $gold }};border-radius:6px;">
            <tr><td style="padding:18px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;">
                <div style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:{{ $goldDark }};margin-bottom:6px;">Zahlung per Überweisung</div>
                @if (! empty($bank['iban']))
                    Kontoinhaber: <b>{{ $bank['holder'] ?? $c['owner'] }}</b><br>
                    IBAN: <span style="font-family:Menlo,Consolas,monospace;font-size:15px;letter-spacing:1px;">{{ trim(chunk_split(str_replace(' ', '', $bank['iban']), 4, ' ')) }}</span><br>
                    @if (! empty($bank['bic']))BIC: {{ $bank['bic'] }}@if (! empty($bank['name'])) · {{ $bank['name'] }}@endif<br>@endif
                    Betrag: <b>{{ number_format($o->totalDue(), 2, ',', '.') }} €</b><br>
                    Verwendungszweck: <b style="font-family:Menlo,Consolas,monospace;">{{ $o->number }}</b>
                @else
                    Die Zahlungsdaten schicken wir dir in Kürze per E-Mail. Betrag: <b>{{ number_format($o->totalDue(), 2, ',', '.') }} €</b>, Verwendungszweck: <b style="font-family:Menlo,Consolas,monospace;">{{ $o->number }}</b>.
                @endif
            </td></tr>
        </table>
    </td></tr>
    @endif
    @endif

    {{-- Infokasten --}}
    @if ($kind === 'in_arbeit' && $o->production_time || $kind === 'versendet' || in_array($kind, ['zugestellt', 'abholbereit'], true))
    <tr><td style="padding:4px 32px 8px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f0;border:1px solid {{ $line }};border-radius:6px;">
            <tr><td style="padding:18px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:{{ $ink }};">
                <div style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:{{ $muted }};margin-bottom:6px;">Deine Bestellung</div>
                <b>{{ $o->summary() }}</b>
                @if ($kind === 'in_arbeit' && $o->production_time)
                    <br>Voraussichtliche Fertigung: <b>{{ $o->production_time }}</b>
                @endif
                @if ($kind === 'versendet')
                    @if ($o->carrierName())<br>Versand mit: <b>{{ $o->carrierName() }}</b>@endif
                    @if ($o->tracking_number)<br>Sendungsnummer: <span style="font-family:Menlo,Consolas,monospace;font-size:15px;letter-spacing:1px;">{{ $o->tracking_number }}</span>@endif
                @endif
                @if ($kind === 'abholbereit')
                    <br>Abholung: {{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['district'] }} · nach Absprache
                @endif
            </td></tr>
        </table>
    </td></tr>
    @endif

    {{-- Hauptknopf --}}
    @php
        $cta = match ($kind) {
            'versendet' => $o->trackingLink() ? ['Sendung verfolgen', $o->trackingLink()] : null,
            'abholbereit' => ['Anrufen: '.$c['phone']['display'], 'tel:'.$c['phone']['e164']],
            'umgebucht' => ! $o->rebookBlocker() ? ['Termin ansehen oder erneut umbuchen', $o->manageUrl()] : null,
            'zugestellt', 'kurs' => $reviewUrl ? ['★ Jetzt bei Google bewerten', $reviewUrl] : null,
            default => null,
        };
    @endphp
    @if (in_array($kind, ['zugestellt', 'kurs'], true) && $reviewUrl)
    <tr><td style="padding:18px 32px 0;font-size:16px;line-height:1.65;">
        <p style="margin:0;">{{ $kind === 'kurs' ? 'Wenn dir der Tag gefallen hat:' : 'Wenn du zufrieden bist:' }} Eine kurze Google-Bewertung hilft uns als kleiner Familienbetrieb enorm – sie dauert keine Minute und zeigt anderen, was sie bei uns erwartet.</p>
        <p style="margin:12px 0 0;font-size:26px;letter-spacing:4px;color:{{ $gold }};text-align:center;">★★★★★</p>
    </td></tr>
    @endif
    @if ($cta)
    <tr><td align="center" style="padding:20px 32px 8px;">
        <table role="presentation" cellpadding="0" cellspacing="0"><tr>
            <td style="background:{{ $gold }};border-radius:4px;">
                <a href="{{ $cta[1] }}" target="_blank" style="display:inline-block;padding:15px 30px;font-family:Menlo,Consolas,monospace;font-size:14px;font-weight:bold;letter-spacing:.5px;color:{{ $night }};text-decoration:none;">{{ $cta[0] }} &rarr;</a>
            </td>
        </tr></table>
        @if ($kind === 'versendet' && $o->trackingLink())
            <div style="margin-top:10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:{{ $muted }};">Link funktioniert nicht? <a href="{{ $o->trackingLink() }}" style="color:{{ $muted }};word-break:break-all;">{{ $o->trackingLink() }}</a></div>
        @endif
    </td></tr>
    @endif

    {{-- Gruß --}}
    <tr><td style="padding:22px 32px 30px;font-size:16px;line-height:1.65;">
        <p style="margin:0 0 4px;">
            @if ($kind === 'zugestellt') Fragen oder etwas nicht in Ordnung? Antworte einfach auf diese E-Mail – wir kümmern uns.
            @elseif ($kind === 'kurs') Bis zum nächsten Mal an der Esse!
            @else Fragen? Antworte einfach auf diese E-Mail oder ruf an: {{ $c['phone']['display'] }}.
            @endif
        </p>
        <p style="margin:18px 0 0;">Herzliche Grüße aus der Werkstatt<br><b>{{ $c['owner'] }}</b><br><span style="color:{{ $muted }};font-size:14px;">{{ $c['owner_role'] }} · {{ $c['name'] }}</span></p>
    </td></tr>

    {{-- Fuß --}}
    <tr><td style="padding:20px 32px;background:{{ $night }};font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#9aa0aa;">
        {{ $c['name'] }} · {{ $c['owner'] }} · {{ $a['street'] }} · {{ $a['postal_code'] }} {{ $a['locality'] }}<br>
        Telefon {{ $c['phone']['display'] }} · <a href="mailto:{{ $c['email'] }}" style="color:#9aa0aa;">{{ $c['email'] }}</a> · <a href="{{ $site }}/" style="color:{{ $gold }};text-decoration:none;">messermanufaktur-ulbrich.de</a><br>
        <a href="{{ $site }}/impressum/" style="color:#9aa0aa;">Impressum</a> · <a href="{{ $site }}/datenschutz/" style="color:#9aa0aa;">Datenschutz</a>
        @if (in_array($kind, ['eingang', 'bezahlt'], true)) · <a href="{{ $site }}/agb/" style="color:#9aa0aa;">AGB</a> · <a href="{{ $site }}/widerruf/" style="color:#9aa0aa;">Widerrufsbelehrung</a> · <a href="{{ $site }}/widerrufen/" style="color:#9aa0aa;">Vertrag widerrufen</a>@if ($o->type === 'kurs') · <a href="{{ $site }}/kursbedingungen/" style="color:#9aa0aa;">Kursbedingungen</a>@endif @endif<br>
        <span style="color:#6f7580;">Diese E-Mail gehört zu deiner Bestellung bzw. Buchung {{ $o->number }}.@if ($reviewUrl) Die Bitte um eine Bewertung bekommst du, weil du bei der Bestellung zugestimmt hast; keine weiteren Anfragen gewünscht? Antworte einfach auf diese E-Mail.@endif</span>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
