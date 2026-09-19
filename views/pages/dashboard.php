<?php
/**
 * ============================================================
 * DASHBOARD ADMIN — ULTIMATE EDITION v5.9
 * Semua gaya memakai prefix dx- agar tidak bentrok
 * ============================================================
 */
$hour = (int) date('G');
if      ($hour < 11) { $dx_greet = 'Selamat pagi';  $dx_greetIcon = 'ph-sun';        $dx_greetEmoji = '☀️';  $dx_greetColor = '#fbbf24'; }
elseif  ($hour < 15) { $dx_greet = 'Selamat siang'; $dx_greetIcon = 'ph-sun-dim';    $dx_greetEmoji = '🌤️'; $dx_greetColor = '#fb923c'; }
elseif  ($hour < 19) { $dx_greet = 'Selamat sore';  $dx_greetIcon = 'ph-cloud-sun';  $dx_greetEmoji = '🌇';  $dx_greetColor = '#f472b6'; }
else                 { $dx_greet = 'Selamat malam'; $dx_greetIcon = 'ph-moon-stars'; $dx_greetEmoji = '🌙';  $dx_greetColor = '#818cf8'; }

$dx_days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$dx_months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// Fallback aman untuk semua variabel
$totalMembers   = (int) ($totalMembers ?? 0);
$totalUsers     = (int) ($totalUsers ?? 0);
$activeUsers    = (int) ($activeUsers ?? 0);
$eventThisMonth = (int) ($eventThisMonth ?? 0);
$activePct      = $totalUsers > 0 ? (int) round(($activeUsers / $totalUsers) * 100) : 0;
$feed           = $feed ?? [];
$isAdmin        = ($user['role'] ?? '') === 'admin';

$dx_sys = [
    'php'    => PHP_VERSION,
    'db'     => defined('DB_NAME') ? DB_NAME : 'organisasi',
    'tz'     => date_default_timezone_get(),
    'upload' => ini_get('upload_max_filesize'),
    'memory' => ini_get('memory_limit'),
];
?>

<style>
/* ============ DASHBOARD ULTIMATE (prefix dx-) ============ */
@keyframes dx-fadeup {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: none; }
}
@keyframes dx-glow {
    0%, 100% { opacity: .4; transform: scale(1); }
    50%      { opacity: .65; transform: scale(1.06); }
}
@keyframes dx-bar-rise {
    from { height: 0; }
}

/* ---- Welcome Hero ---- */
.dx-hero {
    position: relative; overflow: hidden;
    border-radius: 22px;
    border: 1px solid var(--glass-brd);
    padding: 30px 34px;
    margin-bottom: 24px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center; gap: 24px;
    background:
        radial-gradient(600px 260px at 92% -30%, rgba(34,211,238,.22), transparent 60%),
        radial-gradient(520px 240px at 8% 140%, rgba(139,92,246,.28), transparent 60%),
        var(--glass);
    animation: dx-fadeup .6s both;
}
.dx-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 22px 22px;
    pointer-events: none;
}
.dx-avatar { position: relative; z-index: 1; }
.dx-avatar .avatar {
    width: 72px; height: 72px;
    border-radius: 22px; font-size: 26px;
    border: 2px solid rgba(255,255,255,.12);
    box-shadow: 0 12px 30px rgba(99,102,241,.45);
}
.dx-avatar::after {
    content: ''; position: absolute; inset: -8px;
    border-radius: 30px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    filter: blur(20px); opacity: .4;
    z-index: -1;
    animation: dx-glow 3s ease-in-out infinite;
}
.dx-copy { position: relative; z-index: 1; min-width: 0; }
.dx-greet {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 700;
    letter-spacing: .8px; text-transform: uppercase;
    color: var(--acc); margin-bottom: 6px;
}
.dx-greet i { font-size: 15px; }
.dx-copy h1 {
    font-size: clamp(22px, 3vw, 28px);
    font-weight: 800; letter-spacing: -.6px;
    margin-bottom: 6px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.dx-copy p {
    color: var(--txt-1); font-size: 13.5px;
    max-width: 58ch; line-height: 1.6;
}
.dx-copy strong { color: var(--txt-0); }

.dx-date {
    position: relative; z-index: 1;
    display: flex; align-items: center; gap: 12px;
    padding: 12px 18px; border-radius: 14px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    white-space: nowrap;
}
.dx-date-icon {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: grid; place-items: center;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff; font-size: 16px;
}
.dx-date strong {
    display: block; font-size: 13px; font-weight: 800;
}
.dx-date small {
    font-size: 10.5px; color: var(--txt-1); font-weight: 600;
}

/* ---- Quick Actions ---- */
.dx-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px; margin-bottom: 24px;
}
.dx-qa {
    position: relative;
    display: flex; align-items: center; gap: 14px;
    padding: 16px 18px; border-radius: 16px;
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    backdrop-filter: blur(18px);
    transition: .3s cubic-bezier(.22,1,.36,1);
    animation: dx-fadeup .5s both;
    overflow: hidden; text-decoration: none;
    color: inherit;
}
.dx-qa:nth-child(2) { animation-delay: .06s; }
.dx-qa:nth-child(3) { animation-delay: .12s; }
.dx-qa:nth-child(4) { animation-delay: .18s; }
.dx-qa:hover {
    transform: translateY(-5px);
    border-color: rgba(99,102,241,.4);
    box-shadow: 0 18px 40px rgba(2,6,23,.55);
}
.dx-qa-icon {
    width: 44px; height: 44px;
    border-radius: 13px;
    display: grid; place-items: center;
    color: #fff; font-size: 20px;
    flex-shrink: 0;
}
.dx-qa-copy { min-width: 0; }
.dx-qa-copy strong {
    display: block; font-size: 13.5px; font-weight: 800;
    margin-bottom: 2px;
}
.dx-qa-copy small { color: var(--txt-1); font-size: 11.5px; }
.dx-qa-arrow {
    margin-left: auto; color: var(--txt-2);
    font-size: 16px; transition: .3s;
}
.dx-qa:hover .dx-qa-arrow {
    color: var(--acc); transform: translateX(4px);
}

/* ---- Stat Cards ---- */
.dx-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 16px; margin-bottom: 24px;
}
.dx-stat {
    position: relative;
    padding: 22px; border-radius: 22px;
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    backdrop-filter: blur(18px);
    overflow: hidden; transition: .3s;
    animation: dx-fadeup .55s both;
}
.dx-stat:nth-child(2) { animation-delay: .06s; }
.dx-stat:nth-child(3) { animation-delay: .12s; }
.dx-stat:nth-child(4) { animation-delay: .18s; }
.dx-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 26px 60px rgba(2,6,23,.55);
}
.dx-stat::after {
    content: ''; position: absolute;
    top: -20px; right: -20px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--glow, rgba(99,102,241,.2)), transparent 70%);
    pointer-events: none;
    transition: .4s;
}
.dx-stat:hover::after { transform: scale(1.3); opacity: .9; }
.dx-stat-head {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 14px; position: relative; z-index: 1;
}
.dx-stat-icon {
    width: 44px; height: 44px;
    border-radius: 13px;
    display: grid; place-items: center;
    color: #fff; font-size: 20px;
}
.dx-trend {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 99px;
    font-size: 11px; font-weight: 800;
    background: rgba(16,185,129,.1);
    border: 1px solid rgba(16,185,129,.25);
    color: #6ee7b7;
}
.dx-trend.neutral {
    background: rgba(154,163,199,.1);
    border-color: rgba(154,163,199,.25);
    color: var(--txt-1);
}
.dx-stat-num {
    display: block; font-size: 30px; font-weight: 800;
    letter-spacing: -1px;
    font-variant-numeric: tabular-nums;
    line-height: 1; margin-bottom: 6px;
    position: relative; z-index: 1;
}
.dx-stat-label {
    display: block; color: var(--txt-1);
    font-size: 12.5px; font-weight: 600;
    margin-bottom: 12px; position: relative; z-index: 1;
}
.dx-bars {
    display: flex; align-items: flex-end; gap: 3px;
    height: 28px; position: relative; z-index: 1;
}
.dx-bars span {
    flex: 1; border-radius: 2px 2px 0 0;
    background: linear-gradient(180deg, var(--bar, var(--acc)), transparent);
    opacity: .55;
    animation: dx-bar-rise .8s cubic-bezier(.22,1,.36,1) both;
}
.dx-bars span:nth-child(2) { animation-delay: .05s; }
.dx-bars span:nth-child(3) { animation-delay: .1s; }
.dx-bars span:nth-child(4) { animation-delay: .15s; }
.dx-bars span:nth-child(5) { animation-delay: .2s; }
.dx-bars span:nth-child(6) { animation-delay: .25s; }
.dx-bars span:nth-child(7) { animation-delay: .3s; }

.dx-progress {
    height: 6px; border-radius: 99px;
    background: rgba(255,255,255,.08);
    overflow: hidden; position: relative; z-index: 1;
}
.dx-progress-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg, var(--ok), #34d399);
    transition: width 1s cubic-bezier(.22,1,.36,1);
}
.dx-progress-label {
    display: block; margin-top: 6px;
    font-size: 11px; color: var(--txt-2); font-weight: 600;
}

/* ---- Chart ---- */
.dx-chart {
    padding: 24px 26px; margin-bottom: 24px;
    animation: dx-fadeup .6s .12s both;
}
.dx-chart-head {
    display: flex; justify-content: space-between;
    align-items: flex-start; gap: 16px; flex-wrap: wrap;
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--glass-brd);
}
.dx-chart-head h3 {
    font-size: 17px; font-weight: 800; margin-bottom: 3px;
}
.dx-chart-head p {
    color: var(--txt-1); font-size: 12.5px; margin: 0;
}
.dx-chart-right {
    display: flex; align-items: center; gap: 14px;
}
.dx-badge {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 11.5px; font-weight: 700;
    color: var(--acc);
    background: rgba(34,211,238,.1);
    border: 1px solid rgba(34,211,238,.25);
    padding: 6px 12px; border-radius: 99px;
}
.dx-legend {
    display: flex; align-items: center; gap: 7px;
    font-size: 12px; color: var(--txt-1); font-weight: 600;
}
.dx-legend i {
    width: 10px; height: 10px;
    border-radius: 3px; background: var(--acc);
    box-shadow: 0 0 10px rgba(34,211,238,.5);
    display: inline-block;
    font-style: normal;
}
.dx-chart-area { min-height: 320px; }

/* ---- Feed + System Grid ---- */
.dx-grid {
    display: grid;
    grid-template-columns: 1.3fr 1fr;
    gap: 18px; margin-bottom: 24px;
}
.dx-card {
    padding: 24px 26px; border-radius: 22px;
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    backdrop-filter: blur(18px);
    animation: dx-fadeup .6s .18s both;
}
.dx-card-head {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-brd);
}
.dx-card-head h3 {
    font-size: 15px; font-weight: 800;
    display: flex; align-items: center; gap: 8px;
    margin: 0;
}
.dx-card-head h3 i { color: var(--acc); }
.dx-feed {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-direction: column;
}
.dx-feed li {
    display: flex; gap: 14px;
    align-items: flex-start;
    padding: 13px 2px;
    border-bottom: 1px solid rgba(255,255,255,.05);
    transition: .2s;
}
.dx-feed li:last-child { border-bottom: 0; }
.dx-feed li:hover { padding-left: 6px; }
.dx-feed-icon {
    width: 38px; height: 38px;
    border-radius: 11px;
    display: grid; place-items: center;
    color: #fff; font-size: 16px;
    flex-shrink: 0;
}
.dx-feed-body { flex: 1; min-width: 0; }
.dx-feed-body strong {
    display: block; font-size: 13px; font-weight: 700;
    margin-bottom: 2px;
}
.dx-feed-body span {
    color: var(--txt-1); font-size: 11.5px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dx-feed-time {
    color: var(--txt-2); font-size: 11px;
    font-weight: 700; white-space: nowrap;
    padding-top: 5px;
}
.dx-feed-empty {
    padding: 30px 20px;
    text-align: center; color: var(--txt-1);
    font-size: 13px;
}
.dx-feed-empty i {
    font-size: 32px; color: var(--txt-2);
    display: block; margin-bottom: 10px;
}
.dx-sys {
    display: flex; flex-direction: column;
}
.dx-sys-item {
    display: flex; justify-content: space-between;
    align-items: center; gap: 12px;
    padding: 10px 2px;
    border-bottom: 1px dashed rgba(255,255,255,.08);
    font-size: 12.5px;
}
.dx-sys-item:last-child { border-bottom: 0; }
.dx-sys-item > span {
    color: var(--txt-1);
    display: inline-flex; align-items: center; gap: 8px;
}
.dx-sys-item > span i {
    color: var(--acc); font-size: 14px;
}
.dx-sys-item strong {
    font-weight: 700; color: var(--txt-0);
    font-family: Consolas, Monaco, monospace;
    font-size: 11.5px;
}
.dx-sys-ok {
    color: #6ee7b7 !important;
    font-family: inherit !important;
    font-size: 12px !important;
    display: inline-flex; align-items: center; gap: 6px;
}

/* ---- Keyboard Banner ---- */
.dx-kbd {
    margin-top: 8px; padding: 14px 22px;
    border-radius: 14px;
    background: linear-gradient(90deg, rgba(99,102,241,.06), rgba(34,211,238,.04));
    border: 1px solid var(--glass-brd);
    display: flex; align-items: center;
    justify-content: center; gap: 24px;
    flex-wrap: wrap;
    font-size: 12px; color: var(--txt-1);
    animation: dx-fadeup .6s .24s both;
}
.dx-kbd span {
    display: inline-flex; align-items: center; gap: 8px;
}
.dx-kbd kbd {
    display: inline-block;
    padding: 3px 8px; border-radius: 6px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10.5px; font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 2px 0 rgba(0,0,0,.25);
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .dx-hero {
        grid-template-columns: auto 1fr;
    }
    .dx-date { grid-column: 1 / -1; }
    .dx-actions { grid-template-columns: 1fr 1fr; }
    .dx-grid { grid-template-columns: 1fr; }
}
@media (max-width: 560px) {
    .dx-actions { grid-template-columns: 1fr; }
    .dx-hero { padding: 22px; }
    .dx-avatar .avatar { width: 60px; height: 60px; font-size: 22px; }
}
</style>

<!-- ============ WELCOME HERO ============ -->
<section class="dx-hero">
    <div class="dx-avatar">
        <?= avatar_tag($user) ?>
    </div>
    <div class="dx-copy">
        <span class="dx-greet">
            <i class="ph <?= $dx_greetIcon ?>" style="color:<?= $dx_greetColor ?>"></i>
            <?= $dx_greetEmoji ?> <?= e($dx_greet) ?>, <?= e($user['name']) ?>!
        </span>
        <h1>Selamat datang kembali</h1>
        <p>Berikut ringkasan aktivitas dan pertumbuhan <strong><?= e(APP_NAME) ?></strong> hari ini. Semua data dibaca real-time dari basis data.</p>
    </div>
    <div class="dx-date">
        <div class="dx-date-icon"><i class="ph ph-calendar-dots"></i></div>
        <div>
            <strong><?= date('j') ?> <?= e($dx_months[(int)date('n')-1]) ?></strong>
            <small><?= e($dx_days[(int)date('w')]) ?>, <?= date('Y') ?></small>
        </div>
    </div>
</section>

<!-- ============ QUICK ACTIONS ============ -->
<section class="dx-actions">
    <a href="<?= url('members') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-1"><i class="ph ph-user-plus"></i></div>
        <div class="dx-qa-copy">
            <strong>Tambah Anggota</strong>
            <small>Registrasi anggota baru</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('events') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-4"><i class="ph ph-calendar-plus"></i></div>
        <div class="dx-qa-copy">
            <strong>Buat Event</strong>
            <small>Jadwalkan kegiatan baru</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php if ($isAdmin): ?>
    <a href="<?= url('articles') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-2"><i class="ph ph-newspaper"></i></div>
        <div class="dx-qa-copy">
            <strong>Tulis Artikel</strong>
            <small>Publikasikan ke situs</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('settings') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-3"><i class="ph ph-gear-six"></i></div>
        <div class="dx-qa-copy">
            <strong>Pengaturan</strong>
            <small>Identitas organisasi</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php else: ?>
    <a href="<?= url('profile') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-2"><i class="ph ph-identification-card"></i></div>
        <div class="dx-qa-copy">
            <strong>Profil Saya</strong>
            <small>Perbarui data pribadi</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <a href="<?= url('events') ?>" class="dx-qa">
        <div class="dx-qa-icon grad-3"><i class="ph ph-broadcast"></i></div>
        <div class="dx-qa-copy">
            <strong>Event Berlangsung</strong>
            <small>Lihat agenda hari ini</small>
        </div>
        <i class="ph ph-arrow-right dx-qa-arrow"></i>
    </a>
    <?php endif; ?>
</section>

<!-- ============ STATS ============ -->
<section class="dx-stats">
    <article class="dx-stat" style="--glow: rgba(99,102,241,.22); --bar: #8b5cf6;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-1"><i class="ph ph-users-three"></i></div>
            <span class="dx-trend"><i class="ph ph-trend-up"></i> Aktif</span>
        </div>
        <strong class="dx-stat-num" data-count="<?= $totalMembers ?>">0</strong>
        <span class="dx-stat-label">Total Anggota</span>
        <div class="dx-bars">
            <span style="height:35%"></span><span style="height:52%"></span>
            <span style="height:44%"></span><span style="height:68%"></span>
            <span style="height:58%"></span><span style="height:82%"></span>
            <span style="height:94%"></span>
        </div>
    </article>

    <article class="dx-stat" style="--glow: rgba(14,165,233,.22); --bar: #22d3ee;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-2"><i class="ph ph-user-circle"></i></div>
            <span class="dx-trend neutral"><i class="ph ph-minus"></i> Stabil</span>
        </div>
        <strong class="dx-stat-num" data-count="<?= $totalUsers ?>">0</strong>
        <span class="dx-stat-label">Total Pengguna</span>
        <div class="dx-bars">
            <span style="height:60%"></span><span style="height:62%"></span>
            <span style="height:58%"></span><span style="height:64%"></span>
            <span style="height:61%"></span><span style="height:63%"></span>
            <span style="height:62%"></span>
        </div>
    </article>

    <article class="dx-stat" style="--glow: rgba(16,185,129,.22); --bar: #10b981;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-3"><i class="ph ph-shield-check"></i></div>
            <span class="dx-trend"><i class="ph ph-trend-up"></i> <?= $activePct ?>%</span>
        </div>
        <strong class="dx-stat-num" data-count="<?= $activeUsers ?>">0</strong>
        <span class="dx-stat-label">Akun Aktif</span>
        <div class="dx-progress">
            <div class="dx-progress-fill" style="width: <?= $activePct ?>%"></div>
        </div>
        <span class="dx-progress-label"><?= $activePct ?>% dari total pengguna</span>
    </article>

    <article class="dx-stat" style="--glow: rgba(245,158,11,.22); --bar: #f59e0b;">
        <div class="dx-stat-head">
            <div class="dx-stat-icon grad-4"><i class="ph ph-calendar-blank"></i></div>
            <span class="dx-trend"><i class="ph ph-broadcast"></i> Live</span>
        </div>
        <strong class="dx-stat-num" data-count="<?= $eventThisMonth ?>">0</strong>
        <span class="dx-stat-label">Event Bulan Ini</span>
        <div class="dx-bars">
            <span style="height:30%"></span><span style="height:48%"></span>
            <span style="height:72%"></span><span style="height:56%"></span>
            <span style="height:84%"></span><span style="height:64%"></span>
            <span style="height:96%"></span>
        </div>
    </article>
</section>

<!-- ============ CHART ============ -->
<section class="glass-card dx-chart">
    <div class="dx-chart-head">
        <div>
            <h3>Pertumbuhan Anggota</h3>
            <p>Tren pendaftaran dalam 6 bulan terakhir</p>
        </div>
        <div class="dx-chart-right">
            <span class="dx-badge"><i class="ph ph-chart-line-up"></i> 6 bulan terakhir</span>
            <div class="dx-legend"><i></i><span>Pendaftar Baru</span></div>
        </div>
    </div>
    <div id="chartRegistrations" class="dx-chart-area"></div>
</section>

<!-- ============ FEED + SYSTEM ============ -->
<section class="dx-grid">
    <section class="dx-card">
        <div class="dx-card-head">
            <h3><i class="ph ph-pulse"></i> Aktivitas Terkini</h3>
            <span class="live-indicator"><span class="live-dot"></span> Real-time</span>
        </div>
        <?php if (!empty($feed)): ?>
        <ul class="dx-feed">
            <?php foreach ($feed as $f): ?>
            <li>
                <span class="dx-feed-icon <?= e($f['grad'] ?? 'grad-1') ?>">
                    <i class="ph <?= e($f['icon'] ?? 'ph-circle') ?>"></i>
                </span>
                <div class="dx-feed-body">
                    <strong><?= e($f['title']) ?></strong>
                    <span><?= e($f['sub'] ?? '') ?></span>
                </div>
                <span class="dx-feed-time"><?= e(time_ago($f['time'])) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <div class="dx-feed-empty">
            <i class="ph ph-clock-countdown"></i>
            Belum ada aktivitas tercatat. Mulai dengan menambahkan anggota, event, atau artikel pertama.
        </div>
        <?php endif; ?>
    </section>

    <section class="dx-card">
        <div class="dx-card-head">
            <h3><i class="ph ph-cpu"></i> Kesehatan Sistem</h3>
            <span class="live-indicator"><span class="live-dot"></span> Normal</span>
        </div>
        <div class="dx-sys">
            <div class="dx-sys-item">
                <span><i class="ph ph-code"></i> Versi Aplikasi</span>
                <strong>v5.9 ULTIMATE</strong>
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
                <strong class="dx-sys-ok"><i class="ph ph-check-circle"></i> Aktif & Aman</strong>
            </div>
        </div>
    </section>
</section>

<!-- ============ KEYBOARD BANNER ============ -->
<div class="dx-kbd">
    <span><kbd>⌘</kbd><kbd>K</kbd> Pencarian Cepat</span>
    <span><kbd>⌘</kbd><kbd>/</kbd> Buka Pintasan</span>
    <span><kbd>Esc</kbd> Tutup Modal</span>
    <span><kbd>↵</kbd> Konfirmasi</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function(){
    // ---- Counter animasi ----
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (!en.isIntersecting) return;
            const el = en.target;
            const target = parseInt(el.dataset.count, 10) || 0;
            const t0 = performance.now(), dur = 1400;
            const tick = (t) => {
                const p = Math.min(1, (t - t0) / dur);
                el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target.toLocaleString('id-ID');
            };
            requestAnimationFrame(tick);
            obs.unobserve(el);
        });
    }, { threshold: 0.4 });
    document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));

    // ---- Chart ApexCharts ----
    const chartEl = document.getElementById('chartRegistrations');
    if (chartEl && window.ApexCharts) {
        fetch('<?= url('api/stats/registrations') ?>')
            .then(r => r.json())
            .then(d => {
                new ApexCharts(chartEl, {
                    chart: {
                        type: 'area', height: 320,
                        toolbar: { show: false },
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        animations: { easing: 'easeinout', speed: 900 }
                    },
                    series: [{ name: 'Anggota Baru', data: d.totals || [] }],
                    xaxis: {
                        categories: d.labels || [],
                        labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } },
                        forceIntegers: true
                    },
                    colors: ['#22d3ee'],
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, stops: [0, 90, 100] }
                    },
                    grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                    markers: { size: 5, colors: ['#22d3ee'], strokeColors: '#0b1020', strokeWidth: 2, hover: { size: 8 } },
                    dataLabels: { enabled: false },
                    tooltip: {
                        theme: 'dark',
                        style: { fontSize: '12px' },
                        y: { formatter: (v) => v + ' anggota' }
                    },
                    theme: { mode: 'dark' }
                }).render();
            })
            .catch(() => {
                chartEl.innerHTML = '<div style="text-align:center;padding:60px 20px;color:var(--txt-2)"><i class="ph ph-chart-line-down" style="font-size:36px;display:block;margin-bottom:10px"></i>Gagal memuat data grafik.</div>';
            });
    }
})();
</script>