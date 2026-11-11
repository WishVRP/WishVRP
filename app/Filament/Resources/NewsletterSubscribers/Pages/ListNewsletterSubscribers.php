<?php

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    public function getSubheading(): ?string
    {
        return 'Werbe-E-Mails nur an „bestätigt“ (Double-Opt-in). Jede Werbe-E-Mail braucht den persönlichen Abmeldelink – er ist im Export enthalten.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('Bestätigte exportieren (CSV)')->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => response()->streamDownload(function () {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['email', 'bestaetigt_am', 'quelle', 'abmeldelink'], ';');
                    NewsletterSubscriber::query()->active()->orderBy('confirmed_at')->each(fn (NewsletterSubscriber $s) => fputcsv($out, [
                        $s->email, $s->confirmed_at->format('Y-m-d H:i'), $s->source, $s->unsubscribeUrl(),
                    ], ';'));
                    fclose($out);
                }, 'newsletter-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8'])),
        ];
    }
}
