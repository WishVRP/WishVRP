@php $press = $c['press']; @endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" title="Presse" kicker="Pressespiegel" tile="Presse" tileLabel="Berichte über die Manufaktur"
        lead="Berichte über {{ $c['owner'] }}, seine Schmiedekurse und die Werkstatt in {{ $c['address']['district'] }}." />

    <x-page.section title="Berichte" kicker="Pressespiegel">
        @if (count($press))
            <ul class="press-list" role="list">
                @foreach ($press as $item)
                    <li class="reveal"><span class="tech muted">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d.m.Y') }} · {{ $item['outlet'] }}</span><a href="{{ $item['url'] }}" rel="noopener" target="_blank">{{ $item['title'] }} <span aria-hidden="true">↗</span></a></li>
                @endforeach
            </ul>
        @else
            <p class="lead"><x-ui.missing label="Pressespiegel folgt">11 Presseberichte mit Datum und Link von der Live-Seite übernehmen</x-ui.missing></p>
        @endif
        <p class="mt-6">Presseanfragen: <a href="tel:{{ $c['phone']['e164'] }}">{{ $c['phone']['display'] }}</a></p>
    </x-page.section>

    <x-page.cta />
</x-layouts.site>
