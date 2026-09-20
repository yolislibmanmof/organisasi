// File: public/assets/js/settings.js (FINAL v7.0 ULTIMATE)
// Modul Pengaturan Situs: Tabs + Live Preview + Unsaved Warning + Validation
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const form = document.getElementById('settingsForm');
    if (!form) return;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    /* ============================================================
       STATE: Unsaved Changes Tracking
       ============================================================ */
    const initialFormData = new FormData(form);
    const initialState = {};
    for (const [key, val] of initialFormData.entries()) {
        initialState[key] = val;
    }
    let hasUnsavedChanges = false;

    // Warn saat meninggalkan halaman dengan perubahan belum disimpan
    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    function markDirty() {
        const current = new FormData(form);
        for (const [key, val] of current.entries()) {
            if (initialState[key] !== String(val)) {
                hasUnsavedChanges = true;
                updateDirtyIndicator(true);
                return;
            }
        }
        // Cek apakah ada key di initial tapi tidak di current
        for (const key of Object.keys(initialState)) {
            if (!current.has(key)) {
                hasUnsavedChanges = true;
                updateDirtyIndicator(true);
                return;
            }
        }
        hasUnsavedChanges = false;
        updateDirtyIndicator(false);
    }

    function updateDirtyIndicator(dirty) {
        const indicator = document.getElementById('settingsDirtyIndicator');
        if (indicator) {
            indicator.style.display = dirty ? 'inline-flex' : 'none';
        }
        const saveBtn = form.querySelector('button[type="submit"]');
        if (saveBtn) {
            saveBtn.classList.toggle('pulse-soft', dirty);
        }
    }

    /* ============================================================
       1. TABS SWITCHING (Smooth)
       ============================================================ */
    const tabBtns = document.querySelectorAll('[data-tab]');
    const tabPanels = document.querySelectorAll('.settings-card[data-tab-panel]');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;
            tabBtns.forEach(b => b.classList.toggle('tab-active', b === btn));

            tabPanels.forEach(panel => {
                const isTarget = panel.dataset.tabPanel === target;
                panel.style.display = isTarget ? 'block' : 'none';
                if (isTarget) {
                    panel.style.animation = 'fade-up .4s cubic-bezier(.22,1,.36,1) both';
                }
            });

            // Persist tab di localStorage
            try {
                localStorage.setItem('settings_active_tab', target);
            } catch (e) { /* Silent */ }

            // Scroll ke top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    // Restore last active tab
    try {
        const lastTab = localStorage.getItem('settings_active_tab');
        if (lastTab) {
            const btn = document.querySelector(`[data-tab="${lastTab}"]`);
            if (btn) btn.click();
        }
    } catch (e) { /* Silent */ }

    /* ============================================================
       2. FILE PREVIEW (Logo & Favicon) dengan Drag & Drop
       ============================================================ */
    const bindPreview = (inputId, previewId, maxSize = 2) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        const zone = input.closest('.asset-field');

        // Drag & drop handlers
        if (zone) {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                preview.style.borderColor = 'var(--pri)';
                preview.style.background = 'rgba(99,102,241,.08)';
                zone.classList.add('drag-over');
            });
            zone.addEventListener('dragleave', () => {
                preview.style.borderColor = '';
                preview.style.background = '';
                zone.classList.remove('drag-over');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                preview.style.borderColor = '';
                preview.style.background = '';
                zone.classList.remove('drag-over');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    handleFile(e.dataTransfer.files[0]);
                }
            });
        }

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file) handleFile(file);
        });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) {
                toast('Hanya file gambar yang didukung (JPG, PNG, WEBP).', 'error');
                input.value = '';
                return;
            }
            if (file.size > maxSize * 1024 * 1024) {
                toast(`Ukuran gambar maksimal ${maxSize} MB.`, 'error');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.style.transition = 'opacity .3s, transform .3s';
                preview.style.opacity = '0';
                preview.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="loaded">`;
                    preview.style.opacity = '1';
                    preview.style.transform = 'scale(1)';
                    markDirty();
                }, 150);
            };
            reader.readAsDataURL(file);
        }
    };

    bindPreview('logoInput', 'logoPreview', 2);
    bindPreview('faviconInput', 'faviconPreview', 1);

    /* ============================================================
       3. REMOVE ASSET (Logo/Favicon) via API
       ============================================================ */
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.remove;
            const confirmMsg = {
                'logo': 'Hapus logo organisasi saat ini? Logo default akan digunakan.',
                'favicon': 'Hapus favicon saat ini? Ikon default akan digunakan.'
            };
            if (!confirm(confirmMsg[type] || 'Hapus asset ini?')) return;

            const fd = new FormData();
            fd.append('csrf_token', form.querySelector('input[name="csrf_token"]')?.value || '');
            fd.append('type', type);

            btn.classList.add('is-loading');
            try {
                const res = await fetch(BASE + 'settings/remove-asset', { method: 'POST', body: fd });
                const json = await res.json();
                btn.classList.remove('is-loading');

                if (json.ok) {
                    toast(json.message, 'success');
                    const preview = document.getElementById(type + 'Preview');
                    if (preview) {
                        preview.style.transition = 'opacity .3s, transform .3s';
                        preview.style.opacity = '0';
                        preview.style.transform = 'scale(0.9)';
                    }
                    markDirty();
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

    /* ============================================================
       4. LIVE PREVIEW (Multi-target)
       ============================================================ */
    const appNameInput = form.querySelector('input[name="app_name"]');
    if (appNameInput) {
        appNameInput.addEventListener('input', () => {
            const val = appNameInput.value.trim() || 'Organisasi';
            // Update semua elemen yang menampilkan nama
            document.querySelectorAll('.pub-brand span, .side-brand .brand-text, .brand-name, .brand-logo-text')
                .forEach(span => { span.textContent = val; });
            // Update title tab
            document.title = val + ' — Pengaturan';
        });
    }

    const mottoInput = form.querySelector('input[name="motto"]');
    if (mottoInput) {
        mottoInput.addEventListener('input', () => {
            document.querySelectorAll('.brand-sub, [data-live="motto"]').forEach(el => {
                el.textContent = mottoInput.value || 'Motto organisasi Anda';
            });
        });
    }

    /* ============================================================
       5. SOCIAL LINKS PREVIEW
       ============================================================ */
    ['instagram', 'facebook', 'twitter', 'youtube', 'tiktok'].forEach(platform => {
        const input = form.querySelector(`input[name="social_${platform}"]`);
        if (!input) return;

        input.addEventListener('input', () => {
            const val = input.value.trim();
            const target = document.querySelector(`[data-social="${platform}"]`);
            if (target) {
                if (val) {
                    target.href = val;
                    target.style.opacity = '1';
                    target.style.pointerEvents = 'auto';
                } else {
                    target.style.opacity = '0.3';
                    target.style.pointerEvents = 'none';
                }
            }
        });
    });

    /* ============================================================
       6. CHARACTER COUNTER untuk Textarea
       ============================================================ */
    form.querySelectorAll('textarea[data-maxlength]').forEach(ta => {
        const max = parseInt(ta.dataset.maxlength, 10);
        let counter = ta.parentElement.querySelector('.char-counter');

        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'char-counter';
            counter.style.cssText = 'display:block;text-align:right;color:var(--txt-2);font-size:11px;margin-top:4px;font-weight:600';
            ta.parentElement.appendChild(counter);
        }

        const update = () => {
            const len = ta.value.length;
            counter.textContent = `${len} / ${max} karakter`;
            if (len > max) {
                counter.style.color = 'var(--danger-2)';
                ta.style.borderColor = 'var(--danger)';
            } else if (len > max * 0.9) {
                counter.style.color = 'var(--warn)';
                ta.style.borderColor = '';
            } else {
                counter.style.color = 'var(--txt-2)';
                ta.style.borderColor = '';
            }
        };

        ta.addEventListener('input', update);
        update();
    });

    /* ============================================================
       7. FORM VALIDATION
       ============================================================ */
    function validateForm() {
        const errors = [];
        const appName = form.querySelector('input[name="app_name"]')?.value.trim();
        const email   = form.querySelector('input[name="social_email"]')?.value.trim();
        const phone   = form.querySelector('input[name="social_phone"]')?.value.trim();

        if (!appName || appName.length < 3) {
            errors.push('Nama organisasi minimal 3 karakter.');
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Format email kontak tidak valid.');
        }
        if (phone) {
            const digits = phone.replace(/\D/g, '');
            if (digits.length < 8 || digits.length > 15) {
                errors.push('Nomor telepon tidak valid (8-15 digit).');
            }
        }

        // Cek textarea max length
        form.querySelectorAll('textarea[data-maxlength]').forEach(ta => {
            const max = parseInt(ta.dataset.maxlength, 10);
            if (ta.value.length > max) {
                errors.push(`Field "${ta.name}" melebihi batas ${max} karakter.`);
            }
        });

        return errors;
    }

    /* ============================================================
       8. ANNOUNCEMENT TOGGLE (Preview)
       ============================================================ */
    const announcementToggle = form.querySelector('input[name="announcement_active"]');
    if (announcementToggle) {
        announcementToggle.addEventListener('change', () => {
            document.querySelectorAll('[data-live="announcement"]').forEach(el => {
                el.style.display = announcementToggle.checked ? 'flex' : 'none';
            });
        });
    }

    /* ============================================================
       9. SAVE FORM (dengan Validation)
       ============================================================ */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validate
        const errors = validateForm();
        if (errors.length > 0) {
            toast(errors[0], 'error');
            form.style.animation = 'shake .4s';
            setTimeout(() => { form.style.animation = ''; }, 400);
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        btn?.classList.add('is-loading');
        form.style.opacity = '0.85';

        try {
            const res = await fetch(BASE + 'settings/save', {
                method: 'POST',
                body: new FormData(form)
            });
            const json = await res.json();
            btn?.classList.remove('is-loading');
            form.style.opacity = '1';

            if (json.ok) {
                form.style.borderColor = 'var(--ok)';
                form.style.boxShadow = '0 0 0 4px rgba(16,185,129,.15)';
                setTimeout(() => {
                    form.style.borderColor = '';
                    form.style.boxShadow = '';
                }, 1500);
                toast(json.message, 'success');
                launchConfetti();
                hasUnsavedChanges = false;
                updateDirtyIndicator(false);

                // Reload setelah animasi
                setTimeout(() => location.reload(), 1200);
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
                form.style.animation = 'shake .4s';
                setTimeout(() => { form.style.animation = ''; }, 400);
            }
        } catch (err) {
            btn?.classList.remove('is-loading');
            form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ============================================================
       10. CONFETTI ANIMATION (Enhanced)
       ============================================================ */
    function launchConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#8b5cf6'];
        for (let i = 0; i < 40; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.cssText = `
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
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 4500);
        }
    }

    /* ============================================================
       11. STICKY SAVE BAR (Smooth Show/Hide)
       ============================================================ */
    const stickyBar = document.querySelector('.settings-sticky');
    if (stickyBar) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    stickyBar.style.opacity = '1';
                    stickyBar.style.transform = 'translateY(0)';
                    stickyBar.style.pointerEvents = 'auto';
                } else {
                    stickyBar.style.opacity = '0';
                    stickyBar.style.transform = 'translateY(20px)';
                    stickyBar.style.pointerEvents = 'none';
                }
            });
        }, { threshold: 0 });

        observer.observe(form);

        // Initialize hidden
        stickyBar.style.opacity = '0';
        stickyBar.style.transform = 'translateY(20px)';
        stickyBar.style.pointerEvents = 'none';
        stickyBar.style.transition = 'opacity .3s, transform .3s';
    }

    /* ============================================================
       12. SECTION COLLAPSE/EXPAND (v7.0)
       ============================================================ */
    document.querySelectorAll('.settings-head').forEach(head => {
        head.style.cursor = 'pointer';
        head.addEventListener('click', () => {
            const body = head.nextElementSibling;
            if (!body) return;
            const isCollapsed = body.style.display === 'none';
            body.style.display = isCollapsed ? 'block' : 'none';
            body.style.animation = isCollapsed ? 'fade-up .3s both' : '';

            // Toggle icon
            const icon = head.querySelector('.collapse-icon');
            if (icon) {
                icon.style.transform = isCollapsed ? 'rotate(0)' : 'rotate(-90deg)';
            }

            // Persist state
            const sectionId = head.closest('.settings-card')?.dataset.tabPanel || '';
            try {
                const collapsed = JSON.parse(localStorage.getItem('settings_collapsed') || '{}');
                collapsed[sectionId] = !isCollapsed;
                localStorage.setItem('settings_collapsed', JSON.stringify(collapsed));
            } catch (e) { /* Silent */ }
        });
    });

    // Restore collapsed states
    try {
        const collapsed = JSON.parse(localStorage.getItem('settings_collapsed') || '{}');
        Object.entries(collapsed).forEach(([id, isCollapsed]) => {
            if (isCollapsed) {
                const card = document.querySelector(`[data-tab-panel="${id}"]`);
                if (card) {
                    const body = card.querySelector('.settings-body');
                    const icon = card.querySelector('.collapse-icon');
                    if (body) body.style.display = 'none';
                    if (icon) icon.style.transform = 'rotate(-90deg)';
                }
            }
        });
    } catch (e) { /* Silent */ }

    /* ============================================================
       13. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('settings')) return;

        // Ctrl+S = Save
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            form.requestSubmit();
        }

        // Ctrl+R = Reset form
        if ((e.metaKey || e.ctrlKey) && e.shiftKey && e.key.toLowerCase() === 'r') {
            e.preventDefault();
            if (confirm('Reset semua perubahan? Tindakan ini tidak dapat dibatalkan.')) {
                form.reset();
                hasUnsavedChanges = false;
                updateDirtyIndicator(false);
                toast('Form direset ke nilai awal', 'success');
            }
        }
    });

    /* ============================================================
       14. INITIALIZATION
       ============================================================ */
    console.log('%c⚙️ Settings Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();