<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function statusAction(string $name, string $label, string $status, string $color, Heroicon $icon, array $from): Action
    {
        return Action::make($name)->label($label)->icon($icon)->color($color)
            ->visible(fn (Order $record) => in_array($record->status, $from, true))
            ->requiresConfirmation($status === 'storniert')
            ->action(function (Order $record) use ($status, $label) {
                $record->update(['status' => $status]);
                Notification::make()->title("{$record->number}: {$label}")->success()->send();
            });
    }

    public static function actions(): array
    {
        return [
            self::statusAction('zahlung_offen', 'Zahlungsdaten gesendet', 'zahlung_offen', 'info', Heroicon::OutlinedEnvelope, ['neu']),
            self::statusAction('bezahlt', 'Zahlung erhalten', 'bezahlt', 'success', Heroicon::OutlinedBanknotes, ['neu', 'zahlung_offen']),
            self::statusAction('bestaetigt', 'Bestätigen', 'bestaetigt', 'success', Heroicon::OutlinedCheckBadge, ['neu', 'zahlung_offen', 'bezahlt']),
            self::statusAction('versendet', 'Versendet / übergeben', 'versendet', 'gray', Heroicon::OutlinedTruck, ['bezahlt', 'bestaetigt']),
            self::statusAction('abgeschlossen', 'Abschließen', 'abgeschlossen', 'gray', Heroicon::OutlinedArchiveBox, ['bestaetigt', 'versendet']),
            self::statusAction('storniert', 'Stornieren', 'storniert', 'danger', Heroicon::OutlinedXCircle, ['neu', 'zahlung_offen', 'bezahlt', 'bestaetigt']),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nr.')->searchable()->sortable()->weight('bold'),
                TextColumn::make('created_at')->label('Eingang')->since()->dateTimeTooltip('d.m.Y H:i')->sortable(),
                TextColumn::make('type')->label('Art')->badge()->formatStateUsing(fn ($state) => Order::TYPES[$state] ?? $state)->color('gray'),
                TextColumn::make('name')->label('Name')->searchable()->description(fn (Order $r) => $r->email),
                TextColumn::make('summary')->label('Was')->state(fn (Order $r) => $r->summary())->wrap(),
                TextColumn::make('amount')->label('Betrag')->money('EUR', locale: 'de')->sortable()
                    ->description(fn (Order $r) => $r->discount_percent ? "−{$r->discount_percent} % ({$r->discount_code})" : null),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('assignee.name')->label('Zuständig')->toggleable(),
                TextColumn::make('notes_count')->label('Notizen')->counts('notes')->toggleable(),
            ])
            ->filters([
                Filter::make('offen')->label('Nur offene')->query(fn (Builder $query) => $query->whereIn('status', Order::OPEN))->default(),
                SelectFilter::make('type')->label('Art')->options(Order::TYPES),
                SelectFilter::make('status')->label('Status')->options(Order::STATUSES),
            ])
            ->recordActions([
                ActionGroup::make(self::actions())->label('Status')->icon(Heroicon::OutlinedArrowPath)->button(),
                EditAction::make()->label('Öffnen'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Noch keine Vorgänge')
            ->emptyStateDescription('Buchungen und Anfragen aus der Website erscheinen hier automatisch.');
    }
}
