<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Gutschein {{ $v->code }}</title>
<style>
    @page { margin: 12mm; }
    body { font-family: 'DejaVu Sans', sans-serif; color: #1d1f23; margin: 0; }
    .card { border: 2.4pt solid #b8892f; padding: 4mm; height: 168mm; }
    .inner { border: 0.6pt solid #b8892f; height: 146mm; padding: 9mm 16mm; text-align: center; }
    .logo { width: 20mm; height: 20mm; }
    .brand { font-family: 'DejaVu Serif', serif; font-size: 12pt; letter-spacing: 1.5pt; margin-top: 2mm; }
    .kicker { font-size: 8.5pt; letter-spacing: 3pt; text-transform: uppercase; color: #8a6420; margin-top: 7mm; }
    h1 { font-family: 'DejaVu Serif', serif; font-size: 40pt; font-weight: normal; margin: 1mm 0 2mm; }
    .for { font-family: 'DejaVu Serif', serif; font-size: 16pt; margin: 2mm 0 4mm; }
    .what { font-size: 13pt; margin: 0 0 1mm; }
    .msg { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 11.5pt; color: #3a3d43; margin: 5mm auto 0; width: 70%; }
    .code { display: inline-block; margin-top: 7mm; border: 1pt dashed #1d1f23; padding: 2.5mm 7mm; font-size: 15pt; letter-spacing: 2pt; font-weight: bold; }
    .small { font-size: 8.5pt; color: #5b6069; margin-top: 4mm; line-height: 1.5; }
</style>
</head>
<body>
<div class="card"><div class="inner">
    @if ($logo)<img class="logo" src="{{ $logo }}" alt="">@endif
    <div class="brand">{{ strtoupper($c['name']) }}</div>
    <div class="kicker">Schmiedehandwerk seit 1934</div>
    <h1>Gutschein</h1>
    @if ($v->recipient)<p class="for">für {{ $v->recipient }}</p>@endif
    <p class="what">{{ $v->course?->name ?? 'Schmiedekurs' }}</p>
    <p class="small">für {{ $v->persons }} {{ $v->persons === 1 ? 'Person' : 'Personen' }}@if ($v->course?->duration_label) · {{ $v->course->duration_label }}@endif · in der Werkstatt in {{ $c['address']['district'] }}</p>
    @if ($v->gift_message)<p class="msg">„{{ $v->gift_message }}“</p>@endif
    <div class="code">{{ $v->code }}</div>
    <p class="small">
        Gültig bis {{ $v->valid_until->format('d.m.Y') }} · Einlösen: auf messermanufaktur-ulbrich.de/schmiedekurse/buchen/ Kurs und freien Tag wählen und den Code eintragen.<br>
        {{ $c['name'] }} · {{ $c['address']['street'] }} · {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }} · Tel. {{ $c['phone']['display'] }} · {{ $c['email'] }}
    </p>
</div></div>
</body>
</html>
