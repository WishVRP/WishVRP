// Öffentliches JavaScript: Menü, Kopfzeile, Scroll-Reveal, Kurs-Finder.
// Alles ist Progressive Enhancement – ohne JS sind alle Inhalte sichtbar.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---------- Menü ---------- */
const toggle = document.querySelector('[data-nav-toggle]');
const nav = document.querySelector('[data-nav]');

if (toggle && nav) {
    const label = toggle.querySelector('[data-nav-label]');

    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        nav.dataset.open = String(open);
        if (label) label.textContent = open ? 'Schließen' : 'Menü';
        document.body.style.overflow = open ? 'hidden' : '';
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    nav.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
            toggle.focus();
        }
    });
    window.matchMedia('(min-width: 64rem)').addEventListener('change', (mq) => {
        if (mq.matches) setOpen(false);
    });
}

/* ---------- Kopfzeile: dichter beim Scrollen ---------- */
const header = document.querySelector('[data-header]');
if (header) {
    const onScroll = () => { header.dataset.scrolled = String(window.scrollY > 24); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/* ---------- Scroll-Reveal ---------- */
const revealables = document.querySelectorAll('.reveal');
if (!reduceMotion && 'IntersectionObserver' in window && revealables.length) {
    // Erst jetzt verstecken – lädt das Skript nicht, bleibt alles sichtbar.
    document.documentElement.classList.add('reveal-ready');
    // Sicherheitsnetz: was schon im oder über dem Sichtbereich liegt, sofort zeigen
    window.setTimeout(() => {
        revealables.forEach((el) => {
            if (el.getBoundingClientRect().top < window.innerHeight) el.classList.add('is-visible');
        });
    }, 2500);
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                // In seitlich wischbaren Leisten alle Elemente gemeinsam einblenden
                const row = entry.target.closest('.process__track');
                const items = row ? row.querySelectorAll('.reveal') : [entry.target];
                items.forEach((el) => { el.classList.add('is-visible'); io.unobserve(el); });
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealables.forEach((el) => io.observe(el));
} else {
    revealables.forEach((el) => el.classList.add('is-visible'));
}

/* ---------- Kurs-Finder ---------- */
const filters = document.querySelector('[data-finder-filters]');
const list = document.querySelector('[data-finder-list]');
const count = document.querySelector('[data-finder-count]');

if (filters && list) {
    filters.addEventListener('change', (event) => {
        const value = event.target.value;
        let visible = 0;
        list.querySelectorAll('[data-category]').forEach((item) => {
            const show = value === 'alle' || item.dataset.category === value;
            item.hidden = !show;
            if (show) {
                visible += 1;
                item.classList.add('is-visible');
            }
        });
        if (count) count.textContent = visible === 1 ? '1 Kurs' : `${visible} Kurse`;
    });
}

/* ---------- Fotos: Aufdecken beim Scrollen ---------- */
const photos = document.querySelectorAll('.media--photo:not(.hero__media)');
if (!reduceMotion && 'IntersectionObserver' in window) {
    document.documentElement.classList.add('photos-animate');
    const pio = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const row = entry.target.closest('[data-gallery]');
                const items = row ? row.querySelectorAll('.media--photo') : [entry.target];
                items.forEach((el) => { el.classList.add('is-shown'); pio.unobserve(el); });
            }
        });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.05 });
    photos.forEach((el) => pio.observe(el));
}

/* ---------- Zahlen zählen hoch ---------- */
const counters = document.querySelectorAll('[data-count]');
if (!reduceMotion && 'IntersectionObserver' in window) {
    const cio = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            cio.unobserve(el);
            const target = parseFloat(el.dataset.count.replace(',', '.'));
            const decimals = (el.dataset.count.split(',')[1] || '').length;
            const final = el.textContent;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min((now - start) / 1400, 1);
                const eased = 1 - Math.pow(1 - t, 3);
                el.textContent = (target * eased).toFixed(decimals).replace('.', ',');
                if (t < 1) requestAnimationFrame(tick);
                else el.textContent = final;
            };
            requestAnimationFrame(tick);
        });
    }, { threshold: 0.6 });
    counters.forEach((el) => cio.observe(el));
}

/* ---------- Lichtkegel auf Kacheln (folgt dem Zeiger) ---------- */
if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('[data-spotlight]').forEach((el) => {
        el.addEventListener('pointermove', (event) => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${((event.clientX - r.left) / r.width) * 100}%`);
            el.style.setProperty('--my', `${((event.clientY - r.top) / r.height) * 100}%`);
        });
    });
}

/* ---------- Galerie: Pfeile, Tastatur, Ziehen ---------- */
const track = document.querySelector('[data-gallery]');
const galleryNav = document.querySelector('[data-gallery-nav]');
if (track && galleryNav) {
    galleryNav.hidden = false;
    const step = () => (track.querySelector('.gallery__item')?.getBoundingClientRect().width || 300) + 24;
    const go = (dir) => track.scrollBy({ left: dir * step(), behavior: reduceMotion ? 'auto' : 'smooth' });
    galleryNav.querySelector('[data-gallery-prev]').addEventListener('click', () => go(-1));
    galleryNav.querySelector('[data-gallery-next]').addEventListener('click', () => go(1));
    track.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') { event.preventDefault(); go(1); }
        if (event.key === 'ArrowLeft') { event.preventDefault(); go(-1); }
    });

    let down = false; let startX = 0; let startScroll = 0; let moved = false;
    track.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse') return;
        down = true; moved = false; startX = event.clientX; startScroll = track.scrollLeft;
        track.classList.add('is-dragging');
    });
    window.addEventListener('pointermove', (event) => {
        if (!down) return;
        const dx = event.clientX - startX;
        if (Math.abs(dx) > 4) moved = true;
        track.scrollLeft = startScroll - dx;
    });
    window.addEventListener('pointerup', () => { down = false; track.classList.remove('is-dragging'); });
    track.addEventListener('click', (event) => { if (moved) event.preventDefault(); }, true);
    track.addEventListener('dragstart', (event) => event.preventDefault());
}

/* ---------- Funken über der Esse (Hero) ---------- */
const sparkCanvas = document.querySelector('[data-sparks]');
if (sparkCanvas && !reduceMotion) {
    const ctx = sparkCanvas.getContext('2d');
    const sparks = [];
    let w = 0; let h = 0; let running = false; let raf = 0;
    const desktop = window.matchMedia('(min-width: 64rem)');

    const resize = () => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        w = sparkCanvas.clientWidth; h = sparkCanvas.clientHeight;
        sparkCanvas.width = w * dpr; sparkCanvas.height = h * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    };

    // Glutnest: rechts im Bild (Desktop) bzw. rechts im oberen Fotoband (mobil)
    const source = () => desktop.matches
        ? { x: w * (0.82 + Math.random() * 0.16), y: h * (0.55 + Math.random() * 0.25) }
        : { x: w * (0.7 + Math.random() * 0.28), y: Math.min(h, w * 1.25) * (0.42 + Math.random() * 0.2) };

    const spawn = () => {
        const { x, y } = source();
        sparks.push({
            x, y,
            vx: (Math.random() - 0.6) * 0.6,
            vy: -(0.6 + Math.random() * 1.6),
            life: 0,
            max: 70 + Math.random() * 110,
            size: 0.6 + Math.random() * 1.6,
            hue: 28 + Math.random() * 22,
        });
    };

    const frame = () => {
        ctx.clearRect(0, 0, w, h);
        ctx.globalCompositeOperation = 'lighter';
        if (sparks.length < (desktop.matches ? 70 : 35)) { spawn(); if (Math.random() > 0.5) spawn(); }
        for (let i = sparks.length - 1; i >= 0; i -= 1) {
            const s = sparks[i];
            s.life += 1;
            s.vx += (Math.random() - 0.5) * 0.08;
            s.vy -= 0.005;
            s.x += s.vx; s.y += s.vy;
            const k = 1 - s.life / s.max;
            if (k <= 0) { sparks.splice(i, 1); continue; }
            ctx.beginPath();
            ctx.fillStyle = `hsla(${s.hue}, 100%, ${55 + k * 25}%, ${k})`;
            ctx.shadowColor = `hsla(${s.hue}, 100%, 55%, ${k})`;
            ctx.shadowBlur = 8;
            ctx.arc(s.x, s.y, s.size * (0.5 + k * 0.5), 0, Math.PI * 2);
            ctx.fill();
        }
        raf = requestAnimationFrame(frame);
    };

    const start = () => { if (!running) { running = true; raf = requestAnimationFrame(frame); } };
    const stop = () => { running = false; cancelAnimationFrame(raf); };

    resize();
    window.addEventListener('resize', resize, { passive: true });
    new IntersectionObserver(([entry]) => (entry.isIntersecting ? start() : stop())).observe(sparkCanvas);
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
}

/* ---------- Buchung: Kurs, Kalender (ein Kurs pro Tag), Personen, Gesamtpreis ---------- */
const euro = (n) => `${new Intl.NumberFormat('de-DE', { maximumFractionDigits: 2 }).format(n)} €`;
const booking = document.querySelector('[data-booking]');
const summary = document.querySelector('[data-booking-summary]');
if (booking && summary) {
    const persons = booking.querySelector('[data-persons]');
    const max = parseInt(persons.dataset.max, 10) || 6;
    const input = booking.querySelector('[data-cal-input]');
    const grid = booking.querySelector('[data-cal-grid]');
    const title = booking.querySelector('[data-cal-title]');
    const chosen = booking.querySelector('[data-cal-chosen]');
    const first = booking.dataset.first, lastDay = booking.dataset.last;
    const set = (sel, text) => { const el = summary.querySelector(sel); if (el) el.textContent = text; };
    const fmt = (iso) => new Date(`${iso}T12:00:00`).toLocaleDateString('de-DE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    let month = (input.value || first).slice(0, 7);
    let cache = {};

    const course = () => booking.querySelector('input[name="kurs"]:checked');
    const update = () => {
        const co = course();
        const n = Math.min(max, Math.max(1, parseInt(persons.value, 10) || 1));
        persons.value = n;
        if (!co) return;
        const price = parseFloat(co.dataset.price);
        set('[data-sum-name]', co.dataset.name);
        set('[data-sum-duration]', co.dataset.duration || '–');
        set('[data-sum-persons]', String(n));
        set('[data-sum-price]', euro(price));
        set('[data-sum-total]', euro(price * n));
        set('[data-sum-date]', input.value ? fmt(input.value) : '–');
    };

    async function load() {
        const co = course(); if (!co) return;
        const [y, m] = month.split('-').map(Number);
        title.textContent = new Date(y, m - 1, 1).toLocaleDateString('de-DE', { month: 'long', year: 'numeric' });
        const key = `${co.value}|${month}`;
        if (!cache[key]) {
            grid.innerHTML = '<p class="cal__loading">Lädt …</p>';
            try { cache[key] = await (await fetch(`${booking.dataset.availability}?kurs=${encodeURIComponent(co.value)}&monat=${month}`, { headers: { Accept: 'application/json' } })).json(); }
            catch { grid.innerHTML = '<p class="cal__loading">Kalender konnte nicht geladen werden.</p>'; return; }
        }
        const days = cache[key].days || {};
        const lead = (new Date(y, m - 1, 1).getDay() + 6) % 7;
        grid.innerHTML = '';
        for (let i = 0; i < lead; i++) grid.appendChild(document.createElement('span'));
        Object.entries(days).forEach(([iso, state]) => {
            const b = document.createElement('button');
            b.type = 'button'; b.textContent = String(Number(iso.slice(8)));
            b.className = `cal__day cal__day--${state}` + (iso === input.value ? ' is-selected' : '');
            b.disabled = state !== 'frei';
            b.setAttribute('aria-label', `${fmt(iso)}: ${state}`);
            b.addEventListener('click', () => {
                input.value = iso;
                grid.querySelectorAll('.is-selected').forEach((x) => x.classList.remove('is-selected'));
                b.classList.add('is-selected');
                const d = parseInt(course().dataset.days, 10) || 1;
                chosen.textContent = d > 1 ? `Gewählt: ${fmt(iso)} (${d} Kurstage)` : `Gewählt: ${fmt(iso)}`;
                update();
            });
            grid.appendChild(b);
        });
        booking.querySelector('[data-cal-prev]').disabled = month <= first.slice(0, 7);
        booking.querySelector('[data-cal-next]').disabled = month >= lastDay.slice(0, 7);
    }
    const shift = (dir) => { const [y, m] = month.split('-').map(Number); const d = new Date(y, m - 1 + dir, 1); month = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`; load(); };
    booking.querySelector('[data-cal-prev]').addEventListener('click', () => shift(-1));
    booking.querySelector('[data-cal-next]').addEventListener('click', () => shift(1));
    booking.addEventListener('change', (e) => {
        if (e.target.name === 'kurs') { input.value = ''; chosen.textContent = 'Noch kein Tag gewählt.'; load(); }
        update();
    });
    persons.addEventListener('input', update);
    booking.querySelectorAll('[data-step]').forEach((btn) => btn.addEventListener('click', () => {
        persons.value = (parseInt(persons.value, 10) || 1) + parseInt(btn.dataset.step, 10);
        update();
    }));
    update(); load();
}

/* ---------- Gutschein-Vorschau ---------- */
const voucherBuilder = document.querySelector('[data-voucher]');
if (voucherBuilder) {
    const card = voucherBuilder.querySelector('.voucher');
    const nameInput = voucherBuilder.querySelector('[data-voucher-name]');
    const out = (sel) => voucherBuilder.querySelector(sel);
    const update = (animate) => {
        const course = voucherBuilder.querySelector('input[name="gutschein-kurs"]:checked');
        if (course) {
            const price = parseInt(course.dataset.price, 10);
            out('[data-vp-course]').textContent = course.dataset.name;
            out('[data-vp-meta]').textContent = course.dataset.duration || '';
            out('[data-vp-price]').textContent = price ? `ab ${euro(price)}` : 'je nach Kurs';
        }
        out('[data-vp-name]').textContent = nameInput.value.trim() || '…';
        if (animate && !reduceMotion) { card.classList.remove('is-updated'); void card.offsetWidth; card.classList.add('is-updated'); }
    };
    voucherBuilder.addEventListener('change', () => update(true));
    nameInput.addEventListener('input', () => update(false));
    update(false);
}

/* ---------- Zeitstrahl zeichnen ---------- */
document.querySelectorAll('[data-timeline]').forEach((tl) => {
    if (!('IntersectionObserver' in window)) { tl.classList.add('is-drawn'); return; }
    new IntersectionObserver(([entry], obs) => {
        if (entry.isIntersecting) { tl.classList.add('is-drawn'); obs.disconnect(); }
    }, { threshold: 0.3 }).observe(tl);
});

/* ---------- Erlebnis-Geschichte: Kapitel wechseln Bild und Fortschritt ---------- */
const story = document.querySelector('[data-story]');
if (story && 'IntersectionObserver' in window) {
    story.dataset.storyReady = 'true';
    const steps = story.querySelectorAll('[data-story-step]');
    const imgs = story.querySelectorAll('[data-story-img]');
    const bar = story.querySelector('[data-story-bar]');
    const activate = (i) => {
        steps.forEach((s, n) => s.classList.toggle('is-active', n === i));
        imgs.forEach((img, n) => img.classList.toggle('is-active', n === i));
        if (bar) bar.style.width = `${((i + 1) / steps.length) * 100}%`;
    };
    activate(0);
    const sio = new IntersectionObserver((entries) => {
        entries.forEach((entry) => { if (entry.isIntersecting) activate(Number(entry.target.dataset.storyStep)); });
    }, { rootMargin: '-45% 0px -45% 0px' });
    steps.forEach((s) => sio.observe(s));
}


/* ---------- Klick-Zählung (eigener Server, ohne Cookies; respektiert DNT/GPC) ---------- */
(() => {
    if (navigator.doNotTrack === '1' || navigator.globalPrivacyControl || !navigator.sendBeacon) return;
    const kind = (a) => {
        const href = a.getAttribute('href') || '';
        if (href.startsWith('tel:')) return 'telefon';
        if (href.startsWith('mailto:')) return 'email';
        if (href.startsWith('/schmiedekurse/buchen/')) return 'buchen';
        if (href.startsWith('/geschenke/schmiedekurs-gutschein/')) return 'gutschein';
        if (/^https?:\/\//.test(href) && !href.includes(location.host)) return 'extern';
        return null;
    };
    document.addEventListener('click', (event) => {
        const a = event.target.closest('a[href]');
        const n = a && kind(a);
        if (!n) return;
        const data = new FormData();
        data.append('n', n);
        data.append('p', location.pathname);
        if (n === 'extern') data.append('t', a.href);
        navigator.sendBeacon('/e/', data);
    }, { capture: true });
})();


/* ---------- Edition-Raster: angetipptes Feld unter dem Raster anzeigen (Handy) ---------- */
document.querySelectorAll('[data-serials]').forEach((box) => {
    const out = box.querySelector('[data-serial-out]');
    const show = (event) => { const b = event.target.closest('.serial'); if (b && out) out.textContent = b.dataset.tip; };
    const target = box.dataset.selectTarget ? document.querySelector(box.dataset.selectTarget) : null;
    box.addEventListener('click', (event) => {
        show(event);
        const b = event.target.closest('.serial[data-number]');
        if (!b) return;
        if (target) {
            target.value = b.dataset.number;
            box.querySelectorAll('.is-selected').forEach((li) => li.classList.remove('is-selected'));
            b.parentElement.classList.add('is-selected');
            if (out) out.textContent = `Nr. ${b.dataset.number} ausgewählt – unten im Formular reservieren.`;
        } else {
            location.href = `/klappmesser/?nummer=${b.dataset.number}#anfrage`;
        }
    });
    box.addEventListener('focusin', show);
    box.addEventListener('mouseover', show);
});


/* ---------- Weihnachtsaktion (lädt das Spiel nur auf der Aktionsseite) ---------- */
const xmasRoot = document.querySelector('[data-xmas-game]');
if (xmasRoot) {
    import('./xmas-game.js').then(({ startXmasGame }) => startXmasGame(xmasRoot));
}
