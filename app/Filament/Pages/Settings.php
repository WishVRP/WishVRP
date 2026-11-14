<?php

namespace App\Filament\Pages;

use App\Support\RuntimeSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Einstellungen ohne FTP: E-Mail-Versand (SMTP), Bankverbindung, Stripe.
 * Werte haben Vorrang vor der .env; Passwörter/Schlüssel werden verschlüsselt gespeichert und nie wieder angezeigt.
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Einstellungen';

    protected static ?string $title = 'Einstellungen: E-Mail, Bank, Zahlung';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'einstellungen';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $s = RuntimeSettings::all();
        foreach (RuntimeSettings::SECRETS as $k) {
            unset($s[$k]);   // Geheimnisse nie ins Formular laden
        }
        $this->form->fill($s + [
            // Vorschläge: .env-Werte, sonst IONOS (Laravel-Standardwerte wie 127.0.0.1:2525 / hello@example.com ignorieren)
            'mail_host' => in_array(config('mail.mailers.smtp.host'), [null, '', '127.0.0.1', 'localhost'], true) ? 'smtp.ionos.de' : config('mail.mailers.smtp.host'),
            'mail_port' => in_array((int) config('mail.mailers.smtp.port'), [0, 2525, 1025], true) ? 587 : (int) config('mail.mailers.smtp.port'),
            'mail_scheme' => config('mail.mailers.smtp.scheme') ?: 'smtp',
            'mail_username' => config('mail.mailers.smtp.username') ?: config('ulbrich.email'),
            'mail_from_address' => str_ends_with((string) config('mail.from.address'), 'example.com') || blank(config('mail.from.address')) ? config('ulbrich.email') : config('mail.from.address'),
            'mail_from_name' => config('mail.from.name') ?: config('ulbrich.name'),
            'booking_opens_on' => config('ulbrich.booking.opens_on'),
            'bank_holder' => config('ulbrich.bank.holder'), 'bank_iban' => config('ulbrich.bank.iban'),
            'bank_bic' => config('ulbrich.bank.bic'), 'bank_name' => config('ulbrich.bank.name'),
        ]);
    }

    private static function stored(string $secret): string
    {
        return RuntimeSettings::has($secret) ? 'Gespeichert ✓ – leer lassen, um es zu behalten' : 'Noch nicht hinterlegt';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Kursbuchung')->description('Ab welchem Tag Kurse im Kalender buchbar sind. Davor ist kein Tag wählbar; gebucht werden kann trotzdem schon jetzt.')
                ->columns(2)->schema([
                    \Filament\Forms\Components\DatePicker::make('booking_opens_on')->label('Erster buchbarer Kurstag')->native(false)->displayFormat('d.m.Y')
                        ->helperText('Leer = ab übermorgen. Aktuell: '.\App\Support\CourseCalendar::first()->format('d.m.Y')),
                ]),
            Section::make('E-Mail-Versand')->description('Damit Bestellbestätigungen, Rechnungen, Gutscheine und Erinnerungen verschickt werden. Bei IONOS: Postfach info@ mit dessen Passwort.')
                ->columns(2)->schema([
                    TextInput::make('mail_host')->label('SMTP-Server')->required()->maxLength(120)->placeholder('smtp.ionos.de'),
                    TextInput::make('mail_port')->label('Port')->numeric()->required()->default(587)->helperText('587 (Standard) oder 465'),
                    Select::make('mail_scheme')->label('Verschlüsselung')->options(['smtp' => 'STARTTLS (Port 587)', 'smtps' => 'SSL/TLS (Port 465)'])->default('smtp')->required()->native(false),
                    TextInput::make('mail_username')->label('Benutzername (komplette E-Mail-Adresse)')->required()->maxLength(190),
                    TextInput::make('mail_password')->label('Passwort des Postfachs')->password()->revealable()->maxLength(190)->autocomplete('new-password')
                        ->helperText(fn () => self::stored('mail_password')),
                    TextInput::make('mail_from_address')->label('Absender-Adresse')->email()->required()->maxLength(190),
                    TextInput::make('mail_from_name')->label('Absender-Name')->required()->maxLength(120),
                ]),
            Section::make('Bankverbindung')->description('Steht in der Bestellbestätigung (Überweisung), auf der Rechnung und bei Markt-Bewerbungen.')
                ->columns(2)->schema([
                    TextInput::make('bank_holder')->label('Kontoinhaber')->maxLength(120),
                    TextInput::make('bank_iban')->label('IBAN')->maxLength(40)
                        ->rule(fn () => function (string $attr, $value, \Closure $fail) {
                            $iban = strtoupper(preg_replace('/\s+/', '', (string) $value));
                            if ($iban !== '' && ! self::validIban($iban)) {
                                $fail('Diese IBAN ist nicht gültig – bitte prüfen.');
                            }
                        }),
                    TextInput::make('bank_bic')->label('BIC (optional)')->maxLength(20),
                    TextInput::make('bank_name')->label('Bank (optional)')->maxLength(120),
                ]),
            Section::make('Online-Zahlung (Stripe)')->description('Schlüssel aus dem Stripe-Dashboard (Entwickler → API-Schlüssel bzw. Webhooks). Sie werden verschlüsselt gespeichert.')
                ->columns(2)->schema([
                    TextInput::make('stripe_secret')->label('Geheimer Schlüssel (sk_live_… oder sk_test_…)')->password()->revealable()->maxLength(255)->autocomplete('off')
                        ->rule('nullable')->regex('/^(sk|rk)_(live|test)_[A-Za-z0-9]+$/')->helperText(fn () => self::stored('stripe_secret')),
                    TextInput::make('stripe_webhook_secret')->label('Webhook-Signaturschlüssel (whsec_…)')->password()->revealable()->maxLength(255)->autocomplete('off')
                        ->rule('nullable')->regex('/^whsec_[A-Za-z0-9]+$/')->helperText(fn () => self::stored('stripe_webhook_secret')),
                    Placeholder::make('stripe_webhook_url')->label('Webhook-Adresse für Stripe')
                        ->content(new HtmlString('<code>'.e(rtrim(config('app.url'), '/').'/stripe/webhook/').'</code><br>Ereignisse: checkout.session.completed, checkout.session.async_payment_succeeded, checkout.session.async_payment_failed, checkout.session.expired')),
                    Toggle::make('stripe_enabled')->label('Online-Zahlung im Shop aktiv')->disabled()->default(false)
                        ->helperText('Folgt mit dem Stripe-Update für Warenkorb und Kasse. Bis dahin wird per Überweisung bezahlt; die Schlüssel kannst du schon hinterlegen.'),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])->id('form')->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Speichern')->submit('save')])]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['bank_iban'] = filled($data['bank_iban'] ?? null) ? trim(chunk_split(strtoupper(preg_replace('/\s+/', '', $data['bank_iban'])), 4, ' ')) : null;
        $data['stripe_enabled'] = false;
        RuntimeSettings::save($data);
        foreach (RuntimeSettings::SECRETS as $k) {
            $this->data[$k] = null;
        }
        Notification::make()->title('Gespeichert')->body('Die Einstellungen gelten ab sofort. Tipp: oben „Test-E-Mail senden“.')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testmail')->label('Test-E-Mail senden')->icon(Heroicon::OutlinedEnvelope)->color('gray')
                ->modalDescription('Verschickt eine Test-E-Mail mit den gespeicherten Einstellungen. Bei einem Fehler steht hier der genaue Grund.')
                ->schema([TextInput::make('to')->label('An diese Adresse')->email()->required()->default(fn () => auth()->user()?->email)])
                ->action(function (array $data) {
                    try {
                        Mail::raw('Test von der Website '.config('ulbrich.name').".\nWenn du diese E-Mail liest, funktioniert der Versand.\n\nGesendet: ".now()->format('d.m.Y H:i'),
                            fn ($m) => $m->to($data['to'])->subject('Test-E-Mail: Versand funktioniert'));
                        Notification::make()->title('Test-E-Mail an '.$data['to'].' verschickt')->body('Bitte auch im Spam-Ordner nachsehen.')->success()->send();
                    } catch (\Throwable $e) {
                        report($e);
                        $pw = (string) config('mail.mailers.smtp.password');
                        $source = RuntimeSettings::has('mail_password') ? 'aus diesen Einstellungen' : 'aus der .env';
                        Notification::make()->title('Versand fehlgeschlagen')
                            ->body(Str::limit($e->getMessage(), 260)."\n\nVerwendet: ".config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port').', Benutzer '.config('mail.mailers.smtp.username')
                                .', Passwort '.$source.' ('.mb_strlen($pw).' Zeichen'.($pw !== trim($pw) ? ', mit Leerzeichen am Rand!' : '').').')
                            ->danger()->persistent()->send();
                    }
                }),
        ];
    }

    public static function validIban(string $iban): bool
    {
        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{10,30}$/', $iban)) {
            return false;
        }
        $num = '';
        foreach (str_split(substr($iban, 4).substr($iban, 0, 4)) as $ch) {
            $num .= ctype_alpha($ch) ? (string) (ord($ch) - 55) : $ch;
        }
        $mod = 0;
        foreach (str_split($num, 7) as $part) {
            $mod = (int) (($mod.$part) % 97);
        }

        return $mod === 1;
    }
}
