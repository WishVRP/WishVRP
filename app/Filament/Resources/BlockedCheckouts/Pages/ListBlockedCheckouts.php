<?php

namespace App\Filament\Resources\BlockedCheckouts\Pages;

use App\Filament\Resources\BlockedCheckouts\BlockedCheckoutResource;
use Filament\Resources\Pages\ListRecords;

class ListBlockedCheckouts extends ListRecords
{
    protected static string $resource = BlockedCheckoutResource::class;

    public function getSubheading(): ?string
    {
        return 'Bestellversuche, die wegen der Einfuhr-Regeln blockiert wurden – mit Grund. Kontaktdaten werden nach 180 Tagen automatisch gelöscht.';
    }
}
