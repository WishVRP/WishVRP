<?php

return [
    // Pfad der Verwaltung (Filament). Nicht verlinkt, nicht in robots.txt
    // (das würde ihn verraten), per X-Robots-Tag noindex. Login nur mit 2FA.
    'path' => env('ADMIN_PATH', 'verwaltung'),

    // Datenbank automatisch einrichten/aktualisieren (Hosting ohne SSH, z. B. IONOS per FTP).
    // Testumgebung immer an; Produktion nur mit AUTO_SETUP=true in der .env.
    'auto_setup' => env('APP_ENV') === 'staging' || (bool) env('AUTO_SETUP', false),

    // Erstes Admin-Konto in Produktion ohne SSH: kurz auf true, Konto unter /{ADMIN_PATH}/register anlegen,
    // danach wieder false. Sobald ein Konto existiert, ist die Registrierung ohnehin geschlossen.
    'setup_open' => (bool) env('ADMIN_SETUP_OPEN', false),

    // Testumgebung: Passwortschutz für die ganze Seite (leer = aus; in Produktion immer aus).
    'staging_user' => env('STAGING_USER', 'ulbrich'),
    'staging_password' => env('STAGING_PASSWORD', ''),

    // 2FA-Pflicht. Nur für lokale Vorschauen abschaltbar (ADMIN_MFA_REQUIRED=false); in Produktion immer an.
    'mfa_required' => env('APP_ENV') === 'production' ? true : (bool) env('ADMIN_MFA_REQUIRED', true),
];
