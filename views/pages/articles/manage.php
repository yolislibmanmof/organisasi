<!-- File: views/pages/articles/manage.php -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Publikasi</div>
        <h2 class="page-title">Manajemen Artikel</h2>
        <p class="page-sub">Tulis, sunting, dan terbitkan artikel yang dapat dibaca publik.</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('artikel') ?>" target="_blank" class="btn btn-ghost">
            <i class="ph ph-eye"></i><span class="btn-text">Lihat Halaman Publik</span>
        </a>
        <button class="btn btn-primary" id="btnAddArticle">
            <i class="ph ph-plus"></i><span class="btn-text">Tulis Artikel</span>
        </button>
    </div>
</section>

<section class="glass-card table-card">
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="artSearch" placeholder="Cari judul atau kategori…">
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="artInfo"></span>
            <button class="icon-btn" id="btnArtRefresh" title="Segarkan"><i class="ph ph-arrows-clockwise"></i></button>
        </div>
    </div>

    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:44%">Judul</th>
                    <th style="width:16%">Kategori</th>
                    <th style="width:16%">Penulis</th>
                    <th style="width:12%">Tanggal</th>
                    <th style="width:12%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="articleRows"></tbody>
        </table>
    </div>

    <div class="empty-rich" id="emptyArticles" style="display:none;">
        <div class="empty-illustration"><i class="ph ph-newspaper"></i><span class="empty-spark"></span></div>
        <h3>Belum Ada Artikel</h3>
        <p>Mulai publikasikan cerita dan informasi organisasi Anda.</p>
        <button class="btn btn-primary" id="emptyAddArticle">
            <i class="ph ph-plus"></i><span class="btn-text">Tulis Artikel Pertama</span>
        </button>
    </div>

    <div class="table-foot">
        <span id="artPageInfo"></span>
        <div class="pager">
            <button class="pager-btn" id="artPrev"><i class="ph ph-caret-left"></i></button>
            <span class="pager-current" id="artPagerCur">1 / 1</span>
            <button class="pager-btn" id="artNext"><i class="ph ph-caret-right"></i></button>
        </div>
    </div>
</section>

<!-- Modal Form Artikel -->
<div class="modal-backdrop" id="articleModal">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-newspaper"></i></span>
                <div>
                    <h3 id="articleModalTitle">Tulis Artikel Baru</h3>
                    <p class="modal-sub">Artikel yang diterbitkan langsung terlihat oleh publik.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="articleForm">
            <?= csrf_field() ?>
            <input type="hidden" id="aId" value="">
            <div class="modal-body">
                <label class="field">
                    <span class="field-label">Judul Artikel <em>*</em></span>
                    <input type="text" name="title" id="aTitle" placeholder="Contoh: Perjalanan Bakti Sosial 2026" required>
                    <span class="field-error" data-error="title"></span>
                </label>
                <label class="field">
                    <span class="field-label">Kategori <em>*</em></span>
                    <select name="category" id="aCategory" class="field-select">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" data-error="category"></span>
                </label>
                <label class="field">
                    <span class="field-label">Ringkasan (muncul di kartu artikel)</span>
                    <textarea name="excerpt" id="aExcerpt" rows="2" placeholder="Satu hingga dua kalimat penarik minat baca…"></textarea>
                </label>
                <label class="field">
                    <span class="field-label">Isi Artikel <em>*</em></span>
                    <textarea name="content" id="aContent" rows="7" placeholder="Tulis isi artikel di sini…"></textarea>
                    <span class="field-error" data-error="content"></span>
                </label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary"><span class="btn-text">Terbitkan</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-backdrop" id="articleDeleteModal">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3>Hapus Artikel?</h3>
                <p class="modal-sub">Artikel akan hilang dari halaman publik.</p>
            </div>
        </div>
        <div class="modal-body"><p class="modal-text" id="articleDeleteText"></p></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
            <button type="button" class="btn btn-danger" id="btnConfirmArticleDelete"><span class="btn-text">Ya, Hapus</span></button>
        </div>
    </div>
</div>

<div class="toast-zone" id="toastZone"></div>
<script src="<?= asset('js/articles.js') ?>"></script>