// File: public/assets/js/public.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    /* ========== 1. CUSTOM CURSOR WITH TRAIL ========== */
    if (window.innerWidth > 768) {
        const cursor = document.createElement('div');
        cursor.className = 'custom-cursor';
        document.body.appendChild(cursor);

        const trail = [];
        const trailCount = 8;
        for (let i = 0; i < trailCount; i++) {
            const dot = document.createElement('div');
            dot.className = 'cursor-trail';
            document.body.appendChild(dot);
            trail.push({ el: dot, x: 0, y: 0 });
        }

        let mouseX = 0, mouseY = 0;
        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            cursor.style.transform = `translate(${mouseX}px, ${mouseY}px)`;
        });

        const animateTrail = () => {
            trail.forEach((dot, i) => {
                const prev = i === 0 ? { x: mouseX, y: mouseY } : trail[i - 1];
                dot.x += (prev.x - dot.x) * 0.3;
                dot.y += (prev.y - dot.y) * 0.3;
                dot.el.style.transform = `translate(${dot.x}px, ${dot.y}px)`;
                dot.el.style.opacity = 1 - (i / trailCount);
            });
            requestAnimationFrame(animateTrail);
        };
        animateTrail();

        document.querySelectorAll('a, button, .btn').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });
    }

    /* ========== 2. SCROLL PROGRESS INDICATOR ========== */
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', () => {
        const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        progressBar.style.width = scrolled + '%';
    });

    /* ========== 3. NAVBAR BERUBAH SAAT DIGULIR ========== */
    const nav = document.getElementById('pubNavbar');
    const onScroll = () => nav?.classList.toggle('scrolled', window.scrollY > 30);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ========== 4. MENU MOBILE ========== */
    const burger = document.getElementById('pubBurger');
    const links  = document.getElementById('pubNavLinks');
    burger?.addEventListener('click', () => {
        if (!links) return;
        links.classList.toggle('open');
        burger.querySelector('i').className = links.classList.contains('open') ? 'ph ph-x' : 'ph ph-list';
    });
    links?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        links.classList.remove('open');
        if (burger) burger.querySelector('i').className = 'ph ph-list';
    }));

    /* ========== 5. GULIR HALUS DENGAN EASING ========== */
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

    /* ========== 6. ANIMASI MUNCUL SAAT DIGULIR ========== */
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) { en.target.classList.add('revealed'); revealObs.unobserve(en.target); }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

    /* ========== 7. ANGKA STATISTIK BERTUMBUH ========== */
    const countObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (!en.isIntersecting) return;
            const el = en.target;
            const target = parseInt(el.dataset.count, 10) || 0;
            const t0 = performance.now(), dur = 1400;
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 4)));
                if (p < 1) requestAnimationFrame(tick); else el.textContent = target;
            };
            requestAnimationFrame(tick);
            countObs.unobserve(el);
        });
    }, { threshold: 0.4 });
    document.querySelectorAll('[data-count]').forEach(el => countObs.observe(el));

    /* ========== 8. PARTIKEL LATAR ENHANCED ========== */
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

        let mouseX = 0, mouseY = 0;
        canvas.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        const tick = () => {
            ctx.clearRect(0, 0, w, h);
            for (const p of pts) {
                const dx = mouseX - p.x;
                const dy = mouseY - p.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 150) {
                    p.vx -= dx * 0.00005;
                    p.vy -= dy * 0.00005;
                }
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

    /* ========== 9. PARALLAX MULTI-LAYER ========== */
    const visual = document.getElementById('heroVisual');
    if (visual && window.innerWidth > 980) {
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            visual.style.transform = `translate(${x}px, ${y}px)`;
            
            const cards = visual.querySelectorAll('.float-card');
            cards.forEach((card, i) => {
                const depth = (i + 1) * 0.5;
                card.style.transform = `translate(${x * depth}px, ${y * depth}px)`;
            });
        });
    }

    /* ========== 10. 3D TILT PADA HERO CARDS ========== */
    document.querySelectorAll('.float-card').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 15;
            const rotateY = (centerX - x) / 15;
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    /* ========== 11. TYPEWRITER EFFECT ========== */
    const typeText = (el, text, speed = 50) => {
        let i = 0;
        el.textContent = '';
        const timer = setInterval(() => {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(timer);
            }
        }, speed);
    };

    const typewriterEl = document.querySelector('.hero-title em');
    if (typewriterEl) {
        const text = typewriterEl.textContent;
        setTimeout(() => typeText(typewriterEl, text, 80), 500);
    }

    /* ========== 12. LIGHTBOX GALERI ENHANCED ========== */
    const lightbox = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightboxImg');
    const lbCap    = document.getElementById('lightboxCap');

    if (lightbox && lbImg) {
        const closeLightbox = () => {
            lightbox.classList.remove('show');
            document.body.style.overflow = '';
        };

        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', () => {
                lbImg.src = item.dataset.full || '';
                if (lbCap) lbCap.textContent = item.dataset.title || '';
                lightbox.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
        });

        document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
        lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox.classList.contains('show')) closeLightbox();
        });
    }

    /* ========== 13. IMAGE LAZY LOADING DENGAN FADE-IN ========== */
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.addEventListener('load', () => img.classList.add('loaded'));
                imageObs.unobserve(img);
            }
        });
    }, { rootMargin: '50px' });
    lazyImages.forEach(img => imageObs.observe(img));

    /* ========== 14. MAGNETIC LINKS ========== */
    document.querySelectorAll('.pub-link, .btn').forEach(link => {
        link.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            this.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    /* ========== 15. SMOOTH SCROLL KE ATAS ========== */
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.innerHTML = '<i class="ph ph-arrow-up"></i>';
    scrollTopBtn.setAttribute('aria-label', 'Gulir ke atas');
    document.body.appendChild(scrollTopBtn);

    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) scrollTopBtn.classList.add('show');
        else scrollTopBtn.classList.remove('show');
    });

})();