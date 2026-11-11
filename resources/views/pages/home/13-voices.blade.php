<section class="block block--sunken" aria-labelledby="voices-title">
    <div class="container">
        <div class="voices__grid">
            <div class="rating reveal">
                <x-ui.kicker>Bewertungen</x-ui.kicker>
                <h2 id="voices-title" class="visually-hidden">Bewertungen und Presse</h2>
                @if ($rating)
                    <p class="rating__value" data-count="{{ $rating['value'] }}">{{ $rating['value'] }}</p>
                    <x-ui.stars />
                    <p class="rating__meta">{{ $rating['count'] }} Bewertungen im Google-Unternehmensprofil<br>Stand {{ $rating['as_of'] }}</p>
                    @if ($rating['profile_url'])
                        <p><a class="link-arrow" href="{{ $rating['profile_url'] }}" rel="noopener" target="_blank">Alle Bewertungen auf Google <span aria-hidden="true">↗</span></a></p>
                    @else
                        <x-ui.missing silent>Link zum Google-Profil</x-ui.missing>
                    @endif
                @endif
            </div>
            <div class="reveal reveal-delay-1">
                @if (count($reviews))
                    <div class="quotes">
                        @foreach (array_slice($reviews, 0, 4) as $review)
                            @include('pages.partials.review-quote', ['review' => $review])
                        @endforeach
                    </div>
                    @if (count($reviews) > 4)
                        <p><a class="link-arrow" href="/schmiedekurse/erfahrungen/">Alle {{ count($reviews) }} Bewertungen lesen <span aria-hidden="true">→</span></a></p>
                    @endif
                @elseif ($isPreview)
                    {{-- Nur Vorschau: Platz für wörtliche Google-Bewertungen nach Freigabe --}}
                    <div class="quotes" data-placeholder="Bewertungstexte aus dem Google-Profil">
                        @for ($i = 0; $i < 2; $i++)
                            <figure class="quote" aria-hidden="true">
                                <span class="skeleton-line"></span><span class="skeleton-line"></span><span class="skeleton-line"></span><span class="skeleton-line"></span>
                                <figcaption>Bewertung aus dem Google-Profil · wird nach Freigabe wörtlich übernommen</figcaption>
                            </figure>
                        @endfor
                    </div>
                @endif

                <div class="press">
                    <div class="press__head">
                        <h3>Presse über die Manufaktur</h3>
                        <a class="link-arrow" href="/presse/">Pressespiegel <span aria-hidden="true">→</span></a>
                    </div>
                    @if (count($press))
                        <ul class="press__list" role="list">
                            @foreach (collect($press)->where('craft', true)->sortByDesc('date')->take(4) as $item)
                                <li>
                                    <span class="tech">{{ $item['date_label'] ?? \Illuminate\Support\Carbon::parse($item['date'])->format('d.m.Y') }}</span>
                                    <span><b>{{ $item['outlet'] }}</b> – {{ $item['title'] }}</span>
                                    <a class="link-arrow" href="{{ $item['url'] }}" rel="noopener" target="_blank">Artikel <span aria-hidden="true">↗</span></a>
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($isPreview)
                        <ul class="press__list" role="list" data-placeholder="Presseberichte von /presse/">
                            @for ($i = 0; $i < 3; $i++)
                                <li aria-hidden="true">
                                    <span class="tech">TT.MM.JJJJ</span>
                                    <span><span class="skeleton-line"></span></span>
                                    <span class="tech">Quelle</span>
                                </li>
                            @endfor
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
