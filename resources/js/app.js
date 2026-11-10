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
if (!reduceMotion && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
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
