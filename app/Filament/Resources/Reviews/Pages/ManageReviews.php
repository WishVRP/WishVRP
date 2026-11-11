<?php

namespace App\Filament\Resources\Reviews\Pages;

use App\Filament\Resources\Reviews\ReviewResource;
use App\Models\Setting;
use App\Support\GoogleReviews;
use App\Support\Reviews;
use Filament\Forms\Components\Select;
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
        if (GoogleReviews::enabled()) {
            $g = GoogleReviews::get();

            return $g
                ? "Automatisch mit Google verbunden – {$r['value']} Sterne · {$r['count']} Bewertungen, ".count($g['reviews']).' Bewertungstexte von Google (zuletzt abgerufen '.$g['fetched_at'].'). Neue Google-Bewertungen erscheinen von selbst; Google liefert höchstens 5 Texte. Bewertungen hier unten ergänzen diese.'
                : 'Mit Google verbunden, aber der letzte Abruf ist fehlgeschlagen – es werden die Einträge von hier gezeigt. Details in storage/logs/laravel.log.';
        }
        if (filled(config('services.google_places.key'))) {
            return 'Google-Schlüssel ist eingetragen – jetzt oben rechts „Google verbinden“ klicken und das Unternehmen auswählen.';
        }

        return $r
            ? "Google-Gesamtbewertung auf der Website: {$r['value']} Sterne · {$r['count']} Bewertungen (Stand {$r['as_of']})".($r['profile_url'] ? '' : ' · Link zum Google-Profil fehlt noch')
            : 'Google-Gesamtbewertung ist ausgeblendet.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verbinden')->label('Google verbinden')->icon(Heroicon::OutlinedLink)
                ->visible(fn () => filled(config('services.google_places.key')) && ! config('services.google_places.place_id'))
                ->schema([
                    TextInput::make('query')->label('Unternehmen bei Google suchen')->default(config('ulbrich.name').' '.config('ulbrich.address.locality'))->required()
                        ->live(onBlur: true),
                    Select::make('place_id')->label('Treffer auswählen')->required()->native(false)
                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                            if (blank($get('query'))) {
                                return [];
                            }
                            try {
                                return collect(GoogleReviews::search($get('query')))->mapWithKeys(fn ($p) => [$p['id'] => $p['name'].' – '.$p['address']])->all();
                            } catch (\Throwable $e) {
                                return ['' => 'Fehler: '.$e->getMessage()];
                            }
                        }),
                ])
                ->action(function (array $data) {
                    Setting::write('google_place_id', $data['place_id']);
                    $g = GoogleReviews::refresh();
                    $g ? Notification::make()->title('Mit Google verbunden')->body(count($g['reviews']).' Bewertungen geladen.')->success()->send()
                       : Notification::make()->title('Verbunden, aber Abruf fehlgeschlagen')->body('Schlüssel und Freischaltung der „Places API (New)“ prüfen.')->danger()->send();
                }),
            Action::make('aktualisieren')->label('Jetzt von Google laden')->icon(Heroicon::OutlinedArrowPath)->color('gray')
                ->visible(fn () => GoogleReviews::enabled())
                ->action(function () {
                    $g = GoogleReviews::refresh();
                    $g ? Notification::make()->title('Aktualisiert')->body(count($g['reviews']).' Bewertungen von Google.')->success()->send()
                       : Notification::make()->title('Abruf fehlgeschlagen')->danger()->send();
                }),
            Action::make('gesamt')->label('Gesamtbewertung von Hand')->icon(Heroicon::OutlinedChartBar)->color('gray')
                ->visible(fn () => ! GoogleReviews::enabled())
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
            Action::make('bewertungslink')->label('Link „Bewertung schreiben“')->icon(Heroicon::OutlinedPencilSquare)->color('gray')
                ->fillForm(fn () => ['url' => Setting::read('google_review_url')])
                ->modalDescription(fn () => 'Dieser Link steckt im Knopf „Jetzt bei Google bewerten“ der E-Mails nach Zustellung und nach dem Kurs. Aktuell: '.(Reviews::writeUrl() ?? 'noch keiner – bitte eintragen oder Google verbinden').'. Den Link findest du im Google-Unternehmensprofil unter „Bewertungen anfordern“ bzw. „Profil teilen“.')
                ->schema([
                    TextInput::make('url')->label('Link (leer = automatisch aus der Google-Verbindung)')->url()->maxLength(500)
                        ->rule('regex:#^https://(g\.page|maps\.app\.goo\.gl|goo\.gl|search\.google\.com|(www\.)?google\.[a-z.]+)/#i'),
                ])
                ->action(function (array $data) {
                    Setting::write('google_review_url', $data['url'] ?: null);
                    Notification::make()->title('Bewertungslink gespeichert')->success()->send();
                }),
            CreateAction::make()->label('Bewertung hinzufügen'),
        ];
    }
}
