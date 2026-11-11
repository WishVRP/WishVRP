<?php

namespace App\Filament\Resources\ShippingZones\Pages;

use App\Filament\Resources\ShippingZones\ShippingZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingZones extends ManageRecords
{
    protected static string $resource = ShippingZoneResource::class;

    public function getSubheading(): ?string
    {
        return 'DHL-Preise je Ländergruppe und Gewicht eintragen (Preisliste auf dhl.de bzw. euer DHL-Vertrag). Kunden sehen automatisch den auf ,99 aufgerundeten Preis. '
            .'Länder ohne Preis: „Versandkosten auf Anfrage“'.(config('services.dhl_express.key') ? ' bzw. Live-Preis von DHL Express.' : '.')
            .' Gewicht kommt vom Produkt (Feld „Versandgewicht“), sonst '.config('ulbrich.sales.default_parcel_g').' g.';
    }

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Ländergruppe anlegen')];
    }
}
