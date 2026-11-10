<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name', 'category', 'path', 'booking_key', 'duration_label', 'blade_length_cm',
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

    public const CATEGORIES = [
        'einstieg' => 'Einstieg',
        'klinge' => 'Klinge schmieden',
        'damast' => 'Damast',
        'gruppe' => 'Teams & Gruppen',
    ];

    public function categoryLabel(): ?string
    {
        return self::CATEGORIES[$this->category] ?? null;
    }

    public function durationDays(): ?int
    {
        return $this->duration_label ? (int) preg_replace('/\D.*/u', '', $this->duration_label) : null;
    }

    public function dates(): HasMany
    {
        return $this->hasMany(CourseDate::class);
    }

    /** Buchung mit Vorauswahl (?kurs=), Parameter wie auf der Altseite. */
    public function bookingUrl(): string
    {
        return '/schmiedekurse/buchen/'.($this->booking_key ? '?kurs='.urlencode($this->booking_key) : '');
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
