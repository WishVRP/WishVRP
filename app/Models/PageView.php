<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/** Ein Seitenaufruf – ohne IP, ohne User-Agent, ohne Cookie. Nach 13 Monaten gelöscht. */
class PageView extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    protected $fillable = ['path', 'source', 'referrer_host', 'device', 'is_entry', 'viewed_on'];

    protected function casts(): array
    {
        return ['is_entry' => 'boolean', 'viewed_on' => 'date'];
    }

    public function prunable()
    {
        return static::where('viewed_on', '<', now()->subMonths(13));
    }
}
