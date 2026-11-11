<?php

namespace App\Filament\Resources\Vouchers\Pages;

use App\Filament\Resources\Vouchers\VoucherResource;
use App\Models\Voucher;
use Filament\Resources\Pages\ListRecords;

class ListVouchers extends ListRecords
{
    protected static string $resource = VoucherResource::class;

    public function getSubheading(): ?string
    {
        return Voucher::query()->where('status', 'bezahlt')->count().' gültig · '.Voucher::query()->where('status', 'eingeloest')->count().' eingelöst · offener Gutscheinwert '
            .number_format((float) Voucher::query()->where('status', 'bezahlt')->sum('value'), 2, ',', '.').' €. Gutscheine werden nach Zahlung automatisch per E-Mail verschickt.';
    }
}
