{{-- Galerie „Bei uns geschmiedet“ – Fotos aus der Verwaltung (nur mit Einwilligung) --}}
@php $items = \App\Models\GalleryItem::query()->visible()->with('course')->get(); @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Bei uns geschmiedet" kicker="Galerie" tone="brown"
        lead="Messer, die Teilnehmerinnen und Teilnehmer in unseren Schmiedekursen selbst geschmiedet haben – vom ersten Grundlagenmesser bis zum Damastmesser. Jedes Stück ein Unikat." />

    <section class="block">
        <div class="container">
            @if ($items->isEmpty())
                <div class="prose">
                    <p>Hier zeigen wir bald Messer aus unseren Kursen. Bis dahin: Alle Kurse im Überblick findest du unter <a href="/schmiedekurse/">Schmiedekurse</a>.</p>
                </div>
            @else
                <ul class="gallery" role="list">
                    @foreach ($items as $i => $g)
                        @php [$w, $h] = $g->size(); @endphp
                        <li class="gallery__item">
                            <figure>
                                <img src="{{ $g->url() }}" width="{{ $w }}" height="{{ $h }}" alt="{{ $g->alt }}" loading="{{ $i < 3 ? 'eager' : 'lazy' }}" decoding="async">
                                @if ($g->caption || $g->course)
                                    <figcaption>
                                        {{ $g->caption }}
                                        @if ($g->course)<span class="muted">{{ $g->caption ? ' · ' : '' }}<a href="{{ $g->course->url() }}">{{ $g->course->name }}</a></span>@endif
                                    </figcaption>
                                @endif
                            </figure>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <x-page.cta title="Selbst ein Messer schmieden?" text="Such dir einen Kurs und einen freien Tag aus – wir zeigen dir jeden Schritt an der Esse." />
</x-layouts.site>
