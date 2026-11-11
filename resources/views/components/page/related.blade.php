{{-- „Passend dazu“: thematische Querverweise aus config/related.php --}}
@php
    $path = '/'.trim(request()->path(), '/').'/';
    $path = $path === '//' ? '/' : $path;
    $pages = config('pages');
    $items = collect(config('related.'.$path, []))->filter(fn ($p) => isset($pages[$p]));
@endphp
@if ($items->isNotEmpty())
    <nav class="related" aria-labelledby="related-h">
        <div class="container">
            <h2 id="related-h" class="related__title">Passend dazu</h2>
            <ul class="related__list" role="list">
                @foreach ($items as $p)
                    <li>
                        <a class="related__link" href="{{ $p }}">
                            <span class="related__name">{{ $pages[$p]['title'] }}</span>
                            <span class="related__text">{{ \Illuminate\Support\Str::limit($pages[$p]['description'], 90) }}</span>
                            <span class="related__arrow" aria-hidden="true">→</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>
@endif
