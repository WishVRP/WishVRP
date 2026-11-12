<?php

namespace App\Filament\Resources\ExportRules;

use App\Filament\Resources\ExportRules\Pages\ManageExportRules;
use App\Models\ExportRule;
use App\Support\Shipping;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Einfuhr-Regeln: Darf Messerart X nach Land Y? Einträge nur nach rechtlicher Prüfung (Anwalt, Zoll, Botschaft). */
class ExportRuleResource extends Resource
{
    protected static ?string $model = ExportRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Einfuhr-Regeln';

    protected static ?string $modelLabel = 'Einfuhr-Regel';

    protected static ?string $pluralModelLabel = 'Einfuhr-Regeln';

    protected static ?int $navigationSort = 10;

    public static function typeOptions(): array
    {
        return ['*' => 'Alle Messerarten'] + collect(config('ulbrich.export.knife_types'))->map(fn ($t) => $t['label'])->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('country')->label('Zielland')->options(['*' => 'Alle Länder'] + Shipping::countries())->searchable()->required()->default('*'),
            Select::make('knife_type')->label('Messerart')->options(self::typeOptions())->required()->default('*'),
            Select::make('action')->label('Entscheidung')->options(ExportRule::ACTIONS)->required()->native(false)
                ->helperText('Gesperrt: Bestellung nicht möglich (Versuch wird protokolliert). Prüfen: Bestellung kommt als Anfrage ohne Zahlung.'),
            TextInput::make('message')->label('Text für Kunden (optional)')->maxLength(300)
                ->placeholder(config('ulbrich.export.messages.gesperrt')),
            Textarea::make('reason')->label('Interner Grund')->rows(2)->maxLength(500)->required()
                ->placeholder('z. B. Einfuhrverbot für Einhandmesser laut Auskunft Zoll'),
            TextInput::make('source')->label('Quelle')->maxLength(300)->placeholder('z. B. Anwalt, Zoll-Auskunft, Gesetz mit Fundstelle'),
            DatePicker::make('checked_on')->label('Geprüft am')->native(false)->displayFormat('d.m.Y'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('country')
            ->columns([
                TextColumn::make('country')->label('Land')->formatStateUsing(fn ($state) => $state === '*' ? 'Alle Länder' : Shipping::countryName($state))->searchable()->sortable(),
                TextColumn::make('knife_type')->label('Messerart')->formatStateUsing(fn ($state) => self::typeOptions()[$state] ?? $state),
                TextColumn::make('action')->label('Entscheidung')->badge()
                    ->formatStateUsing(fn ($state) => ExportRule::ACTIONS[$state] ?? $state)
                    ->color(fn ($state) => ['erlaubt' => 'success', 'pruefen' => 'warning', 'gesperrt' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('reason')->label('Grund')->limit(60)->wrap(),
                TextColumn::make('checked_on')->label('Geprüft')->date('d.m.Y')->placeholder('–'),
            ])
            ->filters([SelectFilter::make('action')->label('Entscheidung')->options(ExportRule::ACTIONS)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageExportRules::route('/')];
    }
}
