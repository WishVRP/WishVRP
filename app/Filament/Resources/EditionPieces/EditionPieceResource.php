<?php

namespace App\Filament\Resources\EditionPieces;

use App\Filament\Resources\EditionPieces\Pages\ManageEditionPieces;
use App\Models\EditionPiece;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

/** Edition 100: Status und Käufername je Seriennummer (Name öffentlich nur mit Zustimmung). */
class EditionPieceResource extends Resource
{
    protected static ?string $model = EditionPiece::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Edition 100';

    protected static ?string $modelLabel = 'Messer der Edition';

    protected static ?string $pluralModelLabel = 'Edition 100';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return EditionPiece::query()->where('status', 'vergeben')->count().'/'.config('ulbrich.edition.total');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')->label('Status')->options(EditionPiece::STATUSES)->required()->default('frei'),
            TextInput::make('buyer_name')->label('Anzeigename Käufer/in')->maxLength(80)->placeholder('z. B. Erika M.'),
            Toggle::make('show_name')->label('Name darf im Raster auf der Website erscheinen')
                ->helperText('Nur einschalten, wenn die Person zugestimmt hat (z. B. Häkchen im Bestellformular).'),
            Select::make('order_id')->label('Vorgang')->searchable()
                ->getSearchResultsUsing(fn (string $search) => Order::query()->where('number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->limit(20)->get()->mapWithKeys(fn ($o) => [$o->id => "{$o->number} · {$o->name}".($o->public_name ? " (Anzeigename: {$o->public_name})" : '')]))
                ->getOptionLabelUsing(fn ($value) => ($o = Order::find($value)) ? "{$o->number} · {$o->name}" : null),
            TextInput::make('note')->label('Interne Notiz')->maxLength(190)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('number')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('number')->label('Nr.')->sortable()->weight('bold')
                    ->description(fn (EditionPiece $r) => $r->isSpecial() ? 'Sonderstück' : null),
                SelectColumn::make('status')->label('Status')->options(EditionPiece::STATUSES)->selectablePlaceholder(false),
                TextInputColumn::make('buyer_name')->label('Anzeigename')->placeholder('z. B. Erika M.'),
                ToggleColumn::make('show_name')->label('Name öffentlich'),
                TextColumn::make('order.number')->label('Vorgang')
                    ->url(fn (EditionPiece $r) => $r->order_id ? \App\Filament\Resources\Orders\OrderResource::getUrl('edit', ['record' => $r->order_id]) : null),
                TextColumn::make('preview')->label('So erscheint es')->state(fn (EditionPiece $r) => $r->publicLabel())->color('gray'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(EditionPiece::STATUSES),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('vergeben')->label('Als verkauft markieren')->icon(Heroicon::OutlinedCheck)
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'vergeben'])),
                    BulkAction::make('frei')->label('Als frei markieren')->icon(Heroicon::OutlinedArrowUturnLeft)
                        ->action(fn (Collection $records) => $records->each->update(['status' => 'frei', 'order_id' => null, 'buyer_name' => null, 'show_name' => false])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageEditionPieces::route('/')];
    }
}
