@props(['name'])
{{-- Ein Linien-Icon-Satz (1.5 px, 24er Raster) für die ganze Website. --}}
<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.5', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
@switch($name)
    @case('steel')<path d="M3 15h18v4H3zM5 15l2-6h10l2 6M9 9V5h6v4"/>@break
    @case('fire')<path d="M12 21c-4 0-6.5-2.6-6.5-6 0-3.5 3-5.5 3.5-9 2 1.5 3 3.5 3 5 1-1 1.5-2 1.5-3.5 2.5 2 5 4.5 5 7.5 0 3.4-2.5 6-6.5 6z"/><path d="M12 21c-1.7 0-3-1.2-3-3 0-1.6 1.5-2.6 2-4 1.5 1 3.5 2.3 3.5 4 0 1.8-1.1 3-2.5 3z"/>@break
    @case('hammer')<path d="m14 7 3-3 4 4-3 3M14 7l-2 2 3 3 2-2M12 9 3.5 17.5a1.4 1.4 0 0 0 0 2l1 1a1.4 1.4 0 0 0 2 0L15 12"/>@break
    @case('drop')<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/><path d="M9 15a3 3 0 0 0 3 3"/>@break
    @case('temper')<path d="M3 12h18"/><path d="M5 8v8M9 8v8M13 8v8M17 8v8"/><circle cx="12" cy="4" r="1"/>@break
    @case('blade')<path d="M2 15c6-1 11-4 20-11-3 7-8 12-14 13l-1 2H5l1-3z"/><path d="M8 16l1.5 1.5"/>@break
    @case('calendar')<rect x="3" y="5" width="18" height="16" rx="1.5"/><path d="M3 10h18M8 3v4M16 3v4"/>@break
    @case('swap')<path d="M4 8h13l-3-3M20 16H7l3 3"/>@break
    @case('gift')<rect x="3" y="9" width="18" height="12" rx="1"/><path d="M3 13h18M12 9v12M12 9c-2-4-6-4-6-1.5S12 9 12 9zm0 0c2-4 6-4 6-1.5S12 9 12 9z"/>@break
    @case('online')<rect x="3" y="4" width="18" height="13" rx="1.5"/><path d="M8 21h8M12 17v4M8 11l2.5 2.5L16 8"/>@break
    @case('star')<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z" fill="currentColor" stroke="none"/>@break
    @case('download')<path d="M12 3v12M7 10l5 5 5-5M4 19h16"/>@break
    @case('pin')<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>@break
    @case('phone')<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>@break
@endswitch
</svg>
