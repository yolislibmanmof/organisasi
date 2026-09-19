<!-- File: views/pages/content.php -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Kustomisasi</div>
        <h2 class="page-title">Konten Situs Publik</h2>
        <p class="page-sub">Kelola pengurus, testimoni alumni, dan galeri yang tampil di beranda publik.</p>
    </div>
</section>

<div class="tab-bar">
    <button class="tab-btn tab-active" data-tab="officers"><i class="ph ph-users-three"></i> Pengurus <span class="tab-count"><?= $counts['officers'] ?></span></button>
    <button class="tab-btn" data-tab="testimonials"><i class="ph ph-quotes"></i> Testimoni <span class="tab-count"><?= $counts['testimonials'] ?></span></button>
    <button class="tab-btn" data-tab="galleries"><i class="ph ph-image"></i> Galeri <span class="tab-count"><?= $counts['galleries'] ?></span></button>
</div>

<section class="glass-card content-panel">
    <div class="table-tools">
        <span class="table-info" id="contentInfo"></span>
        <button class="btn btn-primary" id="btnAddContent"><i class="ph ph-plus"></i><span class="btn-text" id="btnAddLabel">Tambah Pengurus</span></button>
    </div>
    <div class="content-grid" id="contentGrid"></div>
    <div class="empty-rich" id="emptyContent" style="display:none;">
        <div class="empty-illustration"><i class="ph ph-sparkle"></i><span class="empty-spark"></span></div>
        <h3>Belum Ada Konten</h3>
        <p id="emptyContentText">Tambahkan konten pertama untuk ditampilkan di beranda publik.</p>
    </div>
</section>

<!-- Modal Pengurus -->
<div class="modal-backdrop" id="officerModal">
    <div class="modal glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-1"><i class="ph ph-user-circle"></i></span>
                <div><h3 id="officerModalTitle">Tambah Pengurus</h3><p class="modal-sub">Data tampil pada seksi Struktur Kepengurusan.</p></div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="officerForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="officers">
            <input type="hidden" id="oId" value="">
            <div class="modal-body">
                <label class="field"><span class="field-label">Nama Lengkap <em>*</em></span>
                    <input type="text" name="full_name" id="oName" required>
                    <span class="field-error" data-error="full_name"></span></label>
                <label class="field"><span class="field-label">Jabatan <em>*</em></span>
                    <input type="text" name="position" id="oPosition" placeholder="Contoh: Ketua Umum" required>
                    <span class="field-error" data-error="position"></span></label>
                <div class="field-row">
                    <label class="field"><span class="field-label">Foto (opsional)</span>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
                    <label class="field"><span class="field-label">Urutan Tampil</span>
                        <input type="number" name="sort_order" id="oOrder" value="0" min="0"></label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary"><span class="btn-text">Simpan</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Testimoni -->
<div class="modal-backdrop" id="testimonialModal">
    <div class="modal glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-3"><i class="ph ph-quotes"></i></span>
                <div><h3 id="testimonialModalTitle">Tambah Testimoni</h3><p class="modal-sub">Kutipan tampil pada seksi Kata Alumni.</p></div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="testimonialForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="testimonials">
            <input type="hidden" id="tId" value="">
            <div class="modal-body">
                <label class="field"><span class="field-label">Nama <em>*</em></span>
                    <input type="text" name="name" id="tName" required>
                    <span class="field-error" data-error="name"></span></label>
                <label class="field"><span class="field-label">Peran / Angkatan</span>
                    <input type="text" name="role" id="tRole" placeholder="Contoh: Alumni 2021 — Software Engineer"></label>
                <label class="field"><span class="field-label">Kutipan <em>*</em></span>
                    <textarea name="quote" id="tQuote" rows="4" required></textarea>
                    <span class="field-error" data-error="quote"></span></label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary"><span class="btn-text">Simpan</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Galeri -->
<div class="modal-backdrop" id="galleryModal">
    <div class="modal glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-4"><i class="ph ph-image"></i></span>
                <div><h3 id="galleryModalTitle">Tambah Foto Galeri</h3><p class="modal-sub">Foto tampil pada seksi Galeri Kegiatan.</p></div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="galleryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="galleries">
            <input type="hidden" id="gId" value="">
            <div class="modal-body">
                <label class="field"><span class="field-label">Judul Foto <em>*</em></span>
                    <input type="text" name="title" id="gTitle" placeholder="Contoh: Bakti Sosial 2026" required>
                    <span class="field-error" data-error="title"></span></label>
                <label class="field"><span class="field-label">Berkas Gambar <em>*</em></span>
                    <input type="file" name="image" id="gImage" accept="image/jpeg,image/png,image/webp">
                    <span class="field-error" data-error="image"></span></label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary"><span class="btn-text">Simpan</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal-backdrop" id="contentDeleteModal">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div><h3>Hapus Konten?</h3><p class="modal-sub">Konten akan hilang dari situs publik.</p></div>
        </div>
        <div class="modal-body"><p class="modal-text" id="contentDeleteText"></p></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
            <button type="button" class="btn btn-danger" id="btnConfirmContentDelete"><span class="btn-text">Ya, Hapus</span></button>
        </div>
    </div>
</div>

<div class="toast-zone" id="toastZone"></div>
<script src="<?= asset('js/content.js') ?>"></script>