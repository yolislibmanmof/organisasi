<!-- File: views/pages/sensus.php (FINAL - TAHAP 5.7) -->
<section class="pub-page-head">
    <span class="page-eyebrow">Formulir Anggota</span>
    <h1>Bergabung atau Terdata</h1>
    <p>Pilih keperluan Anda: pendaftaran anggota baru atau sensus rekap alumni.</p>
</section>

<div class="sensus-wrap">
    <div class="glass-card auth-form-card" style="max-width:640px;">
        <?php if (!empty($success)): ?>
            <div class="alert" style="background:rgba(16,185,129,.12);border-color:rgba(52,211,153,.4);color:#6ee7b7;">
                <i class="ph ph-check-circle"></i><span><?= e($success['message']) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><i class="ph ph-warning-circle"></i><span><?= e($error['message']) ?></span></div>
        <?php endif; ?>

        <form method="post" action="<?= url('sensus/store') ?>">
            <?= csrf_field() ?>

            <label class="field">
                <span class="field-label">Keperluan <em>*</em></span>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <label class="check-field" style="flex:1;min-width:180px;padding:12px 14px;border:1px solid var(--glass-brd);border-radius:10px;background:rgba(255,255,255,.02);">
                        <input type="radio" name="purpose" value="pendaftaran" checked>
                        <span class="check-mark"></span>
                        <span><strong style="display:block;font-size:12.5px;">Pendaftaran Anggota Baru</strong>
                              <small style="color:var(--txt-1);font-size:11px;">Saya ingin menjadi anggota aktif</small></span>
                    </label>
                    <label class="check-field" style="flex:1;min-width:180px;padding:12px 14px;border:1px solid var(--glass-brd);border-radius:10px;background:rgba(255,255,255,.02);">
                        <input type="radio" name="purpose" value="sensus">
                        <span class="check-mark"></span>
                        <span><strong style="display:block;font-size:12.5px;">Sensus Rekap Alumni</strong>
                              <small style="color:var(--txt-1);font-size:11px;">Saya hanya terdata sebagai alumni</small></span>
                    </label>
                </div>
            </label>

            <label class="field"><span class="field-label">Nama Lengkap <em>*</em></span>
                <input type="text" name="full_name" required placeholder="Nama lengkap Anda"></label>
            <div class="field-row">
                <label class="field"><span class="field-label">Email <em>*</em></span>
                    <input type="email" name="email" required placeholder="nama@email.com"></label>
                <label class="field"><span class="field-label">No. Telepon</span>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"></label>
            </div>
            <div class="field-row">
                <label class="field"><span class="field-label">Status <em>*</em></span>
                    <select name="status" class="field-select">
                        <option value="pelajar">Pelajar</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="alumni">Alumni</option>
                    </select></label>
                <label class="field"><span class="field-label">Angkatan / Tahun Lulus</span>
                    <input type="text" name="graduation_year" placeholder="Contoh: 2024"></label>
            </div>
            <label class="field"><span class="field-label">Alamat</span>
                <textarea name="address" rows="2" placeholder="Alamat domisili saat ini"></textarea></label>
            <label class="field"><span class="field-label">Pesan (opsional)</span>
                <textarea name="message" rows="3" placeholder="Saran, kesan, atau informasi tambahan…"></textarea></label>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="ph ph-paper-plane-tilt"></i><span class="btn-text">Kirim Data</span>
            </button>
        </form>
        <p class="auth-foot" style="margin-top:20px;">
            <a href="<?= url('') ?>" class="link-accent">← Kembali ke beranda</a>
        </p>
    </div>
</div>