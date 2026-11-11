<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/** Ein Messer der Edition 100 (Seriennummer). */
class EditionPiece extends Model
{
    public const STATUSES = ['offen' => 'Offen / nicht erfasst', 'reserviert' => 'Reserviert', 'vergeben' => 'Vergeben'];

    protected $fillable = ['number', 'status', 'buyer_name', 'show_name', 'order_id', 'note'];

    protected function casts(): array
    {
        return ['show_name' => 'boolean'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSpecial(): bool
    {
        return $this->number > (int) config('ulbrich.edition.regular');
    }

    /** Öffentlicher Text für das Raster – Name nur mit Zustimmung, „offen“ wird nicht als „frei“ behauptet. */
    public function publicLabel(): string
    {
        $label = 'Nr. '.$this->number.($this->isSpecial() ? ' · Sonderstück' : '');

        return match ($this->status) {
            'vergeben' => $label.' · '.($this->show_name && $this->buyer_name ? 'gehört '.$this->buyer_name : 'vergeben'),
            'reserviert' => $label.' · reserviert',
            default => $label,
        };
    }

    /** @return Collection<int, EditionPiece> nach Nummer, fehlende Nummern werden ergänzt (ohne Speichern) */
    public static function grid(): Collection
    {
        $total = (int) config('ulbrich.edition.total');
        try {
            $pieces = static::query()->orderBy('number')->get()->keyBy('number');
        } catch (\Throwable) {
            $pieces = collect();
        }

        return collect(range(1, $total))->map(fn ($n) => $pieces[$n] ?? new static(['number' => $n, 'status' => 'offen']));
    }
}
