<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Klick oder Formular-Absendung (Telefon, E-Mail, Buchen …) – ohne personenbezogene Daten. */
class SiteEvent extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    public const NAMES = [
        'telefon' => 'Telefon-Klick',
        'email' => 'E-Mail-Klick',
        'buchen' => 'Klick „Kurs buchen“',
        'gutschein' => 'Klick Gutschein',
        'extern' => 'Klick Instagram/Facebook/extern',
        'buchung' => 'Buchungsanfrage gesendet',
        'anfrage' => 'Anfrage/Bestellung gesendet',
        'spiel' => 'Weihnachtsspiel gespielt',
    ];

    protected $fillable = ['name', 'path', 'target', 'occurred_on'];

    protected function casts(): array
    {
        return ['occurred_on' => 'date'];
    }

    public function prunable()
    {
        return static::where('occurred_on', '<', now()->subMonths(13));
    }
}
