# Keyword-Erweiterung Messerverkauf: Deutschland, Abholung, weltweit

Stand 11.11.2026. Ergänzt `docs/content-briefs-alle.md`, wo der Schwerpunkt auf Kursen und der Region Heilbronn lag.

**Neuer Fakt laut Inhaber:** Ihr verkauft deutschlandweit, weltweit und mit Selbstabholung vor Ort. Das steht jetzt in `config/ulbrich.php → sales`, und die Strukturdaten lesen es von dort:
- Unternehmen: `areaServed`
- Edition 100: Paketversand und Abholung, Versand Deutschland 7,99 €

Für Auslandsversand fehlen noch Länder, Kosten, Zoll und Laufzeit **[liefern]**. Deshalb steht im Schema bisher nur der Versandtarif für Deutschland.

Wie in der ersten Liste ist kein Keyword-Tool angebunden (Ahrefs: keine Verbindung). Die Prioritäten sind geschätzt. **[liefern]** bedeutet: Das Keyword lohnt sich nur, wenn ihr das Produkt oder die Leistung wirklich anbietet.

---

## 1. Deutschlandweit: Produkt-Keywords ohne Ortsbezug (größter Hebel)

Wer ein Messer online kauft, sucht nicht nach „Heilbronn“. Diese Keywords gehören auf Shop-, Kategorie- und Ratgeberseiten. Es entstehen keine neuen Stadtseiten.

| Keyword-Cluster | Beispiele | Zielseite | Prio | Voraussetzung |
|---|---|---|---|---|
| Damastmesser allgemein | damastmesser kaufen, damastmesser handgeschmiedet, echtes damastmesser, damastmesser deutschland | `/shop/damastmesser/` | hoch | echte Produkte mit Preis |
| Damast-Küchenmesser | damast kochmesser, damast küchenmesser, damast santoku, damast gyuto, damast nakiri | `/shop/kuechenmesser/` | hoch | **[liefern]** welche Formen ihr fertigt; nur diese nennen |
| Handarbeit / Herkunft | handgeschmiedetes messer, messer aus deutscher handarbeit, messermanufaktur deutschland, messerschmied online kaufen | `/shop/` + `/manufaktur/` | hoch | – |
| Klappmesser | damast klappmesser, klappmesser handgemacht, taschenmesser damast, limitiertes taschenmesser | `/klappmesser/` | hoch | Edition-Daten (Maße, Stahl, Griff) |
| Jagd / Outdoor | jagdmesser damast, jagdmesser handgeschmiedet, bushcraft messer handgemacht, outdoormesser schmied | `/shop/jagdmesser/` | mittel | **[liefern]** echte Modelle |
| Geschenk Messer | messer als geschenk, hochwertiges messer verschenken, geschenk für männer messer, geschenk koch | `/geschenke/` | hoch | – |
| Anlass | messer zur hochzeit, messer zum ruhestand, messer firmenjubiläum, messer zum 50. geburtstag | `/geschenke/` (Abschnitte, keine Einzelseiten) | mittel | – |
| Personalisierung | messer mit gravur, messer personalisiert, messer mit initialen | `/sonderanfertigungen/` | mittel | **[liefern]** ob Gravur angeboten wird |
| Firmenkunden | messer als firmengeschenk, kundengeschenk hochwertig, mitarbeitergeschenk messer | neuer Abschnitt auf `/sonderanfertigungen/` | mittel | **[liefern]** Stückzahlen, Logo-Gravur ja/nein |
| Ringe | damast ringe, eheringe damast, eheringe handgeschmiedet, ringe vom schmied | `/shop/ringe/` | mittel | **[liefern]** Material, Ablauf, Preisrahmen |
| Service | messer schärfen lassen, messer reparieren lassen, messer neuen griff | eigene Seite nur bei echtem Angebot | niedrig | **[liefern]** ob angeboten |

### Ratgeber-Keywords mit Suchvolumen in ganz Deutschland
Sie führen Leute auf die Seite, die kaufen wollen, und machen die Marke in KI-Antworten sichtbarer.

| Keyword | Zielseite |
|---|---|
| echtes damastmesser erkennen, damast oder geätzt | `/ratgeber/damastmesser/` (neuer Abschnitt) |
| damast vs edelstahl, rostet damastmesser | `/ratgeber/damastmesser/` |
| wie viele lagen damast | `/ratgeber/damastmesser/`. Nur mit echten Lagenzahlen eurer Messer **[liefern]** |
| messer verschenken unglück, messer verschenken münze | `/geschenke/`: beliebte Suchanfrage, als FAQ beantworten (Brauch, keine Tatsachenbehauptung) |
| warum sind handgeschmiedete messer so teuer | `/ratgeber/messer-kaufen/` |
| damastmesser spülmaschine, messer richtig lagern | `/ratgeber/messer-pflege/` |
| welches messer für fleisch / gemüse | `/ratgeber/welches-kochmesser/` |
| messer mitführen gesetz (§ 42a WaffG) | nur mit anwaltlich geprüftem Text, sonst weglassen |

---

## 2. Vor Ort: Abholung und lokaler Kauf

| Keyword | Umsetzung |
|---|---|
| messer kaufen heilbronn, messerladen heilbronn, messergeschäft in der nähe | `/manufaktur/` + Google-Unternehmensprofil (Kategorie „Messerschmied“ / „Messerladen“) |
| messerschmied in der nähe, messermacher heilbronn | wie oben, mit einheitlicher Schreibweise von Name, Adresse und Telefon |
| damastmesser abholen, messer selbst abholen | Abschnitt „Abholung in der Manufaktur“ auf `/shop/` und `/klappmesser/` |

**Umsetzung:**
- Im Shop „Selbstabholung nach Absprache“ sichtbar machen. Im Schema ist die Abholung bereits hinterlegt.
- **[liefern]:** Wie läuft die Abholung ab (Termin per Telefon? Barzahlung vor Ort möglich?), und darf man vorbeikommen und die Messer ansehen? „Messer ansehen vor dem Kauf“ ist ein starkes Argument für Kunden in der Region.

---

## 3. Österreich und Schweiz: gleiche Sprache, keine eigenen Seiten

- Die Keywords sind dieselben wie in Deutschland. Statt eigener Seiten pro Land sollte der Versand nach Österreich und in die Schweiz auf einer Seite **„Versand & Lieferung“** klar beschrieben sein. Für die Schweiz gehören Zoll und Mehrwertsteuer dazu.
- **[liefern]:** Versandkosten AT/CH und ob der Zoll für die Schweiz vorab bezahlt wird (DDP) oder der Kunde ihn zahlt.
- Neue URL `/versand/` (bei Freigabe in `config/pages.php`), Pflichtangaben gemeinsam mit den AGB.

---

## 4. Weltweit: englische Keywords

International gibt es echte Nachfrage nach „German“ und „hand forged“. Deutsche Seiten ranken dort aber nicht. Nötig ist ein **englischer Bereich `/en/`** mit hreflang (de ↔ en), Texten von Menschen statt reiner Maschinenübersetzung und Preisen in EUR mit klaren Versandangaben.

| Keyword-Cluster | Beispiele | Zielseite (neu) | Prio |
|---|---|---|---|
| German handmade | german damascus knife, handmade knife from germany, german bladesmith, german knife maker | `/en/` (Startseite EN) | hoch |
| Damascus folding knife | damascus folding knife, limited edition folding knife, numbered damascus pocket knife | `/en/folding-knife/` (Edition 100) | hoch |
| Damascus kitchen | damascus chef knife handmade, hand forged kitchen knife | `/en/knives/` | mittel |
| Custom | custom knife maker germany, custom damascus knife, commission a knife | `/en/custom-knives/` | mittel |
| Story | third generation blacksmith, family forge since 1934 | `/en/about/` | mittel (stärkt Vertrauen) |
| Kurse für Touristen | knife making class germany, blacksmith course germany english | `/en/forging-courses/` | niedrig, **nur** wenn Andreas Kurse auf Englisch hält **[liefern]** |
| Shipping | international knife shipping, knife shipping to usa / uk | `/en/shipping/` | Pflicht |

**Vor dem Start des englischen Bereichs klären [liefern]:**
- Zielländer. Wegen strenger Einfuhrregeln für Messer nicht einfach „weltweit“ schreiben: z. B. Großbritannien (Altersprüfung bei Lieferung), Australien, einzelne US-Bundesstaaten bei Klappmessern.
- Versandkosten und Laufzeit je Zone, Versicherung, Zoll (wer zahlt?)
- Zahlung aus dem Ausland (Überweisung auf IBAN funktioniert international schlecht, ein Online-Zahlungsanbieter braucht eure Freigabe)
- Rechtstexte auf Englisch (Widerruf, AGB)

**Empfehlung:** Mit sechs englischen Seiten starten: Startseite, Edition 100, Messer, Anfertigung, Über uns, Versand. Kurse auf Englisch nur mit echtem englischsprachigem Angebot. Das kostet wenig Aufwand und bringt am meisten.

---

## 5. Marken-Keywords (sichern)

ulbrich messer, messermanufaktur ulbrich, andreas ulbrich messerschmied, edition 100 klappmesser, ulbrich damast. Die Website rankt dafür schon. Wichtig ist, dass Instagram, Facebook, das Google-Profil und Presseartikel überall gleich verlinken (`sameAs` ist gesetzt).

---

## 6. Kanäle neben Google-Suche (für bundesweiten und weltweiten Verkauf)

| Kanal | Wofür | Voraussetzung |
|---|---|---|
| Google Merchant Center (kostenlose Einträge) | Messer erscheinen im Shopping-Tab für ganz Deutschland | Produktdaten mit Preis, Bild, Versand und Rückgabe. Das Schema liefert Versand DE schon. **Rückgaberegel [liefern]** |
| Google-Unternehmensprofil | lokale Abholung und Kurse | Profil verifizieren, Kategorie, Fotos |
| Etsy / Marktplätze | internationale Sichtbarkeit für Unikate | eure Entscheidung, Preise identisch zur Website |
| Pinterest / Instagram | Damastmuster verkaufen sich stark über Bilder | vorhandene Studiobilder |

---

## 7. Neue Briefs (gleiche Vorlage wie `content-briefs-alle.md`)

### C1 · Geschenke – „messer als geschenk“
- **Seite:** `/geschenke/` · **Intent:** kommerziell · **Wortzahl:** 900–1.200 · Zielgruppe in ganz Deutschland
- **Titles:**
  1. Messer als Geschenk: handgeschmiedet & persönlich | Ulbrich
  2. Hochwertiges Messer verschenken | Ulbrich
  3. Geschenke aus der Messermanufaktur Ulbrich
- **Description:** Ein handgeschmiedetes Messer, die Edition 100 oder ein Schmiedekurs als Geschenk: So findest du das passende Stück, mit Versand in Deutschland oder Abholung vor Ort.
- **Gliederung:**
  - H1 · H2 Drei Geschenkideen (Messer, Kurs, Gutschein)
  - H2 Für welchen Anlass (Hochzeit, Ruhestand, Geburtstag, Firmenjubiläum)
  - H2 Versand oder Abholung
  - H2 FAQ: Bringt es Unglück, ein Messer zu verschenken? (Brauch mit der Münze, als Brauch erklärt) · Wie lange dauert der Versand? (Edition: 7–14 Werktage)
- **Schema:** FAQPage + Breadcrumb

### C2 · Versand & Lieferung – „messer versand“, „damastmesser versand österreich schweiz“
- **Seite:** `/versand/` (neu, braucht Freigabe) · informational/Vertrauen · 500–800 Wörter
- **Gliederung:** H1 · H2 Deutschland (7,99 €, Laufzeit) · H2 Abholung in der Manufaktur · H2 Österreich & Schweiz **[liefern]** · H2 Weltweit **[liefern]** · H2 Zahlung · FAQ
- **Schema:** WebPage + FAQPage. Später als `shippingDetails` je Land in Product.

### C3 · English home – „german damascus knife“
- **Seite:** `/en/` · commercial · 800–1.000 words
- **Titles:**
  1. Handmade German Damascus Knives | Ulbrich
  2. German Bladesmith since 1934 | Ulbrich
  3. Hand Forged Knives from Germany | Ulbrich
- **Gliederung:** H1 · H2 Third generation since 1934 · H2 Edition 100 folding knife · H2 Custom knives · H2 Shipping worldwide **[liefern]** · H2 FAQ
- **Schema:** LocalBusiness (gleiche @id), `inLanguage: en`, hreflang-Paare

### C4 · English Edition 100 – „damascus folding knife limited edition“
- **Seite:** `/en/folding-knife/` · transactional · 700–900 words
- **Voraussetzung:** technische Daten **[liefern]** und internationale Versandkosten. Ohne diese Angaben noch nicht veröffentlichen.
- **Schema:** Product + Offer mit `shippingDetails` je Zielland

---

## Reihenfolge

1. Deutschlandweite Shop-Keywords (Abschnitt 1) und Abholungsabschnitt: sofort möglich, sobald Produkte mit Bild und Preis da sind
2. `/geschenke/` (C1): jetzt vor Weihnachten
3. `/versand/` (C2), sobald AT/CH und Ausland geklärt sind
4. Englischer Bereich (C3, C4 + 4 weitere Seiten), nachdem Zielländer, Zahlung und Rechtstexte geklärt sind

---

## Umsetzung (11.11.2026, nach Angaben des Inhabers)

**Fakten** (`config/ulbrich.php → sales`):
- Versand in Deutschland: 7,99 € pro Artikel
- Ausland: nur bei Übernahme aller Kosten, sonst Selbstabholung
- Zoll: kommt auf den Preis
- Lieferzeit: je nach fertigem Messer oder Sonderanfertigung
- Zahlung: Überweisung, vor Ort bar oder mit Karte; Online-Zahlung folgt nach Freigabe
- Leistungen: alle Formen, Gravur, Schärfen, Reparatur, neuer Griff

**Neue deutsche Seiten:**
- `/versand/`
- `/messer-schaerfen-lassen/` und `/messer-reparieren-lassen/` (mit Service-Schema)
- `/messer-gravur/`
- `/firmengeschenke/`
- `/ratgeber/messerformen/`
- `/ratgeber/echtes-damastmesser-erkennen/`
- `/ratgeber/messer-verschenken/`
- `/ratgeber/handgeschmiedete-messer-preis/`

**Englischer Bereich** (`lang="en"`, hreflang de ↔ en + x-default, englische Navigation und Fußzeile):

| Englische Seite | Deutsches Gegenstück |
|---|---|
| `/en/` | `/` |
| `/en/folding-knife/` | `/klappmesser/` |
| `/en/knives/` | `/shop/` |
| `/en/custom-knives/` | `/sonderanfertigungen/` |
| `/en/about/` | `/manufaktur/` |
| `/en/shipping/` | `/versand/` |

**Bestehende Seiten:**
- Baustein „Versand, Abholung & Zahlung“ auf Shop, Kategorien und Edition 100
- Interne Links von den Ratgebern, der Anfertigung und den Geschenken auf die neuen Seiten
- Neue Links in der Fußzeile

**Strukturdaten:**
- LocalBusiness: `areaServed`, `paymentAccepted`, `currenciesAccepted`, `makesOffer` (Leistungen)
- Edition 100: Lieferarten Paket und Abholung, Versand DE 7,99 €
- llms.txt mit Versand- und Zahlungsregeln

**Offen [liefern]:**
- Preise oder Preisrahmen für Schärfen, Reparatur und Gravur
- Technische Daten der Edition 100
- Ob Messer zum Schärfen per Post angenommen werden
- Rückgaberegel, damit Google-Shopping-Einträge möglich sind
- Altersprüfung beim Versand und Rechtstexte auf Englisch: anwaltlich prüfen lassen

### Nachtrag 11.11.2026 (Angaben des Inhabers)
- **Garantie:** 2 Jahre bei Pflege nach den Pflegehinweisen. Pflegeheft und Pflegemittel sind kostenlos dabei. Bei einem Defekt trotz richtiger Pflege wird repariert oder erstattet. Rost durch die Spülmaschine ist Eigenverschulden, Kulanz nur im Einzelfall.
  - Umgesetzt: `/garantie/`, `/en/warranty/`, Garantie-Kasten im Shop, Garantie und Lieferumfang bei der Edition 100, `WarrantyPromise` im Produkt-Schema
  - **Anwaltlich prüfen:** Formulierung der Garantieerklärung (§ 479 BGB)
- **Schärfen per Post:** möglich, steht auf `/messer-schaerfen-lassen/` und in llms.txt
- **Noch offen:**
  - Preisspannen für Schärfen, Reparatur und Gravur (Inhaber klärt)
  - Technische Daten der Edition 100 (kommen)
  - Rückgaberegel für Google Shopping: Gemeint ist das Widerrufs- bzw. Rückgaberecht (z. B. 14 Tage), nicht die Garantie. Erst eintragen, wenn die Widerrufsbelehrung anwaltlich steht.
  - Rechtstexte auf Englisch (anwaltlich)
