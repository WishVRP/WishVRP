@props(['meta', 'title', 'lead' => null, 'kicker' => null, 'foto' => null, 'pos' => null, 'tile' => null, 'tileLabel' => null, 'tone' => 'straw'])
{{-- Kopfbereich jeder Unterseite: Brotkrumen, H1, Einleitung, Aktionen; rechts Foto oder Typo-Kachel --}}
<section class="page-hero is-dark textured page-hero--{{ $tone }}" aria-labelledby="page-title">
    <div class="container page-hero__grid">
        <div class="page-hero__text">
            <nav class="breadcrumb breadcrumb--dark" aria-label="Brotkrumen">
                <ol role="list">
                    @foreach ($meta->breadcrumb as $crumb)
                        <li>@if(! $loop->last)<a href="{{ $crumb['path'] }}">{{ $crumb['name'] }}</a>@else<span aria-current="page">{{ $crumb['name'] }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            @if ($kicker)<x-ui.kicker :color="$tone">{{ $kicker }}</x-ui.kicker>@endif
            <h1 id="page-title" class="page-hero__title">{{ $title }}</h1>
            @if ($lead)<p class="page-hero__lead">{{ $lead }}</p>@endif
            @isset($actions)<div class="btn-row">{{ $actions }}</div>@endisset
            {{ $slot }}
        </div>
        @if ($foto)
            <x-ui.media class="page-hero__media" :foto="$foto" :pos="$pos" format="portrait" eager sizes="(min-width: 60rem) 40vw, 100vw" />
        @elseif ($tile)
            <x-ui.type-tile class="page-hero__tile" :title="$tile" :label="$tileLabel" />
        @endif
    </div>
</section>
