<?php

namespace App\Filament\Resources\BlockedCheckouts;

use App\Filament\Resources\BlockedCheckouts\Pages\ListBlockedCheckouts;
use App\Models\BlockedCheckout;
use App\Support\Shipping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Gesperrte Bestellversuche (nur lesen). Einträge werden nach 180 Tagen automatisch gelöscht. */
class BlockedCheckoutResource extends Resource
{
    protected static ?string $model = BlockedCheckout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static ?string $navigationLabel = 'Gesperrte Bestellungen';

    protected static ?string $modelLabel = 'gesperrter Bestellversuch';

    protected static ?string $pluralModelLabel = 'Gesperrte Bestellungen';

    protected static ?int $navigationSort = 11;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Wann')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('country')->label('Land')->formatStateUsing(fn ($state) => Shipping::countryName($state))
                    ->description(fn (BlockedCheckout $r) => trim(($r->postal_code ?? '').' '.($r->city ?? ''))),
                TextColumn::make('item')->label('Artikel')->description(fn (BlockedCheckout $r) => config('ulbrich.export.knife_types.'.$r->knife_type.'.label')),
                TextColumn::make('reason')->label('Grund')->wrap(),
                TextColumn::make('name')->label('Kunde')->description(fn (BlockedCheckout $r) => $r->email)->searchable(['name', 'email']),
            ])
            ->filters([SelectFilter::make('country')->label('Land')->options(fn () => BlockedCheckout::query()->distinct()->pluck('country', 'country')->map(fn ($c) => Shipping::countryName($c))->all())])
            ->emptyStateHeading('Keine gesperrten Bestellversuche')
            ->emptyStateDescription('Wird ein Messer in ein Land bestellt, das laut Einfuhr-Regeln gesperrt ist, erscheint der Versuch hier mit Grund.');
    }

    public static function getPages(): array
    {
        return ['index' => ListBlockedCheckouts::route('/')];
    }
}
