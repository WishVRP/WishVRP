<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Getrennte Limits je Bereich (throttle:8,1 würde einen gemeinsamen Zähler für alle Routen nutzen).
        \Illuminate\Support\Facades\RateLimiter::for('forms', fn (\Illuminate\Http\Request $r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(8)->by('forms|'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('xmas', fn (\Illuminate\Http\Request $r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('xmas|'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('events', fn (\Illuminate\Http\Request $r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(120)->by('events|'.$r->ip()));

        //
    }
}
