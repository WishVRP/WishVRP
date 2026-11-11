<?php

namespace App\Filament\Resources\MarketApplications;

use App\Filament\Resources\MarketApplications\Pages\ManageMarketApplications;
use App\Mail\MarketMail;
use App\Models\MarketApplication;
use App\Support\Market;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

/** Bewerbungen für den Kunst- & Handwerkermarkt (Online-Standplan). */
class MarketApplicationResource extends Resource
{
    protected static ?string $model = MarketApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Markt-Bewerbungen';

    protected static ?string $modelLabel = 'Bewerbung';

    protected static ?string $pluralModelLabel = 'Markt-Bewerbungen';

    protected static ?int $navigationSort = 7;

    public static function getNavigationBadge(): ?string
    {
        $n = MarketApplication::query()->where('status', 'eingegangen')->count();

        return $n ? (string) $n : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Placeholder::make('fotos')->label('Fotos')->columnSpanFull()
                ->content(fn (?MarketApplication $record) => new HtmlString(collect($record?->photos ?? [])->keys()->map(fn ($i) =>
                    '<a href="'.e(route('market.photo', [$record, $i])).'" target="_blank" rel="noopener" class="fi-link">Foto '.($i + 1).' öffnen</a>')->implode(' · ') ?: '–')),
            TextInput::make('name')->label('Name')->required(),
            TextInput::make('company')->label('Werkstatt / Marke'),
            TextInput::make('email')->label('E-Mail')->email()->required(),
            TextInput::make('phone')->label('Telefon'),
            TextInput::make('street')->label('Straße'),
            TextInput::make('postal_code')->label('PLZ'),
            TextInput::make('city')->label('Ort'),
            TextInput::make('website')->label('Website / Instagram'),
            Select::make('category')->label('Sortiment')->options(array_combine(config('ulbrich.market.categories'), config('ulbrich.market.categories'))),
            Select::make('stand')->label('Stand')->options(collect(Market::stands())->mapWithKeys(fn ($s) => [$s['number'] => 'Nr. '.$s['number'].' · '.$s['label']])),
            Textarea::make('description')->label('Beschreibung des Handwerks')->rows(5)->columnSpanFull(),
            Select::make('tables')->label('Biertische (innen)')->options([1 => '1 Biertisch', 2 => '2 Biertische'])->placeholder('– (Außenstand)'),
            \Filament\Forms\Components\Toggle::make('lighting')->label('Beleuchtung gewünscht'),
            \Filament\Forms\Components\Toggle::make('food')->label('Lebensmittel / Getränke (Freigabe nötig)'),
            \Filament\Forms\Components\Toggle::make('demo')->label('Vorführung (Freigabe nötig)'),
            TextInput::make('amount')->label('Gebühr (€)')->numeric()->step(0.01),
            Select::make('status')->label('Status')->options(MarketApplication::STATUSES)->required(),
            Textarea::make('internal_note')->label('Interne Notiz')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    private static function mailAction(string $name, string $label, string $status, string $mailKind, string $color, Heroicon $icon, array $from, array $stamp = []): Action
    {
        return Action::make($name)->label($label)->icon($icon)->color($color)
            ->visible(fn (MarketApplication $record) => in_array($record->status, $from, true))
            ->schema([Textarea::make('personal')->label('Persönliche Nachricht (optional, erscheint in der E-Mail)')->rows(3)->maxLength(1000)])
            ->action(function (MarketApplication $record, array $data) use ($status, $mailKind, $label, $stamp) {
                $record->update(['status' => $status] + array_fill_keys($stamp, now()));
                try {
                    Mail::to($record->email, $record->name)->send(new MarketMail($record, $mailKind, $data['personal'] ?? null));
                } catch (\Throwable $e) {
                    report($e);
                }
                Notification::make()->title("{$record->code}: {$label}")->body('E-Mail an '.$record->email.' gesendet.')->success()->send();
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('stand')
            ->columns([
                TextColumn::make('stand')->label('Stand')->sortable()->formatStateUsing(fn ($state) => 'Nr. '.$state)
                    ->description(fn (MarketApplication $r) => Market::stands()[$r->stand]['label'] ?? ''),
                TextColumn::make('name')->label('Aussteller')->searchable()->description(fn (MarketApplication $r) => $r->company),
                TextColumn::make('category')->label('Sortiment')->toggleable()
                    ->description(fn (MarketApplication $r) => implode(' · ', array_filter([$r->tables ? $r->tables.' Tisch'.($r->tables > 1 ? 'e' : '') : null, $r->lighting ? 'Licht' : null, $r->food ? '⚠ Lebensmittel' : null, $r->demo ? '⚠ Vorführung' : null]))),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => MarketApplication::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['eingegangen' => 'warning', 'angenommen' => 'info', 'bezahlt' => 'success', 'abgelehnt' => 'gray', 'storniert' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('amount')->label('Gebühr')->money('EUR', locale: 'de'),
                TextColumn::make('code')->label('Code')->fontFamily('mono')->copyable(),
                TextColumn::make('season')->label('Markt')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Eingang')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('season')->label('Markt')->options(fn () => MarketApplication::query()->distinct()->pluck('season', 'season')->all())
                    ->default(fn () => Market::season()),
                SelectFilter::make('status')->label('Status')->options(MarketApplication::STATUSES),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::mailAction('annehmen', 'Annehmen (Zahlungsdaten senden)', 'angenommen', 'angenommen', 'success', Heroicon::OutlinedCheckBadge, ['eingegangen'], ['accepted_at']),
                    self::mailAction('bezahlt', 'Zahlung erhalten (Stand fest)', 'bezahlt', 'bezahlt', 'success', Heroicon::OutlinedBanknotes, ['angenommen'], ['paid_at']),
                    self::mailAction('ablehnen', 'Ablehnen (Stand wird frei)', 'abgelehnt', 'abgelehnt', 'gray', Heroicon::OutlinedXMark, ['eingegangen', 'angenommen']),
                    Action::make('stornieren')->label('Stornieren (ohne E-Mail)')->icon(Heroicon::OutlinedXCircle)->color('danger')->requiresConfirmation()
                        ->visible(fn (MarketApplication $record) => in_array($record->status, MarketApplication::BLOCKING, true))
                        ->action(fn (MarketApplication $record) => $record->update(['status' => 'storniert'])),
                ])->label('Status')->icon(Heroicon::OutlinedArrowPath)->button(),
                EditAction::make()->label('Öffnen'),
            ])
            ->emptyStateHeading('Noch keine Bewerbungen')
            ->emptyStateDescription('Bewerbungen aus dem Online-Standplan auf /kunsthandwerkermarkt/ erscheinen hier.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageMarketApplications::route('/')];
    }
}
