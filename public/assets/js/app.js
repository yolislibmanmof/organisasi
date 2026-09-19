// File: public/assets/js/app.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';
    const BASE = document.body.dataset.base || '/';

    /* ========== 1. TOGGLE PASSWORD ========== */
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

    /* ========== 2. RIPPLE EFFECT ENHANCED ========== */
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

    /* ========== 3. MAGNETIC BUTTONS ========== */
    document.querySelectorAll('.btn-primary, .btn-ghost').forEach(btn => {
        btn.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            this.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    /* ========== 4. CARD 3D TILT EFFECT ========== */
    document.querySelectorAll('.stat-card, .glass-card').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    /* ========== 5. SIDEBAR COLLAPSIBLE ENHANCED ========== */
    const sidebar = document.getElementById('sidebar');
    const btnCollapse = document.getElementById('btnCollapse');
    const btnMobile = document.getElementById('btnMobileMenu');
    const overlay = document.getElementById('sidebarOverlay');

    const applyCollapse = (collapsed) => {
        if (!sidebar) return;
        sidebar.classList.toggle('collapsed', collapsed);
        try { localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0'); } catch (e) {}
    };
    try {
        if (localStorage.getItem('sidebar_collapsed') === '1') sidebar?.classList.add('collapsed');
    } catch (e) {}
    btnCollapse?.addEventListener('click', () => applyCollapse(!sidebar.classList.contains('collapsed')));

    /* ========== 6. SIDEBAR MOBILE ========== */
    btnMobile?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    });

    /* ========== 7. COUNT-UP ANIMATION ENHANCED ========== */
    const animateNum = (el, target) => {
        const dur = 1200, t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = Math.floor(target * eased).toLocaleString('id-ID');
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

    /* ========== 8. COMMAND PALETTE (Ctrl+K) ENHANCED ========== */
    const globalSearch = document.getElementById('globalSearch');
    const commandPalette = document.createElement('div');
    commandPalette.className = 'command-palette';
    commandPalette.innerHTML = `
        <div class="command-palette-backdrop"></div>
        <div class="command-palette-modal">
            <div class="command-palette-header">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="commandInput" placeholder="Ketik perintah atau pencarian...">
                <kbd>ESC</kbd>
            </div>
            <div class="command-palette-results"></div>
        </div>
    `;
    document.body.appendChild(commandPalette);

    const commands = [
        { name: 'Dashboard', icon: 'ph-squares-four', action: () => window.location.href = BASE + 'dashboard' },
        { name: 'Anggota', icon: 'ph-users-three', action: () => window.location.href = BASE + 'members' },
        { name: 'Event', icon: 'ph-calendar-blank', action: () => window.location.href = BASE + 'events' },
        { name: 'Artikel', icon: 'ph-newspaper', action: () => window.location.href = BASE + 'articles' },
        { name: 'Konten Situs', icon: 'ph-paint-brush', action: () => window.location.href = BASE + 'content' },
        { name: 'Sensus', icon: 'ph-clipboard-text', action: () => window.location.href = BASE + 'census' },
        { name: 'Pengaturan', icon: 'ph-gear-six', action: () => window.location.href = BASE + 'settings' },
        { name: 'Profil Saya', icon: 'ph-user-circle', action: () => window.location.href = BASE + 'profile' },
        { name: 'Keluar', icon: 'ph-sign-out', action: () => window.location.href = BASE + 'logout' },
    ];

    const openPalette = () => {
        commandPalette.classList.add('show');
        document.getElementById('commandInput').focus();
        renderCommands(commands);
    };
    const closePalette = () => {
        commandPalette.classList.remove('show');
    };
    const renderCommands = (cmds) => {
        const results = commandPalette.querySelector('.command-palette-results');
        results.innerHTML = cmds.map(cmd => `
            <div class="command-item" data-name="${cmd.name}">
                <i class="ph ${cmd.icon}"></i>
                <span>${cmd.name}</span>
                <kbd>↵</kbd>
            </div>
        `).join('');
        results.querySelectorAll('.command-item').forEach(item => {
            item.addEventListener('click', () => {
                const cmd = commands.find(c => c.name === item.dataset.name);
                if (cmd) { cmd.action(); closePalette(); }
            });
        });
    };

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (commandPalette.classList.contains('show')) closePalette();
            else openPalette();
        }
        if (e.key === 'Escape' && commandPalette.classList.contains('show')) {
            closePalette();
        }
    });

    document.getElementById('commandInput')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        const filtered = commands.filter(cmd => cmd.name.toLowerCase().includes(q));
        renderCommands(filtered);
    });

    document.getElementById('commandInput')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const first = commandPalette.querySelector('.command-item');
            if (first) {
                const cmd = commands.find(c => c.name === first.dataset.name);
                if (cmd) { cmd.action(); closePalette(); }
            }
        }
    });

    commandPalette.querySelector('.command-palette-backdrop')?.addEventListener('click', closePalette);

    /* ========== 9. AUTO-HIDE ALERT ========== */
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => {
            a.style.opacity = '0';
            a.style.transform = 'translateX(20px)';
            setTimeout(() => a.remove(), 400);
        }, 4000);
    });

    /* ========== 10. LOADING INDICATOR LOGIN ========== */
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const btn = loginForm.querySelector('button[type="submit"]');
            if (btn) btn.classList.add('is-loading');
        });
    }

    /* ========== 11. FADE-IN KONTEN HALAMAN ========== */
    document.querySelectorAll('.app-content > section, .app-content > .page-head').forEach((el, i) => {
        el.style.animation = `fade-up .55s ${i * 80}ms both cubic-bezier(.22,1,.36,1)`;
    });

    /* ========== 12. TOAST GLOBAL ENHANCED ========== */
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

    /* ========== 14. DROPDOWN NOTIFIKASI ENHANCED ========== */
    const notifBtn = document.getElementById('dxNotifBtn');
    const notifDrop = document.getElementById('dxNotifDropdown');
    const notifList = document.getElementById('dxNotifList');
    const notifDot = document.getElementById('dxNotifDot');
    const notifCount = document.getElementById('dxNotifCount');

    if (notifBtn && notifDrop) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDrop.classList.toggle('show');
            if (notifDrop.classList.contains('show')) loadNotif();
        });

        document.addEventListener('click', (e) => {
            if (!notifDrop.contains(e.target) && e.target !== notifBtn) {
                notifDrop.classList.remove('show');
            }
        });

        const loadNotif = async () => {
            try {
                const res = await fetch(BASE + 'api/notifications');
                const json = await res.json();
                const items = json.items || [];
                const unread = json.unread || 0;

                notifDot.style.display = unread > 0 ? 'block' : 'none';
                notifCount.textContent = items.length + ' notifikasi';

                if (items.length === 0) {
                    notifList.innerHTML = '<li class="dx-notif-empty"><i class="ph ph-bell-slash"></i>Tidak ada notifikasi baru</li>';
                    return;
                }

                notifList.innerHTML = items.map((it, i) => `
                    <a class="dx-notif-item" href="${BASE}${it.link}" style="animation-delay:${i * 50}ms">
                        <span class="dx-notif-icon ${it.grad}"><i class="ph ${it.icon}"></i></span>
                        <div class="dx-notif-body">
                            <strong>${it.title}</strong>
                            <span>${it.sub}</span>
                        </div>
                        <span class="dx-notif-time">${it.time}</span>
                    </a>
                `).join('');
            } catch (err) {
                notifList.innerHTML = '<li class="dx-notif-empty"><i class="ph ph-warning-circle"></i>Gagal memuat</li>';
            }
        };

        setInterval(() => {
            if (notifDrop.classList.contains('show')) loadNotif();
        }, 60000);
    }

    /* ========== 15. KEYBOARD SHORTCUTS OVERLAY ========== */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === '/') {
            e.preventDefault();
            const overlay = document.createElement('div');
            overlay.className = 'shortcuts-overlay';
            overlay.innerHTML = `
                <div class="shortcuts-modal">
                    <div class="shortcuts-header">
                        <h3><i class="ph ph-keyboard"></i> Pintasan Keyboard</h3>
                        <button class="shortcuts-close"><i class="ph ph-x"></i></button>
                    </div>
                    <div class="shortcuts-grid">
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>K</kbd>
                            <span>Command Palette</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>/</kbd>
                            <span>Bantuan Pintasan</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Esc</kbd>
                            <span>Tutup Modal/Palette</span>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
            setTimeout(() => overlay.classList.add('show'), 10);
            overlay.querySelector('.shortcuts-close').addEventListener('click', () => overlay.remove());
            overlay.addEventListener('click', (e) => { if (e.target === overlay) overlay.remove(); });
        }
    });

    /* ========== 16. LOADING BAR ========== */
    const loadingBar = document.createElement('div');
    loadingBar.className = 'loading-bar';
    document.body.appendChild(loadingBar);

    const showLoading = () => loadingBar.classList.add('active');
    const hideLoading = () => loadingBar.classList.remove('active');

    document.querySelectorAll('a[href]').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.startsWith(BASE) && !href.startsWith('#')) {
            link.addEventListener('click', () => {
                showLoading();
                setTimeout(hideLoading, 3000);
            });
        }
    });
    window.addEventListener('load', hideLoading);

})();