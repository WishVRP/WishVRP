<?php

/*
| Thematische Querverweise „Passend dazu“ je Seite (3 Ziele).
| Ziel: jede wichtige Seite hat ≥ 3 sinnvolle eingehende und ausgehende Links im Inhalt
| (Header/Footer zählen bewusst nicht). Texte = Ankertext + kurze Begründung.
*/

return [
    '/schmiedekurse/' => ['/schmiedekurse/vergleich/', '/geschenke/schmiedekurs-gutschein/', '/ratgeber/'],
    '/schmiedekurse/vergleich/' => ['/schmiedekurse/', '/schmiedekurse/buchen/', '/ratgeber/'],
    '/schmiedekurse/heilbronn-region/' => ['/schmiedekurse/', '/manufaktur/', '/erlebnisse/'],
    '/shop/' => ['/klappmesser/', '/messer-unikate/', '/sonderanfertigungen/'],
    '/klappmesser/' => ['/shop/', '/ratgeber/damaststahl/', '/geschenke/'],
    '/sonderanfertigungen/' => ['/messerprojekte/', '/messer-unikate/', '/manufaktur/'],
    '/messer-unikate/' => ['/klappmesser/', '/sonderanfertigungen/', '/ratgeber/messer-pflegen/'],
    '/messerprojekte/' => ['/sonderanfertigungen/', '/messer-unikate/', '/shop/'],
    '/geschenke/' => ['/geschenke/schmiedekurs-gutschein/', '/klappmesser/', '/erlebnisse/'],
    '/geschenke/schmiedekurs-gutschein/' => ['/schmiedekurse/', '/geschenke/', '/erlebnisse/'],
    '/erlebnisse/' => ['/schmiedekurse/', '/geschenke/schmiedekurs-gutschein/', '/schmiedekurse/heilbronn-region/'],
    '/ratgeber/' => ['/ratgeber/damaststahl/', '/ratgeber/messer-pflegen/', '/schmiedekurse/heilbronn-region/'],
    '/ratgeber/damaststahl/' => ['/schmiedekurse/', '/klappmesser/', '/ratgeber/messer-pflegen/'],
    '/ratgeber/messer-pflegen/' => ['/ratgeber/damaststahl/', '/shop/', '/messer-unikate/'],
    '/manufaktur/' => ['/presse/', '/partner/', '/schmiedekurse/heilbronn-region/'],
    '/presse/' => ['/manufaktur/', '/partner/', '/kunsthandwerkermarkt/'],
    '/partner/' => ['/manufaktur/', '/presse/', '/ratgeber/'],
    '/kunsthandwerkermarkt/' => ['/manufaktur/', '/partner/', '/presse/'],
];
