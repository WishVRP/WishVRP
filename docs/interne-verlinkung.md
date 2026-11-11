# Interne Verlinkung – Bericht und Regeln

Stand 11.11.2026, gemessen auf dem Entwicklungsserver (62 indexierbare Seiten).

| Kennzahl | vorher | nachher |
|---|---|---|
| Klicktiefe ab Startseite | alle ≤ 2 | alle ≤ 2 |
| Verwaiste Seiten (inkl. Menü/Fußzeile) | 0 | 0 |
| Kontextlinks im Inhalt (ohne Menü, Fußzeile, „Passend dazu“) | 284 | 452 |
| Seiten ohne eingehenden Kontextlink | 4 (Gravur, Firmengeschenke, /en/about/, /en/warranty/) | 0 |
| Eingehende Kontextlinks je Seite (Schnitt) | ~4,6 | 6,5 |
| Linktexte „Mehr“ | 3 | 0 |
| Höchste Linkzahl auf einer Seite | – | 41 (unkritisch) |

Zusätzlich hat jede Unterseite drei Querverweise „Passend dazu“ aus `config/related.php`.

## Technik
- **Links im Fließtext der Inhaltsdateien:** `[Ankertext](/pfad/)`, gerendert von `App\Support\Inline`. Nur Seiten aus `config/pages.php` werden zu Links, alles andere bleibt Text; HTML wird escaped. Im FAQ-Schema erscheint reiner Text.
- **`scripts/interne-links.py`** setzt Links nach festen Regeln:
  - erstes Vorkommen je Begriff
  - höchstens 8 Links pro Seite
  - kein Link auf die eigene Seite oder in Überschriften
  - Wortgrenzen beachten
  - idempotent
- Nach neuen Inhalten einfach erneut ausführen: `python3 scripts/interne-links.py`
- **Tests:** kein rohes Link-Markup sichtbar, alle Ziele existieren, `Inline` escaped und filtert.

## Themencluster

```
Schmiedekurse  /schmiedekurse/
  ├── 6 Kursseiten  ↔ untereinander als nächster Schritt
  ├── /schmiedekurse/vergleich/, /schmiedekurse/erfahrungen/, /schmiedekurse/heilbronn-region/
  └── Ratgeber: Kosten, Dauer, Anfänger, Vorbereitung, Ablauf

Messer kaufen  /shop/
  ├── /shop/damastmesser/, /shop/kuechenmesser/, /shop/jagdmesser/, /shop/einzelstuecke/, /shop/ringe/
  ├── /klappmesser/ (+ Sonderstücke, Messerprojekt)
  ├── /messer-unikate/, /messerprojekte/, /messer-kunst/
  └── Ratgeber: Messerkauf, Messerformen, Echtes Damastmesser, Damaststahl, Preis

Service  /sonderanfertigungen/
  ├── /messer-gravur/, /messer-schaerfen-lassen/, /messer-reparieren-lassen/
  └── /garantie/, /versand/, /ratgeber/messer-pflege/

Geschenke  /geschenke/
  ├── /geschenke/schmiedekurs-gutschein/, /geschenke/weihnachten/, /geschenke/schmiedekurs-geburtstag/
  ├── /erlebnisse/ → /erlebnisse/schmieden-zu-zweit/, /erlebnisse/teamevent/
  └── /firmengeschenke/, /ratgeber/messer-verschenken/

Manufaktur  /manufaktur/
  └── /presse/, /partner/, /kunsthandwerkermarkt/

English  /en/
  └── /en/knives/, /en/folding-knife/, /en/custom-knives/, /en/about/, /en/shipping/, /en/warranty/
```

## Noch mit nur einem eingehenden Kontextlink
Diese Seiten werden zusätzlich über „Passend dazu“ und die Fußzeile verlinkt; mehr Kontextlinks ergeben sich erst mit neuen Inhalten:
- `/partner/` (konkrete Kooperationen fehlen)
- `/schmiedekurse/erfahrungen/` (freigegebene Bewertungen und Kursbilder fehlen)
