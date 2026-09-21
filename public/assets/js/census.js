// File: public/assets/js/census.js (FINAL v7.0 ULTIMATE)
// Modul Manajemen Sensus: Filter + Bulk + Stats + Detail Modal + Export
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE  = document.body.dataset.base || '/';
    const API   = (p) => BASE + (p ? 'api/census?' + p : 'api/census');
    const PAGE  = (p) => BASE + 'census/' + p;
    const TOKEN = window.CSRF_TOKEN || document.querySelector('input[name="csrf_token"]')?.value || '';

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    const fmtTime = (d) => d ? new Date(d).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }) : '-';

    const fmtRelative = (d) => {
        if (!d) return '-';
        const diff = (Date.now() - new Date(d).getTime()) / 1000;
        if (diff < 60) return 'Baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 172800) return 'Kemarin';
        return Math.floor(diff / 86400) + ' hari lalu';
    };

    const PURPOSE = {
        pendaftaran: { label: 'Pendaftaran', cls: 'status-active', grad: 'grad-1', icon: 'ph-user-plus' },
        sensus:      { label: 'Sensus Rekap', cls: 'status-inactive', grad: 'grad-2', icon: 'ph-clipboard-text' },
    };

    /* ============================================================
       STATE
       ============================================================ */
    const state = {
        page: 1,
        pages: 1,
        status: '',        // '' | 'pending' | 'processed'
        purpose: '',       // '' | 'pendaftaran' | 'sensus'
        q: '',
        selected: new Set(),
        currentData: [],
        abortController: null,
    };

    /* ============================================================
       DOM REFERENCES
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const els = {
        rows:       $('censusRows'),
        empty:      $('emptyCensus'),
        info:       $('censusInfo'),
        newCount:   $('censusNewCount'),
        badge:      $('censusBadge'),
        refresh:    $('btnCensusRefresh'),
        search:     $('censusSearch'),
        stats:      $('censusStats'),
        filters:    $('censusFilters'),
        bulkBar:    $('bulkCensusBar'),
        selectAll:  $('selectAllCensus'),
        prev:       $('censusPrev'),
        next:       $('censusNext'),
        pagerCur:   $('censusPagerCur'),
        detailModal:$('censusDetailModal'),
        detailBody: $('censusDetailBody'),
        exportBtn:  $('btnCensusExport'),
    };

    /* ============================================================
       1. SKELETON LOADING
       ============================================================ */
    function showSkeleton() {
        if (!els.rows) return;
        els.rows.innerHTML = Array.from({ length: 5 }, (_, i) => `
            <tr class="cascade-row" style="animation-delay:${i * 60}ms">
                <td colspan="7" style="padding:14px 22px;">
                    <div class="skel-row">
                        <div class="skel skel-av"></div>
                        <div class="skel-col">
                            <div class="skel" style="height:12px;width:70%"></div>
                            <div class="skel" style="height:10px;width:50%;margin-top:6px"></div>
                        </div>
                        <div class="skel" style="height:10px;width:80px;margin-left:auto"></div>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /* ============================================================
       2. API LAYER (AbortController)
       ============================================================ */
    async function fetchCensus() {
        if (state.abortController) state.abortController.abort();
        state.abortController = new AbortController();

        const params = new URLSearchParams();
        if (state.status)  params.set('status', state.status);
        if (state.purpose) params.set('purpose', state.purpose);
        if (state.q)       params.set('q', state.q);
        params.set('page', state.page);

        const res = await fetch(API(params.toString()), {
            signal: state.abortController.signal
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    async function load(spin = false) {
        if (!els.rows) return;

        if (spin) {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.add('is-spinning');
        } else {
            showSkeleton();
        }

        try {
            const json = await fetchCensus();
            state.currentData = json.data || [];
            state.pages = json.meta?.pages || 1;

            renderTable(state.currentData, json.meta || {});
            renderStats(json.stats || {});
            updatePagination(json.meta || {});
        } catch (err) {
            if (err.name === 'AbortError') return;
            if (els.rows) els.rows.innerHTML = '';
            if (els.empty) els.empty.style.display = 'flex';
            toast('Gagal memuat data sensus.', 'error');
        } finally {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.remove('is-spinning');
        }
    }

    /* ============================================================
       3. STATS MINI CARDS
       ============================================================ */
    function renderStats(stats) {
        if (!els.stats) return;
        const items = [
            { icon: 'ph-clipboard-text', label: 'Total Entri',  value: stats.total || 0,      grad: 'grad-1' },
            { icon: 'ph-hourglass',      label: 'Menunggu',     value: stats.pending || 0,    grad: 'grad-4' },
            { icon: 'ph-check-circle',   label: 'Diproses',     value: stats.processed || 0,  grad: 'grad-3' },
            { icon: 'ph-user-plus',      label: 'Pendaftaran',  value: stats.pendaftaran || 0,grad: 'grad-2' },
            { icon: 'ph-star',           label: 'Baru Hari Ini',value: stats.new_today || 0,  grad: 'grad-1' },
        ];

        els.stats.innerHTML = items.map((s, i) => `
            <div class="mini-stat glass-card" style="animation-delay:${i * 60}ms">
                <div class="stat-icon ${s.grad}"><i class="ph ${s.icon}"></i></div>
                <div>
                    <strong data-count="${s.value}">0</strong>
                    <span>${s.label}</span>
                </div>
            </div>
        `).join('');

        // Animasikan count-up
        els.stats.querySelectorAll('strong[data-count]').forEach((el, idx) => {
            const target = parseInt(el.dataset.count, 10) || 0;
            if (target === 0) {
                el.textContent = '0';
                return;
            }
            const t0 = performance.now();
            const dur = 900 + (idx * 80);
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.floor(target * eased).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target.toLocaleString('id-ID');
            };
            requestAnimationFrame(tick);
        });

        // Update new count di topbar
        if (els.newCount) {
            const oldVal = parseInt(els.newCount.textContent, 10) || 0;
            animateCount(els.newCount, oldVal, stats.pending || 0);
        }
        if (els.badge) {
            const n = stats.pending || 0;
            els.badge.textContent = n;
            els.badge.style.display = n > 0 ? 'inline-block' : 'none';
            if (n > 0) {
                els.badge.classList.remove('badge-pop');
                void els.badge.offsetWidth; // force reflow
                els.badge.classList.add('badge-pop');
            }
        }
    }

    function animateCount(el, from, to) {
        const dur = 600, t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            const val = Math.round(from + (to - from) * eased);
            el.textContent = val.toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /* ============================================================
       4. FILTER CHIPS (Purpose + Status)
       ============================================================ */
    function renderFilters(stats) {
        if (!els.filters) return;
        const s = stats || {};

        els.filters.innerHTML = `
            <div class="filter-group">
                <span class="filter-label">Status:</span>
                <button class="chip ${!state.status ? 'chip-active' : ''}" data-f="status:" data-val="">
                    Semua
                </button>
                <button class="chip ${state.status === 'pending' ? 'chip-active' : ''}" data-f="status:pending">
                    Menunggu <small>(${s.pending || 0})</small>
                </button>
                <button class="chip ${state.status === 'processed' ? 'chip-active' : ''}" data-f="status:processed">
                    Diproses <small>(${s.processed || 0})</small>
                </button>
            </div>
            <div class="filter-group" style="margin-top:10px">
                <span class="filter-label">Tujuan:</span>
                <button class="chip ${!state.purpose ? 'chip-active' : ''}" data-f="purpose:">
                    Semua
                </button>
                <button class="chip ${state.purpose === 'pendaftaran' ? 'chip-active' : ''}" data-f="purpose:pendaftaran">
                    <i class="ph ph-user-plus"></i> Pendaftaran
                </button>
                <button class="chip ${state.purpose === 'sensus' ? 'chip-active' : ''}" data-f="purpose:sensus">
                    <i class="ph ph-clipboard-text"></i> Sensus
                </button>
            </div>
        `;

        els.filters.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const [key, val] = chip.dataset.f.split(':');
                if (key === 'status')  state.status = val;
                if (key === 'purpose') state.purpose = val;
                state.page = 1;
                load();
            });
        });
    }

    /* ============================================================
       5. TABLE RENDERING (dengan Selection)
       ============================================================ */
    function renderTable(data, meta) {
        if (els.info) els.info.textContent = `${data.length} dari ${(meta.total || 0)} entri`;

        state.selected.clear();
        updateBulkBar();

        if (!data.length) {
            if (els.rows) els.rows.innerHTML = '';
            if (els.empty) els.empty.style.display = 'flex';
            return;
        }
        if (els.empty) els.empty.style.display = 'none';

        els.rows.innerHTML = data.map((r, i) => {
            const p = PURPOSE[r.purpose] || PURPOSE.sensus;
            const processed = Number(r.is_processed || r.processed || 0) === 1;
            const initial = (r.full_name || '?').charAt(0).toUpperCase();
            const hasCheck = !!els.selectAll;

            return `
            <tr class="cascade-row member-row" data-id="${r.id}" style="animation-delay:${i * 40}ms">
                <td>
                    <div class="cell-member">
                        ${hasCheck ? `
                            <label class="check-field" style="margin-right:8px">
                                <input type="checkbox" class="row-check" data-id="${r.id}" ${processed ? 'disabled' : ''}>
                                <span class="check-mark"></span>
                            </label>
                        ` : ''}
                        <div class="avatar avatar-sm grad-${i % 4 + 1}" style="flex-shrink:0">
                            ${esc(initial)}
                        </div>
                        <div class="cell-member-info">
                            <strong>
                                ${esc(r.full_name)}
                                ${processed ? '<span class="new-badge" style="background:rgba(16,185,129,.2);color:#6ee7b7;margin-left:6px">✓</span>' : ''}
                            </strong>
                            <small>${esc(r.status || '-')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="status-pill ${p.cls}">
                        <i class="ph ${p.icon}"></i> ${p.label}
                    </span>
                </td>
                <td>
                    <div class="cell-stack">
                        <span class="cell-email">${esc(r.email)}</span>
                        <small>${esc(r.phone || '-')}</small>
                    </div>
                </td>
                <td>${esc(r.graduation_year || '-')}</td>
                <td class="cell-date" title="${fmtTime(r.created_at)}">
                    ${fmtRelative(r.created_at)}
                </td>
                <td>
                    <div class="row-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Lihat detail" data-act="view" data-id="${r.id}">
                            <i class="ph ph-eye"></i>
                        </button>
                        ${processed
                            ? '<span class="status-pill status-inactive" style="pointer-events:none;padding:5px 10px;font-size:10px">✓ Diproses</span>'
                            : `<button class="icon-btn success-btn has-tooltip" data-tooltip="Setujui sebagai anggota" data-act="approve" data-id="${r.id}">
                                <i class="ph ph-check-circle"></i>
                               </button>`}
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus permanen" data-act="del" data-id="${r.id}">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        bindRowInteractions();
    }

    /* ============================================================
       6. ROW INTERACTIONS
       ============================================================ */
    function bindRowInteractions() {
        if (!els.rows) return;

        // Checkbox selection
        els.rows.querySelectorAll('.row-check').forEach(cb => {
            cb.addEventListener('change', () => {
                const id = cb.dataset.id;
                if (cb.checked) state.selected.add(id);
                else state.selected.delete(id);
                updateBulkBar();
                updateSelectAll();
            });
        });

        // Action buttons dengan ripple
        els.rows.querySelectorAll('.icon-btn[data-act]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'btn-ripple';
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top = (e.clientY - rect.top) + 'px';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);

                const id = this.dataset.id;
                const item = state.currentData.find(x => String(x.id) === id);
                if (!item) return;

                switch (this.dataset.act) {
                    case 'view':    openDetailModal(item); break;
                    case 'approve': handleApprove(id, this); break;
                    case 'del':     handleDelete(id, this); break;
                }
            });
        });
    }

    /* ============================================================
       7. BULK ACTIONS
       ============================================================ */
    function updateBulkBar() {
        if (!els.bulkBar) return;
        const count = state.selected.size;
        if (count === 0) {
            els.bulkBar.style.display = 'none';
            return;
        }
        els.bulkBar.style.display = 'flex';
        const countEl = els.bulkBar.querySelector('.bulk-count');
        if (countEl) countEl.textContent = count + ' entri dipilih';
    }

    function updateSelectAll() {
        if (!els.selectAll || !els.rows) return;
        const checks = els.rows.querySelectorAll('.row-check:not(:disabled)');
        const allChecked = checks.length > 0 && [...checks].every(c => c.checked);
        const someChecked = [...checks].some(c => c.checked);
        els.selectAll.checked = allChecked;
        els.selectAll.indeterminate = someChecked && !allChecked;
    }

    els.selectAll?.addEventListener('change', () => {
        const checks = els.rows?.querySelectorAll('.row-check:not(:disabled)') || [];
        checks.forEach(cb => {
            cb.checked = els.selectAll.checked;
            const id = cb.dataset.id;
            if (els.selectAll.checked) state.selected.add(id);
            else state.selected.delete(id);
        });
        updateBulkBar();
    });

    document.querySelectorAll('[data-bulk-census]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.bulkCensus;
            const ids = [...state.selected];
            if (ids.length === 0) return;

            const confirmMsg = {
                'approve': `Setujui ${ids.length} entri sebagai anggota baru? Kredensial akan dibuat otomatis.`,
                'delete':  `Hapus ${ids.length} entri sensus secara permanen?`
            };

            if (!confirm(confirmMsg[action] || 'Lanjutkan?')) return;

            btn.classList.add('is-loading');
            try {
                const endpoint = action === 'approve' ? 'bulk-approve' : 'bulk-delete';
                const fd = new FormData();
                fd.append('csrf_token', TOKEN);
                ids.forEach(id => fd.append('ids[]', id));

                const res = await fetch(PAGE(endpoint), { method: 'POST', body: fd });
                const json = await res.json();
                toast(json.message || 'Selesai', json.ok ? 'success' : 'error');
                if (json.ok) load();
            } catch (err) {
                toast('Gagal melakukan aksi bulk', 'error');
            } finally {
                btn.classList.remove('is-loading');
            }
        });
    });

    /* ============================================================
       8. SEARCH & PAGINATION
       ============================================================ */
    let searchDebounce;
    els.search?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            state.q = els.search.value.trim();
            state.page = 1;
            load();
        }, 400);
    });

    els.prev?.addEventListener('click', () => {
        if (state.page > 1) { state.page--; load(); }
    });
    els.next?.addEventListener('click', () => {
        if (state.page < state.pages) { state.page++; load(); }
    });

    function updatePagination(meta) {
        if (els.pagerCur) els.pagerCur.textContent = `${meta.page || 1} / ${meta.pages || 1}`;
        if (els.prev) els.prev.disabled = (meta.page || 1) <= 1;
        if (els.next) els.next.disabled = (meta.page || 1) >= (meta.pages || 1);
    }

    /* ============================================================
       9. DETAIL MODAL (v7.0)
       ============================================================ */
    let detailModalHandlers = [];

    function openDetailModal(item) {
        if (!els.detailModal || !els.detailBody) return;

        // Cleanup listeners lama
        cleanupDetailModalListeners();

        const p = PURPOSE[item.purpose] || PURPOSE.sensus;

        els.detailBody.innerHTML = `
            <div class="detail-avatar-wrap" style="text-align:center;margin-bottom:20px">
                <div class="avatar avatar-xl ${p.grad}" style="margin:0 auto">
                    ${esc((item.full_name || '?').charAt(0).toUpperCase())}
                </div>
                <h3 style="margin-top:12px;font-size:18px;font-weight:800">${esc(item.full_name)}</h3>
                <span class="status-pill ${p.cls}" style="margin-top:8px;display:inline-flex">
                    <i class="ph ${p.icon}"></i> ${p.label}
                </span>
            </div>

            <div class="detail-grid" style="display:grid;gap:10px">
                ${renderDetailRow('ph-envelope-simple', 'Email', item.email)}
                ${renderDetailRow('ph-phone', 'Telepon', item.phone)}
                ${renderDetailRow('ph-map-pin', 'Alamat', item.address)}
                ${renderDetailRow('ph-identification-card', 'Status', item.status)}
                ${renderDetailRow('ph-graduation-cap', 'Tahun Lulus', item.graduation_year)}
                ${renderDetailRow('ph-clock', 'Dikirim', fmtTime(item.created_at))}
                ${renderDetailRow('ph-wifi-high', 'IP', item.ip_address)}
                ${item.reference ? renderDetailRow('ph-hash', 'Referensi', item.reference) : ''}
            </div>

            ${item.message ? `
                <div style="margin-top:16px">
                    <h4 style="font-size:12px;font-weight:700;color:var(--txt-2);letter-spacing:1px;text-transform:uppercase;margin-bottom:8px">
                        <i class="ph ph-chat-text"></i> Pesan
                    </h4>
                    <p style="color:var(--txt-0);font-size:13.5px;line-height:1.6;padding:12px;background:rgba(255,255,255,.03);border-radius:10px;border:1px solid var(--glass-brd)">
                        ${esc(item.message)}
                    </p>
                </div>
            ` : ''}

            ${Number(item.is_processed || item.processed || 0) === 0 ? `
                <div class="detail-actions" style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end">
                    <button class="btn btn-ghost" data-close-modal><i class="ph ph-x"></i> Tutup</button>
                    <button class="btn btn-primary" data-act="approve-from-modal" data-id="${item.id}">
                        <i class="ph ph-check-circle"></i> Setujui Sekarang
                    </button>
                </div>
            ` : ''}
        `;

        els.detailModal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Bind close dengan tracking
        els.detailModal.querySelectorAll('[data-close-modal]').forEach(b => {
            b.addEventListener('click', closeDetailModal);
            detailModalHandlers.push({ el: b, event: 'click', handler: closeDetailModal });
        });

        const approveBtn = els.detailModal.querySelector('[data-act="approve-from-modal"]');
        if (approveBtn) {
            const approveHandler = function() {
                const btn = this;
                btn.classList.add('is-loading');
                handleApprove(btn.dataset.id, btn, closeDetailModal);
            };
            approveBtn.addEventListener('click', approveHandler);
            detailModalHandlers.push({ el: approveBtn, event: 'click', handler: approveHandler });
        }
    }

    function cleanupDetailModalListeners() {
        detailModalHandlers.forEach(({ el, event, handler }) => {
            el.removeEventListener(event, handler);
        });
        detailModalHandlers = [];
    }

    function renderDetailRow(icon, label, value) {
        return `
            <div style="display:flex;gap:10px;align-items:flex-start;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)">
                <i class="ph ${icon}" style="color:var(--acc);font-size:16px;margin-top:2px"></i>
                <div style="flex:1;min-width:0">
                    <small style="color:var(--txt-2);font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase">${esc(label)}</small>
                    <div style="color:var(--txt-0);font-size:13.5px;font-weight:600;word-break:break-word">${esc(value || '-')}</div>
                </div>
            </div>
        `;
    }

    function closeDetailModal() {
        if (els.detailModal) {
            els.detailModal.classList.remove('show');
            document.body.style.overflow = '';
            cleanupDetailModalListeners();
        }
    }

    document.querySelectorAll('#censusDetailModal [data-close-modal]').forEach(b =>
        b.addEventListener('click', closeDetailModal));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && els.detailModal?.classList.contains('show')) {
            closeDetailModal();
        }
    });

    /* ============================================================
       10. APPROVE / DELETE ACTIONS
       ============================================================ */
    async function handleApprove(id, btn, onSuccess) {
        const fd = new FormData();
        fd.append('csrf_token', TOKEN);
        btn?.classList.add('is-loading');

        try {
            const res = await fetch(PAGE('approve/' + id), { method: 'POST', body: fd });
            const json = await res.json();
            btn?.classList.remove('is-loading');

            if (json.ok) {
                // Tampilkan credential jika pendaftaran
                if (json.type === 'pendaftaran' && json.password) {
                    showCredentialModal(json.email, json.password);
                }
                toast(json.message, 'success');
                if (typeof onSuccess === 'function') onSuccess();
                const row = btn?.closest('tr');
                if (row) {
                    row.style.transition = 'all .4s';
                    row.style.opacity = '0.4';
                    row.style.transform = 'translateX(10px)';
                }
                setTimeout(() => load(), 500);
            } else {
                toast(json.message || 'Terjadi kesalahan.', 'error');
            }
        } catch (err) {
            toast('Koneksi ke server gagal.', 'error');
            btn?.classList.remove('is-loading');
        }
    }

    async function handleDelete(id, btn) {
        if (!confirm('Yakin ingin menghapus entri ini secara permanen?')) return;

        const fd = new FormData();
        fd.append('csrf_token', TOKEN);
        btn.classList.add('is-loading');

        try {
            const res = await fetch(PAGE('delete/' + id), { method: 'POST', body: fd });
            const json = await res.json();
            btn.classList.remove('is-loading');

            if (json.ok) {
                toast(json.message, 'success');
                const row = btn.closest('tr');
                if (row) {
                    row.style.transition = 'all .3s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => load(), 300);
                } else {
                    load();
                }
            } else {
                toast(json.message || 'Gagal menghapus.', 'error');
            }
        } catch (err) {
            toast('Koneksi ke server gagal.', 'error');
            btn.classList.remove('is-loading');
        }
    }

    /* ============================================================
       11. CREDENTIAL MODAL (v7.0 — Show Random Password)
       ============================================================ */
    let credentialModalHandlers = null;

    function showCredentialModal(email, password) {
        // Cleanup modal sebelumnya jika masih ada
        if (credentialModalHandlers) {
            credentialModalHandlers.cleanup();
            credentialModalHandlers = null;
        }

        const modal = document.createElement('div');
        modal.className = 'modal-backdrop show';
        modal.innerHTML = `
            <div class="modal glass-card" style="width:min(480px,92vw)">
                <div class="modal-head">
                    <div class="modal-icon grad-3"><i class="ph ph-user-circle-check"></i></div>
                    <div>
                        <h3>Anggota Berhasil Dibuat!</h3>
                        <p class="modal-sub">Kredensial login telah digenerate otomatis</p>
                    </div>
                </div>
                <div class="modal-body">
                    <div style="background:rgba(255,255,255,.03);border:1px solid var(--glass-brd);border-radius:12px;padding:16px;margin-bottom:14px">
                        <small style="color:var(--txt-2);font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:6px">
                            <i class="ph ph-envelope"></i> Email / Username
                        </small>
                        <div style="color:var(--txt-0);font-size:14px;font-weight:700;font-family:monospace">${esc(email)}</div>
                    </div>
                    <div style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);border-radius:12px;padding:16px">
                        <small style="color:#6ee7b7;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;display:block;margin-bottom:6px">
                            <i class="ph ph-key"></i> Password Sementara
                        </small>
                        <div style="display:flex;align-items:center;gap:10px">
                            <code id="credPassword" style="flex:1;color:#fff;font-size:16px;font-weight:800;font-family:monospace;background:rgba(0,0,0,.3);padding:8px 12px;border-radius:8px">
                                ${esc(password)}
                            </code>
                            <button class="icon-btn" id="btnCopyPassword" data-tooltip="Salin">
                                <i class="ph ph-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="danger-note" style="margin-top:14px">
                        <i class="ph ph-warning"></i>
                        <div>
                            <strong>Simpan password ini!</strong><br>
                            Kirimkan ke pendaftar via email atau SMS. Password ini hanya ditampilkan sekali.
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button class="btn btn-primary" data-close-modal>
                        <i class="ph ph-check"></i> Mengerti
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        const close = () => {
            modal.classList.remove('show');
            setTimeout(() => {
                // Remove event listeners sebelum hapus element
                if (credentialModalHandlers) {
                    credentialModalHandlers.handlers.forEach(({ el, event, handler }) => {
                        el.removeEventListener(event, handler);
                    });
                    credentialModalHandlers = null;
                }
                modal.remove();
                document.body.style.overflow = '';
            }, 300);
        };

        // Simpan reference handlers untuk cleanup
        const handlers = [];
        
        const closeBtnHandler = close;
        modal.querySelectorAll('[data-close-modal]').forEach(b => {
            b.addEventListener('click', closeBtnHandler);
            handlers.push({ el: b, event: 'click', handler: closeBtnHandler });
        });

        const backdropHandler = (e) => { if (e.target === modal) close(); };
        modal.addEventListener('click', backdropHandler);
        handlers.push({ el: modal, event: 'click', handler: backdropHandler });

        const copyBtn = modal.querySelector('#btnCopyPassword');
        if (copyBtn) {
            const copyHandler = () => {
                navigator.clipboard?.writeText(password).then(() => {
                    toast('Password disalin ke clipboard', 'success');
                });
            };
            copyBtn.addEventListener('click', copyHandler);
            handlers.push({ el: copyBtn, event: 'click', handler: copyHandler });
        }

        credentialModalHandlers = { cleanup: close, handlers };
    }

    /* ============================================================
       12. EXPORT CSV (v7.0)
       ============================================================ */
    els.exportBtn?.addEventListener('click', () => {
        const params = new URLSearchParams();
        if (state.status)  params.set('status', state.status);
        if (state.purpose) params.set('purpose', state.purpose);
        window.location.href = PAGE('export?' + params.toString());
        toast('Mengunduh CSV...', 'success');
    });

    /* ============================================================
       13. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('census')) return;

        // Ctrl+R = Refresh
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'r' && !e.shiftKey) {
            e.preventDefault();
            load(true);
        }
        // Ctrl+F = Fokus search
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'f' && els.search) {
            if (document.activeElement !== els.search) {
                e.preventDefault();
                els.search.focus();
                els.search.select();
            }
        }
    });

    /* ============================================================
       14. AUTO-REFRESH (opsional, 30 detik)
       ============================================================ */
    let autoRefreshTimer = null;
    function startAutoRefresh() {
        autoRefreshTimer = setInterval(() => {
            if (document.visibilityState === 'visible' && !state.selected.size) {
                load();
            }
        }, 30000);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !autoRefreshTimer) {
            startAutoRefresh();
        }
    });

    /* ============================================================
       15. INITIALIZATION
       ============================================================ */
    els.refresh?.addEventListener('click', () => {
        const btn = els.refresh;
        btn.style.transform = 'rotate(360deg)';
        setTimeout(() => { btn.style.transform = ''; }, 500);
        load(true);
    });

    if (els.rows) {
        load();
        startAutoRefresh();
        console.log('%c📋 Census Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');
    }

})();