<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
