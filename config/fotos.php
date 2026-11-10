<?php

/*
| Fotos der Manufaktur.
| Auf Wunsch des Auftraggebers (10.11.2026) nur Studiobilder: Die Messer sind echt aus eigener
| Fertigung und vor Ort vorhanden, die Hintergründe sind KI-generiert. Handyfotos aus Werkstatt
| und Kursen werden nicht verwendet.
| Web-Fassungen erzeugt `scripts/fotos-erzeugen.sh` nach public/images/fotos/{key}-{breite}.{webp,jpg}.
| w/h = Maße des Zuschnitts (für width/height → kein Layoutsprung).
| alt beschreibt nur, was zu sehen ist – keine Behauptungen über Edition-Zugehörigkeit oder Material.
| 'pruefen' = vor Go-live zu klären (läuft in `php artisan ulbrich:audit` mit).
*/

$studio = 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026';

return [

    'klappmesser-offen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Geöffnetes Damast-Klappmesser mit dunklem Holzgriff vor dunklem Hintergrund',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-geschlossen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Geschlossenes Klappmesser mit dunklem Holzgriff und Stahlbacke, aufrecht an einem Stein',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-stehend' => [
        'w' => 1287, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Halb geöffnetes Damast-Klappmesser, auf Klinge und Griff stehend',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'edition-messer' => [
        'w' => 816, 'h' => 658, 'widths' => [480, 800, 816],
        'alt' => 'Damast-Klappmesser mit dunklem Holzgriff auf einem Stein, dahinter ein Holzbrett',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-hell' => [
        'w' => 1300, 'h' => 330, 'widths' => [480, 800, 1200],
        'alt' => 'Damast-Klappmesser mit hellem Griff, geöffnet auf einer Steinplatte',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-hell-detail' => [
        'w' => 612, 'h' => 490, 'widths' => [480, 612],
        'alt' => 'Damast-Klappmesser mit hellem Griff, halb geöffnet an einem Holzstück',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-griff' => [
        'w' => 436, 'h' => 490, 'widths' => [436],
        'alt' => 'Heller Griff eines Klappmessers mit Schrauben und Bohrung, Nahaufnahme',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'damast-detail' => [
        'w' => 468, 'h' => 490, 'widths' => [468],
        'alt' => 'Nahaufnahme einer Klinge mit Damastmuster',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'punze-detail' => [
        'w' => 400, 'h' => 300, 'widths' => [400],
        'alt' => 'Detail eines Klappmessers: Damastklinge mit eingeschlagenem Zeichen an der Stahlbacke',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-rotholz' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'alt' => 'Damast-Klappmesser mit rotbraunem Holzgriff auf einer Holzscheibe mit Brandmuster',
        'pruefen' => 'Holzscheiben-Bild: KI-Hintergrund oder echtes Foto? (Wunsch: nur KI-Bilder)',
    ],

    'klappmesser-zebrano' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'alt' => 'Geöffnetes Damast-Klappmesser mit hellem, gestreiftem Holzgriff auf einer Holzscheibe',
        'pruefen' => 'Holzscheiben-Bild: KI-Hintergrund oder echtes Foto? (Wunsch: nur KI-Bilder)',
    ],

];
