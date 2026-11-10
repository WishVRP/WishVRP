@props(['title' => null])
{{-- Sachlicher Hinweis (z. B. „Online-Kauf folgt“) – kein Fehler-Rot --}}
<aside {{ $attributes->class(['note']) }}>
    <x-ui.icon name="online" class="note__icon" />
    <div>
        @if ($title)<p class="note__title">{{ $title }}</p>@endif
        <div class="note__body">{{ $slot }}</div>
    </div>
</aside>
