<?php

namespace App\Filament\Resources\CourseDates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseDateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')->label('Kurs')
                    ->relationship('course', 'name')
                    ->required(),
                DatePicker::make('starts_on')->label('Beginn')->required(),
                DatePicker::make('ends_on')->label('Ende (bei mehrtägigen Kursen)')->afterOrEqual('starts_on'),
                TextInput::make('seats_total')->label('Plätze gesamt')->required()->numeric()->minValue(1)->default(6),
                TextInput::make('seats_booked')->label('Plätze vergeben')->required()->numeric()->minValue(0)->default(0)
                    ->helperText('Bestätigte Buchungen aus der Verwaltung zählen automatisch hoch.'),
                Toggle::make('is_published')->label('Freigeschaltet (auf der Website sichtbar)')->default(false),
]);
    }
}
