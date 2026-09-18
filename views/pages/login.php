<!-- File: views/pages/login.php (FINAL - TAHAP 5.1) -->

<!-- Tautan kembali ke landing page publik -->
<a href="<?= url('') ?>" class="back-home">
    <i class="ph ph-arrow-left"></i>
    <span>Kembali ke beranda</span>
</a>

<div class="auth-stage">
    
    <!-- ============ SISI KIRI: BRAND STORYTELLING ============ -->
    <aside class="auth-brand">
        <div class="brand-inner">
            <div class="brand-top">
                <div class="brand-logo-wrap">
                    <span class="brand-logo">
                        <span class="brand-logo-glow"></span>
                        <span class="brand-logo-text">OU</span>
                    </span>
                    <span class="brand-name"><?= e(APP_NAME) ?></span>
                    <span class="brand-version">v3.0 Ultimate</span>
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
                    <p>
                        <em>"Platform paling elegan yang pernah kami gunakan untuk mengelola organisasi."</em>
                    </p>
                </div>
            </div>
        </div>
    </aside>
    
    <!-- ============ SISI KANAN: FORM LOGIN ============ -->
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
                <!-- Diisi oleh JS -->
            </div>
            
            <div class="form-head">
                <h2>Masuk ke akun Anda</h2>
                <p>Portal khusus anggota. Kelola organisasi Anda dengan beberapa klik.</p>
            </div>
            
            <?php $flash = \Core\Session::getFlash('login_error'); if ($flash): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?= url('login') ?>" id="loginForm" autocomplete="on">
                <?= csrf_field() ?>
                
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-user-circle"></i> Username atau Email
                    </span>
                    <div class="field-input-wrap">
                        <input 
                            type="text" 
                            name="username" 
                            placeholder="nama@organisasi.id" 
                            required 
                            autofocus
                            autocomplete="username">
                        <span class="field-focus-ring"></span>
                    </div>
                </label>
                
                <label class="field field-enhanced">
                    <span class="field-label">
                        <i class="ph ph-lock-key"></i> Kata Sandi
                    </span>
                    <div class="field-input-wrap">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            placeholder="••••••••" 
                            required
                            autocomplete="current-password">
                        <button type="button" class="pass-toggle" data-toggle="password" aria-label="Tampilkan kata sandi">
                            <i class="ph ph-eye"></i>
                        </button>
                        <span class="field-focus-ring"></span>
                    </div>
                </label>
                
                <div class="form-options">
                    <label class="check-field">
                        <input type="checkbox" name="remember">
                        <span class="check-mark"></span>
                        <span>Ingat saya selama 30 hari</span>
                    </label>
                    <a href="#" class="link-forgot">Lupa kata sandi?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-auth">
                    <span class="btn-text">Masuk ke Dashboard</span>
                    <i class="ph ph-arrow-right btn-arrow"></i>
                </button>
                
                <div class="auth-divider">
                    <span>atau lanjutkan dengan</span>
                </div>
                
                <div class="auth-oauth">
                    <button type="button" class="oauth-btn" disabled title="Segera hadir">
                        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        <span>Google</span>
                    </button>
                    <button type="button" class="oauth-btn" disabled title="Segera hadir">
                        <i class="ph ph-microsoft-teams-logo" style="font-size:18px;color:#5e5fff;"></i>
                        <span>Microsoft</span>
                    </button>
                </div>
            </form>
            
            <p class="auth-foot">
                Bukan anggota? <a href="<?= url('') ?>" class="link-accent">Kunjungi situs publik</a>
            </p>
            
            <div class="auth-copyright">
                <i class="ph ph-shield-check"></i>
                <span>© <?= date('Y') ?> <?= e(APP_NAME) ?> — Seluruh hak cipta dilindungi.</span>
            </div>
        </div>
        
        <div class="auth-keyboard-hint">
            <kbd>Enter</kbd> untuk masuk • <kbd>Tab</kbd> untuk berpindah field
        </div>
    </main>
</div>

<script>
/* =========================================================
   MOUSE GLOW pada kartu form (efek cahaya mengikuti kursor)
   ========================================================= */
(() => {
    const card = document.getElementById('authFormCard');
    if (!card) return;
    
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        card.style.setProperty('--mx', x + 'px');
        card.style.setProperty('--my', y + 'px');
        card.classList.add('is-hovering');
    });
    card.addEventListener('mouseleave', () => {
        card.classList.remove('is-hovering');
    });
})();
</script>