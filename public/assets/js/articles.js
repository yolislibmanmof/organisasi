// File: public/assets/js/articles.js (ULTIMATE EDITION - TAHAP 5.9)
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

    /* ========== 1. SKELETON LOADING ENHANCED ========== */
    function renderSkeleton() {
        rowsEl.innerHTML = Array.from({ length: 5 }, (_, i) => `
            <tr class="cascade-row" style="animation-delay:${i * 50}ms">
                <td>
                    <div class="skel-row">
                        <div class="skel skel-av" style="width:42px;height:42px;border-radius:12px"></div>
                        <div class="skel-col">
                            <div class="skel" style="height:13px;width:75%"></div>
                            <div class="skel" style="height:10px;width:50%;margin-top:6px"></div>
                        </div>
                    </div>
                </td>
                <td><div class="skel" style="height:22px;width:80px;border-radius:99px"></div></td>
                <td><div class="skel" style="height:12px;width:90px"></div></td>
                <td><div class="skel" style="height:12px;width:85px"></div></td>
                <td><div class="skel" style="height:30px;width:80px;margin-left:auto"></div></td>
            </tr>`).join('');
    }

    /* ========== 2. LOAD DATA ========== */
    async function load(spin = false) {
        if (spin) {
            refresh.querySelector('i').classList.add('is-spinning');
            refresh.style.transform = 'rotate(360deg)';
        } else {
            renderSkeleton();
        }
        try {
            const res  = await fetch(api('api/articles?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            render(json.data, json.meta);
        } catch (e) {
            rowsEl.innerHTML = '<tr><td colspan="5" class="empty-state error">Gagal memuat data artikel.</td></tr>';
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            refresh.querySelector('i').classList.remove('is-spinning');
            refresh.style.transform = '';
        }
    }

    /* ========== 3. RENDER TABLE ========== */
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

        rowsEl.innerHTML = data.map((a, i) => {
            const initial = (a.title || '?').charAt(0).toUpperCase();
            return `
            <tr class="cascade-row" style="animation-delay:${i * 40}ms">
                <td>
                    <div class="cell-member">
                        <div class="cat-avatar cat-${catSlug(a.category)}">${initial}</div>
                        <div class="cell-member-info">
                            <strong>${esc(a.title)}</strong>
                            <small>
                                ${esc((a.excerpt || '').slice(0, 70))}${(a.excerpt || '').length > 70 ? '…' : ''}
                            </small>
                        </div>
                    </div>
                </td>
                <td><span class="cat-badge cat-${catSlug(a.category)}">${esc(a.category)}</span></td>
                <td>
                    <span class="author-chip">
                        <i class="ph ph-user-circle"></i>
                        @${esc(a.author_name || 'sistem')}
                    </span>
                </td>
                <td class="cell-date">${fmtDate(a.created_at)}</td>
                <td>
                    <div class="row-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Sunting artikel" data-act="edit" data-id="${a.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus artikel" data-act="del" data-id="${a.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Tambahkan ripple effect ke tombol aksi
        bindRowRipples();
    }

    /* ========== 4. RIPPLE PADA TOMBOL BARIS ========== */
    function bindRowRipples() {
        rowsEl.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'btn-ripple';
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top = (e.clientY - rect.top) + 'px';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    /* ========== 5. PENCARIAN & PAGINASI ========== */
    let debounce;
    searchEl.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => { state.q = searchEl.value.trim(); state.page = 1; load(); }, 300);
    });
    searchEl.addEventListener('focus', () => searchEl.parentElement.classList.add('focused'));
    searchEl.addEventListener('blur', () => searchEl.parentElement.classList.remove('focused'));

    prevBtn.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
    nextBtn.addEventListener('click', () => { if (state.page < state.pages) { state.page++; load(); } });
    refresh.addEventListener('click', () => load(true));

    /* ========== 6. MODAL HANDLING ========== */
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
        form.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
        });
        document.getElementById('aId').value = '';
        document.getElementById('articleModalTitle').textContent = 'Tulis Artikel Baru';
        openModal(modal);
        setTimeout(() => document.getElementById('aTitle')?.focus(), 300);
    };
    document.getElementById('btnAddArticle').addEventListener('click', openAdd);
    document.getElementById('emptyAddArticle')?.addEventListener('click', openAdd);

    /* ========== 7. AKSI BARIS ========== */
    rowsEl.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });
            document.getElementById('aId').value       = item.id;
            document.getElementById('aTitle').value    = item.title;
            document.getElementById('aCategory').value = item.category;
            document.getElementById('aExcerpt').value  = item.excerpt || '';
            document.getElementById('aContent').value  = item.content || '';
            document.getElementById('articleModalTitle').textContent = 'Sunting Artikel';
            openModal(modal);
            setTimeout(() => document.getElementById('aTitle')?.focus(), 300);
        }
        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('articleDeleteText').textContent =
                'Artikel "' + item.title + '" akan dihapus permanen dari halaman publik.';
            openModal(delModal);
        }
    });

    /* ========== 8. SIMPAN ARTIKEL ========== */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('aId').value;
        const url = id ? api('articles/update/' + id) : api('articles/store');
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');
        form.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
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
                closeModal(modal);
                toast(json.message, 'success');
                createConfetti();
                load();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = form.querySelector('[data-error="' + k + '"]');
                    if (err) {
                        err.textContent = v;
                        err.closest('.field')?.classList.add('has-error');
                    }
                });
                toast('Mohon periksa kembali isian Anda.', 'error');
                form.style.animation = 'shake .4s';
                setTimeout(() => form.style.animation = '', 400);
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
            }
        } catch (err) {
            btn.classList.remove('is-loading');
            form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ========== 9. KONFETI SEDERHANA ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b'];
        for (let i = 0; i < 25; i++) {
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

    /* ========== 10. HAPUS ARTIKEL ========== */
    document.getElementById('btnConfirmArticleDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmArticleDelete');
        btn.classList.add('is-loading');
        try {
            const res  = await fetch(api('articles/delete/' + deleteId), { method: 'POST', body: fd });
            const json = await res.json();
            btn.classList.remove('is-loading');
            closeModal(delModal);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            btn.classList.remove('is-loading');
            toast('Koneksi ke server gagal.', 'error');
        }
        deleteId = null;
    });

    /* ========== 11. KEYBOARD SHORTCUTS ========== */
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + N untuk artikel baru
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            const isOnArticlesPage = document.body.dataset.base && window.location.pathname.includes('articles');
            if (isOnArticlesPage) {
                e.preventDefault();
                openAdd();
            }
        }
    });

    load();
})();