<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Google-Bewertungen: wörtlich aus dem Google-Profil übernehmen, erscheinen auf Startseite und /schmiedekurse/erfahrungen/. */
class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'Bewertungen';

    protected static ?string $modelLabel = 'Bewertung';

    protected static ?string $pluralModelLabel = 'Bewertungen';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('author')->label('Name (wie bei Google angezeigt, z. B. „Jonas K.“)')->required()->maxLength(80),
            Select::make('rating')->label('Sterne')->options([5 => '★★★★★ (5)', 4 => '★★★★ (4)', 3 => '★★★ (3)', 2 => '★★ (2)', 1 => '★ (1)'])->default(5)->required()->native(false),
            Textarea::make('text')->label('Bewertungstext – wörtlich kopieren, nichts umformulieren')->required()->rows(6)->maxLength(3000)->columnSpanFull(),
            DatePicker::make('reviewed_on')->label('Datum der Bewertung (ungefähr reicht, z. B. 1. des Monats)')->required()->native(false)->displayFormat('d.m.Y')->maxDate(now()),
            Select::make('source')->label('Quelle')->options(['Google' => 'Google', 'Instagram' => 'Instagram', 'E-Mail' => 'E-Mail (mit Zustimmung)'])->default('Google')->required()->native(false),
            TextInput::make('topic')->label('Thema (optional, z. B. Damastkurs, Klappmesser)')->maxLength(80),
            TextInput::make('sort')->label('Reihenfolge (kleiner = weiter vorne)')->numeric()->default(0),
            Toggle::make('published')->label('Auf der Website zeigen')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('author')->label('Name')->searchable(),
                TextColumn::make('rating')->label('Sterne')->formatStateUsing(fn (int $state) => str_repeat('★', $state)),
                TextColumn::make('text')->label('Text')->limit(80)->wrap(),
                TextColumn::make('reviewed_on')->label('Datum')->date('m/Y')->sortable(),
                TextColumn::make('source')->label('Quelle'),
                IconColumn::make('published')->label('Sichtbar')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('Noch keine Bewertungen')
            ->emptyStateDescription('Öffne das Google-Profil, kopiere die Bewertungen wörtlich hierher (Name, Sterne, Text, Monat). Oben rechts trägst du außerdem die Google-Gesamtbewertung und den Link zum Profil ein.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageReviews::route('/')];
    }
}
