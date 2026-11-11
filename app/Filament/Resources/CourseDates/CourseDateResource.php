<?php

namespace App\Filament\Resources\CourseDates;

use App\Filament\Resources\CourseDates\Pages\CreateCourseDate;
use App\Filament\Resources\CourseDates\Pages\EditCourseDate;
use App\Filament\Resources\CourseDates\Pages\ListCourseDates;
use App\Filament\Resources\CourseDates\Schemas\CourseDateForm;
use App\Filament\Resources\CourseDates\Tables\CourseDatesTable;
use App\Models\CourseDate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseDateResource extends Resource
{
    protected static ?string $model = CourseDate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Kurstermine';

    protected static ?string $modelLabel = 'Kurstermin';

    protected static ?string $pluralModelLabel = 'Kurstermine';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CourseDateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseDatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseDates::route('/'),
            'create' => CreateCourseDate::route('/create'),
            'edit' => EditCourseDate::route('/{record}/edit'),
        ];
    }
}
