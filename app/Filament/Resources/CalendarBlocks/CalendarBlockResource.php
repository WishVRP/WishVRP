<?php

namespace App\Filament\Resources\CalendarBlocks;

use App\Filament\Resources\CalendarBlocks\Pages\ManageCalendarBlocks;
use App\Models\CalendarBlock;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Gesperrte Tage im Buchungskalender (alle anderen Tage sind buchbar). */
class CalendarBlockResource extends Resource
{
    protected static ?string $model = CalendarBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Kalender & Sperrtage';

    protected static ?string $modelLabel = 'Sperrtag';

    protected static ?string $pluralModelLabel = 'Kalender & Sperrtage';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date')->label('Tag')->required()->native(false)->displayFormat('d.m.Y')->firstDayOfWeek(1)->unique(ignoreRecord: true),
            TextInput::make('reason')->label('Grund (intern)')->maxLength(120)->placeholder('z. B. Urlaub, Markt'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')->label('Tag')->date('D, d.m.Y')->sortable(),
                TextColumn::make('reason')->label('Grund')->placeholder('–'),
            ])
            ->filters([
                Filter::make('kommend')->label('Nur kommende')->query(fn (Builder $query) => $query->whereDate('date', '>=', now()->toDateString()))->default(),
            ])
            ->recordActions([DeleteAction::make()->label('Freigeben')])
            ->toolbarActions([DeleteBulkAction::make()->label('Ausgewählte freigeben')])
            ->emptyStateHeading('Keine Sperrtage')
            ->emptyStateDescription('Alle Tage sind buchbar (ein Kurs pro Tag). Sperre hier Urlaub oder Markttage.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageCalendarBlocks::route('/')];
    }
}
