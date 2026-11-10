@props(['current' => null])
@php
    $nav = [
        ['label' => 'Schmiedekurse', 'path' => '/schmiedekurse/'],
        ['label' => 'Messer', 'path' => '/shop/'],
        ['label' => 'Anfertigung', 'path' => '/sonderanfertigungen/'],
        ['label' => 'Gutscheine', 'path' => '/geschenke/schmiedekurs-gutschein/'],
        ['label' => 'Manufaktur', 'path' => '/manufaktur/'],
        ['label' => 'Ratgeber', 'path' => '/ratgeber/'],
    ];
    $c = config('ulbrich');
@endphp
<div class="topline">
    <div class="container topline__inner">
        <span>{{ $c['owner_role'] }} {{ $c['owner'] }} · Mitglied der {{ $c['chamber'] }}</span>
        <span>Telefon <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></span>
    </div>
</div>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="wordmark" href="/" @if($current === '/') aria-current="page" @endif>
            {{-- [FEHLT] Logo als SVG – bis dahin Wortmarke in Satzschrift --}}
            <span class="wordmark__name">{{ $c['name'] }}</span>
            <span class="wordmark__place">{{ $c['place_label'] }}</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="hauptnavigation" data-nav-toggle>
            <span class="nav-toggle__icon" aria-hidden="true"></span>
            <span data-nav-label>Menü</span>
        </button>

        <nav class="site-nav" id="hauptnavigation" aria-label="Hauptnavigation" data-nav>
            <ul class="site-nav__list" role="list">
                @foreach ($nav as $item)
                    <li>
                        <a class="site-nav__link" href="{{ $item['path'] }}" @if($current === $item['path']) aria-current="page" @endif>{{ $item['label'] }}</a>
                    </li>
                @endforeach
            </ul>
            <div class="site-nav__cta">
                <x-ui.button href="/schmiedekurse/buchen/">Termin finden</x-ui.button>
            </div>
        </nav>
    </div>
</header>
