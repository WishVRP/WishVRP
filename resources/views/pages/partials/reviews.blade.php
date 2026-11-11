{{-- Google-Bewertungen (wörtlich, gepflegt in der Verwaltung → Bewertungen). Kein Bewertungs-Schema (siehe App\Support\Reviews). --}}
@php
    $reviews = $reviews ?? \App\Support\Reviews::list();
    $rating = $rating ?? \App\Support\Reviews::rating();
    $limit = $limit ?? null;
@endphp
@if (count($reviews))
    <div class="reviews-block" aria-labelledby="reviews-title">
            <h2 id="reviews-title">Was Kursteilnehmer und Kunden sagen</h2>
            @if ($rating)
                <p class="rating__meta">{{ $rating['value'] }} von 5 Sternen · {{ $rating['count'] }} Bewertungen im Google-Unternehmensprofil (Stand {{ $rating['as_of'] }})
                    @if ($rating['profile_url'])· <a class="link-arrow" href="{{ $rating['profile_url'] }}" rel="noopener" target="_blank">Alle auf Google <span aria-hidden="true">↗</span></a>@endif
                </p>
            @endif
            <div class="quotes quotes--grid">
                @foreach ($limit ? array_slice($reviews, 0, $limit) : $reviews as $review)
                    @include('pages.partials.review-quote', ['review' => $review])
                @endforeach
            </div>
    </div>
@endif
