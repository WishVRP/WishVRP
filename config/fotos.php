<?php

/*
| Fotos der Manufaktur (geliefert im Chat am 10.11.2026).
| Web-Fassungen erzeugt `scripts/fotos-erzeugen.sh` nach public/images/fotos/{key}-{breite}.{webp,jpg}.
| w/h = Maße des Zuschnitts (für width/height → kein Layoutsprung).
| alt beschreibt nur, was zu sehen ist – keine Behauptungen über Material, Kurs oder Edition-Zugehörigkeit.
| 'pruefen' = vor Go-live zu klären (läuft in `php artisan ulbrich:audit` mit).
*/

return [

    'andreas-esse' => [
        'w' => 1440, 'h' => 1920, 'widths' => [480, 800, 1200],
        'alt' => 'Andreas Ulbrich in Lederschürze an der Esse, das Kohlefeuer brennt hell',
        'pruefen' => 'Aus Instagram-Story-Screenshot zugeschnitten; Freigabe durch Andreas, Zigarette im Bild für die Startseite gewünscht?',
    ],

    'werkstatt-kurs' => [
        'w' => 1536, 'h' => 1150, 'widths' => [480, 800, 1200],
        'alt' => 'Werkstatthalle mit Amboss, Esse und Presse; eine Gruppe steht an der Werkbank',
        'pruefen' => 'Erkennbare Personen: Einwilligung der Abgebildeten (DSGVO/KUG) vorhanden?',
    ],

    'messer-tisch' => [
        'w' => 1370, 'h' => 1195, 'widths' => [480, 800, 1200],
        'alt' => 'Fünf handgefertigte Messer mit Holzgriffen nebeneinander auf einem Tisch',
        'pruefen' => null,
    ],

    'damast-detail' => [
        'w' => 468, 'h' => 490, 'widths' => [468],
        'alt' => 'Nahaufnahme einer Klinge mit Damastmuster',
        'pruefen' => null, 'hinweis' => 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026',
    ],

    'klappmesser-geschlossen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Geschlossenes Klappmesser mit dunklem Holzgriff und Stahlbacke, aufrecht an einem Stein',
        'pruefen' => null, 'hinweis' => 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026',
    ],

    'klappmesser-offen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Geöffnetes Damast-Klappmesser mit dunklem Holzgriff vor dunklem Hintergrund',
        'pruefen' => null, 'hinweis' => 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026',
    ],

    'klappmesser-stehend' => [
        'w' => 1287, 'h' => 1536, 'widths' => [480, 800, 1200],
        'alt' => 'Halb geöffnetes Damast-Klappmesser, auf Klinge und Griff stehend',
        'pruefen' => null, 'hinweis' => 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026',
    ],

    'klappmesser-rotholz' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'alt' => 'Damast-Klappmesser mit rotbraunem Holzgriff auf einer Holzscheibe mit Brandmuster',
        'pruefen' => null,
    ],

    'klappmesser-zebrano' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'alt' => 'Geöffnetes Damast-Klappmesser mit hellem, gestreiftem Holzgriff auf einer Holzscheibe',
        'pruefen' => null,
    ],

    'andreas-portrait' => [
        'w' => 960, 'h' => 1200, 'widths' => [480, 800],
        'alt' => 'Andreas Ulbrich mit Schiebermütze und Lederschürze an der Esse in seiner Werkstatt',
        'pruefen' => null, 'hinweis' => 'Ausschnitt aus andreas-esse (Freigabe dort)',
    ],

    'werkstatt-halle' => [
        'w' => 1536, 'h' => 960, 'widths' => [480, 800, 1200],
        'alt' => 'Blick in die Werkstatthalle mit Amboss, Werkbänken, Presse und Galerie',
        'pruefen' => null, 'hinweis' => 'Ausschnitt aus werkstatt-kurs (Einwilligung dort)',
    ],

    'werkstatt-amboss' => [
        'w' => 800, 'h' => 500, 'widths' => [480, 800],
        'alt' => 'Andreas Ulbrich am Amboss, daneben schauen Teilnehmer zu',
        'pruefen' => null, 'hinweis' => 'Ausschnitt aus werkstatt-kurs (Einwilligung dort)',
    ],

    'esse-feuer' => [
        'w' => 680, 'h' => 425, 'widths' => [480, 680],
        'alt' => 'Hohe Flammen aus der Esse, davor ein Arm im gestreiften Hemd',
        'pruefen' => null, 'hinweis' => 'Ausschnitt aus andreas-esse',
    ],

    'griff-detail' => [
        'w' => 500, 'h' => 628, 'widths' => [480, 500],
        'alt' => 'Messergriff aus Maserholz und türkisfarbenem Harz mit Messingstiften',
        'pruefen' => null, 'hinweis' => 'Nur der Griff; die Klinge mit lizenzpflichtigem Motiv ist abgeschnitten',
    ],

    'messer-kuechenmesser' => [
        'w' => 747, 'h' => 560, 'widths' => [480, 747],
        'alt' => 'Küchenmesser mit Damastklinge und Holzgriff, daneben ein kleines Messer und ein Klappmesser',
        'pruefen' => null,
    ],

    'punze-detail' => [
        'w' => 400, 'h' => 300, 'widths' => [400],
        'alt' => 'Detail eines Klappmessers: Damastklinge mit eingeschlagenem Zeichen an der Stahlbacke',
        'pruefen' => null, 'hinweis' => 'Messer echt, Hintergrund KI-generiert',
    ],

];
