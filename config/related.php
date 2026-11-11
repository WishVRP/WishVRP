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
    '/klappmesser/' => ['/shop/', '/ratgeber/damastmesser/', '/geschenke/'],
    '/sonderanfertigungen/' => ['/messerprojekte/', '/messer-unikate/', '/manufaktur/'],
    '/messer-unikate/' => ['/klappmesser/', '/sonderanfertigungen/', '/ratgeber/messer-pflege/'],
    '/messerprojekte/' => ['/sonderanfertigungen/', '/messer-unikate/', '/shop/'],
    '/geschenke/' => ['/geschenke/schmiedekurs-gutschein/', '/klappmesser/', '/erlebnisse/'],
    '/geschenke/schmiedekurs-gutschein/' => ['/schmiedekurse/', '/geschenke/', '/erlebnisse/'],
    '/erlebnisse/' => ['/schmiedekurse/', '/geschenke/schmiedekurs-gutschein/', '/schmiedekurse/heilbronn-region/'],
    '/ratgeber/' => ['/ratgeber/damastmesser/', '/ratgeber/messer-pflege/', '/schmiedekurse/heilbronn-region/'],
    '/ratgeber/damastmesser/' => ['/schmiedekurse/', '/klappmesser/', '/ratgeber/messer-pflege/'],
    '/ratgeber/messer-pflege/' => ['/ratgeber/damastmesser/', '/shop/', '/messer-unikate/'],
    '/manufaktur/' => ['/presse/', '/partner/', '/schmiedekurse/heilbronn-region/'],
    '/presse/' => ['/manufaktur/', '/partner/', '/kunsthandwerkermarkt/'],
    '/partner/' => ['/manufaktur/', '/presse/', '/ratgeber/'],
    '/kunsthandwerkermarkt/' => ['/manufaktur/', '/partner/', '/presse/'],
    '/schmiedekurse/grundlagenkurs/' => ['/ratgeber/schmiedekurs-anfaenger/', '/schmiedekurse/klingenkurs/', '/geschenke/schmiedekurs-gutschein/'],
    '/schmiedekurse/klingenkurs/' => ['/schmiedekurse/grundlagenkurs/', '/schmiedekurse/damast-messerkurs/', '/ratgeber/messerschmieden-ablauf/'],
    '/schmiedekurse/klingenkurs-alternativ/' => ['/schmiedekurse/klingenkurs/', '/schmiedekurse/vergleich/', '/ratgeber/schmiedekurs-dauer/'],
    '/schmiedekurse/damast-messerkurs/' => ['/ratgeber/damastmesser/', '/schmiedekurse/damast-premium-kurs/', '/erlebnisse/schmieden-zu-zweit/'],
    '/schmiedekurse/damast-premium-kurs/' => ['/schmiedekurse/damast-messerkurs/', '/ratgeber/damastmesser/', '/schmiedekurse/manager-schmiedekurs/'],
    '/schmiedekurse/manager-schmiedekurs/' => ['/erlebnisse/teamevent/', '/schmiedekurse/damast-premium-kurs/', '/schmiedekurse/vergleich/'],
    '/schmiedekurse/erfahrungen/' => ['/schmiedekurse/vergleich/', '/schmiedekurse/grundlagenkurs/', '/schmiedekurse/damast-messerkurs/'],
    '/erlebnisse/schmieden-zu-zweit/' => ['/geschenke/schmiedekurs-gutschein/', '/schmiedekurse/', '/erlebnisse/teamevent/'],
    '/erlebnisse/teamevent/' => ['/schmiedekurse/manager-schmiedekurs/', '/erlebnisse/', '/schmiedekurse/heilbronn-region/'],
    '/geschenke/schmiedekurs-geburtstag/' => ['/geschenke/schmiedekurs-gutschein/', '/ratgeber/schmiedekurs-anfaenger/', '/geschenke/weihnachten/'],
    '/geschenke/weihnachten/' => ['/geschenke/schmiedekurs-gutschein/', '/klappmesser/', '/geschenke/schmiedekurs-geburtstag/'],
    '/klappmesser/sonderedition/' => ['/klappmesser/', '/messerprojekte/damast-klappmesser-edition-100/', '/sonderanfertigungen/'],
    '/messerprojekte/damast-klappmesser-edition-100/' => ['/klappmesser/', '/klappmesser/sonderedition/', '/messerprojekte/'],
    '/messer-kunst/' => ['/messerprojekte/', '/shop/einzelstuecke/', '/manufaktur/'],
    '/shop/damastmesser/' => ['/ratgeber/damastmesser/', '/klappmesser/', '/shop/einzelstuecke/'],
    '/shop/kuechenmesser/' => ['/ratgeber/welches-kochmesser/', '/ratgeber/messer-pflege/', '/sonderanfertigungen/'],
    '/shop/jagdmesser/' => ['/ratgeber/messer-kaufen/', '/sonderanfertigungen/', '/shop/einzelstuecke/'],
    '/shop/einzelstuecke/' => ['/messer-kunst/', '/messer-unikate/', '/shop/damastmesser/'],
    '/shop/ringe/' => ['/sonderanfertigungen/', '/geschenke/', '/messer-kunst/'],
    '/ratgeber/messerschmieden-ablauf/' => ['/schmiedekurse/', '/ratgeber/schmiedekurs-dauer/', '/ratgeber/damastmesser/'],
    '/ratgeber/schmiedekurs-anfaenger/' => ['/schmiedekurse/grundlagenkurs/', '/ratgeber/schmiedekurs-vorbereitung/', '/ratgeber/schmiedekurs-kosten/'],
    '/ratgeber/schmiedekurs-dauer/' => ['/schmiedekurse/vergleich/', '/ratgeber/schmiedekurs-kosten/', '/schmiedekurse/damast-messerkurs/'],
    '/ratgeber/schmiedekurs-kosten/' => ['/schmiedekurse/vergleich/', '/geschenke/schmiedekurs-gutschein/', '/ratgeber/schmiedekurs-dauer/'],
    '/ratgeber/schmiedekurs-vorbereitung/' => ['/schmiedekurse/buchen/', '/kursbedingungen/', '/ratgeber/schmiedekurs-anfaenger/'],
    '/ratgeber/messer-kaufen/' => ['/shop/', '/ratgeber/welches-kochmesser/', '/sonderanfertigungen/'],
    '/ratgeber/welches-kochmesser/' => ['/shop/kuechenmesser/', '/ratgeber/messer-pflege/', '/ratgeber/messer-kaufen/'],
];
