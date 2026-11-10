@props(['title' => null, 'kicker' => null, 'intro' => null, 'id' => null, 'dark' => false, 'sunken' => false, 'kickerColor' => null])
<section {{ $attributes->class(['block', 'is-dark textured' => $dark, 'block--sunken' => $sunken]) }} @if($id) id="{{ $id }}" @endif @if($title) aria-labelledby="{{ $id ?? \Illuminate\Support\Str::slug($title) }}-h" @endif>
    <div class="container">
        @if ($title)
            <div class="block-head">
                <div>
                    @if ($kicker)<x-ui.kicker :color="$kickerColor">{{ $kicker }}</x-ui.kicker>@endif
                    <h2 id="{{ $id ?? \Illuminate\Support\Str::slug($title) }}-h" class="h-section">{{ $title }}</h2>
                </div>
                @if ($intro)<p class="block-head__intro">{{ $intro }}</p>@endif
            </div>
        @endif
        {{ $slot }}
    </div>
</section>
