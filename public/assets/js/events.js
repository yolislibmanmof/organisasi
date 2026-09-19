// File: public/assets/js/events.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    const timeline = document.getElementById('eventTimeline');
    const emptyEl  = document.getElementById('emptyEvents');
    const infoEl   = document.getElementById('eventInfo');
    const pageInfo = document.getElementById('eventPageInfo');
    const pagerCur = document.getElementById('eventPagerCur');
    const prevBtn  = document.getElementById('eventPrev');
    const nextBtn  = document.getElementById('eventNext');
    const searchEl = document.getElementById('eventSearch');
    const refresh  = document.getElementById('btnEventRefresh');
    const modal    = document.getElementById('eventModal');
    const delModal = document.getElementById('eventDeleteModal');
    const form     = document.getElementById('eventForm');

    const state = { page: 1, pages: 1, q: '' };
    let currentData = [];
    let deleteId = null;

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const csrf = () => form.querySelector('input[name="csrf_token"]').value;

    const STATUS = {
        upcoming: { label: 'Akan Datang',  cls: 'upcoming', icon: 'ph-calendar-plus' },
        ongoing:  { label: 'Berlangsung', cls: 'ongoing', icon: 'ph-broadcast' },
        done:     { label: 'Selesai',     cls: 'done', icon: 'ph-check-circle' }
    };

    /* ========== 1. LOAD DATA ========== */
    async function load(spin = false) {
        if (spin) {
            refresh.querySelector('i').classList.add('is-spinning');
            refresh.style.transform = 'rotate(360deg)';
        } else {
            renderSkeleton();
        }
        try {
            const res  = await fetch(api('api/events?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            render(json.data, json.meta);
        } catch (e) {
            timeline.innerHTML = '<p class="empty-state error"><i class="ph ph-warning-circle"></i> Gagal memuat data event.</p>';
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            refresh.querySelector('i').classList.remove('is-spinning');
            refresh.style.transform = '';
        }
    }

    /* ========== 2. ANIMATE NUMBER ========== */
    function animateNum(el, target) {
        if (!el) return;
        const start = parseInt(el.textContent, 10) || 0;
        if (start === target) return;
        const t0 = performance.now(), dur = 700;
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const eased = 1 - Math.pow(1 - p, 4);
            el.textContent = Math.floor(start + (target - start) * eased);
            if (p < 1) requestAnimationFrame(tick); else el.textContent = target;
        };
        requestAnimationFrame(tick);
    }

    /* ========== 3. SKELETON LOADING ========== */
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

    /* ========== 4. RENDER TIMELINE ========== */
    function render(data, meta) {
        infoEl.textContent   = meta.total + ' event tercatat';
        pageInfo.textContent = 'Menampilkan ' + data.length + ' dari ' + meta.total;
        pagerCur.textContent = meta.page + ' / ' + meta.pages;
        prevBtn.disabled = meta.page <= 1;
        nextBtn.disabled = meta.page >= meta.pages;

        animateNum(document.getElementById('miniUpcoming'), meta.upcoming);
        animateNum(document.getElementById('miniOngoing'),  meta.ongoing);
        animateNum(document.getElementById('miniDone'),     meta.done);

        const badge = document.getElementById('eventBadge');
        if (badge) {
            const oldVal = parseInt(badge.textContent, 10) || 0;
            animateNum(badge, meta.upcoming);
            badge.style.display = meta.upcoming > 0 ? 'inline-block' : 'none';
            if (meta.upcoming > oldVal) badge.classList.add('badge-pop');
        }

        if (!data.length) {
            timeline.innerHTML = '';
            emptyEl.style.display = 'flex';
            document.getElementById('emptyEventTitle').textContent = state.q ? 'Tidak Ada Hasil' : 'Belum Ada Event';
            document.getElementById('emptyEventText').textContent  = state.q
                ? 'Tidak ada event yang cocok dengan pencarian "' + state.q + '".'
                : 'Jadwalkan kegiatan pertama organisasi Anda dan pantau linimasanya di sini.';
            return;
        }
        emptyEl.style.display = 'none';

        timeline.innerHTML = data.map((ev, i) => {
            const d    = new Date(ev.event_date + 'T00:00:00');
            const day  = String(d.getDate()).padStart(2, '0');
            const mon  = d.toLocaleDateString('id-ID', { month: 'short' });
            const st   = STATUS[ev.status] || STATUS.done;
            const time = ev.event_time ? ev.event_time.slice(0, 5) + ' WIB' : 'Waktu menyusul';
            return `
            <div class="timeline-item cascade-row" style="animation-delay:${i * 60}ms">
                <div class="timeline-node ${ev.status}">
                    <span class="timeline-day">${day}</span>
                    <span class="timeline-month">${mon}</span>
                </div>
                <article class="timeline-card glass-card">
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
                        ${ev.creator_name ? `<span><i class="ph ph-user-circle"></i> oleh @${esc(ev.creator_name)}</span>` : ''}
                    </div>
                    <div class="row-actions event-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah event" data-act="edit" data-id="${ev.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus event" data-act="del" data-id="${ev.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </article>
            </div>`;
        }).join('');

        // Bind efek-efek pada timeline cards
        bindCardEffects();
    }

    /* ========== 5. EFEK 3D TILT & RIPPLE PADA CARDS ========== */
    function bindCardEffects() {
        // 3D Tilt pada timeline cards
        timeline.querySelectorAll('.timeline-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 30;
                const rotateY = (centerX - x) / 30;
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateX(6px)`;
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Ripple effect pada tombol aksi
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

    /* ========== 6. PENCARIAN & PAGINASI ========== */
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

    /* ========== 7. MODAL HANDLING ========== */
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
        document.getElementById('eId').value = '';
        document.getElementById('eDate').value = new Date().toISOString().slice(0, 10);
        document.getElementById('eventModalTitle').textContent = 'Buat Event Baru';
        openModal(modal);
        setTimeout(() => document.getElementById('eTitle')?.focus(), 300);
    };
    document.getElementById('btnAddEvent').addEventListener('click', openAdd);
    document.getElementById('emptyAddEvent')?.addEventListener('click', openAdd);

    /* ========== 8. AKSI BARIS ========== */
    timeline.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });
            document.getElementById('eId').value       = item.id;
            document.getElementById('eTitle').value    = item.title;
            document.getElementById('eDate').value     = item.event_date;
            document.getElementById('eTime').value     = item.event_time || '';
            document.getElementById('eLocation').value = item.location || '';
            document.getElementById('eDesc').value     = item.description || '';
            document.getElementById('eventModalTitle').textContent = 'Ubah Event';
            openModal(modal);
            setTimeout(() => document.getElementById('eTitle')?.focus(), 300);
        }
        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('eventDeleteText').textContent =
                'Event "' + item.title + '" akan dihapus permanen dari linimasa.';
            openModal(delModal);
        }
    });

    /* ========== 9. SIMPAN EVENT ========== */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('eId').value;
        const url = id ? api('events/update/' + id) : api('events/store');
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
                toast('Mohon periksa kembali formulir.', 'error');
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

    /* ========== 10. KONFETI ========== */
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

    /* ========== 11. HAPUS EVENT ========== */
    document.getElementById('btnConfirmEventDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmEventDelete');
        btn.classList.add('is-loading');
        try {
            const res  = await fetch(api('events/delete/' + deleteId), { method: 'POST', body: fd });
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

    /* ========== 12. KEYBOARD SHORTCUTS ========== */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'n' && !e.shiftKey) {
            const isOnEventsPage = window.location.pathname.includes('events');
            if (isOnEventsPage) {
                e.preventDefault();
                openAdd();
            }
        }
    });

    load();
})();