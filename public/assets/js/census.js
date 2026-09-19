// File: public/assets/js/census.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';
    const BASE  = document.body.dataset.base || '/';
    const api   = (p) => BASE + p;
    const TOKEN = window.CSRF_TOKEN || '';

    const rowsEl     = document.getElementById('censusRows');
    const emptyEl    = document.getElementById('emptyCensus');
    const infoEl     = document.getElementById('censusInfo');
    const newCountEl = document.getElementById('censusNewCount');
    const badgeEl    = document.getElementById('censusBadge');
    const refreshBtn = document.getElementById('btnCensusRefresh');

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmtTime = (d) => d ? new Date(d).toLocaleString('id-ID',
        { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

    const PURPOSE = {
        pendaftaran: { label: 'Pendaftaran', cls: 'status-active', icon: 'ph-user-plus' },
        sensus:      { label: 'Sensus',      cls: 'status-inactive', icon: 'ph-clipboard-text' },
    };

    /* ========== ANIMATED SKELETON ========== */
    function showSkeleton() {
        rowsEl.innerHTML = Array.from({ length: 5 }, (_, i) => `
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

    /* ========== LOAD DATA ========== */
    async function load(spin = false) {
        if (spin) refreshBtn.querySelector('i').classList.add('is-spinning');
        else showSkeleton();
        try {
            const res  = await fetch(api('api/census'));
            const json = await res.json();
            render(json.data || [], json.meta || {});
        } catch (e) {
            rowsEl.innerHTML = '';
            emptyEl.style.display = 'flex';
            toast('Gagal memuat data sensus.', 'error');
        } finally {
            refreshBtn.querySelector('i').classList.remove('is-spinning');
        }
    }

    /* ========== RENDER TABLE ========== */
    function render(data, meta) {
        const n = meta.new || 0;
        if (newCountEl) {
            const oldVal = parseInt(newCountEl.textContent, 10) || 0;
            animateCount(newCountEl, oldVal, n);
        }
        if (badgeEl) { 
            badgeEl.textContent = n; 
            badgeEl.style.display = n > 0 ? 'inline-block' : 'none';
            if (n > 0) badgeEl.classList.add('badge-pop');
        }
        infoEl.textContent = `${data.length} entri tercatat`;

        if (!data.length) { 
            rowsEl.innerHTML = ''; 
            emptyEl.style.display = 'flex'; 
            return; 
        }
        emptyEl.style.display = 'none';

        rowsEl.innerHTML = data.map((r, i) => {
            const p = PURPOSE[r.purpose] || PURPOSE.sensus;
            const processed = Number(r.processed) === 1;
            const initial = (r.full_name || '?').charAt(0).toUpperCase();
            return `
            <tr class="cascade-row" style="animation-delay:${i * 40}ms">
                <td>
                    <div class="cell-member">
                        <div class="skel-av" style="background:linear-gradient(135deg,var(--pri),var(--acc));display:grid;place-items:center;color:#fff;font-weight:800;font-size:14px;">
                            ${esc(initial)}
                        </div>
                        <div class="cell-member-info">
                            <strong>${esc(r.full_name)}</strong>
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
                <td class="cell-date">${fmtTime(r.created_at)}</td>
                <td>
                    <div class="row-actions">
                        ${processed
                            ? '<span class="status-pill status-inactive" style="pointer-events:none">Diproses</span>'
                            : `<button class="icon-btn has-tooltip success-btn" data-tooltip="Setujui sebagai anggota" data-act="approve" data-id="${r.id}"><i class="ph ph-check-circle"></i></button>`}
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus permanen" data-act="del" data-id="${r.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Tambahkan ripple effect ke tombol aksi
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

    /* ========== ANIMATE COUNT ========== */
    function animateCount(el, from, to) {
        const dur = 500;
        const t0 = performance.now();
        const tick = (t) => {
            const p = Math.min(1, (t - t0) / dur);
            const val = Math.round(from + (to - from) * p);
            el.textContent = val;
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /* ========== REFRESH BUTTON ========== */
    refreshBtn.addEventListener('click', () => {
        refreshBtn.style.transform = 'rotate(360deg)';
        setTimeout(() => refreshBtn.style.transform = '', 500);
        load(true);
    });

    /* ========== ROW ACTIONS ========== */
    rowsEl.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        
        const id = btn.dataset.id;
        const act = btn.dataset.act;

        if (act === 'del') {
            if (!confirm('Yakin ingin menghapus entri ini secara permanen?')) return;
        }

        const fd = new FormData();
        fd.append('csrf_token', TOKEN);
        btn.classList.add('is-loading');

        try {
            const url = act === 'approve'
                ? api('census/approve/' + id)
                : api('census/delete/' + id);
            const res  = await fetch(url, { method: 'POST', body: fd });
            const json = await res.json();
            
            if (json.ok) {
                toast(json.message, 'success');
                // Highlight row sebelum dihapus
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
                toast(json.message || 'Terjadi kesalahan.', 'error');
                btn.classList.remove('is-loading');
            }
        } catch (err) {
            toast('Koneksi ke server gagal.', 'error');
            btn.classList.remove('is-loading');
        }
    });

    /* ========== INITIAL LOAD ========== */
    load();

})();