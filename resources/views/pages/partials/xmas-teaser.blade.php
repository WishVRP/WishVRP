{{-- Hinweis auf die Weihnachtsaktion (nur solange aktiv) --}}
@php $x = config('ulbrich.xmas'); @endphp
@if ($x['enabled'] && now()->lte(\Illuminate\Support\Carbon::parse($x['ends_on'])->endOfDay()))
    <aside class="xmas-teaser" aria-label="Weihnachtsaktion">
        <div class="container xmas-teaser__inner">
            <span class="xmas-teaser__icon" aria-hidden="true">❄</span>
            <p><b>Weihnachtsaktion:</b> Triff den Weihnachtsmann mit Schneebällen – am Ende wartet eine Überraschung.</p>
            <a class="btn btn--straw" href="/weihnachtsaktion/">Jetzt spielen</a>
        </div>
    </aside>
@endif
