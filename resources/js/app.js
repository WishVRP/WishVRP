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
