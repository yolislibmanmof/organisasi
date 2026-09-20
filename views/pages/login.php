<?php
/**
 * ============================================================
 * HALAMAN LOGIN — ULTIMATE EDITION v7.0
 * Autentikasi dengan sinematik visual, password strength,
 * caps lock detector, real-time validation, dan keyboard shortcuts.
 * ============================================================
 */

$flash = \Core\Session::getFlash('login_error');
?>

<style>
/* ---- Layout & Base ---- */
@media (min-width: 821px) {
    .auth-brand { padding-top: 88px; }
}
.back-home {
    background: rgba(10, 15, 31, .65);
    border-color: rgba(255, 255, 255, .18);
    color: var(--txt-0);
}
.back-home:hover {
    background: rgba(99, 102, 241, .25);
    border-color: rgba(99, 102, 241, .5);
    color: #fff;
}

/* ---- Form Card Entry Animation ---- */
.auth-form-card {
    animation: card-enter 0.6s cubic-bezier(.22,1,.36,1) both;
}
@keyframes card-enter {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ---- Brand Animation ---- */
.auth-brand {
    animation: brand-slide 0.8s cubic-bezier(.22,1,.36,1) both;
}
@keyframes brand-slide {
    from {
        opacity: 0;
        transform: translateX(-40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ---- Password Strength Meter (Enhanced) ---- */
.password-strength {
    margin-top: 10px;
    padding: 12px;
    border-radius: 10px;
    background: rgba(255,255,255,.02);
    border: 1px solid var(--glass-brd);
}
.strength-bars {
    display: flex;
    gap: 4px;
    margin-bottom: 8px;
}
.strength-bar {
    flex: 1;
    height: 5px;
    border-radius: 3px;
    background: rgba(255,255,255,.08);
    transition: all 0.4s cubic-bezier(.22,1,.36,1);
}
.strength-bar.active.weak { background: var(--danger); box-shadow: 0 0 10px rgba(239,68,68,.3); }
.strength-bar.active.fair { background: var(--warn); box-shadow: 0 0 10px rgba(245,158,11,.3); }
.strength-bar.active.good { background: #22d3ee; box-shadow: 0 0 10px rgba(34,211,238,.3); }
.strength-bar.active.strong { background: var(--ok); box-shadow: 0 0 10px rgba(16,185,129,.3); }
.strength-text {
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
}
.strength-text i { font-size: 13px; }
.strength-text.weak { color: var(--danger); }
.strength-text.fair { color: var(--warn); }
.strength-text.good { color: #22d3ee; }
.strength-text.strong { color: var(--ok); }

.strength-tips {
    margin-top: 8px;
    font-size: 10.5px;
    color: var(--txt-2);
    line-height: 1.5;
}
.strength-tips li {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 2px 0;
}
.strength-tips li i {
    font-size: 12px;
    flex-shrink: 0;
}
.strength-tips li.met {
    color: var(--ok);
}
.strength-tips li.met i { color: var(--ok); }

/* ---- Caps Lock Warning ---- */
.caps-warning {
    display: none;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    margin-top: 8px;
    border-radius: 8px;
    background: rgba(245, 158, 11, 0.08);
    border: 1px solid rgba(245, 158, 11, 0.25);
    font-size: 12px;
    color: #fbbf24;
    animation: alert-shake 0.4s;
}
.caps-warning.show { display: flex; }
.caps-warning i { font-size: 16px; flex-shrink: 0; }

/* ---- Enhanced Alert ---- */
.alert-enhanced {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 12px;
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.25);
    animation: alert-shake 0.5s cubic-bezier(.36,.07,.19,.97);
}
.alert-enhanced i {
    font-size: 20px;
    color: var(--danger);
    flex-shrink: 0;
    margin-top: 1px;
}
.alert-enhanced-content { flex: 1; }
.alert-enhanced-title {
    font-size: 13px;
    font-weight: 700;
    color: #fca5a5;
    margin-bottom: 2px;
}
.alert-enhanced-msg {
    font-size: 12px;
    color: var(--txt-1);
}
@keyframes alert-shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
    20%, 40%, 60%, 80% { transform: translateX(4px); }
}

/* ---- Input Validation States ---- */
.field-input-wrap.has-success input {
    border-color: var(--ok);
    box-shadow: 0 0 0 3px rgba(16,185,129,.1);
}
.field-input-wrap.has-error input {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px rgba(239,68,68,.1);
    animation: input-shake 0.4s;
}
.field-error-msg {
    display: none;
    font-size: 11px;
    color: var(--danger);
    margin-top: 6px;
    padding-left: 2px;
}
.field-input-wrap.has-error + .field-error-msg { display: block; }
@keyframes input-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}
.field-validation-icon {
    position: absolute;
    right: 44px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0;
    transition: opacity 0.2s;
}
.field-input-wrap.has-success .field-validation-icon.success,
.field-input-wrap.has-error .field-validation-icon.error {
    opacity: 1;
}
.field-validation-icon.success { color: var(--ok); }
.field-validation-icon.error { color: var(--danger); }

/* ---- Social Login Cards ---- */
.auth-oauth {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.oauth-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-0);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    overflow: hidden;
}
.oauth-btn:not(:disabled):hover {
    background: rgba(255,255,255,.08);
    border-color: var(--glass-brd-2);
    transform: translateY(-2px);
}
.oauth-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.oauth-btn::after {
    content: 'Segera';
    position: absolute;
    top: 6px;
    right: 6px;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(245, 158, 11, 0.15);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.oauth-btn.google:hover:not(:disabled) {
    border-color: rgba(66, 133, 244, 0.5);
    background: rgba(66, 133, 244, 0.1);
}
.oauth-btn.microsoft:hover:not(:disabled) {
    border-color: rgba(94, 95, 255, 0.5);
    background: rgba(94, 95, 255, 0.1);
}

/* ---- Remember Me Enhanced ---- */
.check-field {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
}
.check-field input { display: none; }
.check-mark {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 2px solid rgba(255,255,255,.2);
    background: rgba(255,255,255,.03);
    display: grid;
    place-items: center;
    transition: all 0.2s;
    flex-shrink: 0;
}
.check-mark::after {
    content: '';
    width: 10px;
    height: 6px;
    border-left: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(-45deg) scale(0);
    transition: transform 0.2s;
}
.check-field input:checked + .check-mark {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
}
.check-field input:checked + .check-mark::after {
    transform: rotate(-45deg) scale(1);
}
.check-field span:last-child {
    font-size: 12.5px;
    color: var(--txt-1);
}

/* ---- Submit Button Loading ---- */
.btn-auth {
    position: relative;
    overflow: hidden;
}
.btn-auth.is-loading .btn-text,
.btn-auth.is-loading .btn-arrow {
    visibility: hidden;
}
.btn-auth.is-loading::after {
    content: '';
    position: absolute;
    inset: 0;
    margin: auto;
    width: 22px;
    height: 22px;
    border: 2.5px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

/* ---- Keyboard Shortcuts Hint ---- */
.auth-keyboard-hint {
    margin-top: 20px;
    text-align: center;
    font-size: 11.5px;
    color: var(--txt-2);
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}
.auth-keyboard-hint kbd {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 5px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    font-family: inherit;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--txt-1);
    box-shadow: 0 2px 0 rgba(0,0,0,.2);
}

/* ---- Secure Badge Pulse ---- */
.secure-badge {
    animation: secure-pulse 3s ease-in-out infinite;
}
@keyframes secure-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
    50% { box-shadow: 0 0 0 8px rgba(16,185,129,0.1); }
}

/* ---- Form Card Hover Glow ---- */
.auth-form-card {
    transition: border-color 0.3s;
}
.auth-form-card.is-hovering {
    border-color: rgba(99, 102, 241, 0.4);
}

/* ---- Keyboard Shortcuts Overlay ---- */
.shortcuts-overlay {
    position: fixed;
    inset: 0;
    background: rgba(10, 15, 31, 0.8);
    backdrop-filter: blur(8px);
    display: grid;
    place-items: center;
    z-index: 200;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.shortcuts-overlay.show {
    opacity: 1;
    visibility: visible;
}
.shortcuts-modal {
    max-width: 500px;
    width: 90%;
    padding: 24px;
    border-radius: 16px;
    background: rgba(15, 21, 48, 0.95);
    border: 1px solid var(--glass-brd);
    box-shadow: 0 30px 80px rgba(0,0,0,.5);
    transform: scale(0.9) translateY(20px);
    transition: transform 0.3s cubic-bezier(.22,1,.36,1);
}
.shortcuts-overlay.show .shortcuts-modal {
    transform: scale(1) translateY(0);
}
.shortcuts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-brd);
}
.shortcuts-header h3 {
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.shortcuts-header i { color: var(--acc); }
.shortcuts-close {
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    width: 32px;
    height: 32px;
    border-radius: 8px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.shortcuts-close:hover {
    background: rgba(239,68,68,.15);
    border-color: var(--danger);
    color: var(--danger);
}
.shortcuts-grid {
    display: grid;
    gap: 10px;
}
.shortcut-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
}
.shortcut-item kbd {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    color: var(--txt-0);
    box-shadow: 0 2px 0 rgba(0,0,0,.2);
    min-width: 60px;
    text-align: center;
}
.shortcut-item span {
    font-size: 13px;
    color: var(--txt-1);
}

/* ---- Rotating Testimonials ---- */
.testimonial-carousel {
    position: relative;
    min-height: 80px;
}
.testimonial-item {
    position: absolute;
    inset: 0;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.5s cubic-bezier(.22,1,.36,1);
}
.testimonial-item.active {
    opacity: 1;
    transform: translateX(0);
}
.testimonial-indicators {
    display: flex;
    gap: 6px;
    margin-top: 12px;
}
.testimonial-indicators button {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,.2);
    cursor: pointer;
    transition: all 0.3s;
    padding: 0;
}
.testimonial-indicators button.active {
    background: var(--acc);
    width: 18px;
    border-radius: 3px;
}

/* ---- Responsive ---- */
@media (max-width: 520px) {
    .auth-oauth { grid-template-columns: 1fr; }
    .form-options { flex-direction: column; align-items: flex-start; gap: 12px; }
    .shortcuts-modal { padding: 18px; }
    .shortcut-item { padding: 8px 12px; }
}
</style>

<!-- Back to home link -->
<a href="<?= url('') ?>" class="back-home">
    <i class="ph ph-arrow-left"></i>
    <span>Kembali ke beranda</span>
</a>

<div class="auth-stage">
    
    <!-- ============ LEFT SIDE: BRAND STORYTELLING ============ -->
    <aside class="auth-brand">
        <div class="brand-inner">
            <div class="brand-top">
                <div class="brand-logo-wrap">
                    <span class="brand-logo">
                        <span class="brand-logo-glow"></span>
                        <span class="brand-logo-text">OU</span>
                    </span>
                    <span class="brand-name"><?= e(APP_NAME) ?></span>
                    <span class="brand-version">v7.0 Ultimate</span>
                </div>
            </div>
            
            <div class="brand-content">
                <h1 class="brand-headline">
                    Masa depan <em>manajemen organisasi</em> dimulai di sini.
                </h1>
                <p class="brand-sub">
                    Platform all-in-one untuk mengelola anggota, kegiatan, dan pertumbuhan
                    organisasi Anda dengan antarmuka modern yang memukau.
                </p>
                
                <!-- Typewriter tagline -->
                <div class="brand-typewriter">
                    <span class="tw-cursor"></span>
                    <span id="typewriterText" class="tw-text"></span>
                </div>
                
                <!-- Feature showcase -->
                <ul class="brand-features">
                    <li>
                        <span class="feat-icon grad-1"><i class="ph ph-users-three"></i></span>
                        <div>
                            <strong>Manajemen Anggota</strong>
                            <span>CRUD real-time dengan pencarian instan</span>
                        </div>
                    </li>
                    <li>
                        <span class="feat-icon grad-2"><i class="ph ph-chart-line-up"></i></span>
                        <div>
                            <strong>Analitik Visual</strong>
                            <span>Grafik interaktif pertumbuhan anggota</span>
                        </div>
                    </li>
                    <li>
                        <span class="feat-icon grad-3"><i class="ph ph-shield-check"></i></span>
                        <div>
                            <strong>Keamanan Tingkat Tinggi</strong>
                            <span>Proteksi CSRF, bcrypt, dan session hardened</span>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="brand-bottom">
                <div class="brand-testimonial">
                    <div class="testimonial-avatars">
                        <span class="t-av" style="background:linear-gradient(135deg,#f472b6,#ec4899)">A</span>
                        <span class="t-av" style="background:linear-gradient(135deg,#60a5fa,#3b82f6)">B</span>
                        <span class="t-av" style="background:linear-gradient(135deg,#34d399,#10b981)">C</span>
                        <span class="t-av" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)">D</span>
                        <span class="t-av t-av-more">+120</span>
                    </div>
                    
                    <!-- Rotating testimonials -->
                    <div class="testimonial-carousel" id="testimonialCarousel">
                        <div class="testimonial-item active" data-index="0">
                            <p><em>"Platform paling elegan yang pernah kami gunakan untuk mengelola organisasi."</em></p>
                            <small>— Admin, Organisasi A</small>
                        </div>
                        <div class="testimonial-item" data-index="1">
                            <p><em>"Fitur analitiknya membantu kami memahami pertumbuhan anggota dengan jelas."</em></p>
                            <small>— Sekretaris, Organisasi B</small>
                        </div>
                        <div class="testimonial-item" data-index="2">
                            <p><em>"Interface yang intuitif membuat semua anggota mudah beradaptasi."</em></p>
                            <small>— Bendahara, Organisasi C</small>
                        </div>
                    </div>
                    <div class="testimonial-indicators">
                        <button class="active" data-slide="0" aria-label="Testimonial 1"></button>
                        <button data-slide="1" aria-label="Testimonial 2"></button>
                        <button data-slide="2" aria-label="Testimonial 3"></button>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- ============ RIGHT SIDE: LOGIN FORM ============ -->
    <main class="auth-form-wrap">
        <div class="auth-form-card glass-card" id="authFormCard">
            
            <!-- Secure connection badge -->
            <div class="secure-badge">
                <span class="secure-dot"></span>
                <i class="ph ph-lock-simple"></i>
                <span>Koneksi aman terenkripsi</span>
            </div>
            
            <!-- Time-based greeting -->
            <div class="greeting" id="timeGreeting">
                <!-- Filled by JS -->
            </div>
            
            <div class="form-head">
                <h2>Masuk ke akun Anda</h2>
                <p>Portal khusus anggota. Kelola organisasi Anda dengan beberapa klik.</p>
            </div>
            
            <!-- Flash error message -->
            <?php if ($flash): ?>
                <div class="alert-enhanced" role="alert">
                    <i class="ph ph-warning-circle"></i>
                    <div class="alert-enhanced-content">
                        <div class="alert-enhanced-title">Gagal Masuk</div>
                        <div class="alert-enhanced-msg"><?= e($flash['message']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?= url('login') ?>" id="loginForm" autocomplete="on" novalidate data-auth-form>
                <?= csrf_field() ?>
                
                <!-- Username field -->
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-user-circle"></i> Username atau Email
                    </span>
                    <div class="field-input-wrap" id="usernameWrap">
                        <input 
                            type="text" 
                            name="username" 
                            id="username"
                            placeholder="nama@organisasi.id" 
                            required 
                            autofocus
                            autocomplete="username"
                            aria-describedby="usernameError">
                        <span class="field-validation-icon success"><i class="ph ph-check-circle"></i></span>
                        <span class="field-validation-icon error"><i class="ph ph-x-circle"></i></span>
                        <span class="field-focus-ring"></span>
                    </div>
                    <span class="field-error-msg" id="usernameError">Username atau email tidak valid</span>
                </label>
                
                <!-- Password field -->
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-lock-key"></i> Kata Sandi
                    </span>
                    <div class="field-input-wrap" id="passwordWrap">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            placeholder="••••••••" 
                            required
                            autocomplete="current-password"
                            aria-describedby="passwordError capsWarning">
                        <button type="button" class="pass-toggle" id="passToggle" aria-label="Tampilkan kata sandi">
                            <i class="ph ph-eye" id="passToggleIcon"></i>
                        </button>
                        <span class="field-focus-ring"></span>
                    </div>
                    <span class="field-error-msg" id="passwordError">Kata sandi wajib diisi</span>
                    
                    <!-- Caps lock warning -->
                    <div class="caps-warning" id="capsWarning" role="alert">
                        <i class="ph ph-warning"></i>
                        <span>Caps Lock aktif! Pastikan Anda memasukkan kata sandi dengan benar.</span>
                    </div>
                    
                    <!-- Password strength meter -->
                    <div class="password-strength" id="passwordStrength" style="display:none">
                        <div class="strength-bars">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                        <span class="strength-text" id="strengthText">
                            <i class="ph ph-info"></i>
                            <span>Kekuatan kata sandi</span>
                        </span>
                        <ul class="strength-tips" id="strengthTips" style="display:none">
                            <li data-rule="length"><i class="ph ph-x-circle"></i> Minimal 8 karakter</li>
                            <li data-rule="uppercase"><i class="ph ph-x-circle"></i> Huruf besar (A-Z)</li>
                            <li data-rule="lowercase"><i class="ph ph-x-circle"></i> Huruf kecil (a-z)</li>
                            <li data-rule="number"><i class="ph ph-x-circle"></i> Angka (0-9)</li>
                            <li data-rule="special"><i class="ph ph-x-circle"></i> Karakter spesial (!@#$%)</li>
                        </ul>
                    </div>
                </label>
                
                <!-- Remember me & forgot -->
                <div class="form-options">
                    <label class="check-field">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="check-mark"></span>
                        <span>Ingat saya selama 30 hari</span>
                    </label>
                    <a href="#" class="link-forgot" id="forgotLink">Lupa kata sandi?</a>
                </div>
                
                <!-- Submit button -->
                <button type="submit" class="btn btn-primary btn-block btn-auth" id="submitBtn">
                    <span class="btn-text">Masuk ke Dashboard</span>
                    <i class="ph ph-arrow-right btn-arrow"></i>
                </button>
                
                <!-- OAuth divider -->
                <div class="auth-divider">
                    <span>atau lanjutkan dengan</span>
                </div>
                
                <!-- OAuth buttons -->
                <div class="auth-oauth">
                    <button type="button" class="oauth-btn google" disabled title="Segera hadir">
                        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        <span>Google</span>
                    </button>
                    <button type="button" class="oauth-btn microsoft" disabled title="Segera hadir">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none">
                            <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                            <rect x="13" y="1" width="10" height="10" fill="#7FBA00"/>
                            <rect x="1" y="13" width="10" height="10" fill="#00A4EF"/>
                            <rect x="13" y="13" width="10" height="10" fill="#FFB900"/>
                        </svg>
                        <span>Microsoft</span>
                    </button>
                </div>
            </form>
            
            <!-- Footer -->
            <p class="auth-foot">
                Bukan anggota? <a href="<?= url('') ?>" class="link-accent">Kunjungi situs publik</a>
            </p>
            
            <div class="auth-copyright">
                <i class="ph ph-shield-check"></i>
                <span>© <?= date('Y') ?> <?= e(APP_NAME) ?> — Seluruh hak cipta dilindungi.</span>
            </div>
        </div>
        
        <!-- Keyboard shortcuts hint -->
        <div class="auth-keyboard-hint">
            <span><kbd>Enter</kbd> untuk masuk</span>
            <span><kbd>Tab</kbd> untuk berpindah field</span>
            <span><kbd>?</kbd> untuk pintasan</span>
        </div>
    </main>
</div>

<!-- Keyboard Shortcuts Overlay -->
<div class="shortcuts-overlay" id="shortcutsOverlay">
    <div class="shortcuts-modal glass-card">
        <div class="shortcuts-header">
            <h3><i class="ph ph-keyboard"></i> Pintasan Keyboard</h3>
            <button class="shortcuts-close" id="shortcutsClose" aria-label="Tutup"><i class="ph ph-x"></i></button>
        </div>
        <div class="shortcuts-grid">
            <div class="shortcut-item">
                <kbd>Enter</kbd>
                <span>Kirim formulir login</span>
            </div>
            <div class="shortcut-item">
                <kbd>Tab</kbd>
                <span>Berpindah ke field berikutnya</span>
            </div>
            <div class="shortcut-item">
                <kbd>Shift + Tab</kbd>
                <span>Berpindah ke field sebelumnya</span>
            </div>
            <div class="shortcut-item">
                <kbd>Esc</kbd>
                <span>Tutup dialog / kembali</span>
            </div>
            <div class="shortcut-item">
                <kbd>?</kbd>
                <span>Tampilkan pintasan ini</span>
            </div>
        </div>
    </div>
</div>

<script>
/* =========================================================
   LOGIN ULTIMATE v7.0 — Enhanced Interactions
   ========================================================= */
(() => {
    'use strict';
    
    // ---- 1. Mouse glow on form card ----
    const card = document.getElementById('authFormCard');
    if (card) {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mx', (e.clientX - rect.left) + 'px');
            card.style.setProperty('--my', (e.clientY - rect.top) + 'px');
            card.classList.add('is-hovering');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('is-hovering');
        });
    }
    
    // ---- 2. Password toggle ----
    const passToggle = document.getElementById('passToggle');
    const passInput = document.getElementById('password');
    const passIcon = document.getElementById('passToggleIcon');
    
    if (passToggle && passInput) {
        passToggle.addEventListener('click', () => {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            passIcon.className = isPassword ? 'ph ph-eye-slash' : 'ph ph-eye';
            passToggle.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    }
    
    // ---- 3. Caps Lock Detector ----
    const capsWarning = document.getElementById('capsWarning');
    if (passInput && capsWarning) {
        passInput.addEventListener('keyup', (e) => {
            if (e.getModifierState && e.getModifierState('CapsLock')) {
                capsWarning.classList.add('show');
            } else {
                capsWarning.classList.remove('show');
            }
        });
        passInput.addEventListener('blur', () => {
            capsWarning.classList.remove('show');
        });
    }
    
    // ---- 4. Enhanced Password Strength Meter ----
    const strengthMeter = document.getElementById('passwordStrength');
    const strengthBars = strengthMeter?.querySelectorAll('.strength-bar');
    const strengthText = document.getElementById('strengthText');
    const strengthTips = document.getElementById('strengthTips');
    
    function checkPasswordStrength(password) {
        const rules = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };
        
        let score = 0;
        if (password.length >= 6) score++;
        if (rules.length && rules.uppercase && rules.lowercase) score++;
        if (rules.number) score++;
        if (rules.special) score++;
        
        return { score: Math.min(score, 4), rules };
    }
    
    if (passInput && strengthMeter && strengthBars && strengthText && strengthTips) {
        passInput.addEventListener('input', () => {
            const val = passInput.value;
            if (val.length === 0) {
                strengthMeter.style.display = 'none';
                return;
            }
            strengthMeter.style.display = 'block';
            strengthTips.style.display = 'block';
            
            const { score, rules } = checkPasswordStrength(val);
            const levels = ['weak', 'weak', 'fair', 'good', 'strong'];
            const labels = ['Sangat lemah', 'Lemah', 'Cukup', 'Baik', 'Kuat'];
            const icons = ['ph-warning', 'ph-warning', 'ph-info', 'ph-check-circle', 'ph-shield-check'];
            const level = levels[score];
            
            strengthBars.forEach((bar, i) => {
                bar.className = 'strength-bar';
                if (i < score) {
                    bar.classList.add('active', level);
                }
            });
            
            strengthText.className = 'strength-text ' + level;
            strengthText.innerHTML = `<i class="ph ${icons[score]}"></i><span>${labels[score]}</span>`;
            
            // Update tips
            strengthTips.querySelectorAll('li').forEach(li => {
                const rule = li.dataset.rule;
                if (rules[rule]) {
                    li.classList.add('met');
                    li.querySelector('i').className = 'ph ph-check-circle';
                } else {
                    li.classList.remove('met');
                    li.querySelector('i').className = 'ph ph-x-circle';
                }
            });
        });
    }
    
    // ---- 5. Username validation ----
    const usernameInput = document.getElementById('username');
    const usernameWrap = document.getElementById('usernameWrap');
    
    if (usernameInput && usernameWrap) {
        let debounce;
        usernameInput.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                const val = usernameInput.value.trim();
                usernameWrap.classList.remove('has-success', 'has-error');
                if (val.length === 0) return;
                
                const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                const isValid = isEmail || val.length >= 3;
                
                usernameWrap.classList.add(isValid ? 'has-success' : 'has-error');
            }, 300);
        });
    }
    
    // ---- 6. Form submit loading state ----
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', () => {
            submitBtn.classList.add('is-loading');
            submitBtn.disabled = true;
        });
    }
    
    // ---- 7. Forgot password handler ----
    const forgotLink = document.getElementById('forgotLink');
    if (forgotLink) {
        forgotLink.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.toast) {
                window.toast('Fitur reset kata sandi akan segera tersedia. Silakan hubungi administrator.', 'error');
            }
        });
    }
    
    // ---- 8. Keyboard shortcuts ----
    const shortcutsOverlay = document.getElementById('shortcutsOverlay');
    const shortcutsClose = document.getElementById('shortcutsClose');
    
    document.addEventListener('keydown', (e) => {
        if (e.key === '?' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            const tag = document.activeElement.tagName;
            if (tag !== 'INPUT' && tag !== 'TEXTAREA') {
                e.preventDefault();
                shortcutsOverlay?.classList.add('show');
            }
        }
        if (e.key === 'Escape') {
            shortcutsOverlay?.classList.remove('show');
        }
    });
    
    shortcutsClose?.addEventListener('click', () => {
        shortcutsOverlay?.classList.remove('show');
    });
    
    shortcutsOverlay?.addEventListener('click', (e) => {
        if (e.target === shortcutsOverlay) {
            shortcutsOverlay.classList.remove('show');
        }
    });
    
    // ---- 9. Auto-focus handling ----
    if (usernameInput && passInput && usernameInput.value) {
        passInput.focus();
    }
    
    // ---- 10. Rotating testimonials ----
    const carousel = document.getElementById('testimonialCarousel');
    const indicators = document.querySelectorAll('.testimonial-indicators button');
    
    if (carousel && indicators.length > 0) {
        let currentSlide = 0;
        const slides = carousel.querySelectorAll('.testimonial-item');
        const totalSlides = slides.length;
        
        const showSlide = (index) => {
            slides.forEach(s => s.classList.remove('active'));
            indicators.forEach(i => i.classList.remove('active'));
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            currentSlide = index;
        };
        
        indicators.forEach((btn, i) => {
            btn.addEventListener('click', () => showSlide(i));
        });
        
        // Auto-rotate every 5 seconds
        setInterval(() => {
            const next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }, 5000);
    }
})();
</script>