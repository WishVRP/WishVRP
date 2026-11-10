@php $h = $c['heritage']; $years = now()->year - $h['since']; @endphp
<section class="block heritage is-dark textured" id="tradition" aria-labelledby="heritage-title">
    <x-ui.wordmark-mark class="heritage__mark" />
    <div class="container">
        <div class="heritage__head reveal">
            <p class="heritage__count" aria-hidden="true"><span data-count="{{ $years }}">{{ $years }}</span><small>Jahre</small></p>
            <div>
                <x-ui.kicker color="straw">Familientradition</x-ui.kicker>
                <h2 id="heritage-title" class="h-section">Seit {{ $h['since'] }} in der Familie. Heute in {{ $h['generation'] }}. Generation.</h2>
                <p class="heritage__lead">Was du in der Werkstatt lernst, wurde nicht aus einem Buch übernommen. Es ist Handwerk, das in der Familie Ulbrich seit {{ $years }} Jahren weitergegeben wird – und das {{ $c['owner'] }} heute an dich weitergibt.</p>
            </div>
        </div>
        <ol class="timeline" role="list" data-timeline>
            @foreach ($h['generations'] as $g)
                <li class="timeline__item reveal reveal-delay-{{ min($loop->index + 1, 3) }} @if($loop->last) is-now @endif">
                    <span class="timeline__year">{{ $g['year'] ?? ($loop->last ? 'heute' : '') }}</span>
                    <span class="timeline__dot" aria-hidden="true"></span>
                    <p class="timeline__gen">{{ $g['no'] }}. Generation</p>
                    @if ($g['name'])<p class="timeline__name">{{ $g['name'] }}</p>@else<x-ui.missing silent>Name/Stationen {{ $g['no'] }}. Generation</x-ui.missing>@endif
                    <p class="timeline__text">{{ $g['text'] }}</p>
                </li>
            @endforeach
            <li class="timeline__item timeline__item--you reveal reveal-delay-3">
                <span class="timeline__year">dein Termin</span>
                <span class="timeline__dot" aria-hidden="true"></span>
                <p class="timeline__gen">Und dann: du.</p>
                <p class="timeline__text">Dein erstes eigenes Messer – geschmiedet in derselben Tradition.</p>
                <a class="link-arrow" href="/schmiedekurse/buchen/">Termin wählen <span aria-hidden="true">→</span></a>
            </li>
        </ol>
    </div>
</section>
