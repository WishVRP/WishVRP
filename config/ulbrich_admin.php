<?php

return [
    // Pfad der Verwaltung (Filament). Nicht verlinkt, nicht in robots.txt
    // (das würde ihn verraten), per X-Robots-Tag noindex. Login nur mit 2FA.
    'path' => env('ADMIN_PATH', 'verwaltung'),

    // 2FA-Pflicht. Nur für lokale Vorschauen abschaltbar (ADMIN_MFA_REQUIRED=false); in Produktion immer an.
    'mfa_required' => env('APP_ENV') === 'production' ? true : (bool) env('ADMIN_MFA_REQUIRED', true),
];
