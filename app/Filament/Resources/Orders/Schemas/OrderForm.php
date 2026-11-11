<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Course;
use App\Models\CourseDate;
use App\Models\Order;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vorgang')->columns(3)->schema([
                    Select::make('type')->label('Art')->options(Order::TYPES)->required()->default('anfrage')->live(),
                    Select::make('status')->label('Status')->options(Order::STATUSES)->required()->default('neu'),
                    Select::make('assigned_to')->label('Zuständig')->options(fn () => User::query()->pluck('name', 'id'))->placeholder('niemand'),
                ]),
                Section::make('Kundin / Kunde')->columns(2)->schema([
                    TextInput::make('name')->label('Name')->required()->maxLength(120),
                    TextInput::make('email')->label('E-Mail')->email()->required()->maxLength(190),
                    TextInput::make('phone')->label('Telefon')->tel()->maxLength(40),
                    TextInput::make('street')->label('Straße')->maxLength(160),
                    TextInput::make('postal_code')->label('PLZ')->maxLength(20),
                    TextInput::make('city')->label('Ort')->maxLength(120),
                    TextInput::make('country')->label('Land')->maxLength(80),
                ]),
                Section::make('Kurs')->columns(3)->visible(fn ($get) => $get('type') === 'kurs')->schema([
                    Select::make('course_id')->label('Kurs')->options(fn () => Course::query()->orderBy('sort')->pluck('name', 'id'))->live(),
                    Select::make('course_date_id')->label('Termin')
                        ->options(fn ($get) => CourseDate::query()->when($get('course_id'), fn ($q, $id) => $q->where('course_id', $id))
                            ->orderBy('starts_on')->get()->mapWithKeys(fn ($d) => [$d->id => $d->starts_on->format('d.m.Y').' · '.$d->seatsLeftLabel()])),
                    TextInput::make('participants')->label('Teilnehmer')->numeric()->minValue(1)->maxValue(50),
                    TextInput::make('preferred_date')->label('Wunschtermin')->maxLength(120)->columnSpanFull(),
                ]),
                Section::make('Artikel & Betrag')->columns(3)->schema([
                    TextInput::make('item')->label('Artikel / Leistung')->maxLength(190),
                    TextInput::make('quantity')->label('Menge')->numeric()->minValue(1)->default(1),
                    TextInput::make('amount')->label('Betrag (€, ohne Versand, nach Rabatt)')->numeric()->step(0.01),
                    TextInput::make('discount_code')->label('Rabattcode')->maxLength(20)->disabled(),
                    TextInput::make('discount_percent')->label('Rabatt %')->numeric()->disabled(),
                    TextInput::make('public_name')->label('Anzeigename im Edition-Raster (mit Zustimmung)')->maxLength(80),
                    Textarea::make('message')->label('Nachricht der Kundschaft')->rows(4)->columnSpanFull(),
                ]),
                Section::make('Verlauf')->columns(4)->collapsed()->schema([
                    DateTimePicker::make('paid_at')->label('Bezahlt am'),
                    DateTimePicker::make('confirmed_at')->label('Bestätigt am'),
                    DateTimePicker::make('shipped_at')->label('Versendet am'),
                    DateTimePicker::make('cancelled_at')->label('Storniert am'),
                ]),
            ]);
    }
}
