<?php

namespace App\Filament\Resources\ExportRules\Pages;

use App\Filament\Resources\ExportRules\ExportRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageExportRules extends ManageRecords
{
    protected static string $resource = ExportRuleResource::class;

    public function getSubheading(): ?string
    {
        return 'Reihenfolge: Land + Messerart vor Land + alle vor alle + Messerart. Ohne passende Regel gilt „Prüfen“ (Anfrage ohne Zahlung). '
            .'Nur geprüfte Angaben eintragen – Einfuhrrecht ändert sich; Quelle und Prüfdatum festhalten.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Regel anlegen')];
    }
}
