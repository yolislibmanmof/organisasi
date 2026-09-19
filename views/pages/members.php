<?php
/**
 * ============================================================
 * MANAJEMEN ANGGOTA — ULTIMATE EDITION v5.9
 * Tabel anggota dengan view toggle, filter pills, bulk actions,
 * enhanced stat cards, dan modal CRUD yang terstruktur.
 * ============================================================
 */
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Modul Keanggotaan</div>
        <h2 class="page-title">
            <i class="ph ph-users-three" style="color:var(--acc);margin-right:8px"></i>
            Data Anggota
        </h2>
        <p class="page-sub">Kelola data anggota secara real-time tanpa memuat ulang halaman.</p>
    </div>
    <div class="page-head-actions">
        <!-- Export dropdown -->
        <div class="export-dropdown-wrap">
            <button class="btn btn-ghost" id="btnExportMenu" title="Opsi ekspor">
                <i class="ph ph-download-simple"></i>
                <span class="btn-text">Ekspor</span>
                <i class="ph ph-caret-down" style="font-size:12px;margin-left:2px"></i>
            </button>
            <div class="export-dropdown" id="exportDropdown">
                <button class="export-item" id="btnExport">
                    <i class="ph ph-file-csv" style="color:#10b981"></i>
                    <div>
                        <strong>Ekspor CSV</strong>
                        <span>Format spreadsheet</span>
                    </div>
                </button>
                <button class="export-item" id="btnExportPdf">
                    <i class="ph ph-file-pdf" style="color:#ef4444"></i>
                    <div>
                        <strong>Ekspor PDF</strong>
                        <span>Laporan resmi</span>
                    </div>
                </button>
                <button class="export-item" id="btnPrint" onclick="window.print()">
                    <i class="ph ph-printer" style="color:#6366f1"></i>
                    <div>
                        <strong>Cetak</strong>
                        <span>Print langsung</span>
                    </div>
                </button>
            </div>
        </div>
        
        <button class="btn btn-primary" id="btnAdd">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tambah Anggota</span>
        </button>
    </div>
</section>

<!-- ========== ENHANCED STAT CARDS ========== -->
<section class="members-stats">
    <article class="member-stat-card glass-card stat-total">
        <div class="member-stat-icon">
            <i class="ph ph-users-three"></i>
        </div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Total Terdaftar</span>
                <span class="member-stat-trend up" id="trendTotal">
                    <i class="ph ph-trend-up"></i>
                </span>
            </div>
            <strong id="miniTotal" data-count="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barTotal" style="width:100%"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-active">
        <div class="member-stat-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Anggota Aktif</span>
                <span class="member-stat-trend up" id="trendActive">
                    <i class="ph ph-trend-up"></i>
                </span>
            </div>
            <strong id="miniActive" data-count="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barActive"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-new">
        <div class="member-stat-icon">
            <i class="ph ph-calendar-plus"></i>
        </div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Bergabung Bulan Ini</span>
                <span class="member-stat-trend neutral" id="trendNew">
                    <i class="ph ph-minus"></i>
                </span>
            </div>
            <strong id="miniNew" data-count="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barNew"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-inactive">
        <div class="member-stat-icon">
            <i class="ph ph-user-minus"></i>
        </div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Non-aktif</span>
                <span class="member-stat-trend neutral">
                    <i class="ph ph-minus"></i>
                </span>
            </div>
            <strong id="miniInactive" data-count="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barInactive"></div>
        </div>
    </article>
</section>

<!-- ========== TABLE CARD ========== -->
<section class="glass-card table-card members-table-wrap">
    
    <!-- Toolbar -->
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari nama, email, atau telepon…">
            <button class="search-clear" id="btnClearSearch" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <!-- Bulk actions (shown when items selected) -->
            <div class="bulk-actions" id="bulkActions" style="display:none">
                <span class="bulk-count"><span id="bulkCount">0</span> dipilih</span>
                <button class="btn btn-ghost btn-xs" id="bulkDelete" title="Hapus yang dipilih">
                    <i class="ph ph-trash"></i>
                </button>
                <button class="btn btn-ghost btn-xs" id="bulkActivate" title="Aktifkan yang dipilih">
                    <i class="ph ph-check-circle"></i>
                </button>
            </div>
            
            <!-- View toggle -->
            <div class="view-toggle">
                <button class="view-toggle-btn active" data-view="table" title="Tampilan tabel">
                    <i class="ph ph-table"></i>
                </button>
                <button class="view-toggle-btn" data-view="cards" title="Tampilan kartu">
                    <i class="ph ph-squares-four"></i>
                </button>
            </div>
            
            <span class="table-info" id="tableInfo">Memuat...</span>
            <button class="icon-btn" id="btnRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="members-filter-pills">
        <button class="filter-pill active" data-filter="all">
            <i class="ph ph-list"></i> Semua
            <span class="filter-pill-count" id="countAll">0</span>
        </button>
        <button class="filter-pill" data-filter="active">
            <span class="filter-pill-dot active"></span> Aktif
            <span class="filter-pill-count" id="countActive">0</span>
        </button>
        <button class="filter-pill" data-filter="inactive">
            <span class="filter-pill-dot inactive"></span> Non-aktif
            <span class="filter-pill-count" id="countInactive">0</span>
        </button>
        <button class="filter-pill" data-filter="new">
            <i class="ph ph-star" style="color:var(--warn)"></i> Baru Bulan Ini
            <span class="filter-pill-count" id="countNew">0</span>
        </button>
    </div>

    <!-- Active filter indicator -->
    <div class="filter-indicator" id="filterIndicator" style="display:none;">
        <span><i class="ph ph-funnel"></i> Filter aktif: "<span id="filterText"></span>"</span>
        <button class="filter-clear" id="btnClearFilter">Bersihkan</button>
    </div>

    <!-- Table view -->
    <div class="table-scroll" id="tableView">
        <table class="data-table members-table">
            <thead>
                <tr>
                    <th style="width:40px">
                        <label class="check-field check-header">
                            <input type="checkbox" id="selectAll">
                            <span class="check-mark"></span>
                        </label>
                    </th>
                    <th style="width:30%">Anggota</th>
                    <th style="width:25%">Kontak</th>
                    <th style="width:12%">Status</th>
                    <th style="width:15%">Bergabung</th>
                    <th style="width:18%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="memberRows">
                <!-- Skeleton rows -->
                <tr class="skeleton-row">
                    <td><div class="skel" style="width:18px;height:18px;border-radius:4px"></div></td>
                    <td>
                        <div class="skel-row">
                            <div class="skel skel-av"></div>
                            <div class="skel-col">
                                <div class="skel" style="height:13px;width:65%"></div>
                                <div class="skel" style="height:10px;width:40%;margin-top:6px"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="skel-col">
                            <div class="skel" style="height:12px;width:75%"></div>
                            <div class="skel" style="height:10px;width:45%;margin-top:6px"></div>
                        </div>
                    </td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:80px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row"><td colspan="6"><div class="skel" style="height:40px"></div></td></tr>
                <tr class="skeleton-row"><td colspan="6"><div class="skel" style="height:40px"></div></td></tr>
                <tr class="skeleton-row"><td colspan="6"><div class="skel" style="height:40px"></div></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Cards view (hidden by default) -->
    <div class="members-cards-view" id="cardsView" style="display:none">
        <div class="members-cards-grid" id="membersCardsGrid">
            <!-- Populated by JS -->
        </div>
    </div>

    <!-- Empty state -->
    <div class="empty-rich" id="emptyState" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-users-three"></i>
            <span class="empty-spark"></span>
        </div>
        <h3 id="emptyTitle">Belum Ada Data</h3>
        <p id="emptyText">Mulai bangun basis data anggota organisasi Anda dengan menambahkan entri pertama.</p>
        <button class="btn btn-primary" id="emptyAdd">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tambah Anggota Pertama</span>
        </button>
    </div>

    <!-- Pagination -->
    <div class="table-foot">
        <span id="pageInfo">Memuat...</span>
        <div class="pager">
            <button class="pager-btn icon-btn" id="prevPage" disabled aria-label="Halaman sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <span class="pager-current" id="pagerCurrent">1 / 1</span>
            <button class="pager-btn icon-btn" id="nextPage" disabled aria-label="Halaman berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== MODAL FORM ANGGOTA ========== -->
<div class="modal-backdrop" id="memberModal" role="dialog" aria-labelledby="modalTitle">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-1"><i class="ph ph-user-plus" id="modalIcon"></i></span>
                <div>
                    <h3 id="modalTitle">Tambah Anggota Baru</h3>
                    <p class="modal-sub">Isi formulir di bawah dengan data yang valid.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="memberForm">
            <?= csrf_field() ?>
            <input type="hidden" name="_id" id="fId" value="">
            <div class="modal-body">
                <!-- Section: Informasi Pribadi -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-identification-card"></i> Informasi Pribadi
                    </div>
                    <label class="field">
                        <span class="field-label">Nama Lengkap <em>*</em></span>
                        <input type="text" name="full_name" id="fName" placeholder="Contoh: Budi Santoso" required>
                        <span class="field-error" data-error="full_name"></span>
                    </label>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Username <em>*</em></span>
                            <input type="text" name="username" id="fUsername" placeholder="budi.santoso" required>
                            <span class="field-error" data-error="username"></span>
                        </label>
                        <label class="field">
                            <span class="field-label">Alamat Email <em>*</em></span>
                            <input type="email" name="email" id="fEmail" placeholder="nama@organisasi.id" required>
                            <span class="field-error" data-error="email"></span>
                        </label>
                    </div>
                </div>

                <!-- Section: Kontak & Keanggotaan -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-phone"></i> Kontak & Keanggotaan
                    </div>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">No. Telepon</span>
                            <input type="text" name="phone" id="fPhone" placeholder="08xxxxxxxxxx">
                        </label>
                        <label class="field">
                            <span class="field-label">Tanggal Bergabung <em>*</em></span>
                            <input type="date" name="join_date" id="fJoin" required>
                        </label>
                    </div>
                    <label class="field">
                        <span class="field-label">Alamat Lengkap</span>
                        <textarea name="address" id="fAddress" rows="2" placeholder="Jl. Merdeka No. 1, Kota…" maxlength="500"></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Opsional — untuk keperluan korespondensi.</small>
                            <span class="char-counter" id="addressCounter">0 / 500</span>
                        </div>
                    </label>
                </div>

                <!-- Section: Status -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-toggle-right"></i> Status Keanggotaan
                    </div>
                    <div class="status-selector">
                        <label class="status-option active" data-status="active">
                            <input type="radio" name="status" value="active" checked>
                            <span class="status-option-icon">
                                <i class="ph ph-check-circle"></i>
                            </span>
                            <div>
                                <strong>Aktif</strong>
                                <span>Anggota dapat login dan berpartisipasi</span>
                            </div>
                        </label>
                        <label class="status-option" data-status="inactive">
                            <input type="radio" name="status" value="inactive">
                            <span class="status-option-icon">
                                <i class="ph ph-x-circle"></i>
                            </span>
                            <div>
                                <strong>Non-aktif</strong>
                                <span>Anggota tidak dapat login sementara</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i>
                    <span class="btn-text">Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL KONFIRMASI HAPUS ========== -->
<div class="modal-backdrop" id="deleteModal" role="dialog" aria-labelledby="deleteTitle">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3 id="deleteTitle">Konfirmasi Penghapusan</h3>
                <p class="modal-sub">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text" id="deleteText"></p>
            <div class="danger-note">
                <i class="ph ph-warning"></i>
                <span>Akun login beserta seluruh data profil akan dihapus permanen dari sistem.</span>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus Permanen</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/members.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="<?= asset('js/pdf-export.js') ?>"></script>

<!-- ========== STYLING ========== -->
<style>
/* ---- Export Dropdown ---- */
.export-dropdown-wrap {
    position: relative;
}
.export-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 240px;
    padding: 8px;
    background: rgba(15, 21, 48, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-brd);
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(2,6,23,.5);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.25s cubic-bezier(.22,1,.36,1);
    z-index: 50;
}
.export-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.export-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    background: transparent;
    border: none;
    color: var(--txt-0);
    font: inherit;
    text-align: left;
    cursor: pointer;
    transition: all 0.2s;
}
.export-item:hover {
    background: rgba(99,102,241,.12);
}
.export-item i {
    font-size: 20px;
    flex-shrink: 0;
}
.export-item strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
}
.export-item span {
    font-size: 11px;
    color: var(--txt-2);
}

/* ---- Enhanced Stat Cards ---- */
.members-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.member-stat-card {
    position: relative;
    padding: 22px 24px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.member-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}
.member-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    transform: translate(30%, -30%);
    pointer-events: none;
    transition: transform 0.5s;
}
.member-stat-card:hover::before { transform: translate(30%, -30%) scale(1.3); }

.stat-total::before { background: radial-gradient(circle, rgba(99,102,241,.2), transparent 70%); }
.stat-active::before { background: radial-gradient(circle, rgba(16,185,129,.2), transparent 70%); }
.stat-new::before { background: radial-gradient(circle, rgba(245,158,11,.2), transparent 70%); }
.stat-inactive::before { background: radial-gradient(circle, rgba(154,163,199,.15), transparent 70%); }

.member-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    margin-bottom: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
}
.stat-total .member-stat-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-active .member-stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-new .member-stat-icon { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-inactive .member-stat-icon { background: linear-gradient(135deg, #6b7394, #9aa3c7); }

.member-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.member-stat-label {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.member-stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 99px;
}
.member-stat-trend.up {
    background: rgba(16,185,129,.12);
    color: #6ee7b7;
}
.member-stat-trend.down {
    background: rgba(239,68,68,.12);
    color: #fca5a5;
}
.member-stat-trend.neutral {
    background: rgba(154,163,199,.1);
    color: var(--txt-1);
}
.member-stat-trend i { font-size: 12px; }

.member-stat-content strong {
    display: block;
    font-size: 28px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
}

.member-stat-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(255,255,255,.05);
    overflow: hidden;
}
.member-stat-bar-fill {
    height: 100%;
    border-radius: 0 2px 2px 0;
    transition: width 0.8s cubic-bezier(.22,1,.36,1);
}
.stat-total .member-stat-bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.stat-active .member-stat-bar-fill { background: linear-gradient(90deg, #10b981, #34d399); }
.stat-new .member-stat-bar-fill { background: linear-gradient(90deg, #f59e0b, #f97316); }
.stat-inactive .member-stat-bar-fill { background: linear-gradient(90deg, #6b7394, #9aa3c7); }

/* ---- Filter Pills ---- */
.members-filter-pills {
    display: flex;
    gap: 8px;
    padding: 4px;
    margin: 16px 22px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    border-radius: 12px;
    overflow-x: auto;
}
.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.filter-pill i { font-size: 15px; }
.filter-pill:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.filter-pill.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.3);
}
.filter-pill-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.filter-pill-dot.active { background: var(--ok); }
.filter-pill-dot.inactive { background: var(--txt-2); }
.filter-pill-count {
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.filter-pill.active .filter-pill-count {
    background: rgba(255,255,255,.25);
}

/* ---- View Toggle ---- */
.view-toggle {
    display: flex;
    gap: 4px;
    padding: 3px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 8px;
}
.view-toggle-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.view-toggle-btn i { font-size: 15px; }
.view-toggle-btn:hover { color: var(--txt-0); }
.view-toggle-btn.active {
    background: var(--glass);
    color: var(--acc);
}

/* ---- Bulk Actions ---- */
.bulk-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.25);
    border-radius: 10px;
}
.bulk-count {
    font-size: 12px;
    font-weight: 700;
    color: #a5b4fc;
}

/* ---- Cards View ---- */
.members-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    padding: 22px;
}
.member-card {
    padding: 20px;
    border-radius: var(--rad-lg);
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
    animation: card-appear 0.4s both;
}
.member-card:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}
.member-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}
.member-card-avatar {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 18px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
}
.member-card-info h4 {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.member-card-info small {
    font-size: 12px;
    color: var(--txt-2);
}
.member-card-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--glass-brd);
}
.member-card-detail {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: var(--txt-1);
}
.member-card-detail i {
    font-size: 15px;
    color: var(--acc);
    width: 18px;
}
.member-card-actions {
    display: flex;
    gap: 8px;
}
.member-card-actions .btn {
    flex: 1;
    justify-content: center;
}

@keyframes card-appear {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ---- Status Selector ---- */
.status-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.status-option {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    border-radius: 14px;
    background: rgba(255,255,255,.03);
    border: 2px solid var(--glass-brd);
    cursor: pointer;
    transition: all 0.25s;
}
.status-option input { display: none; }
.status-option:hover {
    background: rgba(255,255,255,.05);
    border-color: var(--glass-brd-2);
}
.status-option.active {
    border-color: var(--ok);
    background: rgba(16,185,129,.08);
}
.status-option:has(input[value="inactive"]).active {
    border-color: var(--txt-2);
    background: rgba(154,163,199,.08);
}
.status-option-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.06);
    display: grid;
    place-items: center;
    font-size: 18px;
    color: var(--txt-1);
    flex-shrink: 0;
    transition: all 0.25s;
}
.status-option.active .status-option-icon {
    background: linear-gradient(135deg, var(--ok), #34d399);
    color: #fff;
}
.status-option:has(input[value="inactive"]).active .status-option-icon {
    background: linear-gradient(135deg, #6b7394, #9aa3c7);
}
.status-option strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 2px;
}
.status-option span {
    font-size: 11px;
    color: var(--txt-2);
}

/* ---- Form Section ---- */
.form-section {
    margin-bottom: 24px;
}
.form-section:last-child { margin-bottom: 0; }
.form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--glass-brd);
}
.form-section-title i {
    font-size: 15px;
    color: var(--acc);
}

.field-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
}
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
}
.char-counter.warn { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

/* ---- Check header ---- */
.check-header .check-mark {
    width: 18px;
    height: 18px;
}

/* ---- Row selection ---- */
#memberRows tr.selected {
    background: rgba(99,102,241,.08) !important;
}
#memberRows tr.selected td:first-child::after {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--pri);
}
#memberRows tr {
    position: relative;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .members-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .members-filter-pills {
        margin: 12px 16px;
    }
    .table-tools {
        flex-direction: column;
        gap: 12px;
    }
    .table-tools-right {
        width: 100%;
        justify-content: space-between;
    }
    .status-selector {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
/* =========================================================
   MEMBERS ULTIMATE — Enhanced Interactions
   ========================================================= */
(() => {
    'use strict';
    
    // ---- 1. Export dropdown toggle ----
    const exportMenuBtn = document.getElementById('btnExportMenu');
    const exportDropdown = document.getElementById('exportDropdown');
    
    if (exportMenuBtn && exportDropdown) {
        exportMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            exportDropdown.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!exportDropdown.contains(e.target) && !exportMenuBtn.contains(e.target)) {
                exportDropdown.classList.remove('show');
            }
        });
    }
    
    // ---- 2. View toggle ----
    const viewBtns = document.querySelectorAll('.view-toggle-btn');
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    
    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            viewBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const view = btn.dataset.view;
            if (tableView && cardsView) {
                tableView.style.display = view === 'table' ? '' : 'none';
                cardsView.style.display = view === 'cards' ? '' : 'none';
            }
        });
    });
    
    // ---- 3. Select all checkbox ----
    const selectAll = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const bulkCount = document.getElementById('bulkCount');
    
    function updateBulkActions() {
        const checked = document.querySelectorAll('#memberRows .row-check:checked').length;
        if (bulkActions && bulkCount) {
            bulkActions.style.display = checked > 0 ? 'flex' : 'none';
            bulkCount.textContent = checked;
        }
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('#memberRows .row-check').forEach(cb => {
                cb.checked = selectAll.checked;
                cb.closest('tr')?.classList.toggle('selected', selectAll.checked);
            });
            updateBulkActions();
        });
    }
    
    // Delegate row checkbox changes
    document.getElementById('memberRows')?.addEventListener('change', (e) => {
        if (e.target.classList.contains('row-check')) {
            e.target.closest('tr')?.classList.toggle('selected', e.target.checked);
            updateBulkActions();
            
            // Update select all state
            const allChecks = document.querySelectorAll('#memberRows .row-check');
            const allChecked = Array.from(allChecks).every(cb => cb.checked);
            const someChecked = Array.from(allChecks).some(cb => cb.checked);
            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
            }
        }
    });
    
    // ---- 4. Status selector ----
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.status-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    
    // ---- 5. Character counter ----
    const addressField = document.getElementById('fAddress');
    const addressCounter = document.getElementById('addressCounter');
    
    if (addressField && addressCounter) {
        addressField.addEventListener('input', () => {
            const len = addressField.value.length;
            addressCounter.textContent = `${len} / 500`;
            addressCounter.className = 'char-counter';
            if (len > 450) addressCounter.classList.add('warn');
            if (len > 480) addressCounter.classList.add('danger');
        });
    }
    
    // ---- 6. Filter pills ----
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            
            // Trigger filter in members.js
            const filter = pill.dataset.filter;
            window.dispatchEvent(new CustomEvent('filterMembers', { detail: { filter } }));
        });
    });
    
    // ---- 7. Update stat bars when data loads ----
    window.addEventListener('membersDataLoaded', (e) => {
        const { total, active, inactive, newThisMonth } = e.detail;
        
        const barTotal = document.getElementById('barTotal');
        const barActive = document.getElementById('barActive');
        const barNew = document.getElementById('barNew');
        const barInactive = document.getElementById('barInactive');
        
        if (barTotal) barTotal.style.width = '100%';
        if (barActive && total > 0) barActive.style.width = ((active / total) * 100) + '%';
        if (barNew && total > 0) barNew.style.width = ((newThisMonth / total) * 100) + '%';
        if (barInactive && total > 0) barInactive.style.width = ((inactive / total) * 100) + '%';
        
        // Update filter counts
        const countAll = document.getElementById('countAll');
        const countActive = document.getElementById('countActive');
        const countInactive = document.getElementById('countInactive');
        const countNew = document.getElementById('countNew');
        
        if (countAll) countAll.textContent = total;
        if (countActive) countActive.textContent = active;
        if (countInactive) countInactive.textContent = inactive;
        if (countNew) countNew.textContent = newThisMonth;
    });
})();
</script>