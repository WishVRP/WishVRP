@props(['meta'])
<title>{{ $meta->fullTitle() }}</title>
<meta name="description" content="{{ $meta->description }}">
<meta name="robots" content="{{ $meta->robots() }}">
<link rel="canonical" href="{{ $meta->canonical() }}">

<meta property="og:type" content="{{ $meta->ogType }}">
<meta property="og:site_name" content="{{ config('ulbrich.name') }}">
<meta property="og:locale" content="de_DE">
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
