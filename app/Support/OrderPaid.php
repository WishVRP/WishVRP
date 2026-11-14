<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\Mail;

/** Folgen einer erfolgreichen bzw. abgebrochenen Zahlung – genau einmal je Vorgang. */
final class OrderPaid
{
    public static function paid(Order $order, string $method = 'stripe'): void
    {
        if (in_array($order->status, OrderStatusFlow::PAID, true)) {
            return;   // schon verarbeitet (Webhook kann mehrfach kommen)
        }
        $order->forceFill(['payment_method' => $method, 'hold_until' => null])->save();
        // Alle Vorgänge stehen nach Zahlungseingang auf „Bezahlt“; Kurse bestätigt ihr danach selbst (Mail mit Anleitung).
        $order->update(['status' => 'bezahlt']);   // → OrderStatusFlow::changed → effects()
    }

    /** Folgen des Zahlungseingangs (Lager, Gutschein, Rechnung, Bestätigungsmail) – aufgerufen beim ersten Wechsel auf „bezahlt“. */
    public static function effects(Order $order, bool $mail = true): void
    {
        $order->forceFill(['hold_until' => null])->saveQuietly();
        // Lager: Warenkorb (mehrere Messer) bzw. Einzelkauf
        $sold = $order->items ? collect($order->items)->map(fn ($i) => [$i['product_id'] ?? null, (int) ($i['qty'] ?? 1)])->all()
            : ($order->product_id ? [[$order->product_id, max(1, (int) $order->quantity)]] : []);
        foreach ($sold as [$productId, $qty]) {
            if ($productId && ($p = Product::find($productId))) {
                $left = max(0, $p->stock - max(1, $qty));
                $p->update(['stock' => $left, 'status' => $left === 0 ? 'verkauft' : $p->status]);
            }
        }
        if ($order->type === 'gutschein') {
            Voucher::query()->where('order_id', $order->id)->update(['status' => 'bezahlt']);
            self::mailVoucher($order);
        }
        if ($order->voucher_id) {
            Voucher::query()->whereKey($order->voucher_id)->update(['status' => 'eingeloest', 'redeemed_order_id' => $order->id, 'redeemed_at' => now()]);
        }
        try {
            Invoice::assign($order->fresh());   // Rechnung (PDF) – hängt an der Zahlungsbestätigung
        } catch (\Throwable $e) {
            report($e);
        }
        if ($mail) {
            self::mailConfirmation($order->fresh());
        }
    }

    public static function expired(Order $order): void
    {
        if (! in_array($order->status, ['neu', 'zahlung_offen'], true)) {
            return;
        }
        $order->forceFill(['hold_until' => null])->save();
        OrderStatusFlow::quietly(fn () => $order->update(['status' => 'storniert']));   // gibt Kurstag, Edition-Nummer und Lager frei – ohne Storno-Mail
        Voucher::query()->where('order_id', $order->id)->update(['status' => 'storniert']);
    }

    private static function mailConfirmation(Order $order): void
    {
        $c = config('ulbrich');
        $lines = ["Hallo {$order->name},", '', 'vielen Dank – deine Zahlung ist eingegangen.', '', "Vorgang {$order->number}: {$order->summary()}"];
        if ($order->type === 'kurs' && $order->course_start) {
            $lines[] = 'Kurstag: '.$order->course_start->locale('de')->isoFormat('dddd, D. MMMM YYYY').($order->course_end && ! $order->course_end->equalTo($order->course_start) ? ' bis '.$order->course_end->locale('de')->isoFormat('dddd, D. MMMM YYYY') : '');
            $lines[] = "Ort: {$c['name']}, {$c['address']['street']}, {$c['address']['postal_code']} {$c['address']['locality']} ({$c['address']['district']})";
        }
        if ($order->type === 'messer') {
            $lines[] = $order->shipping_method === 'abholung' ? 'Abholung: Wir melden uns, um einen Termin in der Manufaktur abzustimmen.' : 'Versand: Wir melden uns, sobald dein Messer unterwegs ist.';
        }
        $lines = [...$lines, '', "Fragen? {$c['phone']['display']} · {$c['email']}", '', $c['owner'], $c['name']];
        try {
            CustomerMail::send($order, 'bezahlt');   // gestaltete Zahlungs-/Buchungsbestätigung
            Mail::raw("Bezahlt: {$order->number} · {$order->typeLabel()} · {$order->summary()} · ".number_format((float) $order->amount, 2, ',', '.').' €', fn ($m) => $m->to($c['email'])->subject("Bezahlt: {$order->number}"));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function mailVoucher(Order $order): void
    {
        foreach (Voucher::query()->where('order_id', $order->id)->with(['course', 'order'])->get() as $v) {
            $v->sendToRecipientIfDue();   // ohne Wunschtermin sofort an die beschenkte Person
            try {
                Mail::to($order->email, $order->name)->send(new \App\Mail\VoucherMail($v->refresh(), 'buyer'));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
