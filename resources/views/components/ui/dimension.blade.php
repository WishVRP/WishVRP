@props(['cm', 'max' => 40])
{{-- Bemaßungslinie wie in einer technischen Zeichnung: Klingenlänge maßstäblich zur längsten Kursklinge. --}}
@php
    $w = 200;
    $len = max(24, round($w * $cm / $max));
@endphp
<svg class="dimension" viewBox="0 0 {{ $w + 52 }} 26" width="{{ $w + 52 }}" height="26" role="img" aria-label="Klingenlänge bis {{ $cm }} cm">
    <g stroke="currentColor" stroke-width="1" fill="none">
        <line x1="4" y1="4" x2="4" y2="22" />
        <line x1="{{ 4 + $len }}" y1="4" x2="{{ 4 + $len }}" y2="22" />
        <line x1="4" y1="13" x2="{{ 4 + $len }}" y2="13" />
        <path d="M4 13 l6 -3 v6 z M{{ 4 + $len }} 13 l-6 -3 v6 z" fill="currentColor" stroke="none" />
    </g>
    <text x="{{ 4 + $len + 6 }}" y="17">{{ $cm }} cm</text>
</svg>
