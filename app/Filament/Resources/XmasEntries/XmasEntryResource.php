<?php

namespace App\Filament\Resources\XmasEntries;

use App\Filament\Resources\XmasEntries\Pages\ListXmasEntries;
use App\Models\XmasEntry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Weihnachtsaktion: Teilnahmen, Treffer, Codes, Einlösung. */
class XmasEntryResource extends Resource
{
    protected static ?string $model = XmasEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Weihnachtsaktion';

    protected static ?string $modelLabel = 'Teilnahme';

    protected static ?string $pluralModelLabel = 'Weihnachtsaktion';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('started_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('E-Mail')->searchable(),
                TextColumn::make('phone_key')->label('Handy')->searchable()->placeholder('–'),
                TextColumn::make('verified_at')->label('SMS bestätigt')->since()->placeholder('nein')->toggleable(),
                TextColumn::make('started_at')->label('Gespielt')->since()->dateTimeTooltip('d.m.Y H:i')->sortable(),
                TextColumn::make('score')->label('Treffer')->sortable()->placeholder('läuft/abgebrochen'),
                TextColumn::make('percent')->label('Rabatt')->suffix(' %')->sortable()->placeholder('–'),
                TextColumn::make('code')->label('Code')->searchable()->copyable()->placeholder('kein Code')->weight('bold'),
                TextColumn::make('redeemed_at')->label('Eingelöst')->since()->placeholder('nein'),
                TextColumn::make('order.number')->label('Vorgang')
                    ->url(fn (XmasEntry $record) => $record->order_id ? \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $record->order_id]) : null),
            ])
            ->filters([
                Filter::make('mit_code')->label('Nur mit Code')->query(fn (Builder $query) => $query->whereNotNull('code')),
                Filter::make('eingeloest')->label('Eingelöst')->query(fn (Builder $query) => $query->whereNotNull('redeemed_at')),
            ])
            ->recordActions([
                Action::make('einloesen')->label('Als eingelöst markieren')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (XmasEntry $record) => $record->code && ! $record->redeemed_at)
                    ->requiresConfirmation()->action(fn (XmasEntry $record) => $record->update(['redeemed_at' => now()])),
                Action::make('freigeben')->label('Einlösung zurücknehmen')->icon(Heroicon::OutlinedArrowUturnLeft)->color('gray')
                    ->visible(fn (XmasEntry $record) => (bool) $record->redeemed_at)
                    ->requiresConfirmation()->action(fn (XmasEntry $record) => $record->update(['redeemed_at' => null, 'order_id' => null])),
            ])
            ->emptyStateHeading('Noch keine Teilnahmen');
    }

    public static function getPages(): array
    {
        return ['index' => ListXmasEntries::route('/')];
    }
}
