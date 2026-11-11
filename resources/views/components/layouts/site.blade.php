@props(['meta', 'schema' => null, 'current' => null])
<!DOCTYPE html>
<html lang="{{ $meta->locale() }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script nonce="{{ Vite::cspNonce() }}">document.documentElement.classList.replace('no-js','js')</script>

    <x-seo.meta :meta="$meta" />

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
    <link rel="icon" href="/favicon-16.png" type="image/png" sizes="16x16">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#0e0f10">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if ($schema)
        <script type="application/ld+json" nonce="{{ Vite::cspNonce() }}">{!! $schema !!}</script>
    @endif
</head>
<body>
    <a class="skip-link" href="#inhalt">{{ $meta->locale() === 'en' ? 'Skip to content' : 'Zum Inhalt springen' }}</a>

    <x-site.header :current="$current" :meta="$meta" />

    <main id="inhalt" tabindex="-1">
        {{ $slot }}
    </main>

    @if ($meta->locale() === 'en')
        <x-site.footer-en />
        <x-site.mobile-bar-en />
    @else
        <x-site.footer />
        <x-site.mobile-bar />
    @endif
</body>
</html>
