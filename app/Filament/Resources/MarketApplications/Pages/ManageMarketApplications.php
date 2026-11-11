<?php

namespace App\Filament\Resources\MarketApplications\Pages;

use App\Filament\Resources\MarketApplications\MarketApplicationResource;
use App\Models\Setting;
use App\Support\Market;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageMarketApplications extends ManageRecords
{
    protected static string $resource = MarketApplicationResource::class;

    public function getSubheading(): ?string
    {
        $s = Market::settings();
        $map = array_count_values(Market::statusMap());

        return 'Markt '.$s['season'].' · Termin: '.($s['date'] ?: 'noch nicht eingetragen').' · Bewerbung '.($s['open'] ? 'offen' : 'geschlossen')
            .' · Stände: '.($map['frei'] ?? 0).' frei, '.($map['reserviert'] ?? 0).' in Prüfung/reserviert, '.($map['belegt'] ?? 0).' vergeben (von 18).';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('einstellungen')->label('Markt-Einstellungen')->icon(Heroicon::OutlinedCog6Tooth)
                ->fillForm(fn () => Market::settings())
                ->schema([
                    TextInput::make('season')->label('Markt (Jahr bzw. Name)')->required()->maxLength(20)
                        ->helperText('Neuer Wert = neuer Markt: Der Standplan startet leer, alte Bewerbungen bleiben in der Liste (Filter „Markt“).'),
                    TextInput::make('date')->label('Termin als Text (wie auf der Website angezeigt)')->maxLength(80)->placeholder('z. B. 12.–13. September 2027, 10–18 Uhr'),
                    \Filament\Forms\Components\DatePicker::make('start')->label('Erster Markttag (für Google)')->native(false)->displayFormat('d.m.Y'),
                    \Filament\Forms\Components\DatePicker::make('end')->label('Letzter Markttag')->native(false)->displayFormat('d.m.Y')->afterOrEqual('start'),
                    Toggle::make('open')->label('Bewerbungen über die Website annehmen'),
                ])
                ->action(function (array $data) {
                    Setting::write('market', ['season' => trim($data['season']), 'date' => $data['date'] ?: null, 'open' => (bool) $data['open'],
                        'start' => $data['start'] ? \Illuminate\Support\Carbon::parse($data['start'])->toDateString() : null,
                        'end' => $data['end'] ? \Illuminate\Support\Carbon::parse($data['end'])->toDateString() : null]);
                    Notification::make()->title('Markt-Einstellungen gespeichert')->success()->send();
                }),
        ];
    }
}
