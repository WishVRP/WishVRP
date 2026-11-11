@props(['meta'])
<title>{{ $meta->fullTitle() }}</title>
<meta name="description" content="{{ $meta->description }}">
<meta name="robots" content="{{ $meta->robots() }}">
<link rel="canonical" href="{{ $meta->canonical() }}">
@foreach ($meta->alternates() as $lang => $altPath)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ \App\Support\PageMeta::absolute($altPath) }}">
@endforeach
@if ($alts = $meta->alternates())
<link rel="alternate" hreflang="x-default" href="{{ \App\Support\PageMeta::absolute($alts['de']) }}">
@endif

<meta property="og:type" content="{{ $meta->ogType }}">
<meta property="og:site_name" content="{{ config('ulbrich.name') }}">
<meta property="og:locale" content="{{ $meta->locale() === 'en' ? 'en_GB' : 'de_DE' }}">
<meta property="og:title" content="{{ $meta->fullTitle() }}">
<meta property="og:description" content="{{ $meta->description }}">
<meta property="og:url" content="{{ $meta->canonical() }}">
<meta property="og:image" content="{{ $meta->ogImage() }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta->fullTitle() }}">
<meta name="twitter:description" content="{{ $meta->description }}">
<meta name="twitter:image" content="{{ $meta->ogImage() }}">
