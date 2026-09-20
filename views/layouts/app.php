<?php
/**
 * ============================================================
 * LAYOUT DASHBOARD ADMIN — ULTIMATE EDITION v7.0
 * Fondasi semua halaman admin dengan sidebar, topbar,
 * command palette, dan efek visual premium.
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0f1f">
    <meta name="color-scheme" content="dark">
    <title><?= e($title ?? 'Dashboard') ?> • <?= e(APP_NAME) ?></title>

    <!-- Favicon dinamis -->
    <?php 
// PATCH v7.0: Fallback untuk helper setting() yang mungkin belum ada
$customFav = function_exists('setting') ? setting('favicon') : '';
$brandLogoText = defined('APP_NAME') ? substr(APP_NAME, 0, 2) : 'OU';
?>
    <?php if ($customFav !== ''): ?>
        <link rel="icon" href="<?= url('assets/uploads/brand/' . e($customFav)) ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%22 stop-color=%22%236366f1%22/><stop offset=%221%22 stop-color=%22%2322d3ee%22/></linearGradient></defs><rect width=%22100%22 height=%22100%22 rx=%2222%22 fill=%22url(%23g)%22/><text x=%2250%22 y=%2268%22 font-family=%22Arial%22 font-size=%2256%22 font-weight=%22900%22 fill=%22white%22 text-anchor=%22middle%22>OU</text></svg>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=7.0">

    <!-- ========== STYLING LAYOUT-SPECIFIC (v7.0) ========== -->
    <style>
        /* ---- Noise overlay untuk depth visual ---- */
        .noise-overlay {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            opacity: .015; mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' /%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* ---- Sidebar branding ---- */
        .side-brand { display: flex; align-items: center; gap: 8px; padding: 4px 2px 16px 10px; }
        .brand-text { flex: 1; min-width: 0; }
        .side-brand .logo-mark { width: 36px; height: 36px; font-size: 13px; border-radius: 11px; }
        .side-collapse-btn {
            position: static; transform: none; flex-shrink: 0;
            width: 24px; height: 24px; border-radius: 50%;
            background: rgba(255,255,255,.06);
            border: 1px solid var(--glass-brd-2);
            color: var(--txt-1); cursor: pointer;
            display: grid; place-items: center;
            transition: .3s cubic-bezier(.22,1,.36,1);
        }
        .side-collapse-btn:hover {
            background: linear-gradient(135deg, var(--pri), var(--acc));
            border-color: transparent; color: #fff;
            box-shadow: 0 6px 18px rgba(99,102,241,.5);
            transform: scale(1.12);
        }
        .side-collapse-btn i { font-size: 11px; transition: transform .3s; }
        .sidebar.collapsed .side-collapse-btn i { transform: rotate(180deg); }
        .sidebar.collapsed .side-brand { flex-direction: column; align-items: center; gap: 10px; padding: 6px 0 14px; }

        /* ---- User card indicator ---- */
        .side-user-card { position: relative; }
        .dx-sidebar-pulse {
            position: absolute; top: -4px; right: -4px;
            width: 11px; height: 11px; border-radius: 50%;
            background: #10b981; border: 2px solid var(--bg-1);
            box-shadow: 0 0 0 0 rgba(16,185,129,.55);
            animation: dx-pulse 2s infinite;
        }
        @keyframes dx-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.55); }
            70%  { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }

        /* ---- Topbar extras ---- */
        .dx-topbar-extra {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 10px;
            background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
            font-size: 11.5px; font-weight: 700; color: #6ee7b7;
            transition: all .2s;
        }
        .dx-topbar-extra:hover { background: rgba(16,185,129,.15); transform: translateY(-1px); }
        .dx-topbar-extra i { font-size: 14px; }
        .dx-time-display {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 14px; border-radius: 10px;
            background: rgba(255,255,255,.04); border: 1px solid var(--glass-brd);
            font-size: 12px; font-weight: 700; color: var(--txt-1);
            font-variant-numeric: tabular-nums;
        }
        .dx-time-display i { color: var(--acc); font-size: 15px; }

        .dx-footer-version {
            padding: 3px 10px; border-radius: 99px;
            background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
            border: 1px solid rgba(99,102,241,.3);
            color: #a5b4fc; font-size: 10.5px; font-weight: 800; letter-spacing: .6px;
        }

        /* ---- Breadcrumb Enhanced ---- */
        .breadcrumb {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; font-weight: 600;
        }
        .breadcrumb a {
            color: var(--txt-1);
            transition: color .2s;
        }
        .breadcrumb a:hover { color: var(--acc); }
        .breadcrumb i { color: var(--txt-2); font-size: 11px; }
        .breadcrumb span { color: var(--txt-0); }
        .breadcrumb .breadcrumb-sub {
            font-size: 11px;
            color: var(--txt-2);
            font-weight: 500;
            margin-left: 8px;
            padding: 2px 8px;
            background: rgba(255,255,255,.04);
            border-radius: 6px;
        }

        /* ---- User Dropdown Menu (Enhanced) ---- */
        .dx-user-wrap { position: relative; }
        .dx-user-dropdown {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 280px; padding: 8px;
            background: rgba(15, 21, 48, .95);
            backdrop-filter: blur(24px);
            border: 1px solid var(--glass-brd);
            border-radius: 16px;
            box-shadow: 0 30px 80px rgba(2,6,23,.7);
            opacity: 0; visibility: hidden;
            transform: translateY(-8px) scale(.96);
            transition: all .25s cubic-bezier(.22,1,.36,1);
            z-index: 50;
        }
        .dx-user-dropdown.show {
            opacity: 1; visibility: visible;
            transform: translateY(0) scale(1);
            animation: dropdown-in .3s cubic-bezier(.22,1,.36,1);
        }
        @keyframes dropdown-in {
            from { opacity: 0; transform: translateY(-12px) scale(.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .dx-user-head {
            display: flex; gap: 12px; align-items: center;
            padding: 12px 14px; border-radius: 10px;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--glass-brd);
            margin-bottom: 8px;
        }
        .dx-user-head strong { display: block; font-size: 13px; font-weight: 700; }
        .dx-user-head small { color: var(--txt-1); font-size: 11px; }
        .dx-user-menu { list-style: none; display: flex; flex-direction: column; gap: 2px; }
        .dx-user-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 9px;
            font-size: 12.5px; font-weight: 600; color: var(--txt-0);
            transition: all .2s; text-decoration: none;
        }
        .dx-user-menu a i { font-size: 16px; color: var(--acc); }
        .dx-user-menu a:hover { background: rgba(99,102,241,.12); transform: translateX(2px); }
        .dx-user-menu a.danger { color: #fca5a5; }
        .dx-user-menu a.danger i { color: var(--danger); }
        .dx-user-menu a.danger:hover { background: rgba(239,68,68,.12); }
        .dx-user-menu a kbd {
            margin-left: auto;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(255,255,255,.08);
            font-size: 10px;
            font-weight: 700;
            font-family: inherit;
            color: var(--txt-2);
            border: 1px solid rgba(255,255,255,.1);
        }

        /* ---- Notifikasi Dropdown ---- */
        .dx-notif-wrap { position: relative; }
        .dx-notif-dropdown {
            position: absolute; top: calc(100% + 10px); right: 0;
            width: 340px; max-height: 460px; overflow-y: auto;
            background: rgba(15, 21, 48, .95);
            backdrop-filter: blur(24px);
            border: 1px solid var(--glass-brd);
            border-radius: 16px;
            box-shadow: 0 30px 80px rgba(2,6,23,.7);
            opacity: 0; visibility: hidden;
            transform: translateY(-8px);
            transition: .25s cubic-bezier(.22,1,.36,1);
            z-index: 50;
        }
        .dx-notif-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .dx-notif-head {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid var(--glass-brd);
            font-size: 12.5px; font-weight: 800;
        }
        .dx-notif-head small { color: var(--txt-1); font-weight: 600; font-size: 11px; }
        .dx-notif-list { list-style: none; padding: 6px; }
        .dx-notif-item {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 10px; border-radius: 10px;
            cursor: pointer; transition: .2s;
            text-decoration: none; color: inherit;
        }
        .dx-notif-item:hover { background: rgba(255,255,255,.05); }
        .dx-notif-icon {
            width: 34px; height: 34px; border-radius: 10px;
            display: grid; place-items: center;
            color: #fff; font-size: 15px; flex-shrink: 0;
        }
        .dx-notif-body { flex: 1; min-width: 0; line-height: 1.3; }
        .dx-notif-body strong { display: block; font-size: 12.5px; font-weight: 700; }
        .dx-notif-body span { color: var(--txt-1); font-size: 11px; }
        .dx-notif-time { color: var(--txt-2); font-size: 10px; font-weight: 700; padding-top: 4px; }
        .dx-notif-empty { padding: 30px 20px; text-align: center; color: var(--txt-2); font-size: 12.5px; }
        .dx-notif-empty i { font-size: 28px; display: block; margin-bottom: 8px; }

        /* ---- Floating Action Button (FAB) ENHANCED ---- */
        .fab-zone {
            position: fixed; bottom: 28px; right: 28px; z-index: 40;
            display: flex; flex-direction: column-reverse; align-items: center; gap: 10px;
        }
        .fab-main {
            width: 54px; height: 54px; border-radius: 50%;
            background: linear-gradient(135deg, var(--pri), var(--acc));
            border: none; color: #fff; cursor: pointer;
            display: grid; place-items: center;
            font-size: 22px;
            box-shadow: 0 12px 30px rgba(99,102,241,.45);
            transition: all .3s var(--ease-smooth);
        }
        .fab-main i { transition: transform .3s cubic-bezier(.34,1.56,.64,1); }
        .fab-main:hover { transform: scale(1.08); box-shadow: 0 16px 40px rgba(34,211,238,.4); }
        .fab-main:hover i { transform: rotate(90deg); }
        .fab-zone.open .fab-main i { transform: rotate(45deg); }
        .fab-menu {
            display: flex; flex-direction: column; gap: 8px;
            opacity: 0; visibility: hidden;
            transform: translateY(10px) scale(.9);
            transition: all .25s var(--ease-elastic);
        }
        .fab-zone.open .fab-menu { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .fab-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 14px; border-radius: 99px;
            background: rgba(15, 21, 48, .95);
            backdrop-filter: blur(14px);
            border: 1px solid var(--glass-brd);
            color: var(--txt-0); font-size: 12px; font-weight: 700;
            text-decoration: none; white-space: nowrap;
            box-shadow: 0 10px 25px rgba(2,6,23,.4);
            transition: all .2s;
            opacity: 0;
            transform: translateY(10px);
        }
        .fab-zone.open .fab-item {
            opacity: 1;
            transform: translateY(0);
        }
        .fab-zone.open .fab-item:nth-child(1) { transition-delay: .05s; }
        .fab-zone.open .fab-item:nth-child(2) { transition-delay: .10s; }
        .fab-zone.open .fab-item:nth-child(3) { transition-delay: .15s; }
        .fab-zone.open .fab-item:nth-child(4) { transition-delay: .20s; }
        .fab-zone.open .fab-item:nth-child(5) { transition-delay: .25s; }
        .fab-item i { color: var(--acc); font-size: 16px; }
        .fab-item:hover { transform: translateX(-4px); border-color: var(--pri); background: rgba(99,102,241,.15); }

        /* ---- Toast Zone ---- */
        .toast-zone {
            position: fixed; top: 22px; right: 22px; z-index: 200;
            display: flex; flex-direction: column; gap: 10px;
            max-width: calc(100vw - 44px);
            max-height: calc(100vh - 44px);
            overflow-y: auto;
        }

        /* ---- Print Styles ---- */
        @media print {
            .sidebar, .topbar, .fab-zone, .noise-overlay,
            .loading-bar, .sidebar-overlay, .toast-zone,
            .side-collapse-btn, .dx-notif-wrap, .dx-user-wrap,
            .dx-topbar-extra, .dx-time-display, .top-search { display: none !important; }
            body, .app-shell { background: #fff !important; color: #000 !important; }
            .app-main { margin-left: 0 !important; padding: 0 !important; }
            .app-content { padding: 20px !important; }
            .glass-card, .glass-panel {
                background: #fff !important;
                backdrop-filter: none !important;
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
            .nav-item.active { background: #f3f4f6 !important; }
            .app-footer { border-top: 1px solid #ddd !important; color: #666 !important; }
        }

        /* ---- Responsive ---- */
        @media (max-width: 720px) {
            .dx-topbar-extra, .dx-time-display { display: none; }
            .dx-notif-dropdown { width: calc(100vw - 32px); right: -60px; }
            .fab-zone { bottom: 20px; right: 20px; }
            .breadcrumb .breadcrumb-sub { display: none; }
        }
    </style>
</head>
<?php
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $isDashboard = str_contains($currentPath, '/dashboard');
    $isMembers   = str_contains($currentPath, '/members');
    $isEvents    = str_contains($currentPath, '/events');
    $isProfile   = str_contains($currentPath, '/profile');
    $isArticles  = str_contains($currentPath, '/articles');
    $isContent   = str_contains($currentPath, '/content');
    $isCensus    = str_contains($currentPath, '/census');
    $isSettings  = str_contains($currentPath, '/settings');
    $isAdmin     = ($user['role'] ?? '') === 'admin';

    // Breadcrumb sub-label berdasarkan halaman
    $breadcrumbSub = match (true) {
        $isDashboard => 'Ringkasan',
        $isMembers   => 'Manajemen',
        $isEvents    => 'Agenda',
        $isProfile   => 'Akun',
        $isArticles  => 'Publikasi',
        $isContent   => 'Kustomisasi',
        $isCensus    => 'Data',
        $isSettings  => 'Sistem',
        default      => '',
    };
?>
<body data-base="<?= e(BASE_URL) ?>" class="app-shell">
    <!-- Noise overlay untuk depth visual -->
    <div class="noise-overlay" aria-hidden="true"></div>
    
    <!-- Loading bar -->
    <div class="loading-bar" id="dxLoadingBar" aria-hidden="true"></div>
    
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- ========== SIDEBAR ========== -->
    <aside class="sidebar glass-panel" id="sidebar" aria-label="Navigasi utama">
        <div class="side-brand">
            <span class="logo-mark"><?= e($brandLogoText) ?></span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
            <button class="side-collapse-btn" id="btnCollapse" title="Ciutkan menu" aria-label="Ciutkan menu">
                <i class="ph ph-caret-left"></i>
            </button>
        </div>

        <div class="side-section-label"><span>Menu Utama</span></div>
        <nav class="side-nav">
            <a href="<?= url('dashboard') ?>" class="nav-item <?= $isDashboard ? 'active' : '' ?>">
                <i class="ph ph-squares-four"></i><span class="nav-label">Dashboard</span>
            </a>
            <a href="<?= url('members') ?>" class="nav-item <?= $isMembers ? 'active' : '' ?>">
                <i class="ph ph-users-three"></i><span class="nav-label">Anggota</span>
                <span class="nav-badge" id="memberBadge" data-count="0">0</span>
            </a>
            <a href="<?= url('events') ?>" class="nav-item <?= $isEvents ? 'active' : '' ?>">
                <i class="ph ph-calendar-blank"></i><span class="nav-label">Event</span>
            </a>
            <a href="<?= url('profile') ?>" class="nav-item <?= $isProfile ? 'active' : '' ?>">
                <i class="ph ph-user-circle-gear"></i><span class="nav-label">Profil Saya</span>
            </a>
            <?php if ($isAdmin): ?>
            <div class="side-section-label"><span>Administrasi</span></div>
            <a href="<?= url('articles') ?>" class="nav-item <?= $isArticles ? 'active' : '' ?>">
                <i class="ph ph-newspaper"></i><span class="nav-label">Artikel</span>
            </a>
            <a href="<?= url('content') ?>" class="nav-item <?= $isContent ? 'active' : '' ?>">
                <i class="ph ph-paint-brush"></i><span class="nav-label">Konten Situs</span>
            </a>
            <a href="<?= url('census') ?>" class="nav-item <?= $isCensus ? 'active' : '' ?>">
                <i class="ph ph-clipboard-text"></i><span class="nav-label">Sensus Anggota</span>
                <span class="nav-badge" id="censusBadge" style="display:none">0</span>
            </a>
            <a href="<?= url('settings') ?>" class="nav-item <?= $isSettings ? 'active' : '' ?>">
                <i class="ph ph-gear-six"></i><span class="nav-label">Pengaturan Situs</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="side-section-label"><span>Sesi</span></div>
        <div class="side-user-card">
            <span class="dx-sidebar-pulse" title="Sesi aktif"></span>
            <?= avatar_tag($user, 'avatar-sm') ?>
            <div class="side-user-info">
                <strong><?= e($user['name'] ?? 'Pengguna') ?></strong>
                <small><?= e(ucfirst($user['role'] ?? 'member')) ?></small>
            </div>
        </div>
        <div class="side-foot">
            <a href="<?= url('logout') ?>" class="nav-item danger">
                <i class="ph ph-sign-out"></i><span class="nav-label">Keluar</span>
            </a>
        </div>
    </aside>

    <!-- ========== MAIN AREA ========== -->
    <div class="app-main">
        <!-- TOPBAR -->
        <header class="topbar glass-panel">
            <div class="top-left">
                <button class="icon-btn topbar-toggle" id="btnMobileMenu" aria-label="Buka menu">
                    <i class="ph ph-list"></i>
                </button>
                <div class="breadcrumb">
                    <a href="<?= url('dashboard') ?>">Beranda</a>
                    <i class="ph ph-caret-right"></i>
                    <span><?= e($title ?? 'Dashboard') ?></span>
                    <?php if ($breadcrumbSub): ?>
                        <span class="breadcrumb-sub"><?= e($breadcrumbSub) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="top-right">
                <?php if ($isAdmin): ?>
                <div class="dx-topbar-extra" title="Semua sistem berjalan normal">
                    <i class="ph ph-shield-check"></i><span>Sistem Sehat</span>
                </div>
                <?php endif; ?>
                <div class="dx-time-display">
                    <i class="ph ph-clock"></i>
                    <span id="dxClockText">--:--:--</span>
                </div>
                <div class="top-search">
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="text" placeholder="Pencarian cepat… (⌘K)" id="globalSearch">
                </div>
                <?php if ($isAdmin): ?>
                <div class="dx-notif-wrap">
                    <button class="icon-btn top-icon-btn" id="dxNotifBtn" title="Notifikasi" aria-label="Notifikasi">
                        <i class="ph ph-bell"></i>
                        <span class="notif-dot" id="dxNotifDot" style="display:none;"></span>
                    </button>
                    <div class="dx-notif-dropdown" id="dxNotifDropdown">
                        <div class="dx-notif-head">
                            <span>Notifikasi</span>
                            <small id="dxNotifCount">Memuat...</small>
                        </div>
                        <ul class="dx-notif-list" id="dxNotifList">
                            <li class="dx-notif-empty"><i class="ph ph-arrows-clockwise"></i>Memuat notifikasi…</li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <button class="icon-btn top-icon-btn" title="Notifikasi" aria-label="Notifikasi" disabled>
                    <i class="ph ph-bell"></i>
                </button>
                <?php endif; ?>
                
                <!-- User Dropdown -->
                <div class="dx-user-wrap">
                    <button class="top-user" id="dxUserBtn" aria-label="Menu pengguna" style="background:none;border:none;cursor:pointer;padding:0">
                        <?= avatar_tag($user) ?>
                    </button>
                    <div class="dx-user-dropdown" id="dxUserDropdown">
                        <div class="dx-user-head">
                            <?= avatar_tag($user, 'avatar-sm') ?>
                            <div>
                                <strong><?= e($user['name'] ?? 'Pengguna') ?></strong>
                                <small><?= e(ucfirst($user['role'] ?? 'member')) ?></small>
                            </div>
                        </div>
                        <ul class="dx-user-menu">
                            <li><a href="<?= url('profile') ?>"><i class="ph ph-user-circle-gear"></i> Profil Saya</a></li>
                            <?php if ($isAdmin): ?>
                            <li><a href="<?= url('settings') ?>"><i class="ph ph-gear-six"></i> Pengaturan</a></li>
                            <?php endif; ?>
                            <li><a href="#" onclick="window.print(); return false;"><i class="ph ph-printer"></i> Cetak Halaman <kbd>Ctrl+P</kbd></a></li>
                            <li><a href="#" id="dxShortcutHint"><i class="ph ph-keyboard"></i> Pintasan <kbd>Ctrl+/</kbd></a></li>
                            <li><a href="<?= url('logout') ?>" class="danger"><i class="ph ph-sign-out"></i> Keluar <kbd>Ctrl+Q</kbd></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="app-content"><?= $content ?></main>

        <!-- FOOTER -->
        <footer class="app-footer">
            <span>© <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong></span>
            <span class="dx-footer-version">v7.0 ULTIMATE</span>
            <span class="footer-sep">•</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS</span>
        </footer>
    </div>

    <!-- ========== FLOATING ACTION BUTTON (ENHANCED) ========== -->
    <?php if ($isAdmin): ?>
    <div class="fab-zone" id="fabZone">
        <button class="fab-main" id="fabMain" aria-label="Aksi cepat" title="Aksi cepat">
            <i class="ph ph-plus"></i>
        </button>
        <div class="fab-menu">
            <a href="<?= url('members') ?>?action=add" class="fab-item">
                <i class="ph ph-user-plus"></i><span>Anggota Baru</span>
            </a>
            <a href="<?= url('events') ?>?action=add" class="fab-item">
                <i class="ph ph-calendar-plus"></i><span>Event Baru</span>
            </a>
            <a href="<?= url('articles') ?>?action=add" class="fab-item">
                <i class="ph ph-newspaper"></i><span>Artikel Baru</span>
            </a>
            <a href="<?= url('content') ?>" class="fab-item">
                <i class="ph ph-paint-brush"></i><span>Konten Situs</span>
            </a>
            <a href="<?= url('census') ?>" class="fab-item">
                <i class="ph ph-clipboard-text"></i><span>Sensus</span>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- TOAST ZONE -->
    <div class="toast-zone" id="toastZone" aria-live="polite"></div>

    <!-- SCRIPTS -->
    <script src="<?= asset('js/app.js') ?>?v=7.0"></script>
    <script>
    (() => {
        'use strict';
        
        // ---- Jam real-time ----
        const clockEl = document.getElementById('dxClockText');
        if (clockEl) {
            const tick = () => clockEl.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            tick(); setInterval(tick, 1000);
        }
        
        // ---- User dropdown toggle ----
        const userBtn = document.getElementById('dxUserBtn');
        const userDrop = document.getElementById('dxUserDropdown');
        if (userBtn && userDrop) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDrop.classList.toggle('show');
            });
            document.addEventListener('click', (e) => {
                if (!userDrop.contains(e.target) && !userBtn.contains(e.target)) {
                    userDrop.classList.remove('show');
                }
            });
        }
        
        // ---- FAB toggle ----
        const fabZone = document.getElementById('fabZone');
        const fabMain = document.getElementById('fabMain');
        if (fabZone && fabMain) {
            fabMain.addEventListener('click', (e) => {
                e.stopPropagation();
                fabZone.classList.toggle('open');
            });
            document.addEventListener('click', (e) => {
                if (!fabZone.contains(e.target)) fabZone.classList.remove('open');
            });
        }

        // ---- Global keyboard shortcuts ----
        document.addEventListener('keydown', (e) => {
            // Escape closes FAB and dropdowns
            if (e.key === 'Escape') {
                fabZone?.classList.remove('open');
                userDrop?.classList.remove('show');
            }
            // Ctrl+Q = Logout
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'q') {
                e.preventDefault();
                window.location.href = document.body.dataset.base + 'logout';
            }
            // Ctrl+P = Print (handled by browser, but prevent default for our custom print)
        });
        
        // ---- Shortcut hint ----
        document.getElementById('dxShortcutHint')?.addEventListener('click', (e) => {
            e.preventDefault();
            document.dispatchEvent(new KeyboardEvent('keydown', { key: '/', ctrlKey: true }));
        });
    })();
    </script>
</body>
</html>