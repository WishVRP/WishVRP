<x-layouts.site :meta="$meta" :current="$path">
    <section class="pending-page">
        <div class="container measure">
            <nav class="breadcrumb" aria-label="Brotkrumen">
                <ol role="list">
                    @foreach ($meta->breadcrumb as $crumb)
                        <li>@if(! $loop->last)<a href="{{ $crumb['path'] }}">{{ $crumb['name'] }}</a>@else<span aria-current="page">{{ $crumb['name'] }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            <h1>{{ $page['title'] }}</h1>
            <p class="lead">
                Diese Seite ist Teil des Relaunchs und wird in einem der nächsten Schritte umgesetzt.
                Die Adresse <code class="tech">{{ $path }}</code> bleibt dabei unverändert.
            </p>
            <p class="tech muted mt-4">Nur in der Vorschau sichtbar · noindex · in Produktion nicht vorhanden</p>
            <div class="btn-row mt-5">
                <x-ui.button href="/">Zur Startseite</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.site>
