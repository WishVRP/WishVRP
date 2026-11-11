<?php

use App\Http\Middleware\EnsureTrailingSlash;
use App\Http\Middleware\LegacyRedirects;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StagingPassword;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Reihenfolge wichtig: erst Alt-URL → Ziel (ein Sprung), dann Slash-Normalisierung.
        $middleware->prepend([
            StagingPassword::class,
            LegacyRedirects::class,
            EnsureTrailingSlash::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            TrackPageView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
