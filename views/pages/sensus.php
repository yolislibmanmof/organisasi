<?php
/**
 * ============================================================
 * FORMULIR SENSUS PUBLIK — ULTIMATE EDITION v5.9
 * Formulir pendaftaran anggota dan sensus alumni dengan
 * stepper visual, validasi real-time, dan UX yang engaging.
 * ============================================================
 */

$success = $success ?? null;
$error = $error ?? null;
?>

<!-- ========== HERO SECTION ========== -->
<section class="sensus-hero">
    <div class="sensus-hero-content reveal">
        <span class="page-eyebrow">Formulir Digital</span>
        <h1 class="sensus-hero-title">Bergabung atau Terdata</h1>
        <p class="sensus-hero-sub">
            Pilih keperluan Anda: pendaftaran anggota baru untuk berpartisipasi aktif, 
            atau sensus rekap alumni untuk tetap terhubung dengan keluarga besar.
        </p>
        
        <!-- Quick info cards -->
        <div class="sensus-info-cards">
            <div class="sensus-info-card">
                <i class="ph ph-shield-check"></i>
                <div>
                    <strong>Data Aman</strong>
                    <span>Terenkripsi & terverifikasi</span>
                </div>
            </div>
            <div class="sensus-info-card">
                <i class="ph ph-lightning"></i>
                <div>
                    <strong>Proses Cepat</strong>
                    <span>Hanya 2-3 menit</span>
                </div>
            </div>
            <div class="sensus-info-card">
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
        <div class="sensus-success-icon">
            <i class="ph ph-check-circle"></i>
            <span class="sensus-success-confetti"></span>
        </div>
        <h2>Data Berhasil Dikirim!</h2>
        <p><?= e($success['message']) ?></p>
        <div class="sensus-success-actions">
            <a href="<?= url('') ?>" class="btn btn-primary">
                <i class="ph ph-house"></i>
                <span class="btn-text">Kembali ke Beranda</span>
            </a>
            <button onclick="location.reload()" class="btn btn-ghost">
                <i class="ph ph-plus"></i>
                <span class="btn-text">Kirim Data Lagi</span>
            </button>
        </div>
    </div>
    
    <?php else: ?>
    
    <!-- Stepper Progress -->
    <div class="sensus-stepper reveal">
        <div class="stepper-step active" data-step="1">
            <span class="stepper-num">1</span>
            <span class="stepper-label">Keperluan</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-step" data-step="2">
            <span class="stepper-num">2</span>
            <span class="stepper-label">Data Diri</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-step" data-step="3">
            <span class="stepper-num">3</span>
            <span class="stepper-label">Informasi Tambahan</span>
        </div>
        <div class="stepper-line"></div>
        <div class="stepper-step" data-step="4">
            <span class="stepper-num"><i class="ph ph-check"></i></span>
            <span class="stepper-label">Selesai</span>
        </div>
    </div>
    
    <!-- Form Card -->
    <div class="glass-card sensus-form-card reveal">
        
        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="sensus-alert sensus-alert-error">
            <i class="ph ph-warning-circle"></i>
            <div>
                <strong>Gagal mengirim data</strong>
                <p><?= e($error['message']) ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?= url('sensus/store') ?>" id="sensusForm" novalidate>
            <?= csrf_field() ?>

            <!-- STEP 1: Purpose Selection -->
            <div class="sensus-step" data-step="1">
                <div class="sensus-step-head">
                    <h3>Pilih Keperluan Anda</h3>
                    <p>Bagaimana Anda ingin berinteraksi dengan organisasi?</p>
                </div>
                
                <div class="purpose-cards">
                    <label class="purpose-card active" data-purpose="pendaftaran">
                        <input type="radio" name="purpose" value="pendaftaran" checked>
                        <div class="purpose-icon">
                            <i class="ph ph-user-plus"></i>
                        </div>
                        <div class="purpose-content">
                            <strong>Pendaftaran Anggota Baru</strong>
                            <p>Saya ingin menjadi anggota aktif dan berpartisipasi dalam kegiatan organisasi.</p>
                            <ul class="purpose-features">
                                <li><i class="ph ph-check"></i> Akses dashboard anggota</li>
                                <li><i class="ph ph-check"></i> Ikut serta dalam event</li>
                                <li><i class="ph ph-check"></i> Terima notifikasi kegiatan</li>
                            </ul>
                        </div>
                        <span class="purpose-check">
                            <i class="ph ph-check-circle"></i>
                        </span>
                    </label>
                    
                    <label class="purpose-card" data-purpose="sensus">
                        <input type="radio" name="purpose" value="sensus">
                        <div class="purpose-icon alumni">
                            <i class="ph ph-graduation-cap"></i>
                        </div>
                        <div class="purpose-content">
                            <strong>Sensus Rekap Alumni</strong>
                            <p>Saya ingin tetap terdata sebagai alumni tanpa menjadi anggota aktif.</p>
                            <ul class="purpose-features">
                                <li><i class="ph ph-check"></i> Terdata dalam database alumni</li>
                                <li><i class="ph ph-check"></i> Terhubung dengan jaringan alumni</li>
                                <li><i class="ph ph-check"></i> Info reuni & gathering</li>
                            </ul>
                        </div>
                        <span class="purpose-check">
                            <i class="ph ph-check-circle"></i>
                        </span>
                    </label>
                </div>
            </div>

            <!-- STEP 2: Personal Data -->
            <div class="sensus-step" data-step="2" style="display:none">
                <div class="sensus-step-head">
                    <h3>Data Diri Anda</h3>
                    <p>Informasi dasar untuk keperluan identifikasi.</p>
                </div>
                
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-user"></i> Nama Lengkap <em>*</em>
                    </span>
                    <div class="field-input-wrap">
                        <input type="text" name="full_name" required placeholder="Nama lengkap sesuai identitas" autocomplete="name">
                        <span class="field-focus-ring"></span>
                    </div>
                    <span class="field-error" data-error="full_name"></span>
                </label>
                
                <div class="field-row">
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-envelope-simple"></i> Email <em>*</em>
                        </span>
                        <div class="field-input-wrap">
                            <input type="email" name="email" required placeholder="nama@email.com" autocomplete="email">
                            <span class="field-focus-ring"></span>
                        </div>
                        <span class="field-error" data-error="email"></span>
                    </label>
                    <label class="field field-enhanced">
                        <span class="field-label">
                            <i class="ph ph-phone"></i> No. Telepon
                        </span>
                        <div class="field-input-wrap">
                            <input type="text" name="phone" placeholder="08xxxxxxxxxx" autocomplete="tel">
                            <span class="field-focus-ring"></span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- STEP 3: Additional Info -->
            <div class="sensus-step" data-step="3" style="display:none">
                <div class="sensus-step-head">
                    <h3>Informasi Tambahan</h3>
                    <p>Lengkapi data berikut untuk memperkaya database.</p>
                </div>
                
                <div class="field-row">
                    <label class="field">
                        <span class="field-label">Status <em>*</em></span>
                        <select name="status" class="field-select" required>
                            <option value="">Pilih status...</option>
                            <option value="pelajar">Pelajar</option>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="alumni">Alumni</option>
                        </select>
                    </label>
                    <label class="field">
                        <span class="field-label">Angkatan / Tahun Lulus</span>
                        <input type="text" name="graduation_year" placeholder="Contoh: 2024" maxlength="4" inputmode="numeric">
                    </label>
                </div>
                
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-map-pin"></i> Alamat
                    </span>
                    <textarea name="address" rows="2" placeholder="Alamat domisili saat ini" maxlength="300"></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Opsional — untuk keperluan wilayah.</small>
                        <span class="char-counter" id="addressCounter">0 / 300</span>
                    </div>
                </label>
                
                <label class="field">
                    <span class="field-label">
                        <i class="ph ph-chat-centered-text"></i> Pesan (opsional)
                    </span>
                    <textarea name="message" rows="3" placeholder="Saran, kesan, atau informasi tambahan…" maxlength="500"></textarea>
                    <div class="field-footer">
                        <small class="asset-hint">Sampaikan pesan Anda kepada panitia.</small>
                        <span class="char-counter" id="messageCounter">0 / 500</span>
                    </div>
                </label>
            </div>

            <!-- Navigation buttons -->
            <div class="sensus-nav">
                <button type="button" class="btn btn-ghost sensus-nav-prev" id="btnPrev" style="display:none">
                    <i class="ph ph-arrow-left"></i>
                    <span class="btn-text">Sebelumnya</span>
                </button>
                <div style="flex:1"></div>
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
                <p>Pendaftaran anggota baru memberikan Anda akses ke dashboard, partisipasi dalam kegiatan, dan hak suara. Sensus alumni hanya mendata Anda sebagai bagian dari jaringan alumni tanpa kewajiban aktif.</p>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Apakah data saya aman?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <p>Ya, seluruh data yang Anda kirimkan dienkripsi dan disimpan dengan aman sesuai dengan kebijakan privasi kami. Data hanya digunakan untuk keperluan organisasi.</p>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Berapa lama proses verifikasi?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <p>Untuk pendaftaran anggota baru, verifikasi dilakukan oleh admin dalam waktu 1-3 hari kerja. Sensus alumni diproses otomatis.</p>
            </details>
            <details class="faq-item">
                <summary>
                    <span>Bagaimana jika saya sudah memiliki akun?</span>
                    <i class="ph ph-caret-down"></i>
                </summary>
                <p>Silakan login menggunakan akun yang sudah ada melalui halaman login. Jika Anda lupa kata sandi, hubungi admin untuk bantuan reset.</p>
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

<style>
/* ---- Hero Section ---- */
.sensus-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 800px;
    margin: 0 auto;
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

/* Info cards */
.sensus-info-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    max-width: 640px;
    margin: 0 auto;
}
.sensus-info-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
    text-align: left;
}
.sensus-info-card:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 12px 30px rgba(2,6,23,.3);
}
.sensus-info-card i {
    font-size: 22px;
    color: var(--acc);
    flex-shrink: 0;
}
.sensus-info-card strong {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 2px;
}
.sensus-info-card span {
    font-size: 11px;
    color: var(--txt-2);
}

/* ---- Sensus Wrap ---- */
.sensus-wrap {
    max-width: 720px;
    margin: 0 auto;
    padding: 0 24px 80px;
}

/* ---- Success State ---- */
.sensus-success {
    text-align: center;
    padding: 60px 40px;
    margin-bottom: 40px;
    animation: success-pop 0.6s cubic-bezier(.34,1.56,.64,1);
}
@keyframes success-pop {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.sensus-success-icon {
    position: relative;
    width: 96px;
    height: 96px;
    margin: 0 auto 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981, #34d399);
    display: grid;
    place-items: center;
    font-size: 48px;
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
    font-size: 26px;
    font-weight: 800;
    margin-bottom: 10px;
}
.sensus-success p {
    color: var(--txt-1);
    font-size: 14.5px;
    margin-bottom: 28px;
    max-width: 40ch;
    margin-left: auto;
    margin-right: auto;
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
}
@keyframes alert-slide {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.sensus-alert i {
    font-size: 22px;
    flex-shrink: 0;
    margin-top: 1px;
}
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

/* ---- Stepper ---- */
.sensus-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    padding: 20px 24px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
}
.stepper-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.stepper-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: 2px solid var(--glass-brd);
    display: grid;
    place-items: center;
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
}
.stepper-line {
    flex: 1;
    height: 2px;
    background: var(--glass-brd);
    margin: 0 10px;
    position: relative;
    overflow: hidden;
}
.stepper-step.active .stepper-num {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
    color: #fff;
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}
.stepper-step.active .stepper-label {
    color: var(--txt-0);
}
.stepper-step.completed .stepper-num {
    background: linear-gradient(135deg, #10b981, #34d399);
    border-color: transparent;
    color: #fff;
}
.stepper-step.completed .stepper-label {
    color: #6ee7b7;
}
.stepper-step.completed + .stepper-line {
    background: linear-gradient(90deg, #10b981, var(--glass-brd));
}

/* ---- Form Card ---- */
.sensus-form-card {
    padding: 0;
    overflow: hidden;
}

.sensus-step {
    padding: 32px 32px 24px;
    animation: step-fade 0.4s cubic-bezier(.22,1,.36,1);
}
@keyframes step-fade {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.sensus-step-head {
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--glass-brd);
}
.sensus-step-head h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 6px;
}
.sensus-step-head p {
    font-size: 13.5px;
    color: var(--txt-1);
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
    transition: all 0.3s;
    overflow: hidden;
}
.purpose-card input { display: none; }
.purpose-card:hover {
    background: rgba(255,255,255,.04);
    border-color: var(--glass-brd-2);
}
.purpose-card.active {
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    border-color: rgba(99, 102, 241, 0.5);
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.15);
}
.purpose-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 26px;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
    transition: transform 0.3s;
}
.purpose-icon.alumni {
    background: linear-gradient(135deg, #f59e0b, #f97316);
    box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
}
.purpose-card.active .purpose-icon {
    transform: scale(1.05) rotate(-5deg);
}
.purpose-content {
    flex: 1;
    min-width: 0;
}
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
}
.purpose-features i {
    font-size: 13px;
    color: #10b981;
}
.purpose-check {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: 2px solid var(--glass-brd);
    display: grid;
    place-items: center;
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
    gap: 12px;
    padding: 20px 32px;
    background: rgba(255,255,255,.02);
    border-top: 1px solid var(--glass-brd);
}

/* ---- Field enhanced ---- */
.field-enhanced {
    margin-bottom: 18px;
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

/* ---- FAQ Section ---- */
.sensus-faq {
    margin-top: 48px;
}
.faq-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 20px;
}
.faq-title i {
    font-size: 22px;
    color: var(--acc);
}
.faq-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.faq-item {
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-md);
    overflow: hidden;
    transition: all 0.3s;
}
.faq-item:hover {
    border-color: var(--glass-brd-2);
}
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
.faq-item summary i {
    font-size: 18px;
    color: var(--txt-2);
    transition: transform 0.3s;
    flex-shrink: 0;
}
.faq-item[open] summary i {
    transform: rotate(180deg);
    color: var(--acc);
}
.faq-item p {
    padding: 0 20px 18px;
    font-size: 13px;
    color: var(--txt-1);
    line-height: 1.7;
    margin: 0;
}

/* ---- Back link ---- */
.sensus-back {
    margin-top: 32px;
    text-align: center;
}
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

/* Responsive */
@media (max-width: 768px) {
    .sensus-hero { padding-top: 120px; }
    .sensus-info-cards {
        grid-template-columns: 1fr;
    }
    .sensus-stepper {
        padding: 16px;
        overflow-x: auto;
    }
    .stepper-label { font-size: 10px; }
    .stepper-num { width: 32px; height: 32px; font-size: 12px; }
    .sensus-step { padding: 24px 20px 18px; }
    .purpose-card {
        flex-direction: column;
        padding: 18px;
    }
    .purpose-check {
        top: 14px;
        right: 14px;
    }
    .sensus-nav { padding: 16px 20px; }
}
</style>

<script>
(() => {
    'use strict';
    
    const form = document.getElementById('sensusForm');
    if (!form) return;
    
    const steps = form.querySelectorAll('.sensus-step');
    const stepperSteps = document.querySelectorAll('.stepper-step');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    
    let currentStep = 1;
    const totalSteps = steps.length;
    
    // ---- Step navigation ----
    function showStep(n) {
        steps.forEach(s => s.style.display = 'none');
        const target = form.querySelector(`.sensus-step[data-step="${n}"]`);
        if (target) target.style.display = 'block';
        
        // Update stepper
        stepperSteps.forEach((s, i) => {
            s.classList.remove('active', 'completed');
            if (i + 1 < n) s.classList.add('completed');
            else if (i + 1 === n) s.classList.add('active');
        });
        
        // Update buttons
        btnPrev.style.display = n > 1 ? 'inline-flex' : 'none';
        btnNext.style.display = n < totalSteps ? 'inline-flex' : 'none';
        btnSubmit.style.display = n === totalSteps ? 'inline-flex' : 'none';
        
        currentStep = n;
    }
    
    // ---- Validate current step ----
    function validateStep(n) {
        const step = form.querySelector(`.sensus-step[data-step="${n}"]`);
        const required = step.querySelectorAll('[required]');
        let valid = true;
        
        required.forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.classList.add('has-error');
                input.focus();
            } else {
                input.classList.remove('has-error');
            }
            
            // Email validation
            if (input.type === 'email' && input.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    valid = false;
                    input.classList.add('has-error');
                }
            }
        });
        
        if (!valid && window.toast) {
            window.toast('Mohon lengkapi semua field yang wajib diisi.', 'error');
        }
        
        return valid;
    }
    
    // ---- Button handlers ----
    btnNext?.addEventListener('click', () => {
        if (validateStep(currentStep)) {
            showStep(currentStep + 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
    
    btnPrev?.addEventListener('click', () => {
        showStep(currentStep - 1);
    });
    
    // ---- Purpose card selection ----
    document.querySelectorAll('.purpose-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.purpose-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    
    // ---- Character counters ----
    const addressField = form.querySelector('textarea[name="address"]');
    const addressCounter = document.getElementById('addressCounter');
    if (addressField && addressCounter) {
        addressField.addEventListener('input', () => {
            const len = addressField.value.length;
            addressCounter.textContent = `${len} / 300`;
            addressCounter.className = 'char-counter';
            if (len > 270) addressCounter.classList.add('warn');
            if (len > 290) addressCounter.classList.add('danger');
        });
    }
    
    const messageField = form.querySelector('textarea[name="message"]');
    const messageCounter = document.getElementById('messageCounter');
    if (messageField && messageCounter) {
        messageField.addEventListener('input', () => {
            const len = messageField.value.length;
            messageCounter.textContent = `${len} / 500`;
            messageCounter.className = 'char-counter';
            if (len > 450) messageCounter.classList.add('warn');
            if (len > 480) messageCounter.classList.add('danger');
        });
    }
    
    // ---- Initialize ----
    showStep(1);
    
    // Remove error class on input
    form.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', () => input.classList.remove('has-error'));
    });
})();
</script>