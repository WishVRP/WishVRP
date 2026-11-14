<?php

namespace App\Filament\Resources\DiscountCodes;

use App\Filament\Resources\DiscountCodes\Pages\ListDiscountCodes;
use App\Filament\Resources\Orders\OrderResource;
use App\Mail\DiscountCodeMail;
use App\Models\DiscountCode;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

/**
 * Rabattcodes: in der Verwaltung angelegt (Prozent oder Euro, für Gewinnspiele, Aktionen, Kulanz) und aus dem Newsletter.
 * Weihnachtscodes stehen unter „Weihnachtsaktion“.
 */
class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Rabattcodes';

    protected static ?string $modelLabel = 'Rabattcode';

    protected static ?string $pluralModelLabel = 'Rabattcodes';

    protected static ?int $navigationSort = 9;

    /** Felder für Anlegen, Bearbeiten und „Mehrere Codes erzeugen“. */
    public static function valueFields(): array
    {
        return [
            TextInput::make('label')->label('Anlass (nur intern)')->maxLength(120)->placeholder('z. B. Gewinnspiel Instagram Dezember'),
            Radio::make('type')->label('Art des Rabatts')->options(DiscountCode::TYPES)->default('percent')->inline()->live()->required(),
            TextInput::make('percent')->label('Rabatt in %')->numeric()->minValue(1)->maxValue(100)->suffix('%')
                ->visible(fn (Get $get) => $get('type') !== 'amount')->required(fn (Get $get) => $get('type') !== 'amount'),
            TextInput::make('amount')->label('Rabatt in Euro')->numeric()->minValue(0.01)->step(0.01)->suffix('€')
                ->visible(fn (Get $get) => $get('type') === 'amount')->required(fn (Get $get) => $get('type') === 'amount'),
            Select::make('scope')->label('Gilt für')->options(collect(DiscountCode::SCOPES)->map(fn ($s) => $s[0]))->default('alles')->required()->native(false),
            TextInput::make('min_order')->label('Mindestbestellwert (optional)')->numeric()->minValue(0)->step(0.01)->suffix('€'),
            DateTimePicker::make('valid_from')->label('Gültig ab (optional)')->seconds(false)->native(false),
            DateTimePicker::make('valid_until')->label('Gültig bis (leer = unbegrenzt)')->seconds(false)->native(false)->default(now()->addMonths(3)->endOfDay()),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Code')->required()->maxLength(20)->default(fn () => DiscountCode::newCode('ULB'))
                ->unique(ignoreRecord: true)->regex('/^[A-Za-z0-9-]+$/')->helperText('Nur Buchstaben, Ziffern und Bindestrich. Wird in Großbuchstaben gespeichert.'),
            TextInput::make('max_uses')->label('Wie oft einlösbar? (leer = unbegrenzt)')->numeric()->minValue(1)->default(1)
                ->helperText('1 = nur einmal (z. B. Gewinn). Für einen Aktionscode für alle: leer lassen oder z. B. 50.'),
            ...self::valueFields(),
            TextInput::make('email')->label('Nur gültig mit dieser E-Mail (optional)')->email()->maxLength(190)
                ->helperText('Leer lassen, wenn jeder den Code nutzen darf.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Code')->searchable()->copyable()->copyMessage('Code kopiert')->weight('bold'),
                TextColumn::make('value')->label('Rabatt')->state(fn (DiscountCode $record) => $record->valueLabel()),
                TextColumn::make('label')->label('Anlass')->placeholder(fn (DiscountCode $record) => $record->source === 'newsletter' ? 'Newsletter' : '–')->limit(30)->searchable(),
                TextColumn::make('scope')->label('Gilt für')->formatStateUsing(fn (DiscountCode $record) => $record->scopeLabel())->toggleable(),
                TextColumn::make('uses')->label('Eingelöst')->state(fn (DiscountCode $record) => $record->uses.' / '.($record->max_uses ?? '∞')),
                TextColumn::make('email')->label('E-Mail')->searchable()->placeholder('alle')->toggleable(),
                TextColumn::make('valid_until')->label('Gültig bis')->date('d.m.Y')->sortable()->placeholder('unbegrenzt')
                    ->color(fn (DiscountCode $record) => $record->valid_until?->isPast() ? 'gray' : null),
                TextColumn::make('sent_at')->label('Verschickt')->dateTime('d.m.Y')->placeholder('–')->toggleable(),
                TextColumn::make('order.number')->label('Zuletzt in Vorgang')->toggleable()
                    ->url(fn (DiscountCode $record) => $record->order_id ? OrderResource::getUrl('edit', ['record' => $record->order_id]) : null),
            ])
            ->filters([
                Filter::make('gueltig')->label('Noch einlösbar')->query(fn (Builder $query) => $query
                    ->where(fn ($q) => $q->whereNull('max_uses')->orWhereColumn('uses', '<', 'max_uses'))
                    ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))),
                Filter::make('eingeloest')->label('Mindestens einmal eingelöst')->query(fn (Builder $query) => $query->where('uses', '>', 0)),
                SelectFilter::make('source')->label('Herkunft')->options(['verwaltung' => 'Verwaltung', 'newsletter' => 'Newsletter']),
            ])
            ->recordActions([
                Action::make('senden')->label('Per E-Mail senden')->icon(Heroicon::OutlinedEnvelope)
                    ->modalHeading(fn (DiscountCode $record) => 'Code '.$record->code.' verschicken')
                    ->schema([
                        TextInput::make('to')->label('E-Mail der Empfängerin / des Empfängers')->email()->required()->default(fn (DiscountCode $record) => $record->email),
                        TextInput::make('name')->label('Vorname (für die Anrede, optional)')->maxLength(60),
                        Textarea::make('note')->label('Persönliche Nachricht (optional)')->rows(3)->maxLength(800)
                            ->placeholder('z. B. Herzlichen Glückwunsch zu deinem Gewinn bei unserem Gewinnspiel!'),
                    ])
                    ->action(function (DiscountCode $record, array $data) {
                        Mail::to($data['to'])->send(new DiscountCodeMail($record, $data['name'] ?? null, $data['note'] ?? null));
                        $record->update(['sent_at' => now()]);
                        Notification::make()->title('Code an '.$data['to'].' verschickt')->success()->send();
                    }),
                EditAction::make(),
                DeleteAction::make()->visible(fn (DiscountCode $record) => $record->uses === 0),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDiscountCodes::route('/')];
    }
}
