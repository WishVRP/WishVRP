{{-- $items: [[foto, caption], …] --}}
<ul class="photo-grid" role="list">
    @foreach ($items as $i => [$foto, $caption])
        <li class="reveal {{ $i % 3 ? 'reveal-delay-'.($i % 3) : '' }}">
            <x-ui.media :foto="$foto" format="portrait" sizes="(min-width: 64rem) 30vw, (min-width: 40rem) 45vw, 100vw" />
            <p class="photo-grid__caption"><span>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span> {{ $caption }}</p>
        </li>
    @endforeach
</ul>
