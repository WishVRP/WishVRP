<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportRule extends Model
{
    public const ACTIONS = ['erlaubt' => 'Erlaubt', 'pruefen' => 'Prüfen (Anfrage ohne Zahlung)', 'gesperrt' => 'Gesperrt'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['checked_on' => 'date'];
    }
}
