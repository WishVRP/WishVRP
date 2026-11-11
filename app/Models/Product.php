<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Shop-Produkt (in der Verwaltung gepflegt). */
class Product extends Model
{
    /** Kategorie => [Name, Shop-Pfad] (bestehende Kategorieseiten). */
    public const CATEGORIES = [
        'damast' => ['Damastmesser', '/shop/damastmesser/'],
        'kueche' => ['Küchenmesser', '/shop/kuechenmesser/'],
        'jagd' => ['Jagdmesser', '/shop/jagdmesser/'],
        'einzel' => ['Einzelstücke', '/shop/einzelstuecke/'],
        'ringe' => ['Ringe', '/shop/ringe/'],
    ];

    public const STATUSES = ['entwurf' => 'Entwurf (unsichtbar)', 'aktiv' => 'Im Shop', 'verkauft' => 'Verkauft'];

    protected $fillable = ['name', 'slug', 'category', 'status', 'short', 'description', 'price', 'stock', 'images', 'specs', 'sort'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'images' => 'array', 'specs' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            $p->slug = Str::slug($p->slug ?: $p->name);
            if ($p->status === 'aktiv' && $p->stock < 1) {
                $p->status = 'verkauft';
            }
        });
    }

    /** Sichtbar im Shop: aktiv oder verkauft (verkaufte bleiben als Referenz sichtbar, ohne Kaufen-Knopf). */
    public function scopeVisible(Builder $query): void
    {
        $query->whereIn('status', ['aktiv', 'verkauft']);
    }

    public function url(): string
    {
        return self::CATEGORIES[$this->category][1].$this->slug.'/';
    }

    public function categoryName(): string
    {
        return self::CATEGORIES[$this->category][0] ?? 'Shop';
    }

    public function available(): bool
    {
        if ($this->status !== 'aktiv' || $this->stock < 1) {
            return false;
        }
        // offene Online-Zahlungen halten Stück für die Haltezeit zurück
        $held = Order::query()->where('product_id', $this->id)->where('status', 'zahlung_offen')->where('hold_until', '>', now())->sum('quantity');

        return $this->stock - $held > 0;
    }

    /** @return array<int, string> öffentliche Bild-URLs */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])->map(fn ($p) => '/storage/'.ltrim($p, '/'))->all();
    }
}
