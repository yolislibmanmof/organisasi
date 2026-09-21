// File: public/assets/js/app.js (FINAL v7.0 ULTIMATE)
// Core Application Logic: Sidebar, Modals, Toasts, Notifications, Shortcuts
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const API = (path) => BASE + 'api/' + path;
    const PAGE = (path) => BASE + path;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    /* ============================================================
       1. TOGGLE PASSWORD (Smooth)
       ============================================================ */
    document.querySelectorAll('[data-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.toggle);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    icon.className = show ? 'ph ph-eye-slash' : 'ph ph-eye';
                    icon.style.transform = '';
                }, 150);
            }
        });
    });

    /* ============================================================
       2. RIPPLE EFFECT (Premium)
       ============================================================ */
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

    /* ============================================================
       3. MAGNETIC BUTTONS (Smooth & Responsive)
       ============================================================ */
    document.querySelectorAll('.btn-primary, .btn-ghost').forEach(btn => {
        let rafId = null;
        btn.addEventListener('mousemove', function(e) {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                this.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
            });
        });
        btn.addEventListener('mouseleave', function() {
            if (rafId) cancelAnimationFrame(rafId);
            this.style.transform = '';
        });
    });

    /* ============================================================
       4. CARD 3D TILT EFFECT (Performance Optimized)
       ============================================================ */
    document.querySelectorAll('.stat-card, .glass-card:not(.modal):not(.share-modal-card):not(.command-palette-modal)').forEach(card => {
        let rafId = null;
        card.addEventListener('mousemove', function(e) {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            });
        });
        card.addEventListener('mouseleave', function() {
            if (rafId) cancelAnimationFrame(rafId);
            this.style.transform = '';
        });
    });

    /* ============================================================
       5. SIDEBAR COLLAPSIBLE (Enhanced with Persistence)
       ============================================================ */
    const sidebar = document.getElementById('sidebar');
    const btnCollapse = document.getElementById('btnCollapse');
    const btnMobile = document.getElementById('btnMobileMenu');
    const overlay = document.getElementById('sidebarOverlay');

    const applyCollapse = (collapsed) => {
        if (!sidebar) return;
        sidebar.classList.toggle('collapsed', collapsed);
        try {
            localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
        } catch (e) { /* Silent */ }
    };

    // Load persisted state
    try {
        const saved = localStorage.getItem('sidebar_collapsed');
        if (saved === '1' && sidebar) sidebar.classList.add('collapsed');
    } catch (e) { /* Silent */ }

    btnCollapse?.addEventListener('click', () => {
        applyCollapse(!sidebar.classList.contains('collapsed'));
    });

    /* ============================================================
       6. SIDEBAR MOBILE (Smooth Overlay)
       ============================================================ */
    btnMobile?.addEventListener('click', () => {
        sidebar?.classList.add('open');
        overlay?.classList.add('show');
    });
    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('show');
    });

    /* ============================================================
       7. COUNT-UP ANIMATION (Smooth Easing)
       ============================================================ */
    const animateNum = (el, target) => {
        const dur = 1200, t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4); // Ease-out quartic
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

    document.querySelectorAll('[data-count]').forEach(el => {
        observer.observe(el);
    });

    /* ============================================================
       8. COMMAND PALETTE (Ctrl+K) - Enhanced
       ============================================================ */
    const commandPalette = document.createElement('div');
    commandPalette.className = 'command-palette';
    commandPalette.innerHTML = `
        <div class="command-palette-backdrop"></div>
        <div class="command-palette-modal">
            <div class="command-palette-header">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" id="commandInput" placeholder="Ketik perintah atau pencarian..." autocomplete="off">
                <kbd>ESC</kbd>
            </div>
            <div class="command-palette-results"></div>
        </div>
    `;
    document.body.appendChild(commandPalette);

    const commands = [
        { name: 'Dashboard', icon: 'ph-squares-four', action: () => window.location.href = PAGE('dashboard') },
        { name: 'Anggota', icon: 'ph-users-three', action: () => window.location.href = PAGE('members') },
        { name: 'Event', icon: 'ph-calendar-blank', action: () => window.location.href = PAGE('events') },
        { name: 'Artikel', icon: 'ph-newspaper', action: () => window.location.href = PAGE('articles') },
        { name: 'Konten Situs', icon: 'ph-paint-brush', action: () => window.location.href = PAGE('content') },
        { name: 'Sensus', icon: 'ph-clipboard-text', action: () => window.location.href = PAGE('census') },
        { name: 'Pengaturan', icon: 'ph-gear-six', action: () => window.location.href = PAGE('settings') },
        { name: 'Profil Saya', icon: 'ph-user-circle', action: () => window.location.href = PAGE('profile') },
        { name: 'Keluar', icon: 'ph-sign-out', action: () => window.location.href = PAGE('logout') },
    ];

    const openPalette = () => {
        commandPalette.classList.add('show');
        const input = document.getElementById('commandInput');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 100);
        }
        renderCommands(commands);
    };

    const closePalette = () => {
        commandPalette.classList.remove('show');
    };

    const renderCommands = (cmds) => {
        const results = commandPalette.querySelector('.command-palette-results');
        if (!results) return;
        results.innerHTML = cmds.map(cmd => `
            <div class="command-item" data-name="${esc(cmd.name)}">
                <i class="ph ${cmd.icon}"></i>
                <span>${esc(cmd.name)}</span>
                <kbd>↵</kbd>
            </div>
        `).join('');
        results.querySelectorAll('.command-item').forEach(item => {
            item.addEventListener('click', () => {
                const cmd = commands.find(c => c.name === item.dataset.name);
                if (cmd) {
                    cmd.action();
                    closePalette();
                }
            });
        });
    };

    // Keyboard shortcut
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
                if (cmd) {
                    cmd.action();
                    closePalette();
                }
            }
        }
    });

    commandPalette.querySelector('.command-palette-backdrop')?.addEventListener('click', closePalette);

    /* ============================================================
       9. AUTO-HIDE ALERT (Smooth Fade)
       ============================================================ */
    document.querySelectorAll('.alert').forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity .4s, transform .4s';
            a.style.opacity = '0';
            a.style.transform = 'translateX(20px)';
            setTimeout(() => a.remove(), 400);
        }, 4000);
    });

    /* ============================================================
       10. LOADING INDICATOR LOGIN
       ============================================================ */
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            const btn = loginForm.querySelector('button[type="submit"]');
            if (btn) btn.classList.add('is-loading');
        });
    }

    /* ============================================================
       11. FADE-IN KONTEN HALAMAN (Staggered Animation)
       ============================================================ */
    document.querySelectorAll('.app-content > section, .app-content > .page-head').forEach((el, i) => {
        el.style.animation = `fade-up .55s ${i * 80}ms both cubic-bezier(.22,1,.36,1)`;
    });

    /* ============================================================
       12. TOAST GLOBAL (Enhanced with Queue)
       ============================================================ */
    window.toast = (msg, type = 'success') => {
        const zone = document.getElementById('toastZone');
        if (!zone) return;
        const el = document.createElement('div');
        el.className = 'toast toast-' + type;
        el.innerHTML = `
            <i class="ph ${type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'}"></i>
            <span class="toast-msg">${esc(msg)}</span>
            <button class="toast-close" aria-label="Tutup"><i class="ph ph-x"></i></button>
            <div class="toast-progress"><span></span></div>
        `;
        zone.appendChild(el);
        const close = () => {
            el.classList.add('hide');
            setTimeout(() => el.remove(), 400);
        };
        el.querySelector('.toast-close').addEventListener('click', close);
        setTimeout(close, 4500);
    };

    /* ============================================================
       13. DROPDOWN NOTIFIKASI (Real-time Polling)
       ============================================================ */
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
                const res = await fetch(API('notifications'));
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
                            <strong>${esc(it.title)}</strong>
                            <span>${esc(it.sub)}</span>
                        </div>
                        <span class="dx-notif-time">${esc(it.time)}</span>
                    </a>
                `).join('');
            } catch (err) {
                notifList.innerHTML = '<li class="dx-notif-empty"><i class="ph ph-warning-circle"></i>Gagal memuat</li>';
            }
        };

        // Auto-refresh every 60 seconds
        setInterval(() => {
            if (notifDrop.classList.contains('show')) loadNotif();
        }, 60000);
    }

    /* ============================================================
       14. USER DROPDOWN (v7.0 - Smooth Toggle)
       ============================================================ */
    const userBtn = document.getElementById('dxUserBtn');
    const userDrop = document.getElementById('dxUserDropdown');

    if (userBtn && userDrop) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDrop.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!userDrop.contains(e.target) && e.target !== userBtn && !userBtn.contains(e.target)) {
                userDrop.classList.remove('show');
            }
        });
    }

    /* ============================================================
       15. KEYBOARD SHORTCUTS OVERLAY (Ctrl+/)
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key === '/') {
            e.preventDefault();
            if (document.querySelector('.shortcuts-overlay')) return;
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
                            <kbd>Ctrl</kbd> + <kbd>Q</kbd>
                            <span>Keluar</span>
                        </div>
                        <div class="shortcut-item">
                            <kbd>Ctrl</kbd> + <kbd>P</kbd>
                            <span>Cetak Halaman</span>
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
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.remove();
            });
        }
    });

    /* ============================================================
       16. LOADING BAR (Smooth Transitions)
       ============================================================ */
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

    /* ============================================================
       17. FAB (Floating Action Button) - v7.0
       ============================================================ */
    const fabZone = document.getElementById('fabZone');
    const fabMain = document.getElementById('fabMain');

    if (fabZone && fabMain) {
        fabMain.addEventListener('click', (e) => {
            e.stopPropagation();
            fabZone.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (!fabZone.contains(e.target)) {
                fabZone.classList.remove('open');
            }
        });
    }

    /* ============================================================
       18. GLOBAL KEYBOARD SHORTCUTS (v7.0)
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        // Ctrl+Q = Logout
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'q') {
            e.preventDefault();
            window.location.href = BASE + 'logout';
        }
    });

    /* ============================================================
       19. GLOBAL SEARCH (Topbar) - v7.0
       ============================================================ */
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('focus', () => {
            globalSearch.parentElement.classList.add('focused');
        });
        globalSearch.addEventListener('blur', () => {
            globalSearch.parentElement.classList.remove('focused');
        });
        globalSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                globalSearch.blur();
                globalSearch.value = '';
            }
        });
    }

    /* ============================================================
       20. INITIALIZATION COMPLETE
       ============================================================ */
    console.log('%c✨ Organisasi v7.0 ULTIMATE Loaded', 'color: #22d3ee; font-weight: bold; font-size: 14px;');

})();