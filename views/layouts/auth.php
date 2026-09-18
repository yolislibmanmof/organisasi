<!-- File: views/layouts/auth.php (ULTIMATE EDITION) -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f1f">
    <title><?= e($title ?? 'Masuk') ?> • <?= e(APP_NAME) ?></title>
    
    <!-- Favicon inline SVG bermerek -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%22 stop-color=%22%236366f1%22/><stop offset=%221%22 stop-color=%22%2322d3ee%22/></linearGradient></defs><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22url(%23g)%22/><text x=%2250%22 y=%2268%22 font-family=%22Arial%22 font-size=%2256%22 font-weight=%22900%22 fill=%22white%22 text-anchor=%22middle%22>OU</text></svg>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-body auth-ultimate">
    <!-- Canvas untuk particle background -->
    <canvas id="particleCanvas" class="auth-particles"></canvas>
    
    <!-- Mesh gradient animasi -->
    <div class="auth-mesh">
        <span class="mesh-orb mesh-orb-1"></span>
        <span class="mesh-orb mesh-orb-2"></span>
        <span class="mesh-orb mesh-orb-3"></span>
        <span class="mesh-orb mesh-orb-4"></span>
    </div>
    
    <!-- Grid pattern overlay -->
    <div class="auth-grid"></div>
    
    <!-- Radial spotlight -->
    <div class="auth-spotlight" id="authSpotlight"></div>
    
    <?= $content ?>
    
    <!-- Decorative floating elements -->
    <div class="auth-decor">
        <span class="decor-shape decor-1"><i class="ph ph-users-three"></i></span>
        <span class="decor-shape decor-2"><i class="ph ph-calendar-check"></i></span>
        <span class="decor-shape decor-3"><i class="ph ph-chart-line-up"></i></span>
        <span class="decor-shape decor-4"><i class="ph ph-shield-check"></i></span>
    </div>
    
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
    /* =========================================================
       AUTH ULTIMATE — Particle Canvas + Mouse Spotlight
       ========================================================= */
    (() => {
        'use strict';
        
        // ---- 1. Particle Canvas ----
        const canvas = document.getElementById('particleCanvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let particles = [];
            let mouse = { x: -9999, y: -9999 };
            let w, h;
            
            const resize = () => {
                w = canvas.width  = window.innerWidth;
                h = canvas.height = window.innerHeight;
            };
            resize();
            window.addEventListener('resize', resize);
            
            // Kurangi partikel di mobile untuk performa
            const isMobile = window.innerWidth < 768;
            const COUNT = isMobile ? 35 : 80;
            
            class Particle {
                constructor() { this.reset(true); }
                reset(initial = false) {
                    this.x = Math.random() * w;
                    this.y = initial ? Math.random() * h : -10;
                    this.vx = (Math.random() - 0.5) * 0.25;
                    this.vy = Math.random() * 0.35 + 0.1;
                    this.r = Math.random() * 1.6 + 0.4;
                    this.alpha = Math.random() * 0.5 + 0.2;
                    const hue = Math.random() > 0.5 ? 230 : 185; // biru atau cyan
                    this.color = `hsla(${hue}, 85%, 70%, ${this.alpha})`;
                }
                step() {
                    // Interaksi lemah dengan mouse
                    const dx = this.x - mouse.x;
                    const dy = this.y - mouse.y;
                    const d2 = dx*dx + dy*dy;
                    if (d2 < 14400) { // 120px radius
                        const f = (14400 - d2) / 14400 * 0.4;
                        this.x += dx / Math.sqrt(d2 || 1) * f;
                        this.y += dy / Math.sqrt(d2 || 1) * f;
                    }
                    this.x += this.vx;
                    this.y += this.vy;
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
                // Garis penghubung antar partikel dekat
                for (let i = 0; i < particles.length; i++) {
                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const d = Math.sqrt(dx*dx + dy*dy);
                        if (d < 110) {
                            ctx.strokeStyle = `rgba(99, 102, 241, ${(1 - d/110) * 0.12})`;
                            ctx.lineWidth = 0.6;
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            ctx.stroke();
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
        
        // ---- 2. Mouse Spotlight (radial glow mengikuti kursor) ----
        const spot = document.getElementById('authSpotlight');
        if (spot) {
            document.addEventListener('mousemove', e => {
                spot.style.background = `radial-gradient(600px circle at ${e.clientX}px ${e.clientY}px, rgba(99,102,241,0.12), transparent 40%)`;
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
                    if (ci === current.length) { deleting = true; setTimeout(type, 2200); return; }
                } else {
                    tw.textContent = current.slice(0, --ci);
                    if (ci === 0) { deleting = false; pi = (pi + 1) % phrases.length; }
                }
                setTimeout(type, deleting ? 35 : 70);
            };
            type();
        }
    })();
    </script>
</body>
</html>