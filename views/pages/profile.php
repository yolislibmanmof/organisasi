<?php
/**
 * ============================================================
 * HALAMAN PROFIL SAYA — ULTIMATE EDITION v7.0
 * Kelola informasi pribadi, keamanan akun, dan sesi aktif
 * dengan profile completion, live session, dan premium UX.
 * ============================================================
 */

$profile = $profile ?? [];
$loginAt = $loginAt ?? time();
$account = $account ?? $user ?? [];

/* ---- Hitung profile completion ---- */
$completionFields = [
    !empty($user['name']),
    !empty($account['email']),
    !empty($profile['phone'] ?? ''),
    !empty($profile['address'] ?? ''),
    !empty($user['photo'] ?? ''),
];
$completion = (int) round((count(array_filter($completionFields)) / count($completionFields)) * 100);
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Akun & Keamanan</div>
        <h2 class="page-title">
            <i class="ph ph-user-circle-gear" style="color:var(--acc);margin-right:8px"></i>
            Profil Saya
        </h2>
        <p class="page-sub">Kelola informasi pribadi, foto profil, dan keamanan akun Anda.</p>
    </div>
    <div class="page-head-actions">
        <span class="last-saved" id="lastSaved" title="Waktu penyimpanan terakhir" style="display:none">
            <i class="ph ph-check-circle"></i>
            <span>Tersimpan</span>
        </span>
        <a href="<?= url('logout') ?>" class="btn btn-danger-ghost">
            <i class="ph ph-sign-out"></i>
            <span class="btn-text">Keluar</span>
        </a>
    </div>
</section>

<!-- ========== PROFILE HERO CARD ========== -->
<section class="profile-hero-card glass-card reveal">
    <!-- Animated cover -->
    <div class="profile-cover">
        <div class="profile-cover-pattern"></div>
        <div class="profile-cover-orbs">
            <span class="cover-orb orb-1"></span>
            <span class="cover-orb orb-2"></span>
            <span class="cover-orb orb-3"></span>
        </div>
    </div>
    
    <!-- Completion ring + verification -->
    <div class="profile-hero-badges">
        <div class="profile-completion-ring" title="Kelengkapan profil: <?= $completion ?>%">
            <svg viewBox="0 0 36 36">
                <circle class="ring-bg" cx="18" cy="18" r="15.9"/>
                <circle class="ring-fg" cx="18" cy="18" r="15.9" style="--pct: <?= $completion ?>"/>
            </svg>
            <div class="completion-inner">
                <strong><?= $completion ?>%</strong>
                <span>Lengkap</span>
            </div>
        </div>
        <span class="profile-verified-badge">
            <i class="ph-fill ph-seal-check"></i>
            <span>Terverifikasi</span>
        </span>
    </div>

    <!-- Hero body -->
    <div class="profile-hero-body">
        <div class="profile-avatar-wrap">
            <div class="profile-avatar-ring">
                <?= avatar_tag($user, 'avatar-xl') ?>
            </div>
            <label class="profile-avatar-edit" for="photoInput" title="Ganti foto profil">
                <i class="ph ph-camera"></i>
            </label>
            <span class="profile-avatar-status" title="Online"></span>
        </div>
        
        <div class="profile-hero-info">
            <h2 class="profile-hero-name"><?= e($user['name']) ?></h2>
            <div class="profile-hero-handle">
                <span class="profile-handle">@<?= e($user['username']) ?></span>
                <span class="profile-role-badge">
                    <i class="ph ph-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?>"></i>
                    <?= e(ucfirst($user['role'])) ?>
                </span>
            </div>
            <div class="profile-hero-meta">
                <span class="profile-meta-item">
                    <i class="ph ph-envelope-simple"></i>
                    <?= e($account['email']) ?>
                </span>
                <span class="profile-meta-item">
                    <i class="ph ph-calendar-blank"></i>
                    Anggota sejak <?= $profile ? date('d M Y', strtotime($profile['join_date'])) : '—' ?>
                </span>
                <span class="profile-meta-item online">
                    <span class="profile-meta-dot"></span>
                    Login <time id="loginTime" datetime="<?= date('c', $loginAt) ?>"><?= date('d M Y, H:i', $loginAt) ?></time>
                </span>
            </div>
        </div>
    </div>

    <!-- Quick stats bar (animated counters) -->
    <div class="profile-stats-bar">
        <div class="profile-stat">
            <i class="ph ph-sign-in"></i>
            <div>
                <strong data-count="<?= $profile['login_count'] ?? 1 ?>">0</strong>
                <span>Total Login</span>
            </div>
        </div>
        <div class="profile-stat-sep"></div>
        <div class="profile-stat">
            <i class="ph ph-clock-countdown"></i>
            <div>
                <strong id="sessionDuration">Menghitung...</strong>
                <span>Durasi Sesi</span>
            </div>
        </div>
        <div class="profile-stat-sep"></div>
        <div class="profile-stat">
            <i class="ph ph-shield-check"></i>
            <div>
                <strong class="ok-text" id="securityScore">Aman</strong>
                <span>Status Keamanan</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== MAIN CONTENT GRID ========== -->
<section class="profile-grid">
    
    <!-- LEFT COLUMN -->
    <div class="profile-main-col">
        
        <!-- Informasi Pribadi -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head">
                <div class="profile-section-title">
                    <span class="profile-section-icon">
                        <i class="ph ph-identification-card"></i>
                    </span>
                    <div>
                        <h3>Informasi Pribadi</h3>
                        <p>Data diri yang tampil di profil dan sistem</p>
                    </div>
                </div>
                <span class="profile-section-badge" id="profileBadge">
                    <i class="ph ph-pencil-simple"></i>
                    Dapat diubah
                </span>
            </div>
            
            <form id="profileForm" class="profile-form" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Avatar Upload Zone dengan drag & drop -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar-preview" id="avatarPreview">
                        <?= avatar_tag($user, 'avatar-lg') ?>
                    </div>
                    <div class="profile-avatar-controls">
                        <div class="profile-avatar-actions">
                            <label class="btn btn-ghost btn-sm" for="photoInput">
                                <i class="ph ph-upload-simple"></i>
                                <span class="btn-text">Ganti Foto</span>
                            </label>
                            <button type="button" class="btn btn-ghost btn-sm" id="btnRemovePhoto" style="display:none">
                                <i class="ph ph-trash"></i>
                                <span class="btn-text">Hapus</span>
                            </button>
                            <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" hidden>
                        </div>
                        <div class="avatar-dropzone" id="avatarDropzone">
                            <i class="ph ph-cloud-arrow-up"></i>
                            <span>Seret foto ke sini atau klik tombol di atas</span>
                        </div>
                        <div class="profile-avatar-hint">
                            <i class="ph ph-info"></i>
                            <span>JPG, PNG, atau WEBP — maksimal 2 MB. Disarankan persegi (500×500px).</span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-user"></i> Data Utama
                    </div>
                    <label class="field">
                        <span class="field-label">Nama Lengkap <em>*</em></span>
                        <input type="text" name="full_name" value="<?= e($user['name']) ?>" required data-track>
                        <span class="field-error" data-error="full_name"></span>
                    </label>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Email <em>*</em></span>
                            <input type="email" name="email" value="<?= e($account['email']) ?>" required data-track>
                            <span class="field-error" data-error="email"></span>
                        </label>
                        <label class="field">
                            <span class="field-label">No. Telepon</span>
                            <input type="text" name="phone" value="<?= e($profile['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx" data-track>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-map-pin"></i> Alamat
                    </div>
                    <label class="field">
                        <span class="field-label">Alamat Lengkap</span>
                        <textarea name="address" rows="3" placeholder="Jl. Merdeka No. 1, Kota…" maxlength="500" data-track><?= e($profile['address'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Opsional — untuk keperluan korespondensi.</small>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="addressCounter">0 / 500</span>
                                <div class="char-progress"><div class="char-progress-fill" id="addressBar"></div></div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="profile-form-actions">
                    <button type="reset" class="btn btn-ghost" id="btnResetProfile" disabled>
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span class="btn-text">Reset</span>
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSaveProfile" disabled>
                        <i class="ph ph-floppy-disk"></i>
                        <span class="btn-text">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- Keamanan Akun -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head">
                <div class="profile-section-title">
                    <span class="profile-section-icon security">
                        <i class="ph ph-lock-key"></i>
                    </span>
                    <div>
                        <h3>Keamanan Akun</h3>
                        <p>Ubah kata sandi untuk menjaga keamanan akun Anda</p>
                    </div>
                </div>
            </div>
            
            <form id="passwordForm" class="profile-form">
                <?= csrf_field() ?>
                
                <label class="field">
                    <span class="field-label">Kata Sandi Saat Ini <em>*</em></span>
                    <div class="pass-wrap">
                        <input type="password" name="current_password" id="currentPass" required autocomplete="current-password">
                        <button type="button" class="pass-toggle" data-target="currentPass" aria-label="Tampilkan">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                    <span class="field-error" data-error="current_password"></span>
                </label>

                <div class="form-divider"></div>

                <label class="field">
                    <span class="field-label">Kata Sandi Baru <em>*</em></span>
                    <div class="pass-wrap">
                        <input type="password" name="new_password" id="newPassword" required autocomplete="new-password">
                        <button type="button" class="pass-toggle" data-target="newPassword" aria-label="Tampilkan">
                            <i class="ph ph-eye"></i>
                        </button>
                    </div>
                    <div class="strength-meter" id="strengthMeter">
                        <span class="strength-bar"></span>
                        <span class="strength-bar"></span>
                        <span class="strength-bar"></span>
                        <span class="strength-bar"></span>
                    </div>
                    <div class="strength-info">
                        <div class="strength-header">
                            <span class="strength-label" id="strengthLabel">Masukkan kata sandi baru</span>
                            <span class="strength-score" id="strengthScore">0/5</span>
                        </div>
                        <ul class="password-requirements" id="passwordRequirements">
                            <li data-req="length"><i class="ph ph-circle"></i> Minimal 8 karakter</li>
                            <li data-req="upper"><i class="ph ph-circle"></i> Huruf besar (A-Z)</li>
                            <li data-req="lower"><i class="ph ph-circle"></i> Huruf kecil (a-z)</li>
                            <li data-req="number"><i class="ph ph-circle"></i> Angka (0-9)</li>
                            <li data-req="special"><i class="ph ph-circle"></i> Karakter spesial (!@#)</li>
                        </ul>
                    </div>
                    <span class="field-error" data-error="new_password"></span>
                </label>

                <label class="field">
                    <span class="field-label">Konfirmasi Kata Sandi Baru <em>*</em></span>
                    <div class="pass-wrap">
                        <input type="password" name="confirm_password" id="confirmPass" required autocomplete="new-password">
                        <button type="button" class="pass-toggle" data-target="confirmPass" aria-label="Tampilkan">
                            <i class="ph ph-eye"></i>
                        </button>
                        <span class="field-match-icon" id="matchIcon"></span>
                    </div>
                    <span class="field-error" data-error="confirm_password"></span>
                </label>

                <div class="profile-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-key"></i>
                        <span class="btn-text">Ubah Kata Sandi</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- Danger Zone -->
        <section class="glass-card profile-section danger-zone reveal">
            <div class="profile-section-head">
                <div class="profile-section-title">
                    <span class="profile-section-icon danger">
                        <i class="ph ph-warning-octagon"></i>
                    </span>
                    <div>
                        <h3>Zona Berbahaya</h3>
                        <p>Tindakan permanen yang tidak dapat dibatalkan</p>
                    </div>
                </div>
            </div>
            <div class="danger-zone-content">
                <div class="danger-item">
                    <div class="danger-item-info">
                        <strong>Hapus Akun Saya</strong>
                        <p>Menghapus akun akan menghapus semua data Anda secara permanen termasuk riwayat aktivitas.</p>
                    </div>
                    <button type="button" class="btn btn-danger-ghost" id="btnDeleteAccount">
                        <i class="ph ph-trash"></i>
                        <span class="btn-text">Hapus Akun</span>
                    </button>
                </div>
            </div>
        </section>
    </div>

    <!-- RIGHT COLUMN -->
    <aside class="profile-side-col">
        
        <!-- Sesi Aktif -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-device-mobile"></i> Sesi Aktif</h3>
            </div>
            <ul class="session-list">
                <li class="session-current">
                    <div class="session-icon">
                        <i class="ph ph-desktop" id="uaIcon"></i>
                        <span class="session-current-badge">Saat ini</span>
                    </div>
                    <div class="session-info">
                        <strong id="uaName">Mendeteksi...</strong>
                        <span id="uaVersion" style="display:none"></span>
                        <span><?= e($_SERVER['REMOTE_ADDR'] ?? '—') ?></span>
                    </div>
                </li>
                <li>
                    <div class="session-icon">
                        <i class="ph ph-calendar-check"></i>
                    </div>
                    <div class="session-info">
                        <strong>Login sejak</strong>
                        <span><?= date('d M Y, H:i', $loginAt) ?></span>
                    </div>
                </li>
                <li>
                    <div class="session-icon">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div class="session-info">
                        <strong>Status sesi</strong>
                        <span class="ok-text">
                            <i class="ph ph-check-circle"></i>
                            Aktif & aman
                        </span>
                    </div>
                </li>
            </ul>
        </section>

        <!-- Tips Keamanan + Security Score -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-lightbulb"></i> Tips Keamanan</h3>
            </div>
            
            <!-- Security Score -->
            <div class="security-score-card">
                <div class="security-score-ring">
                    <svg viewBox="0 0 36 36">
                        <circle class="ring-bg" cx="18" cy="18" r="15.9"/>
                        <circle class="ring-fg score-fg" cx="18" cy="18" r="15.9" id="securityRing" style="--pct: 75"/>
                    </svg>
                    <div class="score-inner">
                        <strong id="securityScoreNum">75</strong>
                    </div>
                </div>
                <div class="security-score-info">
                    <strong id="securityScoreLabel">Baik</strong>
                    <span>Skor keamanan akun Anda</span>
                </div>
            </div>
            
            <div class="security-tips">
                <div class="security-tip tip-ok" data-tip="password">
                    <span class="tip-icon success"><i class="ph ph-check"></i></span>
                    <span>Gunakan kata sandi yang unik dan kuat</span>
                </div>
                <div class="security-tip tip-warn" data-tip="share">
                    <span class="tip-icon"><i class="ph ph-warning"></i></span>
                    <span>Jangan bagikan kredensial login Anda</span>
                </div>
                <div class="security-tip tip-warn" data-tip="logout">
                    <span class="tip-icon"><i class="ph ph-warning"></i></span>
                    <span>Logout dari perangkat publik setelah selesai</span>
                </div>
                <div class="security-tip tip-ok" data-tip="verify">
                    <span class="tip-icon success"><i class="ph ph-check"></i></span>
                    <span>Verifikasi email untuk proteksi tambahan</span>
                </div>
                <div class="security-tip tip-new" data-tip="2fa">
                    <span class="tip-icon info"><i class="ph ph-shield-star"></i></span>
                    <span>Aktifkan verifikasi 2 langkah (segera)</span>
                </div>
            </div>
        </section>

        <!-- Activity Log (Dinamis) -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-clock-clockwise"></i> Aktivitas Terbaru</h3>
            </div>
            <div class="activity-timeline" id="activityTimeline">
                <div class="activity-item">
                    <span class="activity-icon"><i class="ph ph-sign-in"></i></span>
                    <div class="activity-info">
                        <strong>Login berhasil</strong>
                        <span class="activity-time" data-time="<?= $loginAt ?>"><?= date('d M Y, H:i', $loginAt) ?></span>
                    </div>
                </div>
                <div class="activity-item">
                    <span class="activity-icon"><i class="ph ph-user-circle"></i></span>
                    <div class="activity-info">
                        <strong>Profil diakses</strong>
                        <span class="activity-time" data-time="<?= time() ?>">Baru saja</span>
                    </div>
                </div>
                <div class="activity-item">
                    <span class="activity-icon"><i class="ph ph-check-circle"></i></span>
                    <div class="activity-info">
                        <strong>Akun diverifikasi</strong>
                        <span><?= $profile ? date('d M Y', strtotime($profile['join_date'])) : '—' ?></span>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</section>

<!-- ========== DELETE ACCOUNT MODAL (Premium) ========== -->
<div class="modal-backdrop" id="deleteAccountModal" role="dialog" aria-labelledby="deleteAccountTitle" aria-modal="true">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-trash"></i></span>
            </div>
            <div>
                <h3 id="deleteAccountTitle">Hapus Akun Permanen?</h3>
                <p class="modal-sub">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="delete-warning-list">
                <div class="delete-warning-item">
                    <i class="ph ph-x-circle"></i>
                    <span>Semua data profil dan foto akan dihapus</span>
                </div>
                <div class="delete-warning-item">
                    <i class="ph ph-x-circle"></i>
                    <span>Riwayat aktivitas tidak dapat dipulihkan</span>
                </div>
                <div class="delete-warning-item">
                    <i class="ph ph-x-circle"></i>
                    <span>Akses ke dashboard akan dicabut permanen</span>
                </div>
            </div>
            <label class="field" style="margin-top: 16px;">
                <span class="field-label">Ketik <strong style="color:var(--danger)">HAPUS</strong> untuk konfirmasi</span>
                <input type="text" id="deleteConfirmInput" placeholder="HAPUS" autocomplete="off">
            </label>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmDeleteAccount" disabled>
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus Akun Saya</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdSave"><kbd>Ctrl</kbd>+<kbd>S</kbd> Simpan Profil</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup Modal</span>
</div>

<!-- Toast Zone -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/profile.js') ?>"></script>

<!-- ========== ENHANCER v7.0 (self-contained) ========== -->
<script>
(function(){
    'use strict';

    /* ---- Helper: Format bytes ---- */
    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    /* ---- 1. Counter animation untuk stats ---- */
    const counters = document.querySelectorAll('.profile-stat strong[data-count]');
    if (counters.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (!en.isIntersecting) return;
                const el = en.target;
                const target = parseInt(el.dataset.count, 10) || 0;
                const t0 = performance.now(), dur = 1000;
                const tick = (t) => {
                    const p = Math.min(1, (t - t0) / dur);
                    const v = Math.floor(target * (1 - Math.pow(1 - p, 3)));
                    el.textContent = v.toLocaleString('id-ID');
                    if (p < 1) requestAnimationFrame(tick);
                    else el.textContent = target.toLocaleString('id-ID');
                };
                requestAnimationFrame(tick);
                obs.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(c => obs.observe(c));
    }

    /* ---- 2. Live session duration (per detik) ---- */
    const sessionEl = document.getElementById('sessionDuration');
    if (sessionEl) {
        const loginAt = <?= $loginAt ?> * 1000;
        const update = () => {
            const diff = Date.now() - loginAt;
            const hours = Math.floor(diff / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            if (hours > 0) sessionEl.textContent = `${hours}j ${mins}m ${secs}d`;
            else if (mins > 0) sessionEl.textContent = `${mins}m ${secs}d`;
            else sessionEl.textContent = `${secs} detik`;
        };
        update();
        setInterval(update, 1000);
    }

    /* ---- 3. User agent detection (lebih akurat) ---- */
    const uaEl = document.getElementById('uaName');
    const uaVersion = document.getElementById('uaVersion');
    const uaIcon = document.getElementById('uaIcon');
    if (uaEl) {
        const ua = navigator.userAgent;
        let browser = 'Peramban', version = '', os = '', icon = 'ph-desktop';
        
        if (ua.includes('Edg/')) {
            browser = 'Microsoft Edge'; version = ua.match(/Edg\/([\d.]+)/)?.[1] || '';
            icon = 'ph-microsoft-edge-logo';
        } else if (ua.includes('Chrome/') && !ua.includes('Edg')) {
            browser = 'Google Chrome'; version = ua.match(/Chrome\/([\d.]+)/)?.[1] || '';
            icon = 'ph-google-chrome-logo';
        } else if (ua.includes('Firefox/')) {
            browser = 'Mozilla Firefox'; version = ua.match(/Firefox\/([\d.]+)/)?.[1] || '';
            icon = 'ph-firefox-logo';
        } else if (ua.includes('Safari/') && !ua.includes('Chrome')) {
            browser = 'Safari'; version = ua.match(/Version\/([\d.]+)/)?.[1] || '';
            icon = 'ph-safari-logo';
        } else if (ua.includes('OPR/') || ua.includes('Opera')) {
            browser = 'Opera'; version = ua.match(/(OPR|Opera)\/([\d.]+)/)?.[2] || '';
            icon = 'ph-opera-logo';
        }
        
        if (ua.includes('Windows')) os = 'Windows';
        else if (ua.includes('Mac')) os = 'macOS';
        else if (ua.includes('Android')) os = 'Android';
        else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS';
        else if (ua.includes('Linux')) os = 'Linux';
        
        uaEl.textContent = browser + (os ? ' di ' + os : '');
        if (version && uaVersion) {
            uaVersion.textContent = 'v' + version.split('.')[0];
            uaVersion.style.display = 'block';
        }
        if (uaIcon) uaIcon.className = 'ph ' + icon;
    }

    /* ---- 4. Avatar preview + drag & drop ---- */
    const photoInput = document.getElementById('photoInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const removeBtn = document.getElementById('btnRemovePhoto');
    const dropzone = document.getElementById('avatarDropzone');

    function handleAvatarFile(file) {
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            if (window.toast) window.toast('Ukuran file melebihi 2 MB.', 'error');
            return;
        }
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            if (window.toast) window.toast('Format file tidak didukung.', 'error');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            if (avatarPreview) {
                avatarPreview.innerHTML = `<div class="avatar avatar-lg" style="background-image:url('${e.target.result}');background-size:cover;background-position:center;"></div>`;
            }
            if (removeBtn) removeBtn.style.display = 'inline-flex';
            markProfileDirty();
        };
        reader.readAsDataURL(file);
    }

    if (photoInput) {
        photoInput.addEventListener('change', () => handleAvatarFile(photoInput.files[0]));
    }

    if (dropzone && photoInput) {
        dropzone.addEventListener('click', () => photoInput.click());
        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.add('drag-over');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
            });
        });
        dropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer.files[0];
            if (file) {
                photoInput.files = e.dataTransfer.files;
                handleAvatarFile(file);
            }
        });
    }

    /* ---- 5. Character counter dengan progress bar ---- */
    const addressField = document.querySelector('textarea[name="address"]');
    const addressCounter = document.getElementById('addressCounter');
    const addressBar = document.getElementById('addressBar');
    
    if (addressField && addressCounter && addressBar) {
        const update = () => {
            const len = addressField.value.length;
            const max = 500;
            const pct = Math.min(100, (len / max) * 100);
            addressCounter.textContent = len + ' / ' + max;
            addressBar.style.width = pct + '%';
            addressCounter.className = 'char-counter';
            addressBar.className = 'char-progress-fill';
            if (len > max * 0.85) { addressCounter.classList.add('warn'); addressBar.classList.add('warn'); }
            if (len > max * 0.95) { addressCounter.classList.add('danger'); addressBar.classList.add('danger'); }
        };
        addressField.addEventListener('input', () => { update(); markProfileDirty(); });
        update();
    }

    /* ---- 6. Password toggle ---- */
    document.querySelectorAll('.pass-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            btn.querySelector('i').className = isPass ? 'ph ph-eye-slash' : 'ph ph-eye';
        });
    });

    /* ---- 7. Password strength + requirements (dengan score) ---- */
    const newPass = document.getElementById('newPassword');
    const confirmPass = document.getElementById('confirmPass');
    const strengthMeter = document.getElementById('strengthMeter');
    const strengthLabel = document.getElementById('strengthLabel');
    const strengthScore = document.getElementById('strengthScore');
    const matchIcon = document.getElementById('matchIcon');
    const reqs = document.querySelectorAll('.password-requirements li');
    
    if (newPass && strengthMeter) {
        const bars = strengthMeter.querySelectorAll('.strength-bar');
        
        newPass.addEventListener('input', () => {
            const val = newPass.value;
            
            const checks = {
                length: val.length >= 8,
                upper: /[A-Z]/.test(val),
                lower: /[a-z]/.test(val),
                number: /[0-9]/.test(val),
                special: /[^A-Za-z0-9]/.test(val)
            };
            
            reqs.forEach(req => {
                const key = req.dataset.req;
                const met = checks[key] || false;
                req.classList.toggle('met', met);
                req.querySelector('i').className = met ? 'ph ph-check-circle' : 'ph ph-circle';
            });
            
            let score = Object.values(checks).filter(Boolean).length;
            
            if (strengthScore) strengthScore.textContent = score + '/5';
            
            if (val.length === 0) {
                bars.forEach(b => b.className = 'strength-bar');
                if (strengthLabel) {
                    strengthLabel.textContent = 'Masukkan kata sandi baru';
                    strengthLabel.className = 'strength-label';
                }
                return;
            }
            
            const levels = ['', 'weak', 'weak', 'fair', 'good', 'strong'];
            const labels = ['', 'Sangat lemah', 'Lemah', 'Cukup kuat', 'Baik', 'Sangat kuat'];
            const level = levels[score] || 'weak';
            
            bars.forEach((bar, i) => {
                bar.className = 'strength-bar';
                if (i < score) bar.classList.add('active', level);
            });
            
            if (strengthLabel) {
                strengthLabel.textContent = labels[score] || '';
                strengthLabel.className = 'strength-label ' + level;
            }
        });
    }
    
    if (confirmPass && matchIcon) {
        confirmPass.addEventListener('input', () => {
            if (!confirmPass.value) {
                matchIcon.className = 'field-match-icon';
                matchIcon.innerHTML = '';
                return;
            }
            if (confirmPass.value === newPass.value) {
                matchIcon.className = 'field-match-icon match';
                matchIcon.innerHTML = '<i class="ph ph-check-circle"></i>';
            } else {
                matchIcon.className = 'field-match-icon mismatch';
                matchIcon.innerHTML = '<i class="ph ph-x-circle"></i>';
            }
        });
    }

    /* ---- 8. Profile form dirty state detection ---- */
    const profileForm = document.getElementById('profileForm');
    const btnSaveProfile = document.getElementById('btnSaveProfile');
    const btnResetProfile = document.getElementById('btnResetProfile');
    const profileBadge = document.getElementById('profileBadge');
    const originalValues = {};
    
    if (profileForm) {
        profileForm.querySelectorAll('[data-track]').forEach(el => {
            originalValues[el.name] = el.value;
        });
        
        window.markProfileDirty = function() {
            let dirty = false;
            profileForm.querySelectorAll('[data-track]').forEach(el => {
                if (el.value !== originalValues[el.name]) dirty = true;
            });
            // Avatar also makes it dirty
            if (photoInput?.files?.length > 0) dirty = true;
            
            if (btnSaveProfile) btnSaveProfile.disabled = !dirty;
            if (btnResetProfile) btnResetProfile.disabled = !dirty;
            if (profileBadge) {
                if (dirty) {
                    profileBadge.innerHTML = '<i class="ph ph-pencil-simple"></i> Dimodifikasi';
                    profileBadge.classList.add('modified');
                } else {
                    profileBadge.innerHTML = '<i class="ph ph-pencil-simple"></i> Dapat diubah';
                    profileBadge.classList.remove('modified');
                }
            }
        };
        
        profileForm.querySelectorAll('[data-track]').forEach(el => {
            el.addEventListener('input', window.markProfileDirty);
        });
        
        profileForm.addEventListener('submit', () => {
            if (btnSaveProfile) {
                btnSaveProfile.innerHTML = '<i class="ph ph-spinner" style="animation:spin 1s linear infinite"></i><span class="btn-text">Menyimpan...</span>';
                btnSaveProfile.disabled = true;
            }
        });
        
        btnResetProfile?.addEventListener('click', () => {
            setTimeout(() => {
                profileForm.querySelectorAll('[data-track]').forEach(el => {
                    el.value = originalValues[el.name] || '';
                });
                window.markProfileDirty();
                if (window.toast) window.toast('Perubahan dibatalkan.', 'info');
            }, 10);
        });
    }

    /* ---- 9. Relative time untuk activity log ---- */
    function relativeTime(ts) {
        const diff = Math.floor((Date.now() / 1000) - ts);
        if (diff < 60) return 'Baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
        return new Date(ts * 1000).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    
    document.querySelectorAll('.activity-time[data-time]').forEach(el => {
        const ts = parseInt(el.dataset.time, 10);
        el.textContent = relativeTime(ts);
    });

    /* ---- 10. Delete account modal (premium) ---- */
    const deleteModal = document.getElementById('deleteAccountModal');
    const btnDeleteAccount = document.getElementById('btnDeleteAccount');
    const deleteConfirmInput = document.getElementById('deleteConfirmInput');
    const btnConfirmDelete = document.getElementById('btnConfirmDeleteAccount');
    
    function openDeleteModal() {
        deleteModal?.classList.add('show');
        document.body.style.overflow = 'hidden';
        setTimeout(() => deleteConfirmInput?.focus(), 100);
    }
    function closeDeleteModal() {
        deleteModal?.classList.remove('show');
        document.body.style.overflow = '';
        if (deleteConfirmInput) deleteConfirmInput.value = '';
        if (btnConfirmDelete) btnConfirmDelete.disabled = true;
    }
    
    btnDeleteAccount?.addEventListener('click', openDeleteModal);
    deleteModal?.querySelector('[data-close-modal]')?.addEventListener('click', closeDeleteModal);
    deleteModal?.addEventListener('click', (e) => { if (e.target === deleteModal) closeDeleteModal(); });
    
    deleteConfirmInput?.addEventListener('input', () => {
        if (btnConfirmDelete) {
            btnConfirmDelete.disabled = deleteConfirmInput.value.trim().toUpperCase() !== 'HAPUS';
        }
    });
    
    btnConfirmDelete?.addEventListener('click', () => {
        if (window.toast) window.toast('Penghapusan akun memerlukan verifikasi dari administrator. Tim kami akan menghubungi Anda.', 'info');
        closeDeleteModal();
    });

    /* ---- 11. Keyboard shortcuts ---- */
    document.getElementById('kbdSave')?.addEventListener('click', () => {
        if (btnSaveProfile && !btnSaveProfile.disabled) profileForm?.requestSubmit();
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && deleteModal?.classList.contains('show')) {
            closeDeleteModal();
            return;
        }
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            const tag = document.activeElement.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') {
                // Only intercept if profile form is focused
                if (profileForm?.contains(document.activeElement)) {
                    e.preventDefault();
                    if (btnSaveProfile && !btnSaveProfile.disabled) profileForm.requestSubmit();
                }
            } else {
                e.preventDefault();
                if (btnSaveProfile && !btnSaveProfile.disabled) profileForm?.requestSubmit();
            }
        }
    });

    /* ---- 12. Security score calculation ---- */
    const securityRing = document.getElementById('securityRing');
    const securityScoreNum = document.getElementById('securityScoreNum');
    const securityScoreLabel = document.getElementById('securityScoreLabel');
    
    if (securityRing && securityScoreNum) {
        let score = 75; // Base score
        // Boost if phone filled
        if ('<?= e($profile['phone'] ?? '') ?>') score += 5;
        // Boost if address filled
        if ('<?= e($profile['address'] ?? '') ?>') score += 5;
        // Boost if photo exists
        if ('<?= e($user['photo'] ?? '') ?>') score += 10;
        // Cap
        score = Math.min(100, score);
        
        securityRing.style.setProperty('--pct', score);
        securityScoreNum.textContent = score;
        
        if (securityScoreLabel) {
            if (score >= 90) securityScoreLabel.textContent = 'Sangat Baik';
            else if (score >= 75) securityScoreLabel.textContent = 'Baik';
            else if (score >= 50) securityScoreLabel.textContent = 'Cukup';
            else securityScoreLabel.textContent = 'Perlu Ditingkatkan';
        }
    }
})();
</script>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Last Saved ---- */
.last-saved {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(16,185,129,.1);
    border: 1px solid rgba(16,185,129,.25);
    font-size: 11.5px;
    font-weight: 600;
    color: #6ee7b7;
    animation: fadeIn 0.3s ease;
}
.last-saved i { font-size: 13px; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

/* ---- Profile Hero Card ---- */
.profile-hero-card {
    position: relative;
    overflow: hidden;
    padding: 0;
    margin-bottom: 32px;
}
.profile-cover {
    position: relative;
    height: 160px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 40%, #22d3ee 100%);
    overflow: hidden;
}
.profile-cover-pattern {
    position: absolute; inset: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.15) 1px, transparent 1px),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 30px 30px, 40px 40px;
    opacity: 0.6;
}
.profile-cover-orbs { position: absolute; inset: 0; pointer-events: none; }
.cover-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.5;
    animation: orb-float 12s ease-in-out infinite;
}
.cover-orb.orb-1 { width: 200px; height: 200px; background: #ec4899; top: -50px; right: 10%; }
.cover-orb.orb-2 { width: 150px; height: 150px; background: #22d3ee; bottom: -40px; left: 20%; animation-delay: -4s; }
.cover-orb.orb-3 { width: 120px; height: 120px; background: #fbbf24; top: 30%; left: 50%; animation-delay: -8s; }
@keyframes orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(20px, -15px) scale(1.1); }
    66% { transform: translate(-15px, 20px) scale(0.95); }
}

/* Hero badges (completion + verified) */
.profile-hero-badges {
    position: absolute;
    top: 20px; right: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
    z-index: 2;
}
.profile-completion-ring {
    position: relative;
    width: 64px; height: 64px;
    display: grid; place-items: center;
    background: rgba(10, 15, 31, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
}
.profile-completion-ring svg {
    position: absolute; inset: 0;
    width: 100%; height: 100%;
    transform: rotate(-90deg);
}
.profile-completion-ring circle {
    fill: none;
    stroke-width: 3;
}
.profile-completion-ring .ring-bg { stroke: rgba(255,255,255,.15); }
.profile-completion-ring .ring-fg {
    stroke: #10b981;
    stroke-linecap: round;
    stroke-dasharray: 100;
    stroke-dashoffset: calc(100 - var(--pct));
    transition: stroke-dashoffset 1.5s cubic-bezier(.22,1,.36,1);
    filter: drop-shadow(0 0 4px rgba(16,185,129,.5));
}
.completion-inner {
    position: relative;
    z-index: 1;
    text-align: center;
}
.completion-inner strong {
    display: block;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.completion-inner span {
    display: block;
    font-size: 8.5px;
    font-weight: 700;
    color: #6ee7b7;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 2px;
}

.profile-verified-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 99px;
    background: rgba(10, 15, 31, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    font-size: 11px;
    font-weight: 800;
    color: #6ee7b7;
    letter-spacing: 0.5px;
}
.profile-verified-badge i { font-size: 14px; color: #10b981; }

.profile-hero-body {
    display: flex;
    align-items: flex-end;
    gap: 24px;
    padding: 0 32px 24px;
    margin-top: -60px;
    position: relative;
    flex-wrap: wrap;
}

.profile-avatar-wrap { position: relative; flex-shrink: 0; }
.profile-avatar-ring {
    position: relative;
    display: inline-block;
    padding: 4px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #22d3ee);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
    transition: transform .3s;
}
.profile-avatar-wrap:hover .profile-avatar-ring { transform: scale(1.03); }
.profile-avatar-ring .avatar { border: 4px solid var(--bg-1); transition: transform .4s; }
.profile-avatar-wrap:hover .profile-avatar-ring .avatar { transform: scale(1.08); }
.profile-avatar-edit {
    position: absolute;
    bottom: 4px; right: 4px;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border: 3px solid var(--bg-1);
    color: #fff;
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 16px;
    transition: all 0.25s;
    z-index: 2;
}
.profile-avatar-edit:hover {
    transform: scale(1.1) rotate(-10deg);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
}
.profile-avatar-status {
    position: absolute;
    top: 8px; right: 8px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #10b981;
    border: 3px solid var(--bg-1);
    z-index: 2;
    animation: pulse-dot 2s infinite;
}

.profile-hero-info { flex: 1; min-width: 240px; padding-bottom: 4px; }
.profile-hero-name {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
}
.profile-hero-handle {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.profile-handle { font-size: 13.5px; color: var(--txt-1); font-weight: 600; }
.profile-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 99px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(139,92,246,.2));
    border: 1px solid rgba(99,102,241,.3);
    color: #a5b4fc;
}
.profile-role-badge i { font-size: 12px; }

.profile-hero-meta { display: flex; flex-wrap: wrap; gap: 16px; }
.profile-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.profile-meta-item i { font-size: 15px; color: var(--acc); }
.profile-meta-item.online { color: #6ee7b7; }
.profile-meta-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,.7); }
    50% { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
}

/* Stats bar */
.profile-stats-bar {
    display: flex;
    align-items: center;
    padding: 18px 32px;
    margin-top: 8px;
    background: rgba(255,255,255,0.02);
    border-top: 1px solid var(--glass-brd);
}
.profile-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    padding: 0 12px;
}
.profile-stat i { font-size: 22px; color: var(--acc); }
.profile-stat strong {
    display: block;
    font-size: 17px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    transition: transform .2s;
}
.profile-stat:hover strong { transform: scale(1.05); }
.profile-stat span {
    display: block;
    font-size: 11px;
    color: var(--txt-2);
    font-weight: 600;
}
.profile-stat-sep { width: 1px; height: 32px; background: var(--glass-brd); }

/* ---- Profile Grid ---- */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 24px;
    margin-bottom: 40px;
}
.profile-section { padding: 0; overflow: hidden; margin-bottom: 24px; }
.profile-section:last-child { margin-bottom: 0; }

.profile-section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--glass-brd);
    flex-wrap: wrap;
}
.profile-section-head.compact { padding: 18px 22px; }
.profile-section-head.compact h3 {
    font-size: 15px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-section-head.compact h3 i { color: var(--acc); font-size: 18px; }

.profile-section-title { display: flex; align-items: center; gap: 16px; }
.profile-section-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 22px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}
.profile-section-icon.security { background: linear-gradient(135deg, #f59e0b, #ef4444); box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3); }
.profile-section-icon.danger { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3); }
.profile-section-title h3 { font-size: 17px; font-weight: 800; margin-bottom: 2px; }
.profile-section-title p { font-size: 12.5px; color: var(--txt-1); }

.profile-section-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 99px;
    background: rgba(34, 211, 238, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.25);
    font-size: 11px;
    font-weight: 700;
    color: #67e8f9;
    transition: all .3s;
}
.profile-section-badge.modified {
    background: rgba(245,158,11,.12);
    border-color: rgba(245,158,11,.3);
    color: #fbbf24;
}
.profile-section-badge i { font-size: 12px; }

/* Avatar section dengan drag & drop */
.profile-avatar-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding: 24px 28px;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid var(--glass-brd);
    flex-wrap: wrap;
}
.profile-avatar-preview { flex-shrink: 0; }
.profile-avatar-controls { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 10px; }
.profile-avatar-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.avatar-dropzone {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: 10px;
    background: rgba(255,255,255,.02);
    border: 1.5px dashed rgba(255,255,255,.1);
    font-size: 11.5px;
    color: var(--txt-2);
    cursor: pointer;
    transition: all .25s;
}
.avatar-dropzone i { color: var(--acc); font-size: 16px; }
.avatar-dropzone:hover, .avatar-dropzone.drag-over {
    background: rgba(99,102,241,.06);
    border-color: var(--pri);
    color: var(--txt-0);
}
.profile-avatar-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: var(--txt-2);
}
.profile-avatar-hint i { color: var(--acc); font-size: 14px; }

/* Profile form */
.profile-form { padding: 24px 28px; }
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

.profile-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid var(--glass-brd);
}
.profile-form-actions .btn:disabled { opacity: 0.5; cursor: not-allowed; }

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

/* Password strength (dengan score) */
.strength-info { margin-top: 10px; }
.strength-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.strength-score {
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(255,255,255,.06);
    font-size: 10.5px;
    font-weight: 800;
    color: var(--txt-1);
    font-variant-numeric: tabular-nums;
}
.password-requirements {
    list-style: none;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 16px;
    margin-top: 0;
    padding: 0;
}
.password-requirements li {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: var(--txt-2);
    transition: all 0.25s;
}
.password-requirements li i { font-size: 12px; transition: all .25s; }
.password-requirements li.met {
    color: #6ee7b7;
    transform: translateX(2px);
}
.password-requirements li.met i {
    animation: check-pop 0.3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes check-pop {
    0% { transform: scale(0.5); }
    60% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Match icon dengan animasi */
.field-match-icon {
    position: absolute;
    right: 44px; top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0;
    transition: all 0.3s cubic-bezier(.34,1.56,.64,1);
}
.field-match-icon.match { color: #10b981; opacity: 1; transform: translateY(-50%) scale(1); }
.field-match-icon.mismatch { color: #ef4444; opacity: 1; transform: translateY(-50%) scale(1); }

/* Session list */
.session-list { list-style: none; padding: 8px 0; margin: 0; }
.session-list li {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 22px;
    border-bottom: 1px solid var(--glass-brd);
    transition: background 0.2s;
}
.session-list li:last-child { border-bottom: 0; }
.session-list li:hover { background: rgba(255,255,255,0.02); }
.session-list li.session-current { background: rgba(16, 185, 129, 0.04); }

.session-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: rgba(99,102,241,0.1);
    display: grid; place-items: center;
    font-size: 18px;
    color: var(--acc);
    position: relative;
    flex-shrink: 0;
}
.session-current-badge {
    position: absolute;
    top: -4px; right: -4px;
    padding: 2px 6px;
    border-radius: 4px;
    background: #10b981;
    color: #fff;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.session-info { flex: 1; min-width: 0; }
.session-info strong { display: block; font-size: 13px; font-weight: 700; margin-bottom: 2px; }
.session-info span { display: block; font-size: 11.5px; color: var(--txt-2); }

/* Security Score Card */
.security-score-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 22px;
    background: linear-gradient(135deg, rgba(16,185,129,.06), rgba(34,211,238,.04));
    border-bottom: 1px solid var(--glass-brd);
}
.security-score-ring {
    position: relative;
    width: 56px; height: 56px;
    flex-shrink: 0;
}
.security-score-ring svg {
    width: 100%; height: 100%;
    transform: rotate(-90deg);
}
.security-score-ring circle {
    fill: none;
    stroke-width: 3.5;
}
.security-score-ring .ring-bg { stroke: rgba(255,255,255,.08); }
.security-score-ring .score-fg {
    stroke: #10b981;
    stroke-linecap: round;
    stroke-dasharray: 100;
    stroke-dashoffset: calc(100 - var(--pct));
    transition: stroke-dashoffset 1.5s cubic-bezier(.22,1,.36,1);
    filter: drop-shadow(0 0 4px rgba(16,185,129,.5));
}
.score-inner {
    position: absolute; inset: 0;
    display: grid; place-items: center;
}
.score-inner strong {
    font-size: 17px;
    font-weight: 800;
    color: #6ee7b7;
    font-variant-numeric: tabular-nums;
}
.security-score-info { flex: 1; }
.security-score-info strong {
    display: block;
    font-size: 14px;
    font-weight: 800;
    color: var(--txt-0);
    margin-bottom: 2px;
}
.security-score-info span {
    font-size: 11px;
    color: var(--txt-2);
}

/* Security tips */
.security-tips { padding: 12px 22px 20px; display: flex; flex-direction: column; gap: 8px; }
.security-tip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--glass-brd);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
    transition: all .25s;
}
.security-tip:hover {
    transform: translateX(2px);
    border-color: var(--glass-brd-2);
}
.security-tip.tip-ok { border-left: 3px solid var(--ok); }
.security-tip.tip-warn { border-left: 3px solid var(--warn); }
.security-tip.tip-new { border-left: 3px solid var(--acc); }
.tip-icon {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    display: grid; place-items: center;
    font-size: 13px;
    flex-shrink: 0;
}
.tip-icon.success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.tip-icon.info { background: rgba(34, 211, 238, 0.15); color: var(--acc); }

/* Activity timeline */
.activity-timeline { padding: 16px 22px 20px; display: flex; flex-direction: column; gap: 2px; }
.activity-item { display: flex; gap: 14px; padding: 10px 0; position: relative; transition: transform .2s; }
.activity-item:hover { transform: translateX(4px); }
.activity-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 15px; top: 38px; bottom: -2px;
    width: 2px;
    background: var(--glass-brd);
}
.activity-icon {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.activity-info { flex: 1; min-width: 0; padding-top: 4px; }
.activity-info strong { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 2px; }
.activity-info span { font-size: 11px; color: var(--txt-2); }

/* Danger zone */
.danger-zone { border: 1px solid rgba(239, 68, 68, 0.2); }
.danger-zone .profile-section-head {
    background: rgba(239, 68, 68, 0.04);
    border-bottom-color: rgba(239, 68, 68, 0.2);
}
.danger-zone-content { padding: 20px 28px; }
.danger-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.05);
    border: 1px dashed rgba(239, 68, 68, 0.3);
    transition: all .25s;
}
.danger-item:hover {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.5);
}
.danger-item-info { flex: 1; }
.danger-item strong { display: block; font-size: 14px; font-weight: 700; color: #fca5a5; margin-bottom: 4px; }
.danger-item p { font-size: 12px; color: var(--txt-1); margin: 0; line-height: 1.5; }

/* Form divider */
.form-divider { height: 1px; background: var(--glass-brd); margin: 18px 0; }

/* ---- Delete Account Modal ---- */
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
.delete-warning-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(239,68,68,.05);
    border: 1px solid rgba(239,68,68,.2);
}
.delete-warning-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12.5px;
    color: var(--txt-1);
}
.delete-warning-item i { color: #fca5a5; font-size: 16px; flex-shrink: 0; }

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
    gap: 4px;
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

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 980px) {
    .profile-grid { grid-template-columns: 1fr; }
    .profile-stats-bar {
        flex-direction: column;
        gap: 12px;
        padding: 16px 24px;
    }
    .profile-stat-sep { width: 80%; height: 1px; }
    .profile-stat { width: 100%; padding: 6px 0; }
    .profile-hero-badges { flex-direction: column; gap: 8px; }
    .profile-completion-ring { width: 56px; height: 56px; }
}

@media (max-width: 640px) {
    .profile-hero-body {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 20px 20px;
    }
    .profile-hero-info { padding-top: 8px; }
    .profile-hero-name { font-size: 22px; }
    .profile-section-head { padding: 18px 20px; }
    .profile-form { padding: 20px; }
    .profile-avatar-section {
        flex-direction: column;
        padding: 20px;
    }
    .password-requirements { grid-template-columns: 1fr; }
    .danger-item {
        flex-direction: column;
        align-items: flex-start;
    }
    .keyboard-hints { display: none; }
    .last-saved { display: none; }
}

@media (max-width: 520px) {
    .profile-hero-badges {
        position: static;
        flex-direction: row;
        justify-content: center;
        padding: 12px 20px;
        background: rgba(10,15,31,0.4);
    }
    .profile-hero-name { font-size: 20px; }
    .profile-hero-meta { flex-direction: column; gap: 8px; }
    .profile-section-title h3 { font-size: 15px; }
    .security-score-card { padding: 14px 18px; }
    .security-score-ring { width: 48px; height: 48px; }
    .score-inner strong { font-size: 14px; }
}
</style>