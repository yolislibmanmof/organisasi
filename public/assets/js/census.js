// File: public/assets/js/census.js (FINAL - TAHAP 5.7)
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
        pendaftaran: { label: 'Pendaftaran', cls: 'status-active' },
        sensus:      { label: 'Sensus',      cls: 'status-inactive' },
    };

    async function load(spin = false) {
        if (spin) refreshBtn.querySelector('i').classList.add('is-spinning');
        else rowsEl.innerHTML = Array.from({ length: 4 }, () =>
            '<tr><td colspan="7"><div class="skel" style="height:18px;width:80%"></div></td></tr>').join('');
        try {
            const res  = await fetch(api('api/census'));
            const json = await res.json();
            render(json.data || [], json.meta || {});
        } catch (e) {
            rowsEl.innerHTML = '';
            emptyEl.style.display = 'flex';
        } finally {
            refreshBtn.querySelector('i').classList.remove('is-spinning');
        }
    }

    function render(data, meta) {
        const n = meta.new || 0;
        if (newCountEl) newCountEl.textContent = n;
        if (badgeEl) { badgeEl.textContent = n; badgeEl.style.display = n > 0 ? 'inline-block' : 'none'; }
        infoEl.textContent = data.length + ' entri tercatat';

        if (!data.length) { rowsEl.innerHTML = ''; emptyEl.style.display = 'flex'; return; }
        emptyEl.style.display = 'none';

        rowsEl.innerHTML = data.map((r, i) => {
            const p = PURPOSE[r.purpose] || PURPOSE.sensus;
            const processed = Number(r.processed) === 1;
            return `
            <tr class="cascade-row" style="animation-delay:${i * 40}ms">
                <td><strong style="font-size:13.5px">${esc(r.full_name)}</strong></td>
                <td><span class="status-pill ${p.cls}">${p.label}</span></td>
                <td><div class="cell-stack"><span>${esc(r.email)}</span><small>${esc(r.phone || '-')}</small></div></td>
                <td>${esc(r.status || '-')}</td>
                <td>${esc(r.graduation_year || '-')}</td>
                <td class="cell-date">${fmtTime(r.created_at)}</td>
                <td>
                    <div class="row-actions">
                        ${processed
                            ? '<span class="status-pill status-inactive" style="pointer-events:none">Diproses</span>'
                            : `<button class="icon-btn has-tooltip" data-tooltip="Setujui" data-act="approve" data-id="${r.id}"><i class="ph ph-check-circle"></i></button>`}
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${r.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    refreshBtn.addEventListener('click', () => load(true));

    rowsEl.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const id = btn.dataset.id;
        const fd = new FormData();
        fd.append('csrf_token', TOKEN);
        btn.classList.add('is-loading');
        try {
            const url = btn.dataset.act === 'approve'
                ? api('census/approve/' + id)
                : api('census/delete/' + id);
            const res  = await fetch(url, { method: 'POST', body: fd });
            const json = await res.json();
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            toast('Koneksi ke server gagal.', 'error');
        } finally {
            btn.classList.remove('is-loading');
        }
    });

    load();
})();