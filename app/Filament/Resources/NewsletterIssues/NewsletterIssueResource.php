<?php

namespace App\Filament\Resources\NewsletterIssues;

use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
use App\Filament\Resources\NewsletterIssues\RelationManagers\CodesRelationManager;
use App\Filament\Resources\NewsletterIssues\RelationManagers\DeliveriesRelationManager;
use App\Models\NewsletterIssue;
use App\Models\NewsletterSubscriber;
use App\Support\NewsletterSender;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/** Newsletter-Ausgaben: automatische Wochen-Ausgaben und eigene, mit Versandprotokoll und Codes. */
class NewsletterIssueResource extends Resource
{
    protected static ?string $model = NewsletterIssue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Newsletter-Ausgaben';

    protected static ?string $modelLabel = 'Ausgabe';

    protected static ?string $pluralModelLabel = 'Newsletter-Ausgaben';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        $max = (int) config('ulbrich.newsletter.max_percent');

        return $schema->components([
            Section::make('Inhalt')->columnSpanFull()->disabled(fn ($record) => $record?->status === 'versendet')->schema([
                TextInput::make('subject')->label('Betreff')->required()->maxLength(190),
                TextInput::make('preheader')->label('Vorschauzeile (erscheint im Postfach neben dem Betreff)')->maxLength(190),
                MarkdownEditor::make('body')->label('Text')
                    ->helperText('Leer lassen = automatischer Inhalt (Kurstermine, Edition 100, Tipp der Woche). Impressum und Abmeldelink werden immer automatisch angehängt.')
                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'heading', 'undo', 'redo']),
            ]),
            Section::make('Rabattcode und Versand')->columns(3)->columnSpanFull()->disabled(fn ($record) => $record?->status === 'versendet')->schema([
                Select::make('discount_percent')->label('Persönlicher Rabattcode')->options(collect([5, 10])->filter(fn ($p) => $p <= $max)->mapWithKeys(fn ($p) => [$p => "{$p} %"]))->placeholder('kein Code'),
                TextInput::make('discount_valid_days')->label('Code gültig (Tage)')->numeric()->minValue(1)->maxValue(90)->default(30),
                DateTimePicker::make('scheduled_at')->label('Versand am')->seconds(false)->native(false)->displayFormat('d.m.Y H:i')->locale('de')->firstDayOfWeek(1)->helperText('Beim Speichern als „Geplant“ wird zu diesem Zeitpunkt versendet.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('subject')->label('Betreff')->searchable()->wrap()->weight('bold')
                    ->description(fn (NewsletterIssue $r) => $r->kind === 'auto' ? 'automatisch · '.$r->week : 'eigene Ausgabe'),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn ($state) => NewsletterIssue::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => ['versendet' => 'success', 'geplant' => 'info', 'sendet' => 'warning'][$state] ?? 'gray'),
                TextColumn::make('scheduled_at')->label('Geplant')->dateTime('d.m.Y H:i')->placeholder('–')->sortable(),
                TextColumn::make('sent_at')->label('Versendet')->dateTime('d.m.Y H:i')->placeholder('–')->sortable(),
                TextColumn::make('recipients_count')->label('Empfänger')->numeric(),
                TextColumn::make('failed_count')->label('Fehler')->numeric()->color(fn ($state) => $state ? 'danger' : null),
                TextColumn::make('discount_percent')->label('Code')->suffix(' %')->placeholder('–'),
                TextColumn::make('redeemed')->label('Eingelöst')->state(fn (NewsletterIssue $r) => $r->discountCodes()->whereNotNull('redeemed_at')->count())->placeholder('–'),
            ])
            ->filters([
                SelectFilter::make('status')->options(NewsletterIssue::STATUSES),
                SelectFilter::make('kind')->label('Art')->options(['auto' => 'Automatisch', 'manuell' => 'Eigene']),
            ])
            ->recordActions([
                self::previewAction(),
                EditAction::make()->visible(fn (NewsletterIssue $r) => $r->status !== 'versendet'),
            ])
            ->emptyStateHeading('Noch keine Ausgaben')
            ->emptyStateDescription('Die automatische Wochen-Ausgabe entsteht von selbst, sobald es bestätigte Empfänger gibt. Eigene Ausgaben legst du oben an.');
    }

    public static function previewAction(): Action
    {
        return Action::make('vorschau')->label('Vorschau')->icon(Heroicon::OutlinedEye)->color('gray')
            ->url(fn (NewsletterIssue $record) => route('newsletter.preview', $record), shouldOpenInNewTab: true);
    }

    /** @return array<int, Action> */
    public static function sendActions(): array
    {
        return [
            Action::make('testmail')->label('Testmail an mich')->icon(Heroicon::OutlinedBeaker)->color('gray')
                ->action(function (NewsletterIssue $record) {
                    NewsletterSender::sendTest($record, auth()->user()->email);
                    Notification::make()->title('Testmail an '.auth()->user()->email.' geschickt')->success()->send();
                }),
            Action::make('planen')->label('Planen')->icon(Heroicon::OutlinedClock)->color('info')
                ->visible(fn (NewsletterIssue $record) => $record->status === 'entwurf' && $record->scheduled_at)
                ->action(fn (NewsletterIssue $record) => $record->update(['status' => 'geplant'])),
            Action::make('jetzt')->label('Jetzt an alle senden')->icon(Heroicon::OutlinedPaperAirplane)->color('success')
                ->visible(fn (NewsletterIssue $record) => in_array($record->status, ['entwurf', 'geplant'], true))
                ->requiresConfirmation()
                ->modalDescription(fn () => 'Die Ausgabe geht an '.NewsletterSubscriber::query()->active()->count().' bestätigte Empfänger. Das lässt sich nicht rückgängig machen.')
                ->action(function (NewsletterIssue $record) {
                    $record->update(['scheduled_at' => now(), 'status' => 'geplant']);
                    NewsletterSender::send($record->fresh());
                    Notification::make()->title('Versendet an '.$record->fresh()->recipients_count.' Empfänger')->success()->send();
                }),
        ];
    }

    public static function getRelations(): array
    {
        return [DeliveriesRelationManager::class, CodesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterIssues::route('/'),
            'create' => CreateNewsletterIssue::route('/create'),
            'edit' => EditNewsletterIssue::route('/{record}/edit'),
        ];
    }
}
