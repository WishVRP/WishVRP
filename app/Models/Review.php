<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['author', 'rating', 'text', 'reviewed_on', 'source', 'topic', 'published', 'sort'];

    protected function casts(): array
    {
        return ['reviewed_on' => 'date', 'published' => 'boolean', 'rating' => 'integer'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('sort')->orderByDesc('reviewed_on');
    }
}
