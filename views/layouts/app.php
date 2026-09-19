<!-- File: views/layouts/app.php (FINAL - TAHAP 5.7) -->
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

    <style>
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
        .side-collapse-btn i { font-size: 11px; }
        .sidebar.collapsed .side-brand { flex-direction: column; align-items: center; gap: 10px; padding: 6px 0 14px; }

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

        .dx-topbar-extra {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 10px;
            background: rgba(16,185,129,.08); border: 1px solid rgba(16,185,129,.2);
            font-size: 11.5px; font-weight: 700; color: #6ee7b7;
        }
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

        /* ---- Dropdown Notifikasi ---- */
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
            cursor: pointer;
            transition: .2s;
            text-decoration: none;
            color: inherit;
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

        @media (max-width: 720px) {
            .dx-topbar-extra, .dx-time-display { display: none; }
            .dx-notif-dropdown { width: calc(100vw - 32px); right: -60px; }
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
?>
<body data-base="<?= e(BASE_URL) ?>" class="app-shell">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
                <button class="icon-btn top-icon-btn" title="Notifikasi" aria-label="Notifikasi">
                    <i class="ph ph-bell"></i>
                </button>
                <?php endif; ?>
                <div class="top-user">
                    <?= avatar_tag($user) ?>
                </div>
            </div>
        </header>

        <main class="app-content"><?= $content ?></main>

        <footer class="app-footer">
            <span>© <?= date('Y') ?> <strong><?= e(APP_NAME) ?></strong></span>
            <span class="dx-footer-version">v3.0 ULTIMATE</span>
            <span class="footer-sep">•</span>
            <span>Dibangun dengan PHP 8+ & Vanilla JS</span>
        </footer>
    </div>

    <div class="toast-zone" id="toastZone"></div>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script>
    (() => {
        const el = document.getElementById('dxClockText');
        if (!el) return;
        const tick = () => el.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        tick(); setInterval(tick, 1000);
    })();
    </script>
</body>
</html>