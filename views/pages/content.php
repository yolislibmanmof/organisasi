<?php
/**
 * ============================================================
 * MANAJEMEN KONTEN PUBLIK — ULTIMATE EDITION v7.0
 * Kelola pengurus, testimoni alumni, dan galeri dengan
 * tab navigation animasi, pencarian, preview, dan skeleton kaya.
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
        <span class="last-updated" id="lastUpdated" title="Waktu sinkronisasi terakhir">
            <i class="ph ph-clock"></i>
            <span>Baru saja</span>
        </span>
    </div>
</section>

<!-- ========== TAB NAVIGATION (ANIMATED) ========== -->
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
    <span class="content-tab-indicator" id="tabIndicator"></span>
</div>

<!-- ========== CONTENT PANEL ========== -->
<section class="glass-card content-panel">
    <div class="table-tools">
        <div class="table-tools-left">
            <span class="table-info" id="contentInfo" aria-live="polite">Memuat data...</span>
            <div class="content-legend">
                <span class="legend-item">
                    <i class="ph ph-sort-ascending"></i>
                    Urutkan sesuai prioritas
                </span>
            </div>
        </div>
        <div class="table-tools-right">
            <div class="content-search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="search" id="contentSearch" placeholder="Cari konten..." aria-label="Cari konten" autocomplete="off">
            </div>
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
        <!-- Skeleton kaya (6 variasi) -->
        <div class="content-card-skeleton skel-var-1">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:70%"></div>
                <div class="skel" style="height:10px;width:50%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
        <div class="content-card-skeleton skel-var-2">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:60%"></div>
                <div class="skel" style="height:10px;width:45%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
        <div class="content-card-skeleton skel-var-1">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:75%"></div>
                <div class="skel" style="height:10px;width:55%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
        <div class="content-card-skeleton skel-var-2">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:65%"></div>
                <div class="skel" style="height:10px;width:48%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
        <div class="content-card-skeleton skel-var-1">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:68%"></div>
                <div class="skel" style="height:10px;width:42%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
        <div class="content-card-skeleton skel-var-2">
            <div class="skel-cover"></div>
            <div class="skel-body">
                <div class="skel" style="height:14px;width:72%"></div>
                <div class="skel" style="height:10px;width:52%;margin-top:8px"></div>
                <div class="skel-actions">
                    <div class="skel" style="height:24px;width:60px"></div>
                    <div class="skel" style="height:24px;width:60px"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state kaya -->
    <div class="empty-rich" id="emptyContent" style="display:none;">
        <div class="empty-illustration">
            <div class="empty-orb empty-orb-1"></div>
            <div class="empty-orb empty-orb-2"></div>
            <i class="ph ph-sparkle"></i>
            <span class="empty-spark"></span>
        </div>
        <h3 id="emptyContentTitle">Belum Ada Konten</h3>
        <p id="emptyContentText">Tambahkan konten pertama untuk ditampilkan di beranda publik.</p>
        <div class="empty-features">
            <span><i class="ph ph-check-circle"></i> Mudah ditambah</span>
            <span><i class="ph ph-check-circle"></i> Langsung tampil</span>
            <span><i class="ph ph-check-circle"></i> Edit kapan saja</span>
        </div>
        <button class="btn btn-primary" id="emptyAddContent">
            <i class="ph ph-plus"></i>
            <span class="btn-text" id="emptyAddLabel">Tambah Konten Pertama</span>
        </button>
    </div>

    <!-- No match state -->
    <div class="empty-rich content-nomatch" id="contentNoMatch" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-magnifying-glass"></i>
        </div>
        <h3>Tidak Ada Hasil</h3>
        <p>Tidak ada konten yang cocok dengan pencarian Anda.</p>
        <button class="btn btn-ghost btn-sm" id="btnContentReset">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <span class="btn-text">Reset Pencarian</span>
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
                            <input type="text" name="division" id="oDivision" placeholder="Contoh: PNI">
                            <small class="asset-hint">Kosongkan jika tidak dikelompokkan.</small>
                        </label>
                    </div>
                </div>

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
                            <small class="asset-hint">JPG/PNG/WEBP, maks 2 MB • Rasio 1:1 disarankan</small>
                        </label>
                        <label class="field">
                            <span class="field-label">Urutan Tampil</span>
                            <input type="number" name="sort_order" id="oOrder" value="0" min="0">
                            <small class="asset-hint">Angka lebih kecil tampil lebih dulu.</small>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-text-aa"></i> Biografi (Opsional)
                    </div>
                    <label class="field">
                        <span class="field-label">Biografi Singkat</span>
                        <textarea name="bio" id="oBio" rows="3" placeholder="Deskripsi singkat tentang pengurus ini…" maxlength="500"></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Bila diisi, tombol "Lihat Profile" muncul di beranda.</small>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="oBioCounter">0 / 500</span>
                                <div class="char-progress">
                                    <div class="char-progress-fill" id="oBioBar" style="width:0%"></div>
                                </div>
                            </div>
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

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-chat-centered-text"></i> Kutipan
                    </div>
                    <label class="field">
                        <span class="field-label">Kutipan <em>*</em></span>
                        <textarea name="quote" id="tQuote" rows="5" placeholder="Tuliskan kutipan inspiratif..." required maxlength="800"></textarea>
                        <div class="field-footer">
                            <span class="field-error" data-error="quote"></span>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="tQuoteCounter">0 / 800</span>
                                <div class="char-progress">
                                    <div class="char-progress-fill" id="tQuoteBar" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-eye"></i> Pratinjau Langsung
                    </div>
                    <div class="quote-preview glass-card" id="quotePreview">
                        <i class="ph ph-quotes quote-preview-icon"></i>
                        <p id="previewQuote">Kutipan akan muncul di sini...</p>
                        <div class="quote-who">
                            <div class="quote-avatar" id="previewAvatar">N</div>
                            <div>
                                <strong id="previewName">Nama</strong>
                                <small id="previewRole">Peran</small>
                            </div>
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
                    <p class="modal-sub">Foto tampil pada seksi Galeri Kegiatan.</p>
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
                                <span>JPG, PNG, atau WEBP</span>
                                <div class="upload-limit">
                                    <span><i class="ph ph-file-image"></i> Maks 10 MB</span>
                                    <span><i class="ph ph-arrows-out"></i> Disarankan 16:9</span>
                                </div>
                            </div>
                            <div class="upload-zone-preview" id="gImagePreview" style="display:none">
                                <img src="" alt="Preview">
                                <div class="upload-zone-info" id="gImageInfo"></div>
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

<!-- ========== MODAL HAPUS (ENHANCED) ========== -->
<div class="modal-backdrop" id="contentDeleteModal" role="dialog" aria-labelledby="deleteTitle">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            </div>
            <div>
                <h3 id="deleteTitle">Hapus Konten?</h3>
                <p class="modal-sub">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="delete-item-preview" id="deleteItemPreview">
                <i class="ph ph-file-text"></i>
                <span id="contentDeleteText">—</span>
            </div>
            <div class="danger-note">
                <div class="danger-note-icon"><i class="ph ph-warning"></i></div>
                <div class="danger-note-content">
                    <strong>Peringatan</strong>
                    <p>Konten akan hilang dari situs publik. Data yang sudah dihapus tidak dapat dipulihkan.</p>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmContentDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus Permanen</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/content.js') ?>"></script>

<!-- ========== ENHANCER v7.0 (self-contained) ========== -->
<script>
(function(){
    'use strict';

    /* ---- Tab indicator animasi ---- */
    const tabs = document.querySelectorAll('.content-tab');
    const indicator = document.getElementById('tabIndicator');
    
    function moveIndicator(tab) {
        if (!indicator || !tab) return;
        const rect = tab.getBoundingClientRect();
        const parentRect = tab.parentElement.getBoundingClientRect();
        indicator.style.width = rect.width + 'px';
        indicator.style.transform = `translateX(${rect.left - parentRect.left}px)`;
    }
    
    const activeTab = document.querySelector('.content-tab.active');
    if (activeTab) {
        setTimeout(() => moveIndicator(activeTab), 100);
    }
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => moveIndicator(tab));
    });
    
    window.addEventListener('resize', () => {
        const active = document.querySelector('.content-tab.active');
        if (active) moveIndicator(active);
    });

    /* ---- Character counter dengan progress bar ---- */
    function setupCounter(fieldId, barId, counterId, max) {
        const field = document.getElementById(fieldId);
        const bar = document.getElementById(barId);
        const counter = document.getElementById(counterId);
        if (!field || !bar || !counter) return;
        
        field.addEventListener('input', () => {
            const len = field.value.length;
            const pct = Math.min(100, (len / max) * 100);
            counter.textContent = `${len} / ${max}`;
            bar.style.width = pct + '%';
            
            counter.className = 'char-counter';
            bar.className = 'char-progress-fill';
            if (len > max * 0.85) { counter.classList.add('warn'); bar.classList.add('warn'); }
            if (len > max * 0.95) { counter.classList.add('danger'); bar.classList.add('danger'); }
        });
    }
    
    setupCounter('oBio', 'oBioBar', 'oBioCounter', 500);
    setupCounter('tQuote', 'tQuoteBar', 'tQuoteCounter', 800);

    /* ---- Quote live preview dengan avatar ---- */
    const tName = document.getElementById('tName');
    const tRole = document.getElementById('tRole');
    const tQuote = document.getElementById('tQuote');
    const pName = document.getElementById('previewName');
    const pRole = document.getElementById('previewRole');
    const pQuote = document.getElementById('previewQuote');
    const pAvatar = document.getElementById('previewAvatar');
    
    function updateQuotePreview() {
        const name = tName?.value.trim() || 'Nama';
        const role = tRole?.value.trim() || 'Peran';
        const quote = tQuote?.value.trim() || 'Kutipan akan muncul di sini...';
        
        if (pName) pName.textContent = name;
        if (pRole) pRole.textContent = role;
        if (pQuote) pQuote.textContent = quote;
        if (pAvatar) pAvatar.textContent = name.charAt(0).toUpperCase() || 'N';
    }
    
    [tName, tRole, tQuote].forEach(f => {
        if (f) f.addEventListener('input', updateQuotePreview);
    });

    /* ---- Image preview dengan info file ---- */
    const gImage = document.getElementById('gImage');
    const gPreview = document.getElementById('gImagePreview');
    const gContent = document.getElementById('galleryUploadContent');
    const gInfo = document.getElementById('gImageInfo');
    
    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
    
    if (gImage) {
        gImage.addEventListener('change', () => {
            const file = gImage.files[0];
            if (file && gPreview && gContent) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    gPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="upload-zone-info">${file.name} • ${formatSize(file.size)}</div>
                        <button type="button" class="upload-zone-remove" id="removeGalleryImage">
                            <i class="ph ph-x"></i>
                        </button>
                    `;
                    gPreview.style.display = 'block';
                    gContent.style.display = 'none';
                    
                    const img = new Image();
                    img.onload = () => {
                        const info = gPreview.querySelector('.upload-zone-info');
                        if (info) info.textContent = `${file.name} • ${formatSize(file.size)} • ${img.width}×${img.height}`;
                    };
                    img.src = e.target.result;
                    
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

    /* ---- Officer photo preview ---- */
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

    /* ---- Search content (filter card yang ada) ---- */
    const search = document.getElementById('contentSearch');
    const grid = document.getElementById('contentGrid');
    const noMatch = document.getElementById('contentNoMatch');
    const info = document.getElementById('contentInfo');
    
    function applySearch() {
        if (!grid || !search) return;
        const term = search.value.trim().toLowerCase();
        const cards = grid.querySelectorAll('[data-content-card]');
        let visible = 0;
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const show = !term || text.includes(term);
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noMatch) noMatch.style.display = (cards.length > 0 && visible === 0) ? 'flex' : 'none';
        if (info && cards.length > 0) info.textContent = `${visible} dari ${cards.length} konten ditampilkan`;
    }
    
    search?.addEventListener('input', applySearch);
    
    document.getElementById('btnContentReset')?.addEventListener('click', () => {
        if (search) { search.value = ''; applySearch(); }
    });

    /* ---- Last updated ---- */
    const lastUpdated = document.getElementById('lastUpdated');
    if (lastUpdated) {
        const span = lastUpdated.querySelector('span');
        if (span) {
            const now = new Date();
            span.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            lastUpdated.title = 'Terakhir diperbarui: ' + now.toLocaleString('id-ID');
        }
    }
    
    /* ---- MutationObserver untuk re-apply search saat content.js re-render ---- */
    if (grid) {
        new MutationObserver(applySearch).observe(grid, { childList: true });
    }

    /* ---- Drag & drop untuk upload zone ---- */
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

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Content Tab Bar (ANIMATED) ---- */
.content-tab-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 12px;
    margin-bottom: 24px;
    position: relative;
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
    z-index: 1;
}
.content-tab:hover {
    background: rgba(255,255,255,.04);
    border-color: var(--glass-brd);
}
.content-tab.active {
    background: linear-gradient(135deg, rgba(99,102,241,.18), rgba(34,211,238,.1));
    border-color: rgba(99,102,241,.45);
    color: var(--txt-0);
    box-shadow: 0 8px 24px rgba(99,102,241,.2);
}
.content-tab-indicator {
    position: absolute;
    bottom: 8px;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    border-radius: 99px;
    transition: all 0.35s cubic-bezier(.22,1,.36,1);
    z-index: 0;
    box-shadow: 0 0 12px rgba(99,102,241,.5);
}
.content-tab-icon {
    width: 44px; height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    background: rgba(255,255,255,.06);
    display: grid; place-items: center;
    font-size: 20px;
    transition: all 0.3s;
}
.content-tab.active .content-tab-icon {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 6px 16px rgba(99,102,241,.35);
    transform: scale(1.05) rotate(-3deg);
}
.content-tab-info { flex: 1; min-width: 0; }
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
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.content-tab.active .content-tab-info span { color: var(--txt-1); }
.content-tab-count {
    padding: 4px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.08);
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    transition: all 0.3s;
}
.content-tab.active .content-tab-count {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.35);
}

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

/* ---- Content Legend ---- */
.content-legend {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}
.legend-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 99px;
    font-size: 10.5px;
    font-weight: 700;
    background: rgba(154,163,199,.08);
    border: 1px solid rgba(154,163,199,.2);
    color: var(--txt-1);
}
.legend-item i { font-size: 12px; color: var(--acc); }

/* ---- Search ---- */
.content-search {
    position: relative;
    width: 200px;
}
.content-search i {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: var(--txt-2);
    font-size: 14px;
    pointer-events: none;
}
.content-search input {
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
.content-search input:focus {
    border-color: var(--pri);
    background: rgba(99,102,241,.06);
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}
.content-search input::placeholder { color: var(--txt-2); }

/* ---- Form Sections ---- */
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
.char-progress-fill.warn { background: linear-gradient(90deg, var(--warn), #fb923c); }
.char-progress-fill.danger { background: linear-gradient(90deg, var(--danger), #f87171); }
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
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
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer;
}
.upload-zone-content {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--txt-1);
    font-size: 12.5px;
}
.upload-zone-content i { font-size: 20px; color: var(--acc); }
.upload-zone-preview {
    width: 60px; height: 60px;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}
.upload-zone-preview img {
    width: 100%; height: 100%;
    object-fit: cover;
}

/* ---- Upload Zone Full ---- */
.upload-zone {
    position: relative;
    padding: 32px 24px;
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
    position: absolute; inset: 0;
    opacity: 0; cursor: pointer;
}
.upload-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.upload-zone-content i { font-size: 40px; color: var(--acc); }
.upload-zone-content strong { font-size: 14px; font-weight: 700; }
.upload-zone-content span { font-size: 12px; color: var(--txt-2); }
.upload-limit {
    display: flex;
    gap: 14px;
    margin-top: 8px;
    font-size: 10.5px;
    color: var(--txt-2);
    flex-wrap: wrap;
    justify-content: center;
}
.upload-limit span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 99px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
}
.upload-limit i { font-size: 12px; color: var(--acc); }
.upload-zone-preview {
    position: relative;
    max-width: 260px;
    margin: 0 auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(0,0,0,.3);
}
.upload-zone-preview img {
    width: 100%; display: block;
}
.upload-zone-info {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 8px 12px;
    background: linear-gradient(0deg, rgba(0,0,0,.85), transparent);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.upload-zone-remove {
    position: absolute;
    top: 8px; right: 8px;
    width: 30px; height: 30px;
    border-radius: 50%;
    background: rgba(0,0,0,.7);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    cursor: pointer;
    display: grid; place-items: center;
    transition: all 0.2s;
    font-size: 14px;
}
.upload-zone-remove:hover {
    background: var(--danger);
    transform: scale(1.1);
}

/* ---- Quote Preview ---- */
.quote-preview {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 22px;
    min-height: 130px;
    position: relative;
    overflow: hidden;
}
.quote-preview::before {
    content: '';
    position: absolute;
    top: -30%; right: -15%;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34,211,238,.12), transparent 70%);
    pointer-events: none;
}
.quote-preview-icon {
    font-size: 26px;
    color: var(--acc);
    filter: drop-shadow(0 4px 8px rgba(34,211,238,.3));
}
.quote-preview p {
    font-family: 'Instrument Serif', serif;
    font-style: italic;
    font-size: 15px;
    line-height: 1.65;
    color: var(--txt-0);
    margin: 0;
    position: relative;
    z-index: 1;
}
.quote-who {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    z-index: 1;
}
.quote-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 16px;
    font-weight: 800;
    display: grid; place-items: center;
    box-shadow: 0 6px 16px rgba(99,102,241,.3);
}
.quote-who strong { display: block; font-size: 13px; font-weight: 700; }
.quote-who small { font-size: 11px; color: var(--txt-2); }

/* ---- Skeleton ---- */
.content-card-skeleton {
    border-radius: 16px;
    overflow: hidden;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    animation: skeleton-pulse 1.5s ease-in-out infinite;
}
.content-card-skeleton.skel-var-2 {
    animation-delay: 0.15s;
}
.skel-cover {
    height: 130px;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    position: relative;
    overflow: hidden;
}
.skel-cover::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.08), transparent);
    animation: skeleton-shimmer 2s infinite;
}
@keyframes skeleton-shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.skel-body { padding: 16px; }
.skel-actions {
    display: flex;
    gap: 8px;
    margin-top: 14px;
}

@keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.65; }
}

/* ---- Empty state kaya ---- */
.empty-rich {
    padding: 50px 30px;
    text-align: center;
}
.empty-illustration {
    position: relative;
    width: 90px; height: 90px;
    margin: 0 auto 20px;
    display: grid;
    place-items: center;
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
.empty-rich h3 {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
}
.empty-rich p {
    color: var(--txt-1);
    font-size: 13.5px;
    max-width: 40ch;
    margin: 0 auto 16px;
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
.empty-features i {
    font-size: 14px;
    color: var(--ok);
}

/* ---- Content No Match ---- */
.content-nomatch { padding: 40px 24px; }
.content-nomatch .empty-illustration {
    width: 80px; height: 80px;
    font-size: 32px;
}

/* ---- Delete Modal Enhanced ---- */
.danger-icon-wrap {
    position: relative;
    width: 48px; height: 48px;
    display: grid; place-items: center;
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
.delete-item-preview i {
    font-size: 18px;
    color: var(--acc);
    flex-shrink: 0;
}
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

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .table-tools {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .table-tools-left, .table-tools-right {
        flex-wrap: wrap;
    }
    .content-search { width: 100%; }
}
@media (max-width: 720px) {
    .content-tab-bar {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .content-tab {
        padding: 12px 16px;
    }
    .content-tab-info span {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .content-tab-indicator { display: none; }
    .content-tab.active {
        border-left: 3px solid var(--acc);
    }
    .field-row { grid-template-columns: 1fr; gap: 14px; }
    .last-updated { display: none; }
}
</style>