@php
    use App\Filament\Resources\Notes\NoteResource;
    use App\Filament\Resources\Orders\OrderResource;
    use App\Models\Order;
@endphp
<x-filament-widgets::widget>
    <div class="uw-grid">
        <x-filament::section heading="Zu erledigen: Vorgänge">
            <x-slot name="afterHeader"><x-filament::link :href="OrderResource::getUrl('index')" size="sm">Alle</x-filament::link></x-slot>
            @forelse ($orders as $o)
                <a class="uw-item" href="{{ OrderResource::getUrl('edit', ['record' => $o]) }}">
                    <div class="uw-item__head"><span>{{ $o->number }} · {{ $o->name }}</span>
                        <x-filament::badge :color="Order::STATUS_COLORS[$o->status] ?? 'gray'" size="sm">{{ $o->statusLabel() }}</x-filament::badge></div>
                    <div class="uw-muted">{{ $o->typeLabel() }} – {{ $o->summary() }} · {{ $o->created_at->diffForHumans() }}</div>
                </a>
            @empty
                <p class="uw-empty">Nichts offen.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Notizen fürs Team">
            <x-slot name="afterHeader"><x-filament::link :href="NoteResource::getUrl('index')" size="sm">Alle / neue Notiz</x-filament::link></x-slot>
            @forelse ($notes as $n)
                <div class="uw-item">
                    <div>@if ($n->is_pinned)<span title="angeheftet">★</span> @endif{{ $n->body }}</div>
                    <div class="uw-muted">{{ $n->user?->name }} · {{ $n->created_at->diffForHumans() }}@if ($n->order) · {{ $n->order->number }}@endif</div>
                </div>
            @empty
                <p class="uw-empty">Keine offenen Notizen.</p>
            @endforelse
        </x-filament::section>

        <x-filament::section heading="Nächste Kurstage">
            <x-slot name="afterHeader"><x-filament::link :href="\App\Filament\Resources\CalendarBlocks\CalendarBlockResource::getUrl('index')" size="sm">Kalender</x-filament::link></x-slot>
            @forelse ($dates as $o)
                <a class="uw-item" href="{{ OrderResource::getUrl('edit', ['record' => $o]) }}">
                    <div class="uw-item__head"><span>{{ $o->course_start->locale('de')->isoFormat('dd, D. MMM YYYY') }}</span>
                        <x-filament::badge :color="Order::STATUS_COLORS[$o->status] ?? 'gray'" size="sm">{{ $o->statusLabel() }}</x-filament::badge></div>
                    <div class="uw-muted">{{ $o->course?->name }} · {{ $o->participants }} P. · {{ $o->name }}</div>
                </a>
            @empty
                <p class="uw-empty">Noch keine Kurse gebucht.</p>
            @endforelse
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
