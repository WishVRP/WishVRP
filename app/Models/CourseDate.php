<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseDate extends Model
{
    protected $fillable = ['course_id', 'starts_on', 'ends_on', 'seats_total', 'seats_booked', 'is_published'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereDate('starts_on', '>=', now()->toDateString())
            ->whereColumn('seats_booked', '<', 'seats_total')
            ->orderBy('starts_on');
    }

    public function seatsLeft(): int
    {
        return max(0, $this->seats_total - $this->seats_booked);
    }

    /** Ehrliche Anzeige ohne künstliche Knappheit: einfach die Zahl. */
    public function seatsLeftLabel(): string
    {
        $n = $this->seatsLeft();

        return $n === 1 ? '1 Platz frei' : "{$n} Plätze frei";
    }
}
