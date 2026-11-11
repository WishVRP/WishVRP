<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/** Ein Messer der Edition 100 (Seriennummer). */
class EditionPiece extends Model
{
    public const STATUSES = ['frei' => 'Frei', 'reserviert' => 'Reserviert', 'vergeben' => 'Verkauft'];

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

    /** Öffentlicher Text für das Raster – Name nur mit Zustimmung. */
    public function publicLabel(): string
    {
        $label = 'Nr. '.$this->number.($this->isSpecial() ? ' · Sonderstück' : '');

        return match ($this->status) {
            'vergeben' => $label.' · '.($this->hasPublicName() ? 'gehört '.$this->buyer_name : 'verkauft'),
            'reserviert' => $label.' · reserviert',
            default => $label.' · '.($this->isSpecial() ? 'Sonderausführung auf Anfrage' : 'noch frei'),
        };
    }

    public function hasPublicName(): bool
    {
        return $this->status === 'vergeben' && $this->show_name && filled($this->buyer_name);
    }

    public function isSelectable(): bool
    {
        return $this->status === 'frei' && ! $this->isSpecial();
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

        return collect(range(1, $total))->map(fn ($n) => $pieces[$n] ?? new static(['number' => $n, 'status' => 'frei']));
    }
}
