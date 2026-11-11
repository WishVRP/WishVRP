{{-- Newsletter (HTML). E-Mail-Programme brauchen Inline-Styles; die CSP der Website betrifft E-Mails nicht. --}}
@php
    $gold = '#c8a24a'; $ink = '#1b1d21'; $muted = '#5d6370';
    $a = $c['address'];
@endphp
<!DOCTYPE html>
<html lang="de">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $issue->subject }}</title></head>
<body style="margin:0;background:#f2f1ee;font-family:Georgia,'Times New Roman',serif;color:{{ $ink }};">
<span style="display:none;max-height:0;overflow:hidden;">{{ $issue->preheader }}</span>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f1ee;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:6px;overflow:hidden;">
    <tr><td style="background:#121316;padding:24px 28px;color:#fff;">
        <div style="font-size:22px;">{{ $c['name'] }}</div>
        <div style="font-family:Menlo,Consolas,monospace;font-size:12px;color:{{ $gold }};margin-top:4px;">Schmiedehandwerk seit {{ $c['heritage']['since'] }}</div>
    </td></tr>

    @if ($code)
    <tr><td style="padding:24px 28px 0;">
        <table role="presentation" width="100%" style="border:2px dashed {{ $gold }};border-radius:6px;"><tr><td style="padding:18px;text-align:center;">
            <div style="font-family:Menlo,Consolas,monospace;font-size:12px;color:{{ $muted }};letter-spacing:1px;text-transform:uppercase;">Dein persönlicher Rabattcode</div>
            <div style="font-size:30px;margin:6px 0;">{{ $code->percent }} % Rabatt</div>
            <div style="font-family:Menlo,Consolas,monospace;font-size:22px;letter-spacing:3px;color:{{ $ink }};">{{ $code->code }}</div>
            <div style="font-size:13px;color:{{ $muted }};margin-top:8px;">Einmal einlösbar bis {{ $code->valid_until->locale('de')->isoFormat('D. MMMM YYYY') }} bei einer Bestellung oder Kursbuchung auf unserer Website – Feld „Rabattcode“, mit dieser E-Mail-Adresse. Gilt ohne Versandkosten, nicht kombinierbar, keine Barauszahlung.</div>
        </td></tr></table>
    </td></tr>
    @endif

    <tr><td style="padding:24px 28px;font-size:16px;line-height:1.6;">
        @if ($bodyHtml)
            {!! $bodyHtml !!}
        @else
            <p style="margin:0 0 16px;">Hallo,</p>
            <p style="margin:0 0 16px;">hier ist das Neueste aus der Werkstatt in {{ $a['district'] }}.</p>

            <h2 style="font-size:20px;margin:24px 0 8px;">Nächste Schmiedekurse</h2>
            @if (! empty($b['dates']))
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                @foreach ($b['dates'] as $d)
                    <tr><td style="padding:8px 0;border-bottom:1px solid #eee;font-size:15px;">
                        <b>{{ $d['date'] }}</b> · {{ $d['course'] }}<br><span style="color:{{ $muted }};">{{ $d['seats'] }}</span> · <a href="{{ $d['url'] }}" style="color:#2d5f93;">Platz buchen</a>
                    </td></tr>
                @endforeach
                </table>
            @else
                <p style="margin:0;">Aktuell sind keine Termine online – ruf einfach an, dann finden wir einen: {{ $c['phone']['display'] }}.</p>
            @endif

            <h2 style="font-size:20px;margin:24px 0 8px;">Edition 100</h2>
            <p style="margin:0;">Von den {{ $b['edition']['regular'] ?? 90 }} regulären Damast-Klappmessern sind noch <b>{{ $b['edition']['free'] ?? '–' }} Nummern frei</b> – such dir deine Wunschnummer aus. <a href="{{ $b['edition']['url'] ?? '#' }}" style="color:#2d5f93;">Zur Edition 100</a></p>

            @if (! empty($b['tip']))
                <h2 style="font-size:20px;margin:24px 0 8px;">Tipp der Woche: {{ $b['tip']['title'] }}</h2>
                <p style="margin:0;">{{ $b['tip']['text'] }} <a href="{{ $b['tip']['url'] }}" style="color:#2d5f93;">Weiterlesen</a></p>
            @endif

            <p style="margin:24px 0 0;">Viele Grüße aus der Werkstatt<br>{{ $c['owner'] }}</p>
        @endif
    </td></tr>

    <tr><td style="padding:20px 28px;background:#f7f6f3;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:{{ $muted }};">
        Du bekommst diese E-Mail, weil du dich{{ $since ? ' am '.$since : '' }} für den Newsletter der {{ $c['name'] }} angemeldet und die Anmeldung bestätigt hast.
        <a href="{{ $unsubscribe }}" style="color:{{ $muted }};">Vom Newsletter abmelden</a>.<br><br>
        {{ $c['name'] }} · {{ $c['owner'] }} · {{ $a['street'] }} · {{ $a['postal_code'] }} {{ $a['locality'] }}<br>
        Telefon {{ $c['phone']['display'] }} · {{ $c['email'] }} · <a href="https://messermanufaktur-ulbrich.de/impressum/" style="color:{{ $muted }};">Impressum</a> · <a href="https://messermanufaktur-ulbrich.de/datenschutz/" style="color:{{ $muted }};">Datenschutz</a>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
