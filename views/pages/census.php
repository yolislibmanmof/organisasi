<?php
/**
 * ============================================================
 * HALAMAN SENSUS (ADMIN) — ULTIMATE EDITION v7.0
 * Verifikasi pendaftaran: summary live, filter tab aktif,
 * pencarian, ekspor CSV, skeleton kaya, dan aksesibilitas.
 * Seluruh logika tambahan self-contained (tidak mengubah census.js).
 * ============================================================
 */
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Verifikasi & Approval</div>
        <h2 class="page-title">
            <i class="ph ph-clipboard-text" style="color:var(--acc);margin-right:8px"></i>
            Sensus Anggota
        </h2>
        <p class="page-sub">Tinjau pendaftaran masuk dan setujui sebagai akun anggota aktif.</p>
    </div>
    <div class="page-head-actions">
        <span class="live-indicator">
            <span class="live-dot"></span>
            <span id="censusNewCount" data-count="0">0</span> entri baru
        </span>
        <button class="btn btn-ghost btn-sm" id="btnCensusExport" title="Unduh rekap CSV">
            <i class="ph ph-download-simple"></i>
            <span class="btn-text">Ekspor CSV</span>
        </button>
        <button class="btn btn-ghost btn-sm" id="btnCensusRefresh" title="Segarkan data">
            <i class="ph ph-arrows-clockwise"></i>
            <span class="btn-text">Segarkan</span>
        </button>
    </div>
</section>

<!-- ========== SUMMARY CARDS (LIVE) ========== -->
<section class="census-summary">
    <div class="census-card census-card-total">
        <div class="census-card-icon"><i class="ph ph-users-three"></i></div>
        <div class="census-card-info">
            <span class="census-card-label">Total Entri</span>
            <strong class="census-card-value" id="censusTotal" data-v="0">0</strong>
            <span class="census-card-sub">Seluruh pendaftar masuk</span>
        </div>
        <span class="census-card-trend"><i class="ph ph-database"></i></span>
    </div>
    <div class="census-card census-card-pending">
        <div class="census-card-icon"><i class="ph ph-clock-countdown"></i></div>
        <div class="census-card-info">
            <span class="census-card-label">Menunggu Approval</span>
            <strong class="census-card-value" id="censusPending" data-v="0">0</strong>
            <span class="census-card-sub">Perlu tindakan Anda</span>
        </div>
        <span class="census-card-trend trend-warn"><i class="ph ph-hourglass-medium"></i></span>
    </div>
    <div class="census-card census-card-approved">
        <div class="census-card-icon"><i class="ph ph-check-circle"></i></div>
        <div class="census-card-info">
            <span class="census-card-label">Sudah Diproses</span>
            <strong class="census-card-value" id="censusApproved" data-v="0">0</strong>
            <span class="census-card-sub">Telah disetujui / ditolak</span>
        </div>
        <span class="census-card-trend trend-ok"><i class="ph ph-check"></i></span>
    </div>
</section>

<!-- ========== DATA TABLE ========== -->
<section class="glass-card table-card census-table-wrap">
    <!-- Table tools -->
    <div class="table-tools">
        <div class="table-tools-left">
            <span class="table-info" id="censusInfo" aria-live="polite">Memuat data...</span>
            <div class="purpose-legend" aria-hidden="true">
                <span class="legend-pill legend-daftar"><i class="ph ph-user-plus"></i> Pendaftaran</span>
                <span class="legend-pill legend-sensus"><i class="ph ph-clipboard-text"></i> Sensus</span>
            </div>
        </div>
        <div class="table-tools-right">
            <div class="census-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="search" id="censusSearch" placeholder="Cari nama pendaftar..." aria-label="Cari pendaftar" autocomplete="off">
            </div>
            <div class="census-filter-tabs" role="tablist" aria-label="Filter status entri">
                <button class="census-tab active" data-filter="all" role="tab" aria-selected="true">
                    <i class="ph ph-list"></i> Semua <span class="census-tab-count" id="tabCountAll">0</span>
                </button>
                <button class="census-tab" data-filter="pending" role="tab" aria-selected="false">
                    <i class="ph ph-clock"></i> Pending <span class="census-tab-count" id="tabCountPending">0</span>
                </button>
                <button class="census-tab" data-filter="approved" role="tab" aria-selected="false">
                    <i class="ph ph-check"></i> Diproses <span class="census-tab-count" id="tabCountApproved">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-scroll">
        <table class="data-table census-table">
            <caption class="visually-hidden">Daftar entri sensus anggota</caption>
            <thead>
                <tr>
                    <th style="width:22%"><span class="th-label">Nama Pendaftar</span></th>
                    <th style="width:12%"><span class="th-label">Keperluan</span></th>
                    <th style="width:24%"><span class="th-label">Kontak</span></th>
                    <th style="width:10%"><span class="th-label">Status</span></th>
                    <th style="width:8%"><span class="th-label">Angkatan</span></th>
                    <th style="width:14%"><span class="th-label">Waktu Daftar</span></th>
                    <th style="width:10%; text-align:right"><span class="th-label">Aksi</span></th>
                </tr>
            </thead>
            <tbody id="censusRows">
                <!-- Skeleton kaya (akan di-replace oleh census.js) -->
                <tr class="skeleton-row">
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:70%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel" style="height:22px;width:80px;border-radius:99px"></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:80%"></div><div class="skel" style="height:10px;width:50%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:12px;width:60px"></div></td>
                    <td><div class="skel" style="height:12px;width:40px"></div></td>
                    <td><div class="skel" style="height:12px;width:90px"></div></td>
                    <td><div class="skel" style="height:28px;width:70px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row">
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:60%"></div><div class="skel" style="height:10px;width:40%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:70%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:12px;width:55px"></div></td>
                    <td><div class="skel" style="height:12px;width:40px"></div></td>
                    <td><div class="skel" style="height:12px;width:85px"></div></td>
                    <td><div class="skel" style="height:28px;width:70px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row">
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:65%"></div><div class="skel" style="height:10px;width:42%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel" style="height:22px;width:75px;border-radius:99px"></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:75%"></div><div class="skel" style="height:10px;width:48%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:12px;width:58px"></div></td>
                    <td><div class="skel" style="height:12px;width:40px"></div></td>
                    <td><div class="skel" style="height:12px;width:88px"></div></td>
                    <td><div class="skel" style="height:28px;width:70px;margin-left:auto"></div></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Empty state (belum ada entri sama sekali) -->
    <div class="empty-rich" id="emptyCensus" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-clipboard-text"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Belum Ada Entri Sensus</h3>
        <p>Formulir sensus publik dapat diakses melalui tautan di halaman beranda atau halaman sensus publik.</p>
        <a href="<?= url('sensus') ?>" class="btn btn-ghost btn-sm" target="_blank" rel="noopener">
            <i class="ph ph-arrow-square-out"></i>
            <span class="btn-text">Lihat Formulir Publik</span>
        </a>
    </div>

    <!-- No-match state (filter/search tidak menemukan hasil) -->
    <div class="empty-rich census-nomatch" id="censusNoMatch" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-magnifying-glass"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Tidak Ada Hasil</h3>
        <p>Tidak ada entri yang cocok dengan filter atau kata kunci saat ini.</p>
        <button class="btn btn-ghost btn-sm" id="btnCensusReset">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <span class="btn-text">Reset Filter</span>
        </button>
    </div>
</section>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script>
    window.CSRF_TOKEN = '<?= e(csrf_token()) ?>';
</script>
<script src="<?= asset('js/census.js') ?>"></script>

<!-- ========== ENHANCER (self-contained, tidak mengubah census.js) ========== -->
<script>
(function(){
    'use strict';

    const tbody   = document.getElementById('censusRows');
    const info    = document.getElementById('censusInfo');
    const noMatch = document.getElementById('censusNoMatch');
    const emptyEl = document.getElementById('emptyCensus');
    const search  = document.getElementById('censusSearch');
    const tabs    = Array.from(document.querySelectorAll('.census-tab'));
    const tabCount = {
        all:      document.getElementById('tabCountAll'),
        pending:  document.getElementById('tabCountPending'),
        approved: document.getElementById('tabCountApproved')
    };
    const cards = {
        total:    document.getElementById('censusTotal'),
        pending:  document.getElementById('censusPending'),
        approved: document.getElementById('censusApproved')
    };

    let filter = 'all';
    let term   = '';

    const isSkeleton = tr => tr.classList.contains('skeleton-row');
    const isPending  = tr => !!tr.querySelector('button[data-act="approve"]');
    const rowName    = tr => (tr.querySelector('td strong')?.textContent || '').toLowerCase();

    /* ---- Count-up animation ---- */
    function animate(el, to){
        if (!el) return;
        const from = parseInt(el.dataset.v || '0', 10);
        el.dataset.v = to;
        if (from === to) { el.textContent = to.toLocaleString('id-ID'); return; }
        const t0 = performance.now(), dur = 600;
        const tick = t => {
            const p = Math.min(1, (t - t0) / dur);
            const v = Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3)));
            el.textContent = v.toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /* ---- Hitung total / pending / approved dari baris nyata ---- */
    function counts(){
        let t = 0, p = 0, a = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            if (isSkeleton(tr)) return;
            t++;
            if (isPending(tr)) p++; else a++;
        });
        return { t, p, a };
    }

    /* ---- Terapkan filter + search + update semua indikator ---- */
    function apply(){
        const c = counts();
        animate(cards.total, c.t);
        animate(cards.pending, c.p);
        animate(cards.approved, c.a);
        if (tabCount.all)      tabCount.all.textContent = c.t;
        if (tabCount.pending)  tabCount.pending.textContent = c.p;
        if (tabCount.approved) tabCount.approved.textContent = c.a;

        let visible = 0;
        tbody.querySelectorAll('tr').forEach(tr => {
            if (isSkeleton(tr)) { tr.style.display = ''; return; }
            const okFilter = filter === 'all' || (filter === 'pending' ? isPending(tr) : !isPending(tr));
            const okTerm   = !term || rowName(tr).includes(term);
            const show = okFilter && okTerm;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (info) info.textContent = visible + ' dari ' + c.t + ' entri ditampilkan';
        const hasRows = c.t > 0;
        if (noMatch) noMatch.style.display = (hasRows && visible === 0) ? 'flex' : 'none';
        if (emptyEl && hasRows) emptyEl.style.display = 'none';
    }

    /* ---- Tab filter ---- */
    tabs.forEach(b => b.addEventListener('click', () => {
        tabs.forEach(x => {
            const on = (x === b);
            x.classList.toggle('active', on);
            x.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        filter = b.dataset.filter;
        apply();
    }));

    /* ---- Search ---- */
    search?.addEventListener('input', () => {
        term = search.value.trim().toLowerCase();
        apply();
    });

    /* ---- Reset filter ---- */
    document.getElementById('btnCensusReset')?.addEventListener('click', () => {
        filter = 'all'; term = '';
        if (search) search.value = '';
        tabs.forEach(x => {
            const on = x.dataset.filter === 'all';
            x.classList.toggle('active', on);
            x.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        apply();
    });

    /* ---- Ekspor CSV dari baris yang terlihat ---- */
    document.getElementById('btnCensusExport')?.addEventListener('click', () => {
        const rows = [['Nama','Keperluan','Kontak','Status','Angkatan','Waktu Daftar']];
        tbody.querySelectorAll('tr').forEach(tr => {
            if (isSkeleton(tr) || tr.style.display === 'none') return;
            const cells = Array.from(tr.querySelectorAll('td'))
                .slice(0, 6)
                .map(td => td.innerText.replace(/\s+/g, ' ').trim());
            if (cells.length) rows.push(cells);
        });
        if (rows.length <= 1) {
            if (window.toast) window.toast('Tidak ada data untuk diekspor.', 'error');
            return;
        }
        const csv = rows.map(r => r.map(v => '"' + String(v ?? '').replace(/"/g, '""') + '"').join(',')).join('\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'sensus-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
        URL.revokeObjectURL(url);
        if (window.toast) window.toast('Rekap CSV berhasil diunduh.', 'success');
    });

    /* ---- Re-apply otomatis setiap kali census.js me-render ulang ---- */
    new MutationObserver(() => apply()).observe(tbody, { childList: true });

    apply();
})();
</script>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Summary Cards (live) ---- */
.census-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.census-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    animation: fade-up 0.5s both;
    overflow: hidden;
}
.census-card:nth-child(2) { animation-delay: 0.08s; }
.census-card:nth-child(3) { animation-delay: 0.16s; }
.census-card::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--card-glow, rgba(99,102,241,.18)), transparent 70%);
    pointer-events: none;
    transition: transform .4s;
}
.census-card:hover::after { transform: scale(1.35); }
.census-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
    border-color: var(--glass-brd-2);
}
.census-card-total    { --card-glow: rgba(99,102,241,.22); }
.census-card-pending  { --card-glow: rgba(245,158,11,.22); }
.census-card-approved { --card-glow: rgba(16,185,129,.22); }

.census-card-icon {
    width: 48px; height: 48px; flex-shrink: 0;
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 22px; color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
    transition: transform .3s;
}
.census-card:hover .census-card-icon { transform: scale(1.08) rotate(-5deg); }
.census-card-total .census-card-icon    { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.census-card-pending .census-card-icon  { background: linear-gradient(135deg, #f59e0b, #f97316); }
.census-card-approved .census-card-icon { background: linear-gradient(135deg, #10b981, #34d399); }

.census-card-info { flex: 1; min-width: 0; }
.census-card-label {
    display: block;
    font-size: 11.5px; font-weight: 600;
    color: var(--txt-1); margin-bottom: 4px;
}
.census-card-value {
    display: block;
    font-size: 26px; font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.census-card-sub {
    display: block;
    font-size: 10.5px; color: var(--txt-2);
    margin-top: 3px;
}
.census-card-trend {
    position: absolute;
    top: 14px; right: 14px;
    width: 28px; height: 28px;
    border-radius: 9px;
    display: grid; place-items: center;
    font-size: 14px;
    color: var(--txt-2);
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
}
.census-card-trend.trend-warn { color: #fbbf24; background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.25); }
.census-card-trend.trend-ok   { color: #6ee7b7; background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.25); }

/* ---- Purpose legend ---- */
.purpose-legend {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}
.legend-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px;
    border-radius: 99px;
    font-size: 10.5px; font-weight: 700;
}
.legend-pill i { font-size: 12px; }
.legend-daftar { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #6ee7b7; }
.legend-sensus { background: rgba(154,163,199,.1); border: 1px solid rgba(154,163,199,.3); color: var(--txt-1); }

/* ---- Search ---- */
.census-search {
    position: relative;
    width: 220px;
}
.census-search i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--txt-2); font-size: 14px; pointer-events: none;
}
.census-search input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    font: inherit; font-size: 12.5px;
    color: var(--txt-0);
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 9px;
    outline: none;
    transition: all .2s;
}
.census-search input:focus {
    border-color: var(--pri);
    background: rgba(99,102,241,.06);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.census-search input::placeholder { color: var(--txt-2); }

/* ---- Filter Tabs (dengan count) ---- */
.census-filter-tabs {
    display: flex;
    gap: 6px;
    padding: 4px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.census-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12px; font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.census-tab i { font-size: 14px; }
.census-tab:hover { color: var(--txt-0); background: rgba(255,255,255,.05); }
.census-tab.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.census-tab-count {
    min-width: 20px;
    padding: 1px 6px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px; font-weight: 800;
    text-align: center;
    font-variant-numeric: tabular-nums;
}
.census-tab.active .census-tab-count { background: rgba(255,255,255,.25); }

/* ---- Table enhancements ---- */
.census-table-wrap { overflow: hidden; }
.th-label { display: inline-flex; align-items: center; gap: 4px; }
.visually-hidden {
    position: absolute; width: 1px; height: 1px;
    margin: -1px; padding: 0; overflow: hidden;
    clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}

/* Row hover dengan accent bar */
#censusRows tr { transition: all 0.2s; }
#censusRows tr:hover { background: rgba(99,102,241,.04); }
#censusRows tr:hover td:first-child { box-shadow: inset 3px 0 0 var(--acc); }

/* ---- Skeleton ---- */
.census-skeleton { padding: 8px 0; }

/* ---- No-match state ---- */
.census-nomatch { padding: 40px 24px; }
.census-nomatch .empty-illustration { width: 80px; height: 80px; }
.census-nomatch .empty-illustration i { font-size: 34px; }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .table-tools { flex-direction: column; align-items: stretch; gap: 12px; }
    .table-tools-left { flex-direction: column; align-items: flex-start; gap: 8px; }
    .table-tools-right { flex-direction: column; align-items: stretch; gap: 10px; }
    .census-search { width: 100%; }
    .census-filter-tabs { width: 100%; }
    .census-tab { flex: 1; justify-content: center; }
}
@media (max-width: 720px) {
    .census-summary { grid-template-columns: 1fr; }
    .census-card-trend { display: none; }
    .purpose-legend { flex-wrap: wrap; }
}
</style>