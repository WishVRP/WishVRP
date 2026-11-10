<?php

return [
    // Pfad der Verwaltung (Filament). Nicht verlinkt, nicht in robots.txt
    // (das würde ihn verraten), per X-Robots-Tag noindex. Login nur mit 2FA.
    'path' => env('ADMIN_PATH', 'verwaltung'),
];
