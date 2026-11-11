{{-- Ergänzende Inhalte je Seite: resources/content/extra/{pfad}.json (Abschnitte + FAQ). FAQ fließt im Controller ins FAQPage-Schema. --}}
@php $en = str_starts_with(request()->path(), 'en'); @endphp
<section class="block extra" aria-label="{{ $en ? 'More information' : 'Mehr Informationen' }}">
    <div class="container prose extra__body">
        @foreach ($x['sections'] as $s)
            <h2>{{ $s['h'] }}</h2>
            @php $list = []; @endphp
            @foreach ($s['items'] as $item)
                @if ($item['t'] === 'li')
                    @php $list[] = $item['x']; @endphp
                @else
                    @if ($list)<ul>@foreach ($list as $li)<li>{!! \App\Support\Inline::html($li) !!}</li>@endforeach</ul>@php $list = []; @endphp @endif
                    @if ($item['t'] === 'h3')<h3>{{ $item['x'] }}</h3>@else<p>{!! \App\Support\Inline::html($item['x']) !!}</p>@endif
                @endif
            @endforeach
            @if ($list)<ul>@foreach ($list as $li)<li>{!! \App\Support\Inline::html($li) !!}</li>@endforeach</ul>@endif
        @endforeach
        @if (! empty($x['faq']))
            <h2>{{ $en ? 'More questions' : 'Weitere Fragen' }}</h2>
            @include('pages.partials.faq', ['faqs' => $x['faq']])
        @endif
    </div>
</section>
