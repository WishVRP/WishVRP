<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Support\Traffic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Zählt öffentliche HTML-Seitenaufrufe (ohne Cookie, IP oder User-Agent). */
class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET')
            || $response->getStatusCode() !== 200
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || $request->is(trim(config('ulbrich_admin.path'), '/'), trim(config('ulbrich_admin.path'), '/').'/*', 'livewire*', 'filament*')
            || ! Traffic::shouldTrack($request)) {
            return;
        }
        $src = Traffic::source($request);
        try {
            PageView::create([
                'path' => substr('/'.ltrim($request->path() === '/' ? '' : $request->path().'/', '/'), 0, 190),
                'source' => $src['source'],
                'referrer_host' => $src['host'] ? substr($src['host'], 0, 120) : null,
                'device' => Traffic::device($request),
                'is_entry' => $src['entry'],
                'viewed_on' => now()->toDateString(),
            ]);
        } catch (\Throwable) {
            // Statistik darf die Seite nie stören.
        }
    }
}
