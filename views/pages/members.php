<?php
/**
 * ============================================================
 * MANAJEMEN ANGGOTA — ULTIMATE EDITION v7.0
 * Tabel anggota dengan stat animasi, filter pills live,
 * bulk actions, view toggle, dan self-contained enhancer
 * yang sinkron dengan members.js via MutationObserver.
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
        <span class="last-updated" id="membersLastUpdated" title="Waktu sinkronisasi terakhir">
            <i class="ph ph-clock"></i>
            <span>Baru saja</span>
        </span>
        <!-- Export dropdown -->
        <div class="export-dropdown-wrap">
            <button class="btn btn-ghost" id="btnExportMenu" title="Opsi ekspor" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-download-simple"></i>
                <span class="btn-text">Ekspor</span>
                <i class="ph ph-caret-down" style="font-size:12px;margin-left:2px"></i>
            </button>
            <div class="export-dropdown" id="exportDropdown" role="menu">
                <button class="export-item" id="btnExport" role="menuitem">
                    <i class="ph ph-file-csv" style="color:#10b981"></i>
                    <div>
                        <strong>Ekspor CSV</strong>
                        <span>Format spreadsheet</span>
                    </div>
                </button>
                <button class="export-item" id="btnExportPdf" role="menuitem">
                    <i class="ph ph-file-pdf" style="color:#ef4444"></i>
                    <div>
                        <strong>Ekspor PDF</strong>
                        <span>Laporan resmi</span>
                    </div>
                </button>
                <button class="export-item" id="btnPrint" onclick="window.print()" role="menuitem">
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
        <div class="member-stat-glow"></div>
        <div class="member-stat-icon"><i class="ph ph-users-three"></i></div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Total Terdaftar</span>
                <span class="member-stat-trend up" id="trendTotal"><i class="ph ph-trend-up"></i></span>
            </div>
            <strong id="miniTotal" data-count="0" data-v="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barTotal" style="width:100%"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-active">
        <div class="member-stat-glow"></div>
        <div class="member-stat-icon"><i class="ph ph-check-circle"></i></div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Anggota Aktif</span>
                <span class="member-stat-trend up" id="trendActive"><i class="ph ph-trend-up"></i></span>
            </div>
            <strong id="miniActive" data-count="0" data-v="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barActive"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-new">
        <div class="member-stat-glow"></div>
        <div class="member-stat-icon"><i class="ph ph-calendar-plus"></i></div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Bergabung Bulan Ini</span>
                <span class="member-stat-trend neutral" id="trendNew"><i class="ph ph-minus"></i></span>
            </div>
            <strong id="miniNew" data-count="0" data-v="0">0</strong>
        </div>
        <div class="member-stat-bar">
            <div class="member-stat-bar-fill" id="barNew"></div>
        </div>
    </article>
    
    <article class="member-stat-card glass-card stat-inactive">
        <div class="member-stat-glow"></div>
        <div class="member-stat-icon"><i class="ph ph-user-minus"></i></div>
        <div class="member-stat-content">
            <div class="member-stat-header">
                <span class="member-stat-label">Non-aktif</span>
                <span class="member-stat-trend neutral"><i class="ph ph-minus"></i></span>
            </div>
            <strong id="miniInactive" data-count="0" data-v="0">0</strong>
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
            <input type="text" id="searchInput" placeholder="Cari nama, email, atau telepon…" aria-label="Cari anggota">
            <button class="search-clear" id="btnClearSearch" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <!-- Bulk actions -->
            <div class="bulk-actions" id="bulkActions" style="display:none" role="toolbar" aria-label="Aksi massal">
                <span class="bulk-count"><span id="bulkCount">0</span> dipilih</span>
                <button class="btn btn-ghost btn-xs" id="bulkDelete" title="Hapus yang dipilih">
                    <i class="ph ph-trash"></i>
                </button>
                <button class="btn btn-ghost btn-xs" id="bulkActivate" title="Aktifkan yang dipilih">
                    <i class="ph ph-check-circle"></i>
                </button>
            </div>
            
            <!-- View toggle -->
            <div class="view-toggle" role="tablist" aria-label="Mode tampilan">
                <button class="view-toggle-btn active" data-view="table" title="Tampilan tabel" role="tab" aria-selected="true">
                    <i class="ph ph-table"></i>
                </button>
                <button class="view-toggle-btn" data-view="cards" title="Tampilan kartu" role="tab" aria-selected="false">
                    <i class="ph ph-squares-four"></i>
                </button>
            </div>
            
            <span class="table-info" id="tableInfo" aria-live="polite">Memuat...</span>
            <button class="icon-btn" id="btnRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="members-filter-pills" role="tablist" aria-label="Filter anggota">
        <button class="filter-pill active" data-filter="all" role="tab" aria-selected="true">
            <i class="ph ph-list"></i> Semua
            <span class="filter-pill-count" id="countAll">0</span>
        </button>
        <button class="filter-pill" data-filter="active" role="tab" aria-selected="false">
            <span class="filter-pill-dot active"></span> Aktif
            <span class="filter-pill-count" id="countActive">0</span>
        </button>
        <button class="filter-pill" data-filter="inactive" role="tab" aria-selected="false">
            <span class="filter-pill-dot inactive"></span> Non-aktif
            <span class="filter-pill-count" id="countInactive">0</span>
        </button>
        <button class="filter-pill" data-filter="new" role="tab" aria-selected="false">
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
            <caption class="visually-hidden">Daftar anggota organisasi</caption>
            <thead>
                <tr>
                    <th style="width:40px">
                        <label class="check-field check-header">
                            <input type="checkbox" id="selectAll" aria-label="Pilih semua">
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
                <!-- Skeleton rows (kaya) -->
                <tr class="skeleton-row skel-var-1">
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
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:75%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:80px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-2">
                    <td><div class="skel" style="width:18px;height:18px;border-radius:4px"></div></td>
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:58%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:70%"></div><div class="skel" style="height:10px;width:50%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:75px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:85px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-1">
                    <td><div class="skel" style="width:18px;height:18px;border-radius:4px"></div></td>
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:72%"></div><div class="skel" style="height:10px;width:48%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:80%"></div><div class="skel" style="height:10px;width:42%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:68px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:78px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-2">
                    <td><div class="skel" style="width:18px;height:18px;border-radius:4px"></div></td>
                    <td><div class="skel-row"><div class="skel skel-av"></div><div class="skel-col"><div class="skel" style="height:13px;width:62%"></div><div class="skel" style="height:10px;width:44%;margin-top:6px"></div></div></div></td>
                    <td><div class="skel-col"><div class="skel" style="height:12px;width:72%"></div><div class="skel" style="height:10px;width:48%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:72px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:82px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cards view -->
    <div class="members-cards-view" id="cardsView" style="display:none">
        <div class="members-cards-grid" id="membersCardsGrid"></div>
    </div>

    <!-- Empty state (kaya) -->
    <div class="empty-rich" id="emptyState" style="display:none;">
        <div class="empty-illustration">
            <div class="empty-orb empty-orb-1"></div>
            <div class="empty-orb empty-orb-2"></div>
            <i class="ph ph-users-three"></i>
            <span class="empty-spark"></span>
        </div>
        <h3 id="emptyTitle">Belum Ada Data</h3>
        <p id="emptyText">Mulai bangun basis data anggota organisasi Anda dengan menambahkan entri pertama.</p>
        <div class="empty-features">
            <span><i class="ph ph-check-circle"></i> CRUD lengkap</span>
            <span><i class="ph ph-check-circle"></i> Ekspor CSV/PDF</span>
            <span><i class="ph ph-check-circle"></i> Akun terintegrasi</span>
        </div>
        <button class="btn btn-primary" id="emptyAdd">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tambah Anggota Pertama</span>
        </button>
    </div>

    <!-- No-match state -->
    <div class="empty-rich members-nomatch" id="membersNoMatch" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-magnifying-glass"></i>
        </div>
        <h3>Tidak Ada Hasil</h3>
        <p>Tidak ada anggota yang cocok dengan filter atau kata kunci saat ini.</p>
        <button class="btn btn-ghost btn-sm" id="btnResetFilter">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <span class="btn-text">Reset Filter</span>
        </button>
    </div>

    <!-- Pagination (dengan First/Last) -->
    <div class="table-foot">
        <span id="pageInfo">Memuat...</span>
        <div class="pager">
            <button class="pager-btn icon-btn" id="firstPage" disabled aria-label="Halaman pertama">
                <i class="ph ph-caret-double-left"></i>
            </button>
            <button class="pager-btn icon-btn" id="prevPage" disabled aria-label="Halaman sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <span class="pager-current" id="pagerCurrent">1 / 1</span>
            <button class="pager-btn icon-btn" id="nextPage" disabled aria-label="Halaman berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
            <button class="pager-btn icon-btn" id="lastPage" disabled aria-label="Halaman terakhir">
                <i class="ph ph-caret-double-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== MODAL FORM ANGGOTA ========== -->
<div class="modal-backdrop" id="memberModal" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
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
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="addressCounter">0 / 500</span>
                                <div class="char-progress">
                                    <div class="char-progress-fill" id="addressBar" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-toggle-right"></i> Status Keanggotaan
                    </div>
                    <div class="status-selector">
                        <label class="status-option active" data-status="active">
                            <input type="radio" name="status" value="active" checked>
                            <span class="status-option-icon"><i class="ph ph-check-circle"></i></span>
                            <div>
                                <strong>Aktif</strong>
                                <span>Anggota dapat login dan berpartisipasi</span>
                            </div>
                        </label>
                        <label class="status-option" data-status="inactive">
                            <input type="radio" name="status" value="inactive">
                            <span class="status-option-icon"><i class="ph ph-x-circle"></i></span>
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

<!-- ========== MODAL KONFIRMASI HAPUS (ENHANCED) ========== -->
<div class="modal-backdrop" id="deleteModal" role="dialog" aria-labelledby="deleteTitle" aria-modal="true">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            </div>
            <div>
                <h3 id="deleteTitle">Konfirmasi Penghapusan</h3>
                <p class="modal-sub">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="delete-item-preview" id="deleteItemPreview">
                <i class="ph ph-user-circle"></i>
                <span id="deleteText">—</span>
            </div>
            <div class="danger-note">
                <div class="danger-note-icon"><i class="ph ph-warning"></i></div>
                <div class="danger-note-content">
                    <strong>Peringatan</strong>
                    <p>Akun login beserta seluruh data profil akan dihapus permanen dari sistem.</p>
                </div>
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

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdNew"><kbd>N</kbd> Tambah Anggota</span>
    <span class="kbd-item" id="kbdRefresh"><kbd>R</kbd> Segarkan</span>
    <span class="kbd-item" id="kbdSearch"><kbd>/</kbd> Cari</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup Modal</span>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/members.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= asset('js/pdf-export.js') ?>"></script>

<!-- ========== ENHANCER v7.0 (self-contained) ========== -->
<script>
(function(){
    'use strict';

    /* ---- Helper: Animate counter ---- */
    function animate(el, to) {
        if (!el) return;
        const from = parseInt(el.dataset.v || '0', 10) || 0;
        el.dataset.v = to;
        if (from === to) { el.textContent = to.toLocaleString('id-ID'); return; }
        const t0 = performance.now(), dur = 700;
        const tick = t => {
            const p = Math.min(1, (t - t0) / dur);
            const v = Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3)));
            el.textContent = v.toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /* ---- 1. Export dropdown dengan click-outside yang robust ---- */
    const exportMenuBtn = document.getElementById('btnExportMenu');
    const exportDropdown = document.getElementById('exportDropdown');
    if (exportMenuBtn && exportDropdown) {
        exportMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const show = !exportDropdown.classList.contains('show');
            exportDropdown.classList.toggle('show', show);
            exportMenuBtn.setAttribute('aria-expanded', show);
        });
        document.addEventListener('click', (e) => {
            if (!exportDropdown.contains(e.target) && !exportMenuBtn.contains(e.target)) {
                exportDropdown.classList.remove('show');
                exportMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && exportDropdown.classList.contains('show')) {
                exportDropdown.classList.remove('show');
                exportMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---- 2. View toggle ---- */
    const viewBtns = document.querySelectorAll('.view-toggle-btn');
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            viewBtns.forEach(b => {
                const on = (b === btn);
                b.classList.toggle('active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            const view = btn.dataset.view;
            if (tableView && cardsView) {
                tableView.style.display = view === 'table' ? '' : 'none';
                cardsView.style.display = view === 'cards' ? '' : 'none';
            }
        });
    });

    /* ---- 3. Select all + bulk actions ---- */
    const selectAll = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const bulkCount = document.getElementById('bulkCount');
    const rowsBody = document.getElementById('memberRows');

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
    rowsBody?.addEventListener('change', (e) => {
        if (e.target.classList.contains('row-check')) {
            e.target.closest('tr')?.classList.toggle('selected', e.target.checked);
            updateBulkActions();
            const allChecks = document.querySelectorAll('#memberRows .row-check');
            const allChecked = Array.from(allChecks).every(cb => cb.checked);
            const someChecked = Array.from(allChecks).some(cb => cb.checked);
            if (selectAll) {
                selectAll.checked = allChecked;
                selectAll.indeterminate = someChecked && !allChecked;
            }
        }
    });

    /* ---- 4. Status selector ---- */
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.status-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    /* ---- 5. Character counter dengan progress bar ---- */
    const addressField = document.getElementById('fAddress');
    const addressCounter = document.getElementById('addressCounter');
    const addressBar = document.getElementById('addressBar');
    if (addressField && addressCounter && addressBar) {
        addressField.addEventListener('input', () => {
            const len = addressField.value.length;
            const max = 500;
            const pct = Math.min(100, (len / max) * 100);
            addressCounter.textContent = len + ' / ' + max;
            addressBar.style.width = pct + '%';
            addressCounter.className = 'char-counter';
            addressBar.className = 'char-progress-fill';
            if (len > max * 0.85) { addressCounter.classList.add('warn'); addressBar.classList.add('warn'); }
            if (len > max * 0.95) { addressCounter.classList.add('danger'); addressBar.classList.add('danger'); }
        });
    }

    /* ---- 6. Filter pills + indicator ---- */
    const filterPills = document.querySelectorAll('.filter-pill');
    const filterIndicator = document.getElementById('filterIndicator');
    const filterText = document.getElementById('filterText');

    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            filterPills.forEach(p => {
                const on = (p === pill);
                p.classList.toggle('active', on);
                p.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            const filter = pill.dataset.filter;
            if (filterIndicator && filterText) {
                if (filter === 'all') {
                    filterIndicator.style.display = 'none';
                } else {
                    filterText.textContent = pill.textContent.trim().replace(/\d+/g, '').trim();
                    filterIndicator.style.display = 'flex';
                }
            }
            window.dispatchEvent(new CustomEvent('filterMembers', { detail: { filter } }));
        });
    });

    /* ---- Clear filter ---- */
    const clearFilterBtn = document.getElementById('btnClearFilter');
    const resetFilterBtn = document.getElementById('btnResetFilter');
    function resetAllFilters() {
        filterPills.forEach(p => {
            const on = p.dataset.filter === 'all';
            p.classList.toggle('active', on);
            p.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (filterIndicator) filterIndicator.style.display = 'none';
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('btnClearSearch');
        if (searchInput) {
            searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
        }
        window.dispatchEvent(new CustomEvent('filterMembers', { detail: { filter: 'all' } }));
    }
    clearFilterBtn?.addEventListener('click', resetAllFilters);
    resetFilterBtn?.addEventListener('click', resetAllFilters);

    /* ---- Search clear button ---- */
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('btnClearSearch');
    if (searchInput && clearSearchBtn) {
        searchInput.addEventListener('input', () => {
            clearSearchBtn.style.display = searchInput.value ? 'grid' : 'none';
        });
        clearSearchBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }

    /* ---- 7. Sync stats + pill counts dari data rows ---- */
    function syncStats() {
        const rows = document.querySelectorAll('#memberRows tr:not(.skeleton-row)');
        let total = 0, active = 0, inactive = 0, newMonth = 0;

        rows.forEach(tr => {
            total++;
            const statusCell = tr.querySelector('td:nth-child(4)');
            const status = statusCell?.textContent.trim().toLowerCase() || '';
            if (status.includes('aktif') && !status.includes('non')) active++;
            else if (status.includes('non')) inactive++;

            // Detect "baru bulan ini" via icon star atau class
            if (tr.querySelector('.ph-star') || tr.dataset.new === '1') newMonth++;
        });

        // Animate counters
        animate(document.getElementById('miniTotal'), total);
        animate(document.getElementById('miniActive'), active);
        animate(document.getElementById('miniNew'), newMonth);
        animate(document.getElementById('miniInactive'), inactive);

        // Progress bars
        const barTotal = document.getElementById('barTotal');
        const barActive = document.getElementById('barActive');
        const barNew = document.getElementById('barNew');
        const barInactive = document.getElementById('barInactive');
        if (barTotal) barTotal.style.width = '100%';
        if (barActive) barActive.style.width = (total ? (active / total * 100) : 0) + '%';
        if (barNew) barNew.style.width = (total ? (newMonth / total * 100) : 0) + '%';
        if (barInactive) barInactive.style.width = (total ? (inactive / total * 100) : 0) + '%';

        // Pill counts
        const cAll = document.getElementById('countAll');
        const cActive = document.getElementById('countActive');
        const cInactive = document.getElementById('countInactive');
        const cNew = document.getElementById('countNew');
        if (cAll) cAll.textContent = total;
        if (cActive) cActive.textContent = active;
        if (cInactive) cInactive.textContent = inactive;
        if (cNew) cNew.textContent = newMonth;

        // Empty state vs no-match
        const empty = document.getElementById('emptyState');
        const noMatch = document.getElementById('membersNoMatch');
        const visible = Array.from(rows).filter(r => r.style.display !== 'none').length;
        if (empty) empty.style.display = total === 0 ? 'flex' : 'none';
        if (noMatch) noMatch.style.display = (total > 0 && visible === 0) ? 'flex' : 'none';

        // Last updated
        const lastEl = document.getElementById('membersLastUpdated');
        if (lastEl) {
            const span = lastEl.querySelector('span');
            if (span) {
                const now = new Date();
                span.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                lastEl.title = 'Terakhir diperbarui: ' + now.toLocaleString('id-ID');
            }
        }
    }

    // Listen to members.js event (backward compat)
    window.addEventListener('membersDataLoaded', (e) => {
        const { total, active, inactive, newThisMonth } = e.detail || {};
        if (typeof total === 'number') animate(document.getElementById('miniTotal'), total);
        if (typeof active === 'number') animate(document.getElementById('miniActive'), active);
        if (typeof inactive === 'number') animate(document.getElementById('miniInactive'), inactive);
        if (typeof newThisMonth === 'number') animate(document.getElementById('miniNew'), newThisMonth);

        const barTotal = document.getElementById('barTotal');
        const barActive = document.getElementById('barActive');
        const barNew = document.getElementById('barNew');
        const barInactive = document.getElementById('barInactive');
        if (barTotal) barTotal.style.width = '100%';
        if (barActive && total > 0) barActive.style.width = ((active / total) * 100) + '%';
        if (barNew && total > 0) barNew.style.width = ((newThisMonth / total) * 100) + '%';
        if (barInactive && total > 0) barInactive.style.width = ((inactive / total) * 100) + '%';
    });

    // MutationObserver untuk sync otomatis
    if (rowsBody) {
        new MutationObserver(syncStats).observe(rowsBody, {
            childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class']
        });
    }

    /* ---- 8. Keyboard shortcuts ---- */
    document.getElementById('kbdNew')?.addEventListener('click', () => {
        document.getElementById('btnAdd')?.click();
    });
    document.getElementById('kbdRefresh')?.addEventListener('click', () => {
        document.getElementById('btnRefresh')?.click();
    });
    document.getElementById('kbdSearch')?.addEventListener('click', () => {
        searchInput?.focus();
    });

    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');

        if (e.key === 'Escape' && exportDropdown?.classList.contains('show')) {
            exportDropdown.classList.remove('show');
            return;
        }

        if (inInput) return;

        if (e.key.toLowerCase() === 'n' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnAdd')?.click();
        }
        if (e.key.toLowerCase() === 'r' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnRefresh')?.click();
        }
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            searchInput?.focus();
        }
    });

    /* ---- Initial sync ---- */
    syncStats();
})();
</script>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Last Updated ---- */
.last-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.last-updated i { color: var(--acc); font-size: 13px; }

/* ---- Visually hidden ---- */
.visually-hidden {
    position: absolute; width: 1px; height: 1px;
    margin: -1px; padding: 0; overflow: hidden;
    clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}

/* ---- Export Dropdown ---- */
.export-dropdown-wrap { position: relative; }
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
    transform: translateY(-8px) scale(.96);
    transition: all 0.25s cubic-bezier(.22,1,.36,1);
    z-index: 50;
}
.export-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
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
    transform: translateX(2px);
}
.export-item i { font-size: 20px; flex-shrink: 0; }
.export-item strong { display: block; font-size: 13px; font-weight: 700; }
.export-item span { font-size: 11px; color: var(--txt-2); }

/* ---- Enhanced Stat Cards ---- */
.members-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
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
.member-stat-glow {
    position: absolute;
    top: -30px; right: -30px;
    width: 140px; height: 140px;
    border-radius: 50%;
    pointer-events: none;
    transition: transform .5s;
    opacity: 0.8;
}
.member-stat-card:hover .member-stat-glow { transform: scale(1.3); }
.stat-total .member-stat-glow    { background: radial-gradient(circle, rgba(99,102,241,.25), transparent 70%); }
.stat-active .member-stat-glow   { background: radial-gradient(circle, rgba(16,185,129,.25), transparent 70%); }
.stat-new .member-stat-glow      { background: radial-gradient(circle, rgba(245,158,11,.25), transparent 70%); }
.stat-inactive .member-stat-glow { background: radial-gradient(circle, rgba(154,163,199,.2), transparent 70%); }

.member-stat-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 22px; color: #fff;
    margin-bottom: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
    position: relative; z-index: 1;
    transition: transform .3s;
}
.member-stat-card:hover .member-stat-icon { transform: scale(1.08) rotate(-5deg); }
.stat-total .member-stat-icon    { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-active .member-stat-icon   { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-new .member-stat-icon      { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-inactive .member-stat-icon { background: linear-gradient(135deg, #6b7394, #9aa3c7); }

.member-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
    position: relative; z-index: 1;
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
.member-stat-trend.up { background: rgba(16,185,129,.12); color: #6ee7b7; }
.member-stat-trend.down { background: rgba(239,68,68,.12); color: #fca5a5; }
.member-stat-trend.neutral { background: rgba(154,163,199,.1); color: var(--txt-1); }
.member-stat-trend i { font-size: 12px; }

.member-stat-content { position: relative; z-index: 1; }
.member-stat-content strong {
    display: block;
    font-size: 28px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
    line-height: 1.1;
}

.member-stat-bar {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: rgba(255,255,255,.05);
    overflow: hidden;
}
.member-stat-bar-fill {
    height: 100%;
    border-radius: 0 2px 2px 0;
    transition: width 0.8s cubic-bezier(.22,1,.36,1);
}
.stat-total .member-stat-bar-fill    { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.stat-active .member-stat-bar-fill   { background: linear-gradient(90deg, #10b981, #34d399); }
.stat-new .member-stat-bar-fill      { background: linear-gradient(90deg, #f59e0b, #f97316); }
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
.filter-pill:hover { color: var(--txt-0); background: rgba(255,255,255,.05); }
.filter-pill.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.3);
}
.filter-pill-dot { width: 8px; height: 8px; border-radius: 50%; }
.filter-pill-dot.active { background: var(--ok); }
.filter-pill-dot.inactive { background: var(--txt-2); }
.filter-pill-count {
    min-width: 22px;
    padding: 2px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.filter-pill.active .filter-pill-count { background: rgba(255,255,255,.25); }

/* ---- Filter Indicator ---- */
.filter-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 22px 8px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    font-size: 12px;
    color: var(--txt-1);
}
.filter-indicator > span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.filter-indicator i { color: var(--acc); font-size: 14px; }
.filter-clear {
    background: transparent;
    border: 1px solid rgba(99,102,241,.3);
    color: var(--acc);
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.filter-clear:hover {
    background: rgba(99,102,241,.15);
    border-color: var(--pri);
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
    width: 32px; height: 32px;
    border-radius: 6px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid; place-items: center;
    transition: all 0.2s;
}
.view-toggle-btn i { font-size: 15px; }
.view-toggle-btn:hover { color: var(--txt-0); }
.view-toggle-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 2px 8px rgba(99,102,241,.3);
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
.bulk-count { font-size: 12px; font-weight: 700; color: #a5b4fc; }

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
    width: 52px; height: 52px;
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 18px; font-weight: 800;
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
.member-card-info small { font-size: 12px; color: var(--txt-2); }
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
.member-card-detail i { font-size: 15px; color: var(--acc); width: 18px; }
.member-card-actions { display: flex; gap: 8px; }
.member-card-actions .btn { flex: 1; justify-content: center; }

@keyframes card-appear {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ---- Status Selector ---- */
.status-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
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
.status-option:hover { background: rgba(255,255,255,.05); border-color: var(--glass-brd-2); }
.status-option.active { border-color: var(--ok); background: rgba(16,185,129,.08); }
.status-option:has(input[value="inactive"]).active {
    border-color: var(--txt-2);
    background: rgba(154,163,199,.08);
}
.status-option-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.06);
    display: grid; place-items: center;
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
.status-option strong { display: block; font-size: 13px; font-weight: 700; margin-bottom: 2px; }
.status-option span { font-size: 11px; color: var(--txt-2); }

/* ---- Form Section ---- */
.form-section { margin-bottom: 24px; }
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
.form-section-title i { font-size: 15px; color: var(--acc); }

/* ---- Character Counter dengan Progress Bar ---- */
.field-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
    gap: 12px;
}
.char-progress-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 100px;
}
.char-progress {
    flex: 1;
    height: 4px;
    background: rgba(255,255,255,.06);
    border-radius: 99px;
    overflow: hidden;
    min-width: 40px;
}
.char-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    border-radius: 99px;
    transition: width .3s, background .3s;
}
.char-progress-fill.warn   { background: linear-gradient(90deg, var(--warn), #fb923c); }
.char-progress-fill.danger { background: linear-gradient(90deg, var(--danger), #f87171); }
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    transition: color 0.2s;
}
.char-counter.warn   { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

/* ---- Check header ---- */
.check-header .check-mark { width: 18px; height: 18px; }

/* ---- Row selection ---- */
#memberRows tr { position: relative; transition: background .2s; }
#memberRows tr.selected { background: rgba(99,102,241,.08) !important; }
#memberRows tr.selected td:first-child { box-shadow: inset 3px 0 0 var(--pri); }

/* ---- Skeleton ---- */
.skeleton-row.skel-var-2 { animation-delay: 0.1s; }

/* ---- Empty State Kaya ---- */
.empty-rich { padding: 50px 30px; text-align: center; }
.empty-illustration {
    position: relative;
    width: 90px; height: 90px;
    margin: 0 auto 20px;
    display: grid; place-items: center;
    background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
    border-radius: 26px;
    font-size: 36px;
    color: var(--acc);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(99,102,241,.2);
}
.empty-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(20px);
    opacity: 0.5;
    pointer-events: none;
}
.empty-orb-1 {
    width: 60px; height: 60px;
    background: var(--pri);
    top: -20px; left: -20px;
    animation: emptyOrb 4s ease-in-out infinite;
}
.empty-orb-2 {
    width: 50px; height: 50px;
    background: var(--acc);
    bottom: -15px; right: -15px;
    animation: emptyOrb 5s ease-in-out infinite reverse;
}
@keyframes emptyOrb {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10px, -10px); }
}
.empty-rich h3 { font-size: 18px; font-weight: 800; margin-bottom: 8px; letter-spacing: -.3px; }
.empty-rich p {
    color: var(--txt-1);
    font-size: 13.5px;
    max-width: 40ch;
    margin: 0 auto 16px;
    line-height: 1.65;
}
.empty-features {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.empty-features span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
}
.empty-features i { font-size: 14px; color: var(--ok); }

/* ---- No Match ---- */
.members-nomatch { padding: 40px 24px; }
.members-nomatch .empty-illustration { width: 80px; height: 80px; font-size: 32px; }

/* ---- Delete Modal Enhanced ---- */
.danger-icon-wrap {
    position: relative;
    width: 48px; height: 48px;
    display: grid; place-items: center;
    flex-shrink: 0;
}
.danger-icon-pulse {
    position: absolute; inset: 0;
    border-radius: 50%;
    background: rgba(239,68,68,.25);
    animation: danger-pulse 2s ease-in-out infinite;
}
@keyframes danger-pulse {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.4); opacity: 0; }
}
.delete-item-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    margin-bottom: 16px;
    color: var(--txt-0);
    font-size: 13px;
    font-weight: 600;
}
.delete-item-preview i { font-size: 18px; color: var(--acc); flex-shrink: 0; }
.danger-note {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(239,68,68,.06);
    border: 1px solid rgba(239,68,68,.2);
}
.danger-note-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(239,68,68,.12);
    display: grid; place-items: center;
    font-size: 18px;
    color: var(--danger);
    flex-shrink: 0;
}
.danger-note-content strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #fca5a5;
    margin-bottom: 3px;
}
.danger-note-content p {
    margin: 0;
    font-size: 12px;
    color: var(--txt-1);
    line-height: 1.5;
}

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    font-size: 11.5px;
    color: var(--txt-2);
}
.kbd-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    cursor: pointer;
    transition: all .2s;
}
.kbd-item:hover {
    background: rgba(99,102,241,.08);
    border-color: rgba(99,102,241,.3);
    color: var(--acc);
}
.kbd-item kbd {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 5px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10.5px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 2px 0 rgba(0,0,0,.25);
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .members-filter-pills { gap: 6px; padding: 3px; }
    .filter-pill { padding: 8px 12px; font-size: 12px; }
}
@media (max-width: 768px) {
    .members-stats { grid-template-columns: repeat(2, 1fr); }
    .members-filter-pills { margin: 12px 16px; }
    .table-tools { flex-direction: column; gap: 12px; }
    .table-tools-right { width: 100%; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
    .status-selector { grid-template-columns: 1fr; }
    .last-updated { display: none; }
    .keyboard-hints { display: none; }
}
@media (max-width: 520px) {
    .members-stats { grid-template-columns: 1fr; }
    .member-stat-card { padding: 18px 20px; }
    .member-stat-icon { width: 42px; height: 42px; font-size: 19px; margin-bottom: 10px; }
    .member-stat-content strong { font-size: 24px; }
    .empty-features { flex-direction: column; gap: 8px; align-items: center; }
    .pager { gap: 3px; }
    .pager-btn { min-width: 32px; height: 32px; }
    .view-toggle { width: 100%; }
    .view-toggle-btn { flex: 1; }
}
</style>