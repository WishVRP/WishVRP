<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Interne Notizen';

    protected static ?string $modelLabel = 'Notiz';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')->label('Notiz')->required()->rows(4)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('Von'),
                TextColumn::make('body')->label('Notiz')->wrap(),
                TextColumn::make('created_at')->label('Wann')->since()->dateTimeTooltip('d.m.Y H:i'),
            ])
            ->headerActions([
                CreateAction::make()->label('Notiz hinzufügen')->mutateDataUsing(fn (array $data) => $data + ['user_id' => auth()->id()]),
            ])
            ->recordActions([DeleteAction::make()]);
    }
}
