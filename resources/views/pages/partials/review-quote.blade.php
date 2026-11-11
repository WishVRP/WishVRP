<figure class="quote">
    <p class="quote__stars" aria-label="{{ $review['rating'] }} von 5 Sternen"><span aria-hidden="true">{{ str_repeat('★', $review['rating']) }}<span class="quote__stars-off">{{ str_repeat('★', 5 - $review['rating']) }}</span></span></p>
    <blockquote><p>{{ $review['text'] }}</p></blockquote>
    <figcaption>
        @if (! empty($review['author_url']))<a href="{{ $review['author_url'] }}" rel="nofollow noopener" target="_blank">{{ $review['author'] }}</a>@else{{ $review['author'] }}@endif
        @if (! empty($review['topic'])) · {{ $review['topic'] }}@endif
        · {{ \Illuminate\Support\Carbon::parse($review['date'])->locale('de')->isoFormat('MMMM YYYY') }}
        · @if (! empty($review['review_url']))<a href="{{ $review['review_url'] }}" rel="nofollow noopener" target="_blank">{{ $review['source'] }}</a>@else{{ $review['source'] }}@endif
    </figcaption>
</figure>
