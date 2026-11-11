# Strukturdaten (JSON-LD) – Übersicht

Stand 11.11.2026. Alle Knoten erzeugt `App\Support\Schema`, die Daten kommen aus `config/ulbrich.php`, der Kursdatenbank und den Inhaltsdateien. Pro Seite gibt es einen `@graph` im `<head>`, mit CSP-Nonce.

| Typ | Seiten | Google-Suchergebnis |
|---|---|---|
| LocalBusiness (+ Person, WebSite) | Startseite, Manufaktur, Region Heilbronn | Knowledge Panel/Unternehmensdaten (zusammen mit dem Google-Unternehmensprofil) |
| WebPage / ProfilePage | alle Seiten; ProfilePage auf `/manufaktur/` und `/en/about/` | Profilseiten-Suchergebnis für Andreas Ulbrich |
| BreadcrumbList | alle Unterseiten | Breadcrumb statt URL im Suchergebnis |
| Course + CourseInstance | 6 Kursseiten | Kurs-Suchergebnisse: Anbieter, Durchführung vor Ort, Dauer, Preis |
| EducationEvent | Kursseiten, **nur bei veröffentlichten freien Terminen** | Veranstaltungs-Suchergebnisse mit Datum, Ort, Preis, Verfügbarkeit |
| ItemList (Course) | Kursübersicht, Vergleich | – (Kontext) |
| Product + Offer | `/klappmesser/`, `/en/folding-knife/` | Produkt-Snippet mit Preis; Versand DE und Garantie hinterlegt |
| Service | Schärfen, Reparatur, Anfertigung (DE/EN) | – (Kontext für Google und KI) |
| Article | 14 Ratgeber | Artikel-Kontext, Herausgeber mit Logo |
| FAQPage | 56 Seiten, je höchstens eine, alle Fragen sichtbar (Test) | FAQ-Suchergebnisse zeigt Google seit 2023 nur noch für Behörden- und Gesundheitsseiten; für KI-Antworten und das Seitenverständnis trotzdem wertvoll |

## Regeln
- Nur sichtbare und belegte Angaben: keine Bewertungen, kein Lagerbestand und kein Datum, das nicht bekannt ist.
- Gründungsdatum = Eröffnung der Manufaktur (1. 3. 2024); 1934 bleibt Familientradition (Test).
- Autor der Ratgeber = die Manufaktur (keine erfundene Einzelautorschaft).
- Veranstaltungen entstehen automatisch aus veröffentlichten Kursterminen mit freien Plätzen (Verfügbarkeit aus der Datenbank).

## Offen
- `availability` der Edition 100 (sobald der Bestandszähler steht)
- `hasMerchantReturnPolicy` (sobald die Widerrufsbelehrung anwaltlich steht) → Voraussetzung für die kostenlosen Google-Shopping-Einträge
- Technische Daten der Edition 100 (Maße, Stahl, Griff), geplant als `additionalProperty`
- `geo` (Koordinaten) und `hasMap`, sobald freigegeben
- Termin des Kunsthandwerkermarkts, geplant als `Event`

## Prüfen nach dem Go-live
- Google Rich Results Test: https://search.google.com/test/rich-results
- Schema Validator: https://validator.schema.org
