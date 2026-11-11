<?php

namespace App\Filament\Resources\XmasEntries\Pages;

use App\Filament\Resources\XmasEntries\XmasEntryResource;
use App\Models\XmasEntry;
use Filament\Resources\Pages\ListRecords;

class ListXmasEntries extends ListRecords
{
    protected static string $resource = XmasEntryResource::class;

    public function getSubheading(): ?string
    {
        $q = XmasEntry::query();

        return sprintf('%d Teilnahmen · %d Codes (5 %%: %d, 10 %%: %d) · %d eingelöst. Jede E-Mail-Adresse kann nur einmal spielen; Codes sind einmalig.',
            (clone $q)->count(), (clone $q)->whereNotNull('code')->count(),
            (clone $q)->where('percent', 5)->count(), (clone $q)->where('percent', 10)->count(),
            (clone $q)->whereNotNull('redeemed_at')->count());
    }
}
