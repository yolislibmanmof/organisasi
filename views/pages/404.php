<?php
/**
 * ============================================================
 * HALAMAN ERROR 404 — ULTIMATE EDITION v5.9
 * Halaman "tidak ditemukan" dengan sinematik visual,
 * animasi floating elements, dan pengalaman yang engaging.
 * ============================================================
 */
?>

<!-- ========== 404 CINEMATIC ========== -->
<section class="error-hero" aria-labelledby="error-title">
    <!-- Floating decorative elements -->
    <div class="error-decor" aria-hidden="true">
        <span class="error-shape shape-1"><i class="ph ph-question"></i></span>
        <span class="error-shape shape-2"><i class="ph ph-map-trifold"></i></span>
        <span class="error-shape shape-3"><i class="ph ph-compass"></i></span>
        <span class="error-shape shape-4"><i class="ph ph-path"></i></span>
    </div>

    <!-- Main error card -->
    <article class="error-card glass-card reveal">
        <!-- Animated illustration -->
        <div class="error-visual" aria-hidden="true">
            <div class="error-icon-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <div class="error-pulse"></div>
                <div class="error-orbit"></div>
            </div>
            <div class="error-code">
                <span class="code-num">4</span>
                <span class="code-num animated">0</span>
                <span class="code-num">4</span>
            </div>
        </div>

        <!-- Content -->
        <div class="error-content">
            <span class="error-badge">
                <i class="ph ph-warning-circle"></i>
                Halaman Tidak Ditemukan
            </span>
            
            <h1 id="error-title" class="error-title">
                Oops! Anda <em>tersesat</em> di sini
            </h1>
            
            <p class="error-desc">
                Halaman yang Anda tuju mungkin telah dipindahkan, dihapus, atau tidak pernah ada. 
                Tapi jangan khawatir, kami siap membantu Anda kembali ke jalur yang benar.
            </p>

            <!-- Quick actions -->
            <div class="error-actions">
                <a href="<?= url('') ?>" class="btn btn-primary btn-lg">
                    <i class="ph ph-house"></i>
                    <span class="btn-text">Kembali ke Beranda</span>
                    <i class="ph ph-arrow-right btn-arrow"></i>
                </a>
                <button onclick="history.back()" class="btn btn-ghost">
                    <i class="ph ph-arrow-left"></i>
                    <span class="btn-text">Halaman Sebelumnya</span>
                </button>
            </div>

            <!-- Helpful links -->
            <div class="error-helpful">
                <p class="error-helpful-title">
                    <i class="ph ph-lightbulb"></i>
                    Mungkin Anda mencari:
                </p>
                <div class="error-links">
                    <a href="<?= url('tentang') ?>" class="error-link">
                        <i class="ph ph-info"></i>
                        <span>Tentang Kami</span>
                    </a>
                    <a href="<?= url('event') ?>" class="error-link">
                        <i class="ph ph-calendar-blank"></i>
                        <span>Event & Kegiatan</span>
                    </a>
                    <a href="<?= url('artikel') ?>" class="error-link">
                        <i class="ph ph-newspaper"></i>
                        <span>Artikel Terbaru</span>
                    </a>
                    <a href="<?= url('') ?>#kontak" class="error-link" data-scroll>
                        <i class="ph ph-envelope-simple"></i>
                        <span>Hubungi Kami</span>
                    </a>
                </div>
            </div>
        </div>
    </article>

    <!-- Fun fact / Easter egg -->
    <div class="error-funfact reveal" aria-label="Fakta menarik">
        <i class="ph ph-star"></i>
        <span>Tahukah Anda? Kode error 404 pertama kali muncul pada tahun 1991 di server CERN.</span>
    </div>
</section>

<!-- ========== STYLING ========== -->
<style>
/* ---- Error Hero Container ---- */
.error-hero {
    position: relative;
    min-height: calc(100vh - 80px);
    display: grid;
    place-items: center;
    padding: 80px 24px 60px;
    overflow: hidden;
}

/* ---- Floating decorative shapes ---- */
.error-decor {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.error-shape {
    position: absolute;
    width: 56px;
    height: 56px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(10px);
    color: rgba(255,255,255,0.3);
    font-size: 26px;
    animation: error-float 14s ease-in-out infinite;
}
.shape-1 { top: 12%; left: 8%; animation-delay: 0s; }
.shape-2 { top: 68%; left: 12%; animation-delay: -4s; }
.shape-3 { top: 18%; right: 10%; animation-delay: -8s; }
.shape-4 { bottom: 14%; right: 8%; animation-delay: -12s; }

@keyframes error-float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(12deg); }
}

/* ---- Error Card ---- */
.error-card {
    position: relative;
    z-index: 1;
    max-width: 640px;
    width: 100%;
    padding: 60px 48px;
    text-align: center;
    animation: error-enter 0.8s cubic-bezier(.22,1,.36,1) both;
}

@keyframes error-enter {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ---- Visual / Icon ---- */
.error-visual {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto 32px;
}

.error-icon-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto;
    border-radius: 36px;
    background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(34,211,238,.2));
    display: grid;
    place-items: center;
    z-index: 2;
}
.error-icon-wrap i {
    font-size: 56px;
    color: var(--acc);
    animation: icon-bounce 2s ease-in-out infinite;
}

@keyframes icon-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}

.error-pulse {
    position: absolute;
    inset: -8px;
    border-radius: 44px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(14px);
    z-index: 1;
    animation: spin 4s linear infinite;
}

.error-orbit {
    position: absolute;
    inset: -20px;
    border-radius: 50%;
    border: 2px dashed rgba(99,102,241,.3);
    animation: spin 20s linear infinite reverse;
}

/* ---- Error Code Numbers ---- */
.error-code {
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 4px;
    z-index: 3;
}
.code-num {
    width: 40px;
    height: 48px;
    display: grid;
    place-items: center;
    background: var(--bg-1);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
    font-size: 24px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 8px 20px rgba(0,0,0,.3);
}
.code-num.animated {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    animation: code-flip 3s ease-in-out infinite;
}

@keyframes code-flip {
    0%, 100% { transform: rotateY(0deg); }
    50% { transform: rotateY(180deg); }
}

/* ---- Content ---- */
.error-content {
    position: relative;
    z-index: 2;
}

.error-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 99px;
    background: rgba(239,68,68,.12);
    border: 1px solid rgba(239,68,68,.3);
    color: #fca5a5;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    margin-bottom: 20px;
    animation: badge-pop 0.5s cubic-bezier(.34,1.56,.64,1) 0.3s both;
}
.error-badge i {
    font-size: 14px;
}

.error-title {
    font-size: clamp(28px, 5vw, 42px);
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1.15;
    margin-bottom: 18px;
}
.error-title em {
    font-family: 'Instrument Serif', serif;
    font-style: italic;
    font-weight: 400;
    background: linear-gradient(135deg, #a5b4fc, #67e8f9);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.error-desc {
    color: var(--txt-1);
    font-size: 15px;
    line-height: 1.75;
    max-width: 44ch;
    margin: 0 auto 32px;
}

/* ---- Actions ---- */
.error-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 36px;
}

/* ---- Helpful Links ---- */
.error-helpful {
    padding-top: 28px;
    border-top: 1px solid var(--glass-brd);
}
.error-helpful-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--txt-2);
    margin-bottom: 16px;
}
.error-helpful-title i {
    color: var(--warn);
    font-size: 16px;
}

.error-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    max-width: 500px;
    margin: 0 auto;
}
.error-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border-radius: 10px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(.22,1,.36,1);
}
.error-link i {
    font-size: 16px;
    color: var(--acc);
}
.error-link:hover {
    background: rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.3);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99,102,241,.2);
}

/* ---- Fun Fact ---- */
.error-funfact {
    position: relative;
    z-index: 1;
    margin-top: 40px;
    padding: 12px 20px;
    border-radius: 99px;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.2);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--txt-1);
    animation: fade-up 0.6s 0.8s both;
}
.error-funfact i {
    color: var(--warn);
    font-size: 16px;
}

/* ---- Responsive ---- */
@media (max-width: 720px) {
    .error-card {
        padding: 48px 28px;
    }
    .error-visual {
        width: 120px;
        height: 120px;
    }
    .error-icon-wrap {
        width: 90px;
        height: 90px;
    }
    .error-icon-wrap i {
        font-size: 42px;
    }
    .code-num {
        width: 32px;
        height: 40px;
        font-size: 18px;
    }
    .error-links {
        grid-template-columns: 1fr;
    }
    .error-funfact {
        font-size: 11px;
        padding: 10px 16px;
    }
}
</style>