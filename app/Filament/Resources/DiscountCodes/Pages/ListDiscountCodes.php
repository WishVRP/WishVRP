<?php

namespace App\Filament\Resources\DiscountCodes\Pages;

use App\Filament\Resources\DiscountCodes\DiscountCodeResource;
use App\Models\DiscountCode;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ListDiscountCodes extends ManageRecords
{
    protected static string $resource = DiscountCodeResource::class;

    public function getSubheading(): ?string
    {
        return 'Kunden geben den Code an der Kasse im Feld „Rabattcode“ ein. Prozent-Rabatte gelten für alle passenden Positionen, Euro-Rabatte einmal pro Bestellung (nie mehr als der Warenwert, Versand ist ausgenommen).';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Neuer Rabattcode'),
            Action::make('mehrere')->label('Mehrere Codes erzeugen')->icon(Heroicon::OutlinedSquare2Stack)->color('gray')
                ->modalHeading('Mehrere Einmal-Codes erzeugen (z. B. für ein Gewinnspiel)')
                ->modalDescription('Jeder Code ist genau einmal einlösbar. Danach kannst du sie einzeln per E-Mail verschicken oder kopieren.')
                ->schema([
                    TextInput::make('count')->label('Anzahl Codes')->numeric()->minValue(1)->maxValue(200)->default(10)->required(),
                    TextInput::make('prefix')->label('Anfang des Codes')->maxLength(8)->default('GEWINN')->regex('/^[A-Za-z0-9]+$/')->required()
                        ->helperText('z. B. GEWINN → GEWINN-7KQ2PX'),
                    ...DiscountCodeResource::valueFields(),
                ])
                ->action(function (array $data) {
                    $count = (int) $data['count'];
                    for ($i = 0; $i < $count; $i++) {
                        DiscountCode::create([
                            'code' => DiscountCode::newCode($data['prefix']), 'max_uses' => 1, 'source' => 'verwaltung',
                        ] + collect($data)->only(['label', 'type', 'percent', 'amount', 'scope', 'min_order', 'valid_from', 'valid_until'])->all());
                    }
                    Notification::make()->title($count.' Codes erzeugt')->body('Sie stehen oben in der Liste. Tipp: nach Anlass suchen.')->success()->send();
                }),
        ];
    }
}
