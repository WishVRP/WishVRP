<?php

namespace App\Filament\Resources\EditionPieces\Pages;

use App\Filament\Resources\EditionPieces\EditionPieceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageEditionPieces extends ManageRecords
{
    protected static string $resource = EditionPieceResource::class;

    public function getSubheading(): ?string
    {
        return 'Status und Anzeigename je Seriennummer. Auf der Website erscheint beim Antippen eines Feldes „Nr. 17 · gehört Erika M.“ – der Name nur, wenn „Name öffentlich“ an ist.';
    }
}
