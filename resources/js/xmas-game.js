/**
 * Weihnachtsaktion: Schneeballschlacht. Eigenes Canvas-Spiel ohne externe Bibliotheken.
 * Steuerung: Tippen/Klicken = Schneeball zum Zielpunkt; Tastatur ← → zielen, Leertaste werfen.
 */
export function startXmasGame(root) {
    const canvas = root.querySelector('canvas');
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const seconds = Number(root.dataset.seconds || 30);
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (s) => root.querySelector(s);
    const scoreEl = $('[data-xmas-score]'), timeEl = $('[data-xmas-time]'), bestEl = $('[data-xmas-best]');
    const startBox = $('[data-xmas-start]'), endBox = $('[data-xmas-end]'), finalEl = $('[data-xmas-final]');

    const store = {
        get() { try { return Number(localStorage.getItem('ulbrich-xmas-best') || 0); } catch { return 0; } },
        set(v) { try { localStorage.setItem('ulbrich-xmas-best', String(v)); } catch { /* privat */ } },
    };
    bestEl.textContent = store.get() || '–';

    const stars = Array.from({ length: 70 }, () => ({ x: Math.random() * W, y: Math.random() * H * 0.6, r: Math.random() * 1.6 + 0.3 }));
    const flakes = reduce ? [] : Array.from({ length: 90 }, () => ({ x: Math.random() * W, y: Math.random() * H, r: Math.random() * 2.5 + 1, v: Math.random() * 30 + 20 }));

    let state = 'idle', score = 0, timeLeft = seconds, last = 0, aimX = W / 2;
    let santa, balls, splats, pops;

    function reset() {
        score = 0; timeLeft = seconds;
        santa = { x: 120, y: 160, dir: 1, speed: reduce ? 140 : 220, t: 0, hitUntil: 0 };
        balls = []; splats = []; pops = [];
        scoreEl.textContent = '0'; timeEl.textContent = String(seconds);
    }

    function throwTo(tx, ty) {
        if (state !== 'play') return;
        const sx = aimX, sy = H - 40;
        const d = Math.hypot(tx - sx, ty - sy) || 1, v = 950;
        balls.push({ x: sx, y: sy, vx: (tx - sx) / d * v, vy: (ty - sy) / d * v, tx, ty });
    }

    function point(event) {
        const r = canvas.getBoundingClientRect();
        return { x: (event.clientX - r.left) / r.width * W, y: (event.clientY - r.top) / r.height * H };
    }

    canvas.addEventListener('pointerdown', (event) => {
        if (state !== 'play') return;
        event.preventDefault();
        const p = point(event);
        aimX = Math.min(W - 40, Math.max(40, p.x));
        throwTo(p.x, p.y);
    });
    root.tabIndex = 0;
    root.addEventListener('keydown', (event) => {
        if (state !== 'play') return;
        if (event.key === 'ArrowLeft') { aimX = Math.max(40, aimX - 40); event.preventDefault(); }
        if (event.key === 'ArrowRight') { aimX = Math.min(W - 40, aimX + 40); event.preventDefault(); }
        if (event.key === ' ' || event.key === 'Enter') { throwTo(aimX, 0); event.preventDefault(); }
    });

    root.querySelectorAll('[data-xmas-play]').forEach((b) => b.addEventListener('click', () => {
        reset(); state = 'play'; startBox.hidden = true; endBox.hidden = true; root.focus({ preventScroll: true });
        last = performance.now(); requestAnimationFrame(loop);
    }));

    function end() {
        state = 'over';
        finalEl.textContent = String(score);
        if (score > store.get()) store.set(score);
        bestEl.textContent = store.get() || '–';
        endBox.hidden = false;
        if (navigator.sendBeacon && navigator.doNotTrack !== '1' && !navigator.globalPrivacyControl) {
            const data = new FormData(); data.append('n', 'spiel'); data.append('p', location.pathname); navigator.sendBeacon('/e/', data);
        }
    }

    function update(dt, now) {
        timeLeft -= dt;
        if (timeLeft <= 0) { timeLeft = 0; timeEl.textContent = '0'; end(); return; }
        timeEl.textContent = String(Math.ceil(timeLeft));

        santa.t += dt;
        santa.x += santa.dir * santa.speed * dt;
        if (santa.x > W - 90) { santa.x = W - 90; santa.dir = -1; }
        if (santa.x < 90) { santa.x = 90; santa.dir = 1; }
        santa.y = 170 + Math.sin(santa.t * (reduce ? 1 : 1.8)) * 80 + Math.sin(santa.t * 0.7) * 30;

        for (const b of balls) {
            b.x += b.vx * dt; b.y += b.vy * dt;
            if (!b.done && now > santa.hitUntil && Math.hypot(b.x - santa.x, b.y - (santa.y - 10)) < 52) {
                b.done = true; score++; scoreEl.textContent = String(score);
                santa.hitUntil = now + 350;
                santa.speed = Math.min(reduce ? 260 : 620, santa.speed + (reduce ? 8 : 18));
                if (Math.random() < 0.35) santa.dir *= -1;
                pops.push({ x: santa.x, y: santa.y - 70, t: 0, text: ['Ho!', 'Ho ho!', 'Brrr!', 'Autsch!'][score % 4] });
                splats.push({ x: b.x, y: b.y, t: 0 });
            }
            const passed = (b.vy <= 0 ? b.y <= b.ty : b.y >= b.ty);
            if (!b.done && (passed || b.y < -20 || b.x < -20 || b.x > W + 20)) { b.done = true; splats.push({ x: b.x, y: b.y, t: 0 }); }
        }
        balls = balls.filter((b) => !b.done);
        splats.forEach((s) => (s.t += dt)); splats = splats.filter((s) => s.t < 0.5);
        pops.forEach((p) => (p.t += dt)); pops = pops.filter((p) => p.t < 0.9);
        flakes.forEach((f) => { f.y += f.v * dt; f.x += Math.sin((f.y + f.r * 50) / 40) * 0.3; if (f.y > H) { f.y = -5; f.x = Math.random() * W; } });
    }

    function drawScene() {
        const g = ctx.createLinearGradient(0, 0, 0, H);
        g.addColorStop(0, '#0b1830'); g.addColorStop(0.7, '#1d3557'); g.addColorStop(1, '#2b4a72');
        ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);
        ctx.fillStyle = '#fff';
        stars.forEach((s) => { ctx.globalAlpha = 0.5 + 0.5 * Math.sin(performance.now() / 700 + s.x); ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, 7); ctx.fill(); });
        ctx.globalAlpha = 1;
        // Mond
        ctx.fillStyle = '#f6f1d5'; ctx.beginPath(); ctx.arc(W - 110, 90, 38, 0, 7); ctx.fill();
        ctx.fillStyle = '#0f203d'; ctx.beginPath(); ctx.arc(W - 96, 82, 34, 0, 7); ctx.fill();
        // Werkstatt-Silhouette mit Esse-Glut
        ctx.fillStyle = '#0c1424';
        ctx.fillRect(110, H - 170, 230, 120); ctx.beginPath(); ctx.moveTo(95, H - 170); ctx.lineTo(225, H - 250); ctx.lineTo(355, H - 170); ctx.fill();
        ctx.fillRect(290, H - 265, 28, 70);
        ctx.fillStyle = '#ffb347'; ctx.globalAlpha = 0.9; ctx.fillRect(150, H - 130, 40, 34); ctx.fillRect(260, H - 130, 40, 34); ctx.globalAlpha = 1;
        ctx.fillStyle = '#0c1424'; ctx.fillRect(W - 300, H - 140, 160, 90); ctx.beginPath(); ctx.moveTo(W - 312, H - 140); ctx.lineTo(W - 220, H - 200); ctx.lineTo(W - 128, H - 140); ctx.fill();
        // Schnee am Boden
        ctx.fillStyle = '#eef4fb'; ctx.beginPath(); ctx.moveTo(0, H - 60);
        for (let x = 0; x <= W; x += 40) ctx.quadraticCurveTo(x + 20, H - 72 + Math.sin(x) * 6, x + 40, H - 60);
        ctx.lineTo(W, H); ctx.lineTo(0, H); ctx.fill();
        ctx.fillStyle = '#fff'; flakes.forEach((f) => { ctx.beginPath(); ctx.arc(f.x, f.y, f.r, 0, 7); ctx.fill(); });
    }

    function drawSanta(now) {
        const { x, y, dir } = santa;
        const hit = now < santa.hitUntil;
        ctx.save(); ctx.translate(x, y); ctx.scale(dir, 1);
        if (hit) ctx.rotate(Math.sin(now / 30) * 0.25);
        // Schlitten
        ctx.fillStyle = '#7a3b12'; ctx.beginPath(); ctx.moveTo(-70, 10); ctx.lineTo(55, 10); ctx.quadraticCurveTo(80, 10, 80, -15); ctx.lineTo(60, -15); ctx.lineTo(55, 30); ctx.lineTo(-60, 30); ctx.closePath(); ctx.fill();
        ctx.strokeStyle = '#c8a24a'; ctx.lineWidth = 5; ctx.beginPath(); ctx.moveTo(-75, 42); ctx.lineTo(60, 42); ctx.quadraticCurveTo(85, 42, 88, 28); ctx.stroke();
        // Sack
        ctx.fillStyle = '#8b6b3e'; ctx.beginPath(); ctx.ellipse(-48, -10, 24, 28, -0.2, 0, 7); ctx.fill();
        // Körper
        ctx.fillStyle = hit ? '#ff4d4d' : '#c1121f'; ctx.beginPath(); ctx.ellipse(5, -18, 30, 32, 0, 0, 7); ctx.fill();
        ctx.fillStyle = '#fff'; ctx.fillRect(-25, -12, 60, 7);
        // Kopf
        ctx.fillStyle = '#f2c9a0'; ctx.beginPath(); ctx.arc(12, -58, 17, 0, 7); ctx.fill();
        // Bart
        ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.moveTo(-4, -56); ctx.quadraticCurveTo(12, -10, 30, -56); ctx.quadraticCurveTo(12, -46, -4, -56); ctx.fill();
        // Mütze
        ctx.fillStyle = '#c1121f'; ctx.beginPath(); ctx.moveTo(-6, -66); ctx.quadraticCurveTo(10, -100, 40, -88); ctx.lineTo(30, -66); ctx.closePath(); ctx.fill();
        ctx.fillStyle = '#fff'; ctx.fillRect(-8, -70, 40, 8); ctx.beginPath(); ctx.arc(42, -88, 6, 0, 7); ctx.fill();
        // Auge
        ctx.fillStyle = '#222'; ctx.beginPath(); ctx.arc(19, -61, hit ? 1 : 2.2, 0, 7); ctx.fill();
        ctx.restore();
    }

    function drawFx() {
        ctx.fillStyle = '#fff';
        balls.forEach((b) => { ctx.beginPath(); ctx.arc(b.x, b.y, 10, 0, 7); ctx.fill(); });
        splats.forEach((s) => { ctx.globalAlpha = 1 - s.t * 2; for (let i = 0; i < 7; i++) { const a = i / 7 * 6.28; ctx.beginPath(); ctx.arc(s.x + Math.cos(a) * s.t * 60, s.y + Math.sin(a) * s.t * 60, 4, 0, 7); ctx.fill(); } });
        ctx.globalAlpha = 1;
        pops.forEach((p) => { ctx.globalAlpha = 1 - p.t; ctx.fillStyle = '#ffd166'; ctx.font = 'bold 30px system-ui, sans-serif'; ctx.textAlign = 'center'; ctx.fillText(p.text, p.x, p.y - p.t * 40); });
        ctx.globalAlpha = 1;
        // Werfer / Ziel
        ctx.fillStyle = '#dbe7f5'; ctx.beginPath(); ctx.arc(aimX, H - 30, 18, Math.PI, 0); ctx.fill();
        ctx.fillStyle = '#fff'; [[-10, -36], [8, -38], [0, -48]].forEach(([dx, dy]) => { ctx.beginPath(); ctx.arc(aimX + dx, H + dy, 7, 0, 7); ctx.fill(); });
    }

    function render(now) { drawScene(); drawSanta(now); drawFx(); }

    function loop(now) {
        const dt = Math.min(0.05, (now - last) / 1000); last = now;
        if (state === 'play') update(dt, now);
        render(now);
        if (state === 'play') requestAnimationFrame(loop);
    }

    reset(); render(performance.now());
}
