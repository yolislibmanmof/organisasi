// File: public/assets/js/profile.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    /* ========== 1. DETEKSI PERAMBAN ========== */
    const ua = navigator.userAgent;
    let browser = 'Peramban tidak dikenal';
    let browserIcon = 'ph-globe';
    if (ua.includes('Edg/'))          { browser = 'Microsoft Edge'; browserIcon = 'ph-microsoft-edge-logo'; }
    else if (ua.includes('OPR/'))     { browser = 'Opera'; browserIcon = 'ph-opera-logo'; }
    else if (ua.includes('Firefox/')) { browser = 'Mozilla Firefox'; browserIcon = 'ph-firefox-logo'; }
    else if (ua.includes('Chrome/'))  { browser = 'Google Chrome'; browserIcon = 'ph-google-chrome-logo'; }
    else if (ua.includes('Safari/'))  { browser = 'Safari'; browserIcon = 'ph-safari-logo'; }
    const uaEl = document.getElementById('uaName');
    if (uaEl) uaEl.innerHTML = `<i class="ph ${browserIcon}"></i> ${browser}`;

    /* ========== 2. UPLOAD ZONE — DRAG & DROP + PREVIEW ========== */
    const zone  = document.getElementById('uploadZone');
    const input = document.getElementById('photoInput');

    if (zone && input) {
        zone.addEventListener('click', () => input.click());

        // Drag & drop support
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
            toast('Hanya file gambar yang didukung.', 'error');
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
                setTimeout(() => img.style.transform = 'scale(1)', 200);
            };
        };
        reader.readAsDataURL(file);
    }

    /* ========== 3. PASSWORD STRENGTH METER ENHANCED ========== */
    const newPass = document.getElementById('newPassword');
    const meter   = document.getElementById('strengthMeter');
    const label   = document.getElementById('strengthLabel');
    const bars    = meter ? meter.querySelectorAll('.strength-bar') : [];

    newPass?.addEventListener('input', () => {
        const v = newPass.value;
        let s = 0;
        if (v.length >= 8) s++;
        if (v.length >= 12) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;

        meter.className = 'strength-meter';
        if (v === '') { 
            label.textContent = ''; 
            bars.forEach(bar => bar.style.width = '0');
            return; 
        }

        if (s <= 2) { 
            meter.classList.add('strength-weak'); 
            label.innerHTML = '<i class="ph ph-warning"></i> Lemah — tambahkan huruf besar, angka, atau simbol.';
        } else if (s <= 3) { 
            meter.classList.add('strength-fair'); 
            label.innerHTML = '<i class="ph ph-info"></i> Cukup — hampir aman.';
        } else { 
            meter.classList.add('strength-strong'); 
            label.innerHTML = '<i class="ph ph-shield-check"></i> Kuat — kata sandi sangat aman.';
        }

        bars.forEach((bar, i) => {
            setTimeout(() => {
                bar.style.width = (i < s) ? '100%' : '0';
            }, i * 100);
        });
    });

    /* ========== 4. FORM SUBMISSION HANDLER ========== */
    const bindForm = (formId, url, onSuccess) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Clear previous errors
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });

            const btn = form.querySelector('button[type="submit"]');
            btn.classList.add('is-loading');

            // Animasi form
            form.style.transition = 'opacity .3s';
            form.style.opacity = '0.7';

            try {
                const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
                const json = await res.json();

                btn.classList.remove('is-loading');
                form.style.opacity = '1';

                if (json.ok) {
                    // Success animation
                    form.style.borderColor = 'var(--ok)';
                    setTimeout(() => form.style.borderColor = '', 1000);
                    
                    toast(json.message, 'success');
                    
                    // Konfeti sederhana
                    createConfetti();
                    
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
                    
                    // Shake animation
                    form.style.animation = 'shake .4s';
                    setTimeout(() => form.style.animation = '', 400);
                } else {
                    toast(json.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (err) {
                btn.classList.remove('is-loading');
                form.style.opacity = '1';
                toast('Koneksi ke server gagal.', 'error');
            }
        });
    };

    /* ========== 5. KONFETI SEDERHANA ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b'];
        for (let i = 0; i < 30; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.cssText = `
                position: fixed;
                width: 8px;
                height: 8px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px;
                left: ${Math.random() * 100}vw;
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events: none;
                z-index: 9999;
                animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 4000);
        }
    }

    /* ========== 6. FORM BINDINGS ========== */
    bindForm('profileForm', api('profile/update'), () => {
        setTimeout(() => location.reload(), 1200);
    });
    bindForm('passwordForm', api('profile/password'), () => {
        document.getElementById('passwordForm').reset();
        if (meter) {
            meter.className = 'strength-meter';
            bars.forEach(bar => bar.style.width = '0');
        }
        if (label) label.textContent = '';
    });

    /* ========== 7. SESSION INFO TOOLTIP ========== */
    document.querySelectorAll('.session-list li').forEach(li => {
        li.addEventListener('mouseenter', function() {
            this.style.background = 'rgba(99,102,241,.08)';
        });
        li.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });

})();