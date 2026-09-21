// File: public/assets/js/profile.js (FINAL v7.0 ULTIMATE)
// Modul Profil Pengguna: Avatar + Password + Session + Data Export
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    /* ============================================================
       1. BROWSER & DEVICE DETECTION (Enhanced)
       ============================================================ */
    const ua = navigator.userAgent;
    const platform = navigator.platform || 'Unknown';
    const language = navigator.language || 'Unknown';
    const screenRes = screen.width + '×' + screen.height;

    let browser = 'Peramban tidak dikenal', browserIcon = 'ph-globe';
    if (ua.includes('Edg/'))          { browser = 'Microsoft Edge';  browserIcon = 'ph-microsoft-edge-logo'; }
    else if (ua.includes('OPR/'))     { browser = 'Opera';           browserIcon = 'ph-opera-logo'; }
    else if (ua.includes('Firefox/')) { browser = 'Mozilla Firefox'; browserIcon = 'ph-firefox-logo'; }
    else if (ua.includes('Chrome/'))  { browser = 'Google Chrome';   browserIcon = 'ph-google-chrome-logo'; }
    else if (ua.includes('Safari/'))  { browser = 'Safari';          browserIcon = 'ph-safari-logo'; }

    const uaEl = document.getElementById('uaName');
    if (uaEl) {
        uaEl.innerHTML = `<i class="ph ${browserIcon}"></i> ${esc(browser)}`;
    }

    // Info tambahan jika ada placeholder
    const platformEl = document.getElementById('devicePlatform');
    if (platformEl) platformEl.textContent = platform;
    const langEl = document.getElementById('deviceLang');
    if (langEl) langEl.textContent = language;
    const resEl = document.getElementById('deviceRes');
    if (resEl) resEl.textContent = screenRes;

    /* ============================================================
       2. UNSAVED CHANGES TRACKING
       ============================================================ */
    let hasUnsavedChanges = false;
    function markDirty() {
        hasUnsavedChanges = true;
        const indicator = document.getElementById('profileDirtyIndicator');
        if (indicator) indicator.style.display = 'inline-flex';
    }
    function markClean() {
        hasUnsavedChanges = false;
        const indicator = document.getElementById('profileDirtyIndicator');
        if (indicator) indicator.style.display = 'none';
    }

    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ============================================================
       3. AVATAR UPLOAD (Drag & Drop + Preview + Validation)
       ============================================================ */
    const zone  = document.getElementById('uploadZone');
    const input = document.getElementById('photoInput');

    if (zone && input) {
        zone.addEventListener('click', (e) => {
            if (e.target.tagName !== 'BUTTON') input.click();
        });

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', () => {
            zone.classList.remove('drag-over');
        });
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleFileSelect();
            }
        });

        input.addEventListener('change', handleFileSelect);
    }

    function handleFileSelect() {
        const file = input.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            toast('Ukuran foto maksimal 2 MB.', 'error');
            input.value = '';
            return;
        }

        if (!file.type.startsWith('image/')) {
            toast('Hanya file gambar (JPG, PNG, WEBP) yang didukung.', 'error');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            let img = zone.querySelector('.avatar, img');
            if (!img || img.tagName !== 'IMG') {
                const next = document.createElement('img');
                next.className = 'avatar avatar-img';
                if (img) img.replaceWith(next);
                else zone.appendChild(next);
                img = next;
            }
            img.style.opacity = '0';
            img.src = e.target.result;
            img.onload = () => {
                img.style.transition = 'opacity .4s ease, transform .4s ease';
                img.style.opacity = '1';
                img.style.transform = 'scale(1.05)';
                setTimeout(() => { img.style.transform = 'scale(1)'; }, 200);
                markDirty();
            };
        };
        reader.readAsDataURL(file);
    }

    /* ============================================================
       4. PASSWORD STRENGTH METER (Entropy-based Scoring)
       ============================================================ */
    const newPass = document.getElementById('newPassword');
    const meter   = document.getElementById('strengthMeter');
    const label   = document.getElementById('strengthLabel');
    const bars    = meter ? meter.querySelectorAll('.strength-bar') : [];

    function calcPasswordScore(pwd) {
        if (!pwd) return { score: 0, label: '', feedback: '' };

        let score = 0;
        const len = pwd.length;

        // Length scoring
        if (len >= 8)  score += 1;
        if (len >= 12) score += 1;
        if (len >= 16) score += 1;

        // Character diversity
        if (/[a-z]/.test(pwd)) score += 1;
        if (/[A-Z]/.test(pwd)) score += 1;
        if (/[0-9]/.test(pwd)) score += 1;
        if (/[^A-Za-z0-9]/.test(pwd)) score += 1;

        // Bonus: campuran kompleks
        const unique = new Set(pwd).size;
        if (unique >= len * 0.7) score += 1;

        // Penalty: pola umum
        const patterns = ['password', '123456', 'qwerty', 'admin', 'welcome'];
        if (patterns.some(p => pwd.toLowerCase().includes(p))) score -= 2;

        // Normalisasi ke 0-5
        score = Math.max(0, Math.min(5, score));

        const labels = {
            0: { level: 'weak',   text: '⚠️ Sangat Lemah', fb: 'Tambahkan minimal 8 karakter.' },
            1: { level: 'weak',   text: '⚠️ Lemah',       fb: 'Tambahkan huruf besar, angka, atau simbol.' },
            2: { level: 'fair',   text: '🟡 Cukup',       fb: 'Hampir aman, tambahkan variasi.' },
            3: { level: 'fair',   text: '🟢 Baik',        fb: 'Cukup kuat untuk penggunaan biasa.' },
            4: { level: 'strong', text: '🔒 Kuat',        fb: 'Kata sandi sangat aman.' },
            5: { level: 'strong', text: '🛡️ Sangat Kuat', fb: 'Sempurna! Kata sandi tingkat militer.' },
        };

        return { score, ...labels[score] };
    }

    newPass?.addEventListener('input', () => {
        const v = newPass.value;
        if (!v) {
            meter.className = 'strength-meter';
            if (label) label.textContent = '';
            bars.forEach(bar => bar.style.width = '0');
            return;
        }

        const result = calcPasswordScore(v);
        meter.className = 'strength-meter strength-' + result.level;
        if (label) label.innerHTML = result.text + ' — <small>' + result.fb + '</small>';

        bars.forEach((bar, i) => {
            setTimeout(() => {
                bar.style.width = (i < result.score) ? '100%' : '0';
            }, i * 80);
        });
        markDirty();
    });

    /* ============================================================
       5. LIVE PREVIEW PROFILE CARD
       ============================================================ */
    const nameInput  = document.querySelector('input[name="full_name"]');
    const emailInput = document.querySelector('input[name="email"]');

    nameInput?.addEventListener('input', () => {
        const v = nameInput.value.trim();
        document.querySelectorAll('[data-live="name"]').forEach(el => {
            el.textContent = v || 'Nama Anda';
        });
        markDirty();
    });

    emailInput?.addEventListener('input', () => {
        const v = emailInput.value.trim();
        document.querySelectorAll('[data-live="email"]').forEach(el => {
            el.textContent = v || 'email@example.com';
        });
        markDirty();
    });

    /* ============================================================
       6. TOGGLE PASSWORD VISIBILITY
       ============================================================ */
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.togglePw;
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPw = input.type === 'password';
            input.type = isPw ? 'text' : 'password';
            const icon = btn.querySelector('i');
            if (icon) {
                icon.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    icon.className = 'ph ' + (isPw ? 'ph-eye-slash' : 'ph-eye');
                    icon.style.transform = '';
                }, 150);
            }
        });
    });

    /* ============================================================
       7. FORM SUBMISSION HANDLER (Enhanced)
       ============================================================ */
    const bindForm = (formId, url, onSuccess) => {
        const form = document.getElementById(formId);
        if (!form) return;

        // Track input untuk dirty state
        form.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('input', markDirty);
            el.addEventListener('change', markDirty);
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Clear previous errors
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });

            const btn = form.querySelector('button[type="submit"]');
            btn?.classList.add('is-loading');
            form.style.transition = 'opacity .3s';
            form.style.opacity = '0.7';

            try {
                const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
                const text = await res.text();
                let json;
                try { json = JSON.parse(text); }
                catch { throw new Error('Respons server tidak valid.'); }

                btn?.classList.remove('is-loading');
                form.style.opacity = '1';

                if (json.ok) {
                    form.style.borderColor = 'var(--ok)';
                    setTimeout(() => { form.style.borderColor = ''; }, 1200);
                    toast(json.message, 'success');
                    launchConfetti();
                    markClean();
                    if (onSuccess) setTimeout(onSuccess, 900);
                } else if (json.errors) {
                    Object.entries(json.errors).forEach(([k, v]) => {
                        const err = form.querySelector('[data-error="' + k + '"]');
                        if (err) {
                            err.textContent = v;
                            err.closest('.field')?.classList.add('has-error');
                        }
                    });
                    toast('Mohon periksa kembali isian Anda.', 'error');
                    form.style.animation = 'shake .4s';
                    setTimeout(() => { form.style.animation = ''; }, 400);
                } else {
                    toast(json.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (err) {
                btn?.classList.remove('is-loading');
                form.style.opacity = '1';
                toast('Koneksi ke server gagal.', 'error');
            }
        });
    };

    /* ============================================================
       8. SESSION MANAGEMENT (Revoke Other Sessions)
       ============================================================ */
    const csrf = () =>
        document.querySelector('input[name="csrf_token"]')?.value ||
        window.CSRF_TOKEN || '';

    document.querySelectorAll('[data-revoke-session]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const sid = btn.dataset.revokeSession;
            if (!confirm('Cabut sesi ini? Pengguna di perangkat tersebut akan dikeluarkan.')) return;

            btn.classList.add('is-loading');
            try {
                const fd = new FormData();
                fd.append('session_id', sid);
                fd.append('csrf_token', csrf());
                const res = await fetch(api('profile/revoke-session'), { method: 'POST', body: fd });
                const json = await res.json();
                btn.classList.remove('is-loading');
                toast(json.message, json.ok ? 'success' : 'error');
                if (json.ok) {
                    const li = btn.closest('li');
                    if (li) {
                        li.style.transition = 'all .3s';
                        li.style.opacity = '0';
                        li.style.transform = 'translateX(20px)';
                        setTimeout(() => li.remove(), 300);
                    }
                }
            } catch {
                btn.classList.remove('is-loading');
                toast('Gagal mencabut sesi.', 'error');
            }
        });
    });

    document.getElementById('btnRevokeAllSessions')?.addEventListener('click', async function() {
        if (!confirm('Cabut SEMUA sesi lain? Anda hanya akan tetap login di perangkat ini.')) return;
        this.classList.add('is-loading');
        try {
            const fd = new FormData();
            fd.append('csrf_token', csrf());
            const res = await fetch(api('profile/revoke-all'), { method: 'POST', body: fd });
            const json = await res.json();
            this.classList.remove('is-loading');
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) setTimeout(() => location.reload(), 800);
        } catch {
            this.classList.remove('is-loading');
            toast('Gagal mencabut sesi.', 'error');
        }
    });

    /* ============================================================
       9. EXPORT PERSONAL DATA (GDPR-like)
       ============================================================ */
    document.getElementById('btnExportMyData')?.addEventListener('click', async function() {
        if (!confirm('Unduh semua data pribadi Anda dalam format JSON?')) return;
        this.classList.add('is-loading');
        try {
            const res = await fetch(api('profile/export'));
            
            // Cek apakah response adalah JSON atau file
            const contentType = res.headers.get('content-type') || '';
            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }
            
            // Jika response adalah JSON error, parse dan tampilkan
            if (contentType.includes('application/json')) {
                const json = await res.json();
                if (!json.ok) {
                    throw new Error(json.message || 'Gagal mengekspor data');
                }
            }
            
            // Jika response adalah file/blob, download
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'data-saya-' + new Date().toISOString().slice(0, 10) + '.json';
            a.click();
            URL.revokeObjectURL(url);
            toast('Data pribadi berhasil diunduh.', 'success');
        } catch (err) {
            toast(err.message || 'Gagal mengekspor data.', 'error');
        } finally {
            this.classList.remove('is-loading');
        }
    });

    /* ============================================================
       10. DELETE ACCOUNT WARNING
       ============================================================ */
    document.getElementById('btnDeleteAccount')?.addEventListener('click', () => {
        const modal = document.getElementById('deleteAccountModal');
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        } else {
            alert('⚠️ Peringatan: Menghapus akun bersifat permanen dan tidak dapat dibatalkan. Hubungi administrator jika Anda yakin.');
        }
    });

    /* ============================================================
       11. 2FA TOGGLE HINT
       ============================================================ */
    document.getElementById('btnSetup2FA')?.addEventListener('click', () => {
        toast('Autentikasi 2 faktor akan segera tersedia.', 'success');
    });

    /* ============================================================
       12. FORM BINDINGS
       ============================================================ */
    bindForm('profileForm', api('profile/update'), () => {
        setTimeout(() => location.reload(), 1200);
    });

    bindForm('passwordForm', api('profile/password'), () => {
        document.getElementById('passwordForm')?.reset();
        if (meter) {
            meter.className = 'strength-meter';
            bars.forEach(bar => { bar.style.width = '0'; });
        }
        if (label) label.textContent = '';
    });

    /* ============================================================
       13. SESSION LIST HOVER EFFECT
       ============================================================ */
    document.querySelectorAll('.session-list li').forEach(li => {
        li.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(99,102,241,.08)';
        });
        li.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });

    /* ============================================================
       14. CONFETTI (Premium Burst)
       ============================================================ */
    function launchConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#ec4899'];
        for (let i = 0; i < 30; i++) {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.cssText = `
                position: fixed;
                width: ${6 + Math.random() * 4}px;
                height: ${6 + Math.random() * 4}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px;
                left: ${Math.random() * 100}vw;
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events: none;
                z-index: 9999;
                animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 4500);
        }
    }

    /* ============================================================
       15. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('profile')) return;
        if (document.querySelector('.modal-backdrop.show')) return;

        // Ctrl+S = Save profile
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
            const form = document.getElementById('profileForm');
            if (form && document.activeElement?.closest('#profileForm')) {
                e.preventDefault();
                form.requestSubmit();
            }
        }
    });

    console.log('%c👤 Profile Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();