<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Setting;
use App\Support\Reviews;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageReviews extends ManageRecords
{
    protected static string $resource = ReviewResource::class;

    public function getSubheading(): ?string
    {
        $r = Reviews::rating();

        return $r
            ? "Google-Gesamtbewertung auf der Website: {$r['value']} Sterne · {$r['count']} Bewertungen (Stand {$r['as_of']})".($r['profile_url'] ? '' : ' · Link zum Google-Profil fehlt noch')
            : 'Google-Gesamtbewertung ist ausgeblendet.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gesamt')->label('Google-Gesamtbewertung')->icon(Heroicon::OutlinedChartBar)->color('gray')
                ->fillForm(function () {
                    $r = Reviews::rating();

                    return ['value' => $r ? str_replace(',', '.', $r['value']) : '5.0', 'count' => $r['count'] ?? 0, 'profile_url' => $r['profile_url'] ?? null];
                })
                ->schema([
                    TextInput::make('value')->label('Durchschnitt (z. B. 5.0 oder 4.9)')->required()->numeric()->minValue(1)->maxValue(5)->step(0.1),
                    TextInput::make('count')->label('Anzahl Bewertungen')->required()->integer()->minValue(0),
                    TextInput::make('profile_url')->label('Link zum Google-Profil (in Google Maps „Teilen“ → Link kopieren)')->url()->maxLength(500)
                        ->rule('regex:#^https://(maps\.app\.goo\.gl|g\.page|goo\.gl|(www\.)?google\.[a-z.]+)/#i'),
                ])
                ->action(function (array $data) {
                    Setting::write('google_rating', [
                        'value' => (float) $data['value'], 'count' => (int) $data['count'],
                        'as_of' => now()->format('d.m.Y'), 'profile_url' => $data['profile_url'] ?: null,
                    ]);
                    Notification::make()->title('Gesamtbewertung gespeichert')->success()->send();
                }),
            CreateAction::make()->label('Bewertung hinzufügen'),
        ];
    }
}
