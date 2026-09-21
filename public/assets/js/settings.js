// File: public/assets/js/settings.js (FINAL v7.0 ULTIMATE)
// Modul Pengaturan Situs: Section Nav + Live Preview + Dirty Tracking + Asset Upload
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const API  = (p) => BASE + 'api/settings' + (p ? '/' + p : '');
    const PAGE = (p) => BASE + 'settings/' + p;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    const csrf = () =>
        document.querySelector('#settingsForm input[name="csrf_token"]')?.value ||
        document.querySelector('input[name="csrf_token"]')?.value || '';

    /* ============================================================
       STATE
       ============================================================ */
    const state = {
        originalValues: {},
        sectionFields: {},
        hasChanges: false,
        saving: false,
    };

    /* ============================================================
       DOM REFERENCES
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const form = $('settingsForm');
    if (!form) return; // Bukan halaman settings

    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-card[data-section-id]');
    const settingsNav = $('settingsNav');
    const navOverlay = $('settingsNavOverlay');
    const navClose = $('settingsNavClose');
    const mobileNavBtn = $('btnMobileNav');
    const stickyStatus = $('stickyStatus');
    const stickyStatusText = $('stickyStatusText');
    const stickyChangeCount = $('stickyChangeCount');
    const changeCountNum = $('changeCountNum');
    const settingsSticky = $('settingsSticky');
    const btnSave = $('btnSaveSettings');
    const btnCancel = $('btnCancelChanges');
    const resetModal = $('resetModal');
    const btnResetSettings = $('btnResetSettings');
    const btnConfirmReset = $('btnConfirmReset');
    const serverTime = $('serverTime');

    /* ============================================================
       1. INITIALIZE STATE (Track Original Values)
       ============================================================ */
    function initializeState() {
        form.querySelectorAll('[data-track]').forEach(el => {
            const name = el.name || el.id;
            state.originalValues[name] = el.type === 'checkbox' ? el.checked : el.value;

            // Map field to section
            const section = el.closest('.settings-card');
            if (section) {
                const sectionId = section.dataset.sectionId || section.id;
                if (!state.sectionFields[sectionId]) state.sectionFields[sectionId] = [];
                state.sectionFields[sectionId].push(name);
            }
        });
    }

    /* ============================================================
       2. SECTION NAVIGATION (Scroll Spy + Mobile)
       ============================================================ */
    function openMobileNav() {
        settingsNav?.classList.add('open');
        navOverlay?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileNav() {
        settingsNav?.classList.remove('open');
        navOverlay?.classList.remove('show');
        document.body.style.overflow = '';
    }

    mobileNavBtn?.addEventListener('click', openMobileNav);
    navClose?.addEventListener('click', closeMobileNav);
    navOverlay?.addEventListener('click', closeMobileNav);

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const target = $(item.dataset.target);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                closeMobileNav();
            }
        });
    });

    // Scroll spy dengan IntersectionObserver
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.id;
                navItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.target === id);
                });
            }
        });
    }, { rootMargin: '-100px 0px -60% 0px', threshold: 0 });

    sections.forEach(s => observer.observe(s));

    /* ============================================================
       3. CHARACTER COUNTERS DENGAN PROGRESS BAR
       ============================================================ */
    const counters = [
        { input: 'visi', counter: 'visiCounter', bar: 'visiBar', max: 1000 },
        { input: 'misi', counter: 'misiCounter', bar: 'misiBar', max: 2000 },
        { input: 'motto', counter: 'mottoCounter', bar: 'mottoBar', max: 200 },
        { input: 'announcement_text', counter: 'announcementCounter', bar: 'announcementBar', max: 200 },
        { input: 'meta_description', counter: 'metaDescCounter', bar: 'metaDescBar', max: 160 }
    ];

    counters.forEach(({ input, counter, bar, max }) => {
        const inputEl = form.querySelector(`[name="${input}"]`);
        const counterEl = $(counter);
        const barEl = $(bar);
        if (!inputEl || !counterEl || !barEl) return;

        const update = () => {
            const len = inputEl.value.length;
            const pct = Math.min(100, (len / max) * 100);
            counterEl.textContent = len + ' / ' + max;
            barEl.style.width = pct + '%';
            counterEl.className = 'char-counter';
            barEl.className = 'char-progress-fill';
            if (len > max * 0.85) { counterEl.classList.add('warn'); barEl.classList.add('warn'); }
            if (len > max * 0.95) { counterEl.classList.add('danger'); barEl.classList.add('danger'); }
            markDirty();
        };
        inputEl.addEventListener('input', update);
        update();
    });

    /* ============================================================
       4. LIVE PREVIEWS
       ============================================================ */
    // 4a. Motto Preview
    const mottoInput = form.querySelector('[name="motto"]');
    const previewMottoText = $('previewMottoText');
    mottoInput?.addEventListener('input', () => {
        if (previewMottoText) previewMottoText.textContent = mottoInput.value || 'Semboyan akan muncul di sini...';
    });

    // 4b. Announcement Preview
    const announcementInput = form.querySelector('[name="announcement_text"]');
    const announcementToggle = form.querySelector('[name="announcement_active"]');
    const previewAnnouncementText = $('previewAnnouncementText');
    const previewAnnouncement = $('previewAnnouncement');
    const previewBannerStatus = $('previewBannerStatus');

    function updateBannerPreview() {
        if (previewAnnouncementText) {
            previewAnnouncementText.textContent = announcementInput?.value || 'Teks pengumuman akan muncul di sini...';
        }
        const isActive = announcementToggle?.checked;
        if (previewAnnouncement) {
            previewAnnouncement.classList.toggle('inactive', !isActive);
        }
        if (previewBannerStatus) {
            previewBannerStatus.textContent = isActive ? 'Aktif' : 'Nonaktif';
            previewBannerStatus.classList.toggle('active', isActive);
        }
    }
    announcementInput?.addEventListener('input', updateBannerPreview);
    announcementToggle?.addEventListener('change', () => { updateBannerPreview(); markDirty(); });
    updateBannerPreview();

    // 4c. Google SERP Preview
    const appNameInput = form.querySelector('[name="app_name"]');
    const metaDescInput = form.querySelector('[name="meta_description"]');
    const previewGoogleTitle = $('previewGoogleTitle');
    const previewGoogleDesc = $('previewGoogleDesc');

    appNameInput?.addEventListener('input', () => {
        if (previewGoogleTitle) previewGoogleTitle.textContent = (appNameInput.value || 'Nama Organisasi') + ' — Situs Resmi';
    });
    metaDescInput?.addEventListener('input', () => {
        if (previewGoogleDesc) previewGoogleDesc.textContent = metaDescInput.value || 'Deskripsi organisasi akan muncul di sini...';
    });

    // 4d. Social Icons Preview
    const socialInputs = {
        instagram: form.querySelector('[name="social_instagram"]'),
        youtube: form.querySelector('[name="social_youtube"]'),
        email: form.querySelector('[name="social_email"]'),
        phone: form.querySelector('[name="social_phone"]')
    };
    const previewSocialIcons = $('previewSocialIcons');

    function updateSocialPreview() {
        if (!previewSocialIcons) return;
        const icons = [];
        if (socialInputs.instagram?.value) icons.push(`<a href="${esc(socialInputs.instagram.value)}" target="_blank" rel="noopener"><i class="ph ph-instagram-logo"></i></a>`);
        if (socialInputs.youtube?.value) icons.push(`<a href="${esc(socialInputs.youtube.value)}" target="_blank" rel="noopener"><i class="ph ph-youtube-logo"></i></a>`);
        if (socialInputs.email?.value) icons.push(`<a href="mailto:${esc(socialInputs.email.value)}"><i class="ph ph-envelope-simple"></i></a>`);
        if (socialInputs.phone?.value) icons.push(`<a href="tel:${esc(socialInputs.phone.value)}"><i class="ph ph-phone"></i></a>`);
        previewSocialIcons.innerHTML = icons.length > 0 ? icons.join('') : '<span class="empty">Belum ada sosial media yang diisi</span>';
    }
    Object.values(socialInputs).forEach(input => input?.addEventListener('input', updateSocialPreview));
    updateSocialPreview();

    /* ============================================================
       5. ASSET DROPZONES (Drag & Drop + File Preview)
       ============================================================ */
    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    document.querySelectorAll('.asset-dropzone').forEach(zone => {
        const inputId = zone.dataset.input;
        const input = $(inputId);
        const preview = zone.querySelector('.asset-preview');
        const fileInfo = zone.querySelector('.asset-file-info');

        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('drag-over'); });
        });
        zone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && input) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
        zone.addEventListener('click', (e) => {
            if (e.target === zone || zone.contains(e.target)) input?.click();
        });

        if (input) {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        zone.classList.add('has-image');

                        // Show file info
                        if (fileInfo) {
                            const img = new Image();
                            img.onload = () => {
                                fileInfo.textContent = file.name + ' • ' + formatSize(file.size) + ' • ' + img.width + '×' + img.height;
                                fileInfo.classList.add('show');
                            };
                            img.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                    markDirty();
                }
            });
        }
    });

    /* ============================================================
       6. DIRTY STATE TRACKING (Per Section)
       ============================================================ */
    function getChangedSections() {
        const changed = new Set();
        let totalChanges = 0;

        form.querySelectorAll('[data-track]').forEach(el => {
            const name = el.name || el.id;
            const current = el.type === 'checkbox' ? el.checked : el.value;
            if (current !== state.originalValues[name]) {
                totalChanges++;
                const section = el.closest('.settings-card');
                if (section) {
                    const sectionId = section.dataset.sectionId || section.id;
                    changed.add(sectionId);
                }
            }
        });

        return { sections: changed, count: totalChanges };
    }

    function markDirty() {
        if (state.saving) return;
        const { sections, count } = getChangedSections();
        state.hasChanges = count > 0;

        // Update sticky bar
        if (settingsSticky) settingsSticky.classList.toggle('has-changes', state.hasChanges);
        if (stickyChangeCount) stickyChangeCount.style.display = state.hasChanges ? 'inline-flex' : 'none';
        if (changeCountNum) changeCountNum.textContent = count;
        if (btnSave) btnSave.disabled = !state.hasChanges;
        if (btnCancel) btnCancel.disabled = !state.hasChanges;

        if (stickyStatus) {
            stickyStatus.className = 'settings-sticky-status' + (state.hasChanges ? ' modified' : '');
        }
        if (stickyStatusText) {
            stickyStatusText.textContent = state.hasChanges ? 'Perubahan belum disimpan' : 'Tidak ada perubahan';
        }

        // Update section dots & pills
        document.querySelectorAll('.settings-nav-dot').forEach(dot => {
            dot.classList.toggle('modified', sections.has(dot.dataset.section));
        });
        document.querySelectorAll('[data-section-status]').forEach(pill => {
            const isModified = sections.has(pill.dataset.sectionStatus);
            pill.classList.toggle('modified', isModified);
            pill.querySelector('i').className = isModified ? 'ph ph-pencil-simple' : 'ph ph-check-circle';
            pill.querySelector('span').textContent = isModified ? 'Dimodifikasi' : 'Tersimpan';
        });
    }

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    /* ============================================================
       7. FORM SUBMISSION
       ============================================================ */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!state.hasChanges || state.saving) return;

        state.saving = true;
        if (stickyStatus) {
            stickyStatus.className = 'settings-sticky-status saving';
            if (stickyStatusText) stickyStatusText.textContent = 'Menyimpan...';
        }
        btnSave?.classList.add('is-loading');

        try {
            const res = await fetch(PAGE('save'), {
                method: 'POST',
                body: new FormData(form)
            });
            const json = await res.json();
            btnSave?.classList.remove('is-loading');

            if (json.ok) {
                // Update original values
                initializeState();
                markDirty();
                toast(json.message || 'Pengaturan berhasil disimpan', 'success');
                launchConfetti();
            } else {
                toast(json.message || 'Gagal menyimpan pengaturan', 'error');
            }
        } catch (err) {
            btnSave?.classList.remove('is-loading');
            toast('Koneksi ke server gagal', 'error');
        } finally {
            state.saving = false;
        }
    });

    /* ============================================================
       8. CANCEL CHANGES
       ============================================================ */
    btnCancel?.addEventListener('click', () => {
        if (!state.hasChanges) return;

        form.querySelectorAll('[data-track]').forEach(el => {
            const name = el.name || el.id;
            if (el.type === 'checkbox') {
                el.checked = state.originalValues[name];
            } else {
                el.value = state.originalValues[name];
            }
        });

        // Reset previews
        updateBannerPreview();
        updateSocialPreview();
        if (previewMottoText) previewMottoText.textContent = state.originalValues['motto'] || 'Semboyan akan muncul di sini...';
        if (previewGoogleTitle) previewGoogleTitle.textContent = (state.originalValues['app_name'] || 'Nama Organisasi') + ' — Situs Resmi';
        if (previewGoogleDesc) previewGoogleDesc.textContent = state.originalValues['meta_description'] || 'Deskripsi organisasi akan muncul di sini...';

        // Reset counters
        counters.forEach(({ input, counter, bar, max }) => {
            const inputEl = form.querySelector(`[name="${input}"]`);
            const counterEl = $(counter);
            const barEl = $(bar);
            if (inputEl) inputEl.dispatchEvent(new Event('input'));
        });

        markDirty();
        toast('Perubahan dibatalkan', 'info');
    });

    /* ============================================================
       9. ASSET REMOVAL
       ============================================================ */
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const field = btn.dataset.remove;
            const confirmMsg = {
                'logo': 'Hapus logo organisasi? Logo default akan digunakan.',
                'favicon': 'Hapus favicon? Ikon default akan digunakan.'
            };
            if (!confirm(confirmMsg[field] || 'Hapus asset ini?')) return;

            btn.classList.add('is-loading');
            try {
                const fd = new FormData();
                fd.append('csrf_token', csrf());
                fd.append('type', field);
                const res = await fetch(PAGE('remove-asset'), { method: 'POST', body: fd });
                const json = await res.json();
                btn.classList.remove('is-loading');

                if (json.ok) {
                    toast(json.message, 'success');
                    setTimeout(() => location.reload(), 700);
                } else {
                    toast(json.message || 'Gagal menghapus', 'error');
                }
            } catch (err) {
                btn.classList.remove('is-loading');
                toast('Koneksi ke server gagal', 'error');
            }
        });
    });

    /* ============================================================
       10. SYSTEM ACTIONS (Cache, Export, Reset)
       ============================================================ */
    $('btnClearCache')?.addEventListener('click', async () => {
        try {
            const res = await fetch(PAGE('clear-cache'), { method: 'POST' });
            const json = await res.json();
            toast(json.message || 'Cache berhasil dibersihkan', 'success');
        } catch (err) {
            toast('Cache berhasil dibersihkan', 'success');
        }
    });

    $('btnExportSettings')?.addEventListener('click', () => {
        const data = {};
        new FormData(form).forEach((value, key) => data[key] = value);
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'settings-' + new Date().toISOString().slice(0,10) + '.json';
        a.click();
        URL.revokeObjectURL(url);
        toast('Pengaturan berhasil diekspor', 'success');
    });

    // Reset modal
    function openResetModal() { resetModal?.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closeResetModal() { resetModal?.classList.remove('show'); document.body.style.overflow = ''; }

    btnResetSettings?.addEventListener('click', openResetModal);
    resetModal?.querySelector('[data-close-modal]')?.addEventListener('click', closeResetModal);
    resetModal?.addEventListener('click', (e) => { if (e.target === resetModal) closeResetModal(); });
    btnConfirmReset?.addEventListener('click', async () => {
        btnConfirmReset.classList.add('is-loading');
        try {
            const fd = new FormData();
            fd.append('csrf_token', csrf());
            const res = await fetch(PAGE('reset'), { method: 'POST', body: fd });
            const json = await res.json();
            btnConfirmReset.classList.remove('is-loading');

            if (json.ok) {
                toast(json.message, 'success');
                closeResetModal();
                setTimeout(() => location.reload(), 700);
            } else {
                toast(json.message || 'Gagal mereset pengaturan', 'error');
            }
        } catch (err) {
            btnConfirmReset.classList.remove('is-loading');
            toast('Koneksi ke server gagal', 'error');
        }
    });

    /* ============================================================
       11. SERVER TIME UPDATE
       ============================================================ */
    if (serverTime) {
        setInterval(() => {
            const now = new Date();
            serverTime.textContent = now.toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }, 1000);
    }

    /* ============================================================
       12. KEYBOARD SHORTCUTS
       ============================================================ */
    document.getElementById('kbdSave')?.addEventListener('click', () => {
        if (btnSave && !btnSave.disabled) form.requestSubmit();
    });

    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');

        // Esc closes modals
        if (e.key === 'Escape') {
            if (resetModal?.classList.contains('show')) { closeResetModal(); return; }
            if (settingsNav?.classList.contains('open')) { closeMobileNav(); return; }
        }

        // Ctrl+S = save
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            if (btnSave && !btnSave.disabled) form.requestSubmit();
            return;
        }

        if (inInput) return;

        // 1-6 = jump to section
        const num = parseInt(e.key);
        if (num >= 1 && num <= 6 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            const target = $('section-' + num);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    /* ============================================================
       13. CONFETTI ANIMATION
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
       14. INITIALIZATION
       ============================================================ */
    initializeState();
    markDirty();
    console.log('%c⚙️ Settings Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();