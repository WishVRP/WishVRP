<?php

/*
| Datensparsame Besucherstatistik (eigener Server, keine Cookies, keine IP,
| kein User-Agent gespeichert). „Do Not Track“ und „Global Privacy Control“
| werden respektiert. Vor Go-live in der Datenschutzerklärung beschreiben.
*/
return [
    'enabled' => env('ANALYTICS_ENABLED', true),
];
