<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Newsletter-Empfänger (Double-Opt-in). Werbung nur an „bestätigt“. */
class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $modelLabel = 'Empfänger';

    protected static ?string $pluralModelLabel = 'Newsletter';

    protected static ?int $navigationSort = 7;

    public static function canCreate(): bool
    {
        return false;   // Einwilligung nur durch die Person selbst (Nachweis)
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) NewsletterSubscriber::query()->active()->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('consented_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('E-Mail')->searchable()->copyable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->state(fn (NewsletterSubscriber $r) => $r->unsubscribed_at ? 'abgemeldet' : ($r->confirmed_at ? 'bestätigt' : 'wartet auf Bestätigung'))
                    ->color(fn (string $state) => ['bestätigt' => 'success', 'abgemeldet' => 'gray'][$state] ?? 'warning'),
                TextColumn::make('source')->label('Quelle'),
                TextColumn::make('consented_at')->label('Eingewilligt')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('confirmed_at')->label('Bestätigt')->dateTime('d.m.Y H:i')->placeholder('–')->sortable(),
                TextColumn::make('deliveries_count')->label('Newsletter erhalten')->counts('deliveries')->sortable(),
                TextColumn::make('unsubscribed_at')->label('Abgemeldet')->dateTime('d.m.Y H:i')->placeholder('–')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('zustand')->label('Status')->options(['aktiv' => 'Bestätigt', 'offen' => 'Wartet auf Bestätigung', 'ab' => 'Abgemeldet'])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'aktiv' => $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at'),
                        'offen' => $query->whereNull('confirmed_at')->whereNull('unsubscribed_at'),
                        'ab' => $query->whereNotNull('unsubscribed_at'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('abmelden')->label('Abmelden')->icon(Heroicon::OutlinedNoSymbol)->color('gray')
                    ->visible(fn (NewsletterSubscriber $r) => ! $r->unsubscribed_at)
                    ->requiresConfirmation()->modalDescription('Zum Beispiel, wenn die Person per E-Mail oder Telefon widerrufen hat.')
                    ->action(fn (NewsletterSubscriber $r) => $r->update(['unsubscribed_at' => now()])),
            ])
            ->emptyStateHeading('Noch keine Newsletter-Anmeldungen');
    }

    public static function getPages(): array
    {
        return ['index' => ListNewsletterSubscribers::route('/')];
    }
}
