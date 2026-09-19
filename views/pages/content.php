<?php
/**
 * ============================================================
 * MANAJEMEN KONTEN PUBLIK — ULTIMATE EDITION v5.9
 * Kelola pengurus, testimoni alumni, dan galeri dengan
 * tab navigation, card grid, dan modal yang terstruktur.
 * ============================================================
 */
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Kustomisasi Situs</div>
        <h2 class="page-title">
            <i class="ph ph-paint-brush" style="color:var(--acc);margin-right:8px"></i>
            Konten Situs Publik
        </h2>
        <p class="page-sub">Kelola pengurus, testimoni alumni, dan galeri yang tampil di beranda publik.</p>
    </div>
    <div class="page-head-actions">
        <span class="live-indicator">
            <span class="live-dot"></span>
            <span id="contentTotal"><?= array_sum($counts) ?></span> konten aktif
        </span>
    </div>
</section>

<!-- ========== TAB NAVIGATION ========== -->
<div class="content-tab-bar glass-card">
    <button class="content-tab active" data-tab="officers">
        <span class="content-tab-icon">
            <i class="ph ph-users-three"></i>
        </span>
        <div class="content-tab-info">
            <strong>Pengurus</strong>
            <span>Struktur kepengurusan</span>
        </div>
        <span class="content-tab-count" id="countOfficers"><?= $counts['officers'] ?></span>
    </button>
    <button class="content-tab" data-tab="testimonials">
        <span class="content-tab-icon">
            <i class="ph ph-quotes"></i>
        </span>
        <div class="content-tab-info">
            <strong>Testimoni</strong>
            <span>Kata alumni & anggota</span>
        </div>
        <span class="content-tab-count" id="countTestimonials"><?= $counts['testimonials'] ?></span>
    </button>
    <button class="content-tab" data-tab="galleries">
        <span class="content-tab-icon">
            <i class="ph ph-image"></i>
        </span>
        <div class="content-tab-info">
            <strong>Galeri</strong>
            <span>Dokumentasi kegiatan</span>
        </div>
        <span class="content-tab-count" id="countGalleries"><?= $counts['galleries'] ?></span>
    </button>
</div>

<!-- ========== CONTENT PANEL ========== -->
<section class="glass-card content-panel">
    <div class="table-tools">
        <div class="table-tools-left">
            <span class="table-info" id="contentInfo">Memuat data...</span>
        </div>
        <div class="table-tools-right">
            <button class="icon-btn" id="btnContentRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
            <button class="btn btn-primary" id="btnAddContent">
                <i class="ph ph-plus"></i>
                <span class="btn-text" id="btnAddLabel">Tambah Pengurus</span>
            </button>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid" id="contentGrid">
        <!-- Skeleton loading (akan di-replace oleh JS) -->
        <div class="content-card-skeleton">
            <div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div>
            <div style="padding:16px">
                <div class="skel" style="height:14px;width:70%;margin-bottom:8px"></div>
                <div class="skel" style="height:10px;width:50%"></div>
            </div>
        </div>
        <div class="content-card-skeleton">
            <div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div>
            <div style="padding:16px">
                <div class="skel" style="height:14px;width:65%;margin-bottom:8px"></div>
                <div class="skel" style="height:10px;width:45%"></div>
            </div>
        </div>
        <div class="content-card-skeleton">
            <div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div>
            <div style="padding:16px">
                <div class="skel" style="height:14px;width:75%;margin-bottom:8px"></div>
                <div class="skel" style="height:10px;width:55%"></div>
            </div>
        </div>
        <div class="content-card-skeleton">
            <div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div>
            <div style="padding:16px">
                <div class="skel" style="height:14px;width:60%;margin-bottom:8px"></div>
                <div class="skel" style="height:10px;width:40%"></div>
            </div>
        </div>
    </div>

    <!-- Empty state -->
    <div class="empty-rich" id="emptyContent" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-sparkle"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Belum Ada Konten</h3>
        <p id="emptyContentText">Tambahkan konten pertama untuk ditampilkan di beranda publik.</p>
        <button class="btn btn-primary" id="emptyAddContent">
            <i class="ph ph-plus"></i>
            <span class="btn-text" id="emptyAddLabel">Tambah Konten Pertama</span>
        </button>
    </div>
</section>

<!-- ========== MODAL PENGURUS ========== -->
<div class="modal-backdrop" id="officerModal" role="dialog" aria-labelledby="officerModalTitle">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-1"><i class="ph ph-user-circle"></i></span>
                <div>
                    <h3 id="officerModalTitle">Tambah Pengurus</h3>
                    <p class="modal-sub">Data tampil pada seksi Struktur Kepengurusan.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="officerForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="officers">
            <input type="hidden" id="oId" value="">
            <div class="modal-body">
                <!-- Section: Informasi Dasar -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-identification-card"></i> Informasi Dasar
                    </div>
                    <label class="field">
                        <span class="field-label">Nama Lengkap <em>*</em></span>
                        <input type="text" name="full_name" id="oName" placeholder="Contoh: Ahmad Fauzi" required>
                        <span class="field-error" data-error="full_name"></span>
                    </label>
                </div>

                <!-- Section: Jabatan -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-buildings"></i> Jabatan & Bidang
                    </div>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Jabatan <em>*</em></span>
                            <input type="text" name="position" id="oPosition" placeholder="Contoh: Ketua Umum" required>
                            <span class="field-error" data-error="position"></span>
                        </label>
                        <label class="field">
                            <span class="field-label">Bidang</span>
                            <input type="text" name="division" id="oDivision" placeholder="Contoh: Pengembangan Nalar Intelektual">
                            <small class="asset-hint">Kosongkan jika tidak dikelompokkan.</small>
                        </label>
                    </div>
                </div>

                <!-- Section: Media & Urutan -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-image"></i> Media & Urutan
                    </div>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Foto (opsional)</span>
                            <div class="upload-zone-mini" id="officerUploadZone">
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="oPhoto">
                                <div class="upload-zone-content">
                                    <i class="ph ph-upload-simple"></i>
                                    <span>Klik atau seret foto</span>
                                </div>
                                <div class="upload-zone-preview" id="oPhotoPreview" style="display:none">
                                    <img src="" alt="Preview">
                                </div>
                            </div>
                            <small class="asset-hint">JPG/PNG/WEBP, maks 2 MB.</small>
                        </label>
                        <label class="field">
                            <span class="field-label">Urutan Tampil</span>
                            <input type="number" name="sort_order" id="oOrder" value="0" min="0">
                            <small class="asset-hint">Angka lebih kecil tampil lebih dulu.</small>
                        </label>
                    </div>
                </div>

                <!-- Section: Biografi -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-text-aa"></i> Biografi (Opsional)
                    </div>
                    <label class="field">
                        <span class="field-label">Biografi Singkat</span>
                        <textarea name="bio" id="oBio" rows="3" placeholder="Deskripsi singkat tentang pengurus ini…" maxlength="500"></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Bila diisi, tombol "Lihat Profile" muncul di beranda publik.</small>
                            <span class="char-counter" id="oBioCounter">0 / 500</span>
                        </div>
                    </label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i>
                    <span class="btn-text">Simpan Pengurus</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL TESTIMONI ========== -->
<div class="modal-backdrop" id="testimonialModal" role="dialog" aria-labelledby="testimonialModalTitle">
    <div class="modal glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-3"><i class="ph ph-quotes"></i></span>
                <div>
                    <h3 id="testimonialModalTitle">Tambah Testimoni</h3>
                    <p class="modal-sub">Kutipan tampil pada seksi Kata Alumni.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="testimonialForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="testimonials">
            <input type="hidden" id="tId" value="">
            <div class="modal-body">
                <!-- Section: Identitas -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-user"></i> Identitas Pemberi Testimoni
                    </div>
                    <label class="field">
                        <span class="field-label">Nama <em>*</em></span>
                        <input type="text" name="name" id="tName" placeholder="Contoh: Budi Santoso" required>
                        <span class="field-error" data-error="name"></span>
                    </label>
                    <label class="field">
                        <span class="field-label">Peran / Angkatan</span>
                        <input type="text" name="role" id="tRole" placeholder="Contoh: Alumni 2021 — Software Engineer">
                        <small class="asset-hint">Contoh: Alumni 2021, Mahasiswa Aktif, dll.</small>
                    </label>
                </div>

                <!-- Section: Kutipan -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-chat-centered-text"></i> Kutipan
                    </div>
                    <label class="field">
                        <span class="field-label">Kutipan <em>*</em></span>
                        <textarea name="quote" id="tQuote" rows="5" placeholder="Tuliskan kutipan inspiratif dari alumni atau anggota..." required maxlength="800"></textarea>
                        <div class="field-footer">
                            <span class="field-error" data-error="quote"></span>
                            <span class="char-counter" id="tQuoteCounter">0 / 800</span>
                        </div>
                    </label>
                </div>

                <!-- Preview -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-eye"></i> Pratinjau
                    </div>
                    <div class="quote-preview glass-card" id="quotePreview">
                        <i class="ph ph-quotes" style="font-size:24px;color:var(--acc)"></i>
                        <p id="previewQuote" style="flex:1;font-style:italic;color:var(--txt-1)">
                            Kutipan akan muncul di sini...
                        </p>
                        <div class="quote-who">
                            <strong id="previewName">Nama</strong>
                            <small id="previewRole">Peran</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i>
                    <span class="btn-text">Simpan Testimoni</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL GALERI ========== -->
<div class="modal-backdrop" id="galleryModal" role="dialog" aria-labelledby="galleryModalTitle">
    <div class="modal glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-4"><i class="ph ph-image"></i></span>
                <div>
                    <h3 id="galleryModalTitle">Tambah Foto Galeri</h3>
                    <p class="modal-sub">Foto tampil pada seksi Galeri Kegiatan & halaman /galeri.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="galleryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="galleries">
            <input type="hidden" id="gId" value="">
            <div class="modal-body">
                <!-- Section: Informasi -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-info"></i> Informasi Foto
                    </div>
                    <label class="field">
                        <span class="field-label">Judul Foto <em>*</em></span>
                        <input type="text" name="title" id="gTitle" placeholder="Contoh: Bakti Sosial 2026" required>
                        <span class="field-error" data-error="title"></span>
                    </label>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Tanggal Kegiatan</span>
                            <input type="date" name="event_date" id="gDate">
                        </label>
                        <label class="field">
                            <span class="field-label">Lokasi</span>
                            <input type="text" name="location" id="gLocation" placeholder="Contoh: Aula Utama">
                        </label>
                    </div>
                </div>

                <!-- Section: Upload -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-upload"></i> Berkas Gambar
                    </div>
                    <label class="field">
                        <div class="upload-zone" id="galleryUploadZone">
                            <input type="file" name="image" id="gImage" accept="image/jpeg,image/png,image/webp" required>
                            <div class="upload-zone-content" id="galleryUploadContent">
                                <i class="ph ph-cloud-arrow-up"></i>
                                <strong>Klik atau seret gambar ke sini</strong>
                                <span>JPG, PNG, atau WEBP (maks 10 MB)</span>
                            </div>
                            <div class="upload-zone-preview" id="gImagePreview" style="display:none">
                                <img src="" alt="Preview">
                                <button type="button" class="upload-zone-remove" id="removeGalleryImage">
                                    <i class="ph ph-x"></i>
                                </button>
                            </div>
                        </div>
                        <span class="field-error" data-error="image"></span>
                    </label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i>
                    <span class="btn-text">Simpan Foto</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL HAPUS ========== -->
<div class="modal-backdrop" id="contentDeleteModal" role="dialog" aria-labelledby="deleteTitle">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3 id="deleteTitle">Hapus Konten?</h3>
                <p class="modal-sub">Konten akan hilang dari situs publik.</p>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text" id="contentDeleteText"></p>
            <div class="danger-note">
                <i class="ph ph-warning"></i>
                <span>Tindakan ini tidak dapat dibatalkan. Pastikan Anda yakin sebelum menghapus.</span>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmContentDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/content.js') ?>"></script>

<!-- ========== STYLING ========== -->
<style>
/* ---- Content Tab Bar ---- */
.content-tab-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 12px;
    margin-bottom: 24px;
}
.content-tab {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    border-radius: 14px;
    background: transparent;
    border: 1px solid transparent;
    color: var(--txt-1);
    font: inherit;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}
.content-tab::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 14px;
    padding: 1px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.3s;
}
.content-tab:hover {
    background: rgba(255,255,255,.04);
    border-color: var(--glass-brd);
}
.content-tab:hover::before { opacity: 0.5; }
.content-tab.active {
    background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.08));
    border-color: rgba(99,102,241,.4);
}
.content-tab.active::before { opacity: 1; }
.content-tab.active .content-tab-icon {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}
.content-tab.active .content-tab-info strong { color: var(--txt-0); }
.content-tab.active .content-tab-count {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}

.content-tab-icon {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    background: rgba(255,255,255,.06);
    display: grid;
    place-items: center;
    font-size: 20px;
    transition: all 0.3s;
}
.content-tab-info {
    flex: 1;
    min-width: 0;
}
.content-tab-info strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 2px;
    transition: color 0.3s;
}
.content-tab-info span {
    font-size: 11.5px;
    color: var(--txt-2);
}
.content-tab-count {
    padding: 4px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.08);
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    transition: all 0.3s;
}

/* ---- Content Panel ---- */
.content-panel {
    overflow: hidden;
}

/* ---- Form Sections ---- */
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
    transition: color 0.2s;
}
.char-counter.warn { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

/* ---- Upload Zone Mini ---- */
.upload-zone-mini {
    position: relative;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1.5px dashed rgba(255,255,255,.15);
    cursor: pointer;
    transition: all 0.25s;
}
.upload-zone-mini:hover {
    border-color: var(--pri);
    background: rgba(99,102,241,.06);
}
.upload-zone-mini input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.upload-zone-content {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--txt-1);
    font-size: 12.5px;
}
.upload-zone-content i {
    font-size: 20px;
    color: var(--acc);
}
.upload-zone-preview {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}
.upload-zone-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ---- Upload Zone Full ---- */
.upload-zone {
    position: relative;
    padding: 30px 24px;
    border-radius: 16px;
    background: rgba(255,255,255,.03);
    border: 2px dashed rgba(255,255,255,.15);
    cursor: pointer;
    transition: all 0.25s;
    text-align: center;
}
.upload-zone:hover {
    border-color: var(--pri);
    background: rgba(99,102,241,.06);
}
.upload-zone.drag-over {
    border-color: var(--acc);
    background: rgba(34,211,238,.1);
    transform: scale(1.01);
}
.upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}
.upload-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.upload-zone-content i {
    font-size: 36px;
    color: var(--acc);
}
.upload-zone-content strong {
    font-size: 14px;
    font-weight: 700;
}
.upload-zone-content span {
    font-size: 12px;
    color: var(--txt-2);
}
.upload-zone-preview {
    position: relative;
    max-width: 240px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
}
.upload-zone-preview img {
    width: 100%;
    display: block;
}
.upload-zone-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(4px);
    border: none;
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.upload-zone-remove:hover {
    background: var(--danger);
    transform: scale(1.1);
}

/* ---- Quote Preview ---- */
.quote-preview {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
    min-height: 120px;
}
.quote-preview p {
    font-size: 13.5px;
    line-height: 1.7;
    margin: 0;
}
.quote-who {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.quote-who strong {
    font-size: 13px;
    font-weight: 700;
}
.quote-who small {
    font-size: 11px;
    color: var(--txt-2);
}

/* ---- Skeleton ---- */
.content-card-skeleton {
    border-radius: 16px;
    overflow: hidden;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    animation: skeleton-pulse 1.5s ease-in-out infinite;
}

@keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .content-tab-bar {
        grid-template-columns: 1fr;
    }
    .content-tab-info span { display: none; }
}
</style>

<script>
/* ---- Character counter ---- */
(() => {
    const bioField = document.getElementById('oBio');
    const bioCounter = document.getElementById('oBioCounter');
    const quoteField = document.getElementById('tQuote');
    const quoteCounter = document.getElementById('tQuoteCounter');
    
    function updateCounter(field, counter, max) {
        if (!field || !counter) return;
        field.addEventListener('input', () => {
            const len = field.value.length;
            counter.textContent = `${len} / ${max}`;
            counter.className = 'char-counter';
            if (len > max * 0.9) counter.classList.add('warn');
            if (len > max * 0.95) counter.classList.add('danger');
        });
    }
    
    updateCounter(bioField, bioCounter, 500);
    updateCounter(quoteField, quoteCounter, 800);
})();

/* ---- Quote live preview ---- */
(() => {
    const nameField = document.getElementById('tName');
    const roleField = document.getElementById('tRole');
    const quoteField = document.getElementById('tQuote');
    const previewName = document.getElementById('previewName');
    const previewRole = document.getElementById('previewRole');
    const previewQuote = document.getElementById('previewQuote');
    
    function updatePreview() {
        if (previewName) previewName.textContent = nameField.value || 'Nama';
        if (previewRole) previewRole.textContent = roleField.value || 'Peran';
        if (previewQuote) previewQuote.textContent = quoteField.value || 'Kutipan akan muncul di sini...';
    }
    
    [nameField, roleField, quoteField].forEach(f => {
        if (f) f.addEventListener('input', updatePreview);
    });
})();

/* ---- Image preview ---- */
(() => {
    // Officer photo preview
    const oPhoto = document.getElementById('oPhoto');
    const oPreview = document.getElementById('oPhotoPreview');
    if (oPhoto && oPreview) {
        oPhoto.addEventListener('change', () => {
            const file = oPhoto.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    oPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    oPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Gallery image preview
    const gImage = document.getElementById('gImage');
    const gPreview = document.getElementById('gImagePreview');
    const gContent = document.getElementById('galleryUploadContent');
    const removeBtn = document.getElementById('removeGalleryImage');
    
    if (gImage && gPreview && gContent) {
        gImage.addEventListener('change', () => {
            const file = gImage.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    gPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="upload-zone-remove" id="removeGalleryImage">
                            <i class="ph ph-x"></i>
                        </button>
                    `;
                    gPreview.style.display = 'block';
                    gContent.style.display = 'none';
                    
                    // Re-bind remove button
                    document.getElementById('removeGalleryImage')?.addEventListener('click', (e) => {
                        e.stopPropagation();
                        gImage.value = '';
                        gPreview.style.display = 'none';
                        gContent.style.display = 'flex';
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Drag & drop
    const uploadZone = document.getElementById('galleryUploadZone');
    if (uploadZone) {
        ['dragenter', 'dragover'].forEach(evt => {
            uploadZone.addEventListener(evt, (e) => {
                e.preventDefault();
                uploadZone.classList.add('drag-over');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            uploadZone.addEventListener(evt, (e) => {
                e.preventDefault();
                uploadZone.classList.remove('drag-over');
            });
        });
    }
})();
</script>