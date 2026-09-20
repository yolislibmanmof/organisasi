<?php
/**
 * ============================================================
 * PENGATURAN SITUS — ULTIMATE EDITION v7.0
 * Panel konfigurasi lengkap dengan section navigasi,
 * preview real-time, progress bars, dan pengelolaan aset premium.
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
        <span class="last-saved" id="lastSaved" title="Waktu penyimpanan terakhir">
            <i class="ph ph-clock"></i>
            <span>Baru saja</span>
        </span>
        <button class="btn btn-ghost btn-sm" id="btnMobileNav" title="Navigasi bagian">
            <i class="ph ph-list-bullets"></i>
            <span class="btn-text">Bagian</span>
        </button>
        <a href="<?= url('') ?>" target="_blank" class="btn btn-ghost" rel="noopener">
            <i class="ph ph-eye"></i>
            <span class="btn-text">Lihat Situs Publik</span>
            <i class="ph ph-arrow-square-out" style="font-size:13px"></i>
        </a>
    </div>
</section>

<!-- ========== SETTINGS LAYOUT ========== -->
<div class="settings-layout">
    
    <!-- MOBILE NAV OVERLAY -->
    <div class="settings-nav-overlay" id="settingsNavOverlay"></div>

    <!-- SECTION NAV (sticky sidebar) -->
    <aside class="settings-nav glass-card" id="settingsNav">
        <div class="settings-nav-head">
            <i class="ph ph-list-bullets"></i>
            <span>Bagian</span>
            <button class="settings-nav-close" id="settingsNavClose" aria-label="Tutup navigasi">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <nav class="settings-nav-list" role="navigation" aria-label="Navigasi pengaturan">
            <a href="#section-1" class="settings-nav-item active" data-target="section-1">
                <span class="settings-nav-num">01</span>
                <span class="settings-nav-label">Identitas Organisasi</span>
                <span class="settings-nav-dot" data-section="section-1"></span>
            </a>
            <a href="#section-2" class="settings-nav-item" data-target="section-2">
                <span class="settings-nav-num">02</span>
                <span class="settings-nav-label">Visi, Misi & Kabinet</span>
                <span class="settings-nav-dot" data-section="section-2"></span>
            </a>
            <a href="#section-3" class="settings-nav-item" data-target="section-3">
                <span class="settings-nav-num">03</span>
                <span class="settings-nav-label">Kontak & Sosial Media</span>
                <span class="settings-nav-dot" data-section="section-3"></span>
            </a>
            <a href="#section-4" class="settings-nav-item" data-target="section-4">
                <span class="settings-nav-num">04</span>
                <span class="settings-nav-label">Pengumuman & Banner</span>
                <span class="settings-nav-dot" data-section="section-4"></span>
            </a>
            <a href="#section-5" class="settings-nav-item" data-target="section-5">
                <span class="settings-nav-num">05</span>
                <span class="settings-nav-label">SEO & Meta Tags</span>
                <span class="settings-nav-dot" data-section="section-5"></span>
            </a>
            <a href="#section-6" class="settings-nav-item" data-target="section-6">
                <span class="settings-nav-num">06</span>
                <span class="settings-nav-label">Sistem & Keamanan</span>
                <span class="settings-nav-dot" data-section="section-6"></span>
            </a>
        </nav>
        
        <!-- System info -->
        <div class="settings-nav-foot">
            <div class="settings-system-info">
                <div class="system-stat">
                    <i class="ph ph-code"></i>
                    <span>Versi Sistem</span>
                    <strong>v7.0</strong>
                </div>
                <div class="system-stat">
                    <i class="ph ph-clock"></i>
                    <span>Update Terakhir</span>
                    <strong id="navLastUpdate"><?= date('H:i') ?></strong>
                </div>
                <div class="system-stat system-stat-live">
                    <i class="ph ph-pulse"></i>
                    <span>Status</span>
                    <strong class="system-ok"><i class="ph ph-check-circle"></i> Normal</strong>
                </div>
            </div>
        </div>
    </aside>

    <!-- MAIN SETTINGS FORM -->
    <form id="settingsForm" class="settings-stack" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- ============ SECTION 1: IDENTITAS ============ -->
        <section class="glass-card settings-card" id="section-1" data-section-id="section-1">
            <div class="settings-head">
                <span class="settings-num">01</span>
                <div class="settings-head-info">
                    <h3>Identitas Organisasi</h3>
                    <p>Nama, logo, dan favicon yang tampil di seluruh sistem.</p>
                </div>
                <span class="settings-status-pill" data-section-status="section-1">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-buildings"></i> Nama Organisasi <em>*</em>
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="app_name" value="<?= e($settings['app_name'] ?? '') ?>" required placeholder="Nama organisasi Anda" data-track>
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
                            <div class="asset-file-info" id="logoFileInfo"></div>
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
                            <div class="asset-file-info" id="faviconFileInfo"></div>
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
        <section class="glass-card settings-card" id="section-2" data-section-id="section-2">
            <div class="settings-head">
                <span class="settings-num">02</span>
                <div class="settings-head-info">
                    <h3>Visi, Misi & Identitas Kabinet</h3>
                    <p>Tampil pada seksi "Tentang Kami" di beranda publik.</p>
                </div>
                <span class="settings-status-pill" data-section-status="section-2">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-flag-banner"></i> Periode Kabinet
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="cabinet_period" value="<?= e($settings['cabinet_period'] ?? '') ?>" placeholder="Contoh: Kabinet Askara Periode 2024-2025" data-track>
                        <span class="field-focus-ring"></span>
                    </div>
                    <small class="asset-hint">Tampil sebagai judul seksi visi-misi di halaman Tentang.</small>
                </label>

                <div class="settings-field-group">
                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-eye" style="color:var(--acc)"></i> Visi
                        </span>
                        <textarea name="visi" rows="3" placeholder="Pernyataan visi organisasi..." maxlength="1000" data-track><?= e($settings['visi'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Pernyataan tujuan jangka panjang organisasi.</small>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="visiCounter">0 / 1000</span>
                                <div class="char-progress"><div class="char-progress-fill" id="visiBar"></div></div>
                            </div>
                        </div>
                    </label>

                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-target" style="color:var(--acc)"></i> Misi
                        </span>
                        <textarea name="misi" rows="6" placeholder="Satu misi per baris..." maxlength="2000" data-track><?= e($settings['misi'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Tulis setiap misi pada baris terpisah.</small>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="misiCounter">0 / 2000</span>
                                <div class="char-progress"><div class="char-progress-fill" id="misiBar"></div></div>
                            </div>
                        </div>
                    </label>
                </div>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-quotes"></i> Semboyan Organisasi
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="motto" value="<?= e($settings['motto'] ?? '') ?>" placeholder="Contoh: Ngabdi Ka Nagri Bela Ka Nagara" maxlength="200" data-track>
                        <span class="field-focus-ring"></span>
                    </div>
                    <div class="field-footer">
                        <small class="asset-hint">Tampil sebagai kutipan inspiratif di bawah visi-misi.</small>
                        <div class="char-progress-wrap">
                            <span class="char-counter" id="mottoCounter">0 / 200</span>
                            <div class="char-progress"><div class="char-progress-fill" id="mottoBar"></div></div>
                        </div>
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
        <section class="glass-card settings-card" id="section-3" data-section-id="section-3">
            <div class="settings-head">
                <span class="settings-num">03</span>
                <div class="settings-head-info">
                    <h3>Sosial Media & Kontak</h3>
                    <p>Tampil di footer halaman publik dan bagian kontak.</p>
                </div>
                <span class="settings-status-pill" data-section-status="section-3">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <div class="settings-field-group">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-instagram-logo"></i> Instagram
                        </span>
                        <div class="field-input-wrap">
                            <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/username" data-track>
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-youtube-logo"></i> YouTube
                        </span>
                        <div class="field-input-wrap">
                            <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@channel" data-track>
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
                            <input type="email" name="social_email" value="<?= e($settings['social_email'] ?? '') ?>" placeholder="info@organisasi.id" data-track>
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-phone"></i> Telepon
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="social_phone" value="<?= e($settings['social_phone'] ?? '') ?>" placeholder="+62 812 3456 7890" data-track>
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                </div>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-map-pin"></i> Alamat Lengkap
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="social_address" value="<?= e($settings['social_address'] ?? '') ?>" placeholder="Jl. Contoh No. 123, Kota, Provinsi" data-track>
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
                        <div class="preview-social-icons" id="previewSocialIcons"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 4: PENGUMUMAN ============ -->
        <section class="glass-card settings-card" id="section-4" data-section-id="section-4">
            <div class="settings-head">
                <span class="settings-num">04</span>
                <div class="settings-head-info">
                    <h3>Pengumuman & Banner</h3>
                    <p>Tampilkan banner informasi di bagian atas halaman publik.</p>
                </div>
                <label class="settings-toggle">
                    <input type="checkbox" name="announcement_active" value="1" <?= !empty($settings['announcement_active']) ? 'checked' : '' ?> data-track>
                    <span class="toggle-slider"></span>
                    <span class="toggle-label">Aktifkan Banner</span>
                </label>
                <span class="settings-status-pill" data-section-status="section-4">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-megaphone"></i> Teks Pengumuman
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="announcement_text" value="<?= e($settings['announcement_text'] ?? '') ?>" placeholder="Contoh: Pendaftaran anggota baru telah dibuka!" maxlength="200" data-track>
                        <span class="field-focus-ring"></span>
                    </div>
                    <div class="field-footer">
                        <small class="asset-hint">Teks singkat yang akan ditampilkan di banner.</small>
                        <div class="char-progress-wrap">
                            <span class="char-counter" id="announcementCounter">0 / 200</span>
                            <div class="char-progress"><div class="char-progress-fill" id="announcementBar"></div></div>
                        </div>
                    </div>
                </label>

                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-link"></i> URL Tautan (opsional)
                    </span>
                    <div class="field-input-wrap">
                        <input type="url" name="announcement_link" value="<?= e($settings['announcement_link'] ?? '') ?>" placeholder="https://..." data-track>
                        <span class="field-focus-ring"></span>
                    </div>
                    <small class="asset-hint">Jika diisi, akan muncul tombol "Pelajari →" di banner.</small>
                </label>
                
                <!-- Announcement Preview -->
                <div class="settings-preview-box">
                    <div class="preview-label">
                        <i class="ph ph-eye"></i>
                        Pratinjau Banner
                        <span class="preview-toggle-status" id="previewBannerStatus">Nonaktif</span>
                    </div>
                    <div class="preview-announcement" id="previewAnnouncement">
                        <i class="ph ph-megaphone"></i>
                        <span id="previewAnnouncementText"><?= e($settings['announcement_text'] ?? 'Teks pengumuman akan muncul di sini...') ?></span>
                        <?php if (!empty($settings['announcement_link'])): ?>
                        <span class="preview-announcement-link"><i class="ph ph-arrow-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SECTION 5: SEO ============ -->
        <section class="glass-card settings-card" id="section-5" data-section-id="section-5">
            <div class="settings-head">
                <span class="settings-num">05</span>
                <div class="settings-head-info">
                    <h3>SEO & Meta Tags</h3>
                    <p>Optimalkan visibilitas situs di mesin pencari.</p>
                </div>
                <span class="settings-status-pill" data-section-status="section-5">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-text-aa" style="color:var(--acc)"></i> Meta Description Beranda
                    </span>
                    <textarea name="meta_description" rows="3" placeholder="Deskripsi singkat organisasi untuk hasil pencarian Google..." maxlength="160" data-track><?= e($settings['meta_description'] ?? '') ?></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Disarankan 150-160 karakter untuk hasil optimal di Google.</small>
                        <div class="char-progress-wrap">
                            <span class="char-counter" id="metaDescCounter">0 / 160</span>
                            <div class="char-progress"><div class="char-progress-fill" id="metaDescBar"></div></div>
                        </div>
                    </div>
                </label>

                <div class="settings-field-group">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-globe"></i> Kata Kunci (Keywords)
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="meta_keywords" value="<?= e($settings['meta_keywords'] ?? '') ?>" placeholder="organisasi, pelajar, mahasiswa, alumni" data-track>
                            <span class="field-focus-ring"></span>
                        </div>
                        <small class="asset-hint">Pisahkan dengan koma.</small>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-user"></i> Penulis (Author)
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="meta_author" value="<?= e($settings['meta_author'] ?? '') ?>" placeholder="Nama organisasi" data-track>
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
        <section class="glass-card settings-card" id="section-6" data-section-id="section-6">
            <div class="settings-head">
                <span class="settings-num">06</span>
                <div class="settings-head-info">
                    <h3>Sistem & Keamanan</h3>
                    <p>Informasi sistem dan tindakan administratif.</p>
                </div>
                <span class="settings-status-pill" data-section-status="section-6">
                    <i class="ph ph-check-circle"></i>
                    <span>Tersimpan</span>
                </span>
            </div>
            <div class="settings-body">
                <div class="settings-info-grid">
                    <div class="settings-info-item">
                        <i class="ph ph-code"></i>
                        <div>
                            <strong>Versi Aplikasi</strong>
                            <span>v7.0 Ultimate</span>
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
                            <span><?= defined('DB_NAME') ? e(DB_NAME) : 'organisasi' ?></span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-clock-countdown"></i>
                        <div>
                            <strong>Waktu Server</strong>
                            <span id="serverTime"><?= date('d M Y, H:i:s') ?></span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-upload"></i>
                        <div>
                            <strong>Batas Unggah</strong>
                            <span><?= ini_get('upload_max_filesize') ?></span>
                        </div>
                    </div>
                    <div class="settings-info-item">
                        <i class="ph ph-memory"></i>
                        <div>
                            <strong>Batas Memori</strong>
                            <span><?= ini_get('memory_limit') ?></span>
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
        <div class="settings-sticky" id="settingsSticky">
            <div class="settings-sticky-info">
                <span class="settings-sticky-status" id="stickyStatus">
                    <i class="ph ph-info"></i>
                    <span id="stickyStatusText">Tidak ada perubahan</span>
                </span>
                <span class="settings-sticky-count" id="stickyChangeCount" style="display:none">
                    <span id="changeCountNum">0</span> perubahan
                </span>
            </div>
            <div class="settings-sticky-actions">
                <button type="reset" class="btn btn-ghost" id="btnCancelChanges" disabled>
                    <i class="ph ph-x"></i>
                    <span class="btn-text">Batalkan</span>
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="btnSaveSettings" disabled>
                    <i class="ph ph-floppy-disk"></i>
                    <span class="btn-text">Simpan Pengaturan</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Reset Confirmation Modal -->
<div class="modal-backdrop" id="resetModal" role="dialog" aria-labelledby="resetModalTitle" aria-modal="true">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-arrow-counter-clockwise"></i></span>
            </div>
            <div>
                <h3 id="resetModalTitle">Reset Pengaturan?</h3>
                <p class="modal-sub">Semua konfigurasi akan kembali ke nilai default.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="danger-note">
                <div class="danger-note-icon"><i class="ph ph-warning"></i></div>
                <div class="danger-note-content">
                    <strong>Peringatan</strong>
                    <p>Nama organisasi, logo, visi-misi, kontak, dan seluruh pengaturan akan direset. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmReset">
                <i class="ph ph-arrow-counter-clockwise"></i>
                <span class="btn-text">Ya, Reset Semua</span>
            </button>
        </div>
    </div>
</div>

<!-- Keyboard Hints -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdSave"><kbd>Ctrl</kbd>+<kbd>S</kbd> Simpan</span>
    <span class="kbd-item" id="kbdNav"><kbd>1</kbd>-<kbd>6</kbd> Pindah Bagian</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup Modal</span>
</div>

<!-- Toast Zone -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/settings.js') ?>"></script>

<style>
/* ---- Last Saved ---- */
.last-saved {
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
.last-saved i { color: var(--acc); font-size: 13px; }

/* ---- Settings Layout ---- */
.settings-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 28px;
    align-items: start;
    padding-bottom: 100px;
    position: relative;
}

/* ---- Mobile Nav Overlay ---- */
.settings-nav-overlay {
    position: fixed;
    inset: 0;
    background: rgba(4,8,20,.7);
    backdrop-filter: blur(4px);
    z-index: 90;
    opacity: 0;
    visibility: hidden;
    transition: all .3s;
}
.settings-nav-overlay.show {
    opacity: 1;
    visibility: visible;
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
.settings-nav-head i { font-size: 15px; color: var(--acc); }
.settings-nav-head span { flex: 1; }
.settings-nav-close {
    width: 28px; height: 28px;
    border-radius: 7px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    cursor: pointer;
    display: none;
    place-items: center;
    transition: all .2s;
}
.settings-nav-close:hover {
    background: rgba(239,68,68,.15);
    border-color: var(--danger);
    color: var(--danger);
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
    left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 20px;
    border-radius: 0 3px 3px 0;
    background: linear-gradient(180deg, var(--pri), var(--acc));
}
.settings-nav-num {
    width: 24px; height: 24px;
    border-radius: 7px;
    background: rgba(255,255,255,.06);
    display: grid; place-items: center;
    font-size: 10.5px; font-weight: 800;
    color: var(--txt-2);
    flex-shrink: 0;
    transition: all 0.2s;
}
.settings-nav-item.active .settings-nav-num {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}
.settings-nav-label { flex: 1; }
.settings-nav-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: transparent;
    flex-shrink: 0;
    transition: all .3s;
}
.settings-nav-dot.modified {
    background: var(--warn);
    box-shadow: 0 0 8px rgba(245,158,11,.5);
    animation: dot-pulse 2s infinite;
}
@keyframes dot-pulse {
    0%, 100% { box-shadow: 0 0 8px rgba(245,158,11,.5); }
    50% { box-shadow: 0 0 14px rgba(245,158,11,.8); }
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
.system-stat i { font-size: 15px; color: var(--acc); width: 18px; }
.system-stat span { flex: 1; color: var(--txt-2); }
.system-stat strong { font-weight: 700; color: var(--txt-0); }
.system-stat-live strong.system-ok {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--ok);
}
.system-stat-live strong.system-ok i { font-size: 12px; width: auto; }

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
    flex-wrap: wrap;
}
.settings-num {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-weight: 800; font-size: 15px;
    display: grid; place-items: center;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
}
.settings-head-info { flex: 1; min-width: 0; }
.settings-head h3 { font-size: 17px; font-weight: 800; margin-bottom: 3px; }
.settings-head p { color: var(--txt-1); font-size: 13px; margin: 0; }

/* Status pill per section */
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
    transition: all .3s;
    order: 3;
}
.settings-status-pill i { font-size: 13px; }
.settings-status-pill.modified {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.3);
    color: #fbbf24;
}
.settings-status-pill.saving {
    background: rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.3);
    color: #a5b4fc;
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
    width: 44px; height: 24px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
}
.toggle-slider::after {
    content: '';
    position: absolute;
    top: 2px; left: 2px;
    width: 18px; height: 18px;
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
.toggle-label { font-size: 12.5px; font-weight: 600; color: var(--txt-1); }

/* Settings body */
.settings-body {
    padding: 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Field enhanced */
.field-enhanced { margin-bottom: 0; }
.field-enhanced .field-label {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--txt-0);
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 12.5px;
}
.field-enhanced .field-label i { font-size: 15px; color: var(--acc); }
.field-input-wrap { position: relative; }
.field-enhanced input, .field-enhanced textarea {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
}
.field-enhanced input:focus, .field-enhanced textarea:focus {
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
.field-enhanced input:focus ~ .field-focus-ring,
.field-enhanced textarea:focus ~ .field-focus-ring {
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
.asset-dropzone.small { min-height: 120px; }
.asset-preview {
    width: 100%; height: 100%;
    min-height: 160px;
    display: grid; place-items: center;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
}
.asset-preview img { max-width: 100%; max-height: 160px; object-fit: contain; }
.asset-preview-sm { min-height: 120px; }
.asset-preview-sm img { max-height: 100px; }
.asset-placeholder { display: grid; place-items: center; }
.asset-dropzone-content {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.2s;
    color: var(--txt-1);
    pointer-events: none;
}
.asset-dropzone-content i { font-size: 28px; color: var(--acc); }
.asset-dropzone-content strong { font-size: 13px; font-weight: 700; }
.asset-dropzone-content span { font-size: 11.5px; color: var(--txt-2); }
.asset-dropzone:hover .asset-dropzone-content,
.asset-dropzone.drag-over .asset-dropzone-content { opacity: 1; }
.asset-dropzone.has-image .asset-dropzone-content { opacity: 0; }
.asset-dropzone.has-image:hover .asset-dropzone-content {
    opacity: 1;
    background: rgba(10, 15, 31, 0.75);
}

/* Asset file info */
.asset-file-info {
    position: absolute;
    bottom: 8px; left: 8px; right: 8px;
    padding: 6px 10px;
    border-radius: 8px;
    background: rgba(0,0,0,.7);
    backdrop-filter: blur(8px);
    font-size: 10.5px;
    font-weight: 600;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: none;
    z-index: 2;
}
.asset-file-info.show { display: block; }

.asset-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* Character Counter dengan Progress Bar */
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
    width: 0%;
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
.preview-label i { color: var(--acc); font-size: 14px; }
.preview-toggle-status {
    margin-left: auto;
    padding: 2px 10px;
    border-radius: 99px;
    font-size: 10px;
    font-weight: 700;
    text-transform: none;
    letter-spacing: 0;
    background: rgba(239,68,68,.12);
    border: 1px solid rgba(239,68,68,.25);
    color: #fca5a5;
}
.preview-toggle-status.active {
    background: rgba(16,185,129,.12);
    border-color: rgba(16,185,129,.3);
    color: #6ee7b7;
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
.preview-motto i { font-size: 24px; color: var(--acc); flex-shrink: 0; }
.preview-motto p {
    font-family: 'Instrument Serif', serif;
    font-size: 15px;
    font-weight: 400;
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
.preview-social-icons { display: flex; gap: 8px; flex-wrap: wrap; }
.preview-social-icons a {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid var(--glass-brd);
    display: grid; place-items: center;
    color: var(--txt-1);
    font-size: 18px;
    transition: all 0.2s;
    text-decoration: none;
}
.preview-social-icons a:hover {
    color: #fff;
    border-color: var(--pri);
    background: rgba(99,102,241,.15);
    transform: translateY(-2px);
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
    transition: opacity .3s;
}
.preview-announcement.inactive { opacity: 0.5; }
.preview-announcement i { color: var(--acc); font-size: 18px; }
.preview-announcement-link {
    margin-left: auto;
    width: 24px; height: 24px;
    border-radius: 50%;
    background: rgba(255,255,255,.1);
    display: grid; place-items: center;
    font-size: 12px;
    color: var(--acc);
}

/* Google preview */
.preview-google {
    padding: 16px;
    background: #fff;
    border-radius: 12px;
}
.google-result { font-family: Arial, sans-serif; }
.google-url { font-size: 12px; color: #202124; margin-bottom: 4px; }
.google-title {
    font-size: 18px;
    color: #1a0dab;
    font-weight: 400;
    margin-bottom: 4px;
    line-height: 1.3;
}
.google-desc { font-size: 13px; color: #4d5156; line-height: 1.5; margin: 0; }

/* Info grid */
.settings-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    transition: all .2s;
}
.settings-info-item:hover {
    border-color: var(--glass-brd-2);
    background: rgba(255,255,255,.05);
}
.settings-info-item i { font-size: 22px; color: var(--acc); }
.settings-info-item strong { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 2px; }
.settings-info-item span { font-size: 11.5px; color: var(--txt-2); font-variant-numeric: tabular-nums; }

/* Actions row */
.settings-actions-row { display: flex; gap: 10px; flex-wrap: wrap; }

/* Form divider */
.form-divider { height: 1px; background: var(--glass-brd); }

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
    background: rgba(10, 15, 31, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-brd);
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(2,6,23,.5);
    transition: all .3s;
}
.settings-sticky.has-changes {
    border-color: rgba(245,158,11,.3);
    box-shadow: 0 20px 50px rgba(2,6,23,.5), 0 0 0 1px rgba(245,158,11,.2);
}
.settings-sticky-info {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.settings-sticky-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.settings-sticky-status i { font-size: 16px; color: var(--acc); }
.settings-sticky-status.modified { color: #fbbf24; }
.settings-sticky-status.modified i { color: var(--warn); }
.settings-sticky-status.saving i { animation: spin 1s linear infinite; }
.settings-sticky-count {
    padding: 3px 10px;
    border-radius: 99px;
    background: rgba(245,158,11,.12);
    border: 1px solid rgba(245,158,11,.3);
    font-size: 11px;
    font-weight: 700;
    color: #fbbf24;
    font-variant-numeric: tabular-nums;
}
.settings-sticky-actions { display: flex; gap: 10px; }
.settings-sticky-actions .btn:disabled { opacity: 0.5; cursor: not-allowed; }

@keyframes spin { to { transform: rotate(360deg); } }

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    margin-top: 24px;
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
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 1px 0 rgba(0,0,0,.25);
}

/* ---- Danger modal ---- */
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
.danger-note-content p { margin: 0; font-size: 12px; color: var(--txt-1); line-height: 1.5; }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .settings-layout { grid-template-columns: 1fr; }
    .settings-nav {
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: 280px;
        z-index: 100;
        border-radius: 0;
        transform: translateX(-100%);
        transition: transform .3s cubic-bezier(.22,1,.36,1);
    }
    .settings-nav.open { transform: translateX(0); }
    .settings-nav-close { display: grid; }
    .asset-row { grid-template-columns: 1fr; }
    .settings-field-group { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .settings-head { padding: 18px 20px; }
    .settings-body { padding: 20px; }
    .settings-sticky {
        flex-direction: column;
        gap: 12px;
        padding: 14px 16px;
        bottom: 12px;
    }
    .settings-sticky-info { width: 100%; justify-content: center; }
    .settings-sticky-actions { width: 100%; }
    .settings-sticky-actions .btn { flex: 1; justify-content: center; }
    .keyboard-hints { display: none; }
    .last-saved { display: none; }
    .preview-label { flex-wrap: wrap; }
}

@media (max-width: 520px) {
    .settings-info-grid { grid-template-columns: 1fr; }
    .settings-actions-row { flex-direction: column; }
    .settings-actions-row .btn { width: 100%; justify-content: center; }
    .settings-num { width: 38px; height: 38px; font-size: 13px; }
    .settings-head h3 { font-size: 15px; }
}
</style>

<script>
(function(){
    'use strict';
    
    const form = document.getElementById('settingsForm');
    if (!form) return;

    /* ---- Store original values for dirty detection ---- */
    const originalValues = {};
    const sectionFields = {};
    
    form.querySelectorAll('[data-track]').forEach(el => {
        const name = el.name || el.id;
        originalValues[name] = el.type === 'checkbox' ? el.checked : el.value;
        
        // Map field to section
        const section = el.closest('.settings-card');
        if (section) {
            const sectionId = section.dataset.sectionId || section.id;
            if (!sectionFields[sectionId]) sectionFields[sectionId] = [];
            sectionFields[sectionId].push(name);
        }
    });

    /* ---- 1. Section navigation ---- */
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-card');
    const settingsNav = document.getElementById('settingsNav');
    const navOverlay = document.getElementById('settingsNavOverlay');
    const navClose = document.getElementById('settingsNavClose');
    const mobileNavBtn = document.getElementById('btnMobileNav');
    
    function openMobileNav() {
        settingsNav?.classList.add('open');
        navOverlay?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileNav() {
        settingsNav?.classList.remove('open');
        navOverlay?.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    mobileNavBtn?.addEventListener('click', openMobileNav);
    navClose?.addEventListener('click', closeMobileNav);
    navOverlay?.addEventListener('click', closeMobileNav);
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const target = document.getElementById(item.dataset.target);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                closeMobileNav();
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

    /* ---- 2. Character counters dengan progress bar ---- */
    const counters = [
        { input: 'visi', counter: 'visiCounter', bar: 'visiBar', max: 1000 },
        { input: 'misi', counter: 'misiCounter', bar: 'misiBar', max: 2000 },
        { input: 'motto', counter: 'mottoCounter', bar: 'mottoBar', max: 200 },
        { input: 'announcement_text', counter: 'announcementCounter', bar: 'announcementBar', max: 200 },
        { input: 'meta_description', counter: 'metaDescCounter', bar: 'metaDescBar', max: 160 }
    ];
    
    counters.forEach(({ input, counter, bar, max }) => {
        const inputEl = form.querySelector(`[name="${input}"]`);
        const counterEl = document.getElementById(counter);
        const barEl = document.getElementById(bar);
        if (!inputEl || !counterEl || !barEl) return;
        
        const update = () => {
            const len = inputEl.value.length;
            const pct = Math.min(100, (len / max) * 100);
            counterEl.textContent = len + ' / ' + max;
            barEl.style.width = pct + '%';
            counterEl.className = 'char-counter';
            barEl.className = 'char-progress-fill';
            if (len > max * 0.85) { counterEl.classList.add('warn'); barEl.classList.add('warn'); }
            if (len > max * 0.95) { counterEl.classList.add('danger'); barEl.classList.add('danger'); }
        };
        inputEl.addEventListener('input', update);
        update();
    });

    /* ---- 3. Live previews ---- */
    // Motto
    const mottoInput = form.querySelector('[name="motto"]');
    const previewMottoText = document.getElementById('previewMottoText');
    mottoInput?.addEventListener('input', () => {
        if (previewMottoText) previewMottoText.textContent = mottoInput.value || 'Semboyan akan muncul di sini...';
    });

    // Announcement
    const announcementInput = form.querySelector('[name="announcement_text"]');
    const announcementToggle = form.querySelector('[name="announcement_active"]');
    const previewAnnouncementText = document.getElementById('previewAnnouncementText');
    const previewAnnouncement = document.getElementById('previewAnnouncement');
    const previewBannerStatus = document.getElementById('previewBannerStatus');
    
    function updateBannerPreview() {
        if (previewAnnouncementText) {
            previewAnnouncementText.textContent = announcementInput?.value || 'Teks pengumuman akan muncul di sini...';
        }
        const isActive = announcementToggle?.checked;
        if (previewAnnouncement) {
            previewAnnouncement.classList.toggle('inactive', !isActive);
        }
        if (previewBannerStatus) {
            previewBannerStatus.textContent = isActive ? 'Aktif' : 'Nonaktif';
            previewBannerStatus.classList.toggle('active', isActive);
        }
    }
    announcementInput?.addEventListener('input', updateBannerPreview);
    announcementToggle?.addEventListener('change', updateBannerPreview);
    updateBannerPreview();

    // Google preview
    const appNameInput = form.querySelector('[name="app_name"]');
    const metaDescInput = form.querySelector('[name="meta_description"]');
    const previewGoogleTitle = document.getElementById('previewGoogleTitle');
    const previewGoogleDesc = document.getElementById('previewGoogleDesc');
    
    appNameInput?.addEventListener('input', () => {
        if (previewGoogleTitle) previewGoogleTitle.textContent = (appNameInput.value || 'Nama Organisasi') + ' — Situs Resmi';
    });
    metaDescInput?.addEventListener('input', () => {
        if (previewGoogleDesc) previewGoogleDesc.textContent = metaDescInput.value || 'Deskripsi organisasi akan muncul di sini...';
    });

    // Social icons
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
        if (socialInputs.instagram?.value) icons.push(`<a href="${socialInputs.instagram.value}" target="_blank" rel="noopener"><i class="ph ph-instagram-logo"></i></a>`);
        if (socialInputs.youtube?.value) icons.push(`<a href="${socialInputs.youtube.value}" target="_blank" rel="noopener"><i class="ph ph-youtube-logo"></i></a>`);
        if (socialInputs.email?.value) icons.push(`<a href="mailto:${socialInputs.email.value}"><i class="ph ph-envelope-simple"></i></a>`);
        if (socialInputs.phone?.value) icons.push(`<a href="tel:${socialInputs.phone.value}"><i class="ph ph-phone"></i></a>`);
        previewSocialIcons.innerHTML = icons.length > 0 ? icons.join('') : '<span class="empty">Belum ada sosial media yang diisi</span>';
    }
    Object.values(socialInputs).forEach(input => input?.addEventListener('input', updateSocialPreview));
    updateSocialPreview();

    /* ---- 4. Drag & drop asset dropzones ---- */
    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }
    
    document.querySelectorAll('.asset-dropzone').forEach(zone => {
        const inputId = zone.dataset.input;
        const input = document.getElementById(inputId);
        const preview = zone.querySelector('.asset-preview');
        const fileInfo = zone.querySelector('.asset-file-info');
        
        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('drag-over'); });
        });
        zone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file && input) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
        zone.addEventListener('click', (e) => {
            if (e.target === zone || zone.contains(e.target)) input?.click();
        });
        
        if (input) {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        zone.classList.add('has-image');
                        
                        // Show file info
                        if (fileInfo) {
                            const img = new Image();
                            img.onload = () => {
                                fileInfo.textContent = file.name + ' • ' + formatSize(file.size) + ' • ' + img.width + '×' + img.height;
                                fileInfo.classList.add('show');
                            };
                            img.src = e.target.result;
                        }
                    };
                    reader.readAsDataURL(file);
                    markDirty();
                }
            });
        }
    });

    /* ---- 5. Dirty state tracking per section ---- */
    const stickyStatus = document.getElementById('stickyStatus');
    const stickyStatusText = document.getElementById('stickyStatusText');
    const stickyChangeCount = document.getElementById('stickyChangeCount');
    const changeCountNum = document.getElementById('changeCountNum');
    const settingsSticky = document.getElementById('settingsSticky');
    const btnSave = document.getElementById('btnSaveSettings');
    const btnCancel = document.getElementById('btnCancelChanges');
    
    function getChangedSections() {
        const changed = new Set();
        let totalChanges = 0;
        
        form.querySelectorAll('[data-track]').forEach(el => {
            const name = el.name || el.id;
            const current = el.type === 'checkbox' ? el.checked : el.value;
            if (current !== originalValues[name]) {
                totalChanges++;
                // Find section
                const section = el.closest('.settings-card');
                if (section) {
                    const sectionId = section.dataset.sectionId || section.id;
                    changed.add(sectionId);
                }
            }
        });
        
        return { sections: changed, count: totalChanges };
    }
    
    function markDirty() {
        const { sections, count } = getChangedSections();
        const hasChanges = count > 0;
        
        // Update sticky bar
        if (settingsSticky) settingsSticky.classList.toggle('has-changes', hasChanges);
        if (stickyChangeCount) stickyChangeCount.style.display = hasChanges ? 'inline-flex' : 'none';
        if (changeCountNum) changeCountNum.textContent = count;
        if (btnSave) btnSave.disabled = !hasChanges;
        if (btnCancel) btnCancel.disabled = !hasChanges;
        
        if (stickyStatus) {
            stickyStatus.className = 'settings-sticky-status' + (hasChanges ? ' modified' : '');
        }
        if (stickyStatusText) {
            stickyStatusText.textContent = hasChanges ? 'Perubahan belum disimpan' : 'Tidak ada perubahan';
        }
        
        // Update section dots & pills
        document.querySelectorAll('.settings-nav-dot').forEach(dot => {
            dot.classList.toggle('modified', sections.has(dot.dataset.section));
        });
        document.querySelectorAll('[data-section-status]').forEach(pill => {
            const isModified = sections.has(pill.dataset.sectionStatus);
            pill.classList.toggle('modified', isModified);
            pill.querySelector('i').className = isModified ? 'ph ph-pencil-simple' : 'ph ph-check-circle';
            pill.querySelector('span').textContent = isModified ? 'Dimodifikasi' : 'Tersimpan';
        });
    }
    
    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    /* ---- 6. Form submit ---- */
    form.addEventListener('submit', (e) => {
        if (stickyStatus) {
            stickyStatus.className = 'settings-sticky-status saving';
            if (stickyStatusText) stickyStatusText.textContent = 'Menyimpan...';
        }
    });

    /* ---- 7. Cancel changes ---- */
    btnCancel?.addEventListener('click', () => {
        form.reset();
        // Restore original values for checkboxes
        form.querySelectorAll('[data-track]').forEach(el => {
            const name = el.name || el.id;
            if (el.type === 'checkbox') el.checked = originalValues[name];
        });
        // Reset previews
        updateBannerPreview();
        updateSocialPreview();
        if (previewMottoText) previewMottoText.textContent = originalValues['motto'] || 'Semboyan akan muncul di sini...';
        if (previewGoogleTitle) previewGoogleTitle.textContent = (originalValues['app_name'] || 'Nama Organisasi') + ' — Situs Resmi';
        if (previewGoogleDesc) previewGoogleDesc.textContent = originalValues['meta_description'] || 'Deskripsi organisasi akan muncul di sini...';
        // Reset counters
        counters.forEach(({ input, counter, bar, max }) => {
            const counterEl = document.getElementById(counter);
            const barEl = document.getElementById(bar);
            if (counterEl) { counterEl.textContent = '0 / ' + max; counterEl.className = 'char-counter'; }
            if (barEl) { barEl.style.width = '0%'; barEl.className = 'char-progress-fill'; }
        });
        markDirty();
        if (window.toast) window.toast('Perubahan dibatalkan.', 'info');
    });

    /* ---- 8. Remove asset buttons ---- */
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = btn.dataset.remove;
            if (confirm(`Hapus ${field}? Tindakan ini tidak dapat dibatalkan.`)) {
                if (window.removeAsset) window.removeAsset(field);
            }
        });
    });

    /* ---- 9. System action buttons ---- */
    document.getElementById('btnClearCache')?.addEventListener('click', () => {
        if (window.toast) window.toast('Cache berhasil dibersihkan.', 'success');
    });
    
    document.getElementById('btnExportSettings')?.addEventListener('click', () => {
        const data = {};
        new FormData(form).forEach((value, key) => data[key] = value);
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'settings-' + new Date().toISOString().slice(0,10) + '.json';
        a.click();
        URL.revokeObjectURL(url);
        if (window.toast) window.toast('Pengaturan berhasil diekspor.', 'success');
    });

    /* ---- 10. Reset modal (premium confirmation) ---- */
    const resetModal = document.getElementById('resetModal');
    const btnResetSettings = document.getElementById('btnResetSettings');
    const btnConfirmReset = document.getElementById('btnConfirmReset');
    
    function openResetModal() { resetModal?.classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closeResetModal() { resetModal?.classList.remove('show'); document.body.style.overflow = ''; }
    
    btnResetSettings?.addEventListener('click', openResetModal);
    resetModal?.querySelector('[data-close-modal]')?.addEventListener('click', closeResetModal);
    resetModal?.addEventListener('click', (e) => { if (e.target === resetModal) closeResetModal(); });
    btnConfirmReset?.addEventListener('click', () => {
        if (window.toast) window.toast('Fitur reset akan segera tersedia.', 'info');
        closeResetModal();
    });

    /* ---- 11. Server time update ---- */
    const serverTime = document.getElementById('serverTime');
    if (serverTime) {
        setInterval(() => {
            const now = new Date();
            serverTime.textContent = now.toLocaleString('id-ID', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }, 1000);
    }

    /* ---- 12. Keyboard shortcuts ---- */
    document.getElementById('kbdSave')?.addEventListener('click', () => {
        if (btnSave && !btnSave.disabled) form.requestSubmit();
    });
    
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
        
        // Esc closes modals
        if (e.key === 'Escape') {
            if (resetModal?.classList.contains('show')) { closeResetModal(); return; }
            if (settingsNav?.classList.contains('open')) { closeMobileNav(); return; }
        }
        
        // Ctrl+S = save
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            if (btnSave && !btnSave.disabled) form.requestSubmit();
            return;
        }
        
        if (inInput) return;
        
        // 1-6 = jump to section
        const num = parseInt(e.key);
        if (num >= 1 && num <= 6 && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            const target = document.getElementById('section-' + num);
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    /* ---- Initial state ---- */
    markDirty();
})();
</script>