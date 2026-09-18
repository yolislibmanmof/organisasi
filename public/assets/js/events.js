// File: public/assets/js/events.js
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
        upcoming: { label: 'Akan Datang',  cls: 'upcoming' },
        ongoing:  { label: 'Berlangsung', cls: 'ongoing'  },
        done:     { label: 'Selesai',     cls: 'done'     }
    };

    async function load(spin = false) {
        if (spin) refresh.querySelector('i').classList.add('is-spinning');
        else renderSkeleton();
        try {
            const res  = await fetch(api('api/events?q=' + encodeURIComponent(state.q) + '&page=' + state.page));
            const json = await res.json();
            currentData = json.data;
            state.pages = json.meta.pages;
            render(json.data, json.meta);
        } catch (e) {
            timeline.innerHTML = '<p class="empty-state error">Gagal memuat data event.</p>';
        } finally {
            refresh.querySelector('i').classList.remove('is-spinning');
        }
    }

    function animateNum(el, target) {
        const start = parseInt(el.textContent, 10) || 0;
        if (start === target) return;
        const t0 = performance.now(), dur = 700;
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            el.textContent = Math.floor(start + (target - start) * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(tick); else el.textContent = target;
        };
        requestAnimationFrame(tick);
    }

    function renderSkeleton() {
        timeline.innerHTML = Array.from({ length: 3 }, () => `
            <div class="timeline-item">
                <div class="skel" style="width:62px;height:62px;border-radius:18px;flex-shrink:0"></div>
                <div class="skel" style="flex:1;height:96px;border-radius:16px"></div>
            </div>`).join('');
    }

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
            badge.textContent = meta.upcoming;
            badge.style.display = meta.upcoming > 0 ? 'inline-block' : 'none';
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
                        <span class="event-pill ${st.cls}">${st.label}</span>
                    </div>
                    ${ev.description ? `<p class="event-desc">${esc(ev.description)}</p>` : ''}
                    <div class="event-meta">
                        <span><i class="ph ph-clock"></i> ${esc(time)}</span>
                        <span><i class="ph ph-map-pin"></i> ${esc(ev.location || 'Lokasi menyusul')}</span>
                        ${ev.creator_name ? `<span><i class="ph ph-user-circle"></i> oleh @${esc(ev.creator_name)}</span>` : ''}
                    </div>
                    <div class="row-actions event-actions">
                        <button class="icon-btn has-tooltip" data-tooltip="Ubah" data-act="edit" data-id="${ev.id}"><i class="ph ph-pencil-simple"></i></button>
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${ev.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </article>
            </div>`;
        }).join('');
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
        document.getElementById('eId').value = '';
        document.getElementById('eDate').value = new Date().toISOString().slice(0, 10);
        document.getElementById('eventModalTitle').textContent = 'Buat Event Baru';
        openModal(modal);
    };
    document.getElementById('btnAddEvent').addEventListener('click', openAdd);
    document.getElementById('emptyAddEvent').addEventListener('click', openAdd);

    /* ---------- Aksi baris ---------- */
    timeline.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.getElementById('eId').value       = item.id;
            document.getElementById('eTitle').value    = item.title;
            document.getElementById('eDate').value     = item.event_date;
            document.getElementById('eTime').value     = item.event_time || '';
            document.getElementById('eLocation').value = item.location || '';
            document.getElementById('eDesc').value     = item.description || '';
            document.getElementById('eventModalTitle').textContent = 'Ubah Event';
            openModal(modal);
        }
        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            document.getElementById('eventDeleteText').textContent =
                'Event "' + item.title + '" akan dihapus permanen dari linimasa.';
            openModal(delModal);
        }
    });

    /* ---------- Simpan ---------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id  = document.getElementById('eId').value;
        const url = id ? api('events/update/' + id) : api('events/store');
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
            toast('Mohon periksa kembali formulir.', 'error');
        } else {
            toast(json.message || 'Gagal menyimpan.', 'error');
        }
    });

    /* ---------- Hapus ---------- */
    document.getElementById('btnConfirmEventDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', csrf());
        const btn = document.getElementById('btnConfirmEventDelete');
        btn.classList.add('is-loading');
        const res  = await fetch(api('events/delete/' + deleteId), { method: 'POST', body: fd });
        const json = await res.json();
        btn.classList.remove('is-loading');
        closeModal(delModal);
        toast(json.message, json.ok ? 'success' : 'error');
        if (json.ok) load();
        deleteId = null;
    });

    load();
})();