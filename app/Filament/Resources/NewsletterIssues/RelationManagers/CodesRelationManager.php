<?php

namespace App\Filament\Resources\NewsletterIssues\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\DiscountCode;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CodesRelationManager extends RelationManager
{
    protected static string $relationship = 'discountCodes';

    protected static ?string $title = 'Rabattcodes dieser Ausgabe';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->copyable()->weight('bold'),
                TextColumn::make('email')->label('E-Mail')->searchable(),
                TextColumn::make('percent')->label('Rabatt')->suffix(' %'),
                TextColumn::make('valid_until')->label('Gültig bis')->date('d.m.Y'),
                TextColumn::make('redeemed_at')->label('Eingelöst')->dateTime('d.m.Y H:i')->placeholder('nein'),
                TextColumn::make('order.number')->label('Vorgang')
                    ->url(fn (DiscountCode $record) => $record->order_id ? OrderResource::getUrl('edit', ['record' => $record->order_id]) : null),
            ]);
    }
}
