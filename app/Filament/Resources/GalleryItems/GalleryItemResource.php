<?php

namespace App\Filament\Resources\GalleryItems;

use App\Filament\Resources\GalleryItems\Pages\ManageGalleryItems;
use App\Models\Course;
use App\Models\GalleryItem;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Galerie „Bei uns geschmiedet“ → öffentliche Seite /galerie/. */
class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Galerie';

    protected static ?string $modelLabel = 'Galerie-Foto';

    protected static ?string $pluralModelLabel = 'Galerie';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')->label('Foto')->required()->image()->disk('public')->directory('galerie')->visibility('public')
                ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, \Filament\Schemas\Components\Utilities\Get $get): string => Product::imageFileName('schmiedekurs-'.((string) $get('alt') ?: 'galerie'), (string) $file->getMimeType()))
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(8192)
                ->imageResizeMode('contain')->imageResizeTargetWidth('1600')->imageResizeTargetHeight('1600')
                ->helperText('Wird automatisch auf max. 1600 px verkleinert. Tipp: erst die Bildbeschreibung ausfüllen – sie wird Teil des Dateinamens.')
                ->columnSpanFull(),
            TextInput::make('alt')->label('Bildbeschreibung für Google und Screenreader *')->required()->maxLength(190)->live(onBlur: true)
                ->placeholder('z. B. Selbst geschmiedetes Damastmesser nach dem Kurs'),
            TextInput::make('caption')->label('Bildunterschrift (optional, sichtbar)')->maxLength(190)->placeholder('z. B. Lisas erstes Messer'),
            Select::make('course_id')->label('Kurs (optional)')->options(fn () => Course::query()->orderBy('name')->pluck('name', 'id'))->native(false),
            TextInput::make('sort')->label('Reihenfolge (kleiner = weiter vorne)')->numeric()->default(0),
            Checkbox::make('consent')->label('Alle erkennbaren Personen haben der Veröffentlichung auf der Website zugestimmt (schriftlich/per E-Mail).')->accepted()->columnSpanFull()
                ->helperText('Ohne Einwilligung wird das Foto nicht gespeichert. Fotos nur vom Werkstück brauchen keine Einwilligung – dann trotzdem bestätigen.'),
            Toggle::make('published')->label('Auf der Website zeigen')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public')->square()->imageSize(64),
                TextColumn::make('alt')->label('Beschreibung')->wrap()->description(fn (GalleryItem $r) => $r->caption),
                TextColumn::make('course.name')->label('Kurs')->placeholder('–'),
                IconColumn::make('consent')->label('Einwilligung')->boolean(),
                IconColumn::make('published')->label('Sichtbar')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('Noch keine Fotos')
            ->emptyStateDescription('Lade Fotos von Kurstagen und fertigen Messern hoch. Sie erscheinen auf der Seite „Galerie“ – nur mit Einwilligung der abgebildeten Personen.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageGalleryItems::route('/')];
    }
}
