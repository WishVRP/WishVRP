<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsletterIssue extends EditRecord
{
    protected static string $resource = NewsletterIssueResource::class;

    public function getSubheading(): ?string
    {
        return $this->record->status === 'versendet'
            ? 'Versendet am '.$this->record->sent_at?->format('d.m.Y H:i').' an '.$this->record->recipients_count.' Empfänger – Inhalt bleibt zur Dokumentation unverändert.'
            : null;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['discount_percent'] = isset($data['discount_percent']) ? min((int) $data['discount_percent'], (int) config('ulbrich.newsletter.max_percent')) : null;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            NewsletterIssueResource::previewAction(),
            ...NewsletterIssueResource::sendActions(),
            DeleteAction::make()->visible(fn () => $this->record->status !== 'versendet'),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->record->status === 'versendet' ? [] : parent::getFormActions();
    }
}
