#!/usr/bin/env python3
"""
Erzeugt docs/vorschau/startseite.html: alle Seiten in EINER Datei (ohne Server
nutzbar, auch auf dem Handy). Interne Links werden zu #/pfad/, Bilder einmal
eingebettet. Voraussetzung: `php artisan serve` läuft auf 127.0.0.1:8000 und
`npm run build` ist aktuell.
Aufruf: python3 scripts/vorschau-erzeugen.py
"""
import base64, json, os, re, subprocess, urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PUB = os.path.join(ROOT, 'public')
BASE = 'http://127.0.0.1:8000'
OUT = os.path.join(ROOT, 'docs/vorschau/startseite.html')

paths = json.loads(subprocess.check_output(['php', '-r', 'echo json_encode(array_keys(require "config/pages.php"));'], cwd=ROOT))

def fetch(path):
    return urllib.request.urlopen(BASE + path).read().decode().replace(BASE + '/', '/')

def b64(path, mime):
    with open(os.path.join(PUB, path.lstrip('/').split('?')[0]), 'rb') as f:
        return f'data:{mime};base64,' + base64.b64encode(f.read()).decode()

images = {}  # Pfad → id

def img_id(src):
    if src not in images:
        images[src] = f'i{len(images)}'
    return images[src]

def rewrite_main(html, path):
    m = re.search(r'<main[^>]*>(.*)</main>', html, re.S)
    body = m.group(1)
    # Bilder: WebP 800 (oder größte vorhandene), einmal eingebettet
    body = re.sub(r'<source type="image/(webp|avif)"[^>]*>', '', body)
    def img(mm):
        tag = mm.group(0)
        srcset = re.search(r'srcset="([^"]+)"', tag).group(1)
        files = [s.strip().split(' ')[0] for s in srcset.split(',')]
        pick = next((f for f in files if '-800.' in f), files[-1]).replace('.jpg', '.webp')
        tag = re.sub(r'\s(srcset|sizes|loading|fetchpriority)="[^"]*"', '', tag)
        return re.sub(r'src="[^"]+"', f'data-img="{img_id(pick)}" src=""', tag)
    body = re.sub(r'<img [^>]*src="/images/fotos/[^>]*>', img, body)
    return body

def rewrite_links(html, path):
    known = set(paths)
    def href(mm):
        url = mm.group(1)
        if url.startswith('#'):
            return f'href="#{path}{url}"'
        target, _, anchor = url.partition('#')
        target = target.split('?')[0]
        if target in known:
            return f'href="#{target}' + (f'#{anchor}' if anchor else '') + '"'
        return mm.group(0)
    return re.sub(r'href="(#[^"]*|/[^"]*)"', href, html)

home = fetch('/')
# Grundgerüst aus der Startseite: Kopf, Header, Footer, Skripte
css_href = re.search(r'<link[^>]+rel="stylesheet"[^>]+href="([^"]+)"', home).group(1)
css = open(os.path.join(PUB, css_href.lstrip('/'))).read()
css_dir = os.path.dirname(css_href)
def css_url(mm):
    u = mm.group(1).strip('\'"')
    if u.startswith('data:'):
        return mm.group(0)
    p = u if u.startswith('/') else os.path.normpath(css_dir + '/' + u)
    if p.endswith(('.woff', '.ttf')):
        return 'url()'
    mime = 'font/woff2' if p.endswith('woff2') else 'image/svg+xml' if p.endswith('svg') else 'application/octet-stream'
    return f'url("{b64(p, mime)}")'
css = re.sub(r'url\(([^)]+)\)', css_url, css)
js_src = re.search(r'<script[^>]+src="(/build/[^"]+)"', home).group(1)
js = open(os.path.join(PUB, js_src.lstrip('/'))).read()

routes = []
titles = {}
for path in paths:
    html = home if path == '/' else fetch(path)
    titles[path] = re.search(r'<title>([^<]+)</title>', html).group(1)
    routes.append(f'<div class="route" data-route="{path}" hidden>' + rewrite_links(rewrite_main(html, path), path) + '</div>')

head = re.search(r'<head>(.*)</head>', home, re.S).group(1)
head = re.sub(r'<link[^>]+(stylesheet|preload|modulepreload|icon|manifest|apple-touch-icon)[^>]*>', '', head)
head = re.sub(r'<script[^>]*>.*?</script>', '', head, flags=re.S)
head = re.sub(r'<meta http-equiv="Content-Security-Policy"[^>]*>', '', head)
shell_top = re.search(r'<body>(.*)<main', home, re.S).group(1)
shell_bottom = re.search(r'</main>(.*)</body>', home, re.S).group(1)
shell_bottom = re.sub(r'<script[^>]*>.*?</script>', '', shell_bottom, flags=re.S)

img_map = {i: b64(src, 'image/webp') for src, i in images.items()}
router = """
const IMG = %s, TITLES = %s;
function show() {
  const raw = location.hash.slice(1) || '/';
  const [path, anchor] = raw.startsWith('/') ? [raw.split('#')[0] || '/', raw.split('#')[1]] : [null, raw];
  if (path === null) return;
  let found = false;
  document.querySelectorAll('.route').forEach(r => {
    const on = r.dataset.route === path; r.hidden = !on; found = found || on;
    if (on) r.querySelectorAll('img[data-img]').forEach(i => { if (!i.getAttribute('src')) i.src = IMG[i.dataset.img]; });
  });
  if (!found) { location.hash = '#/'; return; }
  document.title = TITLES[path] || document.title;
  const el = anchor && document.querySelector('.route[data-route="' + path + '"] #' + CSS.escape(anchor));
  el ? el.scrollIntoView({ behavior: 'instant' }) : window.scrollTo({ top: 0, behavior: 'instant' });
}
window.addEventListener('hashchange', show); show();
""" % (json.dumps(img_map), json.dumps(titles, ensure_ascii=False))

doc = ('<!DOCTYPE html><html lang="de" class="js"><head>' + head + f'<style>{css}</style></head><body>'
       + rewrite_links(shell_top, '/') + '<main id="inhalt" tabindex="-1">' + ''.join(routes) + '</main>'
       + rewrite_links(shell_bottom, '/') + f'<script>{router}</script><script type="module">{js}</script></body></html>')
with open(OUT, 'w') as f:
    f.write(doc)
print(f'{len(paths)} Seiten, {len(images)} Bilder, {len(doc) // 1024} KB → {OUT}')
