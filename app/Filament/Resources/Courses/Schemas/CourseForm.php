<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Kursname')->required()->maxLength(120),
                Select::make('category')->label('Kategorie (Kurs-Finder)')->options(Course::CATEGORIES),
                TextInput::make('path')
                    ->label('URL-Pfad der Kursseite')
                    ->helperText('Bestehenden Pfad der Live-Seite 1:1 übernehmen, z. B. /schmiedekurse/grundlagenkurs/')
                    ->regex('#^/schmiedekurse/[a-z0-9-]+/$#')
                    ->unique(ignoreRecord: true),
                TextInput::make('booking_key')->label('Buchungsschlüssel (?kurs=)')->maxLength(60),
                TextInput::make('duration_label')->label('Dauer (Anzeige)')->placeholder('1 Tag')->maxLength(40),
                TextInput::make('total_length_cm')->label('Max. Gesamtlänge in cm (inkl. Griff)')->numeric()->minValue(1)->maxValue(99),
                TextInput::make('price_from')
                    ->label('„ab“-Preis pro Person in €')
                    ->helperText('Leer lassen = „auf Anfrage“')
                    ->numeric()->minValue(0),
                Textarea::make('summary')->label('Kurzbeschreibung')->columnSpanFull(),
                TextInput::make('sort')->label('Reihenfolge')->required()->numeric()->default(0),
                Toggle::make('is_published')->label('Veröffentlicht')->default(true),
            ]);
    }
}
