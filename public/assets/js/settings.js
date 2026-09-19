// File: public/assets/js/settings.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const form = document.getElementById('settingsForm');
    if (!form) return;

    /* ========== 1. PREVIEW BERKAS DENGAN ANIMASI ========== */
    const bindPreview = (inputId, previewId) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        // Drag & drop support
        const zone = input.closest('.asset-field');
        if (zone) {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                preview.style.borderColor = 'var(--pri)';
                preview.style.background = 'rgba(99,102,241,.08)';
            });
            zone.addEventListener('dragleave', () => {
                preview.style.borderColor = '';
                preview.style.background = '';
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                preview.style.borderColor = '';
                preview.style.background = '';
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    handleFile(input.files[0]);
                }
            });
        }

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file) handleFile(file);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) {
                toast('Hanya file gambar yang didukung.', 'error');
                input.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                toast('Ukuran gambar maksimal 5 MB.', 'error');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.style.opacity = '0';
                preview.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.transition = 'opacity .3s, transform .3s';
                    preview.style.opacity = '1';
                    preview.style.transform = 'scale(1)';
                }, 150);
            };
            reader.readAsDataURL(file);
        }
    };

    bindPreview('logoInput', 'logoPreview');
    bindPreview('faviconInput', 'faviconPreview');

    /* ========== 2. HAPUS LOGO/FAVICON VIA API ========== */
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.remove;
            if (!confirm('Hapus ' + type + ' saat ini? Tindakan ini tidak dapat dibatalkan.')) return;

            const fd = new FormData();
            fd.append('csrf_token', form.querySelector('input[name="csrf_token"]').value);
            fd.append('type', type);

            btn.classList.add('is-loading');
            try {
                const res  = await fetch(BASE + 'settings/remove-asset', { method: 'POST', body: fd });
                const json = await res.json();
                btn.classList.remove('is-loading');

                if (json.ok) {
                    toast(json.message, 'success');
                    // Animasi penghapusan
                    const preview = document.getElementById(type + 'Preview');
                    if (preview) {
                        preview.style.transition = 'opacity .3s, transform .3s';
                        preview.style.opacity = '0';
                        preview.style.transform = 'scale(0.9)';
                    }
                    setTimeout(() => location.reload(), 700);
                } else {
                    toast(json.message || 'Gagal menghapus.', 'error');
                }
            } catch (err) {
                btn.classList.remove('is-loading');
                toast('Koneksi ke server gagal.', 'error');
            }
        });
    });

    /* ========== 3. LIVE PREVIEW NAMA ORGANISASI ========== */
    const appNameInput = document.querySelector('input[name="app_name"]');
    if (appNameInput) {
        appNameInput.addEventListener('input', () => {
            // Update preview di navbar jika ada
            const brandSpans = document.querySelectorAll('.pub-brand span, .side-brand .brand-text');
            brandSpans.forEach(span => {
                if (appNameInput.value.trim()) {
                    span.textContent = appNameInput.value;
                }
            });
        });
    }

    /* ========== 4. CHARACTER COUNTER UNTUK TEXTAREA ========== */
    document.querySelectorAll('textarea[data-maxlength]').forEach(ta => {
        const max = parseInt(ta.dataset.maxlength, 10);
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        counter.style.cssText = 'display:block;text-align:right;color:var(--txt-2);font-size:11px;margin-top:4px;';
        ta.parentElement.appendChild(counter);

        const update = () => {
            const len = ta.value.length;
            counter.textContent = `${len} / ${max} karakter`;
            counter.style.color = len > max * 0.9 ? 'var(--warn)' : 'var(--txt-2)';
            if (len > max) counter.style.color = 'var(--danger)';
        };
        ta.addEventListener('input', update);
        update();
    });

    /* ========== 5. SIMPAN FORM ========== */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');
        form.style.opacity = '0.85';

        try {
            const res  = await fetch(BASE + 'settings/save', { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            btn.classList.remove('is-loading');
            form.style.opacity = '1';

            if (json.ok) {
                form.style.borderColor = 'var(--ok)';
                form.style.boxShadow = '0 0 0 4px rgba(16,185,129,.15)';
                setTimeout(() => {
                    form.style.borderColor = '';
                    form.style.boxShadow = '';
                }, 1500);
                toast(json.message, 'success');
                createConfetti();
                setTimeout(() => location.reload(), 1200);
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
                form.style.animation = 'shake .4s';
                setTimeout(() => form.style.animation = '', 400);
            }
        } catch (err) {
            btn.classList.remove('is-loading');
            form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ========== 6. KONFETI ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#8b5cf6'];
        for (let i = 0; i < 30; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.cssText = `
                position: fixed;
                width: 8px; height: 8px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px; left: ${Math.random() * 100}vw;
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events: none; z-index: 9999;
                animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 4000);
        }
    }

    /* ========== 7. STICKY SAVE BAR VISIBILITY ========== */
    const stickyBar = document.querySelector('.settings-sticky');
    if (stickyBar) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    stickyBar.style.opacity = '1';
                    stickyBar.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0 });
        observer.observe(form);
    }

})();