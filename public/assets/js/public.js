// File: public/assets/js/public.js (FINAL v7.0 ULTIMATE)
// Public Pages: Landing + Artikel + Galeri + Sensus
(() => {
    'use strict';

    const isMobile = window.innerWidth <= 768;
    const isTablet = window.innerWidth <= 1024;

    /* ============================================================
       1. CUSTOM CURSOR WITH TRAIL (Desktop Only, Enhanced)
       ============================================================ */
    if (!isMobile && window.matchMedia('(pointer: fine)').matches) {
        const cursor = document.createElement('div');
        cursor.className = 'custom-cursor';
        document.body.appendChild(cursor);

        const trailCount = 10;
        const trail = Array.from({ length: trailCount }, () => {
            const dot = document.createElement('div');
            dot.className = 'cursor-trail';
            document.body.appendChild(dot);
            return { el: dot, x: 0, y: 0 };
        });

        let mouseX = 0, mouseY = 0;
        let cursorX = 0, cursorY = 0;

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
        });

        // Click animation
        document.addEventListener('mousedown', () => {
            cursor.style.transform = `translate(${cursorX}px, ${cursorY}px) scale(0.8)`;
            setTimeout(() => {
                cursor.style.transform = `translate(${cursorX}px, ${cursorY}px) scale(1)`;
            }, 150);
        });

        const animateTrail = () => {
            cursorX += (mouseX - cursorX) * 0.2;
            cursorY += (mouseY - cursorY) * 0.2;
            cursor.style.transform = `translate(${cursorX}px, ${cursorY}px)`;

            trail.forEach((dot, i) => {
                const prev = i === 0 ? { x: cursorX, y: cursorY } : trail[i - 1];
                dot.x += (prev.x - dot.x) * 0.25;
                dot.y += (prev.y - dot.y) * 0.25;
                dot.el.style.transform = `translate(${dot.x}px, ${dot.y}px)`;
                dot.el.style.opacity = 1 - (i / trailCount);
            });
            requestAnimationFrame(animateTrail);
        };
        animateTrail();

        // Hover effect pada interactive elements
        document.querySelectorAll('a, button, .btn, [role="button"]').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });
    }

    /* ============================================================
       2. SCROLL PROGRESS INDICATOR (Enhanced with Sections)
       ============================================================ */
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    document.body.appendChild(progressBar);

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                progressBar.style.width = Math.min(100, Math.max(0, scrolled)) + '%';
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });

    /* ============================================================
       3. NAVBAR SCROLL EFFECT + MOBILE BURGER
       ============================================================ */
    const nav = document.getElementById('pubNavbar');
    const burger = document.querySelector('.pub-burger');
    const navLinks = document.querySelector('.pub-nav-links');

    if (nav) {
        const onScroll = () => {
            nav.classList.toggle('scrolled', window.scrollY > 30);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // Mobile burger menu
    if (burger && navLinks) {
        burger.addEventListener('click', () => {
            navLinks.classList.toggle('mobile-open');
            burger.classList.toggle('active');
        });

        // Close menu saat klik link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('mobile-open');
                burger.classList.remove('active');
            });
        });
    }

    /* ============================================================
       4. SMOOTH SCROLL (Enhanced with Offset)
       ============================================================ */
    document.querySelectorAll('[data-scroll], a[href^="#"]').forEach(a => {
        a.addEventListener('click', (e) => {
            const href = a.getAttribute('href') || '';
            if (!href.startsWith('#')) return;
            const hash = href.split('#')[1];
            if (!hash) return;

            const target = document.getElementById(hash);
            if (!target) return;

            e.preventDefault();
            const offset = nav ? nav.offsetHeight + 20 : 90;
            const y = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: y, behavior: 'smooth' });

            // Update URL tanpa reload
            history.pushState(null, '', '#' + hash);
        });
    });

    /* ============================================================
       5. SCROLL REVEAL ANIMATION (Staggered)
       ============================================================ */
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach((en, i) => {
            if (en.isIntersecting) {
                setTimeout(() => {
                    en.target.classList.add('revealed');
                }, i * 80);
                revealObs.unobserve(en.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

    /* ============================================================
       6. COUNTER ANIMATION (Locale-formatted)
       ============================================================ */
    const countObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (!en.isIntersecting) return;
            const el = en.target;
            const target = parseInt(el.dataset.count, 10) || 0;
            if (target === 0) return;

            const t0 = performance.now();
            const dur = 1600;

            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                const eased = 1 - Math.pow(1 - p, 4);
                const current = Math.floor(target * eased);
                el.textContent = current.toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target.toLocaleString('id-ID');
            };
            requestAnimationFrame(tick);
            countObs.unobserve(el);
        });
    }, { threshold: 0.3 });

    document.querySelectorAll('[data-count]').forEach(el => countObs.observe(el));

    /* ============================================================
       7. BACKGROUND PARTICLES (Optimized)
       ============================================================ */
    const canvas = document.getElementById('publicParticles');
    if (canvas && !isMobile) {
        const ctx = canvas.getContext('2d');
        let w, h;
        const pts = [];
        const particleCount = isTablet ? 30 : 50;

        const resize = () => {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            // Reposition particles within new bounds
            pts.forEach(p => {
                if (p.x > w) p.x = Math.random() * w;
                if (p.y > h) p.y = Math.random() * h;
            });
        };
        resize();
        window.addEventListener('resize', resize);

        // Initialize particles
        for (let i = 0; i < particleCount; i++) {
            pts.push({
                x: Math.random() * w,
                y: Math.random() * h,
                r: Math.random() * 1.8 + 0.5,
                vx: (Math.random() - 0.5) * 0.25,
                vy: (Math.random() - 0.5) * 0.25,
                a: Math.random() * 0.4 + 0.1
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
                // Mouse interaction
                const dx = mouseX - p.x;
                const dy = mouseY - p.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 120) {
                    const force = (120 - dist) / 120;
                    p.vx -= (dx / dist) * force * 0.02;
                    p.vy -= (dy / dist) * force * 0.02;
                }

                // Move
                p.x += p.vx;
                p.y += p.vy;

                // Bounce
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;

                // Draw
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(148, 163, 255, ${p.a})`;
                ctx.fill();
            }

            // Draw connections
            for (let i = 0; i < pts.length; i++) {
                for (let j = i + 1; j < pts.length; j++) {
                    const dx = pts[i].x - pts[j].x;
                    const dy = pts[i].y - pts[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 100) {
                        ctx.beginPath();
                        ctx.moveTo(pts[i].x, pts[i].y);
                        ctx.lineTo(pts[j].x, pts[j].y);
                        ctx.strokeStyle = `rgba(148, 163, 255, ${0.15 * (1 - dist / 100)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }

            requestAnimationFrame(tick);
        };
        tick();
    }

    /* ============================================================
       8. PARALLAX MULTI-LAYER (Hero Only, Optimized)
       ============================================================ */
    const visual = document.getElementById('heroVisual');
    const cardTransforms = new Map(); // Store parallax offsets
    
    if (visual && !isTablet) {
        let rafId = null;
        window.addEventListener('mousemove', (e) => {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                const x = (e.clientX / window.innerWidth - 0.5) * 25;
                const y = (e.clientY / window.innerHeight - 0.5) * 25;
                visual.style.transform = `translate(${x}px, ${y}px)`;

                const cards = visual.querySelectorAll('.float-card');
                cards.forEach((card, i) => {
                    const depth = (i + 1) * 0.6;
                    const px = x * depth;
                    const py = y * depth;
                    cardTransforms.set(card, { px, py });
                    
                    // Only apply parallax if not being tilted
                    if (!card.classList.contains('is-tilting')) {
                        card.style.transform = `translate(${px}px, ${py}px)`;
                    }
                });
            });
        });
    }

    /* ============================================================
       9. 3D TILT ON HERO CARDS (Smooth - Combined with Parallax)
       ============================================================ */
    document.querySelectorAll('.float-card').forEach(card => {
        let rafId = null;
        card.addEventListener('mouseenter', function() {
            this.classList.add('is-tilting');
        });
        card.addEventListener('mousemove', function(e) {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 15;
                const rotateY = (centerX - x) / 15;
                
                // Combine parallax offset with 3D tilt
                const parallax = cardTransforms.get(this) || { px: 0, py: 0 };
                this.style.transform = `translate(${parallax.px}px, ${parallax.py}px) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
            });
        });
        card.addEventListener('mouseleave', function() {
            if (rafId) cancelAnimationFrame(rafId);
            this.classList.remove('is-tilting');
            // Restore parallax transform
            const parallax = cardTransforms.get(this) || { px: 0, py: 0 };
            this.style.transform = `translate(${parallax.px}px, ${parallax.py}px)`;
        });
    });

    /* ============================================================
       10. TYPEWRITER EFFECT (Multi-phrase)
       ============================================================ */
    const phrases = [
        'Inovasi Organisasi',
        'Kolaborasi Tanpa Batas',
        'Membangun Masa Depan',
        'Bersatu Dalam Visi'
    ];

    const typeText = (el, phrases, speed = 60, pause = 2000) => {
        let phraseIdx = 0;
        let charIdx = 0;
        let isDeleting = false;

        const tick = () => {
            const current = phrases[phraseIdx];

            if (isDeleting) {
                el.textContent = current.substring(0, charIdx - 1);
                charIdx--;
            } else {
                el.textContent = current.substring(0, charIdx + 1);
                charIdx++;
            }

            let typeSpeed = speed;

            if (!isDeleting && charIdx === current.length) {
                typeSpeed = pause;
                isDeleting = true;
            } else if (isDeleting && charIdx === 0) {
                isDeleting = false;
                phraseIdx = (phraseIdx + 1) % phrases.length;
                typeSpeed = speed / 2;
            }

            setTimeout(tick, typeSpeed);
        };

        setTimeout(tick, 500);
    };

    const typewriterEl = document.querySelector('.hero-title em, .brand-typewriter');
    if (typewriterEl) {
        const initialText = typewriterEl.textContent;
        const finalPhrases = initialText ? [initialText, ...phrases] : phrases;
        typeText(typewriterEl, finalPhrases);
    }

    /* ============================================================
       11. LIGHTBOX GALLERY (Enhanced with Navigation)
       ============================================================ */
    const lightbox = document.getElementById('lightbox');
    const lbImg = document.getElementById('lightboxImg');
    const lbCap = document.getElementById('lightboxCap');
    const lbPrev = document.getElementById('lightboxPrev');
    const lbNext = document.getElementById('lightboxNext');

    let galleryItems = [];
    let currentIdx = 0;

    if (lightbox && lbImg) {
        // Force closed on load
        lightbox.classList.remove('show');
        lbImg.src = '';

        const openLightbox = (src, title, idx) => {
            currentIdx = idx;
            lbImg.src = src;
            if (lbCap) lbCap.textContent = title || '';
            lightbox.classList.add('show');
            document.body.style.overflow = 'hidden';
            updateNavButtons();
        };

        const closeLightbox = () => {
            lightbox.classList.remove('show');
            document.body.style.overflow = '';
            setTimeout(() => { lbImg.src = ''; }, 300);
        };

        const navigate = (dir) => {
            currentIdx = (currentIdx + dir + galleryItems.length) % galleryItems.length;
            const item = galleryItems[currentIdx];
            lbImg.src = item.src;
            if (lbCap) lbCap.textContent = item.title || '';
            updateNavButtons();
        };

        const updateNavButtons = () => {
            if (lbPrev) lbPrev.style.display = galleryItems.length > 1 ? 'flex' : 'none';
            if (lbNext) lbNext.style.display = galleryItems.length > 1 ? 'flex' : 'none';
        };

        // Bind gallery items
        galleryItems = Array.from(document.querySelectorAll('.gallery-item, [data-lightbox]')).map(item => ({
            src: item.dataset.full || item.dataset.lightbox || item.querySelector('img')?.src || '',
            title: item.dataset.title || item.querySelector('figcaption')?.textContent || ''
        }));

        document.querySelectorAll('.gallery-item, [data-lightbox]').forEach((item, idx) => {
            item.addEventListener('click', () => {
                const src = item.dataset.full || item.dataset.lightbox || item.querySelector('img')?.src || '';
                const title = item.dataset.title || item.querySelector('figcaption')?.textContent || '';
                openLightbox(src, title, idx);
            });
        });

        document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
        lbPrev?.addEventListener('click', () => navigate(-1));
        lbNext?.addEventListener('click', () => navigate(1));

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });

        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('show')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        });
    }

    /* ============================================================
       12. IMAGE LAZY LOADING (Enhanced with Placeholder)
       ============================================================ */
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.dataset.src;
                img.src = src;
                img.addEventListener('load', () => {
                    img.classList.add('loaded');
                    img.removeAttribute('data-src');
                });
                img.addEventListener('error', () => {
                    img.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23ddd" width="200" height="200"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dy=".3em"%3EGambar tidak tersedia%3C/text%3E%3C/svg%3E';
                });
                imageObs.unobserve(img);
            }
        });
    }, { rootMargin: '100px 0px', threshold: 0.01 });

    lazyImages.forEach(img => imageObs.observe(img));

    /* ============================================================
       13. MAGNETIC EFFECT ON LINKS (Performance Optimized)
       ============================================================ */
    if (!isMobile) {
        document.querySelectorAll('.pub-link, .btn-primary').forEach(link => {
            let rafId = null;
            link.addEventListener('mousemove', function(e) {
                if (rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(() => {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    this.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
                });
            });
            link.addEventListener('mouseleave', function() {
                if (rafId) cancelAnimationFrame(rafId);
                this.style.transform = '';
            });
        });
    }

    /* ============================================================
       14. SCROLL TO TOP BUTTON
       ============================================================ */
    const scrollTopBtn = document.getElementById('scrollTopBtn');
    if (scrollTopBtn) {
        window.addEventListener('scroll', () => {
            scrollTopBtn.classList.toggle('show', window.scrollY > 400);
        }, { passive: true });

        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ============================================================
       15. READING PROGRESS (Article Detail)
       ============================================================ */
    const articleContent = document.querySelector('.detail-content');
    if (articleContent) {
        const readTime = Math.ceil(articleContent.textContent.split(/\s+/).length / 200);
        const metaEl = document.querySelector('.detail-meta');
        if (metaEl && !metaEl.querySelector('.read-time')) {
            const span = document.createElement('span');
            span.className = 'read-time';
            span.innerHTML = `<i class="ph ph-clock"></i> ${readTime} menit baca`;
            metaEl.appendChild(span);
        }
    }

    /* ============================================================
       16. SMOOTH FORM INTERACTIONS
       ============================================================ */
    document.querySelectorAll('.sensus-wrap form, .pub-section form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        });
    });

    /* ============================================================
       17. ANNOUNCEMENT BAR (Auto-hide - Improved)
       ============================================================ */
    const announcement = document.querySelector('[data-announcement]');
    if (announcement) {
        // Check if previously dismissed
        let dismissed = false;
        try {
            dismissed = localStorage.getItem('announcement_dismissed') === '1';
        } catch (e) { /* Silent */ }
        
        if (dismissed) {
            announcement.style.display = 'none';
        } else {
            const closeBtn = announcement.querySelector('[data-close-announcement]');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    announcement.style.transform = 'translateY(-100%)';
                    setTimeout(() => announcement.remove(), 300);
                    try {
                        localStorage.setItem('announcement_dismissed', '1');
                    } catch (e) { /* Silent */ }
                });
            }

            // Auto-hide after 30 seconds (more generous) if user scrolled significantly
            let scrolled = false;
            let autoHideTimer = null;
            window.addEventListener('scroll', () => {
                if (!scrolled && window.scrollY > 300) {
                    scrolled = true;
                    // Start timer only if announcement is still visible
                    autoHideTimer = setTimeout(() => {
                        if (announcement && announcement.style.display !== 'none') {
                            announcement.style.transform = 'translateY(-100%)';
                            setTimeout(() => announcement.remove(), 300);
                        }
                    }, 30000);
                }
            }, { passive: true });
            
            // Pause auto-hide on hover
            announcement.addEventListener('mouseenter', () => {
                if (autoHideTimer) {
                    clearTimeout(autoHideTimer);
                    autoHideTimer = null;
                }
            });
        }
    }

    /* ============================================================
       18. PREFERS REDUCED MOTION
       ============================================================ */
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.style.setProperty('--ease-smooth', 'linear');
        document.documentElement.style.setProperty('--ease-elastic', 'linear');
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('revealed'));
    }

    console.log('%c🌐 Public Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();