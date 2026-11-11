<?php

namespace App\Filament\Resources\EditionPieces\Pages;

use App\Filament\Resources\EditionPieces\EditionPieceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageEditionPieces extends ManageRecords
{
    protected static string $resource = EditionPieceResource::class;

    public function getSubheading(): ?string
    {
        return 'Rot = verkauft, Grün = verkauft mit freigegebenem Namen, schraffiert = reserviert, dunkel = frei (auf der Website wählbar). Bestellungen reservieren ihre Nummer automatisch; bei „Zahlung erhalten“/„Bestätigt“ wird sie verkauft und – mit Zustimmung – grün mit Namen; bei „Storniert“ wieder frei.';
    }
}
