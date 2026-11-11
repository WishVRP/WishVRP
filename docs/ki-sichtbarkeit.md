# KI-Sichtbarkeit Messermanufaktur Ulbrich – 11.11.2026

**Methode und Grenzen:** ChatGPT, Gemini, Perplexity und Copilot konnten von hier aus **nicht direkt abgefragt** werden, es gibt kein Monitoring-Tool. Geprüft wurde stattdessen, was eine KI-gestützte Websuche über die Marke findet. Diese Quellen nutzen KI-Assistenten für ihre Antworten. Einschränkung: Das Suchwerkzeug arbeitet mit einem US-Index, deutsche Quellen können unterrepräsentiert sein. Die Live-Domain ist aus dieser Umgebung gesperrt.

## Aktueller Sichtbarkeitswert: ca. 5/100 (Schätzung)

| Dimension | Wert (0–10) | Befund |
|---|---|---|
| Präsenz | 0 | „Messermanufaktur Ulbrich“, „Andreas Ulbrich Messerschmied“ und regionale Kurssuchen liefern **keinen einzigen Treffer** zur Manufaktur |
| Genauigkeit | 1 | Das System **verwechselt** die Marke mit „Ulbrich Stainless Steels“ (Metallfirma, USA) und mit einer anderen Messermanufaktur, deren Schmied Ulrich heißt |
| Stimmung | – | nicht bewertbar (keine Erwähnungen) |
| Position | 0 | Bei „bester Schmiedekurs Region Heilbronn“ nennt die Suche Karlsruhe, Bruchsal, Franken und Schwarzwald, nicht Ulbrich |
| Vollständigkeit | 0 | Tradition seit 1934, Kurse und Edition 100 unbekannt |
| Konsistenz | 1 | nicht bewertbar; die Schreibweise des Orts (Neuenstadt am Kocher / Stein am Kocher) ist noch offen |

## Prompt-Analyse (Websuche als Näherung)

| Prompt | Erwähnt? | Position | Stimmung | Korrekt? |
|---|---|---|---|---|
| „Messermanufaktur Ulbrich“ | Nein (Verwechslung) | – | – | Nein |
| „Andreas Ulbrich Messerschmied Neuenstadt Kocher Schmiedekurs“ | Nein | – | – | – |
| „bester Schmiedekurs Messer Region Heilbronn Hohenlohe“ | Nein | – | – | – |
| „Schmiedekurs Heilbronn Messer schmieden“ | Nein | – | – | – |
| „Damast Schmiedekurs Baden-Württemberg“ | Nein | – | – | – |

## Wettbewerber in KI-/Suchantworten

| Anbieter | Sichtbarkeit | Häufig genannt bei |
|---|---|---|
| Messermanufaktur Ulbrich | sehr niedrig | – |
| gokonfetti (Kursportal) | hoch | Schmiedekurs + Stadt, Gutschein |
| Messer-Schmiedekurs bei Karlsruhe (über gokonfetti) | mittel | Region Baden |
| Nico Runge, Bruchsal (Hornbach-Artikel) | mittel | Schmiedekurs Baden |
| Schmiede im Schwarzwald (Tourismusportal) | mittel | Damastkurs Baden-Württemberg |
| Laurentius-Schmiede / Eisenhammer Eckersmühlen (Franken) | mittel | Messerschmiedekurs Süddeutschland |

**Muster:** KI nennt Anbieter, die auf **Drittquellen** stehen, also Portale, Zeitungen, Tourismus- und Baumarkt-Magazine. Die eigene Website allein reicht nicht.

## Bereits umgesetzt (Website)
- **`/llms.txt`:** Fakten, Kurse, Preise, Seiten, alles aus einer Quelle.
- **robots.txt:** GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, PerplexityBot, Google-Extended, Applebot-Extended und CCBot sind ausdrücklich zugelassen (Test).
- **Schema:**
  - LocalBusiness mit `disambiguatingDescription`, das die Marke eindeutig zuordnet (Messerschmiede, Stein am Kocher, Landkreis Heilbronn, Inhaber).
  - Person mit Tradition und `knowsAbout`.
  - Dazu Course, Product, FAQPage und Article.
- **Kurzprofil „auf einen Blick“** (Manufaktur- und Regionsseite): ein Definitionssatz und eine Faktenliste, gut wörtlich zitierbar.
- FAQ, Vergleichstabelle, Preistabelle, Ratgeber Damast und Pflege mit klaren, extrahierbaren Antworten.

## Maßnahmenplan (Priorität)
1. **Google-Unternehmensprofil vollständig pflegen.** Name exakt „Messermanufaktur Ulbrich“, Kategorie, Website, Fotos, Beschreibung mit „seit 1934, 3. Generation“ und Beiträge. Das ist die Quelle Nr. 1 für lokale KI-Antworten. *Wirkung: hoch*
2. **Pressespiegel online stellen, mit Links zu den Originalartikeln** (11 Artikel). Zeitungen sind starke Drittquellen. Die Artikel sollen die Website verlinken. *Wirkung: hoch*
3. **Einträge auf Drittseiten, wo die Wettbewerber stehen:**
   - Kursportal (z. B. gokonfetti) mit Link zur eigenen Buchung
   - Tourismus Heilbronner Land / Hohenlohe
   - Handwerkskammer-Betriebsprofil
   - Stadt Neuenstadt am Kocher (Gewerbe/Freizeit)

   *Wirkung: hoch*
4. **Einheitliche Schreibweise überall** (NAP und Ort). Erst entscheiden, dann auf Website, Google-Profil, Instagram und Portalen identisch verwenden. Das verhindert weitere Verwechslungen. *Wirkung: mittel–hoch*
5. **`sameAs` ergänzen** (Instagram, Google-Profil, ggf. Facebook). Das verknüpft die Entität. *Wirkung: mittel*
6. **Echte Bewertungen** nach jedem Kurs aktiv erbitten (Google). KI greift Bewertungszahl und Tenor auf. *Wirkung: mittel*
7. **Regionale Presse aktiv ansprechen:** zum Beispiel die Heilbronner Stimme zu „90 Jahre Schmiedehandwerk in der Familie“ (1934 → 2024/25) oder zur Edition 100. *Wirkung: mittel–hoch*
8. **Community:** Antworten in Foren oder Reddit, nur wenn echt und offen als Anbieter. *Wirkung: niedrig–mittel*

## Inhalte, die bestimmte Prompts bedienen
- [x] „Was kostet ein Schmiedekurs?“ → Ratgeber Kosten (Preistabelle)
- [x] „Was ist Damaststahl?“ → Ratgeber Damaststahl
- [x] „Wie pflege ich ein Damastmesser?“ → Ratgeber Messer pflegen
- [x] „Schmiedekurs in der Nähe von Heilbronn“ → Regionsseite mit Kurzprofil
- [ ] „Schmiedekurs als Geschenk“ → Geschenkseite mit Gutscheinbedingungen (Daten fehlen)
- [ ] „Teamevent Schmieden Heilbronn“ → Firmenseite (Gruppengröße/Ablauf fehlen)
- [ ] „Familienbetrieb Messerschmiede seit 1934“ → Familiengeschichte mit Namen und Fotos (Daten fehlen)
- [ ] „Damastmesser kaufen limitiert“ → Edition 100 mit vollständigen Daten

**Erwartung:** KI-Modelle und -Suchen übernehmen Änderungen mit Verzögerung von Wochen bis Monaten. Entscheidend sind die Drittquellen (Punkte 1–3). Einen erneuten Test ist nach dem Go-live und den ersten Einträgen sinnvoll, idealerweise mit einem Monitoring-Tool.
