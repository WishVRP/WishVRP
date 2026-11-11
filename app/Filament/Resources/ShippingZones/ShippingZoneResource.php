<?php

namespace App\Filament\Resources\ShippingZones;

use App\Filament\Resources\ShippingZones\Pages\ManageShippingZones;
use App\Models\ShippingZone;
use App\Support\Shipping;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Versandkosten: Ländergruppen mit DHL-Preisen je Gewichtsstufe.
 * Kundinnen und Kunden sehen immer den auf ,99 aufgerundeten Preis.
 */
class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Versandkosten';

    protected static ?string $modelLabel = 'Ländergruppe';

    protected static ?string $pluralModelLabel = 'Versandkosten';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Name der Ländergruppe')->required()->maxLength(80),
            Select::make('countries')->label('Länder')->multiple()->searchable()->options(Shipping::countries())
                ->helperText('Bei „Alle übrigen Länder“ leer lassen.'),
            Toggle::make('rest_of_world')->label('Gilt für alle übrigen Länder (Rest der Welt)'),
            Toggle::make('customs')->label('Außerhalb der EU (Hinweis auf Zoll/Einfuhrabgaben)'),
            Toggle::make('active')->label('Aktiv')->default(true),
            Repeater::make('rates')->relationship()->label('Preise laut DHL (vor Aufrundung)')->columnSpanFull()
                ->orderColumn(false)->defaultItems(0)->addActionLabel('Gewichtsstufe hinzufügen')
                ->schema([
                    TextInput::make('max_weight_g')->label('bis Gewicht (g)')->numeric()->required()->minValue(1)->maxValue(31500),
                    TextInput::make('dhl_price')->label('DHL-Preis (€)')->numeric()->required()->step(0.01)->minValue(0.01)->live(onBlur: true),
                    TextInput::make('label')->label('Bezeichnung')->maxLength(80)->placeholder('z. B. DHL Paket bis 2 kg'),
                    Placeholder::make('kunde')->label('Kunde zahlt')
                        ->content(fn (Get $get) => is_numeric($get('dhl_price')) ? number_format(Shipping::roundUp((float) $get('dhl_price')), 2, ',', '.').' €' : '–'),
                ])->columns(4),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')->reorderable('sort')
            ->columns([
                TextColumn::make('name')->label('Ländergruppe')->weight('bold'),
                TextColumn::make('countries')->label('Länder')
                    ->state(fn (ShippingZone $r) => $r->rest_of_world ? 'alle übrigen' : count($r->countries ?? []).' Länder'),
                TextColumn::make('preise')->label('Kunde zahlt (aufgerundet)')->wrap()
                    ->state(fn (ShippingZone $r) => $r->rates->isEmpty() ? 'auf Anfrage – noch keine Preise'
                        : $r->rates->map(fn ($x) => 'bis '.rtrim(rtrim(number_format($x->max_weight_g / 1000, 1, ',', ''), '0'), ',').' kg: '.number_format($x->customerPrice(), 2, ',', '.').' €')->implode(' · ')),
                IconColumn::make('customs')->label('Zollhinweis')->boolean(),
                IconColumn::make('active')->label('Aktiv')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('Keine Ländergruppen');
    }

    public static function getPages(): array
    {
        return ['index' => ManageShippingZones::route('/')];
    }
}
