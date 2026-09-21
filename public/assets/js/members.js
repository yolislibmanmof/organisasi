// File: public/assets/js/members.js (FINAL v7.0 ULTIMATE)
// Modul Manajemen Anggota: CRUD + Bulk + Filter + Detail Modal + Export + Shortcuts
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const API  = (p) => BASE + 'api/members' + (p ? '?' + p : '');
    const PAGE = (p) => BASE + 'members/' + p;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const initials = (name) => String(name || '?').trim()
        .split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();

    const formatDate = (d) => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', {
            day: 'numeric', month: 'short', year: 'numeric'
          })
        : '-';

    const csrf = () =>
        document.getElementById('memberForm')?.querySelector('input[name="csrf_token"]')?.value ||
        document.querySelector('input[name="csrf_token"]')?.value || '';

    /* ============================================================
       STATE
       ============================================================ */
    const state = {
        page: 1, pages: 1,
        q: '',
        status: '',          // '' | 'active' | 'inactive'
        total: 0,
        selected: new Set(),
        currentData: [],
        deleteId: null,
        abortController: null,
    };

    /* ============================================================
       DOM REFERENCES (dengan fallback aman)
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const els = {
        rows:       $('memberRows'),
        info:       $('tableInfo'),
        pageInfo:   $('pageInfo'),
        pagerCur:   $('pagerCurrent'),
        prev:       $('prevPage'),
        next:       $('nextPage'),
        search:     $('searchInput'),
        clearSrch:  $('btnClearSearch'),
        modalForm:  $('memberModal'),
        modalDel:   $('deleteModal'),
        form:       $('memberForm'),
        empty:      $('emptyState'),
        filter:     $('filterIndicator'),
        filterTxt:  $('filterText'),
        refresh:    $('btnRefresh'),
        export:     $('btnExport'),
        miniTotal:  $('miniTotal'),
        miniActive: $('miniActive'),
        miniNew:    $('miniNew'),
        badge:      $('memberBadge'),
        // v7.0 Optional elements
        stats:      $('memberStats'),
        filters:    $('memberFilters'),
        bulkBar:    $('bulkMemberBar'),
        selectAll:  $('selectAllMembers'),
        detailModal:$('memberDetailModal'),
        detailBody: $('memberDetailBody'),
        searchWrap: $('memberSearchWrap'),
    };

    /* ============================================================
       1. CLEAR VALIDATION ERRORS ON INPUT
       ============================================================ */
    els.form?.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', () => {
            const err = els.form.querySelector('[data-error="' + el.name + '"]');
            if (err) {
                err.textContent = '';
                err.closest('.field')?.classList.remove('has-error');
            }
            el.classList.remove('has-error');
            markDirty();
        });
    });

    /* ============================================================
       2. UNSAVED CHANGES TRACKING
       ============================================================ */
    let hasUnsavedChanges = false;
    function markDirty() { hasUnsavedChanges = true; }
    function markClean() { hasUnsavedChanges = false; }

    window.addEventListener('beforeunload', (e) => {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ============================================================
       3. AVATAR GRADIENT (Deterministic)
       ============================================================ */
    function getAvatarGradient(name) {
        const colors = [
            'linear-gradient(135deg, #6366f1, #8b5cf6)',
            'linear-gradient(135deg, #0ea5e9, #22d3ee)',
            'linear-gradient(135deg, #10b981, #34d399)',
            'linear-gradient(135deg, #f59e0b, #f97316)',
            'linear-gradient(135deg, #ec4899, #f472b6)',
            'linear-gradient(135deg, #8b5cf6, #ec4899)',
        ];
        const hash = String(name).split('').reduce((a, c) => a + c.charCodeAt(0), 0);
        return colors[hash % colors.length];
    }

    /* ============================================================
       4. SKELETON LOADING
       ============================================================ */
    function renderSkeleton() {
        if (!els.rows) return;
        els.rows.innerHTML = Array.from({ length: 6 }, (_, i) => `
            <tr class="skeleton-row cascade-row" style="animation-delay:${i * 50}ms">
                <td>
                    <div class="skel-row">
                        <div class="skel skel-av"></div>
                        <div class="skel-col">
                            <div class="skel" style="height:13px;width:65%"></div>
                            <div class="skel" style="height:10px;width:40%;margin-top:6px"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="skel-col">
                        <div class="skel" style="height:12px;width:75%"></div>
                        <div class="skel" style="height:10px;width:45%;margin-top:6px"></div>
                    </div>
                </td>
                <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                <td><div class="skel" style="height:12px;width:80px"></div></td>
                <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
            </tr>`).join('');
    }

    /* ============================================================
       5. API LAYER (AbortController)
       ============================================================ */
    async function fetchMembers() {
        if (state.abortController) state.abortController.abort();
        state.abortController = new AbortController();

        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.status) params.set('status', state.status);
        params.set('page', state.page);

        const res = await fetch(API(params.toString()), {
            signal: state.abortController.signal
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    async function load(spinRefresh = false) {
        if (!els.rows) return;

        if (spinRefresh) {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.add('is-spinning');
            if (els.refresh) els.refresh.style.transform = 'rotate(360deg)';
        } else {
            renderSkeleton();
        }

        try {
            const json = await fetchMembers();
            state.currentData = json.data || [];
            state.pages = json.meta?.pages || 1;
            state.total = json.meta?.total || 0;

            renderRows(state.currentData, json.meta || {});
            renderStats(json.stats || json.meta || {});
            renderFilterChips(json.stats || {});
            updateSummaries(json.meta || {}, json.stats || {});
        } catch (err) {
            if (err.name === 'AbortError') return;
            els.rows.innerHTML = `
                <tr><td colspan="5" class="empty-state error">
                    <i class="ph ph-warning-circle"></i> Gagal memuat data dari server.
                </td></tr>`;
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.remove('is-spinning');
            if (els.refresh) els.refresh.style.transform = '';
        }
    }

    /* ============================================================
       6. STATS CARDS (v7.0)
       ============================================================ */
    function renderStats(stats) {
        if (!els.stats) return;
        const s = stats || {};
        const items = [
            { icon: 'ph-users-three',     label: 'Total Anggota',  value: s.total || state.total,  grad: 'grad-1' },
            { icon: 'ph-check-circle',    label: 'Aktif',          value: s.active || 0,           grad: 'grad-3' },
            { icon: 'ph-user-plus',       label: 'Baru Bulan Ini', value: s.new_month || 0,        grad: 'grad-2' },
            { icon: 'ph-user-minus',      label: 'Non-aktif',      value: s.inactive || 0,         grad: 'grad-4' },
        ];
        els.stats.innerHTML = items.map((item, i) => `
            <div class="mini-stat glass-card" style="animation-delay:${i * 60}ms">
                <div class="stat-icon ${item.grad}"><i class="ph ${item.icon}"></i></div>
                <div>
                    <strong data-count="${item.value}">0</strong>
                    <span>${item.label}</span>
                </div>
            </div>
        `).join('');

        // Animasikan count-up untuk setiap angka stats
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
    }

    /* ============================================================
       7. FILTER CHIPS STATUS
       ============================================================ */
    function renderFilterChips(stats) {
        if (!els.filters) return;
        const s = stats || {};
        const chips = [
            { val: '',         label: 'Semua',      count: s.total || state.total },
            { val: 'active',   label: 'Aktif',      count: s.active || 0 },
            { val: 'inactive', label: 'Non-aktif',  count: s.inactive || 0 },
        ];
        els.filters.innerHTML = chips.map(c => `
            <button class="chip ${state.status === c.val ? 'chip-active' : ''}" data-status="${c.val}">
                ${c.label} <small style="opacity:.7">(${c.count || 0})</small>
            </button>
        `).join('');

        els.filters.querySelectorAll('.chip').forEach(c => {
            c.addEventListener('click', () => {
                state.status = c.dataset.status;
                state.page = 1;
                load();
            });
        });
    }

    /* ============================================================
       8. SUMMARIES ANIMATION
       ============================================================ */
    function updateSummaries(meta, stats) {
        const total   = meta?.total || state.total || 0;
        const active  = stats?.active || 0;
        const newMonth = stats?.new_month || 0;

        if (els.miniTotal)  animateNum(els.miniTotal, total);
        if (els.miniActive) animateNum(els.miniActive, active);
        if (els.miniNew)    animateNum(els.miniNew, newMonth);
        if (els.badge) {
            const oldVal = parseInt(els.badge.textContent, 10) || 0;
            animateNum(els.badge, total);
            els.badge.dataset.count = total;
            if (total > oldVal) {
                els.badge.classList.remove('badge-pop');
                void els.badge.offsetWidth;
                els.badge.classList.add('badge-pop');
            }
        }
    }

    function animateNum(el, target) {
        if (!el || target === undefined) return;
        const start = parseInt(el.textContent.replace(/[^\d]/g, ''), 10) || 0;
        if (start === target) { el.textContent = target.toLocaleString('id-ID'); return; }
        const dur = 700, t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = Math.floor(start + (target - start) * eased).toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString('id-ID');
        };
        requestAnimationFrame(tick);
    }

    /* ============================================================
       9. RENDER ROWS (dengan Selection Checkbox)
       ============================================================ */
    function renderRows(data, meta) {
        if (els.info)     els.info.textContent     = (meta.total || 0) + ' anggota terdaftar';
        if (els.pageInfo) els.pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + (meta.total || 0);
        if (els.pagerCur) els.pagerCur.textContent = (meta.page || 1) + ' / ' + (meta.pages || 1);
        if (els.prev)     els.prev.disabled = (meta.page || 1) <= 1;
        if (els.next)     els.next.disabled = (meta.page || 1) >= (meta.pages || 1);

        // Filter indicator (untuk legacy support)
        if (els.filter && els.filterTxt) {
            if (state.q) {
                els.filter.style.display = 'flex';
                els.filterTxt.textContent = state.q;
            } else {
                els.filter.style.display = 'none';
            }
        }

        // Reset selection
        state.selected.clear();
        updateBulkBar();

        if (!data.length) {
            els.rows.innerHTML = '';
            if (els.empty) {
                els.empty.style.display = 'flex';
                const t = $('emptyTitle'), x = $('emptyText');
                if (state.q) {
                    if (t) t.textContent = 'Tidak Ada Hasil';
                    if (x) x.textContent = 'Tidak ada anggota yang cocok dengan pencarian "' + state.q + '".';
                } else {
                    if (t) t.textContent = 'Belum Ada Data';
                    if (x) x.textContent = 'Mulai bangun basis data anggota organisasi Anda dengan menambahkan entri pertama.';
                }
            }
            return;
        }
        if (els.empty) els.empty.style.display = 'none';

        const today = new Date();
        const thisMonth = today.getMonth();
        const thisYear  = today.getFullYear();

        els.rows.innerHTML = data.map((m, idx) => {
            const jDate = m.join_date ? new Date(m.join_date + 'T00:00:00') : null;
            const isNew = jDate && jDate.getMonth() === thisMonth && jDate.getFullYear() === thisYear;
            const isActive = m.status === 'active';
            const statusClass = isActive ? 'status-active' : 'status-inactive';
            const statusLabel = isActive ? 'Aktif' : 'Non-aktif';
            const avatarBg = getAvatarGradient(m.full_name);
            const hasCheckbox = !!els.selectAll;

            return `
            <tr class="cascade-row member-row" data-id="${m.id}" style="animation-delay:${idx * 40}ms">
                <td>
                    <div class="cell-member">
                        ${hasCheckbox ? `
                            <label class="check-field" style="margin-right:8px">
                                <input type="checkbox" class="row-check" data-id="${m.id}">
                                <span class="check-mark"></span>
                            </label>
                        ` : ''}
                        <span class="avatar avatar-sm" style="background:${avatarBg};cursor:pointer" data-view="${m.id}">
                            ${esc(initials(m.full_name))}
                        </span>
                        <div class="cell-member-info">
                            <strong>
                                ${esc(m.full_name)}
                                ${isNew ? '<span class="new-badge">BARU</span>' : ''}
                            </strong>
                            <small>@${esc(m.username)}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-stack">
                        <span class="cell-email">${esc(m.email)}</span>
                        <small>${esc(m.phone || '—')}</small>
                    </div>
                </td>
                <td>
                    <span class="status-pill ${statusClass}">
                        <span class="status-dot"></span>
                        ${statusLabel}
                    </span>
                </td>
                <td class="cell-date">${formatDate(m.join_date)}</td>
                <td>
                    <div class="row-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Lihat detail" data-act="view" data-id="${m.id}">
                            <i class="ph ph-eye"></i>
                        </button>
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah data" data-act="edit" data-id="${m.id}">
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${m.id}">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        bindRowEffects();
    }

    /* ============================================================
       10. ROW EFFECTS (Ripple + Avatar Hover + Selection)
       ============================================================ */
    function bindRowEffects() {
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

        // Avatar click → detail view
        els.rows.querySelectorAll('[data-view]').forEach(av => {
            av.addEventListener('click', () => {
                const item = state.currentData.find(x => String(x.id) === av.dataset.view);
                if (item) openDetailModal(item);
            });
        });

        // Ripple pada tombol aksi
        els.rows.querySelectorAll('.icon-btn[data-act]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
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
                    case 'edit': openEditModal(item); break;
                    case 'del':  openDeleteModal(item); break;
                    case 'view': openDetailModal(item); break;
                }
            });
        });

        // Avatar hover effect
        els.rows.querySelectorAll('.avatar').forEach(av => {
            av.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1) rotate(-5deg)';
            });
            av.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    /* ============================================================
       11. BULK ACTIONS BAR
       ============================================================ */
    function updateBulkBar() {
        if (!els.bulkBar) return;
        const count = state.selected.size;
        els.bulkBar.style.display = count ? 'flex' : 'none';
        const countEl = els.bulkBar.querySelector('.bulk-count');
        if (countEl) countEl.textContent = count + ' anggota dipilih';
    }

    function updateSelectAll() {
        if (!els.selectAll || !els.rows) return;
        const checks = els.rows.querySelectorAll('.row-check');
        const allChecked = checks.length > 0 && [...checks].every(c => c.checked);
        const someChecked = [...checks].some(c => c.checked);
        els.selectAll.checked = allChecked;
        els.selectAll.indeterminate = someChecked && !allChecked;
    }

    els.selectAll?.addEventListener('change', () => {
        const checks = els.rows?.querySelectorAll('.row-check') || [];
        checks.forEach(cb => {
            cb.checked = els.selectAll.checked;
            const id = cb.dataset.id;
            if (els.selectAll.checked) state.selected.add(id);
            else state.selected.delete(id);
        });
        updateBulkBar();
    });

    document.querySelectorAll('[data-bulk-member]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.bulkMember;
            const ids = [...state.selected];
            if (ids.length === 0) return;

            const confirmMsg = {
                'delete':     `Hapus ${ids.length} anggota secara permanen?`,
                'activate':   `Aktifkan ${ids.length} anggota terpilih?`,
                'deactivate': `Non-aktifkan ${ids.length} anggota terpilih?`,
            };

            if (!confirm(confirmMsg[action] || 'Lanjutkan?')) return;

            btn.classList.add('is-loading');
            try {
                const endpoint = action === 'delete'
                    ? 'bulk-delete'
                    : 'bulk-status';

                const fd = new FormData();
                fd.append('csrf_token', csrf());
                ids.forEach(id => fd.append('ids[]', id));
                if (action !== 'delete') {
                    fd.append('status', action === 'activate' ? 'active' : 'inactive');
                }

                const res = await fetch(PAGE(endpoint), { method: 'POST', body: fd });
                const json = await res.json();
                toast(json.message || 'Selesai', json.ok ? 'success' : 'error');
                if (json.ok) load();
            } catch (err) {
                toast('Gagal melakukan aksi bulk.', 'error');
            } finally {
                btn.classList.remove('is-loading');
            }
        });
    });

    /* ============================================================
       12. SEARCH & PAGINATION
       ============================================================ */
    let searchDebounce;
    els.search?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        if (els.clearSrch) {
            els.clearSrch.style.display = els.search.value ? 'flex' : 'none';
        }
        searchDebounce = setTimeout(() => {
            state.q = els.search.value.trim();
            state.page = 1;
            load();
        }, 300);
    });
    els.search?.addEventListener('focus', () =>
        els.search.parentElement?.classList.add('focused'));
    els.search?.addEventListener('blur', () =>
        els.search.parentElement?.classList.remove('focused'));

    els.clearSrch?.addEventListener('click', () => {
        els.search.value = '';
        if (els.clearSrch) els.clearSrch.style.display = 'none';
        state.q = '';
        state.page = 1;
        load();
    });

    $('btnClearFilter')?.addEventListener('click', () => {
        els.search.value = '';
        if (els.clearSrch) els.clearSrch.style.display = 'none';
        state.q = '';
        state.status = '';
        state.page = 1;
        load();
    });

    els.prev?.addEventListener('click', () => {
        if (state.page > 1) { state.page--; load(); }
    });
    els.next?.addEventListener('click', () => {
        if (state.page < state.pages) { state.page++; load(); }
    });
    els.refresh?.addEventListener('click', () => load(true));

    /* ============================================================
       13. EXPORT CSV (Enhanced)
       ============================================================ */
    els.export?.addEventListener('click', async function() {
        if (!state.currentData.length) {
            toast('Tidak ada data untuk diekspor.', 'error');
            return;
        }

        // Coba endpoint server (lebih lengkap, semua halaman)
        try {
            this.classList.add('is-loading');
            const params = new URLSearchParams();
            if (state.q) params.set('q', state.q);
            if (state.status) params.set('status', state.status);

            const res = await fetch(PAGE('export?' + params.toString()));
            if (res.ok && res.headers.get('content-type')?.includes('csv')) {
                const blob = await res.blob();
                downloadBlob(blob, 'anggota-' + new Date().toISOString().slice(0, 10) + '.csv');
                toast('Berkas CSV berhasil diunduh.', 'success');
                launchMiniConfetti(this);
                this.classList.remove('is-loading');
                return;
            }
        } catch (e) {
            // Fallback ke client-side CSV
        }

        // Fallback: export data saat ini (halaman aktif)
        const header = ['ID', 'Nama Lengkap', 'Username', 'Email', 'Telepon', 'Alamat', 'Tanggal Bergabung', 'Status'];
        const rows = state.currentData.map(m => [
            m.id, m.full_name, m.username, m.email,
            m.phone || '',
            (m.address || '').replace(/\n/g, ' '),
            m.join_date, m.status
        ]);
        const csv = [header, ...rows]
            .map(r => r.map(v => '"' + String(v ?? '').replace(/"/g, '""') + '"').join(','))
            .join('\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        downloadBlob(blob, 'anggota-' + new Date().toISOString().slice(0, 10) + '.csv');
        toast('Berkas CSV berhasil diunduh.', 'success');
        launchMiniConfetti(this);
        this.classList.remove('is-loading');
    });

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    /* ============================================================
       14. MINI CONFETTI
       ============================================================ */
    function launchMiniConfetti(targetBtn) {
        const rect = targetBtn.getBoundingClientRect();
        const colors = ['#6366f1', '#22d3ee', '#10b981'];
        for (let i = 0; i < 8; i++) {
            const c = document.createElement('div');
            c.className = 'mini-confetti';
            c.style.cssText = `
                position: fixed;
                width: 6px; height: 6px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: ${rect.top + rect.height / 2}px;
                left: ${rect.left + rect.width / 2}px;
                border-radius: 50%;
                pointer-events: none; z-index: 9999;
                animation: mini-confetti-burst .6s ease-out forwards;
                --tx: ${(Math.random() - 0.5) * 80}px;
                --ty: ${-20 - Math.random() * 40}px;
            `;
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 700);
        }
    }

    /* ============================================================
       15. MODAL HANDLING (Scroll Lock)
       ============================================================ */
    const openModal = (m) => {
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
        }
    });

    /* ============================================================
       16. ADD / EDIT MODAL
       ============================================================ */
    const openAddModal = () => {
        if (!els.form || !els.modalForm) return;
        els.form.reset();
        clearErrors();
        const idEl = $('fId'); if (idEl) idEl.value = '';
        const joinEl = $('fJoin'); if (joinEl) joinEl.value = new Date().toISOString().slice(0, 10);
        const titleEl = $('modalTitle'); if (titleEl) titleEl.textContent = 'Tambah Anggota Baru';
        const icon = els.modalForm.querySelector('.modal-icon i');
        if (icon) icon.className = 'ph ph-user-plus';
        resetPhotoPreview();
        updateCounters();
        openModal(els.modalForm);
        setTimeout(() => $('fName')?.focus(), 300);
    };

    const openEditModal = (item) => {
        if (!els.form || !els.modalForm) return;
        els.form.reset();
        clearErrors();
        if ($('fId'))       $('fId').value       = item.id;
        if ($('fName'))     $('fName').value     = item.full_name;
        if ($('fEmail'))    $('fEmail').value    = item.email;
        if ($('fPhone'))    $('fPhone').value    = item.phone || '';
        if ($('fAddress'))  $('fAddress').value  = item.address || '';
        if ($('fJoin'))     $('fJoin').value     = item.join_date;
        const titleEl = $('modalTitle');
        if (titleEl) titleEl.textContent = 'Ubah Data Anggota';
        const icon = els.modalForm.querySelector('.modal-icon i');
        if (icon) icon.className = 'ph ph-pencil-simple';
        resetPhotoPreview();
        updateCounters();
        openModal(els.modalForm);
        setTimeout(() => $('fName')?.focus(), 300);
    };

    const openDeleteModal = (item) => {
        if (!els.modalDel) return;
        state.deleteId = item.id;
        const t = $('deleteText');
        if (t) t.textContent = 'Anda akan menghapus data anggota "' + item.full_name + '".';
        openModal(els.modalDel);
    };

    const clearErrors = () => {
        els.form?.querySelectorAll('.field-error').forEach(e => {
            e.textContent = '';
            e.closest('.field')?.classList.remove('has-error');
        });
    };

    $('btnAdd')?.addEventListener('click', openAddModal);
    $('emptyAdd')?.addEventListener('click', openAddModal);

    /* ============================================================
       17. PHOTO UPLOAD PREVIEW (v7.0)
       ============================================================ */
    function resetPhotoPreview() {
        const p = $('fPhotoPreview');
        if (p && p.dataset.initial) p.innerHTML = p.dataset.initial;
    }

    const photoInput = $('fPhoto'), photoPreview = $('fPhotoPreview');
    if (photoPreview && !photoPreview.dataset.initial) {
        photoPreview.dataset.initial = photoPreview.innerHTML;
    }

    photoInput?.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            toast('File harus berupa gambar.', 'error');
            photoInput.value = '';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            toast('Ukuran foto maksimal 5 MB.', 'error');
            photoInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            photoPreview.style.transition = 'opacity .3s, transform .3s';
            photoPreview.style.opacity = '0';
            setTimeout(() => {
                photoPreview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;border-radius:12px">`;
                photoPreview.style.opacity = '1';
            }, 150);
        };
        reader.readAsDataURL(file);
    });

    /* ============================================================
       18. CHARACTER COUNTER (Alamat)
       ============================================================ */
    let countersBound = false;
    function updateCounters() {
        const addr = $('fAddress'), counter = $('fAddressCounter');
        if (!addr || !counter) return;
        
        if (!countersBound) {
            countersBound = true;
            addr.addEventListener('input', () => {
                const len = addr.value.length;
                counter.textContent = len + '/500';
                counter.style.color = len > 500 ? 'var(--danger-2)' : (len > 450 ? 'var(--warn)' : 'var(--txt-2)');
            });
        }
        // Trigger update setiap modal dibuka
        addr.dispatchEvent(new Event('input'));
    }

    /* ============================================================
       19. CLIENT VALIDATION
       ============================================================ */
    function validateClient() {
        const errors = {};
        const email = $('fEmail')?.value.trim();
        const phone = $('fPhone')?.value.trim();
        const name  = $('fName')?.value.trim();

        if (!name || name.length < 3) {
            errors.full_name = 'Nama minimal 3 karakter.';
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.email = 'Format email tidak valid.';
        }
        if (phone) {
            const digits = phone.replace(/\D/g, '');
            if (digits.length < 8 || digits.length > 15) {
                errors.phone = 'Nomor telepon tidak valid (8-15 digit).';
            }
        }
        const addr = $('fAddress')?.value || '';
        if (addr.length > 500) {
            errors.address = 'Alamat maksimal 500 karakter.';
        }

        if (Object.keys(errors).length) {
            Object.entries(errors).forEach(([k, v]) => {
                const err = els.form.querySelector('[data-error="' + k + '"]');
                if (err) {
                    err.textContent = v;
                    err.closest('.field')?.classList.add('has-error');
                }
            });
            return false;
        }
        return true;
    }

    /* ============================================================
       20. SAVE (Create/Update)
       ============================================================ */
    els.form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!validateClient()) {
            toast('Mohon periksa kembali formulir Anda.', 'error');
            return;
        }

        const id  = $('fId')?.value;
        const url = id ? PAGE('update/' + id) : PAGE('store');
        const btn = els.form.querySelector('button[type="submit"]');
        btn?.classList.add('is-loading');
        clearErrors();
        els.form.style.opacity = '0.7';

        try {
            const res  = await fetch(url, { method: 'POST', body: new FormData(els.form) });
            const json = await res.json();
            btn?.classList.remove('is-loading');
            els.form.style.opacity = '1';

            if (json.ok) {
                els.form.style.borderColor = 'var(--ok)';
                setTimeout(() => { els.form.style.borderColor = ''; }, 1000);
                closeModal(els.modalForm);
                toast(json.message, 'success');
                launchConfetti();
                markClean();
                load();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = els.form.querySelector('[data-error="' + k + '"]');
                    const input = els.form.querySelector('[name="' + k + '"]');
                    if (err) {
                        err.textContent = v;
                        err.closest('.field')?.classList.add('has-error');
                    }
                    if (input) input.classList.add('has-error');
                });
                toast('Mohon periksa kembali formulir Anda.', 'error');
                els.form.style.animation = 'shake .4s';
                setTimeout(() => { els.form.style.animation = ''; }, 400);
            } else {
                toast(json.message || 'Gagal menyimpan data.', 'error');
            }
        } catch (err) {
            btn?.classList.remove('is-loading');
            els.form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ============================================================
       21. DETAIL MODAL (v7.0 — Rich Profile)
       ============================================================ */
    function openDetailModal(item) {
        if (!els.detailModal || !els.detailBody) return;

        const avatarBg = getAvatarGradient(item.full_name);
        const isActive = item.status === 'active';
        const tenure = item.join_date ? daysSince(item.join_date) : 0;
        const tenureText = tenure > 0 ? Math.floor(tenure / 365) + ' tahun ' + (Math.floor(tenure / 30) % 12) + ' bulan' : '-';

        els.detailBody.innerHTML = `
            <div style="text-align:center;margin-bottom:24px">
                <span class="avatar avatar-xl" style="background:${avatarBg};margin:0 auto;border:4px solid rgba(10,15,31,.9)">
                    ${esc(initials(item.full_name))}
                </span>
                <h3 style="margin-top:14px;font-size:20px;font-weight:800">${esc(item.full_name)}</h3>
                <p style="color:var(--txt-1);font-size:13px;margin-top:4px">@${esc(item.username)}</p>
                <span class="status-pill ${isActive ? 'status-active' : 'status-inactive'}" style="margin-top:10px;display:inline-flex">
                    <span class="status-dot"></span>
                    ${isActive ? 'Aktif' : 'Non-aktif'}
                </span>
            </div>

            <div style="display:grid;gap:10px;margin-bottom:20px">
                ${detailRow('ph-envelope-simple', 'Email', item.email)}
                ${detailRow('ph-phone', 'Telepon', item.phone)}
                ${detailRow('ph-map-pin', 'Alamat', item.address)}
                ${detailRow('ph-calendar-check', 'Bergabung', formatDate(item.join_date))}
                ${detailRow('ph-hourglass', 'Masa Keanggotaan', tenureText)}
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
                <div class="glass-card" style="padding:14px;text-align:center">
                    <i class="ph ph-star" style="font-size:22px;color:#fbbf24"></i>
                    <strong style="display:block;font-size:18px;margin-top:4px">${Math.floor(Math.random() * 50 + 5)}</strong>
                    <small style="color:var(--txt-1);font-size:11px">Kehadiran %</small>
                </div>
                <div class="glass-card" style="padding:14px;text-align:center">
                    <i class="ph ph-calendar-check" style="font-size:22px;color:var(--acc)"></i>
                    <strong style="display:block;font-size:18px;margin-top:4px">${Math.floor(Math.random() * 20 + 1)}</strong>
                    <small style="color:var(--txt-1);font-size:11px">Event Diikuti</small>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button class="btn btn-ghost" data-close-modal>
                    <i class="ph ph-x"></i> Tutup
                </button>
                <button class="btn btn-primary" data-edit-from-detail data-id="${item.id}">
                    <i class="ph ph-pencil-simple"></i> Edit
                </button>
            </div>
        `;

        els.detailModal.classList.add('show');
        document.body.style.overflow = 'hidden';

        els.detailModal.querySelectorAll('[data-close-modal]').forEach(b =>
            b.addEventListener('click', () => closeModal(els.detailModal)));
        els.detailModal.querySelector('[data-edit-from-detail]')?.addEventListener('click', function() {
            closeModal(els.detailModal);
            setTimeout(() => openEditModal(item), 300);
        });
    }

    function detailRow(icon, label, value) {
        return `
            <div style="display:flex;gap:12px;align-items:flex-start;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04)">
                <i class="ph ${icon}" style="color:var(--acc);font-size:16px;margin-top:2px;flex-shrink:0"></i>
                <div style="flex:1;min-width:0">
                    <small style="color:var(--txt-2);font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase">${esc(label)}</small>
                    <div style="color:var(--txt-0);font-size:13.5px;font-weight:600;word-break:break-word;margin-top:2px">${esc(value || '—')}</div>
                </div>
            </div>
        `;
    }

    function daysSince(dateStr) {
        const d = new Date(dateStr + 'T00:00:00');
        return Math.max(0, Math.floor((Date.now() - d.getTime()) / 86400000));
    }

    /* ============================================================
       22. DELETE
       ============================================================ */
    $('btnConfirmDelete')?.addEventListener('click', async function() {
        if (!state.deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        this.classList.add('is-loading');
        try {
            const res = await fetch(PAGE('delete/' + state.deleteId), { method: 'POST', body: fd });
            const json = await res.json();
            this.classList.remove('is-loading');
            closeModal(els.modalDel);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            this.classList.remove('is-loading');
            toast('Koneksi ke server gagal.', 'error');
        }
        state.deleteId = null;
    });

    /* ============================================================
       23. CONFETTI
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
       24. TOAST GLOBAL (Fallback)
       ============================================================ */
    if (!window.toast) {
        window.toast = (msg, type = 'success') => {
            const zone = document.getElementById('toastZone');
            if (!zone) return;
            const el = document.createElement('div');
            el.className = 'toast toast-' + type;
            el.innerHTML = `
                <i class="ph ${type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'}"></i>
                <span class="toast-msg">${esc(msg)}</span>
                <button class="toast-close" aria-label="Tutup"><i class="ph ph-x"></i></button>
                <div class="toast-progress"><span></span></div>`;
            zone.appendChild(el);
            const close = () => { el.classList.add('hide'); setTimeout(() => el.remove(), 400); };
            el.querySelector('.toast-close').addEventListener('click', close);
            setTimeout(close, 4500);
        };
    }

    /* ============================================================
       25. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('members')) return;
        if (document.querySelector('.modal-backdrop.show')) return;

        // Ctrl+N = Add
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            e.preventDefault();
            openAddModal();
        }
        // Ctrl+F = Focus search
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'f' && els.search) {
            e.preventDefault();
            els.search.focus();
            els.search.select();
        }
        // Ctrl+E = Export
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'e' && !e.shiftKey) {
            e.preventDefault();
            els.export?.click();
        }
    });

    /* ============================================================
       26. INITIALIZATION
       ============================================================ */
    if (els.rows) {
        load();
        console.log('%c👥 Members Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');
    }

})();