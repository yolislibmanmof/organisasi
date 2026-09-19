// File: public/assets/js/articles.js
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    const rowsEl   = document.getElementById('articleRows');
    const emptyEl  = document.getElementById('emptyArticles');
    const infoEl   = document.getElementById('artInfo');
    const pageInfo = document.getElementById('artPageInfo');
    const pagerCur = document.getElementById('artPagerCur');
    const prevBtn  = document.getElementById('artPrev');
    const nextBtn  = document.getElementById('artNext');
    const searchEl = document.getElementById('artSearch');
    const refresh  = document.getElementById('btnArtRefresh');
    const modal    = document.getElementById('articleModal');
    const delModal = document.getElementById('articleDeleteModal');
    const form     = document.getElementById('articleForm');

    const state = { page: 1, pages: 1, q: '' };
    let currentData = [];
    let deleteId = null;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const csrf = () => form.querySelector('input[name="csrf_token"]').value;
    const catSlug = (c) => String(c || '').toLowerCase().replace(/\s+/g, '-');
    const fmtDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

    async function load(spin = false) {
        if (spin) refresh.querySelector('i').classList.add('is-spinning');
        else renderSkeleton();
        try {
            const res  = await fetch(api('api/articles?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            render(json.data, json.meta);
        } catch (e) {
            rowsEl.innerHTML = '<tr><td colspan="5" class="empty-state error">Gagal memuat data artikel.</td></tr>';
        } finally {
            refresh.querySelector('i').classList.remove('is-spinning');
        }
    }

    function renderSkeleton() {
        rowsEl.innerHTML = Array.from({ length: 4 }, () => `
            <tr>
                <td><div class="skel" style="height:15px;width:70%"></div></td>
                <td><div class="skel" style="height:20px;width:70px;border-radius:99px"></div></td>
                <td><div class="skel" style="height:14px;width:60%"></div></td>
                <td><div class="skel" style="height:14px;width:70px"></div></td>
                <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
            </tr>`).join('');
    }

    function render(data, meta) {
        infoEl.textContent   = meta.total + ' artikel';
        pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + meta.total;
        pagerCur.textContent = meta.page + ' / ' + meta.pages;
        prevBtn.disabled = meta.page <= 1;
        nextBtn.disabled = meta.page >= meta.pages;

        if (!data.length) {
            rowsEl.innerHTML = '';
            emptyEl.style.display = 'flex';
            return;
        }
        emptyEl.style.display = 'none';

        rowsEl.innerHTML = data.map((a, i) => `
            <tr class="cascade-row" style="animation-delay:${i * 40}ms">
                <td>
                    <strong style="font-size:13.5px">${esc(a.title)}</strong>
                    <small style="display:block;color:var(--txt-1);font-size:11.5px;margin-top:3px">
                        ${esc((a.excerpt || '').slice(0, 70))}${(a.excerpt || '').length > 70 ? '…' : ''}
                    </small>
                </td>
                <td><span class="cat-badge cat-${catSlug(a.category)}">${esc(a.category)}</span></td>
                <td style="color:var(--txt-1)">@${esc(a.author_name || 'sistem')}</td>
                <td class="cell-date">${fmtDate(a.created_at)}</td>
                <td>
                    <div class="row-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah" data-act="edit" data-id="${a.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${a.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`).join('');
    }

    /* ---------- Pencarian & paginasi ---------- */
    let debounce;
    searchEl.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => { state.q = searchEl.value.trim(); state.page = 1; load(); }, 300);
    });
    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
    nextBtn.addEventListener('click', () => { if (state.page < state.pages) { state.page++; load(); } });
    refresh.addEventListener('click', () => load(true));

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

    const openAdd = () => {
        form.reset();
        form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        document.getElementById('aId').value = '';
        document.getElementById('articleModalTitle').textContent = 'Tulis Artikel Baru';
        openModal(modal);
    };
    document.getElementById('btnAddArticle').addEventListener('click', openAdd);
    document.getElementById('emptyAddArticle').addEventListener('click', openAdd);

    /* ---------- Aksi baris ---------- */
    rowsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.getElementById('aId').value       = item.id;
            document.getElementById('aTitle').value    = item.title;
            document.getElementById('aCategory').value = item.category;
            document.getElementById('aExcerpt').value  = item.excerpt || '';
            document.getElementById('aContent').value  = item.content || '';
            document.getElementById('articleModalTitle').textContent = 'Sunting Artikel';
            openModal(modal);
        }
        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('articleDeleteText').textContent =
                'Artikel "' + item.title + '" akan dihapus permanen dari halaman publik.';
            openModal(delModal);
        }
    });

    /* ---------- Simpan ---------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('aId').value;
        const url = id ? api('articles/update/' + id) : api('articles/store');
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');
        form.querySelectorAll('.field-error').forEach(el => el.textContent = '');

        const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
        const json = await res.json();
        btn.classList.remove('is-loading');

        if (json.ok) {
            closeModal(modal);
            toast(json.message, 'success');
            load();
        } else if (json.errors) {
            Object.entries(json.errors).forEach(([k, v]) => {
                const err = form.querySelector('[data-error="' + k + '"]');
                if (err) err.textContent = v;
            });
            toast('Mohon periksa kembali isian Anda.', 'error');
        } else {
            toast(json.message || 'Gagal menyimpan.', 'error');
        }
    });

    /* ---------- Hapus ---------- */
    document.getElementById('btnConfirmArticleDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmArticleDelete');
        btn.classList.add('is-loading');
        const res  = await fetch(api('articles/delete/' + deleteId), { method: 'POST', body: fd });
        const json = await res.json();
        btn.classList.remove('is-loading');
        closeModal(delModal);
        toast(json.message, json.ok ? 'success' : 'error');
        if (json.ok) load();
        deleteId = null;
    });

    load();
})();