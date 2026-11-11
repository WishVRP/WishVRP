<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getSubheading(): ?string
    {
        $w = $this->record->discountWarnings();

        return $w ? '⚠ Weihnachtsrabatt prüfen – ähnliche Einlösung(en): '.implode(', ', $w).'. Gleicher Name, gleiche Straße oder gleiche Telefonnummer – vor der Bestätigung klären.' : null;
    }

    public function getTitle(): string
    {
        return $this->record->number.' · '.$this->record->typeLabel();
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(OrdersTable::actions())->label('Status ändern')->icon(Heroicon::OutlinedArrowPath)->button()->color('primary'),
            DeleteAction::make(),
        ];
    }
}
