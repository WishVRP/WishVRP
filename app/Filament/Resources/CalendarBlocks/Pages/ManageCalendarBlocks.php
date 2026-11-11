<?php

namespace App\Filament\Resources\CalendarBlocks\Pages;

use App\Filament\Resources\CalendarBlocks\CalendarBlockResource;
use App\Models\CalendarBlock;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class ManageCalendarBlocks extends ManageRecords
{
    protected static string $resource = CalendarBlockResource::class;

    public function getSubheading(): ?string
    {
        $next = Order::query()->where('type', 'kurs')->whereNotNull('course_start')->where('status', '!=', 'storniert')
            ->whereDate('course_start', '>=', now()->toDateString())->orderBy('course_start')->limit(5)->with('course')->get()
            ->map(fn (Order $o) => $o->course_start->format('d.m.').' '.$o->course?->name.' ('.$o->participants.' P., '.$o->number.')')->implode(' · ');

        return 'Jeder Tag ist buchbar, ein Kurs pro Tag; gebuchte Tage sind automatisch belegt. Nächste Kurstage: '.($next ?: 'noch keine').'.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('zeitraum')->label('Zeitraum sperren')->icon(Heroicon::OutlinedNoSymbol)
                ->schema([
                    DatePicker::make('from')->label('Von')->required()->native(false)->displayFormat('d.m.Y')->firstDayOfWeek(1),
                    DatePicker::make('to')->label('Bis')->required()->native(false)->displayFormat('d.m.Y')->firstDayOfWeek(1)->afterOrEqual('from'),
                    TextInput::make('reason')->label('Grund (intern)')->maxLength(120),
                ])
                ->action(function (array $data) {
                    $n = 0;
                    for ($d = Carbon::parse($data['from']); $d->lte(Carbon::parse($data['to'])); $d->addDay()) {
                        CalendarBlock::query()->firstOrCreate(['date' => $d->toDateString()], ['reason' => $data['reason'] ?? null]);
                        $n++;
                    }
                    Notification::make()->title("{$n} Tage gesperrt")->success()->send();
                }),
            CreateAction::make()->label('Einzelnen Tag sperren'),
        ];
    }
}
