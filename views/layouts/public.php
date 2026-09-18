<!-- File: views/layouts/public.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Beranda') ?> • <?= e(APP_NAME) ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%22 stop-color=%22%236366f1%22/><stop offset=%221%22 stop-color=%22%2322d3ee%22/></linearGradient></defs><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22url(%23g)%22/><text x=%2250%22 y=%2268%22 font-family=%22Arial%22 font-size=%2256%22 font-weight=%22900%22 fill=%22white%22 text-anchor=%22middle%22>OU</text></svg>">
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

    <!-- NAVBAR PUBLIK -->
    <header class="pub-navbar" id="pubNavbar">
        <div class="pub-nav-inner">
            <a href="<?= url('') ?>" class="pub-brand">
                <span class="logo-mark">OU</span>
                <span><?= e(APP_NAME) ?></span>
            </a>
            <nav class="pub-nav-links" id="pubNavLinks">
                <a href="<?= url('') ?>" class="pub-link active">Beranda</a>
                <a href="<?= url('') ?>#tentang" class="pub-link" data-scroll>Tentang</a>
                <a href="<?= url('') ?>#event" class="pub-link" data-scroll>Event</a>
                <a href="<?= url('') ?>#keunggulan" class="pub-link" data-scroll>Keunggulan</a>
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
                    <span class="logo-mark">OU</span>
                    <span><?= e(APP_NAME) ?></span>
                </div>
                <p>Organisasi kekeluargaan yang mewadahi pelajar dan mahasiswa untuk bertumbuh, berkontribusi, serta memberikan dampak nyata bagi masyarakat.</p>
                <div class="footer-socials">
                    <a href="#" aria-label="Instagram"><i class="ph ph-instagram-logo"></i></a>
                    <a href="#" aria-label="YouTube"><i class="ph ph-youtube-logo"></i></a>
                    <a href="#" aria-label="Email"><i class="ph ph-envelope-simple"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="<?= url('') ?>">Beranda</a>
                <a href="<?= url('') ?>#tentang" data-scroll>Tentang</a>
                <a href="<?= url('') ?>#event" data-scroll>Event</a>
                <a href="<?= url('login') ?>">Masuk Anggota</a>
            </div>
            <div class="footer-col">
                <h4>Hubungi Kami</h4>
                <p><i class="ph ph-envelope-simple"></i> help@organisasi.id</p>
                <p><i class="ph ph-phone"></i> +62 857-0000-0000</p>
                <p><i class="ph ph-map-pin"></i> Indonesia</p>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© <?= date('Y') ?> <?= e(APP_NAME) ?> — Seluruh hak cipta dilindungi.</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS</span>
        </div>
    </footer>

    <script src="<?= asset('js/public.js') ?>"></script>
</body>
</html>