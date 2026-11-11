<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Shop-Produkte: Bilder, Beschreibung, Preis, Bestand. Aktive Produkte erscheinen im Shop, bei Google (Schema, Sitemap, Feed). */
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Produkte';

    protected static ?string $modelLabel = 'Produkt';

    protected static ?string $pluralModelLabel = 'Produkte';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Produkt')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Name')->required()->maxLength(160)
                    ->helperText('So, wie Kunden suchen: z. B. „Damast-Kochmesser 21 cm mit Olivenholzgriff“.'),
                TextInput::make('slug')->label('Adresse (URL)')->maxLength(160)->helperText('Leer lassen = aus dem Namen erzeugt.')->unique(ignoreRecord: true),
                Select::make('category')->label('Kategorie')->options(collect(Product::CATEGORIES)->map(fn ($c) => $c[0]))->required(),
                Select::make('status')->label('Status')->options(Product::STATUSES)->required()->default('entwurf'),
                TextInput::make('price')->label('Preis (€)')->numeric()->required()->minValue(0)->step(0.01),
                TextInput::make('stock')->label('Bestand')->numeric()->required()->minValue(0)->default(1)->helperText('Unikat = 1. Bei 0 wird es als „verkauft“ angezeigt.'),
                Textarea::make('short')->label('Kurzbeschreibung (für Google und Übersicht)')->rows(2)->maxLength(255)->columnSpanFull(),
                MarkdownEditor::make('description')->label('Beschreibung')->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'heading', 'undo', 'redo']),
            ]),
            Section::make('Bilder')->columnSpanFull()->schema([
                FileUpload::make('images')->label('Fotos (erstes Bild = Titelbild)')->multiple()->reorderable()->maxFiles(8)
                    ->image()->disk('public')->directory('products')->visibility('public')
                    // Sprechender Dateiname für die Google-Bildersuche: produktname-messermanufaktur-ulbrich-xxxxxx.jpg
                    // (nur a–z, 0–9, Bindestrich; Endung aus dem geprüften Bildtyp – nie aus dem Originalnamen)
                    ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, \Filament\Schemas\Components\Utilities\Get $get): string => Product::imageFileName((string) $get('name'), (string) $file->getMimeType()))
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(8192)
                    ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeTargetHeight('1600')
                    ->helperText('Mindestens 1 Foto, ideal 3–5 aus verschiedenen Perspektiven. Werden automatisch auf max. 1600 px verkleinert.'),
            ]),
            Section::make('Technische Daten (nur echte Angaben)')->columnSpanFull()->schema([
                Repeater::make('specs')->label('')->schema([
                    TextInput::make('label')->label('Merkmal')->required()->placeholder('z. B. Klingenlänge'),
                    TextInput::make('value')->label('Wert')->required()->placeholder('z. B. 21 cm'),
                ])->columns(2)->addActionLabel('Merkmal hinzufügen')->defaultItems(0)->reorderable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('images')->label('')->disk('public')->limit(1)->square(),
                TextColumn::make('name')->label('Name')->searchable()->weight('bold')->description(fn (Product $r) => $r->categoryName()),
                TextColumn::make('price')->label('Preis')->money('EUR', locale: 'de')->sortable(),
                TextColumn::make('stock')->label('Bestand')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn ($state) => Product::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['aktiv' => 'success', 'verkauft' => 'gray'][$state] ?? 'warning'),
            ])
            ->filters([
                SelectFilter::make('status')->options(Product::STATUSES),
                SelectFilter::make('category')->label('Kategorie')->options(collect(Product::CATEGORIES)->map(fn ($c) => $c[0])),
            ])
            ->recordActions([
                Action::make('ansehen')->label('Im Shop ansehen')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                    ->visible(fn (Product $r) => $r->status !== 'entwurf')->url(fn (Product $r) => $r->url(), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->emptyStateHeading('Noch keine Produkte')
            ->emptyStateDescription('Lege ein Messer mit Fotos, Beschreibung und Preis an und stelle es auf „Im Shop“.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
