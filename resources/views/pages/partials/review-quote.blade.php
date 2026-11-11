<figure class="quote">
    <p class="quote__stars" aria-label="{{ $review['rating'] }} von 5 Sternen"><span aria-hidden="true">{{ str_repeat('★', $review['rating']) }}<span class="quote__stars-off">{{ str_repeat('★', 5 - $review['rating']) }}</span></span></p>
    <blockquote><p>{{ $review['text'] }}</p></blockquote>
    <figcaption>{{ $review['author'] }}@if (! empty($review['topic'])) · {{ $review['topic'] }}@endif · {{ \Illuminate\Support\Carbon::parse($review['date'])->locale('de')->isoFormat('MMMM YYYY') }} · {{ $review['source'] }}</figcaption>
</figure>
