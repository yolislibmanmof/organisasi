<!-- File: views/pages/census.php -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Verifikasi</div>
        <h2 class="page-title">Sensus Anggota</h2>
        <p class="page-sub">Tinjau pendaftaran masuk dan setujui sebagai akun anggota aktif.</p>
    </div>
    <div class="page-head-actions">
        <span class="live-indicator"><span class="live-dot"></span> <span id="censusNewCount">0</span> entri baru</span>
    </div>
</section>

<section class="glass-card table-card">
    <div class="table-tools">
        <span class="table-info" id="censusInfo"></span>
        <button class="icon-btn" id="btnCensusRefresh" title="Segarkan"><i class="ph ph-arrows-clockwise"></i></button>
    </div>
    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:22%">Nama</th>
                    <th style="width:24%">Kontak</th>
                    <th style="width:14%">Status</th>
                    <th style="width:10%">Angkatan</th>
                    <th style="width:14%">Waktu</th>
                    <th style="width:16%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="censusRows"></tbody>
        </table>
    </div>
    <div class="empty-rich" id="emptyCensus" style="display:none;">
        <div class="empty-illustration"><i class="ph ph-clipboard-text"></i><span class="empty-spark"></span></div>
        <h3>Belum Ada Entri Sensus</h3>
        <p>Formulir sensus publik dapat diakses melalui tautan di beranda.</p>
    </div>
</section>

<div class="toast-zone" id="toastZone"></div>
<script src="<?= asset('js/census.js') ?>"></script>