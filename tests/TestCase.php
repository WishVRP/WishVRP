<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Kalender-Tests arbeiten mit Tagen ab „übermorgen“; die Freischaltung ab 2027 prüft BookingOpensTest eigens.
        config(['ulbrich.booking.opens_on' => null]);
    }

    /**
     * Laravels Test-Client entfernt den Schrägstrich am Ende der URL.
     * Die Website arbeitet bewusst mit /pfad/ – deshalb hier unverändert senden.
     */
    protected function prepareUrlForRequest($uri)
    {
        $uri = (string) $uri;

        if (str_starts_with($uri, 'http')) {
            return $uri;
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($uri, '/');
    }
}
