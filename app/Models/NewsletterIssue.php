<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Eine Newsletter-Ausgabe (automatisch aus Daten oder manuell geschrieben). */
class NewsletterIssue extends Model
{
    public const STATUSES = ['entwurf' => 'Entwurf', 'geplant' => 'Geplant', 'sendet' => 'Wird versendet', 'versendet' => 'Versendet'];

    protected $fillable = ['kind', 'week', 'subject', 'preheader', 'body', 'blocks', 'status', 'scheduled_at', 'sent_at',
        'discount_percent', 'discount_valid_days', 'recipients_count', 'failed_count', 'created_by'];

    protected function casts(): array
    {
        return ['blocks' => 'array', 'scheduled_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function discountCodes(): HasMany
    {
        return $this->hasMany(DiscountCode::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function discount(): int
    {
        return min((int) $this->discount_percent, (int) config('ulbrich.newsletter.max_percent'));
    }
}
