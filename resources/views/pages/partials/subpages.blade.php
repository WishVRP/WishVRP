{{-- Unterseiten eines Bereichs automatisch aus config/pages.php ($prefix = Elternpfad) --}}
@php
    $pages = collect(config('pages'))
        ->filter(fn ($p, $k) => str_starts_with($k, $prefix) && $k !== $prefix && substr_count(trim(substr($k, strlen($prefix)), '/'), '/') === 0 && ($p['view'] ?? '') !== 'courses.show' && ($p['index'] ?? true))
        ->sortKeys();
@endphp
@if ($pages->isNotEmpty())
    <x-page.section :title="$title ?? 'Mehr zum Thema'" :kicker="$kicker ?? 'Weiterlesen'" :sunken="$sunken ?? false">
        <div class="path-cards path-cards--three">
            @foreach ($pages as $k => $p)
                <a class="path-card reveal" href="{{ $k }}">
                    <p class="path-card__label">{{ $p['kicker'] ?? ($kicker ?? 'Mehr') }}</p>
                    <h3>{{ $p['title'] }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($p['description'], 110) }}</p>
                    <span class="link-arrow">Ansehen <span aria-hidden="true">→</span></span>
                </a>
            @endforeach
        </div>
    </x-page.section>
@endif
