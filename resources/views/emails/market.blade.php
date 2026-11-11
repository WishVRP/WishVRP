{{-- Mails an Aussteller des Kunst- & Handwerkermarkts. Inline-Styles wie bei allen E-Mails. --}}
@php
    $gold = '#c8a24a'; $goldDark = '#a8842f'; $ink = '#1b1d21'; $muted = '#5d6370'; $night = '#121316'; $line = '#e6e3dc';
    $ad = $c['address']; $site = 'https://messermanufaktur-ulbrich.de'; $p = 'margin:0 0 16px;';
    $row = 'padding:9px 0;border-bottom:1px solid '.$line.';font-family:Arial,Helvetica,sans-serif;font-size:14px;';
    $lbl = $row.'color:'.$muted.';width:42%;vertical-align:top;';
    $eur = fn ($v) => number_format((float) $v, 2, ',', '.').' €';
@endphp
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light only"><title>{{ $title }}</title></head>
<body style="margin:0;padding:0;background:#efede8;font-family:Georgia,'Times New Roman',serif;color:{{ $ink }};">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#efede8;"><tr><td align="center" style="padding:28px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
    <tr><td style="background:{{ $night }};padding:26px 32px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
            <td style="color:#fff;font-size:21px;line-height:1.2;">{{ $m['name'] }}<br><span style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1px;color:{{ $gold }};text-transform:uppercase;">bei der {{ $c['name'] }}</span></td>
            <td align="right" style="font-family:Menlo,Consolas,monospace;font-size:11px;color:#9aa0aa;white-space:nowrap;">{{ $a->code }}</td>
        </tr></table>
    </td></tr>
    <tr><td style="height:4px;line-height:4px;font-size:0;background:linear-gradient(90deg,#d9b45f,#a0612b,#6b4b8a,#2d5f93);background-color:{{ $gold }};">&nbsp;</td></tr>

    <tr><td style="padding:34px 32px 8px;">
        <div style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:{{ $goldDark }};">Aussteller-Bewerbung</div>
        <h1 style="margin:8px 0 0;font-size:28px;line-height:1.2;font-weight:normal;">{{ $title }}</h1>
    </td></tr>

    <tr><td style="padding:22px 32px 6px;font-size:16px;line-height:1.65;">
        <p style="{{ $p }}">Hallo {{ $firstName }},</p>
        @switch($kind)
            @case('eingang')
                <p style="{{ $p }}">danke für deine Bewerbung für unseren {{ $m['name'] }}! Dein Wunschstand ist für dich vorgemerkt, bis wir deine Bewerbung geprüft haben. Wir melden uns so bald wie möglich.</p>
                <p style="{{ $p }}">Bitte beachte: Die Bewerbung ist noch keine Zusage. Verbindlich wird dein Stand erst nach Prüfung, Vertrag und bestätigtem Zahlungseingang.</p>
                @break
            @case('angenommen')
                <p style="{{ $p }}">wir freuen uns: Deine Bewerbung passt zu unserem Markt – <b>du bist dabei!</b></p>
                <p style="{{ $p }}">Im Anhang findest du den <b>Standmietvertrag</b>, die <b>Marktordnung</b> und den <b>Standplan</b>. Bitte schick uns den Vertrag ausgefüllt und unterschrieben zurück (als Antwort auf diese E-Mail genügt ein Scan oder Foto) und überweise die Standgebühr. Mit Vertrag und Zahlungseingang ist dein Stand fest gebucht.</p>
                @break
            @case('abgelehnt')
                <p style="{{ $p }}">danke für dein Interesse und deine Bewerbung. Leider können wir dir für diesen Markt keinen Stand anbieten. Der Stand ist damit wieder frei.</p>
                @break
            @default
                <p style="{{ $p }}">deine Standgebühr ist eingegangen – <b>dein Stand ist fest gebucht.</b> Wir freuen uns auf dich und dein Handwerk!</p>
        @endswitch
        @if ($personal)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;"><tr><td style="border-left:3px solid {{ $gold }};padding:4px 0 4px 16px;font-style:italic;">{!! nl2br(e($personal)) !!}</td></tr></table>
        @endif
    </td></tr>

    @if ($kind !== 'abgelehnt')
    <tr><td style="padding:4px 32px 8px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid {{ $night }};">
            <tr><td style="{{ $lbl }}">Aussteller-Code</td><td style="{{ $row }}font-family:Menlo,Consolas,monospace;font-size:16px;letter-spacing:1px;"><b>{{ $a->code }}</b></td></tr>
            <tr><td style="{{ $lbl }}">Stand</td><td style="{{ $row }}"><b>Nr. {{ $a->stand }}</b>@if ($stand) · {{ $stand['label'] }} · {{ $stand['size'] }}@endif</td></tr>
            <tr><td style="{{ $lbl }}">Termin</td><td style="{{ $row }}">{{ $date ?: 'wird noch bekannt gegeben' }} · Samstag und Sonntag</td></tr>
            <tr><td style="{{ $lbl }}">Ort</td><td style="{{ $row }}">{{ $m['place'] }}</td></tr>
            <tr><td style="{{ $lbl }}">Sortiment</td><td style="{{ $row }}">{{ $a->category }}</td></tr>
            @if ($a->tables)<tr><td style="{{ $lbl }}">Ausstattung</td><td style="{{ $row }}">{{ $a->tables }} {{ $a->tables > 1 ? 'Biertische' : 'Biertisch' }} (laut Buchung)</td></tr>@endif
            @if ($a->food || $a->demo)<tr><td style="{{ $lbl }}">Angemeldet</td><td style="{{ $row }}">{{ implode(' · ', array_filter([$a->food ? 'Lebensmittel / Getränke' : null, $a->demo ? 'Vorführung' : null])) }} – Freigabe durch uns</td></tr>@endif
            <tr><td style="{{ $lbl }}">Gebühr</td><td style="{{ $row }}">Stand {{ $eur($stand['price'] ?? 0) }} + Werbepauschale {{ $eur($m['fees'][0]['price']) }}@if ($a->lighting) + Beleuchtung {{ $eur($m['fees'][1]['price']) }}@endif = <b style="font-size:16px;">{{ $eur($a->amount) }}</b></td></tr>
        </table>
    </td></tr>
    @endif

    @if ($kind === 'angenommen')
    <tr><td style="padding:16px 32px 4px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed {{ $gold }};border-radius:6px;"><tr><td style="padding:18px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;">
            <div style="font-family:Menlo,Consolas,monospace;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:{{ $goldDark }};margin-bottom:6px;">Standgebühr überweisen</div>
            @if (! empty($bank['iban']))
                Kontoinhaber: <b>{{ $bank['holder'] ?? $c['owner'] }}</b><br>
                IBAN: <span style="font-family:Menlo,Consolas,monospace;font-size:15px;letter-spacing:1px;">{{ trim(chunk_split(str_replace(' ', '', $bank['iban']), 4, ' ')) }}</span><br>
                @if (! empty($bank['bic']))BIC: {{ $bank['bic'] }}<br>@endif
                Betrag: <b>{{ $eur($a->amount) }}</b> · Verwendungszweck: <b style="font-family:Menlo,Consolas,monospace;">{{ $a->code }}</b>
            @else
                Die Zahlungsdaten schicken wir dir in Kürze. Betrag: <b>{{ $eur($a->amount) }}</b>, Verwendungszweck: <b style="font-family:Menlo,Consolas,monospace;">{{ $a->code }}</b>.
            @endif
        </td></tr></table>
    </td></tr>
    @endif

    @if (in_array($kind, ['angenommen', 'bezahlt'], true))
    <tr><td style="padding:16px 32px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:{{ $ink }};">
        <b>Gut zu wissen</b>
        <ul style="margin:6px 0 0;padding-left:18px;">@foreach ($m['rules'] as $r)<li>{{ $r }}</li>@endforeach</ul>
    </td></tr>
    @endif

    <tr><td style="padding:22px 32px 30px;font-size:16px;line-height:1.65;">
        <p style="margin:0;">Fragen? Antworte einfach auf diese E-Mail oder ruf an: {{ $c['phone']['display'] }}.</p>
        <p style="margin:18px 0 0;">Herzliche Grüße<br><b>{{ $c['owner'] }}</b><br><span style="color:{{ $muted }};font-size:14px;">{{ $c['name'] }}</span></p>
    </td></tr>
    <tr><td style="padding:20px 32px;background:{{ $night }};font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#9aa0aa;">
        {{ $c['name'] }} · {{ $c['owner'] }} · {{ $ad['street'] }} · {{ $ad['postal_code'] }} {{ $ad['locality'] }}<br>
        Telefon {{ $c['phone']['display'] }} · <a href="mailto:{{ $c['email'] }}" style="color:#9aa0aa;">{{ $c['email'] }}</a> · <a href="{{ $site }}/kunsthandwerkermarkt/" style="color:{{ $gold }};text-decoration:none;">Zum Markt</a><br>
        <a href="{{ $site }}/impressum/" style="color:#9aa0aa;">Impressum</a> · <a href="{{ $site }}/datenschutz/" style="color:#9aa0aa;">Datenschutz</a>
    </td></tr>
</table></td></tr></table>
</body>
</html>
