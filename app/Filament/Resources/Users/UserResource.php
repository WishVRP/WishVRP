<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** Zugänge zur Verwaltung (ohne SSH anlegbar). Jedes Konto richtet beim ersten Login die 2-Faktor-Anmeldung ein. */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Zugänge';

    protected static ?string $modelLabel = 'Zugang';

    protected static ?string $pluralModelLabel = 'Zugänge';

    protected static ?int $navigationSort = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Name')->required()->maxLength(120),
            TextInput::make('email')->label('E-Mail (Login)')->email()->required()->maxLength(190)->unique(ignoreRecord: true),
            TextInput::make('password')->label('Passwort (mind. 12 Zeichen)')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->minLength(12)->maxLength(200)
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leer lassen = Passwort bleibt.' : 'Beim ersten Login richtet die Person die Authenticator-App ein.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name'),
                TextColumn::make('email')->label('E-Mail'),
                IconColumn::make('app_authentication_secret')->label('2FA eingerichtet')->boolean()
                    ->state(fn (User $record) => filled($record->app_authentication_secret)),
                TextColumn::make('created_at')->label('Angelegt')->date('d.m.Y'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $record) => $record->is(auth()->user()))   // sich selbst nicht löschen
                    ->disabled(fn () => User::query()->count() <= 1),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
