<?php

return [
    // Kanonische Produktionsdomain. Canonicals und Sitemap zeigen immer hierhin,
    // auch auf Staging (dort zusätzlich noindex).
    'canonical_host' => env('SEO_CANONICAL_HOST', 'https://messermanufaktur-ulbrich.de'),

    // Nur auf der Produktionsdomain true. Staging/lokal: noindex + robots Disallow.
    'indexable' => (bool) env('SEO_INDEXABLE', false),

    'title_suffix' => ' | Ulbrich',

    // [FEHLT] Echtes Foto (1200×630) für Social-Vorschau. Bis dahin neutrales Motiv.
    'default_og_image' => '/images/og-default.png',
];
