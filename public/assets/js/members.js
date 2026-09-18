// File: public/assets/js/members.js (FINAL)
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

    async function load(spinRefresh = false) {
        if (spinRefresh) refreshBtn.querySelector('i').classList.add('is-spinning');
        if (!spinRefresh) renderSkeleton();
        try {
            const res  = await fetch(api('api/members?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            state.total = json.meta.total;
            renderRows(json.data, json.meta);
            updateSummaries(json.meta.total);
        } catch (err) {
            rowsEl.innerHTML = '<tr><td colspan="5" class="empty-state error"><i class="ph ph-warning-circle"></i>Gagal memuat data dari server.</td></tr>';
        } finally {
            refreshBtn.querySelector('i').classList.remove('is-spinning');
        }
    }

    function updateSummaries(total) {
        if (miniTotal) animateNum(miniTotal, total);
        if (miniActive) animateNum(miniActive, total); // Sederhana: anggap semua aktif
        if (miniNew) animateNum(miniNew, Math.min(total, Math.floor(total * 0.2)));
        if (memberBadge) {
            memberBadge.textContent = total;
            memberBadge.dataset.count = total;
        }
    }

    function animateNum(el, target) {
        const start = parseInt(el.textContent, 10) || 0;
        if (start === target) return;
        const dur = 700;
        const t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.floor(start + (target - start) * eased);
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target;
        };
        requestAnimationFrame(tick);
    }

    function renderSkeleton() {
        rowsEl.innerHTML = Array.from({ length: 5 }, () => `
            <tr class="skeleton-row">
                <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:65%"></div><div class="skel" style="height:10px;width:40%;margin-top:6px"></div></div></div></td>
                <td><div class="skel-col"><div class="skel" style="height:12px;width:75%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></td>
                <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                <td><div class="skel" style="height:12px;width:80px"></div></td>
                <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
            </tr>`).join('');
    }

    function renderRows(data, meta) {
        infoEl.textContent   = meta.total + ' anggota terdaftar';
        pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + meta.total;
        if (pagerCur) pagerCur.textContent = meta.page + ' / ' + meta.pages;
        prevBtn.disabled = meta.page <= 1;
        nextBtn.disabled = meta.page >= meta.pages;

        // Indikator filter
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
            return `
            <tr class="cascade-row" style="animation-delay:${idx * 40}ms">
                <td>
                    <div class="cell-member">
                        <span class="avatar avatar-sm">${esc(initials(m.full_name))}</span>
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
    }

    /* ---------- Live search & paginasi ---------- */
    let debounce;
    searchEl.addEventListener('input', () => {
        clearTimeout(debounce);
        clearSrch.style.display = searchEl.value ? 'flex' : 'none';
        debounce = setTimeout(() => { state.q = searchEl.value.trim(); state.page = 1; load(); }, 300);
    });
    clearSrch.addEventListener('click', () => {
        searchEl.value = ''; clearSrch.style.display = 'none';
        state.q = ''; state.page = 1; load();
    });
    document.getElementById('btnClearFilter').addEventListener('click', () => {
        searchEl.value = ''; clearSrch.style.display = 'none';
        state.q = ''; state.page = 1; load();
    });
    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
    nextBtn.addEventListener('click', () => { if (state.page < state.pages) { state.page++; load(); } });
    refreshBtn.addEventListener('click', () => load(true));

    /* ---------- Ekspor CSV ---------- */
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
    });

    /* ---------- Modal ---------- */
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
        form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
        form.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));
        document.getElementById('fId').value = '';
        document.getElementById('fJoin').value = new Date().toISOString().slice(0, 10);
        document.getElementById('modalTitle').textContent = 'Tambah Anggota Baru';
        const icon = modalForm.querySelector('.modal-icon i');
        if (icon) icon.className = 'ph ph-user-plus';
        openModal(modalForm);
    };
    document.getElementById('btnAdd').addEventListener('click', openAddModal);
    document.getElementById('emptyAdd').addEventListener('click', openAddModal);

    /* ---------- Aksi baris ---------- */
    rowsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.reset();
            form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
            form.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));
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
        }

        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('deleteText').textContent =
                'Anda akan menghapus data anggota "' + item.full_name + '".';
            openModal(modalDel);
        }
    });

    /* ---------- Simpan ---------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('fId').value;
        const url = id ? api('members/update/' + id) : api('members/store');
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');
        form.querySelectorAll('.field-error').forEach(e => e.textContent = '');
        form.querySelectorAll('.has-error').forEach(e => e.classList.remove('has-error'));

        const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
        const json = await res.json();
        btn.classList.remove('is-loading');

        if (json.ok) {
            closeModal(modalForm);
            toast(json.message, 'success');
            load();
        } else if (json.errors) {
            Object.entries(json.errors).forEach(([k, v]) => {
                const err = form.querySelector('[data-error="' + k + '"]');
                const input = form.querySelector('[name="' + k + '"]');
                if (err) err.textContent = v;
                if (input) input.classList.add('has-error');
            });
            toast('Mohon periksa kembali formulir Anda.', 'error');
        } else {
            toast(json.message || 'Gagal menyimpan data.', 'error');
        }
    });

    /* ---------- Hapus ---------- */
    document.getElementById('btnConfirmDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmDelete');
        btn.classList.add('is-loading');
        const res  = await fetch(api('members/delete/' + deleteId), { method: 'POST', body: fd });
        const json = await res.json();
        btn.classList.remove('is-loading');
        closeModal(modalDel);
        toast(json.message, json.ok ? 'success' : 'error');
        if (json.ok) load();
        deleteId = null;
    });

    /* ---------- Toast dengan progress bar ---------- */
    function toast(msg, type = 'success') {
        const zone = document.getElementById('toastZone');
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
    }

    load();
})();