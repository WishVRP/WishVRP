{{-- Erwähnungen direkt unter dem Hero: nur Handwerk/Veranstaltungen, Quellen aus config/ulbrich.php --}}
@php $outlets = collect($c['press'])->where('craft', true)->pluck('outlet')->map(fn ($o) => explode(' / ', $o)[0])->unique()->values(); @endphp
@if ($outlets->isNotEmpty())
    <section class="press-strip" aria-label="Presse und Erwähnungen">
        <div class="container press-strip__inner">
            <p class="press-strip__label">Presse &amp; Erwähnungen</p>
            <ul class="press-strip__list" role="list">
                @foreach ($outlets as $o)<li>{{ $o }}</li>@endforeach
            </ul>
            <a class="press-strip__link" href="/presse/">Alle Berichte <span aria-hidden="true">→</span></a>
        </div>
    </section>
@endif
