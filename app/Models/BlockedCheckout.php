<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Gesperrter Bestellversuch (Land/Messerart nicht zulässig). Nach 180 Tagen automatisch gelöscht (Datensparsamkeit). */
class BlockedCheckout extends Model
{
    use MassPrunable;

    protected $guarded = ['id'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ExportRule::class, 'export_rule_id');
    }

    public function prunable()
    {
        return static::query()->where('created_at', '<', now()->subDays(180));
    }
}
