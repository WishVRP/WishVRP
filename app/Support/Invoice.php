<?php

namespace App\Support;

use App\Models\Note;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Rechnungen: fortlaufende Nummer je Jahr (RE-2026-0001), einmal erzeugt und unveränderlich gespeichert
 * (storage/app/private/rechnungen). Storno erzeugt eine eigene Stornorechnung mit neuer Nummer.
 * Steuer laut config('ulbrich.tax_mode'): regular = 19 % enthalten, kleinunternehmer = § 19 UStG.
 */
final class Invoice
{
    public const DIR = 'rechnungen';

    /** Rechnung erzeugen (genau einmal) und PDF speichern. */
    public static function assign(Order $o): ?string
    {
        if ($o->invoice_number) {
            return $o->invoice_number;
        }
        if (((float) $o->amount + (float) $o->shipping_amount + (float) $o->customs_amount) <= 0) {
            return null;
        }
        $number = self::nextNumber();
        $o->forceFill(['invoice_number' => $number, 'invoice_date' => now('Europe/Berlin')->toDateString()])->saveQuietly();
        Storage::disk('local')->put(self::path($number), Pdf::render('pdf.invoice', ['o' => $o->fresh(), 'doc' => self::data($o->fresh())]));
        Note::query()->create(['order_id' => $o->id, 'user_id' => auth()->id(), 'body' => "Rechnung {$number} erstellt.", 'done_at' => now()]);

        return $number;
    }

    /** Stornorechnung zu einer bestehenden Rechnung (z. B. nach Widerruf oder Storno). */
    public static function cancel(Order $o): ?string
    {
        if (! $o->invoice_number || $o->cancel_invoice_number) {
            return $o->cancel_invoice_number;
        }
        $number = self::nextNumber();
        $o->forceFill(['cancel_invoice_number' => $number, 'cancel_invoice_date' => now('Europe/Berlin')->toDateString()])->saveQuietly();
        Storage::disk('local')->put(self::path($number), Pdf::render('pdf.invoice', ['o' => $o->fresh(), 'doc' => self::data($o->fresh(), true)]));
        Note::query()->create(['order_id' => $o->id, 'user_id' => auth()->id(), 'body' => "Stornorechnung {$number} zu {$o->invoice_number} erstellt.", 'done_at' => now()]);

        return $number;
    }

    /** PDF-Inhalt der Rechnung (bzw. Stornorechnung); fehlt die Datei, wird sie aus den gespeicherten Daten neu erzeugt. */
    public static function pdf(Order $o, bool $cancel = false): ?string
    {
        $number = $cancel ? $o->cancel_invoice_number : $o->invoice_number;
        if (! $number) {
            return null;
        }
        $disk = Storage::disk('local');
        if (! $disk->exists(self::path($number))) {
            $disk->put(self::path($number), Pdf::render('pdf.invoice', ['o' => $o, 'doc' => self::data($o, $cancel)]));
        }

        return $disk->get(self::path($number));
    }

    public static function filename(string $number): string
    {
        return 'Rechnung-'.$number.'-Messermanufaktur-Ulbrich.pdf';
    }

    public static function path(string $number): string
    {
        return self::DIR.'/'.$number.'.pdf';
    }

    private static function nextNumber(): string
    {
        $year = (int) now('Europe/Berlin')->year;

        return DB::transaction(function () use ($year) {
            $row = DB::table('invoice_counters')->where('year', $year)->lockForUpdate()->first();
            $next = ($row->last ?? 0) + 1;
            $row ? DB::table('invoice_counters')->where('year', $year)->update(['last' => $next])
                : DB::table('invoice_counters')->insert(['year' => $year, 'last' => $next]);

            return sprintf('RE-%d-%04d', $year, $next);
        });
    }

    /** Positionen und Summen. Zoll (vorab berechnet) ist durchlaufender Posten ohne Umsatzsteuer. */
    public static function data(Order $o, bool $cancel = false): array
    {
        $sign = $cancel ? -1 : 1;
        $lines = [];
        $item = $o->summary();
        if ($o->discount_percent) {
            $item .= ' (nach '.(int) $o->discount_percent.' % Rabatt'.($o->discount_code ? ', Code '.$o->discount_code : '').')';
        }
        if ((float) $o->amount > 0) {
            $lines[] = ['text' => $item, 'qty' => max(1, (int) ($o->type === 'kurs' ? 1 : $o->quantity)), 'total' => $sign * (float) $o->amount, 'taxed' => true];
        }
        if ((float) $o->shipping_amount > 0) {
            $lines[] = ['text' => 'Versand'.($o->country ? ' nach '.$o->country : ''), 'qty' => 1, 'total' => $sign * (float) $o->shipping_amount, 'taxed' => true];
        }
        if ((float) $o->voucher_amount > 0) {
            $lines[] = ['text' => 'Gutschein angerechnet', 'qty' => 1, 'total' => -$sign * (float) $o->voucher_amount, 'taxed' => true];
        }
        if ((float) $o->customs_amount > 0) {
            $lines[] = ['text' => 'Zoll und Einfuhrabgaben (verauslagt, durchlaufender Posten)', 'qty' => 1, 'total' => $sign * (float) $o->customs_amount, 'taxed' => false];
        }
        $gross = round(array_sum(array_column($lines, 'total')), 2);
        $taxedGross = round(array_sum(array_column(array_filter($lines, fn ($l) => $l['taxed']), 'total')), 2);
        $mode = config('ulbrich.tax_mode');
        $vat = (float) (Tax::included($taxedGross) ?? 0);

        return [
            'cancel' => $cancel,
            'number' => $cancel ? $o->cancel_invoice_number : $o->invoice_number,
            'date' => \Illuminate\Support\Carbon::parse($cancel ? $o->cancel_invoice_date : $o->invoice_date),
            'refers_to' => $cancel ? $o->invoice_number : null,
            'service_date' => self::serviceDate($o),
            'lines' => $lines,
            'gross' => $gross,
            'vat' => $vat,
            'net' => round($taxedGross - $vat, 2),
            'mode' => $mode,
            'paid' => (bool) $o->paid_at,
            'payment' => ['stripe' => 'online (Stripe)', 'ueberweisung' => 'Überweisung', 'gutschein' => 'Gutschein', 'rabatt' => 'Rabatt'][$o->payment_method] ?? $o->payment_method,
        ];
    }

    private static function serviceDate(Order $o): string
    {
        if ($o->type === 'kurs' && $o->course_start) {
            return $o->course_start->format('d.m.Y').($o->course_end && ! $o->course_end->equalTo($o->course_start) ? ' – '.$o->course_end->format('d.m.Y') : '');
        }
        if ($o->type === 'gutschein') {
            return 'Ausgabe des Gutscheins am '.\Illuminate\Support\Carbon::parse($o->invoice_date ?? now())->format('d.m.Y');
        }

        return 'Lieferung/Leistung im '.\Illuminate\Support\Carbon::parse($o->paid_at ?? $o->invoice_date ?? now())->locale('de')->isoFormat('MMMM YYYY');
    }
}
