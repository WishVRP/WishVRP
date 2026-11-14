#!/usr/bin/env php
<?php

/*
 * Cron-Einstieg für Hoster, deren Cron-Feld nur einen Dateipfad ohne Argumente erlaubt (z. B. IONOS UnixCron).
 * Entspricht „php artisan schedule:run“. Liegt bewusst neben artisan, nicht im öffentlichen Ordner public/.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['argv'] = $argv = ['artisan', 'schedule:run'];
$_SERVER['argc'] = $argc = 2;
chdir(__DIR__);

require __DIR__.'/artisan';
