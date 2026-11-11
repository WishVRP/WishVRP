<?php

namespace App\Http\Controllers;

use App\Models\SiteEvent;
use App\Support\Traffic;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Klick-Zählung per sendBeacon (Telefon, E-Mail, Buchen, Gutschein, extern). */
class EventController extends Controller
{
    private const CLICKS = ['telefon', 'email', 'buchen', 'gutschein', 'extern', 'spiel'];

    public function __invoke(Request $request): Response
    {
        $name = (string) $request->input('n');
        if (in_array($name, self::CLICKS, true) && Traffic::shouldTrack($request)) {
            SiteEvent::create([
                'name' => $name,
                'path' => substr((string) $request->input('p'), 0, 190) ?: null,
                'target' => $name === 'extern' ? substr((string) parse_url((string) $request->input('t'), PHP_URL_HOST), 0, 190) : null,
                'occurred_on' => now()->toDateString(),
            ]);
        }

        return response()->noContent();
    }
}
