<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    public const STATUSES = ['eingegangen' => 'Eingegangen – prüfen', 'angenommen' => 'Angenommen', 'abgelehnt' => 'Abgelehnt (gesetzliche Ausnahme)'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
