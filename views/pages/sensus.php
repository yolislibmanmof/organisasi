<?php
/**
 * ============================================================
 * FORMULIR SENSUS PUBLIK — ULTIMATE EDITION v7.0
 * Formulir pendaftaran anggota dan sensus alumni dengan
 * stepper live, auto-save, validasi real-time, dan UX premium.
 * ============================================================
 */

$success = $success ?? null;
$error = $error ?? null;
?>

<!-- ========== HERO SECTION ========== -->
<section class="sensus-hero">
    <!-- Decorative orbs -->
    <div class="sensus-orbs" aria-hidden="true">
        <span class="sensus-orb orb-1"></span>
        <span class="sensus-orb orb-2"></span>
        <span class="sensus-orb orb-3"></span>
    </div>

    <div class="sensus-hero-content reveal">
        <span class="page-eyebrow">
            <span class="live-dot"></span>
            Formulir Digital
        </span>
        <h1 class="sensus-hero-title">Bergabung atau Terdata</h1>
        <p class="sensus-hero-sub">
            Pilih keperluan Anda: pendaftaran anggota baru untuk berpartisipasi aktif, 
            atau sensus rekap alumni untuk tetap terhubung dengan keluarga besar.
        </p>
        
        <!-- Quick info cards (dengan glow) -->
        <div class="sensus-info-cards">
            <div class="sensus-info-card info-secure">
                <div class="info-glow"></div>
                <i class="ph ph-shield-check"></i>
                <div>
                    <strong>Data Aman</strong>
                    <span>Terenkripsi & terverifikasi</span>
                </div>
            </div>
            <div class="sensus-info-card info-fast">
                <div class="info-glow"></div>
                <i class="ph ph-lightning"></i>
                <div>
                    <strong>Proses Cepat</strong>
                    <span>Hanya 2-3 menit</span>
                </div>
            </div>
            <div class="sensus-info-card info-email">
                <div class="info-glow"></div>
                <i class="ph ph-envelope-simple"></i>
                <div>
                    <strong>Konfirmasi Email</strong>
                    <span>Dikirim otomatis</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== MAIN FORM ========== -->
<div class="sensus-wrap">
    
    <!-- Success State -->
    <?php if (!empty($success)): ?>
    <div class="sensus-success glass-card reveal">
        <!-- Confetti particles -->
        <div class="confetti-container" aria-hidden="true">
            <span class="confetti c1"></span>
            <span class="confetti c2"></span>
            <span class="confetti c3"></span>
            <span class="confetti c4"></span>
            <span class="confetti c5"></span>
            <span class="confetti c6"></span>
            <span class="confetti c7"></span>
            <span class="confetti c8"></span>
        </div>
        
        <div class="sensus-success-icon">
            <i class="ph ph-check-circle"></i>
            <span class="sensus-success-confetti"></span>
        </div>
        <h2>Data Berhasil Dikirim!</h2>
        <p><?= e($success['message']) ?></p>
        
        <?php if (!empty($success['reference'])): ?>
        <div class="success-reference">
            <span class="ref-label">Nomor Referensi</span>
            <div class="ref-value">
                <code id="refCode"><?= e($success['reference']) ?></code>
                <button type="button" class="ref-copy" id="btnCopyRef" title="Salin">
                    <i class="ph ph-copy"></i>
                </button>
            </div>
            <small>Simpan nomor ini untuk melacak status pendaftaran Anda.</small>
        </div>
        <?php endif; ?>
        
        <div class="sensus-success-actions">
            <a href="<?= url('') ?>" class="btn btn-primary">
                <i class="ph ph-house"></i>
                <span class="btn-text">Kembali ke Beranda</span>
            </a>
            <button type="button" onclick="localStorage.removeItem('sensus_draft'); location.reload()" class="btn btn-ghost">
                <i class="ph ph-plus"></i>
                <span class="btn-text">Kirim Data Lagi</span>
            </button>
        </div>
    </div>
    
    <?php else: ?>
    
    <!-- Stepper Progress (dengan percentage) -->
    <div class="sensus-stepper reveal" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" aria-label="Progress formulir">
        <div class="stepper-step active" data-step="1">
            <span class="stepper-num">1</span>
            <span class="stepper-label">Keperluan</span>
            <span class="stepper-time">~30 dtk</span>
        </div>
        <div class="stepper-line"><div class="stepper-line-fill"></div></div>
        <div class="stepper-step" data-step="2">
            <span class="stepper-num">2</span>
            <span class="stepper-label">Data Diri</span>
            <span class="stepper-time">~1 mnt</span>
        </div>
        <div class="stepper-line"><div class="stepper-line-fill"></div></div>
        <div class="stepper-step" data-step="3">
            <span class="stepper-num">3</span>
            <span class="stepper-label">Tambahan</span>
            <span class="stepper-time">~1 mnt</span>
        </div>
        <div class="stepper-line"><div class="stepper-line-fill"></div></div>
        <div class="stepper-step" data-step="4">
            <span class="stepper-num"><i class="ph ph-check"></i></span>
            <span class="stepper-label">Selesai</span>
        </div>
        <div class="stepper-percent" id="stepperPercent">25%</div>
    </div>
    
    <!-- Auto-save indicator -->
    <div class="autosave-indicator" id="autosaveIndicator" aria-live="polite">
        <i class="ph ph-cloud-check"></i>
        <span id="autosaveText">Draft tersimpan otomatis</span>
    </div>
    
    <!-- Form Card -->
    <div class="glass-card sensus-form-card reveal">
        
        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="sensus-alert sensus-alert-error" role="alert">
            <i class="ph ph-warning-circle"></i>
            <div>
                <strong>Gagal mengirim data</strong>
                <p><?= e($error['message']) ?></p>
            </div>
            <button type="button" class="alert-close" data-close-alert aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?= url('sensus/store') ?>" id="sensusForm" novalidate>
            <?= csrf_field() ?>

            <!-- STEP 1: Purpose Selection -->
            <div class="sensus-step" data-step="1" role="region" aria-label="Langkah 1: Pilih Keperluan">
                <div class="sensus-step-head">
                    <div class="step-head-text">
                        <h3>Pilih Keperluan Anda</h3>
                        <p>Bagaimana Anda ingin berinteraksi dengan organisasi?</p>
                    </div>
                    <span class="step-badge">Langkah 1 dari 3</span>
                </div>
                
                <div class="purpose-cards" role="radiogroup" aria-label="Pilih keperluan">
                    <label class="purpose-card active stagger-1" data-purpose="pendaftaran">
                        <input type="radio" name="purpose" value="pendaftaran" checked>
                        <div class="purpose-icon">
                            <i class="ph ph-user-plus"></i>
                        </div>
                        <div class="purpose-content">
                            <strong>Pendaftaran Anggota Baru</strong>
                            <p>Saya ingin menjadi anggota aktif dan berpartisipasi dalam kegiatan organisasi.</p>
                            <ul class="purpose-features">
                                <li><i class="ph ph-check-circle"></i> Akses dashboard anggota</li>
                                <li><i class="ph ph-check-circle"></i> Ikut serta dalam event</li>
                                <li><i class="ph ph-check-circle"></i> Terima notifikasi kegiatan</li>
                            </ul>
                        </div>
                        <span class="purpose-check">
                            <i class="ph ph-check-circle"></i>
                        </span>
                    </label>
                    
                    <label class="purpose-card stagger-2" data-purpose="sensus">
                        <input type="radio" name="purpose" value="sensus">
                        <div class="purpose-icon alumni">
                            <i class="ph ph-graduation-cap"></i>
                        </div>
                        <div class="purpose-content">
                            <strong>Sensus Rekap Alumni</strong>
                            <p>Saya ingin tetap terdata sebagai alumni tanpa menjadi anggota aktif.</p>
                            <ul class="purpose-features">
                                <li><i class="ph ph-check-circle"></i> Terdata dalam database alumni</li>
                                <li><i class="ph ph-check-circle"></i> Terhubung dengan jaringan alumni</li>
                                <li><i class="ph ph-check-circle"></i> Info reuni & gathering</li>
                            </ul>
                        </div>
                        <span class="purpose-check">
                            <i class="ph ph-check-circle"></i>
                        </span>
                    </label>
                </div>
            </div>

            <!-- STEP 2: Personal Data -->
            <div class="sensus-step" data-step="2" style="display:none" role="region" aria-label="Langkah 2: Data Diri">
                <div class="sensus-step-head">
                    <div class="step-head-text">
                        <h3>Data Diri Anda</h3>
                        <p>Informasi dasar untuk keperluan identifikasi.</p>
                    </div>
                    <span class="step-badge">Langkah 2 dari 3</span>
                </div>
                
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-user"></i> Nama Lengkap <em>*</em>
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="full_name" required placeholder="Nama lengkap sesuai identitas" autocomplete="name" minlength="3" data-validate="name">
                        <span class="field-focus-ring"></span>
                        <span class="field-status" aria-hidden="true"><i class="ph ph-check-circle"></i></span>
                    </div>
                    <span class="field-error" data-error="full_name"></span>
                    <small class="field-hint">Minimal 3 karakter, sesuai identitas resmi.</small>
                </label>
                
                <div class="field-row">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-envelope-simple"></i> Email <em>*</em>
                        </span>
                        <div class="field-input-wrap">
                            <input type="email" name="email" required placeholder="nama@email.com" autocomplete="email" data-validate="email">
                            <span class="field-focus-ring"></span>
                            <span class="field-status" aria-hidden="true"><i class="ph ph-check-circle"></i></span>
                        </div>
                        <span class="field-error" data-error="email"></span>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-phone"></i> No. Telepon
                        </span>
                        <div class="field-input-wrap">
                            <input type="tel" name="phone" placeholder="0812-3456-7890" autocomplete="tel" data-validate="phone" inputmode="numeric">
                            <span class="field-focus-ring"></span>
                            <span class="field-status" aria-hidden="true"><i class="ph ph-check-circle"></i></span>
                        </div>
                        <small class="field-hint">Format otomatis saat mengetik.</small>
                    </label>
                </div>
            </div>

            <!-- STEP 3: Additional Info -->
            <div class="sensus-step" data-step="3" style="display:none" role="region" aria-label="Langkah 3: Informasi Tambahan">
                <div class="sensus-step-head">
                    <div class="step-head-text">
                        <h3>Informasi Tambahan</h3>
                        <p>Lengkapi data berikut untuk memperkaya database.</p>
                    </div>
                    <span class="step-badge">Langkah 3 dari 3</span>
                </div>
                
                <div class="field-row">
                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-identification-card"></i> Status <em>*</em>
                        </span>
                        <div class="select-wrap">
                            <select name="status" class="field-select" required>
                                <option value="">Pilih status...</option>
                                <option value="pelajar">Pelajar</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="alumni">Alumni</option>
                                <option value="umum">Umum</option>
                            </select>
                            <i class="ph ph-caret-down select-icon"></i>
                        </div>
                        <span class="field-error" data-error="status"></span>
                    </label>
                    <label class="field">
                        <span class="field-label">
                            <i class="ph ph-calendar"></i> Angkatan / Tahun Lulus
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="graduation_year" placeholder="2024" maxlength="4" inputmode="numeric" data-validate="year">
                            <span class="field-focus-ring"></span>
                        </div>
                        <small class="field-hint">4 digit tahun (1950–<?= date('Y') ?>).</small>
                    </label>
                </div>
                
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-map-pin"></i> Alamat
                    </span>
                    <textarea name="address" rows="2" placeholder="Alamat domisili saat ini" maxlength="300" data-counter="address"></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Opsional — untuk keperluan wilayah.</small>
                        <div class="char-progress-wrap">
                            <span class="char-counter" id="addressCounter">0 / 300</span>
                            <div class="char-progress"><div class="char-progress-fill" id="addressBar"></div></div>
                        </div>
                    </div>
                </label>
                
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-chat-centered-text"></i> Pesan (opsional)
                    </span>
                    <textarea name="message" rows="3" placeholder="Saran, kesan, atau informasi tambahan…" maxlength="500" data-counter="message"></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Sampaikan pesan Anda kepada panitia.</small>
                        <div class="char-progress-wrap">
                            <span class="char-counter" id="messageCounter">0 / 500</span>
                            <div class="char-progress"><div class="char-progress-fill" id="messageBar"></div></div>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Navigation buttons -->
            <div class="sensus-nav">
                <button type="button" class="btn btn-ghost sensus-nav-prev" id="btnPrev" style="display:none">
                    <i class="ph ph-arrow-left"></i>
                    <span class="btn-text">Sebelumnya</span>
                </button>
                <div class="sensus-nav-progress">
                    <span class="nav-progress-text" id="navProgressText">Langkah 1 dari 3</span>
                </div>
                <button type="button" class="btn btn-primary sensus-nav-next" id="btnNext">
                    <span class="btn-text">Lanjutkan</span>
                    <i class="ph ph-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-primary sensus-nav-submit" id="btnSubmit" style="display:none">
                    <i class="ph ph-paper-plane-tilt"></i>
                    <span class="btn-text">Kirim Data</span>
                </button>
            </div>
        </form>
    </div>
    
    <!-- FAQ Section -->
    <section class="sensus-faq reveal">
        <h3 class="faq-title">
            <i class="ph ph-question"></i>
            Pertanyaan yang Sering Diajukan
        </h3>
        <div class="faq-list">
            <details class="faq-item">
                <summary>
                    <span>Apa perbedaan pendaftaran anggota dan sensus alumni?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <div class="faq-content">
                    <p>Pendaftaran anggota baru memberikan Anda akses ke dashboard, partisipasi dalam kegiatan, dan hak suara. Sensus alumni hanya mendata Anda sebagai bagian dari jaringan alumni tanpa kewajiban aktif.</p>
                </div>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Apakah data saya aman?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <div class="faq-content">
                    <p>Ya, seluruh data yang Anda kirimkan dienkripsi dan disimpan dengan aman sesuai dengan kebijakan privasi kami. Data hanya digunakan untuk keperluan organisasi.</p>
                </div>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Berapa lama proses verifikasi?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <div class="faq-content">
                    <p>Untuk pendaftaran anggota baru, verifikasi dilakukan oleh admin dalam waktu 1-3 hari kerja. Sensus alumni diproses otomatis.</p>
                </div>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Bagaimana jika saya sudah memiliki akun?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <div class="faq-content">
                    <p>Silakan login menggunakan akun yang sudah ada melalui halaman login. Jika Anda lupa kata sandi, hubungi admin untuk bantuan reset.</p>
                </div>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Apakah progress saya tersimpan jika keluar?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <div class="faq-content">
                    <p>Ya, formulir ini menyimpan draft secara otomatis di browser Anda. Saat kembali, data yang sudah diisi akan dimuat kembali.</p>
                </div>
            </details>
        </div>
    </section>
    
    <!-- Back link -->
    <div class="sensus-back reveal">
        <a href="<?= url('') ?>" class="link-accent">
            <i class="ph ph-arrow-left"></i>
            Kembali ke beranda
        </a>
    </div>
    
    <?php endif; ?>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<?php if (empty($success)): ?>
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item"><kbd>Enter</kbd> Lanjutkan</span>
    <span class="kbd-item"><kbd>Esc</kbd> Reset</span>
    <span class="kbd-item"><kbd>1</kbd><kbd>2</kbd> Pilih Keperluan</span>
</div>
<?php endif; ?>

<style>
/* ---- Hero Section ---- */
.sensus-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 880px;
    margin: 0 auto;
    overflow: hidden;
}
.sensus-hero-content { position: relative; z-index: 1; }

/* Decorative orbs */
.sensus-orbs {
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
}
.sensus-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: .25;
}
.sensus-orb.orb-1 {
    width: 420px; height: 420px;
    background: #6366f1;
    top: -100px; left: -100px;
    animation: sensusOrb 22s ease-in-out infinite alternate;
}
.sensus-orb.orb-2 {
    width: 340px; height: 340px;
    background: #22d3ee;
    bottom: -80px; right: -80px;
    animation: sensusOrb 26s ease-in-out infinite alternate-reverse;
}
.sensus-orb.orb-3 {
    width: 240px; height: 240px;
    background: #8b5cf6;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: sensusOrb 30s ease-in-out infinite;
}
@keyframes sensusOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.15); }
}

/* Live dot */
.live-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16,185,129,.7);
    animation: live-pulse 2s infinite;
    margin-right: 4px;
}
@keyframes live-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.7); }
    70%  { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}

.sensus-hero-title {
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 800;
    letter-spacing: -1.5px;
    line-height: 1.1;
    margin: 16px 0 20px;
    background: linear-gradient(180deg, #ffffff 0%, #c7d2fe 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.sensus-hero-sub {
    color: var(--txt-1);
    font-size: 16px;
    line-height: 1.7;
    max-width: 52ch;
    margin: 0 auto 36px;
}

/* Info cards (dengan glow) */
.sensus-info-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    max-width: 720px;
    margin: 0 auto;
}
.sensus-info-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    text-align: left;
    overflow: hidden;
}
.info-glow {
    position: absolute;
    top: -20px; right: -20px;
    width: 100px; height: 100px;
    border-radius: 50%;
    pointer-events: none;
    transition: transform .5s;
    opacity: 0.8;
}
.sensus-info-card:hover .info-glow { transform: scale(1.4); }
.info-secure .info-glow { background: radial-gradient(circle, rgba(16,185,129,.25), transparent 70%); }
.info-fast .info-glow   { background: radial-gradient(circle, rgba(245,158,11,.25), transparent 70%); }
.info-email .info-glow  { background: radial-gradient(circle, rgba(99,102,241,.25), transparent 70%); }

.sensus-info-card:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 14px 34px rgba(2,6,23,.35);
}
.sensus-info-card i {
    font-size: 24px;
    flex-shrink: 0;
    position: relative; z-index: 1;
    transition: transform .3s;
}
.sensus-info-card:hover i { transform: scale(1.1) rotate(-5deg); }
.info-secure i { color: #6ee7b7; }
.info-fast i   { color: #fcd34d; }
.info-email i  { color: #a5b4fc; }
.sensus-info-card strong {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 2px;
    position: relative; z-index: 1;
}
.sensus-info-card span {
    font-size: 11px;
    color: var(--txt-2);
    position: relative; z-index: 1;
}

/* ---- Sensus Wrap ---- */
.sensus-wrap {
    max-width: 720px;
    margin: 0 auto;
    padding: 0 24px 80px;
}

/* ---- Success State (enhanced) ---- */
.sensus-success {
    position: relative;
    text-align: center;
    padding: 60px 40px;
    margin-bottom: 40px;
    animation: success-pop 0.6s cubic-bezier(.34,1.56,.64,1);
    overflow: hidden;
}
@keyframes success-pop {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* Confetti particles */
.confetti-container {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}
.confetti {
    position: absolute;
    width: 10px; height: 10px;
    opacity: 0;
    animation: confetti-fall 3s ease-out forwards;
}
.confetti.c1 { top: 10%; left: 15%; background: #6366f1; animation-delay: 0.1s; }
.confetti.c2 { top: 5%; left: 35%; background: #22d3ee; animation-delay: 0.3s; border-radius: 50%; }
.confetti.c3 { top: 12%; left: 55%; background: #10b981; animation-delay: 0.2s; }
.confetti.c4 { top: 8%; left: 75%; background: #f59e0b; animation-delay: 0.4s; border-radius: 50%; }
.confetti.c5 { top: 15%; left: 25%; background: #8b5cf6; animation-delay: 0.5s; }
.confetti.c6 { top: 6%; left: 45%; background: #ef4444; animation-delay: 0.6s; border-radius: 50%; }
.confetti.c7 { top: 10%; left: 65%; background: #22d3ee; animation-delay: 0.7s; }
.confetti.c8 { top: 8%; left: 85%; background: #6366f1; animation-delay: 0.8s; border-radius: 50%; }
@keyframes confetti-fall {
    0% { transform: translateY(0) rotate(0); opacity: 1; }
    100% { transform: translateY(400px) rotate(720deg); opacity: 0; }
}

.sensus-success-icon {
    position: relative;
    width: 100px; height: 100px;
    margin: 0 auto 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #34d399);
    display: grid; place-items: center;
    font-size: 50px;
    color: #fff;
    box-shadow: 0 16px 40px rgba(16, 185, 129, 0.4);
    animation: icon-bounce 2s ease-in-out infinite;
}
@keyframes icon-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}
.sensus-success-confetti {
    position: absolute;
    inset: -20px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, transparent, rgba(16,185,129,.4), transparent);
    filter: blur(16px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.sensus-success h2 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -.5px;
}
.sensus-success > p {
    color: var(--txt-1);
    font-size: 14.5px;
    margin-bottom: 24px;
    max-width: 40ch;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* Reference number */
.success-reference {
    max-width: 400px;
    margin: 0 auto 28px;
    padding: 18px 22px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    border: 1px solid rgba(99,102,241,.25);
}
.ref-label {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--acc);
    margin-bottom: 8px;
}
.ref-value {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(0,0,0,.2);
    border: 1px solid var(--glass-brd);
    margin-bottom: 8px;
}
.ref-value code {
    flex: 1;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 15px;
    font-weight: 700;
    color: var(--txt-0);
    letter-spacing: 1px;
}
.ref-copy {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(255,255,255,.08);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    cursor: pointer;
    display: grid; place-items: center;
    transition: all .2s;
    font-size: 15px;
}
.ref-copy:hover {
    background: var(--pri);
    border-color: var(--pri);
    color: #fff;
    transform: scale(1.05);
}
.ref-copy.copied {
    background: var(--ok);
    border-color: var(--ok);
    color: #fff;
}
.success-reference small {
    font-size: 11px;
    color: var(--txt-2);
}

.sensus-success-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

/* ---- Alerts ---- */
.sensus-alert {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    border-radius: 14px;
    margin-bottom: 24px;
    animation: alert-slide 0.4s cubic-bezier(.22,1,.36,1);
    position: relative;
}
@keyframes alert-slide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.sensus-alert i:first-child {
    font-size: 22px;
    flex-shrink: 0;
    margin-top: 1px;
}
.sensus-alert > div { flex: 1; }
.sensus-alert strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 2px;
}
.sensus-alert p {
    font-size: 12.5px;
    margin: 0;
    line-height: 1.5;
}
.sensus-alert-error {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.25);
}
.sensus-alert-error i { color: #fca5a5; }
.sensus-alert-error strong { color: #fca5a5; }
.sensus-alert-error p { color: var(--txt-1); }
.alert-close {
    width: 26px; height: 26px;
    border-radius: 7px;
    background: rgba(255,255,255,.06);
    border: none;
    color: var(--txt-2);
    cursor: pointer;
    display: grid; place-items: center;
    transition: all .2s;
    flex-shrink: 0;
}
.alert-close:hover {
    background: rgba(239,68,68,.15);
    color: var(--danger);
}

/* ---- Stepper (enhanced dengan percentage) ---- */
.sensus-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 22px 24px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    position: relative;
}
.stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.stepper-num {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: 2px solid var(--glass-brd);
    display: grid; place-items: center;
    font-size: 13px;
    font-weight: 800;
    color: var(--txt-1);
    transition: all 0.3s;
}
.stepper-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    transition: color 0.3s;
    text-align: center;
}
.stepper-time {
    font-size: 9.5px;
    font-weight: 600;
    color: var(--txt-2);
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    font-variant-numeric: tabular-nums;
}
.stepper-line {
    flex: 1;
    height: 2px;
    background: var(--glass-brd);
    margin: 0 8px;
    position: relative;
    overflow: hidden;
    border-radius: 99px;
}
.stepper-line-fill {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #10b981, #34d399);
    width: 0%;
    transition: width 0.5s cubic-bezier(.22,1,.36,1);
}
.stepper-step.active .stepper-num {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
    color: #fff;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}
.stepper-step.active .stepper-label { color: var(--txt-0); }
.stepper-step.active .stepper-time {
    background: rgba(99,102,241,.15);
    border-color: rgba(99,102,241,.3);
    color: #a5b4fc;
}
.stepper-step.completed .stepper-num {
    background: linear-gradient(135deg, #10b981, #34d399);
    border-color: transparent;
    color: #fff;
}
.stepper-step.completed .stepper-label { color: #6ee7b7; }
.stepper-step.completed + .stepper-line .stepper-line-fill { width: 100%; }

.stepper-percent {
    position: absolute;
    top: -10px; right: 16px;
    padding: 3px 10px;
    border-radius: 99px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 10.5px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    box-shadow: 0 4px 12px rgba(99,102,241,.35);
    transition: all .3s;
}

/* ---- Auto-save indicator ---- */
.autosave-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 14px;
    margin-bottom: 16px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-2);
    opacity: 0;
    transform: translateY(-4px);
    transition: all .3s;
}
.autosave-indicator.show {
    opacity: 1;
    transform: translateY(0);
}
.autosave-indicator i { color: var(--ok); font-size: 14px; }
.autosave-indicator.saving i {
    color: var(--acc);
    animation: spin 1s linear infinite;
}

/* ---- Form Card ---- */
.sensus-form-card { padding: 0; overflow: hidden; }

.sensus-step {
    padding: 32px 32px 24px;
    animation: step-fade 0.4s cubic-bezier(.22,1,.36,1);
}
@keyframes step-fade {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.sensus-step-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--glass-brd);
}
.step-head-text h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 6px;
    letter-spacing: -.3px;
}
.step-head-text p {
    font-size: 13.5px;
    color: var(--txt-1);
}
.step-badge {
    padding: 4px 12px;
    border-radius: 99px;
    background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.25);
    color: #a5b4fc;
    font-size: 10.5px;
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ---- Purpose Cards ---- */
.purpose-cards {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.purpose-card {
    position: relative;
    display: flex;
    gap: 18px;
    padding: 22px;
    border-radius: var(--rad-lg);
    background: rgba(255,255,255,.02);
    border: 2px solid var(--glass-brd);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    overflow: hidden;
    animation: purpose-in 0.5s cubic-bezier(.22,1,.36,1) both;
}
.purpose-card.stagger-1 { animation-delay: 0.05s; }
.purpose-card.stagger-2 { animation-delay: 0.15s; }
@keyframes purpose-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.purpose-card input { display: none; }
.purpose-card:hover {
    background: rgba(255,255,255,.04);
    border-color: var(--glass-brd-2);
    transform: translateY(-2px);
}
.purpose-card.active {
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    border-color: rgba(99, 102, 241, 0.5);
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.15);
}
.purpose-icon {
    width: 58px; height: 58px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 26px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    transition: transform 0.4s cubic-bezier(.34,1.56,.64,1);
}
.purpose-icon.alumni {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
}
.purpose-card.active .purpose-icon {
    transform: scale(1.08) rotate(-5deg);
}
.purpose-content { flex: 1; min-width: 0; }
.purpose-content strong {
    display: block;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 6px;
}
.purpose-content p {
    font-size: 12.5px;
    color: var(--txt-1);
    line-height: 1.5;
    margin-bottom: 12px;
}
.purpose-features {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 0;
}
.purpose-features li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: var(--txt-1);
    animation: feature-in 0.4s cubic-bezier(.22,1,.36,1) both;
}
.purpose-card.active .purpose-features li:nth-child(1) { animation-delay: 0.1s; }
.purpose-card.active .purpose-features li:nth-child(2) { animation-delay: 0.18s; }
.purpose-card.active .purpose-features li:nth-child(3) { animation-delay: 0.26s; }
@keyframes feature-in {
    from { opacity: 0; transform: translateX(-6px); }
    to { opacity: 1; transform: translateX(0); }
}
.purpose-features i {
    font-size: 14px;
    color: #10b981;
}
.purpose-check {
    position: absolute;
    top: 18px; right: 18px;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: 2px solid var(--glass-brd);
    display: grid; place-items: center;
    font-size: 16px;
    color: transparent;
    transition: all 0.3s;
}
.purpose-card.active .purpose-check {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
    color: #fff;
    transform: scale(1.1);
}

/* ---- Navigation ---- */
.sensus-nav {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 32px;
    background: rgba(255,255,255,.02);
    border-top: 1px solid var(--glass-brd);
}
.sensus-nav-progress {
    flex: 1;
    text-align: center;
}
.nav-progress-text {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-2);
}

/* ---- Field enhanced (dengan inline status) ---- */
.field-enhanced { margin-bottom: 18px; }
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
.field-enhanced input {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    transition: border-color 0.25s, background 0.25s;
    padding-right: 40px;
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
.field-enhanced input:focus ~ .field-focus-ring { opacity: 1; }

/* Inline validation status */
.field-status {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: transparent;
    pointer-events: none;
    transition: color .3s;
    z-index: 2;
}
.field-enhanced.is-valid .field-status { color: var(--ok); }
.field-enhanced.is-invalid .field-status { color: var(--danger); }
.field-enhanced.is-invalid .field-status i::before { content: "\f62b"; } /* x-circle */
.field-enhanced.is-invalid input {
    border-color: rgba(239,68,68,.5);
}
.field-enhanced.is-invalid .field-focus-ring {
    background: linear-gradient(135deg, var(--danger), #f87171);
}

.field-error {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--danger);
    margin-top: 4px;
    min-height: 14px;
}
.field-hint {
    display: block;
    font-size: 11px;
    color: var(--txt-2);
    margin-top: 4px;
}

/* Select premium */
.select-wrap {
    position: relative;
}
.field-select {
    width: 100%;
    padding: 12px 40px 12px 14px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1.5px solid rgba(255,255,255,.08);
    color: var(--txt-0);
    font: inherit;
    font-size: 13.5px;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: all .25s;
}
.field-select:focus {
    background: rgba(255,255,255,.06);
    border-color: var(--pri);
    outline: none;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}
.field-select option {
    background: #1a1f3a;
    color: var(--txt-0);
}
.select-icon {
    position: absolute;
    right: 14px; top: 50%;
    transform: translateY(-50%);
    color: var(--txt-2);
    font-size: 16px;
    pointer-events: none;
    transition: transform .25s;
}
.select-wrap:focus-within .select-icon {
    transform: translateY(-50%) rotate(180deg);
    color: var(--acc);
}

/* ---- FAQ Section (smooth animation) ---- */
.sensus-faq { margin-top: 48px; }
.faq-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 20px;
    letter-spacing: -.3px;
}
.faq-title i { font-size: 22px; color: var(--acc); }
.faq-list { display: flex; flex-direction: column; gap: 10px; }
.faq-item {
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-md);
    overflow: hidden;
    transition: all 0.3s;
}
.faq-item:hover { border-color: var(--glass-brd-2); }
.faq-item[open] {
    border-color: rgba(99, 102, 241, 0.3);
    background: linear-gradient(135deg, rgba(99,102,241,.05), rgba(34,211,238,.03));
}
.faq-item summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    cursor: pointer;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--txt-0);
    list-style: none;
    user-select: none;
}
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary span { flex: 1; }
.faq-item summary i {
    font-size: 18px;
    color: var(--txt-2);
    transition: transform 0.3s cubic-bezier(.22,1,.36,1);
    flex-shrink: 0;
}
.faq-item[open] summary i {
    transform: rotate(180deg);
    color: var(--acc);
}
.faq-content {
    overflow: hidden;
    animation: faq-open 0.3s cubic-bezier(.22,1,.36,1);
}
@keyframes faq-open {
    from { opacity: 0; max-height: 0; }
    to { opacity: 1; max-height: 200px; }
}
.faq-item p {
    padding: 0 20px 18px;
    font-size: 13px;
    color: var(--txt-1);
    line-height: 1.7;
    margin: 0;
}

/* ---- Back link ---- */
.sensus-back { margin-top: 32px; text-align: center; }
.sensus-back .link-accent {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--txt-1);
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s;
}
.sensus-back .link-accent:hover {
    color: var(--acc);
    gap: 12px;
}

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

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    max-width: 720px;
    margin: 20px auto 0;
    padding: 0 24px;
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

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .sensus-hero { padding-top: 120px; }
    .sensus-info-cards { grid-template-columns: 1fr; }
    .sensus-stepper { padding: 16px; overflow-x: auto; }
    .stepper-label { font-size: 10px; }
    .stepper-time { display: none; }
    .stepper-num { width: 32px; height: 32px; font-size: 12px; }
    .stepper-percent { top: -8px; right: 12px; font-size: 9.5px; padding: 2px 8px; }
    .sensus-step { padding: 24px 20px 18px; }
    .purpose-card { flex-direction: column; padding: 18px; }
    .purpose-check { top: 14px; right: 14px; }
    .sensus-nav { padding: 16px 20px; flex-wrap: wrap; }
    .sensus-nav-progress { order: 3; width: 100%; margin-top: 4px; }
    .sensus-orb { filter: blur(80px); opacity: .18; }
    .keyboard-hints { display: none; }
    .autosave-indicator { font-size: 10.5px; }
    .sensus-step-head { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 520px) {
    .sensus-success { padding: 40px 24px; }
    .sensus-success-icon { width: 80px; height: 80px; font-size: 40px; }
    .sensus-success h2 { font-size: 22px; }
    .sensus-success-actions { flex-direction: column; width: 100%; }
    .sensus-success-actions .btn { width: 100%; justify-content: center; }
    .success-reference { padding: 14px 16px; }
    .ref-value code { font-size: 13px; }
    .sensus-info-card { padding: 12px 14px; }
    .sensus-info-card i { font-size: 20px; }
    .sensus-info-card strong { font-size: 12px; }
    .sensus-info-card span { font-size: 10px; }
}
</style>

<script>
(function(){
    'use strict';
    
    const form = document.getElementById('sensusForm');
    if (!form) {
        // Handle success page
        const btnCopy = document.getElementById('btnCopyRef');
        const refCode = document.getElementById('refCode');
        if (btnCopy && refCode) {
            btnCopy.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(refCode.textContent);
                    btnCopy.classList.add('copied');
                    btnCopy.innerHTML = '<i class="ph ph-check"></i>';
                    if (window.toast) window.toast('Nomor referensi disalin!', 'success');
                    setTimeout(() => {
                        btnCopy.classList.remove('copied');
                        btnCopy.innerHTML = '<i class="ph ph-copy"></i>';
                    }, 2000);
                } catch (e) {
                    // Fallback
                    const range = document.createRange();
                    range.selectNode(refCode);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                }
            });
        }
        return;
    }
    
    const steps = form.querySelectorAll('.sensus-step');
    const stepperSteps = document.querySelectorAll('.stepper-step');
    const stepperLines = document.querySelectorAll('.stepper-line-fill');
    const stepperPercent = document.getElementById('stepperPercent');
    const navProgressText = document.getElementById('navProgressText');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    const autosaveIndicator = document.getElementById('autosaveIndicator');
    const autosaveText = document.getElementById('autosaveText');
    
    let currentStep = 1;
    const totalSteps = steps.length;
    const DRAFT_KEY = 'sensus_draft';
    let saveTimeout = null;
    
    /* ---- Step navigation ---- */
    function showStep(n) {
        steps.forEach(s => s.style.display = 'none');
        const target = form.querySelector(`.sensus-step[data-step="${n}"]`);
        if (target) {
            target.style.display = 'block';
            target.style.animation = 'none';
            target.offsetHeight; // reflow
            target.style.animation = 'step-fade 0.4s cubic-bezier(.22,1,.36,1)';
        }
        
        // Update stepper
        stepperSteps.forEach((s, i) => {
            s.classList.remove('active', 'completed');
            if (i + 1 < n) s.classList.add('completed');
            else if (i + 1 === n) s.classList.add('active');
        });
        
        // Update line fills
        stepperLines.forEach((line, i) => {
            line.style.width = (i + 1 < n) ? '100%' : '0%';
        });
        
        // Update percentage
        const pct = Math.round((n / totalSteps) * 100);
        if (stepperPercent) stepperPercent.textContent = pct + '%';
        const stepperEl = document.querySelector('.sensus-stepper');
        if (stepperEl) stepperEl.setAttribute('aria-valuenow', pct);
        
        // Update nav progress text
        if (navProgressText) navProgressText.textContent = `Langkah ${n} dari ${totalSteps - 1}`;
        
        // Update buttons
        btnPrev.style.display = n > 1 ? 'inline-flex' : 'none';
        btnNext.style.display = n < totalSteps ? 'inline-flex' : 'none';
        btnSubmit.style.display = n === totalSteps ? 'inline-flex' : 'none';
        
        currentStep = n;
        
        // Scroll to form top
        const formCard = document.querySelector('.sensus-form-card');
        if (formCard) {
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    /* ---- Validate current step ---- */
    function validateStep(n) {
        const step = form.querySelector(`.sensus-step[data-step="${n}"]`);
        if (!step) return true;
        
        const required = step.querySelectorAll('[required]');
        let valid = true;
        let firstInvalid = null;
        
        required.forEach(input => {
            const field = input.closest('.field-enhanced, .field');
            const val = input.value.trim();
            
            if (!val) {
                setFieldState(field, 'invalid', 'Field ini wajib diisi');
                valid = false;
                if (!firstInvalid) firstInvalid = input;
                return;
            }
            
            // Type-specific validation
            const vType = input.dataset.validate;
            if (vType === 'email') {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    setFieldState(field, 'invalid', 'Format email tidak valid');
                    valid = false;
                    if (!firstInvalid) firstInvalid = input;
                    return;
                }
            } else if (vType === 'name') {
                if (val.length < 3) {
                    setFieldState(field, 'invalid', 'Nama minimal 3 karakter');
                    valid = false;
                    if (!firstInvalid) firstInvalid = input;
                    return;
                }
            } else if (vType === 'phone' && val) {
                const digits = val.replace(/\D/g, '');
                if (digits.length < 8 || digits.length > 15) {
                    setFieldState(field, 'invalid', 'Nomor telepon tidak valid');
                    valid = false;
                    if (!firstInvalid) firstInvalid = input;
                    return;
                }
            } else if (vType === 'year' && val) {
                const year = parseInt(val, 10);
                const currentYear = new Date().getFullYear();
                if (isNaN(year) || year < 1950 || year > currentYear) {
                    setFieldState(field, 'invalid', `Tahun harus 1950-${currentYear}`);
                    valid = false;
                    if (!firstInvalid) firstInvalid = input;
                    return;
                }
            }
            
            setFieldState(field, 'valid');
        });
        
        if (!valid) {
            if (firstInvalid) firstInvalid.focus();
            if (window.toast) window.toast('Mohon lengkapi semua field yang wajib diisi dengan benar.', 'error');
        }
        
        return valid;
    }
    
    function setFieldState(field, state, message = '') {
        if (!field) return;
        field.classList.remove('is-valid', 'is-invalid');
        if (state) field.classList.add('is-' + state);
        const err = field.querySelector('.field-error');
        if (err) err.textContent = message || '';
    }
    
    /* ---- Button handlers ---- */
    btnNext?.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            showStep(currentStep + 1);
        }
    });
    
    btnPrev?.addEventListener('click', () => {
        showStep(currentStep - 1);
    });
    
    /* ---- Purpose card selection ---- */
    document.querySelectorAll('.purpose-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.purpose-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            saveDraft();
        });
    });
    
    /* ---- Character counters dengan progress bar ---- */
    function setupCounter(name, counterId, barId, max) {
        const field = form.querySelector(`textarea[name="${name}"]`);
        const counter = document.getElementById(counterId);
        const bar = document.getElementById(barId);
        if (!field || !counter || !bar) return;
        
        const update = () => {
            const len = field.value.length;
            const pct = Math.min(100, (len / max) * 100);
            counter.textContent = len + ' / ' + max;
            bar.style.width = pct + '%';
            counter.className = 'char-counter';
            bar.className = 'char-progress-fill';
            if (len > max * 0.85) { counter.classList.add('warn'); bar.classList.add('warn'); }
            if (len > max * 0.95) { counter.classList.add('danger'); bar.classList.add('danger'); }
        };
        field.addEventListener('input', () => { update(); saveDraft(); });
        update();
    }
    
    setupCounter('address', 'addressCounter', 'addressBar', 300);
    setupCounter('message', 'messageCounter', 'messageBar', 500);
    
    /* ---- Phone number auto-format ---- */
    const phoneInput = form.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            let digits = e.target.value.replace(/\D/g, '');
            if (digits.length > 13) digits = digits.slice(0, 13);
            
            let formatted = '';
            if (digits.length > 0) formatted = digits.slice(0, 4);
            if (digits.length > 4) formatted += '-' + digits.slice(4, 8);
            if (digits.length > 8) formatted += '-' + digits.slice(8, 13);
            
            e.target.value = formatted;
            saveDraft();
        });
    }
    
    /* ---- Graduation year: numeric only ---- */
    const yearInput = form.querySelector('input[name="graduation_year"]');
    if (yearInput) {
        yearInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
            saveDraft();
        });
    }
    
    /* ---- Inline validation on blur ---- */
    form.querySelectorAll('input[data-validate]').forEach(input => {
        input.addEventListener('blur', () => {
            const val = input.value.trim();
            if (!val) {
                if (input.required) {
                    setFieldState(input.closest('.field-enhanced, .field'), 'invalid', 'Field ini wajib diisi');
                }
                return;
            }
            
            const vType = input.dataset.validate;
            const field = input.closest('.field-enhanced, .field');
            
            if (vType === 'email') {
                if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) setFieldState(field, 'valid');
                else setFieldState(field, 'invalid', 'Format email tidak valid');
            } else if (vType === 'name') {
                if (val.length >= 3) setFieldState(field, 'valid');
                else setFieldState(field, 'invalid', 'Nama minimal 3 karakter');
            } else if (vType === 'phone') {
                const digits = val.replace(/\D/g, '');
                if (digits.length >= 8 && digits.length <= 15) setFieldState(field, 'valid');
                else setFieldState(field, 'invalid', 'Nomor telepon tidak valid');
            } else if (vType === 'year') {
                const year = parseInt(val, 10);
                const currentYear = new Date().getFullYear();
                if (!isNaN(year) && year >= 1950 && year <= currentYear) setFieldState(field, 'valid');
                else setFieldState(field, 'invalid', `Tahun harus 1950-${currentYear}`);
            }
        });
        
        input.addEventListener('input', () => {
            const field = input.closest('.field-enhanced, .field');
            if (field?.classList.contains('is-invalid')) {
                setFieldState(field, null);
            }
            saveDraft();
        });
    });
    
    /* ---- Auto-save draft to localStorage ---- */
    function saveDraft() {
        if (saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            try {
                const data = {};
                new FormData(form).forEach((value, key) => {
                    if (key !== '_token') data[key] = value;
                });
                data._step = currentStep;
                localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
                
                if (autosaveIndicator && autosaveText) {
                    autosaveText.textContent = 'Menyimpan...';
                    autosaveIndicator.classList.add('show', 'saving');
                    setTimeout(() => {
                        autosaveIndicator.classList.remove('saving');
                        autosaveText.textContent = 'Draft tersimpan otomatis';
                        setTimeout(() => {
                            autosaveIndicator.classList.remove('show');
                        }, 1500);
                    }, 400);
                }
            } catch (e) { /* quota exceeded, ignore */ }
        }, 500);
    }
    
    function loadDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return;
            const data = JSON.parse(raw);
            
            Object.keys(data).forEach(key => {
                if (key === '_step') return;
                const input = form.querySelector(`[name="${key}"]`);
                if (!input) return;
                if (input.type === 'radio') {
                    if (input.value === data[key]) {
                        input.checked = true;
                        input.closest('.purpose-card')?.classList.add('active');
                        document.querySelectorAll('.purpose-card').forEach(c => {
                            if (c !== input.closest('.purpose-card')) c.classList.remove('active');
                        });
                    }
                } else {
                    input.value = data[key];
                }
            });
            
            // Trigger counter updates
            form.querySelectorAll('textarea').forEach(t => t.dispatchEvent(new Event('input')));
            
            // Restore step
            if (data._step && data._step > 1 && data._step <= totalSteps) {
                showStep(parseInt(data._step, 10));
            }
            
            if (autosaveIndicator && autosaveText) {
                autosaveText.textContent = 'Draft dipulihkan';
                autosaveIndicator.classList.add('show');
                setTimeout(() => autosaveIndicator.classList.remove('show'), 2500);
            }
        } catch (e) { /* ignore */ }
    }
    
    // Listen to all inputs for auto-save
    form.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('change', saveDraft);
    });
    
    /* ---- Alert close ---- */
    document.querySelectorAll('[data-close-alert]').forEach(btn => {
        btn.addEventListener('click', () => {
            const alert = btn.closest('.sensus-alert');
            if (alert) {
                alert.style.animation = 'alert-slide 0.3s reverse';
                setTimeout(() => alert.remove(), 280);
            }
        });
    });
    
    /* ---- Clear draft on successful submit ---- */
    form.addEventListener('submit', () => {
        localStorage.removeItem(DRAFT_KEY);
    });
    
    /* ---- Keyboard shortcuts ---- */
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
        
        // Enter on non-textarea = next step
        if (e.key === 'Enter' && !inInput && btnNext.style.display !== 'none') {
            e.preventDefault();
            btnNext.click();
            return;
        }
        if (e.key === 'Enter' && tag === 'INPUT' && btnNext.style.display !== 'none') {
            e.preventDefault();
            btnNext.click();
            return;
        }
        
        if (inInput) return;
        
        // 1 = pendaftaran, 2 = sensus (only on step 1)
        if (currentStep === 1) {
            if (e.key === '1') {
                document.querySelector('.purpose-card[data-purpose="pendaftaran"]')?.click();
            } else if (e.key === '2') {
                document.querySelector('.purpose-card[data-purpose="sensus"]')?.click();
            }
        }
    });
    
    /* ---- Initialize ---- */
    showStep(1);
    loadDraft();
})();
</script>