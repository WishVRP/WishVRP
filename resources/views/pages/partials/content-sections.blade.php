{{-- Abschnitte aus übernommenem Live-Inhalt ($key = resources/content/{key}.json, $skip = Überschriften auslassen) --}}
@php
    $cs = json_decode(file_get_contents(resource_path('content/'.$key.'.json')), true);
    $skipH = $skip ?? [];
    $secs = collect($cs['sections'])->reject(fn ($s) => $s['h'] === '' || in_array($s['h'], $skipH, true));
@endphp
@if ($secs->isNotEmpty())
    <section class="block" aria-label="{{ $label ?? 'Mehr Informationen' }}">
        <div class="container prose">
            @foreach ($secs as $s)
                <h2>{{ $s['h'] }}</h2>
                @foreach ($s['items'] as $item)
                    @if ($item['t'] === 'h3')<h3>{{ $item['x'] }}</h3>@elseif($item['t'] === 'li')<ul><li>{{ $item['x'] }}</li></ul>@else<p>{{ $item['x'] }}</p>@endif
                @endforeach
            @endforeach
        </div>
    </section>
@endif
