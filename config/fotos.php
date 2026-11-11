<?php

/*
| Fotos der Manufaktur.
| Auf Wunsch des Auftraggebers (10.11.2026) Studiobilder plus – ausdrücklich gewünscht – das Foto von Andreas an der Esse: Die Messer sind echt aus eigener
| Fertigung und vor Ort vorhanden, die Hintergründe sind KI-generiert. Handyfotos aus Werkstatt
| und Kursen werden nicht verwendet.
| Web-Fassungen erzeugt `scripts/fotos-erzeugen.sh` nach public/images/fotos/{datei}-{breite}.{avif,webp,jpg}.
| datei = sprechender Dateiname für die Google-Bildersuche (klein, ohne Umlaute, Marke am Ende).
| alt = beschreibt sachlich, was zu sehen ist, plus Marke; max. 125 Zeichen, kein Keyword-Stapeln.
| w/h = Maße des Zuschnitts (für width/height → kein Layoutsprung).
| alt beschreibt nur, was zu sehen ist – keine Behauptungen über Edition-Zugehörigkeit oder Material.
| 'pruefen' = vor Go-live zu klären (läuft in `php artisan ulbrich:audit` mit).
*/

$studio = 'Messer echt (eigene Fertigung, vor Ort), Hintergrund KI-generiert – bestätigt 10.11.2026';

return [

    'klappmesser-offen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'datei' => 'handgeschmiedetes-damast-klappmesser-offen-messermanufaktur-ulbrich',
        'alt' => 'Handgeschmiedetes Damast-Klappmesser der Messermanufaktur Ulbrich, geöffnet, mit dunklem Holzgriff',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-geschlossen' => [
        'w' => 1229, 'h' => 1536, 'widths' => [480, 800, 1200],
        'datei' => 'klappmesser-holzgriff-geschlossen-messermanufaktur-ulbrich',
        'alt' => 'Geschlossenes Klappmesser der Messermanufaktur Ulbrich mit dunklem Holzgriff und Stahlbacke, an einen Stein gelehnt',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-stehend' => [
        'w' => 1287, 'h' => 1536, 'widths' => [480, 800, 1200],
        'datei' => 'damast-klappmesser-halb-offen-messermanufaktur-ulbrich',
        'alt' => 'Halb geöffnetes Damast-Klappmesser aus der Messermanufaktur Ulbrich, auf Klinge und Griff stehend',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'edition-messer' => [
        'w' => 816, 'h' => 658, 'widths' => [480, 800, 816],
        'datei' => 'damast-klappmesser-auf-stein-messermanufaktur-ulbrich',
        'alt' => 'Damast-Klappmesser der Messermanufaktur Ulbrich mit dunklem Holzgriff auf einem Stein',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-hell' => [
        'w' => 1300, 'h' => 330, 'widths' => [480, 800, 1200],
        'datei' => 'damast-klappmesser-heller-griff-messermanufaktur-ulbrich',
        'alt' => 'Damast-Klappmesser der Messermanufaktur Ulbrich mit hellem Griff, geöffnet auf einer Steinplatte',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-hell-detail' => [
        'w' => 612, 'h' => 490, 'widths' => [480, 612],
        'datei' => 'damast-klappmesser-heller-griff-detail-messermanufaktur-ulbrich',
        'alt' => 'Damast-Klappmesser mit hellem Griff, halb geöffnet an einem Holzstück – Messermanufaktur Ulbrich',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-griff' => [
        'w' => 436, 'h' => 490, 'widths' => [436],
        'datei' => 'klappmesser-griff-nahaufnahme-messermanufaktur-ulbrich',
        'alt' => 'Nahaufnahme: heller Griff eines handgefertigten Klappmessers mit Schrauben, Messermanufaktur Ulbrich',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'damast-detail' => [
        'w' => 468, 'h' => 490, 'widths' => [468],
        'datei' => 'damastklinge-muster-nahaufnahme-messermanufaktur-ulbrich',
        'alt' => 'Nahaufnahme einer geschmiedeten Klinge mit Damastmuster aus der Messermanufaktur Ulbrich',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'punze-detail' => [
        'w' => 400, 'h' => 300, 'widths' => [400],
        'datei' => 'damastklinge-punze-detail-messermanufaktur-ulbrich',
        'alt' => 'Detail: Damastklinge mit eingeschlagenem Zeichen an der Stahlbacke eines Ulbrich-Klappmessers',
        'pruefen' => null, 'hinweis' => $studio,
    ],

    'klappmesser-rotholz' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'datei' => 'damast-klappmesser-rotbrauner-holzgriff-messermanufaktur-ulbrich',
        'alt' => 'Damast-Klappmesser der Messermanufaktur Ulbrich mit rotbraunem Holzgriff auf einer Holzscheibe',
        'pruefen' => 'Holzscheiben-Bild: KI-Hintergrund oder echtes Foto? (Wunsch: nur KI-Bilder)',
    ],

    'klappmesser-zebrano' => [
        'w' => 1536, 'h' => 1148, 'widths' => [480, 800, 1200],
        'datei' => 'damast-klappmesser-gestreifter-holzgriff-messermanufaktur-ulbrich',
        'alt' => 'Geöffnetes Damast-Klappmesser der Messermanufaktur Ulbrich mit hellem, gestreiftem Holzgriff',
        'pruefen' => 'Holzscheiben-Bild: KI-Hintergrund oder echtes Foto? (Wunsch: nur KI-Bilder)',
    ],

    // Echtes Foto (Ausnahme von „nur KI-Bilder“, ausdrücklich gewünscht am 10.11.2026)
    'andreas-esse' => [
        'w' => 1440, 'h' => 1920, 'widths' => [480, 800, 1200],
        'datei' => 'messerschmied-andreas-ulbrich-an-der-esse',
        'alt' => 'Messerschmied Andreas Ulbrich in Lederschürze an der Esse seiner Werkstatt, das Kohlefeuer brennt hell',
        'pruefen' => 'Aus Instagram-Story-Screenshot; besser Kamera-Original in voller Auflösung',
    ],

    'andreas-portrait' => [
        'w' => 960, 'h' => 1200, 'widths' => [480, 800, 960],
        'datei' => 'messerschmied-andreas-ulbrich-portrait-werkstatt',
        'alt' => 'Messerschmied Andreas Ulbrich mit Schiebermütze und Lederschürze an der Esse in seiner Werkstatt',
        'pruefen' => null,
    ],

];
