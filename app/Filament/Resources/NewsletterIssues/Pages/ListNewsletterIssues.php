<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsletterIssues extends ListRecords
{
    protected static string $resource = NewsletterIssueResource::class;

    public function getSubheading(): ?string
    {
        $n = config('ulbrich.newsletter');
        $day = ['', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'][$n['weekday']] ?? '';

        return ($n['auto_weekly'] ? "Automatisch jeden {$day} um {$n['time']} Uhr, alle {$n['discount_every_days']} Tage mit persönlichem {$n['discount_percent']}-%-Code." : 'Automatische Wochen-Ausgabe ist ausgeschaltet.')
            .' Versand nur an '.NewsletterSubscriber::query()->active()->count().' bestätigte Empfänger. Öffnungen und Klicks werden bewusst nicht gemessen (Datenschutz).';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Eigene Ausgabe schreiben')];
    }
}
