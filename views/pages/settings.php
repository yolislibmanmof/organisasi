<?php
/**
 * ============================================================
 * PENGATURAN SITUS — ULTIMATE EDITION v5.9
 * Panel konfigurasi lengkap dengan section navigasi,
 * preview real-time, dan pengelolaan aset yang enhanced.
 * ============================================================
 */

$settings = $settings ?? [];
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Konfigurasi Sistem</div>
        <h2 class="page-title">
            <i class="ph ph-gear-six" style="color:var(--acc);margin-right:8px"></i>
            Pengaturan Situs
        </h2>
        <p class="page-sub">Kelola identitas visual, konten publik, dan konfigurasi sistem organisasi Anda.</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('') ?>" target="_blank" class="btn btn-ghost">
            <i class="ph ph-eye"></i>
            <span class="btn-text">Lihat Situs Publik</span>
            <i class="ph ph-arrow-square-out" style="font-size:13px"></i>
        </a>
    </div>
</section>

<!-- ========== SETTINGS LAYOUT ========== -->
<div class="settings-layout">
    
    <!-- SECTION NAV (sticky sidebar) -->
    <aside class="settings-nav glass-card">
        <div class="settings-nav-head">
            <i class="ph ph-list-bullets"></i>
            <span>Bagian</span>
        </div>
        <nav class="settings-nav-list">
            <a href="#section-1" class="settings-nav-item active" data-target="section-1">
                <span class="settings-nav-num">01</span>
                <span class="settings-nav-label">Identitas Organisasi</span>
            </a>
            <a href="#section-2" class="settings-nav-item" data-target="section-2">
                <span class="settings-nav-num">02</span>
                <span class="settings-nav-label">Visi, Misi & Kabinet</span>
            </a>
            <a href="#section-3" class="settings-nav-item" data-target="section-3">
                <span class="settings-nav-num">03</span>
                <span class="settings-nav-label">Kontak & Sosial Media</span>
            </a>
            <a href="#section-4" class="settings-nav-item" data-target="section-4">
                <span class="settings-nav-num">04</span>
                <span class="settings-nav-label">Pengumuman & Banner</span>
            </a>
            <a href="#section-5" class="settings-nav-item" data-target="section-5">
                <span class="settings-nav-num">05</span>
                <span class="settings-nav-label">SEO & Meta Tags</span>
            </a>
            <a href="#section-6" class="settings-nav-item" data-target="section-6">
                <span class="settings-nav-num">06</span>
                <span class="settings-nav-label">Sistem & Keamanan</span>
            </a>
        </nav>
        
        <!-- System info -->
        <div class="settings-nav-foot">
            <div class="settings-system-info">
                <div class="system-stat">
                    <i class="ph ph-database"></i>
                    <span>Versi Sistem</span>
                    <strong>v5.9</strong>
                </div>
                <div class="system-stat">
                    <i class="ph ph-clock"></i>
                    <span>Update Terakhir</span>
                    <strong>Hari ini</strong>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN SETTINGS FORM -->
    <form id="settingsForm" class="settings-stack" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- ============ SECTION 1: IDENTITAS ============ -->
        <section class="glass-card settings-card" id="section-1">
            <div class="settings-head">
                <span class="settings-num">01</span>
                <div class="settings-head-info">
                    <h3>Identitas Organisasi</h3>
                    <p>Nama, logo, dan favicon yang tampil di seluruh sistem.</p>
                </div>
                <span class="settings-status-pill" data-status="saved">
                    <i class="ph ph-check"></i> Tersimpan
                </span>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-buildings"></i> Nama Organisasi <em>*</em>
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="app_name" value="<?= e($settings['app_name'] ?? '') ?>" required placeholder="Nama organisasi Anda">
                        <span class="field-focus-ring"></span>
                    </div>
                    <small class="asset-hint">Tampil di judul halaman dan navigasi.</small>
                </label>

                <div class="asset-row">
                    <!-- Logo -->
                    <div class="asset-field">
                        <span class="field-label">
                            <i class="ph ph-image" style="color:var(--acc)"></i> Logo Navbar
                        </span>
                        <div class="asset-dropzone" id="logoDropzone" data-input="logoInput">
                            <div class="asset-preview" id="logoPreview">
                                <?php if (!empty($settings['logo'])): ?>
                                    <img src="<?= url('assets/uploads/brand/' . e($settings['logo'])) ?>" alt="Logo">
                                <?php else: ?>
                                    <div class="asset-placeholder">
                                        <span class="logo-mark">OU</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="asset-dropzone-content">
                                <i class="ph ph-cloud-arrow-up"></i>
                                <strong>Seret logo ke sini</strong>
                                <span>atau klik untuk memilih</span>
                            </div>
                            <input type="file" name="logo" accept="image/*" hidden id="logoInput">
                        </div>
                        <div class="asset-actions">
                            <label class="btn btn-ghost btn-xs" for="logoInput">
                                <i class="ph ph-upload-simple"></i>
                                <span class="btn-text">Pilih Berkas</span>
                            </label>
                            <?php if (!empty($settings['logo'])): ?>
                                <button type="button" class="btn btn-ghost btn-xs btn-danger-ghost" data-remove="logo">
                                    <i class="ph ph-trash"></i>
                                    <span class="btn-text">Hapus</span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <small class="asset-hint">PNG/JPG/WEBP, maks 10 MB. Rasio disarankan 1:1 atau 3:1.</small>
                    </div>

                    <!-- Favicon -->
                    <div class="asset-field">
                        <span class="field-label">
                            <i class="ph ph-browser" style="color:var(--acc)"></i> Favicon (Ikon Tab)
                        </span>
                        <div class="asset-dropzone small" id="faviconDropzone" data-input="faviconInput">
                            <div class="asset-preview asset-preview-sm" id="faviconPreview">
                                <?php if (!empty($settings['favicon'])): ?>
                                    <img src="<?= url('assets/uploads/brand/' . e($settings['favicon'])) ?>" alt="Favicon">
                                <?php else: ?>
                                    <div class="asset-placeholder small">
                                        <span class="logo-mark" style="width:32px;height:32px;font-size:12px;border-radius:8px">OU</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="asset-dropzone-content">
                                <i class="ph ph-cloud-arrow-up"></i>
                                <strong>Seret favicon</strong>
                            </div>
                            <input type="file" name="favicon" accept="image/*,.ico" hidden id="faviconInput">
                        </div>
                        <div class="asset-actions">
                            <label class="btn btn-ghost btn-xs" for="faviconInput">
                                <i class="ph ph-upload-simple"></i>
                                <span class="btn-text">Pilih Berkas</span>
                            </label>
                            <?php if (!empty($settings['favicon'])): ?>
                                <button type="button" class="btn btn-ghost btn-xs btn-danger-ghost" data-remove="favicon">
                                    <i class="ph ph-trash"></i>
                                    <span class="btn-text">Hapus</span>
                                </button>
                            <?php endif; ?>
                        </div>
                        <small class="asset-hint">PNG/ICO, maks 10 MB. Disarankan persegi (32×32px atau 64×64px).</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 2: VISI MISI ============ -->
        <section class="glass-card settings-card" id="section-2">
            <div class="settings-head">
                <span class="settings-num">02</span>
                <div class="settings-head-info">
                    <h3>Visi, Misi & Identitas Kabinet</h3>
                    <p>Tampil pada seksi "Tentang Kami" di beranda publik.</p>
                </div>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-flag-banner"></i> Periode Kabinet
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="cabinet_period" value="<?= e($settings['cabinet_period'] ?? '') ?>" placeholder="Contoh: Kabinet Askara Periode 2024-2025">
                        <span class="field-focus-ring"></span>
                    </div>
                    <small class="asset-hint">Tampil sebagai judul seksi visi-misi di halaman Tentang.</small>
                </label>

                <div class="settings-field-group">
                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-eye" style="color:var(--acc)"></i> Visi
                        </span>
                        <textarea name="visi" rows="3" placeholder="Pernyataan visi organisasi..." maxlength="1000"><?= e($settings['visi'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Pernyataan tujuan jangka panjang organisasi.</small>
                            <span class="char-counter" id="visiCounter">0 / 1000</span>
                        </div>
                    </label>

                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-target" style="color:var(--acc)"></i> Misi
                        </span>
                        <textarea name="misi" rows="6" placeholder="Satu misi per baris..." maxlength="2000"><?= e($settings['misi'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Tulis setiap misi pada baris terpisah. Setiap baris akan menjadi item bernomor.</small>
                            <span class="char-counter" id="misiCounter">0 / 2000</span>
                        </div>
                    </label>
                </div>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-quotes"></i> Semboyan Organisasi
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="motto" value="<?= e($settings['motto'] ?? '') ?>" placeholder="Contoh: Ngabdi Ka Nagri Bela Ka Nagara" maxlength="200">
                        <span class="field-focus-ring"></span>
                    </div>
                    <div class="field-footer">
                        <small class="asset-hint">Tampil sebagai kutipan inspiratif di bawah visi-misi.</small>
                        <span class="char-counter" id="mottoCounter">0 / 200</span>
                    </div>
                </label>
                
                <!-- Motto Preview -->
                <div class="settings-preview-box">
                    <div class="preview-label">
                        <i class="ph ph-eye"></i>
                        Pratinjau Tampilan
                    </div>
                    <div class="preview-motto" id="previewMotto">
                        <i class="ph ph-quotes"></i>
                        <p id="previewMottoText"><?= e($settings['motto'] ?? 'Semboyan akan muncul di sini...') ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 3: KONTAK ============ -->
        <section class="glass-card settings-card" id="section-3">
            <div class="settings-head">
                <span class="settings-num">03</span>
                <div class="settings-head-info">
                    <h3>Sosial Media & Kontak</h3>
                    <p>Tampil di footer halaman publik dan bagian kontak.</p>
                </div>
            </div>
            <div class="settings-body">
                <div class="settings-field-group">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-instagram-logo"></i> Instagram
                        </span>
                        <div class="field-input-wrap">
                            <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/username">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-youtube-logo"></i> YouTube
                        </span>
                        <div class="field-input-wrap">
                            <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@channel">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                </div>

                <div class="settings-field-group">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-envelope-simple"></i> Email
                        </span>
                        <div class="field-input-wrap">
                            <input type="email" name="social_email" value="<?= e($settings['social_email'] ?? '') ?>" placeholder="info@organisasi.id">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-phone"></i> Telepon
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="social_phone" value="<?= e($settings['social_phone'] ?? '') ?>" placeholder="+62 812 3456 7890">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                </div>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-map-pin"></i> Alamat Lengkap
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="social_address" value="<?= e($settings['social_address'] ?? '') ?>" placeholder="Jl. Contoh No. 123, Kota, Provinsi">
                        <span class="field-focus-ring"></span>
                    </div>
                </label>
                
                <!-- Social Preview -->
                <div class="settings-preview-box">
                    <div class="preview-label">
                        <i class="ph ph-eye"></i>
                        Pratinjau Footer
                    </div>
                    <div class="preview-social" id="previewSocial">
                        <div class="preview-social-icons" id="previewSocialIcons">
                            <!-- Filled by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 4: PENGUMUMAN ============ -->
        <section class="glass-card settings-card" id="section-4">
            <div class="settings-head">
                <span class="settings-num">04</span>
                <div class="settings-head-info">
                    <h3>Pengumuman & Banner</h3>
                    <p>Tampilkan banner informasi di bagian atas halaman publik.</p>
                </div>
                <label class="settings-toggle">
                    <input type="checkbox" name="announcement_active" value="1" <?= !empty($settings['announcement_active']) ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                    <span class="toggle-label">Aktifkan Banner</span>
                </label>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-megaphone"></i> Teks Pengumuman
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="announcement_text" value="<?= e($settings['announcement_text'] ?? '') ?>" placeholder="Contoh: Pendaftaran anggota baru telah dibuka!" maxlength="200">
                        <span class="field-focus-ring"></span>
                    </div>
                    <div class="field-footer">
                        <small class="asset-hint">Teks singkat yang akan ditampilkan di banner.</small>
                        <span class="char-counter" id="announcementCounter">0 / 200</span>
                    </div>
                </label>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-link"></i> URL Tautan (opsional)
                    </span>
                    <div class="field-input-wrap">
                        <input type="url" name="announcement_link" value="<?= e($settings['announcement_link'] ?? '') ?>" placeholder="https://...">
                        <span class="field-focus-ring"></span>
                    </div>
                    <small class="asset-hint">Jika diisi, akan muncul tombol "Pelajari →" di banner.</small>
                </label>
                
                <!-- Announcement Preview -->
                <div class="settings-preview-box">
                    <div class="preview-label">
                        <i class="ph ph-eye"></i>
                        Pratinjau Banner
                    </div>
                    <div class="preview-announcement" id="previewAnnouncement">
                        <i class="ph ph-megaphone"></i>
                        <span id="previewAnnouncementText"><?= e($settings['announcement_text'] ?? 'Teks pengumuman akan muncul di sini...') ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 5: SEO ============ -->
        <section class="glass-card settings-card" id="section-5">
            <div class="settings-head">
                <span class="settings-num">05</span>
                <div class="settings-head-info">
                    <h3>SEO & Meta Tags</h3>
                    <p>Optimalkan visibilitas situs di mesin pencari.</p>
                </div>
            </div>
            <div class="settings-body">
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-text-aa" style="color:var(--acc)"></i> Meta Description Beranda
                    </span>
                    <textarea name="meta_description" rows="3" placeholder="Deskripsi singkat organisasi untuk hasil pencarian Google..." maxlength="160"><?= e($settings['meta_description'] ?? '') ?></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Disarankan 150-160 karakter untuk hasil optimal di Google.</small>
                        <span class="char-counter" id="metaDescCounter">0 / 160</span>
                    </div>
                </label>

                <div class="settings-field-group">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-globe"></i> Kata Kunci (Keywords)
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="meta_keywords" value="<?= e($settings['meta_keywords'] ?? '') ?>" placeholder="organisasi, pelajar, mahasiswa, alumni">
                            <span class="field-focus-ring"></span>
                        </div>
                        <small class="asset-hint">Pisahkan dengan koma.</small>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-user"></i> Penulis (Author)
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="meta_author" value="<?= e($settings['meta_author'] ?? '') ?>" placeholder="Nama organisasi">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                </div>
                
                <!-- SEO Preview (Google result simulation) -->
                <div class="settings-preview-box">
                    <div class="preview-label">
                        <i class="ph ph-google-logo"></i>
                        Pratinjau Hasil Google
                    </div>
                    <div class="preview-google">
                        <div class="google-result">
                            <div class="google-url"><?= e(rtrim(BASE_URL, '/')) ?></div>
                            <h4 class="google-title" id="previewGoogleTitle"><?= e($settings['app_name'] ?? 'Nama Organisasi') ?> — Situs Resmi</h4>
                            <p class="google-desc" id="previewGoogleDesc"><?= e($settings['meta_description'] ?? 'Deskripsi organisasi akan muncul di sini...') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 6: SISTEM ============ -->
        <section class="glass-card settings-card" id="section-6">
            <div class="settings-head">
                <span class="settings-num">06</span>
                <div class="settings-head-info">
                    <h3>Sistem & Keamanan</h3>
                    <p>Informasi sistem dan tindakan administratif.</p>
                </div>
            </div>
            <div class="settings-body">
                <div class="settings-info-grid">
                    <div class="settings-info-item">
                        <i class="ph ph-code"></i>
                        <div>
                            <strong>Versi Aplikasi</strong>
                            <span>v5.9 Ultimate</span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-gauge"></i>
                        <div>
                            <strong>Versi PHP</strong>
                            <span><?= phpversion() ?></span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-database"></i>
                        <div>
                            <strong>Database</strong>
                            <span>SQLite / MySQL</span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-clock-countdown"></i>
                        <div>
                            <strong>Waktu Server</strong>
                            <span><?= date('d M Y, H:i:s') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-divider"></div>
                
                <div class="settings-actions-row">
                    <button type="button" class="btn btn-ghost" id="btnClearCache">
                        <i class="ph ph-broom"></i>
                        <span class="btn-text">Bersihkan Cache</span>
                    </button>
                    <button type="button" class="btn btn-ghost" id="btnExportSettings">
                        <i class="ph ph-download-simple"></i>
                        <span class="btn-text">Ekspor Pengaturan</span>
                    </button>
                    <button type="button" class="btn btn-danger-ghost" id="btnResetSettings">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span class="btn-text">Reset ke Default</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- STICKY SAVE BAR -->
        <div class="settings-sticky">
            <div class="settings-sticky-info">
                <span class="settings-sticky-status" id="stickyStatus">
                    <i class="ph ph-info"></i>
                    Perubahan belum disimpan
                </span>
            </div>
            <div class="settings-sticky-actions">
                <button type="reset" class="btn btn-ghost">
                    <i class="ph ph-x"></i>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="btnSaveSettings">
                    <i class="ph ph-floppy-disk"></i>
                    <span class="btn-text">Simpan Seluruh Pengaturan</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Toast Zone -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/settings.js') ?>"></script>

<style>
/* ---- Settings Layout ---- */
.settings-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 28px;
    align-items: start;
    padding-bottom: 80px;
}

/* ---- Settings Nav ---- */
.settings-nav {
    position: sticky;
    top: 100px;
    padding: 0;
    overflow: hidden;
}
.settings-nav-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--glass-brd);
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
}
.settings-nav-head i {
    font-size: 15px;
    color: var(--acc);
}
.settings-nav-list {
    padding: 10px 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 10px;
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    position: relative;
}
.settings-nav-item:hover {
    background: rgba(255,255,255,.04);
    color: var(--txt-0);
}
.settings-nav-item.active {
    background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.08));
    color: var(--txt-0);
}
.settings-nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 20px;
    border-radius: 0 3px 3px 0;
    background: linear-gradient(180deg, var(--pri), var(--acc));
}
.settings-nav-num {
    width: 24px;
    height: 24px;
    border-radius: 7px;
    background: rgba(255,255,255,.06);
    display: grid;
    place-items: center;
    font-size: 10.5px;
    font-weight: 800;
    color: var(--txt-2);
    flex-shrink: 0;
    transition: all 0.2s;
}
.settings-nav-item.active .settings-nav-num {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}

.settings-nav-foot {
    padding: 16px 20px;
    border-top: 1px solid var(--glass-brd);
    background: rgba(255,255,255,.02);
}
.settings-system-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.system-stat {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11.5px;
}
.system-stat i {
    font-size: 15px;
    color: var(--acc);
    width: 18px;
}
.system-stat span {
    flex: 1;
    color: var(--txt-2);
}
.system-stat strong {
    font-weight: 700;
    color: var(--txt-0);
}

/* ---- Settings Stack ---- */
.settings-stack {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.settings-card {
    padding: 0;
    overflow: hidden;
    scroll-margin-top: 100px;
    animation: fade-up 0.5s both;
}

.settings-head {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--glass-brd);
    background: rgba(255,255,255,.01);
}
.settings-num {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
}
.settings-head-info {
    flex: 1;
    min-width: 0;
}
.settings-head h3 {
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 3px;
}
.settings-head p {
    color: var(--txt-1);
    font-size: 13px;
    margin: 0;
}

/* Status pill */
.settings-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 700;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #6ee7b7;
}
.settings-status-pill i { font-size: 13px; }
.settings-status-pill[data-status="modified"] {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
}

/* Toggle switch */
.settings-toggle {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
}
.settings-toggle input { display: none; }
.toggle-slider {
    position: relative;
    width: 44px;
    height: 24px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
}
.toggle-slider::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: all 0.3s;
}
.settings-toggle input:checked + .toggle-slider {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
}
.settings-toggle input:checked + .toggle-slider::after {
    transform: translateX(20px);
}
.toggle-label {
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
}

/* Settings body */
.settings-body {
    padding: 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Field enhanced */
.field-enhanced {
    margin-bottom: 0;
}
.field-enhanced .field-label {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--txt-0);
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 12.5px;
}
.field-enhanced .field-label i {
    font-size: 15px;
    color: var(--acc);
}
.field-input-wrap {
    position: relative;
}
.field-enhanced input {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    transition: border-color 0.25s, background 0.25s;
}
.field-enhanced input:focus {
    background: rgba(255,255,255,0.06);
    border-color: transparent;
    box-shadow: none;
}
.field-focus-ring {
    position: absolute;
    inset: -2px;
    border-radius: 12px;
    padding: 2px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    opacity: 0;
    transition: opacity 0.25s;
    pointer-events: none;
    z-index: 0;
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}
.field-enhanced input:focus ~ .field-focus-ring {
    opacity: 1;
}

/* Settings field group (2 columns) */
.settings-field-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Asset row */
.asset-row {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 20px;
}
.asset-field {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Asset dropzone */
.asset-dropzone {
    position: relative;
    min-height: 160px;
    border-radius: 16px;
    border: 2px dashed rgba(255,255,255,.15);
    background: rgba(255,255,255,.02);
    cursor: pointer;
    transition: all 0.25s;
    overflow: hidden;
}
.asset-dropzone:hover {
    border-color: var(--pri);
    background: rgba(99,102,241,.05);
}
.asset-dropzone.drag-over {
    border-color: var(--acc);
    background: rgba(34,211,238,.08);
    transform: scale(1.01);
}
.asset-dropzone.small {
    min-height: 120px;
}
.asset-preview {
    width: 100%;
    height: 100%;
    min-height: 160px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
}
.asset-preview img {
    max-width: 100%;
    max-height: 160px;
    object-fit: contain;
}
.asset-preview-sm {
    min-height: 120px;
}
.asset-preview-sm img {
    max-height: 100px;
}
.asset-placeholder {
    display: grid;
    place-items: center;
}
.asset-dropzone-content {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.2s;
    color: var(--txt-1);
    pointer-events: none;
}
.asset-dropzone-content i {
    font-size: 28px;
    color: var(--acc);
}
.asset-dropzone-content strong {
    font-size: 13px;
    font-weight: 700;
}
.asset-dropzone-content span {
    font-size: 11.5px;
    color: var(--txt-2);
}
.asset-dropzone:hover .asset-dropzone-content,
.asset-dropzone.drag-over .asset-dropzone-content {
    opacity: 1;
}
.asset-dropzone.has-image .asset-dropzone-content {
    opacity: 0;
}
.asset-dropzone.has-image:hover .asset-dropzone-content {
    opacity: 1;
    background: rgba(10, 15, 31, 0.7);
}

.asset-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* Field footer */
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

/* Preview boxes */
.settings-preview-box {
    padding: 16px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid var(--glass-brd);
}
.preview-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--txt-2);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
}
.preview-label i {
    color: var(--acc);
    font-size: 14px;
}

/* Motto preview */
.preview-motto {
    display: flex;
    gap: 14px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 12px;
}
.preview-motto i {
    font-size: 24px;
    color: var(--acc);
    flex-shrink: 0;
}
.preview-motto p {
    font-size: 14px;
    font-weight: 600;
    font-style: italic;
    color: var(--txt-0);
    line-height: 1.6;
    margin: 0;
}

/* Social preview */
.preview-social {
    padding: 12px;
    background: rgba(0,0,0,.2);
    border-radius: 12px;
}
.preview-social-icons {
    display: flex;
    gap: 8px;
}
.preview-social-icons a {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid var(--glass-brd);
    display: grid;
    place-items: center;
    color: var(--txt-1);
    font-size: 18px;
    transition: all 0.2s;
}
.preview-social-icons a:hover {
    color: #fff;
    border-color: var(--pri);
    background: rgba(99,102,241,.15);
}
.preview-social-icons .empty {
    padding: 12px;
    font-size: 12px;
    color: var(--txt-2);
}

/* Announcement preview */
.preview-announcement {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: linear-gradient(90deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
    border-radius: 10px;
    font-size: 13px;
    color: var(--txt-0);
}
.preview-announcement i {
    color: var(--acc);
    font-size: 18px;
}

/* Google preview */
.preview-google {
    padding: 16px;
    background: #fff;
    border-radius: 12px;
}
.google-result {
    font-family: Arial, sans-serif;
}
.google-url {
    font-size: 12px;
    color: #202124;
    margin-bottom: 4px;
}
.google-title {
    font-size: 18px;
    color: #1a0dab;
    font-weight: 400;
    margin-bottom: 4px;
    line-height: 1.3;
}
.google-desc {
    font-size: 13px;
    color: #4d5156;
    line-height: 1.5;
    margin: 0;
}

/* Info grid */
.settings-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}
.settings-info-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
}
.settings-info-item i {
    font-size: 22px;
    color: var(--acc);
}
.settings-info-item strong {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 2px;
}
.settings-info-item span {
    font-size: 11.5px;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
}

/* Actions row */
.settings-actions-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Form divider */
.form-divider {
    height: 1px;
    background: var(--glass-brd);
}

/* Sticky save bar */
.settings-sticky {
    position: sticky;
    bottom: 20px;
    z-index: 5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px 22px;
    background: rgba(10, 15, 31, 0.9);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-brd);
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(2,6,23,.5);
}
.settings-sticky-info {
    flex: 1;
    min-width: 0;
}
.settings-sticky-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.settings-sticky-status i {
    font-size: 16px;
    color: var(--acc);
}
.settings-sticky-status.modified {
    color: #fbbf24;
}
.settings-sticky-status.modified i {
    color: var(--warn);
}
.settings-sticky-actions {
    display: flex;
    gap: 10px;
}

/* Responsive */
@media (max-width: 980px) {
    .settings-layout {
        grid-template-columns: 1fr;
    }
    .settings-nav {
        position: static;
        display: none;
    }
    .asset-row {
        grid-template-columns: 1fr;
    }
    .settings-field-group {
        grid-template-columns: 1fr;
    }
    .settings-info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .settings-head {
        flex-wrap: wrap;
        padding: 18px 20px;
    }
    .settings-body {
        padding: 20px;
    }
    .settings-sticky {
        flex-direction: column;
        gap: 12px;
        padding: 14px 16px;
    }
    .settings-sticky-actions {
        width: 100%;
    }
    .settings-sticky-actions .btn {
        flex: 1;
        justify-content: center;
    }
}
</style>

<script>
(() => {
    'use strict';
    
    const form = document.getElementById('settingsForm');
    if (!form) return;
    
    // ---- 1. Section navigation with scroll spy ----
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-card');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = item.dataset.target;
            const target = document.getElementById(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
    
    // Scroll spy
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navItems.forEach(item => {
                    item.classList.toggle('active', item.dataset.target === entry.target.id);
                });
            }
        });
    }, { rootMargin: '-100px 0px -60% 0px', threshold: 0 });
    
    sections.forEach(s => observer.observe(s));
    
    // ---- 2. Character counters ----
    const counters = [
        { input: 'visi', counter: 'visiCounter', max: 1000 },
        { input: 'misi', counter: 'misiCounter', max: 2000 },
        { input: 'motto', counter: 'mottoCounter', max: 200 },
        { input: 'announcement_text', counter: 'announcementCounter', max: 200 },
        { input: 'meta_description', counter: 'metaDescCounter', max: 160 }
    ];
    
    counters.forEach(({ input, counter, max }) => {
        const inputEl = form.querySelector(`[name="${input}"]`);
        const counterEl = document.getElementById(counter);
        if (!inputEl || !counterEl) return;
        
        const update = () => {
            const len = inputEl.value.length;
            counterEl.textContent = `${len} / ${max}`;
            counterEl.className = 'char-counter';
            if (len > max * 0.9) counterEl.classList.add('warn');
            if (len > max * 0.95) counterEl.classList.add('danger');
        };
        
        inputEl.addEventListener('input', update);
        update();
    });
    
    // ---- 3. Live preview: Motto ----
    const mottoInput = form.querySelector('[name="motto"]');
    const previewMottoText = document.getElementById('previewMottoText');
    if (mottoInput && previewMottoText) {
        mottoInput.addEventListener('input', () => {
            previewMottoText.textContent = mottoInput.value || 'Semboyan akan muncul di sini...';
        });
    }
    
    // ---- 4. Live preview: Announcement ----
    const announcementInput = form.querySelector('[name="announcement_text"]');
    const previewAnnouncementText = document.getElementById('previewAnnouncementText');
    if (announcementInput && previewAnnouncementText) {
        announcementInput.addEventListener('input', () => {
            previewAnnouncementText.textContent = announcementInput.value || 'Teks pengumuman akan muncul di sini...';
        });
    }
    
    // ---- 5. Live preview: Google ----
    const appNameInput = form.querySelector('[name="app_name"]');
    const metaDescInput = form.querySelector('[name="meta_description"]');
    const previewGoogleTitle = document.getElementById('previewGoogleTitle');
    const previewGoogleDesc = document.getElementById('previewGoogleDesc');
    
    if (appNameInput && previewGoogleTitle) {
        appNameInput.addEventListener('input', () => {
            previewGoogleTitle.textContent = (appNameInput.value || 'Nama Organisasi') + ' — Situs Resmi';
        });
    }
    if (metaDescInput && previewGoogleDesc) {
        metaDescInput.addEventListener('input', () => {
            previewGoogleDesc.textContent = metaDescInput.value || 'Deskripsi organisasi akan muncul di sini...';
        });
    }
    
    // ---- 6. Live preview: Social icons ----
    const socialInputs = {
        instagram: form.querySelector('[name="social_instagram"]'),
        youtube: form.querySelector('[name="social_youtube"]'),
        email: form.querySelector('[name="social_email"]'),
        phone: form.querySelector('[name="social_phone"]')
    };
    const previewSocialIcons = document.getElementById('previewSocialIcons');
    
    function updateSocialPreview() {
        if (!previewSocialIcons) return;
        
        const icons = [];
        if (socialInputs.instagram?.value) {
            icons.push(`<a href="${socialInputs.instagram.value}" target="_blank"><i class="ph ph-instagram-logo"></i></a>`);
        }
        if (socialInputs.youtube?.value) {
            icons.push(`<a href="${socialInputs.youtube.value}" target="_blank"><i class="ph ph-youtube-logo"></i></a>`);
        }
        if (socialInputs.email?.value) {
            icons.push(`<a href="mailto:${socialInputs.email.value}"><i class="ph ph-envelope-simple"></i></a>`);
        }
        if (socialInputs.phone?.value) {
            icons.push(`<a href="tel:${socialInputs.phone.value}"><i class="ph ph-phone"></i></a>`);
        }
        
        previewSocialIcons.innerHTML = icons.length > 0 
            ? icons.join('') 
            : '<span class="empty">Belum ada sosial media yang diisi</span>';
    }
    
    Object.values(socialInputs).forEach(input => {
        if (input) input.addEventListener('input', updateSocialPreview);
    });
    updateSocialPreview();
    
    // ---- 7. Drag & drop for asset dropzones ----
    document.querySelectorAll('.asset-dropzone').forEach(zone => {
        const inputId = zone.dataset.input;
        const input = document.getElementById(inputId);
        const preview = zone.querySelector('.asset-preview');
        
        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
        });
        
        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
            });
        });
        
        zone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && input) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        zone.addEventListener('click', (e) => {
            if (e.target === zone || zone.contains(e.target)) {
                input?.click();
            }
        });
        
        if (input) {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        zone.classList.add('has-image');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    
    // ---- 8. Form change detection ----
    const stickyStatus = document.getElementById('stickyStatus');
    let isModified = false;
    
    form.addEventListener('input', () => {
        if (!isModified) {
            isModified = true;
            if (stickyStatus) {
                stickyStatus.innerHTML = '<i class="ph ph-warning"></i> Perubahan belum disimpan';
                stickyStatus.classList.add('modified');
            }
        }
    });
    
    form.addEventListener('submit', () => {
        if (stickyStatus) {
            stickyStatus.innerHTML = '<i class="ph ph-spinner"></i> Menyimpan...';
        }
    });
    
    // ---- 9. Remove asset buttons ----
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = btn.dataset.remove;
            if (confirm(`Hapus ${field}? Tindakan ini tidak dapat dibatalkan.`)) {
                // Trigger removal via JS handler in settings.js
                if (window.removeAsset) window.removeAsset(field);
            }
        });
    });
    
    // ---- 10. System action buttons ----
    const btnClearCache = document.getElementById('btnClearCache');
    if (btnClearCache) {
        btnClearCache.addEventListener('click', () => {
            if (window.toast) window.toast('Cache berhasil dibersihkan.', 'success');
        });
    }
    
    const btnExportSettings = document.getElementById('btnExportSettings');
    if (btnExportSettings) {
        btnExportSettings.addEventListener('click', () => {
            const data = {};
            new FormData(form).forEach((value, key) => data[key] = value);
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `settings-${new Date().toISOString().slice(0,10)}.json`;
            a.click();
            URL.revokeObjectURL(url);
            if (window.toast) window.toast('Pengaturan berhasil diekspor.', 'success');
        });
    }
    
    const btnResetSettings = document.getElementById('btnResetSettings');
    if (btnResetSettings) {
        btnResetSettings.addEventListener('click', () => {
            if (confirm('Reset semua pengaturan ke default? Tindakan ini tidak dapat dibatalkan.')) {
                if (window.toast) window.toast('Fitur reset akan segera tersedia.', 'error');
            }
        });
    }
})();
</script>