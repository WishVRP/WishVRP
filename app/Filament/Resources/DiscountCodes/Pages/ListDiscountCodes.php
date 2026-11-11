<?php

namespace App\Filament\Resources\DiscountCodes\Pages;

use App\Filament\Resources\DiscountCodes\DiscountCodeResource;
use App\Models\DiscountCode;
use Filament\Resources\Pages\ListRecords;

class ListDiscountCodes extends ListRecords
{
    protected static string $resource = DiscountCodeResource::class;

    public function getSubheading(): ?string
    {
        return DiscountCode::query()->count().' Codes · '.DiscountCode::query()->whereNotNull('redeemed_at')->count().' eingelöst. Jeder Code ist persönlich (nur mit der Empfänger-E-Mail), einmalig und befristet.';
    }
}
