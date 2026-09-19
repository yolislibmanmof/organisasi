<!-- File: views/layouts/public.php (FINAL - TAHAP 5.4) -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Beranda') ?> • <?= e(setting('app_name', 'Organisasi')) ?></title>

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
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/landing.css') ?>">
</head>
<body class="public-body">
    <canvas id="publicParticles" class="public-particles"></canvas>
    <div class="public-mesh">
        <span class="mesh-orb mesh-orb-1"></span>
        <span class="mesh-orb mesh-orb-2"></span>
        <span class="mesh-orb mesh-orb-3"></span>
    </div>

    <?php
        $pubPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $appName = setting('app_name', 'Organisasi');
        $logo    = setting('logo');
    ?>

    <!-- NAVBAR PUBLIK -->
    <header class="pub-navbar" id="pubNavbar">
        <div class="pub-nav-inner">
            <a href="<?= url('') ?>" class="pub-brand">
                <?php if ($logo !== ''): ?>
                    <img src="<?= url('assets/uploads/brand/' . e($logo)) ?>" alt="<?= e($appName) ?>" class="pub-brand-logo">
                <?php else: ?>
                    <span class="logo-mark">OU</span>
                <?php endif; ?>
                <span><?= e($appName) ?></span>
            </a>
            <nav class="pub-nav-links" id="pubNavLinks">
                <a href="<?= url('') ?>" class="pub-link <?= (str_contains($pubPath, '/artikel') || str_contains($pubPath, '/sensus')) ? '' : 'active' ?>">Beranda</a>
                <a href="<?= url('') ?>#tentang" class="pub-link" data-scroll>Tentang</a>
                <a href="<?= url('') ?>#event" class="pub-link" data-scroll>Event</a>
                <a href="<?= url('artikel') ?>" class="pub-link <?= str_contains($pubPath, '/artikel') ? 'active' : '' ?>">Artikel</a>
                <a href="<?= url('') ?>#galeri" class="pub-link" data-scroll>Galeri</a>
                <a href="<?= url('sensus') ?>" class="pub-link <?= str_contains($pubPath, '/sensus') ? 'active' : '' ?>">Sensus</a>
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
                <button class="pub-burger" id="pubBurger" aria-label="Buka menu"><i class="ph ph-list"></i></button>
            </div>
        </div>
    </header>

    <?= $content ?>

    <!-- FOOTER PUBLIK -->
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
                ?>
                <div class="footer-socials">
                    <?php if ($ig !== ''): ?>
                        <a href="<?= e($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="ph ph-instagram-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($yt !== ''): ?>
                        <a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="ph ph-youtube-logo"></i></a>
                    <?php endif; ?>
                    <?php if ($em !== ''): ?>
                        <a href="mailto:<?= e($em) ?>" aria-label="Email"><i class="ph ph-envelope-simple"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="<?= url('') ?>">Beranda</a>
                <a href="<?= url('') ?>#tentang" data-scroll>Tentang</a>
                <a href="<?= url('') ?>#event" data-scroll>Event</a>
                <a href="<?= url('artikel') ?>">Artikel</a>
                <a href="<?= url('') ?>#galeri" data-scroll>Galeri</a>
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
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <strong><?= e($appName) ?></strong> — Seluruh hak cipta dilindungi.</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS</span>
        </div>
    </footer>

    <!-- Lightbox galeri -->
    <div class="lightbox" id="lightbox">
        <button class="lightbox-close" id="lightboxClose" aria-label="Tutup"><i class="ph ph-x"></i></button>
        <img src="" alt="" id="lightboxImg">
        <p id="lightboxCap"></p>
    </div>

    <script src="<?= asset('js/public.js') ?>"></script>
</body>
</html>