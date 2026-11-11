<?php

namespace App\Filament\Resources\Vouchers;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Vouchers\Pages\ListVouchers;
use App\Models\Voucher;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Kurs-Gutscheine: Code, Wert, Status, Kauf- und Einlöse-Vorgang. */
class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Gutscheine';

    protected static ?string $modelLabel = 'Gutschein';

    protected static ?string $pluralModelLabel = 'Gutscheine';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;   // entstehen durch Kauf (online oder als Vorgang „Gutschein“)
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->copyable()->weight('bold'),
                TextColumn::make('course.name')->label('Kurs')->placeholder('Wertgutschein')->description(fn (Voucher $r) => $r->persons.' '.($r->persons === 1 ? 'Person' : 'Personen').($r->recipient ? ' · für '.$r->recipient : '')),
                TextColumn::make('value')->label('Wert')->money('EUR', locale: 'de')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn ($state) => Voucher::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['bezahlt' => 'success', 'eingeloest' => 'gray', 'storniert' => 'danger'][$state] ?? 'warning'),
                TextColumn::make('valid_until')->label('Gültig bis')->date('d.m.Y')->sortable(),
                TextColumn::make('order.number')->label('Gekauft')->url(fn (Voucher $r) => $r->order_id ? OrderResource::getUrl('edit', ['record' => $r->order_id]) : null),
                TextColumn::make('redeemedOrder.number')->label('Eingelöst bei')->placeholder('–')
                    ->url(fn (Voucher $r) => $r->redeemed_order_id ? OrderResource::getUrl('edit', ['record' => $r->redeemed_order_id]) : null),
            ])
            ->filters([SelectFilter::make('status')->options(Voucher::STATUSES)])
            ->recordActions([
                Action::make('eingeloest')->label('Als eingelöst markieren')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (Voucher $r) => $r->status === 'bezahlt')->requiresConfirmation()
                    ->modalDescription('Zum Beispiel, wenn der Gutschein telefonisch für eine Buchung genutzt wurde.')
                    ->action(fn (Voucher $r) => $r->update(['status' => 'eingeloest', 'redeemed_at' => now()])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListVouchers::route('/')];
    }
}
