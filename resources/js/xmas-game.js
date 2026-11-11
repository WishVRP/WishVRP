/**
 * Weihnachtsaktion: Schneeballschlacht. Eigenes Canvas-Spiel ohne externe Bibliotheken.
 * Steuerung: Tippen/Klicken = Schneeball zum Zielpunkt; Tastatur ← → zielen, Leertaste werfen.
 * Anspruch: langsame Bälle (vorhalten!), wechselnder Wind, Wolken fangen Bälle ab,
 * der Weihnachtsmann weicht aus und wird schneller, Nachladezeit, max. 2 Bälle in der Luft.
 */
const BALL_SPEED = 620;      // px/s – man muss vorhalten
const COOLDOWN = 0.45;       // s zwischen zwei Würfen
const MAX_IN_AIR = 2;
const HIT_RADIUS = 40;
export function startXmasGame(root) {
    const canvas = root.querySelector('canvas');
    const ctx = canvas.getContext('2d');
    const W = canvas.width, H = canvas.height;
    const seconds = Number(root.dataset.seconds || 30);
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = (s) => root.querySelector(s);
    const scoreEl = $('[data-xmas-score]'), timeEl = $('[data-xmas-time]'), bestEl = $('[data-xmas-best]');
    const startBox = $('[data-xmas-start]'), endBox = $('[data-xmas-end]');

    const store = {
        get() { try { return Number(localStorage.getItem('ulbrich-xmas-best') || 0); } catch { return 0; } },
        set(v) { try { localStorage.setItem('ulbrich-xmas-best', String(v)); } catch { /* privat */ } },
    };
    bestEl.textContent = store.get() || '–';

    const stars = Array.from({ length: 70 }, () => ({ x: Math.random() * W, y: Math.random() * H * 0.6, r: Math.random() * 1.6 + 0.3 }));
    const flakes = reduce ? [] : Array.from({ length: 90 }, () => ({ x: Math.random() * W, y: Math.random() * H, r: Math.random() * 2.5 + 1, v: Math.random() * 30 + 20 }));

    let state = 'idle', score = 0, timeLeft = seconds, last = 0, aimX = W / 2, elapsed = 0, reload = 0;
    let santa, balls, splats, pops, clouds, wind;

    function reset() {
        score = 0; timeLeft = seconds; elapsed = 0; reload = 0;
        santa = { x: 140, y: 170, dir: 1, speed: 200, t: 0, hitUntil: 0, targetY: 170, vy: 0, nextTurn: 1.5, nextY: 1, dodgeReady: 0 };
        balls = []; splats = []; pops = [];
        clouds = [
            { x: 200, y: 150, w: 170, v: 26 },
            { x: 620, y: 250, w: 150, v: -34 },
            { x: 820, y: 120, w: 120, v: 18 },
        ];
        wind = { now: 0, target: 0, next: 4 };
        scoreEl.textContent = '0'; timeEl.textContent = String(seconds);
    }

    function throwTo(tx, ty) {
        if (state !== 'play' || reload > 0 || balls.length >= MAX_IN_AIR) return;
        const sx = aimX, sy = H - 40;
        const d = Math.hypot(tx - sx, ty - sy) || 1;
        balls.push({ x: sx, y: sy, vx: (tx - sx) / d * BALL_SPEED, vy: (ty - sy) / d * BALL_SPEED, tx, ty, life: d / BALL_SPEED });
        reload = COOLDOWN;
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

    const form = $('[data-xmas-form]'), codeForm = $('[data-xmas-code-form]'), errorEl = $('[data-xmas-error]'), goBtn = $('[data-xmas-go]');
    const headline = $('[data-xmas-headline]'), prize = $('[data-xmas-prize]'), codeEl = $('[data-xmas-code]'), note = $('[data-xmas-note]');
    const csrf = $('[data-xmas-csrf]')?.value;
    let token = null, ref = root.dataset.ref || null;
    const step = (n) => root.querySelectorAll('[data-xmas-step]').forEach((el) => { el.hidden = el.dataset.xmasStep !== String(n); });
    const error = (msg) => { errorEl.textContent = msg || ''; errorEl.hidden = !msg; };

    async function post(url, data) {
        const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: data, credentials: 'same-origin' });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(Object.values(json.errors || {}).flat()[0] || json.message || 'Das hat leider nicht geklappt. Bitte versuch es später noch einmal.');
        return json;
    }

    function showResult(r, already) {
        startBox.hidden = true; endBox.hidden = false;
        headline.textContent = already ? 'Du hast schon mitgespielt' : `Ho ho ho! ${r.score} Treffer`;
        if (r.percent > 0) {
            prize.textContent = `${r.percent} % Rabatt`;
            codeEl.hidden = false; codeEl.querySelector('b').textContent = r.code;
            note.textContent = `Einmal einlösbar bis ${root.dataset.until} bei einer Bestellung oder Kursbuchung auf dieser Website – nur mit der E-Mail-Adresse und Handynummer, mit denen du gespielt hast, einmal pro Person bzw. Haushalt.` + (already ? '' : ' Den Code haben wir dir auch per E-Mail geschickt.');
        } else {
            prize.textContent = already ? 'Diesmal gab es leider keinen Rabatt.' : 'Zu wenige Treffer – diesmal kein Rabatt.';
            codeEl.hidden = true;
            note.textContent = (already && r.message) ? r.message : 'Die Aktion gibt es nur einmal pro Person. Danke fürs Mitspielen und frohe Weihnachten!';
        }
    }

    async function requestCode(button) {
        error(''); button.disabled = true;
        try {
            const r = await post(root.dataset.register, new FormData(form));
            if (r.status === 'sent') {
                step('sent'); $('[data-xmas-sent-text]').textContent = r.message;
                const dev = $('[data-xmas-dev-mail]'); dev.hidden = !r.dev_link; if (r.dev_link) dev.querySelector('a').href = r.dev_link;
                return;
            }
            ref = r.ref; step(2);
            $('[data-xmas-sms-text]').textContent = r.message;
            const dev = $('[data-xmas-dev]'); dev.hidden = !r.dev_code; if (r.dev_code) $('[data-xmas-dev-code]').textContent = r.dev_code;
            codeForm.querySelector('input[name=code]').focus();
        } catch (e) { error(e.message); } finally { button.disabled = false; }
    }

    // Schritt 1: E-Mail + Handynummer → SMS-Code
    form.addEventListener('submit', (event) => { event.preventDefault(); requestCode(form.querySelector('button[type=submit]')); });
    $('[data-xmas-resend]').addEventListener('click', (event) => requestCode(event.currentTarget));

    // Schritt 2: Code bestätigen
    codeForm.addEventListener('submit', async (event) => {
        event.preventDefault(); error('');
        const button = codeForm.querySelector('button[type=submit]'); button.disabled = true;
        try {
            const data = new FormData(codeForm); data.append('ref', ref);
            await post(root.dataset.verify, data);
            step(3); goBtn.focus();
        } catch (e) { error(e.message); } finally { button.disabled = false; }
    });

    // Schritt 3: genau ein Spiel
    goBtn.addEventListener('click', async () => {
        error(''); goBtn.disabled = true;
        try {
            const data = new FormData(); data.append('ref', ref);
            const r = await post(root.dataset.start, data);
            if (r.status === 'played') { showResult(r, true); return; }
            token = r.token;
            reset(); state = 'play'; startBox.hidden = true; endBox.hidden = true; root.focus({ preventScroll: true });
            last = performance.now(); requestAnimationFrame(loop);
        } catch (e) { error(e.message); goBtn.disabled = false; }
    });

    async function end() {
        state = 'over';
        if (score > store.get()) store.set(score);
        bestEl.textContent = store.get() || '–';
        headline.textContent = 'Auswertung …'; prize.textContent = '…'; codeEl.hidden = true; note.textContent = '';
        endBox.hidden = false;
        if (navigator.sendBeacon && navigator.doNotTrack !== '1' && !navigator.globalPrivacyControl) {
            const data = new FormData(); data.append('n', 'spiel'); data.append('p', location.pathname); navigator.sendBeacon('/e/', data);
        }
        const data = new FormData(); data.append('token', token); data.append('score', String(score));
        try { showResult(await post(root.dataset.finish, data), false); }
        catch (e) { headline.textContent = 'Ups'; prize.textContent = e.message; }
    }

    function update(dt, now) {
        timeLeft -= dt; elapsed += dt; reload = Math.max(0, reload - dt);
        if (timeLeft <= 0) { timeLeft = 0; timeEl.textContent = '0'; end(); return; }
        timeEl.textContent = String(Math.ceil(timeLeft));

        // Wind wechselt alle paar Sekunden (wird mit der Zeit stärker)
        wind.next -= dt;
        if (wind.next <= 0) {
            const max = 90 + elapsed * 4;
            wind.target = Math.round((Math.random() * 2 - 1) * max);
            wind.next = 3 + Math.random() * 3;
        }
        wind.now += (wind.target - wind.now) * Math.min(1, dt * 1.5);

        // Weihnachtsmann: wird schneller, wechselt unvorhersehbar Richtung und Höhe, weicht Bällen aus
        santa.t += dt;
        const speed = Math.min(560, santa.speed + elapsed * 6);
        santa.nextTurn -= dt;
        if (santa.nextTurn <= 0) { if (Math.random() < 0.55) santa.dir *= -1; santa.nextTurn = 0.8 + Math.random() * 1.8; }
        santa.nextY -= dt;
        if (santa.nextY <= 0) { santa.targetY = 110 + Math.random() * 210; santa.nextY = 0.7 + Math.random() * 1.3; }
        santa.dodgeReady = Math.max(0, santa.dodgeReady - dt);
        if (santa.dodgeReady === 0) {
            const threat = balls.find((b) => b.vy < 0 && Math.abs(b.x - santa.x) < 120 && b.y - santa.y < 170 && b.y > santa.y);
            if (threat && Math.random() < Math.min(0.55, 0.25 + score * 0.02)) {
                santa.vy = (santa.y > 220 ? -1 : 1) * 520; santa.dir *= Math.random() < 0.5 ? -1 : 1; santa.dodgeReady = 0.9;
            }
        }
        santa.vy *= Math.pow(0.02, dt);
        santa.x += santa.dir * speed * dt;
        santa.y += (santa.targetY - santa.y) * Math.min(1, dt * 2.2) + santa.vy * dt + Math.sin(santa.t * 3) * 0.6;
        santa.y = Math.max(95, Math.min(360, santa.y));
        if (santa.x > W - 90) { santa.x = W - 90; santa.dir = -1; }
        if (santa.x < 90) { santa.x = 90; santa.dir = 1; }

        // Wolken ziehen über den Himmel
        clouds.forEach((c) => { c.x += c.v * dt; if (c.x > W + c.w) c.x = -c.w; if (c.x < -c.w) c.x = W + c.w; });
        const inCloud = (x, y) => clouds.some((c) => Math.abs(x - c.x) < c.w * 0.5 && Math.abs(y - c.y) < 34);

        for (const b of balls) {
            b.vx += wind.now * dt;            // Wind treibt seitlich ab
            b.x += b.vx * dt; b.y += b.vy * dt; b.life -= dt;
            if (b.done) continue;
            if (inCloud(b.x, b.y)) { b.done = true; splats.push({ x: b.x, y: b.y, t: 0, soft: true }); continue; }
            if (now > santa.hitUntil && Math.hypot(b.x - santa.x, b.y - (santa.y - 12)) < HIT_RADIUS) {
                b.done = true; score++; scoreEl.textContent = String(score);
                santa.hitUntil = now + 350;
                santa.speed += 10;
                if (Math.random() < 0.5) santa.dir *= -1;
                santa.targetY = 110 + Math.random() * 210;
                pops.push({ x: santa.x, y: santa.y - 70, t: 0, text: ['Ho!', 'Ho ho!', 'Brrr!', 'Autsch!'][score % 4] });
                splats.push({ x: b.x, y: b.y, t: 0 });
                continue;
            }
            if (b.life <= 0 || b.y < -20 || b.x < -20 || b.x > W + 20) { b.done = true; splats.push({ x: b.x, y: b.y, t: 0 }); }
        }
        balls = balls.filter((b) => !b.done);
        splats.forEach((sp) => (sp.t += dt)); splats = splats.filter((sp) => sp.t < 0.5);
        pops.forEach((p) => (p.t += dt)); pops = pops.filter((p) => p.t < 0.9);
        flakes.forEach((f) => { f.y += f.v * dt; f.x += Math.sin((f.y + f.r * 50) / 40) * 0.3 + wind.now * dt * 0.15; if (f.y > H) { f.y = -5; f.x = Math.random() * W; } if (f.x > W) f.x = 0; if (f.x < 0) f.x = W; });
    }

    function drawClouds() {
        clouds.forEach((c) => {
            ctx.fillStyle = 'rgba(214,226,240,0.92)';
            const r = c.w / 4;
            [[-1.2, 6], [-0.4, -10], [0.4, -4], [1.2, 8]].forEach(([k, dy]) => { ctx.beginPath(); ctx.arc(c.x + k * r, c.y + dy, r, 0, 7); ctx.fill(); });
            ctx.fillRect(c.x - c.w * 0.45, c.y, c.w * 0.9, r * 0.9);
        });
    }

    function drawWind() {
        const w = wind.now, n = Math.round(Math.abs(w) / 40);
        ctx.fillStyle = 'rgba(255,255,255,0.85)'; ctx.font = '600 18px system-ui, sans-serif'; ctx.textAlign = 'center';
        ctx.fillText(n === 0 ? 'Windstill' : `Wind ${(w > 0 ? '→' : '←').repeat(Math.min(4, n))}`, W / 2, 62);
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
        ctx.globalAlpha = reload > 0 || balls.length >= MAX_IN_AIR ? 0.35 : 1;
        ctx.fillStyle = '#dbe7f5'; ctx.beginPath(); ctx.arc(aimX, H - 30, 18, Math.PI, 0); ctx.fill();
        ctx.fillStyle = '#fff'; [[-10, -36], [8, -38], [0, -48]].forEach(([dx, dy]) => { ctx.beginPath(); ctx.arc(aimX + dx, H + dy, 7, 0, 7); ctx.fill(); });
        ctx.globalAlpha = 1;
    }

    function render(now) { drawScene(); drawSanta(now); drawClouds(); drawWind(); drawFx(); }

    function loop(now) {
        const dt = Math.min(0.05, (now - last) / 1000); last = now;
        if (state === 'play') update(dt, now);
        render(now);
        if (state === 'play') requestAnimationFrame(loop);
    }

    reset(); render(performance.now());
}
