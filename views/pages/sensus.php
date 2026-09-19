<!-- File: views/pages/sensus.php -->
<section class="pub-page-head">
    <span class="page-eyebrow">Sensus Anggota</span>
    <h1>Digitalisasi Database Anggota</h1>
    <p>Lengkapi formulir berikut agar Anda tercatat dalam sistem keanggotaan terpadu. Data akan diverifikasi oleh pengurus.</p>
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
                <i class="ph ph-paper-plane-tilt"></i><span class="btn-text">Kirim Data Sensus</span>
            </button>
        </form>
        <p class="auth-foot" style="margin-top:20px;">
            <a href="<?= url('') ?>" class="link-accent">← Kembali ke beranda</a>
        </p>
    </div>
</div>