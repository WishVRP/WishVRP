<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterIssue extends CreateRecord
{
    protected static string $resource = NewsletterIssueResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['discount_percent'] = isset($data['discount_percent']) ? min((int) $data['discount_percent'], (int) config('ulbrich.newsletter.max_percent')) : null;
        if (blank($data['body'] ?? null)) {
            $data['blocks'] = \App\Support\NewsletterContent::weekly($data['discount_percent']);
        }

        return $data + ['kind' => 'manuell', 'status' => 'entwurf', 'created_by' => auth()->id()];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
