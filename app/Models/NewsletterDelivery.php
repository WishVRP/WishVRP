<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Versandprotokoll: wer hat welche Ausgabe wann bekommen (ohne Öffnungs- oder Klick-Tracking). */
class NewsletterDelivery extends Model
{
    protected $fillable = ['newsletter_issue_id', 'newsletter_subscriber_id', 'email', 'status', 'error', 'discount_code_id', 'sent_at'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(NewsletterIssue::class, 'newsletter_issue_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'newsletter_subscriber_id');
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }
}
