<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Support\CustomerMail;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function statusAction(string $name, string $label, string $status, string $color, Heroicon $icon, array $from): Action
    {
        return Action::make($name)->label($label)->icon($icon)->color($color)
            ->visible(fn (Order $record) => in_array($record->status, $from, true))
            ->requiresConfirmation($status === 'storniert')
            ->action(function (Order $record) use ($status, $label) {
                if ($status === 'bezahlt') {
                    \App\Support\OrderPaid::paid($record, 'ueberweisung');   // Gutscheincode, Lager, Bestätigungsmail
                } else {
                    $record->update(['status' => $status]);
                }
                Notification::make()->title("{$record->number}: {$label}")->success()->send();
            });
    }

    private static function notifyField(string $label = 'Kunde per E-Mail informieren'): Toggle
    {
        return Toggle::make('notify')->label($label)->default(true);
    }

    private static function done(Order $record, string $title, bool $sent): void
    {
        Notification::make()->title("{$record->number}: {$title}")
            ->body($sent ? 'E-Mail an '.$record->email.' gesendet.' : null)->success()->send();
    }

    /** In Arbeit: Fertigungszeit angeben, Kunde bekommt „Wir haben begonnen“. */
    public static function inProgressAction(): Action
    {
        return Action::make('in_arbeit')->label('In Arbeit')->icon(Heroicon::OutlinedWrenchScrewdriver)->color('warning')
            ->visible(fn (Order $record) => $record->type !== 'kurs' && in_array($record->status, ['neu', 'zahlung_offen', 'bezahlt', 'bestaetigt'], true))
            ->fillForm(fn (Order $record) => ['production_time' => $record->production_time ?: 'ca. 3–4 Wochen', 'notify' => true])
            ->schema([
                TextInput::make('production_time')->label('Voraussichtliche Fertigungszeit')->required()->maxLength(80)->placeholder('z. B. ca. 3–4 Wochen'),
                Textarea::make('personal')->label('Persönliche Nachricht (optional, erscheint in der E-Mail)')->rows(3)->maxLength(1000),
                self::notifyField(),
            ])
            ->modalDescription('Status wird „In Arbeit“. Mit Häkchen bekommt der Kunde eine E-Mail: Wir haben begonnen, Fertigung voraussichtlich …')
            ->action(function (Order $record, array $data) {
                $record->update(['status' => 'in_arbeit', 'production_time' => $data['production_time']]);
                $sent = $data['notify'] && CustomerMail::send($record, 'in_arbeit', $data['personal'] ?? null);
                self::done($record, 'In Arbeit', $sent);
            });
    }

    /** Versendet: Versanddienst + Sendungsnummer → Tracking-Mail (bei Abholung: „Abholbereit“). */
    public static function shippedAction(): Action
    {
        return Action::make('versendet')->label('Versendet / abholbereit')->icon(Heroicon::OutlinedTruck)->color('info')
            ->visible(fn (Order $record) => $record->type !== 'kurs' && in_array($record->status, ['bezahlt', 'bestaetigt', 'in_arbeit'], true))
            ->fillForm(fn (Order $record) => ['method' => $record->isPickup() ? 'abholung' : 'versand', 'tracking_carrier' => $record->tracking_carrier ?: 'dhl', 'notify' => true])
            ->schema([
                Select::make('method')->label('Art')->options(['versand' => 'Versand (Paket)', 'abholung' => 'Abholung in der Manufaktur'])->required()->native(false)->live(),
                Select::make('tracking_carrier')->label('Versanddienst')->options(collect(Order::CARRIERS)->map(fn ($c) => $c[0]))->native(false)
                    ->visible(fn (Get $get) => $get('method') === 'versand')->required(fn (Get $get) => $get('method') === 'versand')->live(),
                TextInput::make('tracking_number')->label('Sendungsnummer')->maxLength(60)->regex('/^[A-Za-z0-9 \-]+$/')
                    ->visible(fn (Get $get) => $get('method') === 'versand')->required(fn (Get $get) => $get('method') === 'versand' && $get('tracking_carrier') !== 'sonstige'),
                TextInput::make('tracking_url')->label('Tracking-Link (nur bei anderem Versanddienst)')->url()->maxLength(500)->rule('starts_with:https://')
                    ->visible(fn (Get $get) => $get('method') === 'versand' && $get('tracking_carrier') === 'sonstige'),
                Textarea::make('personal')->label('Persönliche Nachricht (optional)')->rows(2)->maxLength(1000),
                self::notifyField(),
            ])
            ->action(function (Order $record, array $data) {
                $pickup = $data['method'] === 'abholung';
                $record->update([
                    'status' => 'versendet',
                    'shipping_method' => $pickup ? 'abholung' : 'versand',
                    'tracking_carrier' => $pickup ? null : $data['tracking_carrier'],
                    'tracking_number' => $pickup ? null : (preg_replace('/\s+/', '', (string) ($data['tracking_number'] ?? '')) ?: null),
                    'tracking_url' => $pickup ? null : ($data['tracking_url'] ?? null),
                ]);
                $sent = $data['notify'] && CustomerMail::send($record, $pickup ? 'abholbereit' : 'versendet', $data['personal'] ?? null);
                self::done($record, $pickup ? 'Abholbereit' : 'Versendet', $sent);
            });
    }

    /** Zugestellt: Kunde bekommt „Angekommen“ mit Bitte um Google-Bewertung. */
    public static function deliveredAction(): Action
    {
        return Action::make('zugestellt')->label('Zugestellt / abgeholt')->icon(Heroicon::OutlinedHomeModern)->color('success')
            ->visible(fn (Order $record) => $record->status === 'versendet')
            ->fillForm(['notify' => true])
            ->schema([self::notifyField('Kunde bekommt die „Angekommen“-E-Mail (mit Bitte um Google-Bewertung, falls eingewilligt)')])
            ->modalDescription(fn (Order $record) => $record->tracking_carrier === 'dhl' && config('services.dhl.key')
                ? 'Hinweis: DHL-Sendungen werden auch automatisch erkannt, sobald sie zugestellt sind.' : null)
            ->action(function (Order $record, array $data) {
                $record->update(['status' => 'zugestellt']);
                $sent = $data['notify'] && CustomerMail::send($record, 'zugestellt');
                self::done($record, 'Zugestellt', $sent);
            });
    }

    /** Bewertungsanfrage von Hand (z. B. Kurs ohne Datum, Telefonbuchung). */
    public static function reviewAction(): Action
    {
        return Action::make('bewertung')->label('Bitte um Google-Bewertung senden')->icon(Heroicon::OutlinedStar)->color('gray')
            ->visible(fn (Order $record) => $record->review_consent && ! $record->review_requested_at && in_array($record->status, ['bezahlt', 'bestaetigt', 'zugestellt', 'abgeschlossen'], true))
            ->requiresConfirmation()
            ->modalDescription(fn (Order $record) => $record->type === 'kurs'
                ? 'Normalerweise geht diese E-Mail am Abend des letzten Kurstags automatisch raus.'
                : 'Schickt die „Angekommen“-E-Mail mit Link zur Google-Bewertung.')
            ->action(function (Order $record) {
                $sent = CustomerMail::send($record, $record->type === 'kurs' ? 'kurs' : 'zugestellt');
                self::done($record, 'Bewertungsanfrage', $sent);
            });
    }

    public static function actions(): array
    {
        return [
            self::statusAction('zahlung_offen', 'Zahlungsdaten gesendet', 'zahlung_offen', 'info', Heroicon::OutlinedEnvelope, ['neu']),
            self::statusAction('bezahlt', 'Zahlung erhalten', 'bezahlt', 'success', Heroicon::OutlinedBanknotes, ['neu', 'zahlung_offen']),
            self::statusAction('bestaetigt', 'Bestätigen', 'bestaetigt', 'success', Heroicon::OutlinedCheckBadge, ['neu', 'zahlung_offen', 'bezahlt']),
            Action::make('bestaetigung')->label('Eingangsbestätigung senden')->icon(Heroicon::OutlinedEnvelope)->color('gray')
                ->visible(fn (Order $record) => in_array($record->status, ['neu', 'zahlung_offen'], true))
                ->requiresConfirmation()
                ->modalDescription('Schickt die Eingangsbestätigung mit aktuellem Betrag, Versandkosten und Bankdaten (z. B. nachdem du Versandkosten ins Ausland eingetragen hast).')
                ->action(fn (Order $record) => self::done($record, 'Eingangsbestätigung', CustomerMail::send($record, 'eingang'))),
            Action::make('umbuchen')->label('Kurs umbuchen')->icon(Heroicon::OutlinedCalendarDays)->color('gray')
                ->visible(fn (Order $record) => $record->type === 'kurs' && $record->course_start && ! in_array($record->status, ['storniert', 'abgeschlossen'], true))
                ->schema([
                    \Filament\Forms\Components\DatePicker::make('start')->label('Neuer erster Kurstag')->required()->native(false)->displayFormat('d.m.Y'),
                    Toggle::make('count')->label('Als kostenlose Umbuchung zählen')->default(true),
                    self::notifyField(),
                ])
                ->modalDescription(fn (Order $record) => 'Aktuell: '.$record->course_start->format('d.m.Y').' · kostenlose Umbuchungen übrig: '.$record->freeRebookingsLeft().'. Preis bleibt unverändert (Preisbindung). Hier sind auch Umbuchungen über die Regeln hinaus möglich.')
                ->action(function (Order $record, array $data) {
                    $start = \Illuminate\Support\Carbon::parse($data['start'])->startOfDay();
                    if (! $record->course || ! \App\Support\CourseCalendar::available($record->course, $start, $record->id)) {
                        Notification::make()->title('Dieser Tag ist nicht frei')->danger()->send();

                        return;
                    }
                    $old = $record->course_start->format('d.m.Y');
                    $record->forceFill([
                        'original_course_start' => $record->original_course_start ?? $record->course_start,
                        'course_start' => $start, 'course_end' => $start->copy()->addDays(\App\Support\CourseCalendar::days($record->course) - 1),
                        'rebook_count' => $record->rebook_count + ($data['count'] ? 1 : 0), 'rebooked_at' => now(),
                    ])->save();
                    \App\Models\Note::query()->create(['order_id' => $record->id, 'user_id' => auth()->id(), 'body' => 'Umgebucht in der Verwaltung: '.$old.' → '.$start->format('d.m.Y').($data['count'] ? '' : ' (zählt nicht als kostenlose Umbuchung)'), 'done_at' => now()]);
                    $sent = $data['notify'] && CustomerMail::send($record, 'umgebucht');
                    self::done($record, 'Umgebucht', $sent);
                }),
            self::inProgressAction(),
            self::shippedAction(),
            self::deliveredAction(),
            self::reviewAction(),
            self::statusAction('abgeschlossen', 'Abschließen', 'abgeschlossen', 'gray', Heroicon::OutlinedArchiveBox, ['bestaetigt', 'versendet', 'zugestellt']),
            self::statusAction('storniert', 'Stornieren', 'storniert', 'danger', Heroicon::OutlinedXCircle, ['neu', 'zahlung_offen', 'bezahlt', 'bestaetigt']),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('Nr.')->searchable()->sortable()->weight('bold'),
                TextColumn::make('created_at')->label('Eingang')->since()->dateTimeTooltip('d.m.Y H:i')->sortable(),
                TextColumn::make('type')->label('Art')->badge()->formatStateUsing(fn ($state) => Order::TYPES[$state] ?? $state)->color('gray'),
                TextColumn::make('name')->label('Name')->searchable()->description(fn (Order $r) => $r->email),
                TextColumn::make('summary')->label('Was')->state(fn (Order $r) => $r->summary())->wrap(),
                TextColumn::make('amount')->label('Betrag')->money('EUR', locale: 'de')->sortable()
                    ->description(fn (Order $r) => $r->discount_percent ? "−{$r->discount_percent} % ({$r->discount_code})".($r->discountWarnings() ? ' ⚠ prüfen' : '') : null),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('export_note')->label('Einfuhr')->badge()->toggleable()
                    ->state(fn (Order $r) => $r->export_note ? (str_contains($r->export_note, 'Einfuhr prüfen') ? 'prüfen' : (str_contains($r->export_note, 'Gesperrt') ? 'gesperrt' : 'Zollpapiere')) : null)
                    ->color(fn ($state) => ['prüfen' => 'warning', 'gesperrt' => 'danger', 'Zollpapiere' => 'info'][$state] ?? 'gray')
                    ->tooltip(fn (Order $r) => $r->export_note),
                TextColumn::make('assignee.name')->label('Zuständig')->toggleable(),
                TextColumn::make('notes_count')->label('Notizen')->counts('notes')->toggleable(),
            ])
            ->filters([
                Filter::make('offen')->label('Nur offene')->query(fn (Builder $query) => $query->whereIn('status', Order::OPEN))->default(),
                SelectFilter::make('type')->label('Art')->options(Order::TYPES),
                Filter::make('einfuhr')->label('Einfuhr prüfen')->query(fn (Builder $query) => $query->where('export_note', 'like', '%Einfuhr prüfen%')),
                SelectFilter::make('status')->label('Status')->options(Order::STATUSES),
            ])
            ->recordActions([
                ActionGroup::make(self::actions())->label('Status')->icon(Heroicon::OutlinedArrowPath)->button(),
                EditAction::make()->label('Öffnen'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('Noch keine Vorgänge')
            ->emptyStateDescription('Buchungen und Anfragen aus der Website erscheinen hier automatisch.');
    }
}
