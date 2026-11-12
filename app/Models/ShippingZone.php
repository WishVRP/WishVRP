<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'countries', 'rest_of_world', 'customs', 'active', 'sort', 'customs_mode', 'duty_percent', 'vat_percent', 'customs_fee'];

    protected function casts(): array
    {
        return ['countries' => 'array', 'rest_of_world' => 'boolean', 'customs' => 'boolean', 'active' => 'boolean'];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->orderBy('max_weight_g');
    }
}
