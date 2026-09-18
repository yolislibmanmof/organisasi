<!-- File: views/layouts/app.php (FINAL - TERINTEGRASI TAHAP 4.2) -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> • <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<?php
    $currentPath   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $isDashboard   = str_contains($currentPath, '/dashboard');
    $isMembers     = str_contains($currentPath, '/members');
    $isEvents      = str_contains($currentPath, '/events');
    $isProfile     = str_contains($currentPath, '/profile');
?>
<body data-base="<?= e(BASE_URL) ?>" class="app-shell">
    <!-- Overlay mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar glass-panel" id="sidebar">
        <div class="side-brand">
            <span class="logo-mark">OU</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
            <button class="side-collapse-btn" id="btnCollapse" title="Ciutkan menu" aria-label="Ciutkan menu">
                <i class="ph ph-caret-left"></i>
            </button>
        </div>

        <div class="side-section-label"><span>Menu Utama</span></div>
        <nav class="side-nav">
            <a href="<?= url('dashboard') ?>" class="nav-item <?= $isDashboard ? 'active' : '' ?>">
                <i class="ph ph-squares-four"></i>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="<?= url('members') ?>" class="nav-item <?= $isMembers ? 'active' : '' ?>">
                <i class="ph ph-users-three"></i>
                <span class="nav-label">Anggota</span>
                <span class="nav-badge" id="memberBadge" data-count="0">0</span>
            </a>
            <a href="<?= url('events') ?>" class="nav-item <?= $isEvents ? 'active' : '' ?>">
                <i class="ph ph-calendar-blank"></i>
                <span class="nav-label">Event</span>
                <span class="nav-badge" id="eventBadge" style="display:none">0</span>
            </a>
            <a href="<?= url('profile') ?>" class="nav-item <?= $isProfile ? 'active' : '' ?>">
                <i class="ph ph-user-circle-gear"></i>
                <span class="nav-label">Profil Saya</span>
            </a>
        </nav>

        <div class="side-section-label"><span>Sesi</span></div>
        <div class="side-user-card">
            <?= avatar_tag($user, 'avatar-sm') ?>
            <div class="side-user-info">
                <strong><?= e($user['name'] ?? 'Pengguna') ?></strong>
                <small><?= e(ucfirst($user['role'] ?? 'member')) ?></small>
            </div>
        </div>
        <div class="side-foot">
            <a href="<?= url('logout') ?>" class="nav-item danger">
                <i class="ph ph-sign-out"></i>
                <span class="nav-label">Keluar</span>
            </a>
        </div>
    </aside>

    <!-- AREA UTAMA -->
    <div class="app-main">
        <header class="topbar glass-panel">
            <div class="top-left">
                <button class="icon-btn topbar-toggle" id="btnMobileMenu" aria-label="Buka menu">
                    <i class="ph ph-list"></i>
                </button>
                <div class="breadcrumb">
                    <a href="<?= url('dashboard') ?>">Beranda</a>
                    <i class="ph ph-caret-right"></i>
                    <span><?= e($title ?? 'Dashboard') ?></span>
                </div>
            </div>
            <div class="top-right">
                <div class="top-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" placeholder="Pencarian cepat… (⌘K)" id="globalSearch">
                </div>
                <button class="icon-btn top-icon-btn" title="Notifikasi" aria-label="Notifikasi">
                    <i class="ph ph-bell"></i>
                    <span class="notif-dot"></span>
                </button>
                <div class="top-user">
                    <?= avatar_tag($user) ?>
                </div>
            </div>
        </header>

        <main class="app-content">
            <?= $content ?>
        </main>

        <footer class="app-footer">
            <span>© <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong></span>
            <span class="footer-sep">•</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS</span>
        </footer>
    </div>

    <div class="toast-zone" id="toastZone"></div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>