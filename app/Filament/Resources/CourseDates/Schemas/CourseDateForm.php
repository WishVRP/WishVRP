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
                Select::make('course_id')
                    ->relationship('course', 'name')
                    ->required(),
                DatePicker::make('starts_on')
                    ->required(),
                DatePicker::make('ends_on'),
                TextInput::make('seats_total')
                    ->required()
                    ->numeric(),
                TextInput::make('seats_booked')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->required(),
            ]);
    }
}
