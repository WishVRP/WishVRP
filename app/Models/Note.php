<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Interne Notiz – für alle Konten der Verwaltung sichtbar (Pinnwand). */
class Note extends Model
{
    protected $fillable = ['user_id', 'body', 'order_id', 'is_pinned', 'done_at'];

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean', 'done_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('done_at');
    }
}
