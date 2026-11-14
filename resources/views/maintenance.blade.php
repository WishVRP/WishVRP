{{-- Wartungshinweis (MAINTENANCE=true). Eigenständig, ohne Menü; Kontakt und Pflichtangaben bleiben erreichbar. --}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wir bauen um | {{ $c['name'] }}</title>
    <meta name="description" content="Die Website der {{ $c['name'] }} wird gerade erneuert und ist in Kürze wieder erreichbar. Für Schmiedekurse, Messer und Gutscheine erreichst du uns telefonisch oder per E-Mail.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta name="theme-color" content="#0e0f10">
    @vite(['resources/css/app.css'])
</head>
<body class="maintenance is-dark textured">
    <main class="maintenance__inner">
        <x-ui.wordmark-mark class="maintenance__mark" />
        <p class="maintenance__brand">{{ $c['name'] }} · Schmiedehandwerk seit {{ $c['heritage']['since'] }}</p>
        <h1 class="maintenance__title">Wir bauen gerade um.</h1>
        <p class="maintenance__lead">Unsere neue Website ist fast fertig und in Kürze wieder für dich da. Bis dahin erreichst du uns wie gewohnt – für Schmiedekurse, Messer, Gutscheine und alle Fragen.</p>
        <ul class="maintenance__contact" role="list">
            <li><span>Telefon</span> <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></li>
            <li><span>E-Mail</span> <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></li>
            <li><span>Adresse</span> {{ $c['address']['street'] }}, {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}</li>
        </ul>
        <p class="maintenance__legal"><a href="/impressum/">Impressum</a> · <a href="/datenschutz/">Datenschutz</a></p>
    </main>
</body>
</html>
