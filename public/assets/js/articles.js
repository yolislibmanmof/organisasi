// File: public/assets/js/articles.js (FINAL v7.0 ULTIMATE)
// Modul Manajemen Artikel: CRUD + Bulk + Filter + Export + Keyboard Shortcuts
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const API  = (path) => BASE + 'api/articles' + (path ? '?' + path : '');
    const PAGE = (path) => BASE + 'articles/' + path;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
    );

    const CAT_COLORS = {
        'Artikel': 'cat-artikel', 'Berita': 'cat-berita',
        'Edukasi': 'cat-edukasi', 'Podcast': 'cat-podcast',
        'Hari Besar': 'cat-hari-besar'
    };

    const catSlug = (c) => {
        const key = String(c || 'Artikel');
        return CAT_COLORS[key] || 'cat-' + key.toLowerCase().replace(/\s+/g, '-');
    };

    const fmtDate = (d) => d
        ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
        : '-';

    /* ============================================================
       STATE MANAGEMENT
       ============================================================ */
    const state = {
        page: 1,
        pages: 1,
        q: '',
        status: '',
        category: '',
        selected: new Set(),
        currentData: [],
        deleteId: null,
        abortController: null,
    };

    /* ============================================================
       DOM REFERENCES (dengan fallback)
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const els = {
        rows:     $('articleRows'),
        empty:    $('emptyArticles'),
        info:     $('artInfo'),
        pageInfo: $('artPageInfo'),
        pagerCur: $('artPagerCur'),
        prev:     $('artPrev'),
        next:     $('artNext'),
        search:   $('artSearch'),
        refresh:  $('btnArtRefresh'),
        modal:    $('articleModal'),
        delModal: $('articleDeleteModal'),
        form:     $('articleForm'),
        stats:    $('articleStats'),        // optional v7.0
        filters:  $('articleFilters'),      // optional v7.0
        bulkBar:  $('bulkActionBar'),       // optional v7.0
        selectAll:$('selectAllArticles'),   // optional v7.0
    };

    const csrf = () => els.form?.querySelector('input[name="csrf_token"]')?.value || '';

    /* ============================================================
       1. SKELETON LOADING (Premium Shimmer)
       ============================================================ */
    const renderSkeleton = () => {
        if (!els.rows) return;
        els.rows.innerHTML = Array.from({ length: 5 }, (_, i) => `
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
    };

    /* ============================================================
       2. API LAYER (dengan AbortController)
       ============================================================ */
    async function fetchArticles() {
        if (state.abortController) state.abortController.abort();
        state.abortController = new AbortController();

        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.status) params.set('status', state.status);
        if (state.category) params.set('category', state.category);
        params.set('page', state.page);

        const res = await fetch(API(params.toString()), {
            signal: state.abortController.signal
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    async function loadArticles(spin = false) {
        if (!els.rows) return;

        if (spin) {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.add('is-spinning');
        } else {
            renderSkeleton();
        }

        try {
            const json = await fetchArticles();
            state.currentData = json.data || [];
            state.pages = json.meta?.pages || 1;

            renderTable(state.currentData, json.meta || {});
            renderStats(json.stats || {});
            renderCategoryFilters(json.categoryCounts || {});
        } catch (err) {
            if (err.name === 'AbortError') return;
            els.rows.innerHTML = `
                <tr><td colspan="5" class="empty-state error">
                    <i class="ph ph-warning-circle"></i>
                    Gagal memuat data artikel.
                </td></tr>`;
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            const icon = els.refresh?.querySelector('i');
            if (icon) icon.classList.remove('is-spinning');
        }
    }

    /* ============================================================
       3. STATS MINI CARDS (v7.0)
       ============================================================ */
    function renderStats(stats) {
        if (!els.stats) return;
        const items = [
            { icon: 'ph-newspaper', label: 'Total', value: stats.total || 0, grad: 'grad-1' },
            { icon: 'ph-check-circle', label: 'Terbit', value: stats.published || 0, grad: 'grad-3' },
            { icon: 'ph-note-pencil', label: 'Draft', value: stats.draft || 0, grad: 'grad-4' },
            { icon: 'ph-eye', label: 'Total Views', value: stats.total_views || 0, grad: 'grad-2' },
        ];
        els.stats.innerHTML = items.map((s, i) => `
            <div class="mini-stat glass-card" style="animation-delay:${i * 60}ms">
                <div class="stat-icon ${s.grad}"><i class="ph ${s.icon}"></i></div>
                <div>
                    <strong data-count="${s.value}">${s.value.toLocaleString('id-ID')}</strong>
                    <span>${s.label}</span>
                </div>
            </div>
        `).join('');
    }

    /* ============================================================
       4. CATEGORY FILTER CHIPS (v7.0)
       ============================================================ */
    function renderCategoryFilters(counts) {
        if (!els.filters) return;
        const cats = Object.entries(counts || {});
        if (cats.length === 0) {
            els.filters.style.display = 'none';
            return;
        }
        els.filters.style.display = 'flex';
        els.filters.innerHTML = `
            <button class="chip ${!state.category ? 'chip-active' : ''}" data-cat="">
                Semua <small style="opacity:.7">(${cats.reduce((s, [, c]) => s + c, 0)})</small>
            </button>
        ` + cats.map(([cat, count]) => `
            <button class="chip ${state.category === cat ? 'chip-active' : ''}" data-cat="${esc(cat)}">
                ${esc(cat)} <small style="opacity:.7">(${count})</small>
            </button>
        `).join('');

        els.filters.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                state.category = chip.dataset.cat;
                state.page = 1;
                loadArticles();
            });
        });
    }

    /* ============================================================
       5. TABLE RENDERING (dengan Selection)
       ============================================================ */
    function renderTable(data, meta) {
        if (els.info) els.info.textContent = (meta.total || 0) + ' artikel';
        if (els.pageInfo) els.pageInfo.textContent =
            'Menampilkan ' + data.length + ' dari ' + (meta.total || 0);
        if (els.pagerCur) els.pagerCur.textContent = (meta.page || 1) + ' / ' + (meta.pages || 1);
        if (els.prev) els.prev.disabled = (meta.page || 1) <= 1;
        if (els.next) els.next.disabled = (meta.page || 1) >= (meta.pages || 1);

        // Reset selection
        state.selected.clear();
        updateBulkBar();

        if (!data.length) {
            if (els.rows) els.rows.innerHTML = '';
            if (els.empty) els.empty.style.display = 'flex';
            return;
        }
        if (els.empty) els.empty.style.display = 'none';

        els.rows.innerHTML = data.map((a, i) => {
            const initial = (a.title || '?').charAt(0).toUpperCase();
            const catClass = catSlug(a.category);
            const statusClass = a.status === 'draft' ? 'status-inactive' : 'status-active';
            const statusLabel = a.status === 'draft' ? 'Draft' : 'Terbit';
            const checked = state.selected.has(String(a.id)) ? 'checked' : '';

            return `
            <tr class="cascade-row member-row" data-id="${a.id}" style="animation-delay:${i * 40}ms">
                <td>
                    <div class="cell-member">
                        ${els.selectAll ? `
                            <label class="check-field" style="margin-right:8px">
                                <input type="checkbox" class="row-check" data-id="${a.id}" ${checked}>
                                <span class="check-mark"></span>
                            </label>
                        ` : ''}
                        <div class="cat-avatar ${catClass}">${initial}</div>
                        <div class="cell-member-info">
                            <strong>${esc(a.title)}</strong>
                            <small>${esc((a.excerpt || '').slice(0, 70))}${(a.excerpt || '').length > 70 ? '…' : ''}</small>
                        </div>
                    </div>
                </td>
                <td><span class="cat-badge ${catClass}">${esc(a.category || '-')}</span></td>
                <td>
                    <span class="author-chip">
                        <i class="ph ph-user-circle"></i>
                        @${esc(a.author_name || 'sistem')}
                    </span>
                </td>
                <td>
                    <span class="status-pill ${statusClass}">
                        <span class="status-dot"></span>
                        ${statusLabel}
                    </span>
                </td>
                <td class="cell-date">${fmtDate(a.created_at)}</td>
                <td>
                    <div class="row-actions">
                        <button class="icon-btn has-tooltip success-btn" data-tooltip="Lihat di situs" data-act="view" data-id="${a.id}">
                            <i class="ph ph-eye"></i>
                        </button>
                        <button class="icon-btn has-tooltip" data-tooltip="Sunting" data-act="edit" data-id="${a.id}">
                            <i class="ph ph-pencil-simple"></i>
                        </button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${a.id}">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        bindRowInteractions();
    }

    /* ============================================================
       6. ROW INTERACTIONS (Ripple + Actions)
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
                updateSelectAllState();
            });
        });

        // Action buttons dengan ripple
        els.rows.querySelectorAll('.icon-btn[data-act]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Ripple effect
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
                    case 'view': window.open(BASE + 'artikel/' + id, '_blank'); break;
                }
            });
        });
    }

    /* ============================================================
       7. BULK ACTIONS BAR (v7.0)
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
        if (countEl) countEl.textContent = count + ' dipilih';
    }

    function updateSelectAllState() {
        if (!els.selectAll || !els.rows) return;
        const checks = els.rows.querySelectorAll('.row-check');
        const allChecked = checks.length > 0 && [...checks].every(c => c.checked);
        const someChecked = [...checks].some(c => c.checked);
        els.selectAll.checked = allChecked;
        els.selectAll.indeterminate = someChecked && !allChecked;
    }

    // Select All toggle
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

    // Bulk action buttons
    document.querySelectorAll('[data-bulk]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.bulk;
            const ids = [...state.selected];
            if (ids.length === 0) return;

            const confirmMsg = {
                'delete': `Hapus ${ids.length} artikel terpilih secara permanen?`,
                'publish': `Terbitkan ${ids.length} artikel terpilih?`,
                'draft': `Simpan ${ids.length} artikel sebagai draft?`
            };

            if (!confirm(confirmMsg[action] || 'Lanjutkan?')) return;

            btn.classList.add('is-loading');
            try {
                const endpoint = action === 'delete' ? 'bulk-delete' : 'bulk-status';
                const body = action === 'delete'
                    ? { ids: ids.map(Number) }
                    : { ids: ids.map(Number), status: action === 'publish' ? 'published' : 'draft' };

                const res = await fetch(BASE + 'articles/' + endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ...body, csrf_token: csrf() })
                });
                const json = await res.json();
                toast(json.message || 'Selesai', json.ok ? 'success' : 'error');
                if (json.ok) loadArticles();
            } catch (err) {
                toast('Gagal melakukan aksi bulk', 'error');
            } finally {
                btn.classList.remove('is-loading');
            }
        });
    });

    /* ============================================================
       8. SEARCH & PAGINATION (dengan Debounce)
       ============================================================ */
    let searchDebounce;
    els.search?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            state.q = els.search.value.trim();
            state.page = 1;
            loadArticles();
        }, 300);
    });

    els.search?.addEventListener('focus', () =>
        els.search.parentElement?.classList.add('focused'));
    els.search?.addEventListener('blur', () =>
        els.search.parentElement?.classList.remove('focused'));

    els.prev?.addEventListener('click', () => {
        if (state.page > 1) { state.page--; loadArticles(); }
    });
    els.next?.addEventListener('click', () => {
        if (state.page < state.pages) { state.page++; loadArticles(); }
    });
    els.refresh?.addEventListener('click', () => loadArticles(true));

    /* ============================================================
       9. MODAL MANAGEMENT (dengan Keyboard Trap)
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

    // Close triggers
    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => {
            if (e.target === bd) closeModal(bd);
        }));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.show').forEach(m => closeModal(m));
        }
    });

    /* ============================================================
       10. FORM HANDLING (dengan Counter)
       ============================================================ */
    const openAddModal = () => {
        if (!els.form || !els.modal) return;
        els.form.reset();
        clearErrors();
        $('aId') && ($('aId').value = '');
        $('articleModalTitle') && ($('articleModalTitle').textContent = 'Tulis Artikel Baru');
        updateCounters();
        openModal(els.modal);
        setTimeout(() => $('aTitle')?.focus(), 300);
    };

    const openEditModal = (item) => {
        if (!els.form || !els.modal) return;
        clearErrors();
        $('aId')       && ($('aId').value = item.id);
        $('aTitle')    && ($('aTitle').value = item.title || '');
        $('aCategory') && ($('aCategory').value = item.category || 'Artikel');
        $('aStatus')   && ($('aStatus').value = item.status || 'published');
        $('aExcerpt')  && ($('aExcerpt').value = item.excerpt || '');
        $('aContent')  && ($('aContent').value = item.content || '');
        $('articleModalTitle') && ($('articleModalTitle').textContent = 'Sunting Artikel');
        updateCounters();
        openModal(els.modal);
        setTimeout(() => $('aTitle')?.focus(), 300);
    };

    const openDeleteModal = (item) => {
        if (!els.delModal) return;
        state.deleteId = item.id;
        const textEl = $('articleDeleteText');
        if (textEl) textEl.textContent =
            `Artikel "${item.title}" akan dihapus permanen dari halaman publik.`;
        openModal(els.delModal);
    };

    const clearErrors = () => {
        els.form?.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
        });
    };

    $('btnAddArticle')?.addEventListener('click', openAddModal);
    $('emptyAddArticle')?.addEventListener('click', openAddModal);

    /* ============================================================
       11. CHARACTER & WORD COUNTERS (v7.0)
       ============================================================ */
    const updateCounters = () => {
        const excerpt = $('aExcerpt');
        const content = $('aContent');
        const excerptCounter = $('excerptCounter');
        const contentCounter = $('contentCounter');

        if (excerpt && excerptCounter) {
            excerpt.addEventListener('input', () => {
                const len = excerpt.value.length;
                excerptCounter.textContent = len + '/300';
                excerptCounter.style.color = len > 300 ? 'var(--danger-2)' : 'var(--txt-2)';
            });
            excerpt.dispatchEvent(new Event('input'));
        }

        if (content && contentCounter) {
            content.addEventListener('input', () => {
                const words = content.value.trim().split(/\s+/).filter(Boolean).length;
                const readTime = Math.max(1, Math.ceil(words / 200));
                contentCounter.textContent = words + ' kata · ~' + readTime + ' menit baca';
            });
            content.dispatchEvent(new Event('input'));
        }
    };

    /* ============================================================
       12. SAVE ARTICLE (Create/Update)
       ============================================================ */
    els.form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = $('aId')?.value;
        const url = id ? PAGE('update/' + id) : PAGE('store');
        const btn = els.form.querySelector('button[type="submit"]');

        btn?.classList.add('is-loading');
        clearErrors();
        els.form.style.opacity = '0.7';

        try {
            const res = await fetch(url, { method: 'POST', body: new FormData(els.form) });
            const json = await res.json();
            btn?.classList.remove('is-loading');
            els.form.style.opacity = '1';

            if (json.ok) {
                els.form.style.borderColor = 'var(--ok)';
                setTimeout(() => { els.form.style.borderColor = ''; }, 1000);
                closeModal(els.modal);
                toast(json.message, 'success');
                launchConfetti();
                loadArticles();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = els.form.querySelector('[data-error="' + k + '"]');
                    if (err) {
                        err.textContent = v;
                        err.closest('.field')?.classList.add('has-error');
                    }
                });
                toast('Mohon periksa kembali isian Anda.', 'error');
                els.form.style.animation = 'shake .4s';
                setTimeout(() => { els.form.style.animation = ''; }, 400);
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
            }
        } catch (err) {
            btn?.classList.remove('is-loading');
            els.form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ============================================================
       13. DELETE ARTICLE
       ============================================================ */
    $('btnConfirmArticleDelete')?.addEventListener('click', async () => {
        if (!state.deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = $('btnConfirmArticleDelete');
        btn?.classList.add('is-loading');

        try {
            const res = await fetch(PAGE('delete/' + state.deleteId), {
                method: 'POST', body: fd
            });
            const json = await res.json();
            btn?.classList.remove('is-loading');
            closeModal(els.delModal);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) loadArticles();
        } catch (err) {
            btn?.classList.remove('is-loading');
            toast('Koneksi ke server gagal.', 'error');
        }
        state.deleteId = null;
    });

    /* ============================================================
       14. CONFETTI ANIMATION (Enhanced)
       ============================================================ */
    function launchConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#ec4899'];
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
       15. EXPORT CSV (v7.0)
       ============================================================ */
    $('btnExportArticles')?.addEventListener('click', () => {
        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.status) params.set('status', state.status);
        if (state.category) params.set('category', state.category);
        window.location.href = BASE + 'articles/export?' + params.toString();
        toast('Mengunduh CSV...', 'success');
    });

    /* ============================================================
       16. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + N = Artikel Baru
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            const onArticlesPage = window.location.pathname.includes('articles');
            if (onArticlesPage && !document.querySelector('.modal-backdrop.show')) {
                e.preventDefault();
                openAddModal();
            }
        }

        // Ctrl/Cmd + F = Fokus pencarian
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'f' && els.search) {
            const onArticlesPage = window.location.pathname.includes('articles');
            if (onArticlesPage && document.activeElement !== els.search) {
                e.preventDefault();
                els.search.focus();
                els.search.select();
            }
        }

        // Ctrl/Cmd + R = Refresh
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'r' && !e.shiftKey) {
            const onArticlesPage = window.location.pathname.includes('articles');
            if (onArticlesPage) {
                e.preventDefault();
                loadArticles(true);
            }
        }
    });

    /* ============================================================
       17. INITIALIZATION
       ============================================================ */
    if (els.rows) {
        loadArticles();
        console.log('%c📰 Articles Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');
    }

})();