@props(['current' => null, 'meta' => null])
@php
    $en = $meta?->locale() === 'en';
    $alt = $meta?->alternates() ?? [];
    $nav = $en ? [
        ['label' => 'Knives', 'path' => '/en/knives/', 'hint' => 'handmade'],
        ['label' => 'Edition 100', 'path' => '/en/folding-knife/', 'hint' => 'folding knife'],
        ['label' => 'Custom knives', 'path' => '/en/custom-knives/', 'hint' => 'made to order'],
        ['label' => 'About', 'path' => '/en/about/', 'hint' => 'since 1934'],
        ['label' => 'Shipping', 'path' => '/en/shipping/', 'hint' => 'worldwide'],
    ] : [
        ['label' => 'Schmiedekurse', 'path' => '/schmiedekurse/', 'hint' => '6 Kursformate'],
        ['label' => 'Messer', 'path' => '/shop/', 'hint' => 'Edition 100 · Unikate'],
        ['label' => 'Anfertigung', 'path' => '/sonderanfertigungen/', 'hint' => 'nach Wunsch'],
        ['label' => 'Gutscheine', 'path' => '/geschenke/schmiedekurs-gutschein/', 'hint' => 'zum Verschenken'],
        ['label' => 'Manufaktur', 'path' => '/manufaktur/', 'hint' => 'Andreas Ulbrich'],
        ['label' => 'Ratgeber', 'path' => '/ratgeber/', 'hint' => 'Wissen'],
    ];
    $c = config('ulbrich');
@endphp
<header class="site-header" data-header>
    <div class="container site-header__inner">
        <a class="wordmark" href="{{ $en ? '/en/' : '/' }}" @if($current === ($en ? '/en/' : '/')) aria-current="page" @endif>
            <x-ui.wordmark-mark />
            <span class="wordmark__text">
                <span class="wordmark__name">{{ $c['name'] }}</span>
                <span class="wordmark__place">{{ $en ? 'Bladesmiths since' : 'Schmiedehandwerk seit' }} {{ $c['heritage']['since'] }}</span>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="hauptnavigation" data-nav-toggle>
            <span class="nav-toggle__icon" aria-hidden="true"></span>
            <span data-nav-label>{{ $en ? 'Menu' : 'Menü' }}</span>
        </button>

        <nav class="site-nav" id="hauptnavigation" aria-label="{{ $en ? 'Main navigation' : 'Hauptnavigation' }}" data-nav>
            <ul class="site-nav__list" role="list">
                @foreach ($nav as $item)
                    <li>
                        <a class="site-nav__link" href="{{ $item['path'] }}" @if($current === $item['path']) aria-current="page" @endif>
                            {{ $item['label'] }} <small>{{ $item['hint'] }}</small>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="site-nav__cta">
                @if ($en)
                    <x-ui.button href="mailto:{{ $c['email'] }}" variant="straw">Send an enquiry</x-ui.button>
                @else
                    <x-ui.button href="/schmiedekurse/buchen/" variant="straw">Kurs buchen</x-ui.button>
                @endif
            </div>
            <p class="site-nav__meta">
                {{ $c['owner_role'] }} {{ $c['owner'] }}<br>
                {{ $en ? 'Phone' : 'Telefon' }} <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone'][$en ? 'intl' : 'display'] }}</a>
                @if ($en)
                    <br><a href="{{ $alt['de'] ?? '/' }}" hreflang="de" lang="de">Deutsche Website</a>
                @elseif (isset($alt['en']))
                    <br><a href="{{ $alt['en'] }}" hreflang="en" lang="en">English</a>
                @endif
            </p>
        </nav>
    </div>
    <span class="site-header__progress" aria-hidden="true"></span>
</header>
