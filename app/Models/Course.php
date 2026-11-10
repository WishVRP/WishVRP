<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name', 'path', 'booking_key', 'duration_label', 'blade_length_cm',
        'price_from', 'summary', 'sort', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'blade_length_cm' => 'integer',
            'price_from' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function dates(): HasMany
    {
        return $this->hasMany(CourseDate::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /** Kursseite; solange der Live-Pfad nicht übernommen ist, die Kursübersicht. */
    public function url(): string
    {
        return $this->path ?: '/schmiedekurse/';
    }
}
