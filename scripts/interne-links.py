#!/usr/bin/env python3
"""Setzt kontextuelle interne Links in resources/content (Format [Text](/pfad/)).

Regeln: je Seite nur das erste Vorkommen eines Begriffs, höchstens MAX neue Links,
nie auf die eigene Seite, nie in Überschriften, nie doppelt auf dasselbe Ziel.
Idempotent: bereits gesetzte Links werden erkannt.
"""
import json, re, glob, os, sys

MAX = 8
DE = [  # (Regex, Ziel) – Reihenfolge = Priorität
    (r"Damast Premium-Kurs", "/schmiedekurse/damast-premium-kurs/"),
    (r"Damast-Messerkurs", "/schmiedekurse/damast-messerkurs/"),
    (r"Manager-Schmiedekurs", "/schmiedekurse/manager-schmiedekurs/"),
    (r"Grundlagenkurs", "/schmiedekurse/grundlagenkurs/"),
    (r"Klingenkurs(?! „Alternativ“)", "/schmiedekurse/klingenkurs/"),
    (r"Schmiedekurs-Gutschein", "/geschenke/schmiedekurs-gutschein/"),
    (r"Edition 100", "/klappmesser/"),
    (r"Sonderanfertigung(?:en)?", "/sonderanfertigungen/"),
    (r"Gravur(?:en)?", "/messer-gravur/"),
    (r"Firmengeschenk(?:e)?", "/firmengeschenke/"),
    (r"Teamevents?", "/erlebnisse/teamevent/"),
    (r"(?:zwei Plätze|Zwei Plätze)", "/erlebnisse/schmieden-zu-zweit/"),
    (r"2 Jahre Garantie", "/garantie/"),
    (r"7,99 € pro Artikel", "/versand/"),
    (r"(?:Messer )?schärfen lassen", "/messer-schaerfen-lassen/"),
    (r"Reparatur(?:en)?", "/messer-reparieren-lassen/"),
    (r"Messerformen", "/ratgeber/messerformen/"),
    (r"echte[nm]? Damast(?:messer)?", "/ratgeber/echtes-damastmesser-erkennen/"),
    (r"Damaststahl", "/ratgeber/damastmesser/"),
    (r"Pflegehinweise[nm]?", "/ratgeber/messer-pflege/"),
    (r"Küchenmesser", "/shop/kuechenmesser/"),
    (r"Jagdmesser", "/shop/jagdmesser/"),
    (r"Unikate?", "/messer-unikate/"),
    (r"Einzelstücke", "/shop/einzelstuecke/"),
    (r"Ringpaare?", "/shop/ringe/"),
    (r"Münze", "/ratgeber/messer-verschenken/"),
    (r"Messerprojekte", "/messerprojekte/"),
    (r"Familientradition|seit 1934", "/manufaktur/"),
    (r"Weihnachten", "/geschenke/weihnachten/"),
    (r"(?:runde[nm]? )?Geburtstag", "/geschenke/schmiedekurs-geburtstag/"),
    (r"ohne Vorwissen|Einstieg ohne Vorwissen|Anfänger", "/ratgeber/schmiedekurs-anfaenger/"),
    (r"Härten", "/ratgeber/messerschmieden-ablauf/"),
    (r"(?:einen|einem|zwei|drei) Tag(?:en?)? mit (?:jeweils )?etwa acht Stunden", "/ratgeber/schmiedekurs-dauer/"),
    (r"Handwerkskunst", "/messer-kunst/"),
    (r"Sonderstück(?:e|en)?|Sonderausführungen", "/klappmesser/sonderedition/"),
    (r"Damastklingen?", "/shop/damastmesser/"),
    (r"Kooperation(?:en)?", "/partner/"),
    (r"Kurspreise|Preise gelten pro Person", "/ratgeber/schmiedekurs-kosten/"),
    (r"Kleidung und Schuhe|Anreise", "/ratgeber/schmiedekurs-vorbereitung/"),
    (r"Klinge und Griff als Ganzes gestaltet|als Ganzes gestaltet", "/messer-kunst/"),
    (r"was Teilnehmende mit nach Hause nehmen|Kursergebnisse", "/schmiedekurse/erfahrungen/"),
]
EN = [
    (r"Edition 100", "/en/folding-knife/"),
    (r"custom kni(?:fe|ves)", "/en/custom-knives/"),
    (r"2-year warranty|warranty", "/en/warranty/"),
    (r"third generation", "/en/about/"),
    (r"collect your knife|pickup", "/en/shipping/"),
    (r"kitchen knives", "/en/knives/"),
    (r"Andreas Ulbrich", "/en/about/"),
]
LINK = re.compile(r"\[[^\]]+\]\((/[^)]*)\)")

def page_path(file, data, pages_keys):
    base = os.path.basename(file)[:-5]
    if "/extra/" in file:
        return "/" if base == "home" else "/" + base.replace("__", "/") + "/"
    for p, key in pages_keys.items():
        if key == base:
            return p
    if base.startswith("schmiedekurse__"):
        return "/" + base.replace("__", "/") + "/"
    return None

def link_text(text, rules, own, used, budget):
    for rx, target in rules:
        if budget[0] <= 0 or target == own or target in used:
            continue
        # nicht innerhalb bestehender Links ersetzen
        spans = [m.span() for m in LINK.finditer(text)]
        for m in re.finditer(r"(?<!\w)(?:" + rx + r")\w*", text):
            if any(a <= m.start() < b for a, b in spans):
                continue
            text = text[:m.start()] + f"[{m.group(0)}]({target})" + text[m.end():]
            used.add(target); budget[0] -= 1
            break
    return text

def main():
    import subprocess
    keys = json.loads(subprocess.check_output(["php", "-r",
        'echo json_encode(array_map(fn($p) => $p["content"] ?? null, require "config/pages.php"));']))
    changed = 0
    for f in sorted(glob.glob("resources/content/*.json") + glob.glob("resources/content/extra/*.json")):
        d = json.load(open(f))
        own = page_path(f, d, keys)
        if own is None:
            continue
        rules = EN if own.startswith("/en/") else DE
        used = {m.group(1) for s in d.get("sections", []) for i in s["items"] for m in LINK.finditer(i["x"])}
        budget = [MAX - len(used)]
        before = json.dumps(d, ensure_ascii=False)
        for s in d.get("sections", []):
            for i in s["items"]:
                if i["t"] in ("p", "li"):
                    i["x"] = link_text(i["x"], rules, own, used, budget)
        for q in d.get("faq", []):
            q["a"] = link_text(q["a"], rules, own, used, budget)
        if json.dumps(d, ensure_ascii=False) != before:
            json.dump(d, open(f, "w"), ensure_ascii=False, indent=1)
            changed += 1
            print(f"{own:48} {len(used):2} Links")
    print(f"{changed} Dateien geändert")

if __name__ == "__main__":
    main()
