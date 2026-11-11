@php
    $boxes = [
        ['Beliebteste Seiten (30 Tage)', $pages],
        ['Woher die Besucher kommen', $sources],
        ['Klicks & Anfragen (30 Tage)', $clicks],
        ['Geräte', $devices],
        ['Verweisende Websites', array_filter($referrers, fn ($n, $k) => filled($k), ARRAY_FILTER_USE_BOTH)],
    ];
@endphp
<x-filament-widgets::widget>
    <div class="uw-grid">
        @foreach ($boxes as [$title, $rows])
            <x-filament::section :heading="$title">
                @if (empty($rows))
                    <p class="uw-empty">Noch keine Daten</p>
                @else
                    @php $max = max($rows) ?: 1; @endphp
                    <ul class="uw-list">
                        @foreach ($rows as $k => $n)
                            <li>
                                <div class="uw-row"><span class="uw-row__label" title="{{ $k }}">{{ $k }}</span><span class="uw-row__num">{{ number_format($n, 0, ',', '.') }}</span></div>
                                <progress class="uw-bar" max="{{ $max }}" value="{{ $n }}">{{ $n }}</progress>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>
        @endforeach
    </div>
</x-filament-widgets::widget>
