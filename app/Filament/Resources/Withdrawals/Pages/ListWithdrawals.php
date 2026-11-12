<?php

namespace App\Filament\Resources\Withdrawals\Pages;

use App\Filament\Resources\Withdrawals\WithdrawalResource;
use Filament\Resources\Pages\ListRecords;

class ListWithdrawals extends ListRecords
{
    protected static string $resource = WithdrawalResource::class;

    public function getSubheading(): ?string
    {
        return 'Über „Vertrag widerrufen“ eingegangene Erklärungen. Der Kunde hat sofort eine Eingangsbestätigung bekommen. Bitte zeitnah prüfen und annehmen oder – nur bei gesetzlicher Ausnahme – begründet ablehnen.';
    }
}
