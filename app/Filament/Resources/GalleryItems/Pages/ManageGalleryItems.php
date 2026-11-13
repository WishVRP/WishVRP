<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGalleryItems extends ManageRecords
{
    protected static string $resource = GalleryItemResource::class;

    public function getSubheading(): ?string
    {
        return 'Fotos erscheinen auf messermanufaktur-ulbrich.de/galerie/. Ab 6 sichtbaren Fotos wird die Seite auch für Google freigegeben.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Foto hinzufügen')];
    }
}
