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
        if (in_array($order->status, ['bezahlt', 'bestaetigt', 'in_arbeit', 'versendet', 'zugestellt', 'abgeschlossen'], true)) {
            return;   // schon verarbeitet (Webhook kann mehrfach kommen)
        }
        $order->forceFill(['payment_method' => $method, 'hold_until' => null])->save();
        // Kurs und Gutschein sind mit der Zahlung bestätigt; Messer gehen danach in den Versand.
        $order->update(['status' => in_array($order->type, ['kurs', 'gutschein'], true) ? 'bestaetigt' : 'bezahlt']);

        if ($order->product_id && ($p = Product::find($order->product_id))) {
            $left = max(0, $p->stock - max(1, (int) $order->quantity));
            $p->update(['stock' => $left, 'status' => $left === 0 ? 'verkauft' : $p->status]);
        }
        if ($order->type === 'gutschein') {
            Voucher::query()->where('order_id', $order->id)->update(['status' => 'bezahlt']);
            self::mailVoucher($order);
        }
        if ($order->voucher_id) {
            Voucher::query()->whereKey($order->voucher_id)->update(['status' => 'eingeloest', 'redeemed_order_id' => $order->id, 'redeemed_at' => now()]);
        }
        self::mailConfirmation($order);
    }

    public static function expired(Order $order): void
    {
        if (! in_array($order->status, ['neu', 'zahlung_offen'], true)) {
            return;
        }
        $order->forceFill(['hold_until' => null])->save();
        $order->update(['status' => 'storniert']);   // gibt Kurstag, Edition-Nummer und Lager wieder frei
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
        $c = config('ulbrich');
        foreach (Voucher::query()->where('order_id', $order->id)->with('course')->get() as $v) {
            try {
                Mail::raw(implode("\n", [
                    "Hallo {$order->name},", '',
                    'hier ist dein Gutschein der '.$c['name'].':', '',
                    "Gutscheincode: {$v->code}",
                    'Für: '.($v->course?->name ?? 'Schmiedekurs').' · '.$v->persons.' '.($v->persons === 1 ? 'Person' : 'Personen').($v->recipient ? " · für {$v->recipient}" : ''),
                    'Wert: '.number_format((float) $v->value, 2, ',', '.').' €',
                    'Gültig bis: '.$v->valid_until->locale('de')->isoFormat('D. MMMM YYYY'), '',
                    'So wird er eingelöst: Auf der Website unter „Kurs buchen“ den Kurs und einen freien Tag wählen und den Gutscheincode eintragen.', '',
                    $c['owner'], $c['name'],
                ]), fn ($m) => $m->to($order->email, $order->name)->subject("Dein Gutschein {$v->code}"));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
