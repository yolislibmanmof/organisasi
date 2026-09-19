// File: public/assets/js/members.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    const rowsEl    = document.getElementById('memberRows');
    const infoEl    = document.getElementById('tableInfo');
    const pageInfo  = document.getElementById('pageInfo');
    const pagerCur  = document.getElementById('pagerCurrent');
    const prevBtn   = document.getElementById('prevPage');
    const nextBtn   = document.getElementById('nextPage');
    const searchEl  = document.getElementById('searchInput');
    const clearSrch = document.getElementById('btnClearSearch');
    const modalForm = document.getElementById('memberModal');
    const modalDel  = document.getElementById('deleteModal');
    const form      = document.getElementById('memberForm');
    const emptyEl   = document.getElementById('emptyState');
    const filterEl  = document.getElementById('filterIndicator');
    const filterTxt = document.getElementById('filterText');
    const refreshBtn= document.getElementById('btnRefresh');
    const exportBtn = document.getElementById('btnExport');
    const miniTotal = document.getElementById('miniTotal');
    const miniActive= document.getElementById('miniActive');
    const miniNew   = document.getElementById('miniNew');
    const memberBadge = document.getElementById('memberBadge');

    const state = { page: 1, pages: 1, q: '', total: 0 };
    let currentData = [];
    let deleteId = null;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const initials = (name) => String(name || '?').trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
    const formatDate = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
    const csrf = () => form.querySelector('input[name="csrf_token"]').value;

    // Hapus error validasi saat pengguna mengetik
    form.querySelectorAll('input, textarea').forEach(el => {
        el.addEventListener('input', () => {
            const err = form.querySelector('[data-error="' + el.name + '"]');
            if (err) { err.textContent = ''; el.classList.remove('has-error'); }
        });
    });

    /* ========== 1. LOAD DATA ========== */
    async function load(spinRefresh = false) {
        if (spinRefresh) {
            refreshBtn.querySelector('i').classList.add('is-spinning');
            refreshBtn.style.transform = 'rotate(360deg)';
        } else {
            renderSkeleton();
        }
        try {
            const res  = await fetch(api('api/members?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            state.total = json.meta.total;
            renderRows(json.data, json.meta);
            updateSummaries(json.meta.total);
        } catch (err) {
            rowsEl.innerHTML = '<tr><td colspan="5" class="empty-state error"><i class="ph ph-warning-circle"></i> Gagal memuat data dari server.</td></tr>';
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            refreshBtn.querySelector('i').classList.remove('is-spinning');
            refreshBtn.style.transform = '';
        }
    }

    /* ========== 2. UPDATE SUMMARIES ========== */
    function updateSummaries(total) {
        if (miniTotal) animateNum(miniTotal, total);
        if (miniActive) animateNum(miniActive, total);
        if (miniNew) animateNum(miniNew, Math.min(total, Math.floor(total * 0.2)));
        if (memberBadge) {
            const oldVal = parseInt(memberBadge.textContent, 10) || 0;
            animateNum(memberBadge, total);
            memberBadge.dataset.count = total;
            if (total > oldVal) memberBadge.classList.add('badge-pop');
        }
    }

    /* ========== 3. ANIMATE NUMBER ========== */
    function animateNum(el, target) {
        const start = parseInt(el.textContent, 10) || 0;
        if (start === target) return;
        const dur = 700;
        const t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = Math.floor(start + (target - start) * eased);
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target;
        };
        requestAnimationFrame(tick);
    }

    /* ========== 4. SKELETON LOADING ========== */
    function renderSkeleton() {
        rowsEl.innerHTML = Array.from({ length: 6 }, (_, i) => `
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

    /* ========== 5. GRADIENT AVATAR COLOR ========== */
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

    /* ========== 6. RENDER ROWS ========== */
    function renderRows(data, meta) {
        infoEl.textContent   = meta.total + ' anggota terdaftar';
        pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + meta.total;
        if (pagerCur) pagerCur.textContent = meta.page + ' / ' + meta.pages;
        prevBtn.disabled = meta.page <= 1;
        nextBtn.disabled = meta.page >= meta.pages;

        if (state.q) {
            filterEl.style.display = 'flex';
            filterTxt.textContent = state.q;
        } else {
            filterEl.style.display = 'none';
        }

        if (!data.length) {
            rowsEl.innerHTML = '';
            emptyEl.style.display = 'flex';
            const emptyTitle = document.getElementById('emptyTitle');
            const emptyText  = document.getElementById('emptyText');
            if (state.q) {
                emptyTitle.textContent = 'Tidak Ada Hasil';
                emptyText.textContent  = 'Tidak ada anggota yang cocok dengan pencarian "' + state.q + '".';
            } else {
                emptyTitle.textContent = 'Belum Ada Data';
                emptyText.textContent  = 'Mulai bangun basis data anggota organisasi Anda dengan menambahkan entri pertama.';
            }
            return;
        }

        emptyEl.style.display = 'none';
        const today = new Date();
        const thisMonth = today.getMonth();
        const thisYear  = today.getFullYear();

        rowsEl.innerHTML = data.map((m, idx) => {
            const jDate = m.join_date ? new Date(m.join_date + 'T00:00:00') : null;
            const isNew = jDate && jDate.getMonth() === thisMonth && jDate.getFullYear() === thisYear;
            const statusClass = (m.status === 'active') ? 'status-active' : 'status-inactive';
            const statusLabel = (m.status === 'active') ? 'Aktif' : 'Non-aktif';
            const avatarBg = getAvatarGradient(m.full_name);
            return `
            <tr class="cascade-row member-row" style="animation-delay:${idx * 40}ms">
                <td>
                    <div class="cell-member">
                        <span class="avatar avatar-sm" style="background:${avatarBg}">${esc(initials(m.full_name))}</span>
                        <div class="cell-member-info">
                            <strong>${esc(m.full_name)}${isNew ? '<span class="new-badge">BARU</span>' : ''}</strong>
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
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah data" data-act="edit" data-id="${m.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${m.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Bind efek pada baris dan tombol
        bindRowEffects();
    }

    /* ========== 7. EFEK PADA BARIS & TOMBOL ========== */
    function bindRowEffects() {
        // Ripple effect pada tombol aksi
        rowsEl.querySelectorAll('.icon-btn').forEach(btn => {
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

        // Highlight effect pada avatar saat hover
        rowsEl.querySelectorAll('.avatar').forEach(av => {
            av.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1) rotate(-5deg)';
            });
            av.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }

    /* ========== 8. LIVE SEARCH & PAGINASI ========== */
    let debounce;
    searchEl.addEventListener('input', () => {
        clearTimeout(debounce);
        clearSrch.style.display = searchEl.value ? 'flex' : 'none';
        debounce = setTimeout(() => { state.q = searchEl.value.trim(); state.page = 1; load(); }, 300);
    });
    searchEl.addEventListener('focus', () => searchEl.parentElement.classList.add('focused'));
    searchEl.addEventListener('blur', () => searchEl.parentElement.classList.remove('focused'));
    
    clearSrch.addEventListener('click', () => {
        searchEl.value = ''; clearSrch.style.display = 'none';
        state.q = ''; state.page = 1; load();
    });
    document.getElementById('btnClearFilter')?.addEventListener('click', () => {
        searchEl.value = ''; clearSrch.style.display = 'none';
        state.q = ''; state.page = 1; load();
    });
    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
    nextBtn.addEventListener('click', () => { if (state.page < state.pages) { state.page++; load(); } });
    refreshBtn.addEventListener('click', () => load(true));

    /* ========== 9. EKSPOR CSV ========== */
    exportBtn.addEventListener('click', () => {
        if (!currentData.length) { toast('Tidak ada data untuk diekspor.', 'error'); return; }
        const header = ['ID', 'Nama Lengkap', 'Username', 'Email', 'Telepon', 'Alamat', 'Tanggal Bergabung', 'Status'];
        const rows = currentData.map(m => [m.id, m.full_name, m.username, m.email, m.phone || '', (m.address || '').replace(/\n/g, ' '), m.join_date, m.status]);
        const csv = [header, ...rows].map(r => r.map(v => '"' + String(v ?? '').replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'anggota-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
        URL.revokeObjectURL(url);
        toast('Berkas CSV berhasil diunduh.', 'success');
        createMiniConfetti(exportBtn);
    });

    /* ========== 10. MINI KONFETI PADA TOMBOL ========== */
    function createMiniConfetti(targetBtn) {
        const rect = targetBtn.getBoundingClientRect();
        const colors = ['#6366f1', '#22d3ee', '#10b981'];
        for (let i = 0; i < 8; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'mini-confetti';
            confetti.style.cssText = `
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
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 700);
        }
    }

    /* ========== 11. MODAL HANDLING ========== */
    const openModal  = (m) => m.classList.add('show');
    const closeModal = (m) => m.classList.remove('show');
    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd); }));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.show').forEach(m => closeModal(m));
    });

    const openAddModal = () => {
        form.reset();
        form.querySelectorAll('.field-error').forEach(e => {
            e.textContent = '';
            e.closest('.field')?.classList.remove('has-error');
        });
        document.getElementById('fId').value = '';
        document.getElementById('fJoin').value = new Date().toISOString().slice(0, 10);
        document.getElementById('modalTitle').textContent = 'Tambah Anggota Baru';
        const icon = modalForm.querySelector('.modal-icon i');
        if (icon) icon.className = 'ph ph-user-plus';
        openModal(modalForm);
        setTimeout(() => document.getElementById('fName')?.focus(), 300);
    };
    document.getElementById('btnAdd').addEventListener('click', openAddModal);
    document.getElementById('emptyAdd')?.addEventListener('click', openAddModal);

    /* ========== 12. AKSI BARIS ========== */
    rowsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.reset();
            form.querySelectorAll('.field-error').forEach(e => {
                e.textContent = '';
                e.closest('.field')?.classList.remove('has-error');
            });
            document.getElementById('fId').value      = item.id;
            document.getElementById('fName').value    = item.full_name;
            document.getElementById('fEmail').value   = item.email;
            document.getElementById('fPhone').value   = item.phone || '';
            document.getElementById('fAddress').value = item.address || '';
            document.getElementById('fJoin').value    = item.join_date;
            document.getElementById('modalTitle').textContent = 'Ubah Data Anggota';
            const icon = modalForm.querySelector('.modal-icon i');
            if (icon) icon.className = 'ph ph-pencil-simple';
            openModal(modalForm);
            setTimeout(() => document.getElementById('fName')?.focus(), 300);
        }

        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('deleteText').textContent =
                'Anda akan menghapus data anggota "' + item.full_name + '".';
            openModal(modalDel);
        }
    });

    /* ========== 13. SIMPAN ========== */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('fId').value;
        const url = id ? api('members/update/' + id) : api('members/store');
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');
        form.querySelectorAll('.field-error').forEach(e => {
            e.textContent = '';
            e.closest('.field')?.classList.remove('has-error');
        });

        form.style.opacity = '0.7';
        try {
            const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            btn.classList.remove('is-loading');
            form.style.opacity = '1';

            if (json.ok) {
                form.style.borderColor = 'var(--ok)';
                setTimeout(() => form.style.borderColor = '', 1000);
                closeModal(modalForm);
                toast(json.message, 'success');
                createConfetti();
                load();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = form.querySelector('[data-error="' + k + '"]');
                    const input = form.querySelector('[name="' + k + '"]');
                    if (err) {
                        err.textContent = v;
                        err.closest('.field')?.classList.add('has-error');
                    }
                    if (input) input.classList.add('has-error');
                });
                toast('Mohon periksa kembali formulir Anda.', 'error');
                form.style.animation = 'shake .4s';
                setTimeout(() => form.style.animation = '', 400);
            } else {
                toast(json.message || 'Gagal menyimpan data.', 'error');
            }
        } catch (err) {
            btn.classList.remove('is-loading');
            form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ========== 14. KONFETI ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b'];
        for (let i = 0; i < 25; i++) {
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

    /* ========== 15. HAPUS ========== */
    document.getElementById('btnConfirmDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmDelete');
        btn.classList.add('is-loading');
        try {
            const res  = await fetch(api('members/delete/' + deleteId), { method: 'POST', body: fd });
            const json = await res.json();
            btn.classList.remove('is-loading');
            closeModal(modalDel);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            btn.classList.remove('is-loading');
            toast('Koneksi ke server gagal.', 'error');
        }
        deleteId = null;
    });

    /* ========== 16. KEYBOARD SHORTCUTS ========== */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            const isOnMembersPage = window.location.pathname.includes('members');
            if (isOnMembersPage) {
                e.preventDefault();
                openAddModal();
            }
        }
    });

    /* ========== 17. TOAST GLOBAL (fallback jika belum ada) ========== */
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

    load();
})();