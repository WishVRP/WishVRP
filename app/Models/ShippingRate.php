<?php

namespace App\Models;

use App\Support\Shipping;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = ['shipping_zone_id', 'max_weight_g', 'dhl_price', 'label'];

    protected function casts(): array
    {
        return ['dhl_price' => 'decimal:2', 'max_weight_g' => 'integer'];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /** Preis für Kundschaft: aufgerundet auf ,99. */
    public function customerPrice(): float
    {
        return Shipping::roundUp((float) $this->dhl_price);
    }
}
