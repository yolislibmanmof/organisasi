<!-- File: views/pages/members.php (FINAL - TAHAP 4.3) -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Modul Keanggotaan</div>
        <h2 class="page-title">Data Anggota</h2>
        <p class="page-sub">Kelola data anggota secara real-time tanpa memuat ulang halaman.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-ghost" id="btnExport" title="Unduh data dalam format CSV">
            <i class="ph ph-download-simple"></i>
            <span class="btn-text">Ekspor CSV</span>
        </button>
        <button class="btn btn-ghost" id="btnExportPdf" title="Unduh laporan resmi berformat PDF">
            <i class="ph ph-file-pdf"></i>
            <span class="btn-text">Ekspor PDF</span>
        </button>
        <button class="btn btn-primary" id="btnAdd">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tambah Anggota</span>
        </button>
    </div>
</section>

<!-- Kartu ringkasan cepat -->
<section class="mini-stats">
    <article class="mini-stat glass-card">
        <i class="ph ph-users-three"></i>
        <div>
            <strong id="miniTotal">0</strong>
            <span>Total Terdaftar</span>
        </div>
    </article>
    <article class="mini-stat glass-card">
        <i class="ph ph-check-circle"></i>
        <div>
            <strong id="miniActive">0</strong>
            <span>Aktif</span>
        </div>
    </article>
    <article class="mini-stat glass-card">
        <i class="ph ph-calendar-plus"></i>
        <div>
            <strong id="miniNew">0</strong>
            <span>Bulan Ini</span>
        </div>
    </article>
</section>

<section class="glass-card table-card">
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Cari nama, email, atau telepon…">
            <button class="search-clear" id="btnClearSearch" title="Bersihkan" style="display:none;">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="tableInfo"></span>
            <button class="icon-btn" id="btnRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Indikator filter aktif -->
    <div class="filter-indicator" id="filterIndicator" style="display:none;">
        <span><i class="ph ph-funnel"></i> Filter aktif: "<span id="filterText"></span>"</span>
        <button class="filter-clear" id="btnClearFilter">Bersihkan</button>
    </div>

    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:35%">Anggota</th>
                    <th style="width:25%">Kontak</th>
                    <th style="width:12%">Status</th>
                    <th style="width:15%">Bergabung</th>
                    <th style="width:13%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="memberRows"></tbody>
        </table>
    </div>

    <!-- Empty state kaya -->
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

    <div class="table-foot">
        <span id="pageInfo"></span>
        <div class="pager">
            <button class="pager-btn" id="prevPage" title="Sebelumnya"><i class="ph ph-caret-left"></i></button>
            <span class="pager-current" id="pagerCurrent">1 / 1</span>
            <button class="pager-btn" id="nextPage" title="Berikutnya"><i class="ph ph-caret-right"></i></button>
        </div>
    </div>
</section>

<!-- Modal Formulir Anggota (diperkaya) -->
<div class="modal-backdrop" id="memberModal">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-1"><i class="ph ph-user-plus"></i></span>
                <div>
                    <h3 id="modalTitle">Tambah Anggota Baru</h3>
                    <p class="modal-sub">Isi formulir di bawah dengan data yang valid.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="memberForm">
            <?= csrf_field() ?>
            <input type="hidden" name="_id" id="fId" value="">
            <div class="modal-body">
                <div class="form-section">
                    <div class="form-section-title">Informasi Pribadi</div>
                    <label class="field">
                        <span class="field-label">Nama Lengkap <em>*</em></span>
                        <input type="text" name="full_name" id="fName" placeholder="Contoh: Budi Santoso" required>
                        <span class="field-error" data-error="full_name"></span>
                    </label>
                    <label class="field">
                        <span class="field-label">Alamat Email <em>*</em></span>
                        <input type="email" name="email" id="fEmail" placeholder="nama@organisasi.id" required>
                        <span class="field-error" data-error="email"></span>
                    </label>
                </div>

                <div class="form-divider"></div>

                <div class="form-section">
                    <div class="form-section-title">Kontak & Keanggotaan</div>
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
                        <textarea name="address" id="fAddress" rows="2" placeholder="Jl. Merdeka No. 1, Kota…"></textarea>
                    </label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary">
                    <span class="btn-text">Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (diperkaya) -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3>Konfirmasi Penghapusan</h3>
                <p class="modal-sub">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text" id="deleteText"></p>
            <div class="danger-note">
                <i class="ph ph-info"></i>
                <span>Akun login beserta seluruh data profil akan dihapus permanen dari sistem.</span>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
            <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                <span class="btn-text">Ya, Hapus Permanen</span>
            </button>
        </div>
    </div>
</div>

<script src="<?= asset('js/members.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script src="<?= asset('js/pdf-export.js') ?>"></script>