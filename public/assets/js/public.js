// File: public/assets/js/public.js
(() => {
    'use strict';

    /* ---------- 1. Navbar berubah saat digulir ---------- */
    const nav = document.getElementById('pubNavbar');
    const onScroll = () => nav?.classList.toggle('scrolled', window.scrollY > 30);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- 2. Menu mobile ---------- */
    const burger = document.getElementById('pubBurger');
    const links  = document.getElementById('pubNavLinks');
    burger?.addEventListener('click', () => {
        links.classList.toggle('open');
        burger.querySelector('i').className = links.classList.contains('open') ? 'ph ph-x' : 'ph ph-list';
    });
    links?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        links.classList.remove('open');
        if (burger) burger.querySelector('i').className = 'ph ph-list';
    }));

    /* ---------- 3. Gulir halus untuk tautan anchor ---------- */
    document.querySelectorAll('[data-scroll]').forEach(a => {
        a.addEventListener('click', (e) => {
            const hash = (a.getAttribute('href') || '').split('#')[1];
            if (!hash) return;
            const target = document.getElementById(hash);
            if (!target) return;
            e.preventDefault();
            const y = target.getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: y, behavior: 'smooth' });
        });
    });

    /* ---------- 4. Animasi muncul saat digulir ---------- */
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) { en.target.classList.add('revealed'); revealObs.unobserve(en.target); }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

    /* ---------- 5. Angka statistik bertumbuh ---------- */
    const countObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (!en.isIntersecting) return;
            const el = en.target;
            const target = parseInt(el.dataset.count, 10) || 0;
            const t0 = performance.now(), dur = 1400;
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(tick); else el.textContent = target;
            };
            requestAnimationFrame(tick);
            countObs.unobserve(el);
        });
    }, { threshold: 0.4 });
    document.querySelectorAll('[data-count]').forEach(el => countObs.observe(el));

    /* ---------- 6. Partikel latar ---------- */
    const canvas = document.getElementById('publicParticles');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let w, h;
        const pts = [];
        const resize = () => { w = canvas.width = window.innerWidth; h = canvas.height = window.innerHeight; };
        resize();
        window.addEventListener('resize', resize);
        const N = window.innerWidth < 768 ? 25 : 55;
        for (let i = 0; i < N; i++) {
            pts.push({
                x: Math.random() * w, y: Math.random() * h,
                r: Math.random() * 1.5 + 0.4,
                vx: (Math.random() - 0.5) * 0.22, vy: (Math.random() - 0.5) * 0.22,
                a: Math.random() * 0.35 + 0.12
            });
        }
        const tick = () => {
            ctx.clearRect(0, 0, w, h);
            for (const p of pts) {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(148, 163, 255, ${p.a})`;
                ctx.fill();
            }
            requestAnimationFrame(tick);
        };
        tick();
    }

    /* ---------- 7. Parallax halus pada visual hero ---------- */
    const visual = document.getElementById('heroVisual');
    if (visual && window.innerWidth > 980) {
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 16;
            const y = (e.clientY / window.innerHeight - 0.5) * 16;
            visual.style.transform = `translate(${x}px, ${y}px)`;
        });
    }
})();