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
        'in_arbeit' => 'In Arbeit',
        'versendet' => 'Versendet / übergeben',
        'zugestellt' => 'Zugestellt',
        'abgeschlossen' => 'Abgeschlossen',
        'storniert' => 'Storniert',
    ];

    public const STATUS_COLORS = [
        'neu' => 'warning',
        'zahlung_offen' => 'info',
        'bezahlt' => 'success',
        'bestaetigt' => 'success',
        'in_arbeit' => 'warning',
        'versendet' => 'info',
        'zugestellt' => 'success',
        'abgeschlossen' => 'gray',
        'storniert' => 'danger',
    ];

    /** Offene Vorgänge = noch etwas zu tun. */
    public const OPEN = ['neu', 'zahlung_offen', 'bezahlt', 'in_arbeit'];

    /** Versanddienstleister: Name und Sendungsverfolgung ({n} = Sendungsnummer). */
    public const CARRIERS = [
        'dhl' => ['DHL', 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?piececode={n}'],
        'dpd' => ['DPD', 'https://tracking.dpd.de/status/de_DE/parcel/{n}'],
        'hermes' => ['Hermes', 'https://www.myhermes.de/empfangen/sendungsverfolgung/sendungsinformation#{n}'],
        'gls' => ['GLS', 'https://gls-group.com/DE/de/paketverfolgung?match={n}'],
        'ups' => ['UPS', 'https://www.ups.com/track?loc=de_DE&tracknum={n}'],
        'post' => ['Deutsche Post', 'https://www.deutschepost.de/de/s/sendungsverfolgung.html?piececode={n}'],
        'sonstige' => ['Anderer Versanddienst', null],
    ];

    public function carrierName(): ?string
    {
        return self::CARRIERS[$this->tracking_carrier][0] ?? null;
    }

    /** Link zur Sendungsverfolgung: eigener Link oder aus Versanddienst + Nummer gebaut. */
    public function trackingLink(): ?string
    {
        if ($this->tracking_url && preg_match('#^https://#i', $this->tracking_url)) {
            return $this->tracking_url;
        }
        $tpl = self::CARRIERS[$this->tracking_carrier][1] ?? null;

        return $tpl && $this->tracking_number ? str_replace('{n}', rawurlencode($this->tracking_number), $tpl) : null;
    }

    /** Zu zahlender Betrag: Betrag (nach Rabatt) + Versand + vorab bezahlter Zoll − Gutschein. */
    public function totalDue(): float
    {
        return max(0, round((float) $this->amount + (float) $this->shipping_amount + (float) $this->customs_amount - (float) $this->voucher_amount, 2));
    }

    public function isPickup(): bool
    {
        return $this->shipping_method === 'abholung';
    }

    /** Letzter Kurstag (für die Bewertungsanfrage am Abend). */
    public function courseLastDay(): ?\Illuminate\Support\Carbon
    {
        return $this->course_end ?? $this->course_start ?? $this->courseDate?->ends_on ?? $this->courseDate?->starts_on;
    }

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
            'course_start' => 'date',
            'course_end' => 'date',
            'hold_until' => 'datetime',
            'in_progress_at' => 'datetime',
            'delivered_at' => 'datetime',
            'tracking_checked_at' => 'datetime',
            'review_requested_at' => 'datetime',
            'review_consent' => 'boolean',
            'customs_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'voucher_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            // Einwilligung „Bitte um Bewertung“ aus dem Website-Formular (Checkbox in contact-fields), sonst nein
            if ($order->review_consent === null && app()->bound('request') && request()->has('review_consent')) {
                $order->review_consent = request()->boolean('review_consent');
            }
            $year = now()->year;
            $last = static::query()->where('number', 'like', "U{$year}-%")->orderByDesc('id')->value('number');
            $next = $last ? (int) substr($last, -4) + 1 : 1;
            $order->number = sprintf('U%d-%04d', $year, $next);
        });

        static::saving(function (Order $order) {
            if (! $order->isDirty('status')) {
                return;
            }
            $stamp = ['bezahlt' => 'paid_at', 'bestaetigt' => 'confirmed_at', 'in_arbeit' => 'in_progress_at', 'versendet' => 'shipped_at', 'zugestellt' => 'delivered_at', 'storniert' => 'cancelled_at'][$order->status] ?? null;
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

    /** Straße vergleichbar machen („Hauptstr. 5“ = „hauptstraße 5“). */
    public static function streetKey(?string $street): ?string
    {
        $s = mb_strtolower(trim((string) $street));
        $s = preg_replace('/str(\.|asse|aße)?\b/u', 'str', $s);
        $s = preg_replace('/[^\p{L}\p{N}]/u', '', $s);

        return $s !== '' ? $s : null;
    }

    /**
     * Hinweise auf mögliche Mehrfach-Rabatte (zur Prüfung vor der Bestätigung):
     * andere Vorgänge mit Weihnachtsrabatt und gleichem Namen, gleicher Straße oder gleicher Telefonnummer.
     *
     * @return array<int, string>
     */
    public function discountWarnings(): array
    {
        $xmas = XmasEntry::query()->whereNotNull('code')->pluck('code');
        if (! $this->discount_code || ! $xmas->contains($this->discount_code)) {
            return [];   // Warnung nur für Weihnachtsrabatte (ein Rabatt je Haushalt)
        }
        $name = mb_strtolower(trim($this->name));
        $street = self::streetKey($this->street);
        $phone = XmasEntry::phoneKey($this->phone);

        return static::query()->whereIn('discount_code', $xmas)->whereKeyNot($this->id)->where('status', '!=', 'storniert')->get()
            ->filter(fn (Order $o) => mb_strtolower(trim($o->name)) === $name
                || ($street && self::streetKey($o->street) === $street)
                || ($phone && XmasEntry::phoneKey($o->phone) === $phone))
            ->map(fn (Order $o) => "{$o->number} ({$o->name})")->values()->all();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
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
            $date = $this->course_start?->locale('de')->isoFormat('D. MMMM YYYY')
                ?? $this->courseDate?->starts_on?->locale('de')->isoFormat('D. MMMM YYYY') ?? ($this->preferred_date ? 'Wunsch: '.$this->preferred_date : 'Termin offen');

            return trim(($this->course?->name ?? 'Kurs').' · '.$date.' · '.$this->participants.' P.');
        }

        return trim(($this->item ?? $this->typeLabel()).($this->quantity > 1 ? ' × '.$this->quantity : ''));
    }
}
