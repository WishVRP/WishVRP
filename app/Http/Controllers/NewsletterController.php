<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Bestätigen und Abmelden per Knopfdruck (POST) – damit Link-Scanner in Postfächern
 * nicht versehentlich bestätigen oder abmelden.
 */
class NewsletterController extends Controller
{
    public function confirm(Request $request): RedirectResponse
    {
        $sub = NewsletterSubscriber::query()->where('confirm_token', (string) $request->input('t'))->first();
        if ($sub && ! $sub->confirmed_at) {
            $sub->update(['confirmed_at' => now(), 'unsubscribed_at' => null]);
        }

        return redirect('/newsletter/bestaetigen/')->with('newsletter', $sub ? 'confirmed' : 'invalid');
    }

    public function unsubscribe(Request $request): RedirectResponse
    {
        $sub = NewsletterSubscriber::query()->where('unsubscribe_token', (string) $request->input('t'))->first();
        $sub?->update(['unsubscribed_at' => now()]);

        return redirect('/newsletter/abmelden/')->with('newsletter', $sub ? 'unsubscribed' : 'invalid');
    }
}
