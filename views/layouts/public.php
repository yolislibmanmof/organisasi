<?php
/**
 * ============================================================
 * LAYOUT HALAMAN PUBLIK — ULTIMATE EDITION v7.0
 * Navbar profesional, footer kaya, SEO lengkap, clean & elegant
 * ============================================================
 */

$appName   = setting('app_name', 'Organisasi');
$pubPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$logo      = setting('logo');
$isGaleri  = str_contains($pubPath, '/galeri');
$isArtikel = str_contains($pubPath, '/artikel');
$isSensus  = str_contains($pubPath, '/sensus');
$isAbout   = str_contains($pubPath, '/tentang');
$isEvent   = str_contains($pubPath, '/event');
$isDetail  = str_contains($pubPath, '/artikel/') || str_contains($pubPath, '/event/');

$metaDesc = match (true) {
    $pubPath === '/' => 'Situs resmi ' . $appName . ' — rumah kekeluargaan pelajar dan mahasiswa untuk bertumbuh dan berdedikasi.',
    $isArtikel      => 'Artikel, berita, edukasi, podcast, dan hari besar dari ' . $appName . '.',
    $isGaleri       => 'Dokumentasi lengkap kegiatan ' . $appName . '.',
    $isAbout        => 'Mengenal lebih dekat ' . $appName . ': visi, misi, dan struktur kepengurusan.',
    $isEvent        => 'Arsip event & kegiatan ' . $appName . ' — yang sedang berlangsung dan mendatang.',
    $isSensus       => 'Formulir sensus anggota ' . $appName . ' — digitalisasi database.',
    default         => 'Situs resmi ' . $appName . ' — organisasi kekeluargaan pelajar & mahasiswa.',
};
$ogImage = $logo !== '' ? url('assets/uploads/brand/' . $logo) : url('assets/uploads/brand/default-og.png');
$canonical = rtrim(BASE_URL, '/') . $pubPath;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f1f">
    <title><?= e($title ?? 'Beranda') ?> • <?= e($appName) ?></title>

    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="author" content="<?= e($appName) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($appName) ?>">
    <meta property="og:title" content="<?= e(($title ?? 'Beranda') . ' • ' . $appName) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="id_ID">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e(($title ?? 'Beranda') . ' • ' . $appName) ?>">
    <meta name="twitter:description" content="<?= e($metaDesc) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <?php $customFav = setting('favicon'); ?>
    <?php if ($customFav !== ''): ?>
        <link rel="icon" href="<?= url('assets/uploads/brand/' . e($customFav)) ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%22 stop-color=%22%236366f1%22/><stop offset=%221%22 stop-color=%22%2322d3ee%22/></linearGradient></defs><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22url(%23g)%22/><text x=%2250%22 y=%2268%22 font-family=%22Arial%22 font-size=%2256%22 font-weight=%22900%22 fill=%22white%22 text-anchor=%22middle%22>OU</text></svg>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="<?= asset('css/landing.css') ?>?v=7.0">

    <style>
        /* ---- Announcement Bar ---- */
        .pub-announcement {
            position: relative; z-index: 60;
            padding: 10px 20px;
            background: linear-gradient(90deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
            border-bottom: 1px solid rgba(99,102,241,.2);
            text-align: center; font-size: 12.5px; font-weight: 600; color: var(--txt-0);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .pub-announcement i { color: var(--acc); font-size: 15px; }
        .pub-announcement a { color: var(--acc); text-decoration: underline; font-weight: 700; }
        .pub-announcement-close {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,.06); border: 1px solid var(--glass-brd);
            width: 24px; height: 24px; border-radius: 6px;
            color: var(--txt-1); cursor: pointer;
            display: grid; place-items: center; transition: .2s;
        }
        .pub-announcement-close:hover { background: rgba(239,68,68,.15); color: #fca5a5; border-color: var(--danger); }

        /* ---- LIGHTBOX FIX: force hidden when not shown ---- */
        .lightbox:not(.show) { display: none !important; }
        .lightbox:not(.show) .lightbox-close { display: none !important; }
        .lightbox.show { display: grid !important; }

        /* ---- Reading progress (article detail) ---- */
        .reading-progress {
            position: fixed; top: 0; left: 0; height: 3px; z-index: 100;
            background: linear-gradient(90deg, var(--pri), var(--acc));
            width: 0; transition: width .1s;
            box-shadow: 0 0 10px var(--acc);
        }

        /* ---- Social share floating ---- */
        .social-share-float {
            position: fixed; left: 24px; top: 50%;
            transform: translateY(-50%);
            display: flex; flex-direction: column; gap: 10px; z-index: 30;
        }
        .social-share-float a {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(15, 21, 48, .9); backdrop-filter: blur(14px);
            border: 1px solid var(--glass-brd);
            display: grid; place-items: center;
            color: var(--txt-1); font-size: 16px; text-decoration: none;
            transition: all .25s;
        }
        .social-share-float a:hover {
            color: #fff; border-color: var(--pri);
            background: rgba(99,102,241,.15); transform: translateX(4px);
        }

        /* ---- Scroll to top (single, no duplicate) ---- */
        .scroll-top-btn {
            position: fixed; bottom: 30px; right: 30px;
            width: 48px; height: 48px; border-radius: 50%;
            background: linear-gradient(135deg, var(--pri), var(--acc));
            border: none; color: #fff; cursor: pointer; font-size: 20px;
            box-shadow: 0 10px 30px rgba(99,102,241,.4);
            opacity: 0; visibility: hidden;
            transform: translateY(20px);
            transition: all .3s cubic-bezier(.22,1,.36,1);
            z-index: 50; display: grid; place-items: center;
        }
        .scroll-top-btn.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .scroll-top-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(34,211,238,.4);
        }

        /* ---- Cookie consent ---- */
        .cookie-consent {
            position: fixed; bottom: 20px; left: 20px;
            max-width: 380px; padding: 16px 20px;
            background: rgba(15, 21, 48, .95); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-brd); border-radius: 16px;
            box-shadow: 0 20px 50px rgba(2,6,23,.5);
            z-index: 40; animation: slide-up .4s cubic-bezier(.22,1,.36,1);
        }
        .cookie-consent.hide { display: none; }
        .cookie-consent h4 { font-size: 13px; font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
        .cookie-consent h4 i { color: var(--acc); font-size: 16px; }
        .cookie-consent p { font-size: 12px; color: var(--txt-1); line-height: 1.5; margin-bottom: 12px; }
        .cookie-consent a { color: var(--acc); text-decoration: underline; }
        .cookie-actions { display: flex; gap: 8px; }
        .cookie-btn {
            padding: 7px 14px; border-radius: 8px;
            font-size: 12px; font-weight: 700;
            border: 1px solid var(--glass-brd);
            background: rgba(255,255,255,.05);
            color: var(--txt-0); cursor: pointer; transition: .2s;
        }
        .cookie-btn:hover { background: rgba(255,255,255,.1); }
        .cookie-btn.primary {
            background: linear-gradient(135deg, var(--pri), var(--acc));
            border-color: transparent; color: #fff;
        }
        .cookie-btn.primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.3); }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ---- Skip link ---- */
        .skip-link {
            position: absolute; top: -100px; left: 10px;
            padding: 10px 18px; border-radius: 8px;
            background: var(--pri); color: #fff;
            font-weight: 700; font-size: 13px;
            text-decoration: none; z-index: 9999; transition: top .2s;
        }
        .skip-link:focus { top: 10px; }

        /* ---- Professional Navbar Enhancements ---- */
        .pub-navbar {
            transition: all .35s cubic-bezier(.22,1,.36,1);
        }
        .pub-brand { transition: all .3s; }
        .pub-brand:hover { transform: translateY(-1px); }
        .pub-brand-logo {
            transition: transform .3s;
            filter: drop-shadow(0 4px 12px rgba(99,102,241,.3));
        }
        .pub-brand:hover .pub-brand-logo { transform: scale(1.05); }

        .pub-link { position: relative; }
        .pub-link::after {
            content: ''; position: absolute;
            bottom: 4px; left: 14px; right: 14px; height: 2px;
            background: linear-gradient(90deg, var(--pri), var(--acc));
            border-radius: 2px;
            transform: scaleX(0); transform-origin: left;
            transition: transform .35s var(--ease-smooth);
        }
        .pub-link:hover::after, .pub-link.active::after { transform: scaleX(1); }

        /* ---- Footer Enhancements ---- */
        .footer-brand { position: relative; }
        .footer-brand::after {
            content: ''; position: absolute;
            top: 0; right: 0; width: 1px; height: 100%;
            background: linear-gradient(180deg, transparent, var(--glass-brd), transparent);
        }
        .footer-col h4::before {
            content: ''; display: block;
            width: 28px; height: 2px;
            background: linear-gradient(90deg, var(--pri), var(--acc));
            margin-bottom: 10px; border-radius: 2px;
        }
        .footer-socials a { transition: all .3s var(--ease-smooth); }
        .footer-socials a:hover {
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 8px 20px rgba(99,102,241,.3);
        }
        .footer-col a { position: relative; padding-left: 0; transition: all .25s; }
        .footer-col a::before {
            content: '→'; position: absolute;
            left: -16px; opacity: 0;
            color: var(--acc); font-size: 12px;
            transition: all .25s;
        }
        .footer-col a:hover::before { left: 0; opacity: 1; }
        .footer-col a:hover { padding-left: 16px; }

        .footer-bottom {
            background: linear-gradient(180deg, transparent, rgba(0,0,0,.2));
        }

        /* ---- Newsletter section ---- */
        .newsletter-box {
            background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(34,211,238,.08));
            border: 1px solid rgba(99,102,241,.25);
            border-radius: 16px; padding: 20px; margin-top: 20px;
        }
        .newsletter-box h5 {
            font-size: 13px; font-weight: 800; margin-bottom: 6px;
            display: flex; align-items: center; gap: 6px;
        }
        .newsletter-box h5 i { color: var(--acc); }
        .newsletter-box p { font-size: 11.5px; color: var(--txt-1); line-height: 1.5; margin-bottom: 10px; }
        .newsletter-form { display: flex; gap: 6px; }
        .newsletter-form input {
            flex: 1; padding: 9px 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--glass-brd); border-radius: 8px;
            color: var(--txt-0); font-size: 12px; outline: none; transition: .2s;
        }
        .newsletter-form input:focus { border-color: var(--pri); box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .newsletter-form button {
            padding: 9px 14px;
            background: linear-gradient(135deg, var(--pri), var(--acc));
            border: none; border-radius: 8px;
            color: #fff; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: .2s;
        }
        .newsletter-form button:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99,102,241,.3); }

        /* ---- Mobile responsive (clean wrap nav) ---- */
        @media (max-width: 980px) {
            .pub-nav-inner { flex-wrap: wrap; gap: 12px; }
            .pub-nav-links {
                flex-direction: row; flex-wrap: wrap;
                gap: 2px; margin-left: auto;
            }
            .pub-link {
                padding: 8px 10px; font-size: 12.5px;
            }
            .social-share-float { display: none; }
            .cookie-consent { left: 10px; right: 10px; max-width: none; bottom: 10px; }
            .scroll-top-btn { bottom: 20px; right: 20px; width: 44px; height: 44px; }
            .footer-brand::after { display: none; }
        }
    </style>
</head>
<body class="public-body">
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <?php if ($isDetail): ?>
    <div class="reading-progress" id="readingProgress" aria-hidden="true"></div>
    <?php endif; ?>

    <?php $announcement = setting('announcement_text'); ?>
    <?php if ($announcement && !isset($_COOKIE['announcement_dismissed'])): ?>
    <div class="pub-announcement" id="pubAnnouncement">
        <i class="ph ph-megaphone"></i>
        <span><?= e($announcement) ?></span>
        <?php $announcementLink = setting('announcement_link'); ?>
        <?php if ($announcementLink): ?>
            <a href="<?= e($announcementLink) ?>">Pelajari →</a>
        <?php endif; ?>
        <button class="pub-announcement-close" id="closeAnnouncement" aria-label="Tutup pengumuman">
            <i class="ph ph-x"></i>
        </button>
    </div>
    <?php endif; ?>

    <canvas id="publicParticles" class="public-particles" aria-hidden="true"></canvas>

    <div class="public-mesh" aria-hidden="true">
        <span class="mesh-orb mesh-orb-1"></span>
        <span class="mesh-orb mesh-orb-2"></span>
        <span class="mesh-orb mesh-orb-3"></span>
    </div>

    <!-- ========== NAVBAR PUBLIK (v7.0 - CLEAN & PROFESSIONAL) ========== -->
    <header class="pub-navbar" id="pubNavbar">
        <div class="pub-nav-inner">
            <a href="<?= url('') ?>" class="pub-brand" aria-label="<?= e($appName) ?> - Beranda">
                <?php if ($logo !== ''): ?>
                    <img src="<?= url('assets/uploads/brand/' . e($logo)) ?>" alt="<?= e($appName) ?>" class="pub-brand-logo">
                <?php else: ?>
                    <span class="logo-mark">OU</span>
                <?php endif; ?>
                <span><?= e($appName) ?></span>
            </a>
            <nav class="pub-nav-links" id="pubNavLinks" aria-label="Navigasi utama">
                <a href="<?= url('') ?>" class="pub-link <?= ($isGaleri || $isArtikel || $isSensus || $isAbout || $isEvent) ? '' : 'active' ?>">Beranda</a>
                <a href="<?= url('tentang') ?>" class="pub-link <?= $isAbout ? 'active' : '' ?>">Tentang</a>
                <a href="<?= url('event') ?>" class="pub-link <?= $isEvent ? 'active' : '' ?>">Event</a>
                <a href="<?= url('artikel') ?>" class="pub-link <?= $isArtikel ? 'active' : '' ?>">Artikel</a>
                <a href="<?= url('galeri') ?>" class="pub-link <?= $isGaleri ? 'active' : '' ?>">Galeri</a>
                <a href="<?= url('sensus') ?>" class="pub-link <?= $isSensus ? 'active' : '' ?>">Sensus</a>
                <a href="<?= url('') ?>#kontak" class="pub-link" data-scroll>Kontak</a>
            </nav>
            <div class="pub-nav-cta">
                <?php if (!empty($loggedIn)): ?>
                    <a href="<?= url('dashboard') ?>" class="btn btn-primary btn-sm">
                        <i class="ph ph-squares-four"></i><span class="btn-text">Dashboard</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-primary btn-sm">
                        <i class="ph ph-sign-in"></i><span class="btn-text">Masuk</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main id="main-content">
        <?= $content ?>
    </main>

    <!-- ========== FOOTER PUBLIK (v7.0 - RICH & PROFESSIONAL) ========== -->
    <footer class="pub-footer" id="kontak">
        <div class="footer-grid">
            <div class="footer-col footer-brand">
                <div class="pub-brand">
                    <?php if ($logo !== ''): ?>
                        <img src="<?= url('assets/uploads/brand/' . e($logo)) ?>" alt="<?= e($appName) ?>" class="pub-brand-logo">
                    <?php else: ?>
                        <span class="logo-mark">OU</span>
                    <?php endif; ?>
                    <span><?= e($appName) ?></span>
                </div>
                <p>Organisasi kekeluargaan yang mewadahi pelajar dan mahasiswa untuk bertumbuh, berkontribusi, serta memberikan dampak nyata bagi masyarakat.</p>
                <?php
                    $ig = setting('social_instagram');
                    $yt = setting('social_youtube');
                    $em = setting('social_email');
                    $fb = setting('social_facebook') ?? '';
                    $tw = setting('social_twitter') ?? '';
                ?>
                <div class="footer-socials">
                    <?php if ($ig !== ''): ?>
                        <a href="<?= e($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="ph ph-instagram-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($yt !== ''): ?>
                        <a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="ph ph-youtube-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($fb !== ''): ?>
                        <a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="ph ph-facebook-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($tw !== ''): ?>
                        <a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="ph ph-x-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($em !== ''): ?>
                        <a href="mailto:<?= e($em) ?>" aria-label="Email"><i class="ph ph-envelope-simple"></i></a>
                    <?php endif; ?>
                </div>

                <div class="newsletter-box">
                    <h5><i class="ph ph-envelope-simple"></i> Newsletter</h5>
                    <p>Dapatkan update event dan artikel terbaru langsung ke email Anda.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <input type="email" placeholder="email@anda.com" required>
                        <button type="submit"><i class="ph ph-paper-plane-tilt"></i></button>
                    </form>
                </div>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="<?= url('') ?>">Beranda</a>
                <a href="<?= url('tentang') ?>">Tentang</a>
                <a href="<?= url('event') ?>">Event</a>
                <a href="<?= url('artikel') ?>">Artikel</a>
                <a href="<?= url('galeri') ?>">Galeri</a>
                <a href="<?= url('sensus') ?>">Sensus</a>
                <a href="<?= url('login') ?>">Masuk Anggota</a>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <?php if (setting('social_email') !== ''): ?>
                    <p><i class="ph ph-envelope-simple"></i> <?= e(setting('social_email')) ?></p>
                <?php endif; ?>
                <?php if (setting('social_phone') !== ''): ?>
                    <p><i class="ph ph-phone"></i> <?= e(setting('social_phone')) ?></p>
                <?php endif; ?>
                <?php if (setting('social_address') !== ''): ?>
                    <p><i class="ph ph-map-pin"></i> <?= e(setting('social_address')) ?></p>
                <?php endif; ?>
                <?php if (setting('social_hours') !== ''): ?>
                    <p><i class="ph ph-clock"></i> <?= e(setting('social_hours')) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <strong><?= e($appName) ?></strong> — Seluruh hak cipta dilindungi.</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS • v7.0 ULTIMATE</span>
        </div>
    </footer>

    <?php if ($isDetail): ?>
    <div class="social-share-float" aria-label="Bagikan artikel">
        <a href="#" id="shareTwitter" target="_blank" rel="noopener" aria-label="Bagikan ke Twitter">
            <i class="ph ph-x-logo"></i>
        </a>
        <a href="#" id="shareFacebook" target="_blank" rel="noopener" aria-label="Bagikan ke Facebook">
            <i class="ph ph-facebook-logo"></i>
        </a>
        <a href="#" id="shareWhatsApp" target="_blank" rel="noopener" aria-label="Bagikan ke WhatsApp">
            <i class="ph ph-whatsapp-logo"></i>
        </a>
        <a href="#" id="copyLink" aria-label="Salin tautan">
            <i class="ph ph-link"></i>
        </a>
    </div>
    <?php endif; ?>

    <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Gulir ke atas">
        <i class="ph ph-arrow-up"></i>
    </button>

    <div class="lightbox" id="lightbox" role="dialog" aria-label="Lightbox gambar">
        <button class="lightbox-close" id="lightboxClose" aria-label="Tutup"><i class="ph ph-x"></i></button>
        <img src="" alt="" id="lightboxImg">
        <p id="lightboxCap"></p>
    </div>

    <?php if (!isset($_COOKIE['cookie_consent'])): ?>
    <div class="cookie-consent" id="cookieConsent">
        <h4><i class="ph ph-cookie"></i> Privasi Anda Penting</h4>
        <p>Kami menggunakan cookie untuk meningkatkan pengalaman Anda. Dengan melanjutkan, Anda menyetujui <a href="#">kebijakan privasi</a> kami.</p>
        <div class="cookie-actions">
            <button class="cookie-btn primary" id="acceptCookie">Terima</button>
            <button class="cookie-btn" id="declineCookie">Tolak</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="toast-zone" id="toastZone" aria-live="polite"></div>

    <script src="<?= asset('js/public.js') ?>?v=7.0"></script>
    <script>
    (() => {
        'use strict';

        const readingBar = document.getElementById('readingProgress');
        if (readingBar) {
            window.addEventListener('scroll', () => {
                const h = document.documentElement;
                const scrolled = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
                readingBar.style.width = scrolled + '%';
            }, { passive: true });
        }

        const scrollBtn = document.getElementById('scrollTopBtn');
        if (scrollBtn) {
            window.addEventListener('scroll', () => {
                scrollBtn.classList.toggle('show', window.scrollY > 400);
            }, { passive: true });
            scrollBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        const closeAnnouncement = document.getElementById('closeAnnouncement');
        if (closeAnnouncement) {
            closeAnnouncement.addEventListener('click', () => {
                const banner = document.getElementById('pubAnnouncement');
                if (banner) {
                    banner.style.transition = 'opacity .3s, transform .3s';
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateY(-10px)';
                    setTimeout(() => banner.remove(), 300);
                    document.cookie = 'announcement_dismissed=1; max-age=86400; path=/';
                }
            });
        }

        const acceptCookie = document.getElementById('acceptCookie');
        const declineCookie = document.getElementById('declineCookie');
        const cookieBox = document.getElementById('cookieConsent');

        if (acceptCookie) {
            acceptCookie.addEventListener('click', () => {
                document.cookie = 'cookie_consent=accepted; max-age=31536000; path=/';
                cookieBox.style.opacity = '0';
                cookieBox.style.transform = 'translateY(20px)';
                setTimeout(() => cookieBox.remove(), 300);
                if (window.toast) window.toast('Preferensi cookie disimpan.', 'success');
            });
        }
        if (declineCookie) {
            declineCookie.addEventListener('click', () => {
                document.cookie = 'cookie_consent=declined; max-age=86400; path=/';
                cookieBox.style.opacity = '0';
                cookieBox.style.transform = 'translateY(20px)';
                setTimeout(() => cookieBox.remove(), 300);
            });
        }

        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (window.toast) {
                    window.toast('Terima kasih! Anda akan menerima update dari kami.', 'success');
                }
                newsletterForm.reset();
            });
        }

        const currentUrl = encodeURIComponent(window.location.href);
        const currentTitle = encodeURIComponent(document.title);

        const shareTwitter = document.getElementById('shareTwitter');
        if (shareTwitter) shareTwitter.href = `https://twitter.com/intent/tweet?url=${currentUrl}&text=${currentTitle}`;

        const shareFacebook = document.getElementById('shareFacebook');
        if (shareFacebook) shareFacebook.href = `https://www.facebook.com/sharer/sharer.php?u=${currentUrl}`;

        const shareWhatsApp = document.getElementById('shareWhatsApp');
        if (shareWhatsApp) shareWhatsApp.href = `https://wa.me/?text=${currentTitle}%20${currentUrl}`;

        const copyLink = document.getElementById('copyLink');
        if (copyLink) {
            copyLink.addEventListener('click', (e) => {
                e.preventDefault();
                navigator.clipboard.writeText(window.location.href).then(() => {
                    if (window.toast) window.toast('Tautan berhasil disalin!', 'success');
                    copyLink.querySelector('i').className = 'ph ph-check-circle';
                    setTimeout(() => copyLink.querySelector('i').className = 'ph ph-link', 2000);
                });
            });
        }
    })();
    </script>
</body>
</html>