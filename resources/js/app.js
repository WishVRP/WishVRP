// Einziges JavaScript der öffentlichen Seite: Menü auf kleinen Bildschirmen.
// Ohne JS ist die Navigation immer sichtbar (.no-js in base/sections.css).
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
