<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\OrderPaid;
use App\Support\Stripe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Stripe-Webhook: Zahlung abgeschlossen bzw. Checkout abgelaufen. */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $event = Stripe::event($request->getContent(), $request->header('Stripe-Signature'));
        if (! $event) {
            return response('Ungültige Signatur', 400);
        }
        $session = $event['data']['object'] ?? [];
        $order = Order::query()->where('stripe_session_id', $session['id'] ?? '')->first()
            ?? Order::query()->find($session['metadata']['order_id'] ?? 0);
        if (! $order) {
            return response('Vorgang unbekannt', 200);
        }

        match ($event['type'] ?? '') {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => ($session['payment_status'] ?? '') === 'paid' ? OrderPaid::paid($order) : null,
            'checkout.session.expired', 'checkout.session.async_payment_failed' => OrderPaid::expired($order),
            default => null,
        };

        return response('ok', 200);
    }
}
