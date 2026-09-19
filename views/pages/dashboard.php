<!-- File: views/pages/dashboard.php (FINAL - TERINTEGRASI DENGAN FEED DINAMIS) -->

<?php
    // Logika internal view: sapaan dinamis + tanggal Indonesia
    $hour  = (int) date('G');
    if      ($hour < 11) { $dx_greet = 'Selamat pagi';     $dx_greetIcon = '☀️'; }
    elseif  ($hour < 15) { $dx_greet = 'Selamat siang';    $dx_greetIcon = '🌤️'; }
    elseif  ($hour < 19) { $dx_greet = 'Selamat sore';     $dx_greetIcon = '🌇'; }
    else                 { $dx_greet = 'Selamat malam';    $dx_greetIcon = '🌙'; }

    $dx_days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $dx_months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $dx_todayLabel = $dx_days[(int)date('w')] . ', ' . date('j') . ' ' . $dx_months[(int)date('n')-1] . ' ' . date('Y');

    // Informasi sistem
    $dx_sys = [
        'php'    => PHP_VERSION,
        'db'     => defined('DB_NAME') ? DB_NAME : 'organisasi',
        'tz'     => date_default_timezone_get(),
        'upload' => ini_get('upload_max_filesize'),
        'memory' => ini_get('memory_limit'),
    ];

    // Hitung persentase aktif
    $dx_activePct = round(((int)($activeUsers ?? 0) / max(1, (int)($totalUsers ?? 1))) * 100);

    // Pastikan variabel $feed ada (fallback ke array kosong)
    $feed = $feed ?? [];
?>

<!-- ============================================================
     DELUXE DASHBOARD STYLES — Internal (prefix dx-)
     ============================================================ -->
<style>
    /* ---------- Welcome Hero Strip ---------- */
    .dx-hero-strip {
        position: relative;
        overflow: hidden;
        border-radius: var(--rad-lg);
        border: 1px solid var(--glass-brd);
        padding: 28px 32px;
        margin-bottom: 24px;
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 22px;
        background:
            radial-gradient(600px 260px at 92% -30%, rgba(34,211,238,.22), transparent 60%),
            radial-gradient(520px 240px at 8% 140%, rgba(139,92,246,.28), transparent 60%),
            radial-gradient(400px 200px at 50% 50%, rgba(99,102,241,.1), transparent 70%),
            var(--glass);
        animation: dx-fadeup .6s both;
    }
    .dx-hero-strip::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 22px 22px;
        pointer-events: none;
    }
    .dx-hero-avatar { position: relative; z-index: 1; }
    .dx-hero-avatar .avatar {
        width: 72px; height: 72px;
        border-radius: 22px;
        font-size: 26px;
        box-shadow: 0 12px 30px rgba(99,102,241,.45);
        border: 2px solid rgba(255,255,255,.1);
    }
    .dx-hero-avatar::after {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 30px;
        background: linear-gradient(135deg, var(--pri), var(--acc));
        filter: blur(20px);
        opacity: .4;
        z-index: -1;
        animation: dx-glow 3s ease-in-out infinite;
    }
    @keyframes dx-glow {
        0%,100% { opacity: .4; transform: scale(1); }
        50% { opacity: .65; transform: scale(1.05); }
    }
    .dx-hero-copy { position: relative; z-index: 1; min-width: 0; }
    .dx-hero-greeting {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: var(--acc);
        margin-bottom: 6px;
    }
    .dx-hero-greeting i { font-size: 14px; }
    .dx-hero-copy h1 {
        font-size: clamp(22px, 3vw, 28px);
        font-weight: 800;
        letter-spacing: -.6px;
        line-height: 1.15;
        margin-bottom: 6px;
        background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .dx-hero-copy p {
        color: var(--txt-1);
        font-size: 13.5px;
        max-width: 58ch;
        line-height: 1.6;
    }
    .dx-hero-date {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        padding: 10px 18px;
        border-radius: 14px;
        background: rgba(255,255,255,.04);
        border: 1px solid var(--glass-brd);
        white-space: nowrap;
    }
    .dx-hero-date strong {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--txt-0);
    }
    .dx-hero-date small {
        font-size: 10.5px;
        color: var(--txt-1);
        font-weight: 600;
        letter-spacing: .4px;
    }
    .dx-hero-date .dx-date-icon {
        width: 28px; height: 28px;
        border-radius: 9px;
        display: grid; place-items: center;
        background: linear-gradient(135deg, var(--pri), var(--acc));
        color: #fff;
        font-size: 13px;
        margin-bottom: 6px;
    }
    @keyframes dx-fadeup {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---------- Quick Actions Grid ---------- */
    .dx-qa-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 24px;
    }
    .dx-qa-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-radius: 16px;
        background: var(--glass);
        border: 1px solid var(--glass-brd);
        backdrop-filter: blur(18px);
        transition: .3s cubic-bezier(.22,1,.36,1);
        animation: dx-fadeup .5s both;
        overflow: hidden;
    }
    .dx-qa-card:nth-child(1) { animation-delay: .05s; }
    .dx-qa-card:nth-child(2) { animation-delay: .1s; }
    .dx-qa-card:nth-child(3) { animation-delay: .15s; }
    .dx-qa-card:nth-child(4) { animation-delay: .2s; }
    .dx-qa-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(99,102,241,.05));
        opacity: 0;
        transition: .3s;
    }
    .dx-qa-card:hover {
        transform: translateY(-5px);
        border-color: rgba(99,102,241,.4);
        box-shadow: 0 18px 40px rgba(2,6,23,.55);
    }
    .dx-qa-card:hover::before { opacity: 1; }
    .dx-qa-icon {
        width: 44px; height: 44px;
        border-radius: 13px;
        display: grid; place-items: center;
        color: #fff;
        font-size: 20px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }
    .dx-qa-copy { position: relative; z-index: 1; min-width: 0; }
    .dx-qa-copy strong {
        display: block;
        font-size: 13.5px;
        font-weight: 800;
        margin-bottom: 2px;
    }
    .dx-qa-copy small {
        color: var(--txt-1);
        font-size: 11.5px;
    }
    .dx-qa-arrow {
        margin-left: auto;
        color: var(--txt-2);
        font-size: 16px;
        transition: .3s;
        position: relative;
        z-index: 1;
    }
    .dx-qa-card:hover .dx-qa-arrow {
        color: var(--acc);
        transform: translateX(4px);
    }

    /* ---------- Enhanced Stat Cards ---------- */
    .dx-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .dx-stat {
        position: relative;
        padding: 22px;
        border-radius: var(--rad-lg);
        background: var(--glass);
        border: 1px solid var(--glass-brd);
        backdrop-filter: blur(18px);
        overflow: hidden;
        transition: .3s cubic-bezier(.22,1,.36,1);
        animation: dx-fadeup .55s both;
    }
    .dx-stat:nth-child(1) { animation-delay: .05s; }
    .dx-stat:nth-child(2) { animation-delay: .1s; }
    .dx-stat:nth-child(3) { animation-delay: .15s; }
    .dx-stat:nth-child(4) { animation-delay: .2s; }
    .dx-stat::after {
        content: '';
        position: absolute;
        top: -20px; right: -20px;
        width: 120px; height: 120px;
        border-radius: 50%;
        background: radial-gradient(circle, var(--stat-glow, rgba(99,102,241,.18)), transparent 70%);
        pointer-events: none;
        transition: .4s;
    }
    .dx-stat:hover {
        transform: translateY(-5px);
        box-shadow: 0 26px 60px rgba(2,6,23,.55);
    }
    .dx-stat:hover::after {
        transform: scale(1.3);
        opacity: .9;
    }
    .dx-stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
    }
    .dx-stat-icon {
        width: 44px; height: 44px;
        border-radius: 13px;
        display: grid; place-items: center;
        color: #fff;
        font-size: 20px;
    }
    .dx-stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 11px;
        font-weight: 800;
        background: rgba(16,185,129,.1);
        border: 1px solid rgba(16,185,129,.25);
        color: #6ee7b7;
    }
    .dx-stat-trend.neutral {
        background: rgba(154,163,199,.1);
        border-color: rgba(154,163,199,.25);
        color: var(--txt-1);
    }
    .dx-stat-body { position: relative; z-index: 1; }
    .dx-stat-num {
        display: block;
        font-size: 30px;
        font-weight: 800;
        letter-spacing: -1px;
        font-variant-numeric: tabular-nums;
        line-height: 1;
        margin-bottom: 6px;
    }
    .dx-stat-label {
        display: block;
        color: var(--txt-1);
        font-size: 12.5px;
        font-weight: 600;
        margin-bottom: 12px;
    }
    .dx-mini-bar {
        display: flex;
        align-items: flex-end;
        gap: 3px;
        height: 28px;
    }
    .dx-mini-bar span {
        flex: 1;
        background: linear-gradient(180deg, var(--bar-color, var(--acc)), transparent);
        border-radius: 2px 2px 0 0;
        opacity: .55;
        animation: dx-bar-rise .8s cubic-bezier(.22,1,.36,1) both;
    }
    .dx-mini-bar span:nth-child(1) { animation-delay: .05s; }
    .dx-mini-bar span:nth-child(2) { animation-delay: .1s; }
    .dx-mini-bar span:nth-child(3) { animation-delay: .15s; }
    .dx-mini-bar span:nth-child(4) { animation-delay: .2s; }
    .dx-mini-bar span:nth-child(5) { animation-delay: .25s; }
    .dx-mini-bar span:nth-child(6) { animation-delay: .3s; }
    .dx-mini-bar span:nth-child(7) { animation-delay: .35s; }
    @keyframes dx-bar-rise {
        from { height: 0; }
    }

    /* ---------- Activity Feed Deluxe ---------- */
    .dx-feed-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 18px;
        margin-bottom: 24px;
    }
    .dx-card {
        padding: 24px 26px;
        border-radius: var(--rad-lg);
        background: var(--glass);
        border: 1px solid var(--glass-brd);
        backdrop-filter: blur(18px);
        animation: dx-fadeup .6s .15s both;
    }
    .dx-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--glass-brd);
    }
    .dx-card-head h3 {
        font-size: 15px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dx-card-head h3 i { color: var(--acc); }
    .dx-card-head .link-soft {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--acc);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: .2s;
    }
    .dx-card-head .link-soft:hover { gap: 8px; }

    .dx-feed-list {
        list-style: none;
        display: flex;
        flex-direction: column;
    }
    .dx-feed-item {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 13px 2px;
        border-bottom: 1px solid rgba(255,255,255,.05);
        transition: .2s;
    }
    .dx-feed-item:last-child { border-bottom: 0; }
    .dx-feed-item:hover { padding-left: 6px; }
    .dx-feed-icon {
        width: 38px; height: 38px;
        border-radius: 11px;
        display: grid; place-items: center;
        color: #fff;
        font-size: 16px;
        flex-shrink: 0;
    }
    .dx-feed-body {
        flex: 1;
        min-width: 0;
    }
    .dx-feed-body strong {
        display: block;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 2px;
    }
    .dx-feed-body span {
        color: var(--txt-1);
        font-size: 11.5px;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dx-feed-time {
        color: var(--txt-2);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        padding-top: 5px;
    }

    /* ---------- Empty Feed State ---------- */
    .dx-feed-empty {
        padding: 30px 20px;
        text-align: center;
        color: var(--txt-1);
    }
    .dx-feed-empty i {
        font-size: 32px;
        color: var(--txt-2);
        display: block;
        margin-bottom: 10px;
    }
    .dx-feed-empty p {
        font-size: 13px;
        line-height: 1.6;
    }

    /* ---------- System Info Panel ---------- */
    .dx-sys-list {
        display: flex;
        flex-direction: column;
    }
    .dx-sys-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 2px;
        border-bottom: 1px dashed rgba(255,255,255,.08);
        font-size: 12.5px;
    }
    .dx-sys-item:last-child { border-bottom: 0; }
    .dx-sys-item span { color: var(--txt-1); }
    .dx-sys-item strong {
        font-weight: 700;
        color: var(--txt-0);
        font-family: 'SF Mono', Monaco, Consolas, monospace;
        font-size: 11.5px;
    }
    .dx-sys-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 99px;
        background: rgba(16,185,129,.1);
        border: 1px solid rgba(16,185,129,.25);
        color: #6ee7b7;
        font-size: 10.5px;
        font-weight: 800;
    }
    .dx-sys-status i { font-size: 11px; }

    /* ---------- Keyboard Shortcuts Banner ---------- */
    .dx-kbd-banner {
        margin-top: 8px;
        padding: 14px 22px;
        border-radius: 14px;
        background: linear-gradient(90deg, rgba(99,102,241,.06), rgba(34,211,238,.04));
        border: 1px solid var(--glass-brd);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        flex-wrap: wrap;
        font-size: 12px;
        color: var(--txt-1);
        animation: dx-fadeup .6s .3s both;
    }
    .dx-kbd {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .dx-kbd kbd {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        font-family: inherit;
        font-size: 10.5px;
        font-weight: 800;
        color: var(--txt-0);
        box-shadow: 0 2px 0 rgba(0,0,0,.25);
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 980px) {
        .dx-hero-strip { grid-template-columns: auto 1fr; }
        .dx-hero-date { grid-column: 1 / -1; flex-direction: row; align-items: center; gap: 12px; }
        .dx-hero-date .dx-date-icon { margin-bottom: 0; }
        .dx-qa-grid { grid-template-columns: 1fr 1fr; }
        .dx-feed-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .dx-qa-grid { grid-template-columns: 1fr; }
        .dx-hero-strip { padding: 22px; }
        .dx-hero-avatar .avatar { width: 60px; height: 60px; font-size: 22px; }
    }
</style>

<!-- ============ HERO STRIP ============ -->
<section class="dx-hero-strip">
    <div class="dx-hero-avatar">
        <?= avatar_tag($user) ?>
    </div>
    <div class="dx-hero-copy">
        <span class="dx-hero-greeting">
            <i class="ph ph-sparkle"></i>
            <?= e($dx_greetIcon) ?> <?= e($dx_greet) ?>
        </span>
        <h1>Selamat datang kembali, <?= e($user['name']) ?>!</h1>
        <p>Berikut ringkasan aktivitas dan pertumbuhan <strong><?= e(APP_NAME) ?></strong> hari ini. Semua data dibaca langsung dari basis data secara real-time.</p>
    </div>
    <div class="dx-hero-date">
        <div class="dx-date-icon"><i class="ph ph-calendar-dots"></i></div>
        <strong><?= e(date('j')) ?> <?= e($dx_months[(int)date('n')-1]) ?></strong>
        <small><?= e($dx_days[(int)date('w')]) ?>, <?= e(date('Y')) ?></small>
    </div>
</section>

<!-- ============ QUICK ACTIONS ============ -->
<section class="dx-qa-grid">
    <a href="<?= url('members') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-1"><i class="ph ph-user-plus"></i></div>
        <div class="dx-qa-copy">
            <strong>Tambah Anggota</strong>
            <small>Registrasi anggota baru</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('events') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-4"><i class="ph ph-calendar-plus"></i></div>
        <div class="dx-qa-copy">
            <strong>Buat Event</strong>
            <small>Jadwalkan kegiatan baru</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php if (($user['role'] ?? '') === 'admin'): ?>
    <a href="<?= url('articles') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-2"><i class="ph ph-newspaper"></i></div>
        <div class="dx-qa-copy">
            <strong>Tulis Artikel</strong>
            <small>Publikasikan ke situs</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('settings') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-3"><i class="ph ph-gear-six"></i></div>
        <div class="dx-qa-copy">
            <strong>Pengaturan</strong>
            <small>Identitas organisasi</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php else: ?>
    <a href="<?= url('profile') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-2"><i class="ph ph-identification-card"></i></div>
        <div class="dx-qa-copy">
            <strong>Profil Saya</strong>
            <small>Perbarui data pribadi</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('events') ?>" class="dx-qa-card">
        <div class="dx-qa-icon grad-3"><i class="ph ph-broadcast"></i></div>
        <div class="dx-qa-copy">
            <strong>Event Berlangsung</strong>
            <small>Lihat agenda hari ini</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php endif; ?>
</section>

<!-- ============ ENHANCED STATS ============ -->
<section class="dx-stats-grid">
    <article class="dx-stat" style="--stat-glow: rgba(99,102,241,.22); --bar-color: #8b5cf6;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-1"><i class="ph ph-users-three"></i></div>
            <span class="dx-stat-trend"><i class="ph ph-trend-up"></i> Aktif</span>
        </div>
        <div class="dx-stat-body">
            <strong class="dx-stat-num" data-count="<?= (int) $totalMembers ?>">0</strong>
            <span class="dx-stat-label">Total Anggota</span>
            <div class="dx-mini-bar">
                <span style="height:35%"></span><span style="height:52%"></span>
                <span style="height:44%"></span><span style="height:68%"></span>
                <span style="height:58%"></span><span style="height:82%"></span>
                <span style="height:94%"></span>
            </div>
        </div>
    </article>

    <article class="dx-stat" style="--stat-glow: rgba(14,165,233,.22); --bar-color: #22d3ee;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-2"><i class="ph ph-user-circle"></i></div>
            <span class="dx-stat-trend neutral"><i class="ph ph-minus"></i> Stabil</span>
        </div>
        <div class="dx-stat-body">
            <strong class="dx-stat-num" data-count="<?= (int) $totalUsers ?>">0</strong>
            <span class="dx-stat-label">Total Pengguna</span>
            <div class="dx-mini-bar">
                <span style="height:60%"></span><span style="height:62%"></span>
                <span style="height:58%"></span><span style="height:64%"></span>
                <span style="height:61%"></span><span style="height:63%"></span>
                <span style="height:62%"></span>
            </div>
        </div>
    </article>

    <article class="dx-stat" style="--stat-glow: rgba(16,185,129,.22); --bar-color: #10b981;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-3"><i class="ph ph-shield-check"></i></div>
            <span class="dx-stat-trend"><i class="ph ph-trend-up"></i> <?= $dx_activePct ?>%</span>
        </div>
        <div class="dx-stat-body">
            <strong class="dx-stat-num" data-count="<?= (int) $activeUsers ?>">0</strong>
            <span class="dx-stat-label">Akun Aktif</span>
            <div class="dx-mini-bar">
                <span style="height:<?= max(20, $dx_activePct - 30) ?>%"></span>
                <span style="height:<?= max(25, $dx_activePct - 20) ?>%"></span>
                <span style="height:<?= max(30, $dx_activePct - 15) ?>%"></span>
                <span style="height:<?= max(35, $dx_activePct - 10) ?>%"></span>
                <span style="height:<?= max(40, $dx_activePct - 5) ?>%"></span>
                <span style="height:<?= max(50, $dx_activePct) ?>%"></span>
                <span style="height:<?= min(100, $dx_activePct + 5) ?>%"></span>
            </div>
        </div>
    </article>

    <article class="dx-stat" style="--stat-glow: rgba(245,158,11,.22); --bar-color: #f59e0b;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-4"><i class="ph ph-calendar-blank"></i></div>
            <span class="dx-stat-trend"><i class="ph ph-broadcast"></i> Live</span>
        </div>
        <div class="dx-stat-body">
            <strong class="dx-stat-num" data-count="<?= (int) ($eventThisMonth ?? 0) ?>">0</strong>
            <span class="dx-stat-label">Event Bulan Ini</span>
            <div class="dx-mini-bar">
                <span style="height:30%"></span><span style="height:48%"></span>
                <span style="height:72%"></span><span style="height:56%"></span>
                <span style="height:84%"></span><span style="height:64%"></span>
                <span style="height:96%"></span>
            </div>
        </div>
    </article>
</section>

<!-- ============ CHART SECTION ============ -->
<section class="glass-card chart-card">
    <div class="chart-head">
        <div>
            <h3>Pertumbuhan Anggota</h3>
            <p class="chart-sub">Tren pendaftaran dalam 6 bulan terakhir</p>
        </div>
        <div class="chart-head-right">
            <span class="chart-badge"><i class="ph ph-chart-line-up"></i> 6 bulan terakhir</span>
            <div class="chart-legend">
                <span class="legend-dot"></span>
                <span>Pendaftar Baru</span>
            </div>
        </div>
    </div>
    <div id="chartRegistrations" class="chart-area"></div>
</section>

<!-- ============ ACTIVITY FEED + SYSTEM INFO (DINAMIS DARI DATABASE) ============ -->
<section class="dx-feed-grid">
    <section class="dx-card">
        <div class="dx-card-head">
            <h3><i class="ph ph-pulse"></i> Aktivitas Terkini</h3>
            <span class="live-indicator"><span class="live-dot"></span> Real-time</span>
        </div>

        <?php if (!empty($feed)): ?>
            <ul class="dx-feed-list">
                <?php foreach ($feed as $f): ?>
                <li class="dx-feed-item">
                    <span class="dx-feed-icon <?= e($f['grad']) ?>">
                        <i class="ph <?= e($f['icon']) ?>"></i>
                    </span>
                    <div class="dx-feed-body">
                        <strong><?= e($f['title']) ?></strong>
                        <span><?= e($f['sub']) ?></span>
                    </div>
                    <span class="dx-feed-time"><?= e(time_ago($f['time'])) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="dx-feed-empty">
                <i class="ph ph-clock-countdown"></i>
                <p>Belum ada aktivitas yang tercatat.<br>Mulai dengan menambahkan anggota, event, atau artikel pertama.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="dx-card">
        <div class="dx-card-head">
            <h3><i class="ph ph-cpu"></i> Kesehatan Sistem</h3>
            <span class="dx-sys-status"><i class="ph ph-check-circle"></i> Normal</span>
        </div>
        <div class="dx-sys-list">
            <div class="dx-sys-item">
                <span><i class="ph ph-code"></i> Versi Aplikasi</span>
                <strong>v3.0 ULTIMATE</strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-file-php"></i> PHP Engine</span>
                <strong><?= e($dx_sys['php']) ?></strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-database"></i> Basis Data</span>
                <strong><?= e($dx_sys['db']) ?></strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-globe"></i> Zona Waktu</span>
                <strong><?= e($dx_sys['tz']) ?></strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-upload"></i> Batas Unggah</span>
                <strong><?= e($dx_sys['upload']) ?></strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-memory"></i> Batas Memori</span>
                <strong><?= e($dx_sys['memory']) ?></strong>
            </div>
            <div class="dx-sys-item">
                <span><i class="ph ph-shield-check"></i> Status Sesi</span>
                <strong style="color:#6ee7b7;">Aktif & Aman</strong>
            </div>
        </div>
    </section>
</section>

<!-- ============ KEYBOARD SHORTCUTS BANNER ============ -->
<div class="dx-kbd-banner">
    <span class="dx-kbd"><kbd>⌘</kbd> <kbd>K</kbd> Pencarian Cepat</span>
    <span class="dx-kbd"><kbd>⌘</kbd> <kbd>/</kbd> Buka Pintasan</span>
    <span class="dx-kbd"><kbd>Esc</kbd> Tutup Modal</span>
    <span class="dx-kbd"><kbd>↵</kbd> Konfirmasi</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function(){
    const chartEl = document.querySelector('#chartRegistrations');
    if (!chartEl) return;
    fetch('<?= url('api/stats/registrations') ?>')
        .then(r => r.json())
        .then(d => {
            new ApexCharts(chartEl, {
                chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif', animations: { easing: 'easeinout', speed: 900 } },
                series: [{ name: 'Anggota Baru', data: d.totals }],
                xaxis: {
                    categories: d.labels,
                    labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } }, forceIntegers: true
                },
                colors: ['#22d3ee'],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, stops: [0, 90, 100],
                        colorStops: [
                            { offset: 0, opacity: 0.5, color: '#22d3ee' },
                            { offset: 100, opacity: 0.0, color: '#22d3ee' }
                        ]
                    }
                },
                grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                markers: { size: 5, colors: ['#22d3ee'], strokeColors: '#0b1020', strokeWidth: 2, hover: { size: 8 } },
                dataLabels: { enabled: false },
                tooltip: {
                    theme: 'dark',
                    style: { fontSize: '12px' },
                    y: { formatter: (v) => v + ' anggota' },
                    marker: { show: true }
                },
                theme: { mode: 'dark' }
            }).render();
        });

    /* Animasi counter pada stat card */
    const countObs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (!en.isIntersecting) return;
            const el = en.target;
            const target = parseInt(el.dataset.count, 10) || 0;
            const t0 = performance.now(), dur = 1400;
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick); else el.textContent = target.toLocaleString('id-ID');
            };
            requestAnimationFrame(tick);
            countObs.unobserve(el);
        });
    }, { threshold: 0.4 });
    document.querySelectorAll('[data-count]').forEach(el => countObs.observe(el));
})();
</script>