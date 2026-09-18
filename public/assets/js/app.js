// File: public/assets/js/app.js (FINAL - TERINTEGRASI TAHAP 4)
(() => {
    'use strict';

    /* ---------- 1. Toggle kata sandi ---------- */
    document.querySelectorAll('[data-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.toggle);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) icon.className = show ? 'ph ph-eye-slash' : 'ph ph-eye';
        });
    });

    /* ---------- 2. Efek ripple ---------- */
    document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (this.classList.contains('is-loading')) return;
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 650);
        });
    });

    /* ---------- 3. Sidebar collapsible ---------- */
    const sidebar  = document.getElementById('sidebar');
    const btnCollapse = document.getElementById('btnCollapse');
    const btnMobile   = document.getElementById('btnMobileMenu');
    const overlay     = document.getElementById('sidebarOverlay');

    const applyCollapse = (collapsed) => {
        if (!sidebar) return;
        if (collapsed) sidebar.classList.add('collapsed');
        else sidebar.classList.remove('collapsed');
        try { localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0'); } catch (e) {}
    };
    try {
        if (localStorage.getItem('sidebar_collapsed') === '1') sidebar?.classList.add('collapsed');
    } catch (e) {}
    btnCollapse?.addEventListener('click', () => applyCollapse(!sidebar.classList.contains('collapsed')));

    /* ---------- 4. Sidebar mobile ---------- */
    btnMobile?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    });

    /* ---------- 5. Count-up animasi global untuk dashboard ---------- */
    const animateNum = (el, target) => {
        const start = 0;
        const dur = 1200;
        const t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(start + (target - start) * eased).toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString('id-ID');
        };
        requestAnimationFrame(tick);
    };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.count, 10);
                if (!isNaN(target)) animateNum(entry.target, target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.stat-num[data-count]').forEach(el => observer.observe(el));

    /* ---------- 6. Pencarian global cepat (⌘K / Ctrl+K) ---------- */
    const globalSearch = document.getElementById('globalSearch');
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            globalSearch?.focus();
        }
    });
    globalSearch?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const q = globalSearch.value.trim();
            if (q) {
                const base = document.body.dataset.base || '/';
                window.location.href = base + 'members?q=' + encodeURIComponent(q);
            }
        }
    });

    /* ---------- 7. Auto-hide alert ---------- */
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => {
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 400);
        }, 4000);
    });

    /* ---------- 8. Loading indicator saat form login dikirim ---------- */
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const btn = loginForm.querySelector('button[type="submit"]');
            if (btn) btn.classList.add('is-loading');
        });
    }

    /* ---------- 9. Fade-in untuk konten halaman ---------- */
    document.querySelectorAll('.app-content > section, .app-content > .page-head').forEach((el, i) => {
        el.style.animation = `fade-up .55s ${i * 80}ms both cubic-bezier(.22,1,.36,1)`;
    });

    /* ---------- 10. Toast Global (dipakai modul Event & halaman lain) ---------- */
    window.toast = (msg, type = 'success') => {
        const zone = document.getElementById('toastZone');
        if (!zone) return;
        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const el = document.createElement('div');
        el.className = 'toast toast-' + type;
        el.innerHTML = '<i class="ph ' + (type === 'success' ? 'ph-check-circle' : 'ph-warning-circle') + '"></i>' +
                       '<span class="toast-msg">' + esc(msg) + '</span>' +
                       '<button class="toast-close" aria-label="Tutup"><i class="ph ph-x"></i></button>' +
                       '<div class="toast-progress"><span></span></div>';
        zone.appendChild(el);
        const close = () => { el.classList.add('hide'); setTimeout(() => el.remove(), 400); };
        el.querySelector('.toast-close').addEventListener('click', close);
        setTimeout(close, 4500);
    };
})();