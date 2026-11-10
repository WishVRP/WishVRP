@props(['prefix' => null])
{{-- Ein Steuerhinweis für alle Preise – gesteuert über config('ulbrich.tax_mode'). --}}
@php $mode = config('ulbrich.tax_mode'); @endphp
<span {{ $attributes }}>
    @if ($prefix){{ $prefix }} @endif
    @if ($mode === 'kleinunternehmer')
        Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.
    @elseif ($mode === 'regular')
        Alle Preise inkl. 19 % MwSt.
    @else
        <x-ui.missing ask="mit Steuerberater klären" label="Steuerhinweis folgt">Steuerhinweis – § 19 UStG oder „inkl. MwSt.“</x-ui.missing>
    @endif
</span>
