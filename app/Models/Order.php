<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Bestellung, Kursbuchung oder Anfrage aus der Website.
 * Kursplätze werden erst bei „Bestätigt“ gezählt (und bei Storno wieder freigegeben).
 */
class Order extends Model
{
    public const TYPES = [
        'kurs' => 'Kursbuchung',
        'gutschein' => 'Gutschein',
        'messer' => 'Messerbestellung',
        'service' => 'Messerservice',
        'anfrage' => 'Anfrage',
    ];

    public const STATUSES = [
        'neu' => 'Neu',
        'zahlung_offen' => 'Zahlung offen',
        'bezahlt' => 'Bezahlt',
        'bestaetigt' => 'Bestätigt',
        'versendet' => 'Versendet / übergeben',
        'abgeschlossen' => 'Abgeschlossen',
        'storniert' => 'Storniert',
    ];

    public const STATUS_COLORS = [
        'neu' => 'warning',
        'zahlung_offen' => 'info',
        'bezahlt' => 'success',
        'bestaetigt' => 'success',
        'versendet' => 'gray',
        'abgeschlossen' => 'gray',
        'storniert' => 'danger',
    ];

    /** Offene Vorgänge = noch etwas zu tun. */
    public const OPEN = ['neu', 'zahlung_offen', 'bezahlt'];

    protected $guarded = ['id', 'number', 'seats_counted'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'seats_counted' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $year = now()->year;
            $last = static::query()->where('number', 'like', "U{$year}-%")->orderByDesc('id')->value('number');
            $next = $last ? (int) substr($last, -4) + 1 : 1;
            $order->number = sprintf('U%d-%04d', $year, $next);
        });

        static::saving(function (Order $order) {
            if (! $order->isDirty('status')) {
                return;
            }
            $stamp = ['bezahlt' => 'paid_at', 'bestaetigt' => 'confirmed_at', 'versendet' => 'shipped_at', 'storniert' => 'cancelled_at'][$order->status] ?? null;
            if ($stamp && ! $order->{$stamp}) {
                $order->{$stamp} = now();
            }
            $order->syncSeats();
        });

        static::saved(function (Order $order) {
            if ($order->wasChanged('status') || $order->wasChanged('public_name')) {
                $order->syncEditionPiece();
            }
        });
    }

    /** Plätze im Kurstermin zählen bzw. freigeben (genau einmal). */
    public function syncSeats(): void
    {
        if ($this->type !== 'kurs' || ! $this->course_date_id || ! $this->participants) {
            return;
        }
        $date = CourseDate::find($this->course_date_id);
        if (! $date) {
            return;
        }
        $counts = in_array($this->status, ['bestaetigt', 'versendet', 'abgeschlossen'], true);
        if ($counts && ! $this->seats_counted) {
            $date->increment('seats_booked', $this->participants);
            $this->seats_counted = true;
        } elseif (! $counts && $this->seats_counted) {
            $date->update(['seats_booked' => max(0, $date->seats_booked - $this->participants)]);
            $this->seats_counted = false;
        }
    }

    /** Edition-Nummer: bezahlt/bestätigt → verkauft (Name automatisch, falls zugestimmt); storniert → wieder frei. */
    public function syncEditionPiece(): void
    {
        $piece = EditionPiece::query()->where('order_id', $this->id)->first();
        if (! $piece) {
            return;
        }
        if ($this->status === 'storniert') {
            $piece->update(['status' => 'frei', 'order_id' => null, 'buyer_name' => null, 'show_name' => false]);
        } elseif (in_array($this->status, ['bezahlt', 'bestaetigt', 'versendet', 'abgeschlossen'], true)) {
            $piece->update(['status' => 'vergeben', 'buyer_name' => $this->public_name, 'show_name' => filled($this->public_name)]);
        } else {
            $piece->update(['status' => 'reserviert', 'buyer_name' => $this->public_name, 'show_name' => filled($this->public_name)]);
        }
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseDate(): BelongsTo
    {
        return $this->belongsTo(CourseDate::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', self::OPEN);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Kurzbeschreibung für Listen und E-Mails. */
    public function summary(): string
    {
        if ($this->type === 'kurs') {
            $date = $this->courseDate?->starts_on?->locale('de')->isoFormat('D. MMMM YYYY') ?? ($this->preferred_date ? 'Wunsch: '.$this->preferred_date : 'Termin offen');

            return trim(($this->course?->name ?? 'Kurs').' · '.$date.' · '.$this->participants.' P.');
        }

        return trim(($this->item ?? $this->typeLabel()).($this->quantity > 1 ? ' × '.$this->quantity : ''));
    }
}
