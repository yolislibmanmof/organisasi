<?php
/**
 * ============================================================
 * HALAMAN SENSUS (ADMIN) — ULTIMATE EDITION v5.9
 * Verifikasi pendaftaran masuk dengan indikator visual,
 * skeleton loading, dan feedback interaktif.
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
        <button class="btn btn-ghost btn-sm" id="btnCensusRefresh" title="Segarkan data">
            <i class="ph ph-arrows-clockwise"></i>
            <span class="btn-text">Segarkan</span>
        </button>
    </div>
</section>

<!-- ========== SUMMARY CARDS ========== -->
<section class="census-summary">
    <div class="census-card census-card-total">
        <div class="census-card-icon">
            <i class="ph ph-users-three"></i>
        </div>
        <div class="census-card-info">
            <span class="census-card-label">Total Entri</span>
            <strong class="census-card-value" id="censusTotal">—</strong>
        </div>
    </div>
    <div class="census-card census-card-pending">
        <div class="census-card-icon">
            <i class="ph ph-clock-countdown"></i>
        </div>
        <div class="census-card-info">
            <span class="census-card-label">Menunggu Approval</span>
            <strong class="census-card-value" id="censusPending">—</strong>
        </div>
    </div>
    <div class="census-card census-card-approved">
        <div class="census-card-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="census-card-info">
            <span class="census-card-label">Sudah Diproses</span>
            <strong class="census-card-value" id="censusApproved">—</strong>
        </div>
    </div>
</section>

<!-- ========== DATA TABLE ========== -->
<section class="glass-card table-card census-table-wrap">
    <!-- Table tools -->
    <div class="table-tools">
        <div class="table-tools-left">
            <span class="table-info" id="censusInfo">Memuat data...</span>
        </div>
        <div class="table-tools-right">
            <div class="census-filter-tabs">
                <button class="census-tab active" data-filter="all">
                    <i class="ph ph-list"></i> Semua
                </button>
                <button class="census-tab" data-filter="pending">
                    <i class="ph ph-clock"></i> Pending
                </button>
                <button class="census-tab" data-filter="approved">
                    <i class="ph ph-check"></i> Diproses
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-scroll">
        <table class="data-table census-table">
            <thead>
                <tr>
                    <th style="width:22%">
                        <span class="th-label">Nama Pendaftar</span>
                    </th>
                    <th style="width:12%">
                        <span class="th-label">Keperluan</span>
                    </th>
                    <th style="width:24%">
                        <span class="th-label">Kontak</span>
                    </th>
                    <th style="width:10%">
                        <span class="th-label">Status</span>
                    </th>
                    <th style="width:8%">
                        <span class="th-label">Angkatan</span>
                    </th>
                    <th style="width:14%">
                        <span class="th-label">Waktu Daftar</span>
                    </th>
                    <th style="width:10%; text-align:right">
                        <span class="th-label">Aksi</span>
                    </th>
                </tr>
            </thead>
            <tbody id="censusRows">
                <!-- Skeleton rows (akan di-replace oleh JS) -->
                <tr class="skeleton-row">
                    <td colspan="7">
                        <div class="census-skeleton">
                            <div class="skel-row">
                                <div class="skel skel-av"></div>
                                <div class="skel-col">
                                    <div class="skel" style="height:13px;width:60%"></div>
                                    <div class="skel" style="height:10px;width:40%;margin-top:6px"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="skeleton-row"><td colspan="7"><div class="census-skeleton"><div class="skel" style="height:14px;width:80%"></div></div></td></tr>
                <tr class="skeleton-row"><td colspan="7"><div class="census-skeleton"><div class="skel" style="height:14px;width:70%"></div></div></td></tr>
                <tr class="skeleton-row"><td colspan="7"><div class="census-skeleton"><div class="skel" style="height:14px;width:85%"></div></div></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Empty state -->
    <div class="empty-rich" id="emptyCensus" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-clipboard-text"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Belum Ada Entri Sensus</h3>
        <p>Formulir sensus publik dapat diakses melalui tautan di halaman beranda atau halaman sensus publik.</p>
        <a href="<?= url('sensus') ?>" class="btn btn-ghost btn-sm" target="_blank">
            <i class="ph ph-arrow-square-out"></i>
            <span class="btn-text">Lihat Formulir Publik</span>
        </a>
    </div>
</section>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script>
    window.CSRF_TOKEN = '<?= e(csrf_token()) ?>';
</script>
<script src="<?= asset('js/census.js') ?>"></script>

<!-- ========== STYLING ========== -->
<style>
/* ---- Summary Cards ---- */
.census-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.census-card {
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
}
.census-card:nth-child(2) { animation-delay: 0.08s; }
.census-card:nth-child(3) { animation-delay: 0.16s; }

.census-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
    border-color: var(--glass-brd-2);
}

.census-card-icon {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
}
.census-card-total .census-card-icon {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}
.census-card-pending .census-card-icon {
    background: linear-gradient(135deg, #f59e0b, #f97316);
}
.census-card-approved .census-card-icon {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.census-card-info {
    flex: 1;
    min-width: 0;
}
.census-card-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-1);
    margin-bottom: 4px;
}
.census-card-value {
    display: block;
    font-size: 24px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
}

/* ---- Filter Tabs ---- */
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
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.census-tab i { font-size: 14px; }
.census-tab:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.census-tab.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}

/* ---- Table enhancements ---- */
.census-table-wrap {
    overflow: hidden;
}
.th-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* ---- Skeleton loading ---- */
.census-skeleton {
    padding: 8px 0;
}

/* ---- Row animations ---- */
#censusRows tr {
    transition: all 0.2s;
}
#censusRows tr:hover {
    background: rgba(99,102,241,.04);
}

/* ---- Responsive ---- */
@media (max-width: 720px) {
    .census-summary {
        grid-template-columns: 1fr;
    }
    .table-tools {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .census-filter-tabs {
        width: 100%;
    }
    .census-tab {
        flex: 1;
        justify-content: center;
    }
}
</style>