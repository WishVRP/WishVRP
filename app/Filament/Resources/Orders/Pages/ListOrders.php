<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('testmail')->label('Test-E-Mail senden')->icon(\Filament\Support\Icons\Heroicon::OutlinedEnvelope)->color('gray')
                ->modalDescription('Prüft, ob der Mail-Versand (MAIL_* in der .env) funktioniert. Bei einem Fehler steht hier der genaue Grund.')
                ->schema([\Filament\Forms\Components\TextInput::make('to')->label('An diese Adresse')->email()->required()->default(fn () => auth()->user()?->email)])
                ->action(function (array $data) {
                    try {
                        \Illuminate\Support\Facades\Mail::raw("Test von der Website ".config('ulbrich.name').".\nWenn du diese E-Mail liest, funktioniert der Versand.\n\nGesendet: ".now()->format('d.m.Y H:i'), fn ($m) => $m->to($data['to'])->subject('Test-E-Mail: Versand funktioniert'));
                        \Filament\Notifications\Notification::make()->title('Test-E-Mail an '.$data['to'].' verschickt')->body('Bitte auch im Spam-Ordner nachsehen.')->success()->send();
                    } catch (\Throwable $e) {
                        report($e);
                        \Filament\Notifications\Notification::make()->title('Versand fehlgeschlagen')->body(\Illuminate\Support\Str::limit($e->getMessage(), 400))->danger()->persistent()->send();
                    }
                }),
            CreateAction::make()->label('Vorgang anlegen (z. B. Telefonbuchung)'),
        ];
    }
}
