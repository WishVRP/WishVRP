<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Galerie „Bei uns geschmiedet“: Fotos aus Kursen – nur mit Einwilligung der abgebildeten Personen. */
class GalleryItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'published' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('published', true)->where('consent', true)->orderBy('sort')->orderByDesc('id');
    }

    public function url(): string
    {
        return '/storage/'.ltrim($this->image, '/');
    }

    /** @return array{0:int,1:int} */
    public function size(): array
    {
        $file = storage_path('app/public/'.$this->image);
        $s = is_file($file) ? getimagesize($file) : false;

        return $s ? [$s[0], $s[1]] : [1200, 900];
    }
}
