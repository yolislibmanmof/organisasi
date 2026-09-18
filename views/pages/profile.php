<!-- File: views/pages/profile.php -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Akun & Keamanan</div>
        <h2 class="page-title">Profil Saya</h2>
        <p class="page-sub">Kelola informasi pribadi, foto profil, dan keamanan akun Anda.</p>
    </div>
</section>

<!-- Kartu hero profil -->
<section class="profile-hero glass-card">
    <div class="hero-cover"></div>
    <span class="hero-badge"><i class="ph ph-shield-check"></i> Akun Terverifikasi</span>
    <div class="hero-body">
        <?= avatar_tag($user, 'avatar-xl') ?>
        <div class="hero-info">
            <h2><?= e($user['name']) ?></h2>
            <p>@<?= e($user['username']) ?> • <?= e(ucfirst($user['role'])) ?></p>
            <div class="hero-meta">
                <span><i class="ph ph-envelope-simple"></i> <?= e($account['email']) ?></span>
                <span><i class="ph ph-calendar-blank"></i> Anggota sejak <?= $profile ? date('d M Y', strtotime($profile['join_date'])) : '—' ?></span>
                <span><i class="ph ph-sign-in"></i> Login <?= date('d M Y, H:i', $loginAt) ?></span>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-grid-two">
    <!-- Informasi pribadi -->
    <section class="glass-card panel-card">
        <div class="card-head">
            <h3><i class="ph ph-identification-card"></i> Informasi Pribadi</h3>
        </div>
        <form id="profileForm">
            <?= csrf_field() ?>

            <div class="upload-zone" id="uploadZone">
                <?= avatar_tag($user, 'avatar-lg') ?>
                <div class="upload-text">
                    <strong>Klik untuk mengganti foto profil</strong>
                    <small>JPG, PNG, atau WEBP — maksimal 2 MB</small>
                </div>
                <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png,image/webp" hidden>
            </div>

            <label class="field">
                <span class="field-label">Nama Lengkap <em>*</em></span>
                <input type="text" name="full_name" value="<?= e($user['name']) ?>" required>
                <span class="field-error" data-error="full_name"></span>
            </label>
            <label class="field">
                <span class="field-label">Email <em>*</em></span>
                <input type="email" name="email" value="<?= e($account['email']) ?>" required>
                <span class="field-error" data-error="email"></span>
            </label>
            <label class="field">
                <span class="field-label">No. Telepon</span>
                <input type="text" name="phone" value="<?= e($profile['phone'] ?? '') ?>">
            </label>
            <label class="field">
                <span class="field-label">Alamat</span>
                <textarea name="address" rows="2"><?= e($profile['address'] ?? '') ?></textarea>
            </label>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="ph ph-floppy-disk"></i>
                <span class="btn-text">Simpan Perubahan</span>
            </button>
        </form>
    </section>

    <div class="stack-col">
        <!-- Keamanan -->
        <section class="glass-card panel-card">
            <div class="card-head">
                <h3><i class="ph ph-lock-key"></i> Keamanan Akun</h3>
            </div>
            <form id="passwordForm">
                <?= csrf_field() ?>
                <label class="field">
                    <span class="field-label">Kata Sandi Saat Ini</span>
                    <input type="password" name="current_password" required>
                    <span class="field-error" data-error="current_password"></span>
                </label>
                <label class="field">
                    <span class="field-label">Kata Sandi Baru</span>
                    <input type="password" name="new_password" id="newPassword" required>
                    <div class="strength-meter" id="strengthMeter"><span></span></div>
                    <span class="strength-label" id="strengthLabel"></span>
                    <span class="field-error" data-error="new_password"></span>
                </label>
                <label class="field">
                    <span class="field-label">Konfirmasi Kata Sandi Baru</span>
                    <input type="password" name="confirm_password" required>
                    <span class="field-error" data-error="confirm_password"></span>
                </label>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="ph ph-key"></i>
                    <span class="btn-text">Ubah Kata Sandi</span>
                </button>
            </form>
        </section>

        <!-- Informasi sesi -->
        <section class="glass-card panel-card">
            <div class="card-head">
                <h3><i class="ph ph-device-mobile"></i> Informasi Sesi</h3>
            </div>
            <ul class="session-list">
                <li><span>Alamat IP</span><strong><?= e($_SERVER['REMOTE_ADDR'] ?? '—') ?></strong></li>
                <li><span>Peramban</span><strong id="uaName">Mendeteksi…</strong></li>
                <li><span>Login sejak</span><strong><?= date('d M Y, H:i', $loginAt) ?></strong></li>
                <li><span>Status sesi</span><strong class="ok-text"><i class="ph ph-check-circle"></i> Aktif & aman</strong></li>
            </ul>
        </section>
    </div>
</section>

<script src="<?= asset('js/profile.js') ?>"></script>