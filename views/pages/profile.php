<?php
/**
 * ============================================================
 * HALAMAN PROFIL SAYA — ULTIMATE EDITION v5.9
 * Kelola informasi pribadi, keamanan akun, dan sesi aktif
 * dengan visual hierarchy yang jelas dan fitur keamanan modern.
 * ============================================================
 */

$profile = $profile ?? [];
$loginAt = $loginAt ?? time();
$account = $account ?? $user ?? [];
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
        <a href="<?= url('logout') ?>" class="btn btn-danger-ghost">
            <i class="ph ph-sign-out"></i>
            <span class="btn-text">Keluar</span>
        </a>
    </div>
</section>

<!-- ========== PROFILE HERO CARD ========== -->
<section class="profile-hero-card glass-card reveal">
    <!-- Animated cover background -->
    <div class="profile-cover">
        <div class="profile-cover-pattern"></div>
        <div class="profile-cover-orbs">
            <span class="cover-orb orb-1"></span>
            <span class="cover-orb orb-2"></span>
            <span class="cover-orb orb-3"></span>
        </div>
    </div>
    
    <!-- Verification badge -->
    <span class="profile-verified-badge">
        <i class="ph-fill ph-seal-check"></i>
        <span>Akun Terverifikasi</span>
    </span>

    <!-- Hero body -->
    <div class="profile-hero-body">
        <!-- Avatar dengan edit button -->
        <div class="profile-avatar-wrap">
            <div class="profile-avatar-ring">
                <?= avatar_tag($user, 'avatar-xl') ?>
            </div>
            <label class="profile-avatar-edit" for="photoInput" title="Ganti foto profil">
                <i class="ph ph-camera"></i>
            </label>
            <span class="profile-avatar-status" title="Online"></span>
        </div>
        
        <!-- Info -->
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
                    Login <?= date('d M Y, H:i', $loginAt) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Quick stats bar -->
    <div class="profile-stats-bar">
        <div class="profile-stat">
            <i class="ph ph-sign-in"></i>
            <div>
                <strong><?= $profile['login_count'] ?? 1 ?></strong>
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
                <strong class="ok-text">Aman</strong>
                <span>Status Keamanan</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== MAIN CONTENT GRID ========== -->
<section class="profile-grid">
    
    <!-- LEFT COLUMN: Forms -->
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
                <span class="profile-section-badge">
                    <i class="ph ph-pencil-simple"></i>
                    Dapat diubah
                </span>
            </div>
            
            <form id="profileForm" class="profile-form">
                <?= csrf_field() ?>

                <!-- Avatar Upload Zone (hidden input triggered by avatar edit) -->
                <div class="profile-avatar-section">
                    <div class="profile-avatar-preview" id="avatarPreview">
                        <?= avatar_tag($user, 'avatar-lg') ?>
                    </div>
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
                    <div class="profile-avatar-hint">
                        <i class="ph ph-info"></i>
                        <span>JPG, PNG, atau WEBP — maksimal 2 MB. Disarankan persegi (500×500px).</span>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-user"></i> Data Utama
                    </div>
                    <label class="field">
                        <span class="field-label">Nama Lengkap <em>*</em></span>
                        <input type="text" name="full_name" value="<?= e($user['name']) ?>" required>
                        <span class="field-error" data-error="full_name"></span>
                    </label>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Email <em>*</em></span>
                            <input type="email" name="email" value="<?= e($account['email']) ?>" required>
                            <span class="field-error" data-error="email"></span>
                        </label>
                        <label class="field">
                            <span class="field-label">No. Telepon</span>
                            <input type="text" name="phone" value="<?= e($profile['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-map-pin"></i> Alamat
                    </div>
                    <label class="field">
                        <span class="field-label">Alamat Lengkap</span>
                        <textarea name="address" rows="3" placeholder="Jl. Merdeka No. 1, Kota…" maxlength="500"><?= e($profile['address'] ?? '') ?></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Opsional — untuk keperluan korespondensi.</small>
                            <span class="char-counter" id="addressCounter">0 / 500</span>
                        </div>
                    </label>
                </div>

                <div class="profile-form-actions">
                    <button type="reset" class="btn btn-ghost">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                        <span class="btn-text">Reset</span>
                    </button>
                    <button type="submit" class="btn btn-primary">
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
                        <span class="strength-label" id="strengthLabel">Masukkan kata sandi baru</span>
                        <ul class="password-requirements" id="passwordRequirements">
                            <li data-req="length"><i class="ph ph-circle"></i> Minimal 8 karakter</li>
                            <li data-req="upper"><i class="ph ph-circle"></i> Huruf besar (A-Z)</li>
                            <li data-req="lower"><i class="ph ph-circle"></i> Huruf kecil (a-z)</li>
                            <li data-req="number"><i class="ph ph-circle"></i> Angka (0-9)</li>
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
                    <div>
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

    <!-- RIGHT COLUMN: Info & Sessions -->
    <aside class="profile-side-col">
        
        <!-- Sesi Aktif -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-device-mobile"></i> Sesi Aktif</h3>
            </div>
            <ul class="session-list">
                <li class="session-current">
                    <div class="session-icon">
                        <i class="ph ph-desktop"></i>
                        <span class="session-current-badge">Saat ini</span>
                    </div>
                    <div class="session-info">
                        <strong id="uaName">Mendeteksi peramban...</strong>
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

        <!-- Tips Keamanan -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-lightbulb"></i> Tips Keamanan</h3>
            </div>
            <div class="security-tips">
                <div class="security-tip">
                    <span class="tip-icon success"><i class="ph ph-check"></i></span>
                    <span>Gunakan kata sandi yang unik dan kuat</span>
                </div>
                <div class="security-tip">
                    <span class="tip-icon"><i class="ph ph-warning"></i></span>
                    <span>Jangan bagikan kredensial login Anda</span>
                </div>
                <div class="security-tip">
                    <span class="tip-icon"><i class="ph ph-warning"></i></span>
                    <span>Logout dari perangkat publik setelah selesai</span>
                </div>
                <div class="security-tip">
                    <span class="tip-icon success"><i class="ph ph-check"></i></span>
                    <span>Verifikasi email untuk proteksi tambahan</span>
                </div>
            </div>
        </section>

        <!-- Activity Log (Mini) -->
        <section class="glass-card profile-section reveal">
            <div class="profile-section-head compact">
                <h3><i class="ph ph-clock-clockwise"></i> Aktivitas Terbaru</h3>
            </div>
            <div class="activity-timeline">
                <div class="activity-item">
                    <span class="activity-icon"><i class="ph ph-sign-in"></i></span>
                    <div class="activity-info">
                        <strong>Login berhasil</strong>
                        <span><?= date('d M Y, H:i', $loginAt) ?></span>
                    </div>
                </div>
                <div class="activity-item">
                    <span class="activity-icon"><i class="ph ph-user-circle"></i></span>
                    <div class="activity-info">
                        <strong>Profil diakses</strong>
                        <span>Hari ini</span>
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

<!-- Toast Zone -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/profile.js') ?>"></script>

<style>
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
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.15) 1px, transparent 1px),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 30px 30px, 40px 40px;
    opacity: 0.6;
}
.profile-cover-orbs {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.cover-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.5;
    animation: orb-float 12s ease-in-out infinite;
}
.cover-orb.orb-1 {
    width: 200px; height: 200px;
    background: #ec4899;
    top: -50px; right: 10%;
}
.cover-orb.orb-2 {
    width: 150px; height: 150px;
    background: #22d3ee;
    bottom: -40px; left: 20%;
    animation-delay: -4s;
}
.cover-orb.orb-3 {
    width: 120px; height: 120px;
    background: #fbbf24;
    top: 30%; left: 50%;
    animation-delay: -8s;
}
@keyframes orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(20px, -15px) scale(1.1); }
    66% { transform: translate(-15px, 20px) scale(0.95); }
}

.profile-verified-badge {
    position: absolute;
    top: 20px;
    right: 20px;
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
.profile-verified-badge i {
    font-size: 14px;
    color: #10b981;
}

.profile-hero-body {
    display: flex;
    align-items: flex-end;
    gap: 24px;
    padding: 0 32px 24px;
    margin-top: -60px;
    position: relative;
    flex-wrap: wrap;
}

.profile-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}
.profile-avatar-ring {
    position: relative;
    display: inline-block;
    padding: 4px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #22d3ee);
    box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
}
.profile-avatar-ring .avatar {
    border: 4px solid var(--bg-1);
}
.profile-avatar-edit {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border: 3px solid var(--bg-1);
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
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
    top: 8px;
    right: 8px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #10b981;
    border: 3px solid var(--bg-1);
    z-index: 2;
    animation: pulse-dot 2s infinite;
}

.profile-hero-info {
    flex: 1;
    min-width: 240px;
    padding-bottom: 4px;
}
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
.profile-handle {
    font-size: 13.5px;
    color: var(--txt-1);
    font-weight: 600;
}
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
.profile-role-badge i {
    font-size: 12px;
}

.profile-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}
.profile-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.profile-meta-item i {
    font-size: 15px;
    color: var(--acc);
}
.profile-meta-item.online {
    color: #6ee7b7;
}
.profile-meta-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    animation: pulse-dot 2s infinite;
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
.profile-stat i {
    font-size: 22px;
    color: var(--acc);
}
.profile-stat strong {
    display: block;
    font-size: 17px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.profile-stat span {
    display: block;
    font-size: 11px;
    color: var(--txt-2);
    font-weight: 600;
}
.profile-stat-sep {
    width: 1px;
    height: 32px;
    background: var(--glass-brd);
}

/* ---- Profile Grid ---- */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 24px;
    margin-bottom: 40px;
}

.profile-section {
    padding: 0;
    overflow: hidden;
    margin-bottom: 24px;
}
.profile-section:last-child { margin-bottom: 0; }

.profile-section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--glass-brd);
}
.profile-section-head.compact {
    padding: 18px 22px;
}
.profile-section-head.compact h3 {
    font-size: 15px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-section-head.compact h3 i {
    color: var(--acc);
    font-size: 18px;
}

.profile-section-title {
    display: flex;
    align-items: center;
    gap: 16px;
}
.profile-section-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
}
.profile-section-icon.security {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
}
.profile-section-icon.danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
}
.profile-section-title h3 {
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 2px;
}
.profile-section-title p {
    font-size: 12.5px;
    color: var(--txt-1);
}

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
}
.profile-section-badge i { font-size: 12px; }

/* Avatar section in form */
.profile-avatar-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding: 24px 28px;
    background: rgba(255,255,255,0.02);
    border-bottom: 1px solid var(--glass-brd);
}
.profile-avatar-preview {
    flex-shrink: 0;
}
.profile-avatar-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.profile-avatar-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: var(--txt-2);
}
.profile-avatar-hint i {
    color: var(--acc);
    font-size: 14px;
}

/* Profile form */
.profile-form {
    padding: 24px 28px;
}

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

.profile-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid var(--glass-brd);
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
}
.char-counter.warn { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

/* Password requirements */
.strength-info {
    margin-top: 10px;
}
.password-requirements {
    list-style: none;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px 16px;
    margin-top: 8px;
}
.password-requirements li {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: var(--txt-2);
    transition: color 0.2s;
}
.password-requirements li i {
    font-size: 12px;
}
.password-requirements li.met {
    color: #6ee7b7;
}
.password-requirements li.met i::before {
    content: "\e184"; /* check-circle */
}

/* Match icon */
.field-match-icon {
    position: absolute;
    right: 44px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0;
    transition: opacity 0.2s;
}
.field-match-icon.match {
    color: #10b981;
    opacity: 1;
}
.field-match-icon.mismatch {
    color: #ef4444;
    opacity: 1;
}

/* Session list */
.session-list {
    list-style: none;
    padding: 8px 0;
}
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
.session-list li.session-current {
    background: rgba(16, 185, 129, 0.04);
}

.session-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(99,102,241,0.1);
    display: grid;
    place-items: center;
    font-size: 18px;
    color: var(--acc);
    position: relative;
    flex-shrink: 0;
}
.session-current-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    padding: 2px 6px;
    border-radius: 4px;
    background: #10b981;
    color: #fff;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.session-info {
    flex: 1;
    min-width: 0;
}
.session-info strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 2px;
}
.session-info span {
    display: block;
    font-size: 11.5px;
    color: var(--txt-2);
}

/* Security tips */
.security-tips {
    padding: 12px 22px 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
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
}
.tip-icon {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    display: grid;
    place-items: center;
    font-size: 13px;
    flex-shrink: 0;
}
.tip-icon.success {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}

/* Activity timeline */
.activity-timeline {
    padding: 16px 22px 20px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.activity-item {
    display: flex;
    gap: 14px;
    padding: 10px 0;
    position: relative;
}
.activity-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 38px;
    bottom: -2px;
    width: 2px;
    background: var(--glass-brd);
}
.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.activity-info {
    flex: 1;
    min-width: 0;
    padding-top: 4px;
}
.activity-info strong {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 2px;
}
.activity-info span {
    font-size: 11px;
    color: var(--txt-2);
}

/* Danger zone */
.danger-zone {
    border: 1px solid rgba(239, 68, 68, 0.2);
}
.danger-zone .profile-section-head {
    background: rgba(239, 68, 68, 0.04);
    border-bottom-color: rgba(239, 68, 68, 0.2);
}
.danger-zone-content {
    padding: 20px 28px;
}
.danger-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 16px 20px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.05);
    border: 1px dashed rgba(239, 68, 68, 0.3);
}
.danger-item strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #fca5a5;
    margin-bottom: 4px;
}
.danger-item p {
    font-size: 12px;
    color: var(--txt-1);
    margin: 0;
    line-height: 1.5;
}

/* Form divider */
.form-divider {
    height: 1px;
    background: var(--glass-brd);
    margin: 18px 0;
}

/* Responsive */
@media (max-width: 980px) {
    .profile-grid {
        grid-template-columns: 1fr;
    }
    .profile-stats-bar {
        flex-direction: column;
        gap: 12px;
        padding: 16px 24px;
    }
    .profile-stat-sep {
        width: 80%;
        height: 1px;
    }
    .profile-stat {
        width: 100%;
        padding: 6px 0;
    }
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
    .password-requirements {
        grid-template-columns: 1fr;
    }
    .danger-item {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
/* =========================================================
   PROFILE ULTIMATE — Enhanced Interactions
   ========================================================= */
(() => {
    'use strict';
    
    // ---- 1. Session duration timer ----
    const sessionEl = document.getElementById('sessionDuration');
    if (sessionEl) {
        const loginAt = <?= $loginAt ?> * 1000;
        const update = () => {
            const diff = Date.now() - loginAt;
            const hours = Math.floor(diff / 3600000);
            const mins = Math.floor((diff % 3600000) / 60000);
            sessionEl.textContent = hours > 0 ? `${hours}j ${mins}m` : `${mins} menit`;
        };
        update();
        setInterval(update, 60000);
    }
    
    // ---- 2. User agent detection ----
    const uaEl = document.getElementById('uaName');
    if (uaEl) {
        const ua = navigator.userAgent;
        let browser = 'Peramban tidak dikenal';
        if (ua.includes('Chrome') && !ua.includes('Edg')) browser = 'Google Chrome';
        else if (ua.includes('Firefox')) browser = 'Mozilla Firefox';
        else if (ua.includes('Safari') && !ua.includes('Chrome')) browser = 'Safari';
        else if (ua.includes('Edg')) browser = 'Microsoft Edge';
        else if (ua.includes('Opera') || ua.includes('OPR')) browser = 'Opera';
        
        let os = '';
        if (ua.includes('Windows')) os = 'Windows';
        else if (ua.includes('Mac')) os = 'macOS';
        else if (ua.includes('Linux')) os = 'Linux';
        else if (ua.includes('Android')) os = 'Android';
        else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS';
        
        uaEl.textContent = os ? `${browser} di ${os}` : browser;
    }
    
    // ---- 3. Avatar preview ----
    const photoInput = document.getElementById('photoInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const removeBtn = document.getElementById('btnRemovePhoto');
    
    if (photoInput && avatarPreview) {
        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    if (window.toast) window.toast('Ukuran file melebihi 2 MB.', 'error');
                    photoInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    avatarPreview.innerHTML = `
                        <div class="avatar avatar-lg" style="background-image:url('${e.target.result}');background-size:cover;background-position:center;"></div>
                    `;
                    if (removeBtn) removeBtn.style.display = 'inline-flex';
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // ---- 4. Character counter ----
    const addressField = document.querySelector('textarea[name="address"]');
    const addressCounter = document.getElementById('addressCounter');
    
    if (addressField && addressCounter) {
        const updateCounter = () => {
            const len = addressField.value.length;
            addressCounter.textContent = `${len} / 500`;
            addressCounter.className = 'char-counter';
            if (len > 450) addressCounter.classList.add('warn');
            if (len > 480) addressCounter.classList.add('danger');
        };
        addressField.addEventListener('input', updateCounter);
        updateCounter();
    }
    
    // ---- 5. Password toggle ----
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
    
    // ---- 6. Password strength + requirements ----
    const newPass = document.getElementById('newPassword');
    const confirmPass = document.getElementById('confirmPass');
    const strengthMeter = document.getElementById('strengthMeter');
    const strengthLabel = document.getElementById('strengthLabel');
    const matchIcon = document.getElementById('matchIcon');
    const reqs = document.querySelectorAll('.password-requirements li');
    
    if (newPass && strengthMeter) {
        const bars = strengthMeter.querySelectorAll('.strength-bar');
        
        newPass.addEventListener('input', () => {
            const val = newPass.value;
            
            // Check requirements
            const checks = {
                length: val.length >= 8,
                upper: /[A-Z]/.test(val),
                lower: /[a-z]/.test(val),
                number: /[0-9]/.test(val)
            };
            
            reqs.forEach(req => {
                const key = req.dataset.req;
                req.classList.toggle('met', checks[key]);
                req.querySelector('i').className = checks[key] ? 'ph ph-check-circle' : 'ph ph-circle';
            });
            
            // Strength score
            let score = 0;
            if (checks.length) score++;
            if (checks.upper && checks.lower) score++;
            if (checks.number) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            
            if (val.length === 0) {
                bars.forEach(b => b.className = 'strength-bar');
                strengthLabel.textContent = 'Masukkan kata sandi baru';
                strengthLabel.className = 'strength-label';
                return;
            }
            
            const levels = ['weak', 'weak', 'fair', 'good', 'strong'];
            const labels = ['Sangat lemah', 'Lemah', 'Cukup kuat', 'Baik', 'Sangat kuat'];
            const level = levels[score];
            
            bars.forEach((bar, i) => {
                bar.className = 'strength-bar';
                if (i < score) bar.classList.add('active', level);
            });
            
            strengthLabel.textContent = labels[score];
            strengthLabel.className = 'strength-label ' + level;
        });
    }
    
    if (confirmPass && matchIcon) {
        confirmPass.addEventListener('input', () => {
            if (!confirmPass.value) {
                matchIcon.className = 'field-match-icon';
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
    
    // ---- 7. Delete account handler ----
    const deleteBtn = document.getElementById('btnDeleteAccount');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => {
            if (window.toast) {
                window.toast('Fitur penghapusan akun memerlukan konfirmasi dari administrator. Silakan hubungi dukungan.', 'error');
            }
        });
    }
})();
</script>