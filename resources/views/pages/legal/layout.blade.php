{{-- Rechtsseiten: schlichtes Layout, noindex. Inhalte vor Go-live anwaltlich prüfen. --}}
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <section class="legal-head">
        <div class="container measure">
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <ol role="list">
                    @foreach ($meta->breadcrumb as $crumb)
                        <li>@if(! $loop->last)<a href="{{ $crumb['path'] }}">{{ $crumb['name'] }}</a>@else<span aria-current="page">{{ $crumb['name'] }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            <h1>{{ $page['title'] }}</h1>
        </div>
    </section>
    <section class="legal">
        <div class="container measure prose">
            @yield('legal')
        </div>
    </section>
</x-layouts.site>
