// File: public/assets/js/census.js
(() => {
    'use strict';
    const BASE = document.body.dataset.base || '/';
    const api  = (p) => BASE + p;

    const rowsEl  = document.getElementById('censusRows');
    const emptyEl = document.getElementById('emptyCensus');
    const infoEl  = document.getElementById('censusInfo');
    const newEl   = document.getElementById('censusNewCount');
    const badge   = document.getElementById('censusBadge');
    const refresh = document.getElementById('btnCensusRefresh');

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmt = (d) => d ? new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
    let currentData = [];

    async function load(spin = false) {
        if (spin) refresh.querySelector('i').classList.add('is-spinning');
        else rowsEl.innerHTML = Array.from({ length: 4 }, () =>
            '<tr><td colspan="6"><div class="skel" style="height:18px;width:80%"></div></td></tr>').join('');
        try {
            const res  = await fetch(api('api/census'));
            const json = await res.json();
            currentData = json.data || [];
            const n = json.meta?.new || 0;
            newEl.textContent = n;
            if (badge) { badge.textContent = n; badge.style.display = n > 0 ? 'inline-block' : 'none'; }
            render();
        } finally {
            refresh.querySelector('i').classList.remove('is-spinning');
        }
    }

    function render() {
        infoEl.textContent = currentData.length + ' entri sensus';
        if (!currentData.length) { rowsEl.innerHTML = ''; emptyEl.style.display = 'flex'; return; }
        emptyEl.style.display = 'none';

        rowsEl.innerHTML = currentData.map((r, i) => `
            <tr class="cascade-row" style="animation-delay:${i * 40}ms">
                <td><strong style="font-size:13.5px">${esc(r.full_name)}</strong></td>
                <td><div class="cell-stack"><span>${esc(r.email)}</span><small>${esc(r.phone || '-')}</small></div></td>
                <td><span class="status-pill ${r.status === 'alumni' ? 'status-inactive' : 'status-active'}">${esc(r.status)}</span></td>
                <td class="cell-date">${esc(r.graduation_year || '-')}</td>
                <td class="cell-date">${fmt(r.created_at)}</td>
                <td>
                    <div class="row-actions">
                        ${Number(r.processed) === 0 ? `
                            <button class="icon-btn has-tooltip" data-tooltip="Setujui sebagai anggota" data-act="approve" data-id="${r.id}"><i class="ph ph-user-plus"></i></button>` : `
                            <span class="status-pill status-active" style="pointer-events:none"><i class="ph ph-check"></i> Diproses</span>`}
                        <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${r.id}"><i class="ph ph-trash"></i></button>
                    </div>
                </td>
            </tr>`).join('');
    }

    refresh.addEventListener('click', () => load(true));

    rowsEl.addEventListener('click', async (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const csrfToken = () => {
            const el = document.querySelector('input[name="csrf_token"]');
            return el ? el.value : '';
        };
        const fd = new FormData();
        fd.append('csrf_token', csrfToken());

        if (btn.dataset.act === 'approve') {
            btn.classList.add('is-loading');
            const res  = await fetch(api('census/approve/' + btn.dataset.id), { method: 'POST', body: fd });
            const json = await res.json();
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        }
        if (btn.dataset.act === 'del') {
            btn.classList.add('is-loading');
            const res  = await fetch(api('census/delete/' + btn.dataset.id), { method: 'POST', body: fd });
            const json = await res.json();
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        }
    });

    load();
})();