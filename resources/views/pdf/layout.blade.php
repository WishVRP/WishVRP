<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>{{ $pdfTitle ?? $c['name'] }}</title>
<style>
    @page { margin: 22mm 18mm 24mm 20mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; color: #1d1f23; line-height: 1.45; }
    h1 { font-family: 'DejaVu Serif', serif; font-size: 20pt; font-weight: normal; margin: 0 0 4mm; }
    h2 { font-family: 'DejaVu Serif', serif; font-size: 12.5pt; font-weight: normal; margin: 6mm 0 2mm; }
    .top td { vertical-align: top; }
    .muted { color: #6b7079; }
    .small { font-size: 8pt; }
    .brand { width: 100%; border-bottom: 1.2pt solid #b8892f; padding-bottom: 3mm; margin-bottom: 8mm; }
    .brand td { vertical-align: middle; }
    .brand img { width: 13mm; height: 13mm; }
    .brand .name { font-family: 'DejaVu Serif', serif; font-size: 14pt; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 4mm; }
    table.lines th { text-align: left; font-size: 8pt; color: #6b7079; font-weight: normal; border-bottom: 0.6pt solid #b9bdc4; padding: 2mm 1.5mm; }
    table.lines td { border-bottom: 0.4pt solid #e1e3e7; padding: 2.2mm 1.5mm; vertical-align: top; }
    .r { text-align: right; }
    .sum td { border: 0; padding: 1.2mm 1.5mm; }
    .sum .total td { font-weight: bold; font-size: 11pt; border-top: 0.8pt solid #1d1f23; padding-top: 2mm; }
    .footer { position: fixed; bottom: -16mm; left: 0; right: 0; font-size: 7.2pt; color: #6b7079; border-top: 0.4pt solid #b9bdc4; padding-top: 2mm; }
    .footer td { vertical-align: top; width: 33%; }
</style>
</head>
<body>
@php
    $a = $c['address'];
    $bank = array_filter($c['bank'] ?? []);
@endphp
<table class="brand"><tr>
    <td>@if ($logo)<img src="{{ $logo }}" alt="">@endif</td>
    <td class="name">{{ $c['name'] }}<br><span class="small muted">{{ $c['owner'] }} · {{ $a['street'] }} · {{ $a['postal_code'] }} {{ $a['locality'] }}</span></td>
</tr></table>

<div class="footer"><table width="100%"><tr>
    <td>{{ $c['name'] }}<br>Inhaber {{ $c['owner'] }}<br>{{ $a['street'] }}, {{ $a['postal_code'] }} {{ $a['locality'] }}</td>
    <td>Tel. {{ $c['phone']['display'] }}<br>{{ $c['email'] }}<br>messermanufaktur-ulbrich.de</td>
    <td>@if (! empty($c['vat_id']))USt-IdNr. {{ $c['vat_id'] }}<br>@endif @if ($bank && ! empty($showBank))IBAN {{ $bank['iban'] ?? '' }}@if (! empty($bank['bic'])) · BIC {{ $bank['bic'] }}@endif<br>{{ $bank['name'] ?? '' }}@endif</td>
</tr></table></div>

@yield('content')
</body>
</html>
