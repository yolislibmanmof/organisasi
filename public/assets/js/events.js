// File: public/assets/js/events.js (FINAL v7.0 ULTIMATE)
// Modul Manajemen Event: Timeline + Filter + Stats + Cover + Multi-Day
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const csrf = () =>
        document.getElementById('eventForm')?.querySelector('input[name="csrf_token"]')?.value ||
        document.querySelector('input[name="csrf_token"]')?.value || '';

    const STATUS = {
        upcoming: { label: 'Akan Datang',  cls: 'upcoming', icon: 'ph-calendar-plus' },
        ongoing:  { label: 'Berlangsung', cls: 'ongoing',  icon: 'ph-broadcast'     },
        done:     { label: 'Selesai',     cls: 'done',     icon: 'ph-check-circle'  },
    };

    const daysUntil = (dateStr) => {
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const d = new Date(dateStr + 'T00:00:00');
        return Math.round((d - today) / 86400000);
    };

    const countdownLabel = (dateStr) => {
        const n = daysUntil(dateStr);
        if (n === 0) return 'Hari ini';
        if (n === 1) return 'Besok';
        if (n > 1)   return 'dalam ' + n + ' hari';
        return Math.abs(n) + ' hari lalu';
    };

    /* ============================================================
       STATE & DOM
       ============================================================ */
    const state = { page: 1, pages: 1, q: '', status: '' };
    let currentData = [];
    let deleteId = null;
    let abortCtrl = null;

    const $ = (id) => document.getElementById(id);
    const timeline = $('eventTimeline');
    const emptyEl  = $('emptyEvents');
    const infoEl   = $('eventInfo');
    const pageInfo = $('eventPageInfo');
    const pagerCur = $('eventPagerCur');
    const prevBtn  = $('eventPrev');
    const nextBtn  = $('eventNext');
    const searchEl = $('eventSearch');
    const refresh  = $('btnEventRefresh');
    const modal    = $('eventModal');
    const delModal = $('eventDeleteModal');
    const form     = $('eventForm');
    const filtEl   = $('eventFilters');

    if (!timeline) return; // Bukan halaman events

    /* ============================================================
       1. SKELETON LOADING
       ============================================================ */
    function renderSkeleton() {
        timeline.innerHTML = Array.from({ length: 4 }, (_, i) => `
            <div class="timeline-item cascade-row" style="animation-delay:${i * 60}ms">
                <div class="timeline-node skeleton-node">
                    <div class="skel" style="width:30px;height:20px"></div>
                    <div class="skel" style="width:40px;height:10px;margin-top:4px"></div>
                </div>
                <div class="timeline-card glass-card" style="padding:18px 20px">
                    <div style="display:flex;justify-content:space-between;margin-bottom:10px">
                        <div class="skel" style="width:60%;height:16px"></div>
                        <div class="skel" style="width:80px;height:22px;border-radius:99px"></div>
                    </div>
                    <div class="skel" style="width:90%;height:12px;margin-bottom:8px"></div>
                    <div class="skel" style="width:70%;height:12px;margin-bottom:12px"></div>
                    <div style="display:flex;gap:14px">
                        <div class="skel" style="width:80px;height:12px"></div>
                        <div class="skel" style="width:100px;height:12px"></div>
                    </div>
                </div>
            </div>`).join('');
    }

    /* ============================================================
       2. LOAD DATA (AbortController)
       ============================================================ */
    async function load(spin = false) {
        const icon = refresh?.querySelector('i');
        if (spin && icon) {
            icon.classList.add('is-spinning');
            if (refresh) refresh.style.transform = 'rotate(360deg)';
        } else {
            renderSkeleton();
        }

        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();

        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.status) params.set('status', state.status);
        params.set('page', state.page);

        try {
            const res  = await fetch(api('api/events?' + params.toString()), { signal: abortCtrl.signal });
            const json = await res.json();
            currentData = json.data || [];
            state.pages = json.meta?.pages || 1;
            render(currentData, json.meta || {});
        } catch (e) {
            if (e.name === 'AbortError') return;
            timeline.innerHTML = '<p class="empty-state error"><i class="ph ph-warning-circle"></i> Gagal memuat data event.</p>';
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            if (icon) icon.classList.remove('is-spinning');
            if (refresh) refresh.style.transform = '';
        }
    }

    /* ============================================================
       3. ANIMATE NUMBER
       ============================================================ */
    function animateNum(el, target) {
        if (!el || target === undefined || target === null) return;
        target = parseInt(target, 10) || 0;
        // Strip non-digits agar bisa parse "1,000" dengan benar
        const start = parseInt(String(el.textContent).replace(/[^\d]/g, ''), 10) || 0;
        if (start === target) { el.textContent = target.toLocaleString('id-ID'); return; }
        const t0 = performance.now(), dur = 700;
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4);
            const val = Math.floor(start + (target - start) * eased);
            el.textContent = val.toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = target.toLocaleString('id-ID');
        };
        requestAnimationFrame(tick);
    }

    /* ============================================================
       4. FILTER CHIPS STATUS
       ============================================================ */
    function renderFilters(meta) {
        if (!filtEl) return;
        const opts = [
            { val: '',         label: 'Semua',       count: meta.total    },
            { val: 'upcoming', label: 'Akan Datang', count: meta.upcoming },
            { val: 'ongoing',  label: 'Berlangsung', count: meta.ongoing  },
            { val: 'done',     label: 'Selesai',     count: meta.done     },
        ];
        filtEl.innerHTML = opts.map(o => `
            <button class="chip ${state.status === o.val ? 'chip-active' : ''}" data-status="${o.val}">
                ${o.label}${o.count !== undefined ? ` <small style="opacity:.7">(${o.count})</small>` : ''}
            </button>
        `).join('');

        filtEl.querySelectorAll('.chip').forEach(c => {
            c.addEventListener('click', () => {
                state.status = c.dataset.status;
                state.page = 1;
                load();
            });
        });
    }

    /* ============================================================
       5. RENDER TIMELINE (dengan Pemisah Bulan + Countdown)
       ============================================================ */
    function render(data, meta) {
        if (infoEl)   infoEl.textContent   = (meta.total || 0) + ' event tercatat';
        if (pageInfo) pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + (meta.total || 0);
        if (pagerCur) pagerCur.textContent = (meta.page || 1) + ' / ' + (meta.pages || 1);
        if (prevBtn)  prevBtn.disabled = (meta.page || 1) <= 1;
        if (nextBtn)  nextBtn.disabled = (meta.page || 1) >= (meta.pages || 1);

        animateNum($('miniUpcoming'), meta.upcoming);
        animateNum($('miniOngoing'),  meta.ongoing);
        animateNum($('miniDone'),     meta.done);
        animateNum($('miniTotal'),    meta.total);

        const badge = $('eventBadge');
        if (badge) {
            const oldVal = parseInt(badge.textContent, 10) || 0;
            animateNum(badge, meta.upcoming || 0);
            badge.style.display = (meta.upcoming || 0) > 0 ? 'inline-block' : 'none';
            if ((meta.upcoming || 0) > oldVal) {
                badge.classList.remove('badge-pop');
                void badge.offsetWidth;
                badge.classList.add('badge-pop');
            }
        }

        renderFilters(meta);

        if (!data.length) {
            timeline.innerHTML = '';
            if (emptyEl) {
                emptyEl.style.display = 'flex';
                const t = $('emptyEventTitle'), x = $('emptyEventText');
                if (t) t.textContent = state.q ? 'Tidak Ada Hasil' : 'Belum Ada Event';
                if (x) x.textContent = state.q
                    ? 'Tidak ada event yang cocok dengan pencarian "' + state.q + '".'
                    : 'Jadwalkan kegiatan pertama organisasi Anda dan pantau linimasanya di sini.';
            }
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';

        let lastMonth = '';
        let html = '';

        data.forEach((ev, i) => {
            const d    = new Date(ev.event_date + 'T00:00:00');
            const day  = String(d.getDate()).padStart(2, '0');
            const mon  = d.toLocaleDateString('id-ID', { month: 'short' });
            const st   = STATUS[ev.status] || STATUS.done;
            const time = ev.event_time ? ev.event_time.slice(0, 5) + ' WIB' : 'Waktu menyusul';

            // Pemisah bulan
            const monthKey = d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
            if (monthKey !== lastMonth) {
                lastMonth = monthKey;
                html += `
                <div style="display:flex;align-items:center;gap:14px;margin:${i === 0 ? '0' : '28px'} 0 16px;
                            color:var(--txt-2);font-size:11px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase">
                    <span style="flex:1;height:1px;background:var(--glass-brd)"></span>
                    <span><i class="ph ph-calendar-dots" style="margin-right:6px;color:var(--acc)"></i>${monthKey}</span>
                    <span style="flex:1;height:1px;background:var(--glass-brd)"></span>
                </div>`;
            }

            // Rentang tanggal (multi-day)
            const endMeta = ev.end_date
                ? `<span><i class="ph ph-calendar-dots"></i> s/d ${new Date(ev.end_date + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>`
                : '';

            // Countdown untuk upcoming
            const cdMeta = ev.status === 'upcoming'
                ? `<span style="color:#67e8f9"><i class="ph ph-hourglass"></i> ${countdownLabel(ev.event_date)}</span>`
                : '';

            // Cover image (v7.0)
            const cover = ev.cover_image
                ? `<div style="margin:-18px -20px 14px;height:130px;overflow:hidden;border-radius:14px 14px 0 0">
                       <img src="${BASE}assets/uploads/events/${esc(ev.cover_image)}" alt="" loading="lazy"
                            style="width:100%;height:100%;object-fit:cover;transition:transform .5s"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''">
                   </div>`
                : '';

            html += `
            <div class="timeline-item cascade-row" style="animation-delay:${i * 60}ms">
                <div class="timeline-node ${ev.status}">
                    <span class="timeline-day">${day}</span>
                    <span class="timeline-month">${mon}</span>
                </div>
                <article class="timeline-card glass-card">
                    ${cover}
                    <div class="event-head">
                        <h4>${esc(ev.title)}</h4>
                        <span class="event-pill ${st.cls}">
                            <i class="ph ${st.icon}"></i> ${st.label}
                        </span>
                    </div>
                    ${ev.description ? `<p class="event-desc">${esc(ev.description)}</p>` : ''}
                    <div class="event-meta">
                        <span><i class="ph ph-clock"></i> ${esc(time)}</span>
                        <span><i class="ph ph-map-pin"></i> ${esc(ev.location || 'Lokasi menyusul')}</span>
                        ${endMeta}
                        ${cdMeta}
                        ${ev.creator_name ? `<span><i class="ph ph-user-circle"></i> oleh @${esc(ev.creator_name)}</span>` : ''}
                    </div>
                    <div class="row-actions event-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah event" data-act="edit" data-id="${ev.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus event" data-act="del" data-id="${ev.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </article>
            </div>`;
        });

        timeline.innerHTML = html;
        bindCardEffects();
    }

    /* ============================================================
       6. CARD EFFECTS (3D Tilt + Ripple)
       ============================================================ */
    function bindCardEffects() {
        timeline.querySelectorAll('.timeline-card').forEach(card => {
            let raf = null;
            card.addEventListener('mousemove', function(e) {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(() => {
                    const rect = this.getBoundingClientRect();
                    const rotateX = (e.clientY - rect.top - rect.height / 2) / 30;
                    const rotateY = (rect.width / 2 - (e.clientX - rect.left)) / 30;
                    this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(6px)`;
                });
            });
            card.addEventListener('mouseleave', function() {
                if (raf) cancelAnimationFrame(raf);
                this.style.transform = '';
            });
        });

        timeline.querySelectorAll('.icon-btn').forEach(btn => {
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
    }

    /* ============================================================
       7. PENCARIAN & PAGINASI
       ============================================================ */
    let debounce;
    searchEl?.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            state.q = searchEl.value.trim();
            state.page = 1;
            load();
        }, 300);
    });
    searchEl?.addEventListener('focus', () => searchEl.parentElement?.classList.add('focused'));
    searchEl?.addEventListener('blur',  () => searchEl.parentElement?.classList.remove('focused'));

    prevBtn?.addEventListener('click', () => { if (state.page > 1) { state.page--; load(); } });
    nextBtn?.addEventListener('click', () => { if (state.page < state.pages) { state.page++; load(); } });
    refresh?.addEventListener('click', () => load(true));

    /* ============================================================
       8. MODAL HANDLING
       ============================================================ */
    const openModal  = (m) => { if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; } };
    const closeModal = (m) => { if (m) { m.classList.remove('show'); document.body.style.overflow = ''; } };

    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd); }));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.show').forEach(m => closeModal(m));
    });

    const openAdd = () => {
        form?.reset();
        clearErrors();
        if ($('eId')) $('eId').value = '';
        if ($('eDate')) $('eDate').value = new Date().toISOString().slice(0, 10);
        if ($('eventModalTitle')) $('eventModalTitle').textContent = 'Buat Event Baru';
        resetCoverPreview();
        updateDescCounter();  // ← TAMBAHKAN BARIS INI
        openModal(modal);
        setTimeout(() => $('eTitle')?.focus(), 300);
    };
    $('btnAddEvent')?.addEventListener('click', openAdd);
    $('emptyAddEvent')?.addEventListener('click', openAdd);

    function clearErrors() {
        form?.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
        });
    }

    /* ============================================================
       9. COVER UPLOAD PREVIEW
       ============================================================ */
    function resetCoverPreview() {
        const p = $('eCoverPreview');
        if (p && p.dataset.initial) p.innerHTML = p.dataset.initial;
    }

    const coverInput = $('eCover'), coverPreview = $('eCoverPreview');
    if (coverPreview && !coverPreview.dataset.initial) coverPreview.dataset.initial = coverPreview.innerHTML;

    coverInput?.addEventListener('change', () => {
        const file = coverInput.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            toast('Cover harus berupa gambar.', 'error');
            coverInput.value = '';
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            toast('Ukuran cover maksimal 10 MB.', 'error');
            coverInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (ev) => {
            coverPreview.style.transition = 'opacity .3s';
            coverPreview.style.opacity = '0';
            setTimeout(() => {
                coverPreview.innerHTML = `<img src="${ev.target.result}" alt="Preview" style="width:100%;height:100%;object-fit:cover;border-radius:12px">`;
                coverPreview.style.opacity = '1';
            }, 150);
        };
        reader.readAsDataURL(file);
    });

    /* ============================================================
       10. VALIDASI KLIEN (End Date >= Start Date)
       ============================================================ */
    function validateClient() {
        const start = $('eDate')?.value;
        const end   = $('eEndDate')?.value;
        if (start && end && end < start) {
            const err = form?.querySelector('[data-error="end_date"]');
            if (err) {
                err.textContent = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
                err.closest('.field')?.classList.add('has-error');
            }
            toast('Tanggal selesai tidak valid.', 'error');
            return false;
        }
        return true;
    }

    /* ============================================================
       11. AKSI BARIS (Edit / Delete)
       ============================================================ */
    timeline.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            clearErrors();
            if ($('eId'))       $('eId').value       = item.id;
            if ($('eTitle'))    $('eTitle').value    = item.title || '';
            if ($('eDate'))     $('eDate').value     = item.event_date || '';
            if ($('eEndDate'))  $('eEndDate').value  = item.end_date || '';
            if ($('eTime'))     $('eTime').value     = item.event_time || '';
            if ($('eLocation')) $('eLocation').value = item.location || '';
            if ($('eDesc'))     $('eDesc').value     = item.description || '';
            if ($('eventModalTitle')) $('eventModalTitle').textContent = 'Ubah Event';
            resetCoverPreview();
            updateDescCounter();
            openModal(modal);
            setTimeout(() => $('eTitle')?.focus(), 300);
        }

        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            const t = $('eventDeleteText');
            if (t) t.textContent = 'Event "' + item.title + '" akan dihapus permanen dari linimasa.';
            openModal(delModal);
        }
    });

    /* ============================================================
       12. DESCRIPTION COUNTER
       ============================================================ */
    function updateDescCounter() {
        const desc = $('eDesc'), counter = $('eDescCounter'), bar = $('eDescBar');
        if (!desc || !counter) return;
        const max = 1000;  // Sesuai maxlength di HTML
        const len = desc.value.length;
        const pct = Math.min(100, (len / max) * 100);
        counter.textContent = len + ' / ' + max;
        if (bar) bar.style.width = pct + '%';

        counter.className = 'char-counter';
        if (bar) bar.className = 'char-progress-fill';
        if (len > max * 0.85) { counter.classList.add('warn'); if (bar) bar.classList.add('warn'); }
        if (len > max * 0.95) { counter.classList.add('danger'); if (bar) bar.classList.add('danger'); }
    }
    $('eDesc')?.addEventListener('input', updateDescCounter);

    /* ============================================================
       13. SIMPAN EVENT
       ============================================================ */
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        if (!validateClient()) return;

        const id  = $('eId')?.value;
        const url = id ? api('events/update/' + id) : api('events/store');
        const btn = form.querySelector('button[type="submit"]');
        btn?.classList.add('is-loading');
        form.style.opacity = '0.7';

        try {
            const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            btn?.classList.remove('is-loading');
            form.style.opacity = '1';

            if (json.ok) {
                form.style.borderColor = 'var(--ok)';
                setTimeout(() => { form.style.borderColor = ''; }, 1000);
                closeModal(modal);
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
                toast('Mohon periksa kembali formulir.', 'error');
                form.style.animation = 'shake .4s';
                setTimeout(() => { form.style.animation = ''; }, 400);
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
            }
        } catch (err) {
            btn?.classList.remove('is-loading');
            form.style.opacity = '1';
            toast('Koneksi ke server gagal.', 'error');
        }
    });

    /* ============================================================
       14. HAPUS EVENT
       ============================================================ */
    $('btnConfirmEventDelete')?.addEventListener('click', async function() {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        this.classList.add('is-loading');
        try {
            const res  = await fetch(api('events/delete/' + deleteId), { method: 'POST', body: fd });
            const json = await res.json();
            this.classList.remove('is-loading');
            closeModal(delModal);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            this.classList.remove('is-loading');
            toast('Koneksi ke server gagal.', 'error');
        }
        deleteId = null;
    });

    /* ============================================================
       15. CONFETTI
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
       16. KEYBOARD SHORTCUTS
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if (!window.location.pathname.includes('events')) return;
        if (document.querySelector('.modal-backdrop.show')) return;

        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            e.preventDefault();
            openAdd();
        }
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'f' && searchEl) {
            e.preventDefault();
            searchEl.focus();
            searchEl.select();
        }
    });

    /* ============================================================
       17. INITIALIZATION
       ============================================================ */
    load();
    console.log('%c📅 Events Module v7.0 Loaded', 'color: #22d3ee; font-weight: bold;');

})();