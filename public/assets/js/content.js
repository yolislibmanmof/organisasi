// File: public/assets/js/content.js (FINAL v7.0 ULTIMATE)
// Modul Manajemen Konten: Pengurus + Testimoni + Galeri
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const api  = (p) => BASE + p;

    const LABELS = { officers: 'Pengurus', testimonials: 'Testimoni', galleries: 'Foto Galeri' };
    const MODALS = { officers: 'officerModal', testimonials: 'testimonialModal', galleries: 'galleryModal' };
    const FORMS  = { officers: 'officerForm',  testimonials: 'testimonialForm',  galleries: 'galleryForm'  };
    const ID_ELS = { officers: 'oId',          testimonials: 'tId',              galleries: 'gId'          };

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const formatDate = (d) => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
        : '';

    const csrfToken = () =>
        document.querySelector('#officerForm input[name="csrf_token"]')?.value ||
        document.querySelector('input[name="csrf_token"]')?.value ||
        window.CSRF_TOKEN || '';

    /* ============================================================
       STATE
       ============================================================ */
    let type = 'officers';
    let currentData = [];
    let deleteId = null;
    let abortCtrl = null;
    const selected = new Set();
    const state = { q: '', status: '', year: '', division: '' };

    /* ============================================================
       DOM REFERENCES
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const grid     = $('contentGrid');
    const emptyEl  = $('emptyContent');
    const infoEl   = $('contentInfo');
    const addBtn   = $('btnAddContent');
    const addLbl   = $('btnAddLabel');
    const delModal = $('contentDeleteModal');
    const statsEl  = $('contentStats');
    const filtEl   = $('contentFilters');
    const searchEl = $('contentSearch');
    const bulkBar  = $('bulkContentBar');

    if (!grid) return; // Bukan halaman konten

    /* ============================================================
       1. FETCH JSON DENGAN DIAGNOSTIK
       ============================================================ */
    async function fetchJSON(url, options) {
        const res = await fetch(url, options);
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('[CONTENT] Respons bukan JSON (' + url + '):', text.slice(0, 300));
            throw new Error('Respons server tidak valid.');
        }
    }

    /* ============================================================
       2. MODAL HANDLING (dengan Scroll Lock)
       ============================================================ */
    const openModal = (id) => {
        const m = $(id);
        if (!m) return;
        m.classList.add('show');
        document.body.style.overflow = 'hidden';
    };
    const closeModal = (m) => {
        if (!m) return;
        m.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd); }));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(m => closeModal(m));
            closeLightbox();
        }
    });

    /* ============================================================
       3. TAB SWITCHING (Smooth + Persisted)
       ============================================================ */
    const tabBtns = document.querySelectorAll('.content-tab');

    function switchTab(newType, silent = false) {
        type = newType;
        tabBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === type));
        state.q = ''; state.status = ''; state.year = ''; state.division = '';
        selected.clear();
        if (searchEl) searchEl.value = '';
        if (addLbl) addLbl.textContent = 'Tambah ' + LABELS[type];
        try { localStorage.setItem('content_last_tab', type); } catch (e) { /* Silent */ }
        if (!silent) load();
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.tab === type) return;
            grid.style.transition = 'opacity .25s, transform .25s';
            grid.style.opacity = '0';
            grid.style.transform = 'translateY(10px)';
            setTimeout(() => {
                switchTab(btn.dataset.tab);
                setTimeout(() => {
                    grid.style.opacity = '1';
                    grid.style.transform = 'translateY(0)';
                }, 60);
            }, 200);
        });
    });

    $('btnContentRefresh')?.addEventListener('click', () => load(true));
    $('emptyAddContent')?.addEventListener('click', () => addBtn?.click());

    /* ============================================================
       4. LOAD DATA (Skeleton + AbortController)
       ============================================================ */
    function renderSkeleton() {
        grid.innerHTML = Array.from({ length: 8 }, (_, i) => `
            <div class="content-card-skeleton" style="animation-delay:${i * 50}ms">
                <div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div>
                <div style="padding:14px">
                    <div class="skel" style="height:14px;width:80%;margin-bottom:8px"></div>
                    <div class="skel" style="height:10px;width:60%"></div>
                </div>
            </div>
        `).join('');
    }

    async function load(spin = false) {
        const refreshBtn = $('btnContentRefresh');
        const icon = refreshBtn?.querySelector('i');

        if (spin && icon) icon.classList.add('is-spinning');
        else renderSkeleton();

        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();

        const params = new URLSearchParams({ per_page: '100' });
        if (state.q)      params.set('q', state.q);
        if (state.status) params.set('status', state.status);
        if (state.year)   params.set('year', state.year);

        try {
            const json = await fetchJSON(api('api/content/' + type + '?' + params.toString()), {
                signal: abortCtrl.signal
            });
            currentData = json.data || [];
            renderStats();
            renderFilters();
            renderGrid();
        } catch (err) {
            if (err.name === 'AbortError') return;
            grid.innerHTML = '';
            if (emptyEl) emptyEl.style.display = 'flex';
            const t = $('emptyContentText');
            if (t) t.textContent = 'Gagal memuat data. Periksa konsol untuk detail.';
            toast('Gagal memuat data konten.', 'error');
        } finally {
            if (icon) icon.classList.remove('is-spinning');
        }
    }

    /* ============================================================
       5. STATS MINI CARDS (Computed Client-Side — Always Accurate)
       ============================================================ */
    function computeStats() {
        const d = currentData;
        if (type === 'officers') {
            const divisions = new Set(d.map(x => x.division).filter(Boolean));
            return [
                { icon: 'ph-users-three',  label: 'Total Pengurus', value: d.length, grad: 'grad-1' },
                { icon: 'ph-camera',       label: 'Dengan Foto',    value: d.filter(x => x.photo).length, grad: 'grad-2' },
                { icon: 'ph-squares-four', label: 'Divisi',         value: divisions.size, grad: 'grad-3' },
            ];
        }
        if (type === 'testimonials') {
            return [
                { icon: 'ph-chat-circle-text', label: 'Total Testimoni', value: d.length, grad: 'grad-1' },
                { icon: 'ph-hourglass',        label: 'Menunggu',        value: d.filter(x => x.status === 'pending').length, grad: 'grad-4' },
                { icon: 'ph-check-circle',     label: 'Terbit',          value: d.filter(x => x.status === 'published').length, grad: 'grad-3' },
            ];
        }
        const years = new Set(d.map(x => (x.event_date || '').slice(0, 4)).filter(Boolean));
        return [
            { icon: 'ph-images',     label: 'Total Foto',  value: d.length, grad: 'grad-1' },
            { icon: 'ph-calendar',   label: 'Tahun',       value: years.size, grad: 'grad-2' },
            { icon: 'ph-map-pin',    label: 'Dengan Lokasi', value: d.filter(x => x.location).length, grad: 'grad-3' },
        ];
    }

    function renderStats() {
        if (!statsEl) return;
        statsEl.innerHTML = computeStats().map((s, i) => `
            <div class="mini-stat glass-card" style="animation-delay:${i * 60}ms">
                <div class="stat-icon ${s.grad}"><i class="ph ${s.icon}"></i></div>
                <div>
                    <strong>${s.value.toLocaleString('id-ID')}</strong>
                    <span>${s.label}</span>
                </div>
            </div>
        `).join('');
    }

    /* ============================================================
       6. FILTER CHIPS (Divisi / Status / Tahun)
       ============================================================ */
    function renderFilters() {
        if (!filtEl) return;
        let chips = '';

        if (type === 'officers') {
            const divisions = [...new Set(currentData.map(x => x.division).filter(Boolean))].sort();
            chips = chip('', 'Semua') + divisions.map(d => chip(d, esc(d))).join('');
            filtEl.dataset.key = 'division';
        } else if (type === 'testimonials') {
            chips = chip('', 'Semua') +
                chip('pending', 'Menunggu') +
                chip('published', 'Terbit') +
                chip('draft', 'Draft');
            filtEl.dataset.key = 'status';
        } else {
            const years = [...new Set(currentData.map(x => (x.event_date || '').slice(0, 4)).filter(Boolean))].sort().reverse();
            chips = chip('', 'Semua Tahun') + years.map(y => chip(y, y)).join('');
            filtEl.dataset.key = 'year';
        }

        filtEl.innerHTML = chips;
        filtEl.querySelectorAll('.chip').forEach(c => {
            c.addEventListener('click', () => {
                const key = filtEl.dataset.key;
                state[key] = c.dataset.val;
                // Reset filter lain
                ['status', 'year', 'division'].forEach(k => { if (k !== key) state[k] = ''; });
                load();
            });
        });

        function chip(val, label) {
            const active = state[filtEl.dataset.key === 'division' ? 'division' : (filtEl.dataset.key === 'status' ? 'status' : 'year')] === val;
            return `<button class="chip ${active ? 'chip-active' : ''}" data-val="${esc(val)}">${label}</button>`;
        }
    }

    /* ============================================================
       7. RENDER GRID (Per Tipe)
       ============================================================ */
    function visibleData() {
        let d = currentData;
        if (type === 'officers' && state.division) {
            d = d.filter(x => (x.division || '') === state.division);
        }
        return d;
    }

    function renderGrid() {
        const data = visibleData();
        if (infoEl) infoEl.textContent = data.length + ' ' + LABELS[type].toLowerCase() + ' terdaftar';

        selected.clear();
        updateBulkBar();

        if (!data.length) {
            grid.innerHTML = '';
            if (emptyEl) emptyEl.style.display = 'flex';
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';

        grid.innerHTML = data.map((item, i) => {
            let thumb = '', title = '', sub = '', extra = '';

            if (type === 'officers') {
                thumb = item.photo
                    ? `<img src="${BASE}assets/uploads/officers/${esc(item.photo)}" alt="" loading="lazy">`
                    : `<i class="ph ph-user-circle"></i>`;
                title = item.full_name;
                sub = [item.position, item.division].filter(Boolean).join(' · ');
                extra = `<span class="nav-chip" style="position:absolute;top:10px;left:10px;z-index:2">#${item.sort_order ?? i + 1}</span>`;
            } else if (type === 'testimonials') {
                thumb = item.photo
                    ? `<img src="${BASE}assets/uploads/testimonials/${esc(item.photo)}" alt="" loading="lazy">`
                    : `<i class="ph ph-quotes"></i>`;
                title = item.name;
                sub = item.role || 'Alumni';
                const st = item.status || 'pending';
                const stars = '★'.repeat(Math.max(1, Math.min(5, parseInt(item.rating, 10) || 5)));
                extra = `
                    <span class="status-pill ${st === 'published' ? 'status-active' : 'status-inactive'}"
                          style="position:absolute;top:10px;left:10px;z-index:2;${st === 'pending' ? 'background:rgba(245,158,11,.15);border-color:rgba(245,158,11,.35);color:#fcd34d;' : ''}">
                        ${st === 'published' ? 'Terbit' : st === 'pending' ? 'Menunggu' : 'Draft'}
                    </span>
                    <span style="position:absolute;bottom:8px;right:10px;color:#fbbf24;font-size:11px;letter-spacing:1px;z-index:2">${stars}</span>`;
            } else {
                thumb = item.image
                    ? `<img src="${BASE}assets/uploads/galleries/${esc(item.image)}" alt="" loading="lazy">`
                    : `<i class="ph ph-image"></i>`;
                title = item.title;
                sub = [item.event_date ? formatDate(item.event_date) : '', item.location || ''].filter(Boolean).join(' · ') || 'Foto kegiatan';
            }

            return `
            <article class="content-card" data-id="${item.id}" style="animation-delay:${i * 40}ms">
                ${extra}
                <div class="content-actions">
                    <label class="check-field" style="margin-right:2px" title="Pilih">
                        <input type="checkbox" class="card-check" data-id="${item.id}">
                        <span class="check-mark" style="width:22px;height:22px;background:rgba(10,15,31,.7)"></span>
                    </label>
                    <button class="icon-btn has-tooltip" data-tooltip="Ubah" data-act="edit" data-id="${item.id}"><i class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${item.id}"><i class="ph ph-trash"></i></button>
                </div>
                <div class="content-thumb" ${type === 'galleries' ? 'data-lightbox="' + item.id + '" style="cursor:zoom-in"' : ''}>${thumb}</div>
                <div class="content-info"><strong>${esc(title)}</strong><small>${esc(sub)}</small></div>
            </article>`;
        }).join('');

        bindCardEffects();
    }

    /* ============================================================
       8. CARD EFFECTS (3D Tilt + Ripple + Lightbox + Selection)
       ============================================================ */
    function bindCardEffects() {
        // 3D Tilt (rAF optimized)
        grid.querySelectorAll('.content-card').forEach(card => {
            let raf = null;
            card.addEventListener('mousemove', function(e) {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    const rect = this.getBoundingClientRect();
                    const rotateX = (e.clientY - rect.top - rect.height / 2) / 25;
                    const rotateY = (rect.width / 2 - (e.clientX - rect.left)) / 25;
                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
                });
            });
            card.addEventListener('mouseleave', function() {
                if (raf) cancelAnimationFrame(raf);
                this.style.transform = '';
            });
        });

        // Ripple pada tombol aksi
        grid.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'btn-ripple';
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top = (e.clientY - rect.top) + 'px';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Selection checkboxes
        grid.querySelectorAll('.card-check').forEach(cb => {
            cb.addEventListener('change', () => {
                if (cb.checked) selected.add(cb.dataset.id);
                else selected.delete(cb.dataset.id);
                updateBulkBar();
            });
        });

        // Lightbox galeri
        grid.querySelectorAll('[data-lightbox]').forEach(thumb => {
            thumb.addEventListener('click', () => {
                const item = currentData.find(x => String(x.id) === thumb.dataset.lightbox);
                if (item) openLightbox(BASE + 'assets/uploads/galleries/' + item.image, item.title);
            });
        });
    }

    /* ============================================================
       9. LIGHTBOX (Dynamic)
       ============================================================ */
    let lightboxEl = null;
    function openLightbox(src, caption) {
        closeLightbox();
        lightboxEl = document.createElement('div');
        lightboxEl.className = 'lightbox show';
        lightboxEl.innerHTML = `
            <img src="${esc(src)}" alt="${esc(caption || '')}">
            <p>${esc(caption || '')}</p>
            <button class="lightbox-close"><i class="ph ph-x"></i></button>
        `;
        document.body.appendChild(lightboxEl);
        document.body.style.overflow = 'hidden';
        lightboxEl.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        lightboxEl.addEventListener('click', (e) => { if (e.target === lightboxEl) closeLightbox(); });
    }
    function closeLightbox() {
        if (lightboxEl) {
            lightboxEl.remove();
            lightboxEl = null;
            document.body.style.overflow = '';
        }
    }

    /* ============================================================
       10. BULK ACTION BAR (dengan Fallback Loop Delete)
       ============================================================ */
    function updateBulkBar() {
        if (!bulkBar) return;
        const n = selected.size;
        bulkBar.style.display = n ? 'flex' : 'none';
        const c = bulkBar.querySelector('.bulk-count');
        if (c) c.textContent = n + ' item dipilih';
    }

    bulkBar?.querySelector('[data-bulk="clear"]')?.addEventListener('click', () => {
        selected.clear();
        grid.querySelectorAll('.card-check').forEach(cb => { cb.checked = false; });
        updateBulkBar();
    });

    bulkBar?.querySelector('[data-bulk="delete"]')?.addEventListener('click', async function() {
        const ids = [...selected];
        if (!ids.length) return;
        if (!confirm(`Hapus ${ids.length} item ${LABELS[type].toLowerCase()} secara permanen?`)) return;

        this.classList.add('is-loading');
        try {
            // Coba endpoint bulk dulu
            const fd = new FormData();
            fd.append('csrf_token', csrfToken());
            fd.append('type', type);
            ids.forEach(id => fd.append('ids[]', id));
            const res = await fetch(api('content/bulk-delete'), { method: 'POST', body: fd });
            const json = res.ok ? await res.json() : null;

            if (json && json.ok) {
                toast(json.message, 'success');
            } else {
                // Fallback: hapus satu per satu
                let ok = 0;
                for (const id of ids) {
                    const f2 = new FormData();
                    f2.append('csrf_token', csrfToken());
                    f2.append('type', type);
                    const r2 = await fetch(api('content/delete/' + id), { method: 'POST', body: f2 });
                    const j2 = await r2.json();
                    if (j2.ok) ok++;
                }
                toast(ok + ' dari ' + ids.length + ' item berhasil dihapus.', ok === ids.length ? 'success' : 'error');
            }
            selected.clear();
            load();
        } catch (err) {
            toast('Gagal menghapus item.', 'error');
        } finally {
            this.classList.remove('is-loading');
        }
    });

    /* ============================================================
       11. PENCARIAN (Debounced)
       ============================================================ */
    let searchDebounce;
    searchEl?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            state.q = searchEl.value.trim();
            load();
        }, 350);
    });

    /* ============================================================
       12. TAMBAH KONTEN
       ============================================================ */
    addBtn?.addEventListener('click', () => {
        resetForms();
        const titles = { officers: 'Tambah Pengurus', testimonials: 'Tambah Testimoni', galleries: 'Tambah Foto Galeri' };
        const t = $( { officers: 'officerModalTitle', testimonials: 'testimonialModalTitle', galleries: 'galleryModalTitle' }[type] );
        if (t) t.textContent = titles[type];
        openModal(MODALS[type]);
    });

    function resetForms() {
        Object.values(FORMS).forEach(id => $(id)?.reset());
        Object.values(ID_ELS).forEach(id => { const el = $(id); if (el) el.value = ''; });
        document.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
        });
        // Reset previews ke state awal
        ['oPhotoPreview', 'tPhotoPreview', 'gImagePreview'].forEach(pid => {
            const p = $(pid);
            if (p && !p.dataset.initial) p.dataset.initial = p.innerHTML;
            if (p && p.dataset.initial) p.innerHTML = p.dataset.initial;
        });
    }

    /* ============================================================
       13. EDIT & DELETE (Delegated)
       ============================================================ */
    grid.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            resetForms();
            if (type === 'officers') {
                $('oId').value = item.id;
                $('oName').value = item.full_name || '';
                $('oPosition').value = item.position || '';
                $('oDivision').value = item.division || '';
                $('oOrder').value = item.sort_order || 0;
                $('oBio').value = item.bio || '';
                $('officerModalTitle').textContent = 'Sunting Pengurus';
            } else if (type === 'testimonials') {
                $('tId').value = item.id;
                $('tName').value = item.name || '';
                $('tRole').value = item.role || '';
                $('tQuote').value = item.quote || '';
                if ($('tRating')) $('tRating').value = item.rating || 5;
                if ($('tStatus')) $('tStatus').value = item.status || 'pending';
                $('testimonialModalTitle').textContent = 'Sunting Testimoni';
            } else {
                $('gId').value = item.id;
                $('gTitle').value = item.title || '';
                $('gDate').value = item.event_date || '';
                $('gLocation').value = item.location || '';
                $('galleryModalTitle').textContent = 'Sunting Foto Galeri';
            }
            updateCounters();
            openModal(MODALS[type]);
        }

        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            const name = type === 'officers' ? item.full_name : (type === 'testimonials' ? item.name : item.title);
            const t = $('contentDeleteText');
            if (t) t.textContent = 'Konten "' + name + '" akan dihapus permanen.';
            openModal('contentDeleteModal');
        }
    });

    /* ============================================================
       14. FILE PREVIEW (Drag & Drop)
       ============================================================ */
    function bindFilePreview(inputId, previewId, maxMB = 20) {
        const input = $(inputId), preview = $(previewId);
        if (!input || !preview) return;

        const zone = input.closest('.field') || input.parentElement;
        zone?.addEventListener('dragover', (e) => {
            e.preventDefault();
            preview.style.borderColor = 'var(--pri)';
            preview.style.background = 'rgba(99,102,241,.08)';
        });
        zone?.addEventListener('dragleave', () => {
            preview.style.borderColor = '';
            preview.style.background = '';
        });
        zone?.addEventListener('drop', (e) => {
            e.preventDefault();
            preview.style.borderColor = '';
            preview.style.background = '';
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handle(e.dataTransfer.files[0]);
            }
        });

        input.addEventListener('change', () => { if (input.files[0]) handle(input.files[0]); });

        function handle(file) {
            if (!file.type.startsWith('image/')) {
                toast('Hanya file gambar yang didukung.', 'error');
                input.value = '';
                return;
            }
            if (file.size > maxMB * 1024 * 1024) {
                toast('Ukuran maksimal ' + maxMB + ' MB.', 'error');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (ev) => {
                preview.style.transition = 'opacity .3s, transform .3s';
                preview.style.opacity = '0';
                setTimeout(() => {
                    preview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover">`;
                    preview.style.opacity = '1';
                }, 150);
            };
            reader.readAsDataURL(file);
        }
    }

    bindFilePreview('oPhoto', 'oPhotoPreview', 20);
    bindFilePreview('tPhoto', 'tPhotoPreview', 20);
    bindFilePreview('gImage', 'gImagePreview', 20);

    /* ============================================================
       15. CHARACTER COUNTERS
       ============================================================ */
    function bindCounter(inputId, counterId, max) {
        const input = $(inputId), counter = $(counterId);
        if (!input || !counter) return;
        const update = () => {
            const len = input.value.length;
            counter.textContent = len + '/' + max;
            counter.style.color = len > max ? 'var(--danger-2)' : (len > max * 0.9 ? 'var(--warn)' : 'var(--txt-2)');
        };
        input.addEventListener('input', update);
        update();
    }
    bindCounter('oBio', 'oBioCounter', 500);
    bindCounter('tQuote', 'tQuoteCounter', 1000);

    function updateCounters() {
        ['oBio', 'tQuote'].forEach(id => $(id)?.dispatchEvent(new Event('input')));
    }

    /* ============================================================
       16. SIMPAN FORM (3 Form)
       ============================================================ */
    function bindForm(formId, idEl) {
        const form = $(formId);
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = $(idEl)?.value;
            const url = id ? api('content/update/' + id) : api('content/store');
            const btn = form.querySelector('button[type="submit"]');
            btn?.classList.add('is-loading');
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });

            form.style.opacity = '0.7';
            try {
                const json = await fetchJSON(url, { method: 'POST', body: new FormData(form) });
                btn?.classList.remove('is-loading');
                form.style.opacity = '1';

                if (json.ok) {
                    form.style.borderColor = 'var(--ok)';
                    setTimeout(() => { form.style.borderColor = ''; }, 1000);
                    closeModal(form.closest('.modal-backdrop'));
                    toast(json.message, 'success');
                    launchConfetti();
                    load();
                } else if (json.errors) {
                    Object.entries(json.errors).forEach(([k, v]) => {
                        const err = form.querySelector('[data-error="' + k + '"]');
                        if (err) {
                            err.textContent = v;
                            err.closest('.field')?.classList.add('has-error');
                        }
                    });
                    toast('Periksa kembali isian Anda.', 'error');
                    form.style.animation = 'shake .4s';
                    setTimeout(() => { form.style.animation = ''; }, 400);
                } else {
                    toast(json.message || 'Gagal menyimpan.', 'error');
                }
            } catch (err) {
                btn?.classList.remove('is-loading');
                form.style.opacity = '1';
                toast(err.message || 'Koneksi ke server gagal.', 'error');
            }
        });
    }
    bindForm('officerForm', 'oId');
    bindForm('testimonialForm', 'tId');
    bindForm('galleryForm', 'gId');

    /* ============================================================
       17. HAPUS KONTEN
       ============================================================ */
    $('btnConfirmContentDelete')?.addEventListener('click', async function() {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrfToken());
        fd.append('type', type);
        this.classList.add('is-loading');
        try {
            const json = await fetchJSON(api('content/delete/' + deleteId), { method: 'POST', body: fd });
            this.classList.remove('is-loading');
            closeModal(delModal);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            this.classList.remove('is-loading');
            toast(err.message || 'Koneksi ke server gagal.', 'error');
        }
        deleteId = null;
    });

    /* ============================================================
       18. EXPORT CSV (Blob Download)
       ============================================================ */
    $('btnContentExport')?.addEventListener('click', async function() {
        this.classList.add('is-loading');
        try {
            const res = await fetch(api('content/export/' + type));
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = type + '-' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
            URL.revokeObjectURL(url);
            toast('CSV berhasil diunduh.', 'success');
        } catch (err) {
            toast('Gagal mengunduh CSV.', 'error');
        } finally {
            this.classList.remove('is-loading');
        }
    });

    /* ============================================================
       19. CONFETTI
       ============================================================ */
    function launchConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#ec4899'];
        for (let i = 0; i < 30; i++) {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.cssText = `
                position:fixed; width:${6 + Math.random() * 4}px; height:${6 + Math.random() * 4}px;
                background:${colors[Math.floor(Math.random() * colors.length)]};
                top:-10px; left:${Math.random() * 100}vw;
                border-radius:${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events:none; z-index:9999;
                animation:confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 4500);
        }
    }

    /* ============================================================
       20. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('content')) return;
        if (document.querySelector('.modal-backdrop.show')) return;

        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            e.preventDefault();
            addBtn?.click();
        }
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'f' && searchEl) {
            e.preventDefault();
            searchEl.focus();
            searchEl.select();
        }
    });

    /* ============================================================
       21. INITIALIZATION
       ============================================================ */
    try {
        const last = localStorage.getItem('content_last_tab');
        if (last && LABELS[last]) switchTab(last, true);
    } catch (e) { /* Silent */ }

    load();
    console.log('%c🎨 Content Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();