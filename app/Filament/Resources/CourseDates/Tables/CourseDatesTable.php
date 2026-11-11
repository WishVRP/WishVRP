<?php

namespace App\Filament\Resources\CourseDates\Tables;

use App\Models\CourseDate;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CourseDatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on')
            ->columns([
                TextColumn::make('course.name')->label('Kurs')->searchable()->weight('bold'),
                TextColumn::make('starts_on')->label('Beginn')->date('D, d.m.Y')->sortable(),
                TextColumn::make('ends_on')->label('Ende')->date('d.m.Y')->placeholder('–'),
                TextColumn::make('belegung')->label('Belegung')
                    ->state(fn (CourseDate $r) => "{$r->seats_booked} / {$r->seats_total}")
                    ->badge()
                    ->color(fn (CourseDate $r) => $r->seatsLeft() === 0 ? 'danger' : ($r->seats_booked / max(1, $r->seats_total) >= 0.75 ? 'warning' : 'success'))
                    ->description(fn (CourseDate $r) => $r->seatsLeft() === 0 ? 'ausgebucht' : $r->seatsLeftLabel()),
                ToggleColumn::make('is_published')->label('Freigeschaltet'),
            ])
            ->filters([
                Filter::make('kommend')->label('Nur kommende')->query(fn (Builder $query) => $query->whereDate('starts_on', '>=', now()->toDateString()))->default(),
                SelectFilter::make('course_id')->label('Kurs')->relationship('course', 'name'),
            ])
            ->recordActions([
                ReplicateAction::make()->label('Kopieren')->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->schema([
                        DatePicker::make('starts_on')->label('Neuer Beginn')->required(),
                        DatePicker::make('ends_on')->label('Neues Ende'),
                    ])
                    ->mutateRecordDataUsing(fn (array $data) => [...$data, 'seats_booked' => 0, 'is_published' => false])
                    ->beforeReplicaSaved(function (CourseDate $replica, array $data) {
                        $replica->fill(['starts_on' => $data['starts_on'], 'ends_on' => $data['ends_on'] ?? null, 'seats_booked' => 0, 'is_published' => false]);
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('freischalten')->label('Freischalten')->icon(Heroicon::OutlinedEye)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => true])),
                    BulkAction::make('verbergen')->label('Verbergen')->icon(Heroicon::OutlinedEyeSlash)
                        ->action(fn (Collection $records) => $records->each->update(['is_published' => false])),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Noch keine Kurstermine')
            ->emptyStateDescription('Lege einen Termin an und schalte ihn frei – er erscheint sofort auf der Website und in Google.');
    }
}
