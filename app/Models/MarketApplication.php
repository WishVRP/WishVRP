<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MarketApplication extends Model
{
    public const STATUSES = [
        'eingegangen' => 'Eingegangen (prüfen)',
        'angenommen' => 'Angenommen – Zahlung offen',
        'bezahlt' => 'Bezahlt – Stand fest',
        'abgelehnt' => 'Abgelehnt',
        'storniert' => 'Storniert',
    ];

    /** Diese Status belegen bzw. reservieren den Stand. */
    public const BLOCKING = ['eingegangen', 'angenommen', 'bezahlt'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['photos' => 'array', 'lighting' => 'boolean', 'food' => 'boolean', 'demo' => 'boolean', 'tables' => 'integer', 'amount' => 'decimal:2', 'accepted_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public static function newCode(): string
    {
        do {
            $code = 'KHM-'.Str::upper(Str::random(6));
        } while (static::query()->where('code', $code)->exists());

        return strtr($code, ['0' => '7', 'O' => 'Q', 'I' => 'K', 'L' => 'X', '1' => '4']);
    }
}
