<?php
/**
 * ============================================================
 * HALAMAN ERROR 404 — ULTIMATE EDITION v7.0
 * Halaman "tidak ditemukan" dengan sinematik visual,
 * animasi floating elements, search bar, dan rotating fun facts.
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
        <span class="error-shape shape-5"><i class="ph ph-binoculars"></i></span>
        <span class="error-shape shape-6"><i class="ph ph-rocket-launch"></i></span>
    </div>

    <!-- Main error card -->
    <article class="error-card glass-card reveal">
        <!-- Animated illustration -->
        <div class="error-visual" aria-hidden="true">
            <div class="error-icon-wrap">
                <i class="ph ph-magnifying-glass"></i>
                <div class="error-pulse"></div>
                <div class="error-orbit"></div>
                <div class="error-orbit orbit-2"></div>
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

            <!-- Search bar -->
            <form class="error-search" action="<?= url('') ?>" method="get" role="search">
                <div class="error-search-wrap">
                    <i class="ph ph-magnifying-glass"></i>
                    <input 
                        type="search" 
                        name="q" 
                        placeholder="Cari halaman, artikel, atau event..."
                        autocomplete="off"
                        aria-label="Pencarian situs">
                    <button type="submit" class="error-search-btn" aria-label="Cari">
                        <i class="ph ph-arrow-right"></i>
                    </button>
                </div>
            </form>

            <!-- Quick actions -->
            <div class="error-actions">
                <a href="<?= url('') ?>" class="btn btn-primary btn-lg">
                    <i class="ph ph-house"></i>
                    <span class="btn-text">Kembali ke Beranda</span>
                    <i class="ph ph-arrow-right btn-arrow"></i>
                </a>
                <button type="button" onclick="history.back()" class="btn btn-ghost">
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
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                    <a href="<?= url('event') ?>" class="error-link">
                        <i class="ph ph-calendar-blank"></i>
                        <span>Event & Kegiatan</span>
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                    <a href="<?= url('artikel') ?>" class="error-link">
                        <i class="ph ph-newspaper"></i>
                        <span>Artikel Terbaru</span>
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                    <a href="<?= url('galeri') ?>" class="error-link">
                        <i class="ph ph-images"></i>
                        <span>Galeri Foto</span>
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                    <a href="<?= url('sensus') ?>" class="error-link">
                        <i class="ph ph-clipboard-text"></i>
                        <span>Sensus Anggota</span>
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                    <a href="<?= url('') ?>#kontak" class="error-link" data-scroll>
                        <i class="ph ph-envelope-simple"></i>
                        <span>Hubungi Kami</span>
                        <i class="ph ph-arrow-right error-link-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </article>

    <!-- Rotating Fun Facts -->
    <div class="error-funfact" id="errorFunfact" aria-label="Fakta menarik">
        <i class="ph ph-star-four"></i>
        <span id="funfactText">Tahukah Anda? Kode error 404 pertama kali muncul pada tahun 1991 di server CERN.</span>
        <button type="button" class="funfact-next" id="funfactNext" aria-label="Fakta berikutnya" title="Fakta berikutnya">
            <i class="ph ph-arrows-clockwise"></i>
        </button>
    </div>

    <!-- Keyboard shortcuts hint -->
    <div class="error-kbd" aria-label="Pintasan keyboard">
        <span class="error-kbd-item" id="kbdHome">
            <kbd>H</kbd> Kembali ke Beranda
        </span>
        <span class="error-kbd-item" id="kbdBack">
            <kbd>B</kbd> Halaman Sebelumnya
        </span>
    </div>
</section>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Error Hero Container ---- */
.error-hero {
    position: relative;
    min-height: calc(100vh - 80px);
    display: grid;
    place-items: center;
    padding: 100px 24px 60px;
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
    transition: all .3s;
}
.error-shape:hover {
    color: var(--acc);
    border-color: rgba(99,102,241,.3);
    background: rgba(99,102,241,.08);
}
.shape-1 { top: 12%; left: 8%; animation-delay: 0s; }
.shape-2 { top: 68%; left: 12%; animation-delay: -4s; }
.shape-3 { top: 18%; right: 10%; animation-delay: -8s; }
.shape-4 { bottom: 14%; right: 8%; animation-delay: -12s; }
.shape-5 { top: 40%; left: 4%; animation-delay: -2s; width: 48px; height: 48px; font-size: 22px; }
.shape-6 { bottom: 30%; right: 4%; animation-delay: -6s; width: 48px; height: 48px; font-size: 22px; }

@keyframes error-float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(12deg); }
}

/* ---- Error Card ---- */
.error-card {
    position: relative;
    z-index: 1;
    max-width: 680px;
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
    width: 180px;
    height: 180px;
    margin: 0 auto 36px;
}

.error-icon-wrap {
    position: relative;
    width: 130px;
    height: 130px;
    margin: 0 auto;
    border-radius: 38px;
    background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(34,211,238,.2));
    display: grid;
    place-items: center;
    z-index: 2;
    box-shadow: 0 20px 50px rgba(99,102,241,.25);
}
.error-icon-wrap i {
    font-size: 60px;
    color: var(--acc);
    animation: icon-bounce 2s ease-in-out infinite;
    filter: drop-shadow(0 4px 12px rgba(34,211,238,.4));
}

@keyframes icon-bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
}

.error-pulse {
    position: absolute;
    inset: -10px;
    border-radius: 48px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(16px);
    z-index: 1;
    animation: spin 4s linear infinite;
}

.error-orbit {
    position: absolute;
    inset: -22px;
    border-radius: 50%;
    border: 2px dashed rgba(99,102,241,.3);
    animation: spin 20s linear infinite reverse;
}
.error-orbit.orbit-2 {
    inset: -38px;
    border-color: rgba(34,211,238,.2);
    animation-duration: 30s;
    animation-direction: normal;
}

/* ---- Error Code Numbers ---- */
.error-code {
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 3;
    perspective: 1000px;
}
.code-num {
    width: 44px;
    height: 52px;
    display: grid;
    place-items: center;
    background: var(--bg-1);
    border: 1px solid var(--glass-brd);
    border-radius: 11px;
    font-size: 26px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 10px 24px rgba(0,0,0,.35);
    font-variant-numeric: tabular-nums;
}
.code-num.animated {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    animation: code-flip 3s ease-in-out infinite;
    transform-style: preserve-3d;
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

@keyframes badge-pop {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
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
    margin: 0 auto 28px;
}

/* ---- Search Bar ---- */
.error-search {
    max-width: 480px;
    margin: 0 auto 28px;
}
.error-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 14px;
    padding: 4px 4px 4px 18px;
    transition: all .25s;
    backdrop-filter: blur(14px);
}
.error-search-wrap:focus-within {
    border-color: var(--pri);
    background: rgba(99,102,241,.08);
    box-shadow: 0 0 0 4px rgba(99,102,241,.12);
}
.error-search-wrap > i {
    color: var(--txt-2);
    font-size: 18px;
    flex-shrink: 0;
}
.error-search-wrap input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    padding: 12px 14px;
    color: var(--txt-0);
    font-size: 13.5px;
    font-family: inherit;
    font-weight: 500;
}
.error-search-wrap input::placeholder {
    color: var(--txt-2);
}
.error-search-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all .25s;
    flex-shrink: 0;
}
.error-search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(99,102,241,.35);
}
.error-search-btn:active {
    transform: translateY(0);
}

/* ---- Actions ---- */
.error-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 36px;
}

.error-actions .btn-arrow {
    transition: transform .3s;
}
.error-actions .btn:hover .btn-arrow {
    transform: translateX(4px);
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
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    max-width: 580px;
    margin: 0 auto;
}
.error-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 11px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}
.error-link::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(99,102,241,.1), rgba(34,211,238,.05));
    opacity: 0;
    transition: opacity .3s;
}
.error-link:hover::before { opacity: 1; }
.error-link i:first-child {
    font-size: 18px;
    color: var(--acc);
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.error-link span {
    flex: 1;
    position: relative;
    z-index: 1;
}
.error-link-arrow {
    font-size: 14px;
    color: var(--txt-2);
    opacity: 0;
    transform: translateX(-8px);
    transition: all .3s;
    position: relative;
    z-index: 1;
}
.error-link:hover {
    border-color: rgba(99,102,241,.35);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(99,102,241,.2);
}
.error-link:hover .error-link-arrow {
    opacity: 1;
    transform: translateX(0);
    color: var(--acc);
}

/* ---- Fun Fact ---- */
.error-funfact {
    position: relative;
    z-index: 1;
    margin-top: 36px;
    padding: 12px 14px 12px 20px;
    border-radius: 99px;
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.2);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: var(--txt-1);
    animation: fade-up 0.6s 0.8s both;
    max-width: 680px;
    transition: all .3s;
}
.error-funfact:hover {
    background: rgba(245,158,11,.12);
    border-color: rgba(245,158,11,.3);
}
.error-funfact > i {
    color: var(--warn);
    font-size: 16px;
    flex-shrink: 0;
    animation: star-twinkle 2s ease-in-out infinite;
}
@keyframes star-twinkle {
    0%, 100% { opacity: 1; transform: scale(1) rotate(0deg); }
    50% { opacity: .6; transform: scale(1.1) rotate(15deg); }
}
#funfactText {
    flex: 1;
    transition: opacity .3s;
    line-height: 1.5;
}
#funfactText.fading { opacity: 0; }
.funfact-next {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 1px solid rgba(245,158,11,.25);
    background: rgba(245,158,11,.08);
    color: var(--warn);
    font-size: 13px;
    cursor: pointer;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    transition: all .25s;
}
.funfact-next:hover {
    background: rgba(245,158,11,.2);
    border-color: var(--warn);
    transform: rotate(180deg);
}

/* ---- Keyboard Hints ---- */
.error-kbd {
    margin-top: 20px;
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
    font-size: 11.5px;
    color: var(--txt-2);
}
.error-kbd-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    cursor: pointer;
    transition: all .2s;
}
.error-kbd-item:hover {
    background: rgba(99,102,241,.08);
    border-color: rgba(99,102,241,.3);
    color: var(--acc);
}
.error-kbd kbd {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 5px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10.5px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 2px 0 rgba(0,0,0,.25);
}

/* ---- Responsive ---- */
@media (max-width: 720px) {
    .error-card {
        padding: 48px 24px;
    }
    .error-visual {
        width: 140px;
        height: 140px;
    }
    .error-icon-wrap {
        width: 100px;
        height: 100px;
        border-radius: 30px;
    }
    .error-icon-wrap i {
        font-size: 46px;
    }
    .code-num {
        width: 34px;
        height: 42px;
        font-size: 20px;
    }
    .error-links {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .error-funfact {
        font-size: 11px;
        padding: 10px 14px 10px 16px;
        max-width: 100%;
    }
    .error-shape {
        width: 44px;
        height: 44px;
        font-size: 20px;
    }
    .shape-5, .shape-6 {
        display: none;
    }
}

@media (max-width: 520px) {
    .error-links {
        grid-template-columns: 1fr;
    }
    .error-actions {
        flex-direction: column;
        width: 100%;
    }
    .error-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
(function(){
    'use strict';

    // ---- Rotating Fun Facts ----
    const funfacts = [
        'Tahukah Anda? Kode error 404 pertama kali muncul pada tahun 1991 di server CERN.',
        'Asal-usul nama "404" berasal dari room number 404 di CERN tempat server web pertama berada.',
        'Di beberapa negara, error 404 diblokir oleh pemerintah untuk sensor internet.',
        'Banyak situs kreatif membuat halaman 404 unik sebagai branding mereka.',
        'Google pernah menampilkan game interaktif di halaman 404 mereka.',
        'Error 404 adalah status code HTTP yang berarti "Not Found" dalam protokol web.',
        'Istilah "404" telah masuk ke dalam kamus umum sebagai slang untuk "tidak ditemukan".'
    ];

    const funfactText = document.getElementById('funfactText');
    const funfactNext = document.getElementById('funfactNext');
    let currentFact = 0;
    let autoRotateTimer;

    function showNextFact() {
        if (!funfactText) return;
        funfactText.classList.add('fading');
        setTimeout(() => {
            currentFact = (currentFact + 1) % funfacts.length;
            funfactText.textContent = funfacts[currentFact];
            funfactText.classList.remove('fading');
        }, 300);
    }

    if (funfactNext) {
        funfactNext.addEventListener('click', () => {
            showNextFact();
            resetAutoRotate();
        });
    }

    function resetAutoRotate() {
        clearInterval(autoRotateTimer);
        autoRotateTimer = setInterval(showNextFact, 8000);
    }
    resetAutoRotate();

    // ---- Keyboard Shortcuts ----
    const kbdHome = document.getElementById('kbdHome');
    const kbdBack = document.getElementById('kbdBack');

    if (kbdHome) {
        kbdHome.addEventListener('click', () => {
            window.location.href = '<?= url('') ?>';
        });
    }
    if (kbdBack) {
        kbdBack.addEventListener('click', () => {
            history.back();
        });
    }

    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

        if (e.key.toLowerCase() === 'h' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            window.location.href = '<?= url('') ?>';
        }
        if (e.key.toLowerCase() === 'b' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            history.back();
        }
    });

    // ---- Auto-focus search on load ----
    const searchInput = document.querySelector('.error-search input');
    if (searchInput && window.innerWidth > 768) {
        setTimeout(() => searchInput.focus(), 800);
    }
})();
</script>