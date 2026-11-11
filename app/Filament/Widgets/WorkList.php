<?php

namespace App\Filament\Widgets;

use App\Models\CourseDate;
use App\Models\Note;
use App\Models\Order;
use Filament\Widgets\Widget;

/** Arbeitsliste: neue Vorgänge, angeheftete/offene Notizen, nächste Kurstermine. */
class WorkList extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.work-list';

    protected function getViewData(): array
    {
        return [
            'orders' => Order::query()->open()->with(['course', 'courseDate'])->latest()->limit(6)->get(),
            'notes' => Note::query()->open()->with(['user', 'order'])->orderByDesc('is_pinned')->latest()->limit(6)->get(),
            'dates' => CourseDate::query()->with('course')->whereDate('starts_on', '>=', now()->toDateString())->orderBy('starts_on')->limit(6)->get(),
        ];
    }
}
