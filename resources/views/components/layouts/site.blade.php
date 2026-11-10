@props(['meta', 'schema' => null, 'current' => null])
<!DOCTYPE html>
<html lang="de" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script nonce="{{ Vite::cspNonce() }}">document.documentElement.classList.replace('no-js','js')</script>

    <x-seo.meta :meta="$meta" />

    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#1c1e20">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($schema)
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">{!! $schema !!}</script>
    @endif
</head>
<body>
    <a class="skip-link" href="#inhalt">Zum Inhalt springen</a>

    <x-site.header :current="$current" />

    <main id="inhalt" tabindex="-1">
        {{ $slot }}
    </main>

    <x-site.footer />
    <x-site.mobile-bar />
</body>
</html>
