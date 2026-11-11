<?php

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\ManageNotes;
use App\Models\Note;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Pinnwand: interne Notizen, für alle Konten sichtbar. */
class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Notizen';

    protected static ?string $modelLabel = 'Notiz';

    protected static ?string $pluralModelLabel = 'Notizen';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $n = Note::query()->open()->count();

        return $n ? (string) $n : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')->label('Notiz')->required()->rows(5)->columnSpanFull()
                ->placeholder('z. B. „Herr M. hat angerufen wegen Rückerstattung – meldet sich heute nochmal.“'),
            Select::make('order_id')->label('Gehört zu Vorgang (optional)')->searchable()
                ->getSearchResultsUsing(fn (string $search) => Order::query()->where('number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->limit(20)->get()->mapWithKeys(fn ($o) => [$o->id => "{$o->number} · {$o->name}"]))
                ->getOptionLabelUsing(fn ($value) => ($o = Order::find($value)) ? "{$o->number} · {$o->name}" : null),
            Toggle::make('is_pinned')->label('Oben anheften'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'order'])
                ->orderByDesc('is_pinned')->orderByRaw('done_at is not null')->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('is_pinned')->label('')->formatStateUsing(fn ($state) => $state ? '★' : '')->color('warning')->tooltip(fn (Note $record) => $record->is_pinned ? 'angeheftet' : null),
                TextColumn::make('body')->label('Notiz')->wrap()->searchable()
                    ->color(fn (Note $r) => $r->done_at ? 'gray' : null),
                TextColumn::make('user.name')->label('Von')->placeholder('System'),
                TextColumn::make('order.number')->label('Vorgang')
                    ->url(fn (Note $r) => $r->order_id ? \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $r->order_id]) : null),
                TextColumn::make('created_at')->label('Wann')->since()->dateTimeTooltip('d.m.Y H:i'),
                TextColumn::make('done_at')->label('Erledigt')->since()->placeholder('offen'),
            ])
            ->filters([
                TernaryFilter::make('erledigt')->label('Status')->placeholder('Alle')
                    ->trueLabel('Erledigt')->falseLabel('Offen')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('done_at'),
                        false: fn (Builder $query) => $query->whereNull('done_at'),
                        blank: fn (Builder $query) => $query,
                    )
                    ->default(false),
            ])
            ->recordActions([
                Action::make('erledigt')->label('Erledigt')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (Note $r) => ! $r->done_at)->action(fn (Note $r) => $r->update(['done_at' => now()])),
                Action::make('wieder_offen')->label('Wieder öffnen')->icon(Heroicon::OutlinedArrowUturnLeft)->color('gray')
                    ->visible(fn (Note $r) => (bool) $r->done_at)->action(fn (Note $r) => $r->update(['done_at' => null])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Keine offenen Notizen');
    }

    public static function getPages(): array
    {
        return ['index' => ManageNotes::route('/')];
    }
}
