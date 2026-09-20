<?php
/**
 * ============================================================
 * LAYOUT AUTENTIKASI — ULTIMATE EDITION v7.0
 * Fondasi halaman login dengan sinematik visual, partikel,
 * mesh gradient, spotlight, dan efek interaktif premium.
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f1f">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Masuk') ?> • <?= e(APP_NAME) ?></title>

    <!-- Favicon inline SVG bermerek -->
    <?php $customFav = setting('favicon'); ?>
    <?php if ($customFav !== ''): ?>
        <link rel="icon" href="<?= url('assets/uploads/brand/' . e($customFav)) ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%22 stop-color=%22%236366f1%22/><stop offset=%221%22 stop-color=%22%2322d3ee%22/></linearGradient></defs><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22url(%23g)%22/><text x=%2250%22 y=%2268%22 font-family=%22Arial%22 font-size=%2256%22 font-weight=%22900%22 fill=%22white%22 text-anchor=%22middle%22>OU</text></svg>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=7.0">

    <!-- ========== AUTH-SPECIFIC STYLING (v7.0) ========== -->
    <style>
        body.auth-body {
            margin: 0; padding: 0; min-height: 100vh;
            background: var(--bg-0);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--txt-0);
            overflow-x: hidden;
        }

        /* ---- Back to home link ---- */
        .back-home {
            position: fixed; top: 22px; left: 26px; z-index: 10;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 9px 16px; border-radius: 99px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-brd);
            backdrop-filter: blur(14px);
            color: var(--txt-1); font-size: 13px; font-weight: 700;
            text-decoration: none;
            transition: all .25s cubic-bezier(.22,1,.36,1);
        }
        .back-home:hover {
            color: #fff; border-color: var(--pri);
            background: rgba(99, 102, 241, 0.15);
            transform: translateX(-3px);
            box-shadow: 0 10px 30px rgba(99,102,241,.2);
        }
        .back-home i { font-size: 16px; transition: transform .25s; }
        .back-home:hover i { transform: translateX(-3px); }

        /* ---- Canvas particles ---- */
        .auth-particles {
            position: fixed; inset: 0; z-index: 0;
            pointer-events: none; opacity: 0.6;
        }

        /* ---- Mesh gradient orbs ---- */
        .auth-mesh {
            position: fixed; inset: 0; z-index: 0;
            pointer-events: none; overflow: hidden;
        }
        .mesh-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: orb-float 20s ease-in-out infinite;
        }
        .mesh-orb-1 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(99,102,241,.4), transparent 70%);
            top: -10%; left: -10%;
            animation-delay: 0s;
        }
        .mesh-orb-2 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(34,211,238,.3), transparent 70%);
            bottom: -15%; right: -10%;
            animation-delay: -5s;
        }
        .mesh-orb-3 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(139,92,246,.25), transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
        }
        .mesh-orb-4 {
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(16,185,129,.2), transparent 70%);
            top: 20%; right: 20%;
            animation-delay: -15s;
        }
        @keyframes orb-float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -40px) scale(1.05); }
            50% { transform: translate(-20px, 30px) scale(0.95); }
            75% { transform: translate(40px, 20px) scale(1.02); }
        }

        /* ---- Grid pattern overlay ---- */
        .auth-grid {
            position: fixed; inset: 0; z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.5;
        }

        /* ---- Radial spotlight (subtle) ---- */
        .auth-spotlight {
            position: fixed; inset: 0; z-index: 1;
            pointer-events: none;
            transition: background .3s ease;
        }

        /* ---- Floating decorative shapes ---- */
        .auth-decor {
            position: fixed; inset: 0; z-index: 1;
            pointer-events: none; overflow: hidden;
        }
        .decor-shape {
            position: absolute;
            width: 50px; height: 50px;
            display: grid; place-items: center;
            border-radius: 16px;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--glass-brd);
            backdrop-filter: blur(8px);
            color: var(--acc);
            font-size: 22px;
            opacity: 0.3;
            animation: decor-float 12s ease-in-out infinite;
        }
        .decor-1 { top: 15%; left: 8%; animation-delay: 0s; }
        .decor-2 { top: 65%; left: 12%; animation-delay: -3s; }
        .decor-3 { top: 25%; right: 10%; animation-delay: -6s; }
        .decor-4 { bottom: 20%; right: 8%; animation-delay: -9s; }
        @keyframes decor-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* ---- Security status bar ---- */
        .auth-status-bar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 5;
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 26px;
            background: rgba(10, 15, 31, 0.7);
            backdrop-filter: blur(14px);
            border-top: 1px solid var(--glass-brd);
            font-size: 11px; font-weight: 600; color: var(--txt-2);
        }
        .auth-status-bar .status-item {
            display: inline-flex; align-items: center; gap: 6px;
        }
        .auth-status-bar i { font-size: 13px; color: var(--ok); }
        .auth-status-bar .version {
            padding: 2px 8px; border-radius: 99px;
            background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
            border: 1px solid rgba(99,102,241,.3);
            color: #a5b4fc; font-size: 10px; font-weight: 800; letter-spacing: .5px;
        }
        .auth-status-bar .security-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 99px;
            background: rgba(16,185,129,.1);
            border: 1px solid rgba(16,185,129,.3);
            color: #6ee7b7; font-size: 10px; font-weight: 700;
        }

        /* ---- Toast zone ---- */
        .toast-zone {
            position: fixed; top: 22px; right: 22px; z-index: 100;
            display: flex; flex-direction: column; gap: 10px;
            max-width: calc(100vw - 44px);
        }

        /* ---- Loading overlay ---- */
        .auth-loading {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(10, 15, 31, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center; justify-content: center;
            flex-direction: column; gap: 16px;
        }
        .auth-loading.show { display: flex; }
        .auth-loading-spinner {
            width: 48px; height: 48px;
            border: 3px solid rgba(99,102,241,.2);
            border-top-color: var(--pri);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .auth-loading-text {
            color: var(--txt-1); font-size: 14px; font-weight: 600;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---- Responsive ---- */
        @media (max-width: 768px) {
            .back-home { top: 14px; left: 14px; padding: 7px 12px; font-size: 12px; }
            .auth-status-bar { padding: 8px 14px; font-size: 10px; }
            .auth-status-bar .hide-mobile { display: none; }
            .decor-shape { width: 40px; height: 40px; font-size: 18px; }
            .mesh-orb { filter: blur(60px); opacity: 0.3; }
        }
    </style>
</head>
<body class="auth-body auth-ultimate">
    <!-- Back to home -->
    <a href="<?= url('') ?>" class="back-home" aria-label="Kembali ke beranda">
        <i class="ph ph-arrow-left"></i>
        <span>Kembali</span>
    </a>

    <!-- Canvas untuk particle background -->
    <canvas id="particleCanvas" class="auth-particles" aria-hidden="true"></canvas>

    <!-- Mesh gradient animasi -->
    <div class="auth-mesh" aria-hidden="true">
        <span class="mesh-orb mesh-orb-1"></span>
        <span class="mesh-orb mesh-orb-2"></span>
        <span class="mesh-orb mesh-orb-3"></span>
        <span class="mesh-orb mesh-orb-4"></span>
    </div>

    <!-- Grid pattern overlay -->
    <div class="auth-grid" aria-hidden="true"></div>

    <!-- Radial spotlight -->
    <div class="auth-spotlight" id="authSpotlight" aria-hidden="true"></div>

    <!-- Decorative floating elements -->
    <div class="auth-decor" aria-hidden="true">
        <span class="decor-shape decor-1"><i class="ph ph-users-three"></i></span>
        <span class="decor-shape decor-2"><i class="ph ph-calendar-check"></i></span>
        <span class="decor-shape decor-3"><i class="ph ph-chart-line-up"></i></span>
        <span class="decor-shape decor-4"><i class="ph ph-shield-check"></i></span>
    </div>

    <!-- CONTENT -->
    <?= $content ?>

    <!-- Toast zone -->
    <div class="toast-zone" id="toastZone" aria-live="polite"></div>

    <!-- Loading overlay -->
    <div class="auth-loading" id="authLoading" role="alert" aria-live="assertive">
        <div class="auth-loading-spinner"></div>
        <div class="auth-loading-text">Memverifikasi identitas...</div>
    </div>

    <!-- Status bar (security + version) -->
    <div class="auth-status-bar" aria-label="Status keamanan">
        <div class="status-item">
            <i class="ph ph-lock-key"></i>
            <span>Koneksi terenkripsi SSL</span>
            <span class="hide-mobile"> • <?= date('Y') ?> <?= e(APP_NAME) ?></span>
        </div>
        <div class="status-item">
            <span class="security-badge hide-mobile">
                <i class="ph ph-shield-check"></i>
                <span>Aman</span>
            </span>
            <span class="version">v7.0 ULTIMATE</span>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="<?= asset('js/app.js') ?>?v=7.0"></script>
    <script>
    /* =========================================================
       AUTH ULTIMATE v7.0 — Particle Canvas + Mouse Spotlight
       ========================================================= */
    (() => {
        'use strict';

        // ---- 1. Particle Canvas (optimized) ----
        const canvas = document.getElementById('particleCanvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let particles = [];
            let mouse = { x: -9999, y: -9999 };
            let w, h;
            let resizeTimer;

            const resize = () => {
                w = canvas.width  = window.innerWidth;
                h = canvas.height = window.innerHeight;
            };
            
            // Debounced resize
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(resize, 150);
            });
            resize();

            const isMobile = window.innerWidth < 768;
            const COUNT = isMobile ? 30 : 60;
            const CONNECTION_DIST = isMobile ? 80 : 100;
            const MAX_CONNECTIONS = isMobile ? 2 : 3;

            class Particle {
                constructor() { this.reset(true); }
                reset(initial = false) {
                    this.x = Math.random() * w;
                    this.y = initial ? Math.random() * h : -10;
                    this.vx = (Math.random() - 0.5) * 0.2;
                    this.vy = Math.random() * 0.3 + 0.08;
                    this.r = Math.random() * 1.4 + 0.3;
                    this.alpha = Math.random() * 0.4 + 0.15;
                    const hue = Math.random() > 0.5 ? 230 : 185;
                    this.color = `hsla(${hue}, 80%, 65%, ${this.alpha})`;
                    this.connections = 0;
                }
                step() {
                    const dx = this.x - mouse.x;
                    const dy = this.y - mouse.y;
                    const d2 = dx*dx + dy*dy;
                    if (d2 < 16900) {
                        const f = (16900 - d2) / 16900 * 0.3;
                        const dist = Math.sqrt(d2) || 1;
                        this.x += dx / dist * f;
                        this.y += dy / dist * f;
                    }
                    this.x += this.vx;
                    this.y += this.vy;
                    this.connections = 0;
                    if (this.y > h + 10 || this.x < -10 || this.x > w + 10) this.reset();
                }
                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                    ctx.fillStyle = this.color;
                    ctx.fill();
                }
            }

            for (let i = 0; i < COUNT; i++) particles.push(new Particle());

            const tick = () => {
                ctx.clearRect(0, 0, w, h);
                
                // Draw connections (optimized - limit per particle)
                for (let i = 0; i < particles.length; i++) {
                    if (particles[i].connections >= MAX_CONNECTIONS) continue;
                    for (let j = i + 1; j < particles.length; j++) {
                        if (particles[j].connections >= MAX_CONNECTIONS) continue;
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const d = Math.sqrt(dx*dx + dy*dy);
                        if (d < CONNECTION_DIST) {
                            const alpha = (1 - d/CONNECTION_DIST) * 0.1;
                            ctx.strokeStyle = `rgba(99, 102, 241, ${alpha})`;
                            ctx.lineWidth = 0.5;
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
                            particles[i].connections++;
                            particles[j].connections++;
                        }
                    }
                }
                
                particles.forEach(p => { p.step(); p.draw(); });
                requestAnimationFrame(tick);
            };
            tick();

            window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
            window.addEventListener('mouseout',  () => { mouse.x = -9999; mouse.y = -9999; });
        }

        // ---- 2. Mouse Spotlight (subtle) ----
        const spot = document.getElementById('authSpotlight');
        if (spot) {
            document.addEventListener('mousemove', e => {
                spot.style.background = `radial-gradient(500px circle at ${e.clientX}px ${e.clientY}px, rgba(99,102,241,0.06), transparent 50%)`;
            });
        }

        // ---- 3. Time-based greeting ----
        const greetEl = document.getElementById('timeGreeting');
        if (greetEl) {
            const h = new Date().getHours();
            let text, icon, accent;
            if (h >= 4  && h < 11) { text = 'Selamat Pagi';    icon = 'ph-sun';        accent = '#fbbf24'; }
            else if (h < 15)        { text = 'Selamat Siang';   icon = 'ph-sun-dim';    accent = '#fb923c'; }
            else if (h < 18)        { text = 'Selamat Sore';    icon = 'ph-cloud-sun';  accent = '#f472b6'; }
            else                    { text = 'Selamat Malam';   icon = 'ph-moon-stars'; accent = '#818cf8'; }
            greetEl.innerHTML = `<i class="ph ${icon}" style="color:${accent}"></i> ${text}`;
        }

        // ---- 4. Typewriter effect ----
        const tw = document.getElementById('typewriterText');
        if (tw) {
            const phrases = [
                'Kelola anggota dengan mudah.',
                'Pantau pertumbuhan organisasi.',
                'Otomatiskan administrasi.',
                'Fokus pada hal yang penting.'
            ];
            let pi = 0, ci = 0, deleting = false;
            const type = () => {
                const current = phrases[pi];
                if (!deleting) {
                    tw.textContent = current.slice(0, ++ci);
                    if (ci === current.length) { deleting = true; setTimeout(type, 2500); return; }
                } else {
                    tw.textContent = current.slice(0, --ci);
                    if (ci === 0) { deleting = false; pi = (pi + 1) % phrases.length; }
                }
                setTimeout(type, deleting ? 40 : 65);
            };
            type();
        }

        // ---- 5. Loading overlay for form submit ----
        const loading = document.getElementById('authLoading');
        const forms = document.querySelectorAll('form[data-auth-form]');
        forms.forEach(form => {
            form.addEventListener('submit', () => {
                if (loading) loading.classList.add('show');
            });
        });
    })();
    </script>
</body>
</html>