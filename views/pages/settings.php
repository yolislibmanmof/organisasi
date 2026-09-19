<!-- File: views/pages/settings.php (FINAL - TAHAP 5.6) -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Konfigurasi</div>
        <h2 class="page-title">Pengaturan Situs</h2>
        <p class="page-sub">Kelola identitas visual, visi-misi, dan informasi kontak organisasi.</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('') ?>" target="_blank" class="btn btn-ghost">
            <i class="ph ph-eye"></i><span class="btn-text">Lihat Situs Publik</span>
        </a>
    </div>
</section>

<form id="settingsForm" class="settings-stack">
    <?= csrf_field() ?>

    <!-- IDENTITAS ORGANISASI -->
    <section class="glass-card settings-card">
        <div class="settings-head">
            <span class="settings-num">01</span>
            <div>
                <h3>Identitas Organisasi</h3>
                <p>Nama, logo, dan favicon yang tampil di seluruh sistem.</p>
            </div>
        </div>
        <div class="settings-body">
            <label class="field">
                <span class="field-label">Nama Organisasi <em>*</em></span>
                <input type="text" name="app_name" value="<?= e($settings['app_name'] ?? '') ?>" required>
            </label>

            <div class="field-row">
                <div class="asset-field">
                    <span class="field-label">Logo Navbar</span>
                    <div class="asset-preview" id="logoPreview">
                        <?php if (!empty($settings['logo'])): ?>
                            <img src="<?= url('assets/uploads/brand/' . e($settings['logo'])) ?>" alt="Logo">
                        <?php else: ?>
                            <span class="logo-mark">OU</span>
                        <?php endif; ?>
                    </div>
                    <div class="asset-actions">
                        <label class="btn btn-ghost btn-xs">
                            <i class="ph ph-upload-simple"></i><span class="btn-text">Pilih Berkas</span>
                            <input type="file" name="logo" accept="image/*" hidden id="logoInput">
                        </label>
                        <?php if (!empty($settings['logo'])): ?>
                            <button type="button" class="btn btn-ghost btn-xs btn-danger-ghost" data-remove="logo">
                                <i class="ph ph-trash"></i><span class="btn-text">Hapus</span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <small class="asset-hint">PNG/JPG/WEBP, maks 10 MB. Kosongkan untuk logo default.</small>
                </div>

                <div class="asset-field">
                    <span class="field-label">Favicon (Ikon Tab)</span>
                    <div class="asset-preview asset-preview-sm" id="faviconPreview">
                        <?php if (!empty($settings['favicon'])): ?>
                            <img src="<?= url('assets/uploads/brand/' . e($settings['favicon'])) ?>" alt="Favicon">
                        <?php else: ?>
                            <span class="logo-mark" style="width:32px;height:32px;font-size:12px;border-radius:8px">OU</span>
                        <?php endif; ?>
                    </div>
                    <div class="asset-actions">
                        <label class="btn btn-ghost btn-xs">
                            <i class="ph ph-upload-simple"></i><span class="btn-text">Pilih Berkas</span>
                            <input type="file" name="favicon" accept="image/*,.ico" hidden id="faviconInput">
                        </label>
                        <?php if (!empty($settings['favicon'])): ?>
                            <button type="button" class="btn btn-ghost btn-xs btn-danger-ghost" data-remove="favicon">
                                <i class="ph ph-trash"></i><span class="btn-text">Hapus</span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <small class="asset-hint">PNG/ICO, maks 10 MB. Disarankan persegi.</small>
                </div>
            </div>
        </div>
    </section>

    <!-- VISI & MISI + PERIODE KABINET + SEMBOYAN -->
    <section class="glass-card settings-card">
        <div class="settings-head">
            <span class="settings-num">02</span>
            <div>
                <h3>Visi, Misi & Identitas Kabinet</h3>
                <p>Tampil pada seksi "Tentang Kami" di beranda publik.</p>
            </div>
        </div>
        <div class="settings-body">
            <label class="field">
                <span class="field-label">Periode Kabinet</span>
                <input type="text" name="cabinet_period" value="<?= e($settings['cabinet_period'] ?? '') ?>" placeholder="Contoh: Kabinet Askara Periode 2024-2025">
                <small class="asset-hint">Tampil sebagai judul seksi visi-misi di beranda.</small>
            </label>
            <label class="field">
                <span class="field-label">Visi</span>
                <textarea name="visi" rows="3" placeholder="Pernyataan visi organisasi…"><?= e($settings['visi'] ?? '') ?></textarea>
            </label>
            <label class="field">
                <span class="field-label">Misi</span>
                <textarea name="misi" rows="6" placeholder="Satu misi per baris…"><?= e($settings['misi'] ?? '') ?></textarea>
                <small class="asset-hint">Tulis setiap misi pada baris terpisah.</small>
            </label>
            <label class="field">
                <span class="field-label">Semboyan Organisasi</span>
                <input type="text" name="motto" value="<?= e($settings['motto'] ?? '') ?>" placeholder="Contoh: Ngabdi Ka Nagri Bela Ka Nagara">
                <small class="asset-hint">Tampil sebagai kutipan inspiratif di bawah visi-misi.</small>
            </label>
        </div>
    </section>

    <!-- SOSIAL MEDIA & KONTAK -->
    <section class="glass-card settings-card">
        <div class="settings-head">
            <span class="settings-num">03</span>
            <div>
                <h3>Sosial Media & Kontak</h3>
                <p>Tampil di footer halaman publik.</p>
            </div>
        </div>
        <div class="settings-body">
            <div class="field-row">
                <label class="field">
                    <span class="field-label"><i class="ph ph-instagram-logo"></i> Instagram</span>
                    <input type="url" name="social_instagram" value="<?= e($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
                </label>
                <label class="field">
                    <span class="field-label"><i class="ph ph-youtube-logo"></i> YouTube</span>
                    <input type="url" name="social_youtube" value="<?= e($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@...">
                </label>
            </div>
            <div class="field-row">
                <label class="field">
                    <span class="field-label"><i class="ph ph-envelope-simple"></i> Email</span>
                    <input type="email" name="social_email" value="<?= e($settings['social_email'] ?? '') ?>">
                </label>
                <label class="field">
                    <span class="field-label"><i class="ph ph-phone"></i> Telepon</span>
                    <input type="text" name="social_phone" value="<?= e($settings['social_phone'] ?? '') ?>">
                </label>
            </div>
            <label class="field">
                <span class="field-label"><i class="ph ph-map-pin"></i> Alamat</span>
                <input type="text" name="social_address" value="<?= e($settings['social_address'] ?? '') ?>">
            </label>
        </div>
    </section>

    <div class="settings-sticky">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="ph ph-floppy-disk"></i>
            <span class="btn-text">Simpan Seluruh Pengaturan</span>
        </button>
    </div>
</form>

<div class="toast-zone" id="toastZone"></div>
<script src="<?= asset('js/settings.js') ?>"></script>