<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Gesperrter Tag im Buchungskalender (Urlaub, Markt, Krankheit …). */
class CalendarBlock extends Model
{
    protected $fillable = ['date', 'reason'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
