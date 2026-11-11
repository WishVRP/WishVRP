<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    | Stripe (Online-Zahlung). Ohne Schlüssel: keine Online-Zahlung, Ablauf wie bisher (Anfrage/Überweisung).
    | Schlüssel nur in .env. Webhook-Ziel: /stripe/webhook/ (Ereignisse checkout.session.completed/expired).
    */
    /*
    | Google-Bewertungen automatisch (Places API New, serverseitig – kein Google-Skript beim Besucher).
    | Schlüssel nur in .env; in der Google Cloud auf „Places API (New)“ und die Server-IP beschränken.
    | Liefert Gesamtbewertung, Anzahl und die bis zu 5 von Google ausgewählten Bewertungen.
    */
    'google_places' => [
        'key' => env('GOOGLE_PLACES_KEY'),
        'place_id' => env('GOOGLE_PLACE_ID'),
        'refresh_minutes' => (int) env('GOOGLE_REVIEWS_REFRESH_MINUTES', 60),
    ],

    // DHL Shipment Tracking (developer.dhl.com, kostenlos bis 250 Abfragen/Tag): erkennt Zustellungen automatisch.
    'dhl' => [
        'key' => env('DHL_API_KEY'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    | SMS für die Weihnachtsaktion. driver: log (Entwicklung, nichts wird verschickt)
    | oder seven (seven.io, Anbieter aus Deutschland). API-Schlüssel nur in .env, nie ins Repository.
    */
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'from' => env('SMS_FROM', 'Ulbrich'),        // Absendername, max. 11 Zeichen
        'seven_key' => env('SEVEN_API_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
