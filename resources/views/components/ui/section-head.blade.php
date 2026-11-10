@props(['title', 'id' => null, 'spec' => null, 'level' => 2])
<div {{ $attributes->class(['section-head']) }}>
    <div>
        <h{{ $level }} @if($id) id="{{ $id }}" @endif>{{ $title }}</h{{ $level }}>
        @if ($slot->isNotEmpty())
            <p class="section-head__intro">{{ $slot }}</p>
        @endif
    </div>
    @if ($spec)
        <p class="section-head__spec">{{ $spec }}</p>
    @endif
</div>
