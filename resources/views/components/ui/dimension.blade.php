@props(['cm', 'max' => 40])
{{-- Bemaßungslinie wie in einer technischen Zeichnung: Länge maßstäblich zur längsten Kursklinge, Wert als Text daneben. --}}
@php $len = max(20, round(120 * $cm / $max)); @endphp
<svg class="dimension" viewBox="0 0 128 16" width="{{ round(128 * 0.875) }}" height="14" aria-hidden="true">
    <g stroke="currentColor" stroke-width="1" fill="none">
        <line x1="2" y1="2" x2="2" y2="14" />
        <line x1="{{ 2 + $len }}" y1="2" x2="{{ 2 + $len }}" y2="14" />
        <line x1="2" y1="8" x2="{{ 2 + $len }}" y2="8" />
        <path d="M2 8 l5 -2.5 v5 z M{{ 2 + $len }} 8 l-5 -2.5 v5 z" fill="currentColor" stroke="none" />
    </g>
</svg>
<span class="dimension-label">bis {{ $cm }} cm</span>
