<?php

namespace App\Filament\Resources\NewsletterIssues\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    protected static ?string $title = 'Versandprotokoll';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('E-Mail')->searchable(),
                TextColumn::make('status')->label('Status')->badge()->color(fn ($state) => $state === 'versendet' ? 'success' : 'danger'),
                TextColumn::make('discountCode.code')->label('Code')->placeholder('–')->copyable(),
                TextColumn::make('sent_at')->label('Zeit')->dateTime('d.m.Y H:i'),
                TextColumn::make('error')->label('Fehler')->placeholder('–')->wrap()->toggleable(),
            ])
            ->filters([SelectFilter::make('status')->options(['versendet' => 'Versendet', 'fehler' => 'Fehler'])]);
    }
}
