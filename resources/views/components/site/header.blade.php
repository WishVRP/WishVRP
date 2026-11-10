@props(['current' => null])
@php
    $nav = [
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
        <a class="wordmark" href="/" @if($current === '/') aria-current="page" @endif>
            <x-ui.wordmark-mark />
            <span class="wordmark__text">
                <span class="wordmark__name">{{ $c['name'] }}</span>
                <span class="wordmark__place">{{ $c['place_label'] }}</span>
            </span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="hauptnavigation" data-nav-toggle>
            <span class="nav-toggle__icon" aria-hidden="true"></span>
            <span data-nav-label>Menü</span>
        </button>

        <nav class="site-nav" id="hauptnavigation" aria-label="Hauptnavigation" data-nav>
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
                <x-ui.button href="/schmiedekurse/buchen/" variant="straw">Termin finden</x-ui.button>
            </div>
            <p class="site-nav__meta">
                {{ $c['owner_role'] }} {{ $c['owner'] }}<br>
                Telefon <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a>
            </p>
        </nav>
    </div>
</header>
