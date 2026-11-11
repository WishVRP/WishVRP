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
        <a href="{{ $site }}/impressum/" style="color:#9aa0aa;">Impressum</a> · <a href="{{ $site }}/datenschutz/" style="color:#9aa0aa;">Datenschutz</a><br>
        <span style="color:#6f7580;">Diese E-Mail gehört zu deiner Bestellung bzw. Buchung {{ $o->number }}.@if ($reviewUrl) Die Bitte um eine Bewertung bekommst du, weil du bei der Bestellung zugestimmt hast; keine weiteren Anfragen gewünscht? Antworte einfach auf diese E-Mail.@endif</span>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
