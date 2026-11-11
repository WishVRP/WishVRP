<?php

namespace App\Filament\Resources\DiscountCodes;

use App\Filament\Resources\DiscountCodes\Pages\ListDiscountCodes;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\DiscountCode;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Alle persönlichen Newsletter-Rabattcodes (Weihnachtscodes stehen unter „Weihnachtsaktion“). */
class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Rabattcodes';

    protected static ?string $modelLabel = 'Rabattcode';

    protected static ?string $pluralModelLabel = 'Rabattcodes (Newsletter)';

    protected static ?int $navigationSort = 9;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->copyable()->weight('bold'),
                TextColumn::make('email')->label('E-Mail')->searchable(),
                TextColumn::make('percent')->label('Rabatt')->suffix(' %'),
                TextColumn::make('issue.subject')->label('Aus Ausgabe')->limit(40),
                TextColumn::make('valid_until')->label('Gültig bis')->date('d.m.Y')->sortable()
                    ->color(fn (DiscountCode $record) => ! $record->redeemed_at && $record->valid_until->isPast() ? 'gray' : null),
                TextColumn::make('redeemed_at')->label('Eingelöst')->dateTime('d.m.Y H:i')->placeholder('nein')->sortable(),
                TextColumn::make('order.number')->label('Vorgang')
                    ->url(fn (DiscountCode $record) => $record->order_id ? OrderResource::getUrl('edit', ['record' => $record->order_id]) : null),
            ])
            ->filters([
                Filter::make('eingeloest')->label('Eingelöst')->query(fn (Builder $query) => $query->whereNotNull('redeemed_at')),
                Filter::make('gueltig')->label('Noch gültig, nicht eingelöst')->query(fn (Builder $query) => $query->whereNull('redeemed_at')->where('valid_until', '>=', now())),
            ])
            ->recordActions([
                Action::make('einloesen')->label('Als eingelöst markieren')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (DiscountCode $record) => ! $record->redeemed_at)->requiresConfirmation()
                    ->action(fn (DiscountCode $record) => $record->update(['redeemed_at' => now()])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDiscountCodes::route('/')];
    }
}
