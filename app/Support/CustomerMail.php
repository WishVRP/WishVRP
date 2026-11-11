<?php

namespace App\Support;

use App\Mail\CustomerUpdateMail;
use App\Models\Note;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

/** Status-Mails an die Kundschaft verschicken und als Notiz am Vorgang festhalten. */
final class CustomerMail
{
    public static function send(Order $order, string $kind, ?string $personal = null): bool
    {
        if (! $order->email || ! isset(CustomerUpdateMail::KINDS[$kind])) {
            return false;
        }
        try {
            Mail::to($order->email, $order->name)->send(new CustomerUpdateMail($order, $kind, $personal));
            Note::query()->create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'body' => 'E-Mail an '.$order->email.': „'.CustomerUpdateMail::KINDS[$kind].'“'.($personal ? ' (mit persönlicher Nachricht)' : '').(auth()->guest() ? ' – automatisch' : ''),
                'done_at' => now(),
            ]);
            if (in_array($kind, ['zugestellt', 'kurs'], true) && $order->review_consent) {
                $order->forceFill(['review_requested_at' => now()])->saveQuietly();
            }

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
