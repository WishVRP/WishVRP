<?php

namespace App\Filament\Resources\Withdrawals;

use App\Filament\Resources\Withdrawals\Pages\ListWithdrawals;
use App\Mail\WithdrawalMail;
use App\Models\Note;
use App\Models\Withdrawal;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

/** Eingegangene Widerrufe (Widerrufsfunktion der Website): prüfen, annehmen (Vorgang stornieren) oder begründet ablehnen. */
class WithdrawalResource extends Resource
{
    protected static ?string $model = Withdrawal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?string $navigationLabel = 'Widerrufe';

    protected static ?string $modelLabel = 'Widerruf';

    protected static ?string $pluralModelLabel = 'Widerrufe';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Withdrawal::query()->where('status', 'eingegangen')->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    private static function decide(Withdrawal $w, string $status, ?string $note): void
    {
        $w->update(['status' => $status, 'decision_note' => $note, 'decided_at' => now()]);
        if ($status === 'angenommen' && $w->order && $w->order->status !== 'storniert') {
            $w->order->update(['status' => 'storniert']);   // gibt Kurstag, Lager und Edition-Nummer frei
        }
        if ($w->order) {
            Note::query()->create(['order_id' => $w->order_id, 'user_id' => auth()->id(), 'body' => 'Widerruf '.($status === 'angenommen' ? 'angenommen – Vorgang storniert. Rückzahlung innerhalb von 14 Tagen veranlassen.' : 'abgelehnt: '.$note), 'done_at' => now()]);
        }
        try {
            Mail::to($w->email, $w->name)->send(new WithdrawalMail($w, $status));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Eingegangen')->dateTime('d.m.Y H:i')->sortable(),
                TextColumn::make('name')->label('Name')->description(fn (Withdrawal $r) => $r->email)->searchable(['name', 'email']),
                TextColumn::make('order_number')->label('Vorgang')->placeholder('–')
                    ->description(fn (Withdrawal $r) => $r->order ? $r->order->typeLabel().' · '.$r->order->summary() : ($r->order_number ? 'nicht zugeordnet (Nummer/E-Mail passt nicht)' : null)),
                TextColumn::make('message')->label('Nachricht')->limit(60)->wrap()->placeholder('–'),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn ($s) => Withdrawal::STATUSES[$s] ?? $s)
                    ->color(fn ($s) => ['eingegangen' => 'danger', 'angenommen' => 'success', 'abgelehnt' => 'gray'][$s] ?? 'gray'),
            ])
            ->filters([SelectFilter::make('status')->options(Withdrawal::STATUSES)])
            ->recordActions([
                Action::make('annehmen')->label('Annehmen (stornieren)')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (Withdrawal $r) => $r->status === 'eingegangen')
                    ->schema([Textarea::make('note')->label('Nachricht an Kunden (optional)')->rows(2)])
                    ->modalDescription('Vorgang wird storniert (Kurstag/Lager werden frei), der Kunde bekommt die Bestätigung. Die Rückzahlung (innerhalb von 14 Tagen) veranlasst ihr selbst.')
                    ->action(function (Withdrawal $record, array $data) {
                        self::decide($record, 'angenommen', $data['note'] ?? null);
                        Notification::make()->title('Widerruf angenommen')->success()->send();
                    }),
                Action::make('ablehnen')->label('Ablehnen (Ausnahme)')->icon(Heroicon::OutlinedXMark)->color('gray')
                    ->visible(fn (Withdrawal $r) => $r->status === 'eingegangen')
                    ->schema([Textarea::make('note')->label('Begründung für den Kunden')->required()->rows(3)
                        ->default('Bei Freizeitveranstaltungen mit festem Termin (z. B. Schmiedekurs) bzw. bei Waren, die nach Kundenwunsch angefertigt werden, besteht nach § 312g Abs. 2 BGB kein Widerrufsrecht.')])
                    ->modalDescription('Nur wenn eine gesetzliche Ausnahme greift – im Zweifel vorher rechtlich prüfen.')
                    ->action(function (Withdrawal $record, array $data) {
                        self::decide($record, 'abgelehnt', $data['note']);
                        Notification::make()->title('Widerruf abgelehnt, Kunde informiert')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Keine Widerrufe')
            ->emptyStateDescription('Widerrufe über „Vertrag widerrufen“ auf der Website erscheinen hier.');
    }

    public static function getPages(): array
    {
        return ['index' => ListWithdrawals::route('/')];
    }
}
