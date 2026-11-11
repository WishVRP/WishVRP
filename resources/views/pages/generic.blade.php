{{--
    Übernommene Inhaltsseite (Quelle: resources/content/{key}.json, aus der Live-Seite übernommen und bereinigt).
    Kopf, Tonalität und Bild kommen aus config/pages.php: kicker, tone, foto | tile, cta.
--}}
@php
    $content = json_decode(file_get_contents(resource_path('content/'.$page['content'].'.json')), true);
    $tone = $page['tone'] ?? 'straw';
    $lead = $page['lead'] ?? implode(' ', array_slice($content['lead'], -1));
    $en = $meta->locale() === 'en';
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$page['h1'] ?? $content['h1']" :kicker="$page['kicker'] ?? null" :tone="$tone"
        :foto="$page['foto'] ?? null" :pos="$page['pos'] ?? null" :tile="$page['tile'] ?? null" :tileLabel="$page['tile_label'] ?? null" :lead="$lead">
        @if (! empty($page['actions']))
            <x-slot:actions>
                @foreach ($page['actions'] as $i => [$label, $href])
                    @php $href = match ($href) { '@email' => 'mailto:'.$c['email'], '@phone' => 'tel:'.$c['phone']['e164'], default => $href }; @endphp
                    <x-ui.button :href="$href" :variant="$i ? 'secondary' : 'straw'" size="lg">{{ $label }}</x-ui.button>
                @endforeach
            </x-slot:actions>
        @endif
    </x-page.hero>

    @if (! empty($page['intro_view']))
        @include($page['intro_view'])
    @endif

    <article class="block article" aria-labelledby="page-title">
        <div class="container article__grid">
            @if (count($content['sections']) >= 3)
                <nav class="article__toc" aria-label="{{ $en ? 'Contents' : 'Inhalt' }}">
                    <p class="article__toc-title">{{ $en ? 'Contents' : 'Inhalt' }}</p>
                    <ol role="list">
                        @foreach ($content['sections'] as $i => $s)
                            @if ($s['h'])<li><a href="#abschnitt-{{ $i + 1 }}">{{ $s['h'] }}</a></li>@endif
                        @endforeach
                        @if (count($content['faq']))<li><a href="#faq">{{ $en ? 'FAQ' : 'Häufige Fragen' }}</a></li>@endif
                    </ol>
                </nav>
            @else
                <div></div>
            @endif
            <div class="prose article__body">
                @foreach ($content['sections'] as $i => $s)
                    @if ($s['h'])<h2 id="abschnitt-{{ $i + 1 }}">{{ $s['h'] }}</h2>@endif
                    @php $list = []; @endphp
                    @foreach ($s['items'] as $item)
                        @if ($item['t'] === 'li')
                            @php $list[] = $item['x']; @endphp
                        @else
                            @if ($list)<ul>@foreach ($list as $li)<li>{{ $li }}</li>@endforeach</ul>@php $list = []; @endphp @endif
                            @if ($item['t'] === 'h3')<h3>{{ $item['x'] }}</h3>@else<p>{{ $item['x'] }}</p>@endif
                        @endif
                    @endforeach
                    @if ($list)<ul>@foreach ($list as $li)<li>{{ $li }}</li>@endforeach</ul>@endif
                @endforeach

                @if (! empty($page['outro_view']))
                    @include($page['outro_view'])
                @endif

                @if (count($content['faq']))
                    <h2 id="faq">{{ $en ? 'Frequently asked questions' : 'Häufige Fragen' }}</h2>
                    @include('pages.partials.faq', ['faqs' => $content['faq']])
                @endif
            </div>
        </div>
    </article>

    <x-page.cta :title="$page['cta_title'] ?? ($en ? 'Questions? Just get in touch.' : 'Fragen? Ruf einfach an.')" />
</x-layouts.site>
