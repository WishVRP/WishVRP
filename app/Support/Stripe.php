<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Stripe Checkout über die REST-API (ohne SDK). Ohne Schlüssel ist die Online-Zahlung aus.
 * Webhook-Signaturen werden nach Stripe-Verfahren geprüft (HMAC-SHA256, 5 Minuten Toleranz).
 */
final class Stripe
{
    public static function enabled(): bool
    {
        return filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret'));
    }

    /**
     * Checkout-Sitzung anlegen und URL zurückgeben.
     *
     * @param  array<int, array{name: string, amount: float, quantity?: int}>  $lines  Beträge in Euro
     */
    public static function checkout(Order $order, array $lines): string
    {
        $params = [
            'mode' => 'payment',
            'locale' => 'de',
            'customer_email' => $order->email,
            'client_reference_id' => $order->number,
            'metadata[order_id]' => $order->id,
            'metadata[order_number]' => $order->number,
            'success_url' => PageMeta::absolute('/zahlung/danke/').'?o='.$order->number,
            'cancel_url' => PageMeta::absolute('/zahlung/abgebrochen/').'?o='.$order->number,
            'expires_at' => now()->addMinutes(max(30, (int) config('ulbrich.booking.hold_minutes')))->timestamp,
        ];
        foreach (array_values($lines) as $i => $line) {
            $params["line_items[{$i}][quantity]"] = $line['quantity'] ?? 1;
            $params["line_items[{$i}][price_data][currency]"] = 'eur';
            $params["line_items[{$i}][price_data][unit_amount]"] = (int) round($line['amount'] * 100);
            $params["line_items[{$i}][price_data][product_data][name]"] = mb_substr($line['name'], 0, 250);
        }

        $res = Http::withToken((string) config('services.stripe.secret'))->asForm()->timeout(15)
            ->post('https://api.stripe.com/v1/checkout/sessions', $params);
        if (! $res->successful() || ! $res->json('url')) {
            throw new RuntimeException('Stripe-Checkout fehlgeschlagen: '.$res->status().' '.$res->json('error.message'));
        }
        $order->forceFill(['stripe_session_id' => $res->json('id'), 'payment_method' => 'stripe'])->save();

        return (string) $res->json('url');
    }

    /** Webhook prüfen; gibt das Ereignis zurück oder null bei ungültiger Signatur. */
    public static function event(string $payload, ?string $header): ?array
    {
        $secret = (string) config('services.stripe.webhook_secret');
        if ($secret === '' || ! $header) {
            return null;
        }
        $parts = collect(explode(',', $header))->mapWithKeys(function ($p) {
            [$k, $v] = array_pad(explode('=', trim($p), 2), 2, '');

            return [$k => $v];
        });
        $t = (int) ($parts['t'] ?? 0);
        $expected = hash_hmac('sha256', $t.'.'.$payload, $secret);
        $valid = collect(explode(',', $header))->map(fn ($p) => trim($p))
            ->filter(fn ($p) => str_starts_with($p, 'v1='))->contains(fn ($p) => hash_equals($expected, substr($p, 3)));
        if (! $valid || abs(time() - $t) > 300) {
            return null;
        }

        return json_decode($payload, true) ?: null;
    }
}
