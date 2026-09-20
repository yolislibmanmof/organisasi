<?php
/**
 * ============================================================
 * HALAMAN EVENT PUBLIK — ULTIMATE EDITION v7.0
 * Daftar kegiatan dengan filter animasi, countdown visual,
 * year-grouped archive, dan "Add to Calendar" integration.
 * ============================================================
 */

$active = $active ?? [];
$done   = $done ?? [];
$totalEvents = count($active) + count($done);
$upcomingCount = count(array_filter($active, fn($e) => $e['status'] === 'upcoming'));
$ongoingCount  = count(array_filter($active, fn($e) => $e['status'] === 'ongoing'));

/* ---- Group archive by year ---- */
$doneByYear = [];
foreach ($done as $ev) {
    $year = date('Y', strtotime($ev['event_date']));
    $doneByYear[$year][] = $ev;
}
krsort($doneByYear); // Newest year first

/* ---- Helper: Google Calendar link ---- */
$gcalLink = function($ev) {
    $start = date('Ymd', strtotime($ev['event_date'])) . 'T' . 
             str_replace(':', '', substr($ev['event_time'] ?? '09:00', 0, 5)) . '00';
    $end   = date('Ymd', strtotime($ev['event_date'])) . 'T' . 
             str_replace(':', '', substr($ev['event_time'] ?? '10:00', 0, 5)) . '00';
    $params = http_build_query([
        'action'   => 'TEMPLATE',
        'text'     => $ev['title'],
        'dates'    => $start . '/' . $end,
        'details'  => ($ev['description'] ?? '') . "\n\nLokasi: " . ($ev['location'] ?? '-'),
        'location' => $ev['location'] ?? '',
        'trp'      => 'false',
    ]);
    return 'https://calendar.google.com/calendar/render?' . $params;
};
?>

<!-- ========== HERO SECTION ========== -->
<section class="event-hero">
    <!-- Decorative orbs -->
    <div class="event-orbs" aria-hidden="true">
        <span class="event-orb orb-1"></span>
        <span class="event-orb orb-2"></span>
        <span class="event-orb orb-3"></span>
    </div>

    <div class="event-hero-content reveal">
        <span class="page-eyebrow">
            <?php if ($ongoingCount > 0): ?>
                <span class="live-dot"></span>
                <?= $ongoingCount ?> Live Sekarang
            <?php else: ?>
                <i class="ph ph-calendar-star"></i>
                Agenda & Kegiatan
            <?php endif; ?>
        </span>
        <h1 class="event-hero-title">Event & Kegiatan</h1>
        <p class="event-hero-sub">
            Seluruh kegiatan organisasi — yang sedang berlangsung, mendatang, 
            serta arsip kegiatan yang telah terlaksana.
        </p>
        
        <!-- Quick stats (animated) -->
        <div class="event-hero-stats">
            <div class="event-stat stat-upcoming">
                <div class="event-stat-icon"><i class="ph ph-calendar-plus"></i></div>
                <div class="event-stat-info">
                    <strong data-count="<?= $upcomingCount ?>">0</strong>
                    <span>Akan Datang</span>
                </div>
            </div>
            <div class="event-stat stat-ongoing">
                <div class="event-stat-icon"><i class="ph ph-broadcast"></i></div>
                <div class="event-stat-info">
                    <strong data-count="<?= $ongoingCount ?>">0</strong>
                    <span>Berlangsung</span>
                </div>
            </div>
            <div class="event-stat stat-done">
                <div class="event-stat-icon"><i class="ph ph-check-circle"></i></div>
                <div class="event-stat-info">
                    <strong data-count="<?= count($done) ?>">0</strong>
                    <span>Selesai</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== MAIN CONTENT ========== -->
<div class="event-container">

    <!-- ===== Active Events (Ongoing + Upcoming) ===== -->
    <?php if (!empty($active)): ?>
    <section class="event-section reveal">
        <div class="event-section-head">
            <div class="event-section-title">
                <span class="event-section-icon">
                    <i class="ph ph-calendar-star"></i>
                </span>
                <div>
                    <h2>Sedang Berlangsung & Mendatang</h2>
                    <p><?= count($active) ?> kegiatan yang sedang atau akan berlangsung</p>
                </div>
            </div>
            <div class="event-section-filter" role="tablist" aria-label="Filter event aktif">
                <button class="event-filter-btn active" data-filter="all" role="tab" aria-selected="true">
                    <i class="ph ph-list"></i>
                    Semua
                    <span class="filter-count"><?= count($active) ?></span>
                </button>
                <?php if ($ongoingCount > 0): ?>
                <button class="event-filter-btn" data-filter="ongoing" role="tab" aria-selected="false">
                    <i class="ph ph-broadcast"></i>
                    Berlangsung
                    <span class="filter-count count-ongoing"><?= $ongoingCount ?></span>
                </button>
                <?php endif; ?>
                <?php if ($upcomingCount > 0): ?>
                <button class="event-filter-btn" data-filter="upcoming" role="tab" aria-selected="false">
                    <i class="ph ph-calendar-plus"></i>
                    Akan Datang
                    <span class="filter-count"><?= $upcomingCount ?></span>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="pub-events" id="activeEvents">
            <?php foreach ($active as $ev): 
                $isOngoing = $ev['status'] === 'ongoing';
                $eventDate = strtotime($ev['event_date']);
                $daysUntil = max(0, ceil(($eventDate - time()) / 86400));
                $countdownPct = min(100, max(0, (30 - $daysUntil) / 30 * 100));
            ?>
            <article class="glass-card pub-event-card <?= $isOngoing ? 'is-ongoing' : '' ?>" data-status="<?= $ev['status'] ?>">
                <!-- Accent stripe untuk ongoing -->
                <?php if ($isOngoing): ?>
                <div class="pec-accent-stripe"></div>
                <?php endif; ?>

                <!-- Date badge -->
                <div class="pec-date <?= $isOngoing ? 'date-ongoing' : '' ?>">
                    <strong><?= date('d', $eventDate) ?></strong>
                    <span><?= date('M', $eventDate) ?></span>
                    <small><?= date('Y', $eventDate) ?></small>
                    <?php if ($isOngoing): ?>
                    <span class="pec-date-live"><i class="ph ph-broadcast"></i> LIVE</span>
                    <?php endif; ?>
                </div>
                
                <!-- Content -->
                <div class="pec-body">
                    <div class="pec-head">
                        <h3><?= e($ev['title']) ?></h3>
                        <span class="event-pill <?= e($ev['status']) ?>">
                            <i class="ph <?= $isOngoing ? 'ph-broadcast' : 'ph-calendar-plus' ?>"></i>
                            <?= $isOngoing ? 'Berlangsung' : 'Akan Datang' ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($ev['description'])): ?>
                    <p class="pec-desc"><?= e(mb_substr($ev['description'], 0, 120)) ?><?= mb_strlen($ev['description']) > 120 ? '...' : '' ?></p>
                    <?php endif; ?>
                    
                    <div class="event-meta">
                        <span>
                            <i class="ph ph-clock"></i>
                            <?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : 'Waktu menyusul' ?>
                        </span>
                        <span>
                            <i class="ph ph-map-pin"></i>
                            <?= e($ev['location'] ?: 'Lokasi menyusul') ?>
                        </span>
                    </div>
                    
                    <!-- Countdown visual (upcoming only) -->
                    <?php if (!$isOngoing && $daysUntil > 0 && $daysUntil <= 30): ?>
                    <div class="pec-countdown">
                        <div class="pec-countdown-ring" style="--pct: <?= $countdownPct ?>%">
                            <svg viewBox="0 0 36 36">
                                <circle class="ring-bg" cx="18" cy="18" r="15.9" />
                                <circle class="ring-fg" cx="18" cy="18" r="15.9" />
                            </svg>
                        </div>
                        <div class="pec-countdown-text">
                            <strong>
                                <?php if ($daysUntil === 1): ?>
                                    Besok
                                <?php elseif ($daysUntil <= 7): ?>
                                    <?= $daysUntil ?> hari lagi
                                <?php else: ?>
                                    <?= floor($daysUntil / 7) ?> minggu lagi
                                <?php endif; ?>
                            </strong>
                            <span>Menuju hari H</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Ongoing live indicator -->
                    <?php if ($isOngoing): ?>
                    <div class="pec-live">
                        <span class="pec-live-dot"></span>
                        <span>SEDANG BERLANGSUNG SEKARANG</span>
                    </div>
                    <?php endif; ?>

                    <!-- Action buttons -->
                    <div class="pec-actions">
                        <a href="<?= e($gcalLink($ev)) ?>" target="_blank" rel="noopener" class="pec-action-btn" title="Tambah ke Google Calendar">
                            <i class="ph ph-calendar-plus"></i>
                            <span>Ingatkan Saya</span>
                        </a>
                        <?php if (!empty($ev['location'])): ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($ev['location']) ?>" target="_blank" rel="noopener" class="pec-action-btn" title="Buka di Maps">
                            <i class="ph ph-map-trifold"></i>
                            <span>Peta</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php else: ?>
    <!-- Empty state for active events -->
    <section class="event-section reveal">
        <div class="event-empty-card glass-card">
            <div class="event-empty-icon">
                <i class="ph ph-calendar-blank"></i>
                <span class="event-empty-spark"></span>
            </div>
            <h3>Belum Ada Event Aktif</h3>
            <p>Saat ini belum ada event yang sedang berlangsung atau akan datang. Pantau terus halaman ini untuk update terbaru!</p>
            <div class="event-empty-actions">
                <?php if (!empty($done)): ?>
                <a href="#archive" class="btn btn-ghost btn-sm" data-scroll>
                    <i class="ph ph-archive"></i>
                    <span class="btn-text">Lihat Arsip Kegiatan</span>
                </a>
                <?php endif; ?>
                <a href="<?= url('') ?>#kontak" class="btn btn-primary btn-sm" data-scroll>
                    <i class="ph ph-envelope-simple"></i>
                    <span class="btn-text">Hubungi Kami</span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Archive Events (Grouped by Year) ===== -->
    <?php if (!empty($doneByYear)): ?>
    <section class="event-section event-section-archive reveal" id="archive">
        <div class="event-section-head">
            <div class="event-section-title">
                <span class="event-section-icon archive">
                    <i class="ph ph-archive"></i>
                </span>
                <div>
                    <h2>Arsip Kegiatan Lalu</h2>
                    <p><?= count($done) ?> kegiatan yang telah terlaksana</p>
                </div>
            </div>
            <div class="archive-year-tabs" role="tablist" aria-label="Filter tahun arsip">
                <?php $first = true; foreach ($doneByYear as $year => $_): ?>
                <button class="year-tab <?= $first ? 'active' : '' ?>" data-year="<?= $year ?>" role="tab" aria-selected="<?= $first ? 'true' : 'false' ?>">
                    <?= $year ?>
                    <span class="year-count"><?= count($doneByYear[$year]) ?></span>
                </button>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
        
        <?php $first = true; foreach ($doneByYear as $year => $yearEvents): ?>
        <div class="pub-events pub-events-archive archive-year-panel" data-year="<?= $year ?>" <?= $first ? '' : 'style="display:none"' ?>>
            <?php foreach ($yearEvents as $ev): ?>
            <article class="glass-card pub-event-card is-done">
                <div class="pec-date done">
                    <strong><?= date('d', strtotime($ev['event_date'])) ?></strong>
                    <span><?= date('M', strtotime($ev['event_date'])) ?></span>
                    <small><?= date('Y', strtotime($ev['event_date'])) ?></small>
                </div>
                <div class="pec-body">
                    <div class="pec-head">
                        <h3><?= e($ev['title']) ?></h3>
                        <span class="event-pill done">
                            <i class="ph ph-check-circle"></i>
                            Selesai
                        </span>
                    </div>
                    <?php if (!empty($ev['description'])): ?>
                    <p class="pec-desc"><?= e(mb_substr($ev['description'], 0, 100)) ?><?= mb_strlen($ev['description']) > 100 ? '...' : '' ?></p>
                    <?php endif; ?>
                    <div class="event-meta">
                        <span>
                            <i class="ph ph-clock"></i>
                            <?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : '-' ?>
                        </span>
                        <span>
                            <i class="ph ph-map-pin"></i>
                            <?= e($ev['location'] ?: '-') ?>
                        </span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php $first = false; endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- ===== Total Empty ===== -->
    <?php if (empty($active) && empty($done)): ?>
    <section class="event-section reveal">
        <div class="event-empty-card glass-card event-empty-full">
            <div class="event-empty-icon large">
                <i class="ph ph-calendar-x"></i>
                <span class="event-empty-spark"></span>
            </div>
            <h3>Belum Ada Event</h3>
            <p>Organisasi belum memiliki event yang terjadwal. Hubungi kami untuk informasi lebih lanjut atau jadilah yang pertama mengusulkan kegiatan.</p>
            <div class="event-empty-actions">
                <a href="<?= url('sensus') ?>" class="btn btn-primary">
                    <i class="ph ph-user-plus"></i>
                    <span class="btn-text">Daftar Sebagai Anggota</span>
                </a>
                <a href="<?= url('') ?>#kontak" class="btn btn-ghost" data-scroll>
                    <i class="ph ph-envelope-simple"></i>
                    <span class="btn-text">Hubungi Kami</span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Event Hero ---- */
.event-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 880px;
    margin: 0 auto;
    overflow: hidden;
}
.event-hero-content { position: relative; z-index: 1; }

/* Decorative orbs */
.event-orbs {
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
}
.event-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: .25;
}
.event-orb.orb-1 {
    width: 480px; height: 480px;
    background: #6366f1;
    top: -100px; left: -120px;
    animation: eventOrb 22s ease-in-out infinite alternate;
}
.event-orb.orb-2 {
    width: 380px; height: 380px;
    background: #22d3ee;
    bottom: -80px; right: -100px;
    animation: eventOrb 26s ease-in-out infinite alternate-reverse;
}
.event-orb.orb-3 {
    width: 280px; height: 280px;
    background: #8b5cf6;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: eventOrb 30s ease-in-out infinite;
}
@keyframes eventOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.15); }
}

.event-hero-title {
    font-size: clamp(36px, 5vw, 56px);
    font-weight: 800;
    letter-spacing: -1.5px;
    line-height: 1.1;
    margin: 16px 0 20px;
    background: linear-gradient(180deg, #ffffff 0%, #c7d2fe 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.event-hero-sub {
    color: var(--txt-1);
    font-size: 16px;
    line-height: 1.7;
    max-width: 52ch;
    margin: 0 auto 36px;
}

/* Live dot */
.live-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16,185,129,.7);
    animation: live-pulse 2s infinite;
    margin-right: 4px;
}
@keyframes live-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.7); }
    70%  { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}

/* ---- Hero Stats ---- */
.event-hero-stats {
    display: flex;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
}
.event-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}
.event-stat::before {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: var(--stat-glow, rgba(99,102,241,.2));
    pointer-events: none;
    transition: transform .4s;
}
.event-stat:hover::before { transform: scale(1.4); }
.event-stat:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 14px 34px rgba(2,6,23,.35);
}
.stat-upcoming { --stat-glow: rgba(34,211,238,.25); }
.stat-ongoing  { --stat-glow: rgba(245,158,11,.25); }
.stat-done     { --stat-glow: rgba(16,185,129,.25); }

.event-stat-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: grid; place-items: center;
    font-size: 20px; color: #fff;
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.stat-upcoming .event-stat-icon { background: linear-gradient(135deg, #22d3ee, #06b6d4); }
.stat-ongoing  .event-stat-icon { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-done     .event-stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }

.event-stat-info { position: relative; z-index: 1; }
.event-stat-info strong {
    display: block;
    font-size: 24px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
}
.event-stat-info span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-1);
    margin-top: 2px;
}

/* ---- Container ---- */
.event-container {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px 80px;
    display: flex;
    flex-direction: column;
    gap: 48px;
}

/* ---- Section Head ---- */
.event-section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.event-section-title {
    display: flex;
    align-items: center;
    gap: 16px;
}
.event-section-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
    flex-shrink: 0;
}
.event-section-icon.archive {
    background: linear-gradient(135deg, #6b7394, #9aa3c7);
    box-shadow: 0 8px 20px rgba(107,115,148,.3);
}
.event-section-title h2 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 4px;
    letter-spacing: -.4px;
}
.event-section-title p {
    font-size: 13px;
    color: var(--txt-1);
}

/* ---- Filter Buttons with Badge Count ---- */
.event-section-filter {
    display: flex;
    gap: 6px;
    padding: 4px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.event-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.event-filter-btn i { font-size: 13px; }
.event-filter-btn:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.event-filter-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.filter-count {
    min-width: 20px;
    padding: 1px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.event-filter-btn.active .filter-count {
    background: rgba(255,255,255,.25);
}
.filter-count.count-ongoing {
    background: rgba(245,158,11,.25);
    color: #fcd34d;
}
.event-filter-btn.active .filter-count.count-ongoing {
    background: rgba(255,255,255,.25);
    color: #fff;
}

/* ---- Archive Year Tabs ---- */
.archive-year-tabs {
    display: flex;
    gap: 6px;
    padding: 4px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.year-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-variant-numeric: tabular-nums;
}
.year-tab:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.year-tab.active {
    background: linear-gradient(135deg, #6b7394, #9aa3c7);
    color: #fff;
    box-shadow: 0 4px 12px rgba(107,115,148,.3);
}
.year-count {
    min-width: 20px;
    padding: 1px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.year-tab.active .year-count {
    background: rgba(255,255,255,.25);
}

/* ---- Event Cards ---- */
.pub-events {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.pub-event-card {
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.35s cubic-bezier(.22,1,.36,1);
    position: relative;
}
.pub-event-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 26px 60px rgba(2,6,23,.5);
}

/* Accent stripe untuk ongoing */
.pec-accent-stripe {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--warn), #f97316, var(--warn));
    background-size: 200% 100%;
    animation: shimmer-stripe 3s linear infinite;
    z-index: 2;
}
@keyframes shimmer-stripe {
    0% { background-position: 0% 0; }
    100% { background-position: 200% 0; }
}

.pub-event-card.is-ongoing {
    border-color: rgba(245,158,11,.35);
    background:
        radial-gradient(600px 200px at 100% 0%, rgba(245,158,11,.08), transparent 60%),
        var(--glass);
}
.pub-event-card.is-ongoing:hover {
    border-color: rgba(245,158,11,.55);
    box-shadow: 0 30px 70px rgba(245,158,11,.2), 0 20px 50px rgba(2,6,23,.5);
}

/* Hover accent bar (upcoming) */
.pub-event-card:not(.is-ongoing):not(.is-done)::after {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 0;
    background: linear-gradient(180deg, var(--pri), var(--acc));
    transition: width .3s;
}
.pub-event-card:not(.is-ongoing):not(.is-done):hover::after {
    width: 3px;
}

.pub-event-card.is-done {
    opacity: 0.75;
}
.pub-event-card.is-done:hover {
    opacity: 1;
}

.pec-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 22px 20px;
    background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(34,211,238,.2));
    border-bottom: 1px solid var(--glass-brd);
    min-width: 90px;
    position: relative;
}
.pec-date.date-ongoing {
    background: linear-gradient(135deg, rgba(245,158,11,.35), rgba(249,115,22,.25));
}
.pec-date.done {
    background: linear-gradient(135deg, rgba(107,115,148,.3), rgba(154,163,199,.2));
}
.pec-date strong {
    font-size: 36px;
    font-weight: 800;
    line-height: 1;
    font-variant-numeric: tabular-nums;
    letter-spacing: -1px;
}
.pec-date span {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #c7d2fe;
    margin-top: 4px;
}
.pec-date.date-ongoing span { color: #fde68a; }
.pec-date small {
    font-size: 11px;
    color: var(--txt-2);
    margin-top: 2px;
}
.pec-date-live {
    position: absolute;
    top: 8px; right: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(0,0,0,.35);
    backdrop-filter: blur(6px);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #fcd34d;
}
.pec-date-live i { font-size: 10px; }

.pec-body {
    padding: 22px;
    flex: 1;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}
.pec-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
}
.pec-head h3 {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.35;
    flex: 1;
    transition: color .3s;
}
.pub-event-card:hover .pec-head h3 { color: var(--acc); }

.event-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 5px 11px;
    border-radius: 99px;
    border: 1px solid;
    white-space: nowrap;
    flex-shrink: 0;
}
.event-pill i { font-size: 11px; }
.event-pill.upcoming {
    color: #67e8f9;
    background: rgba(34,211,238,.12);
    border-color: rgba(34,211,238,.3);
}
.event-pill.ongoing {
    color: #fcd34d;
    background: rgba(245,158,11,.12);
    border-color: rgba(245,158,11,.35);
    animation: pulse-soft 2s infinite;
}
@keyframes pulse-soft {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,.4); }
    50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}
.event-pill.done {
    color: var(--txt-1);
    background: rgba(154,163,199,.08);
    border-color: rgba(154,163,199,.25);
}

.pec-desc {
    color: var(--txt-1);
    font-size: 13px;
    line-height: 1.65;
    margin-bottom: 12px;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    padding: 12px 0;
}
.event-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.event-meta i {
    color: var(--acc);
    font-size: 14px;
}

/* ---- Countdown with Ring Progress ---- */
.pec-countdown {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 12px;
    background:
        linear-gradient(135deg, rgba(99,102,241,.1), rgba(34,211,238,.06));
    border: 1px solid rgba(99,102,241,.2);
}
.pec-countdown-ring {
    width: 44px; height: 44px;
    position: relative;
    flex-shrink: 0;
}
.pec-countdown-ring svg {
    width: 100%; height: 100%;
    transform: rotate(-90deg);
}
.pec-countdown-ring circle {
    fill: none;
    stroke-width: 3.5;
}
.ring-bg {
    stroke: rgba(255,255,255,.08);
}
.ring-fg {
    stroke: url(#ringGrad);
    stroke: var(--acc);
    stroke-linecap: round;
    stroke-dasharray: 100;
    stroke-dashoffset: calc(100 - var(--pct));
    transition: stroke-dashoffset 1s cubic-bezier(.22,1,.36,1);
    filter: drop-shadow(0 0 4px rgba(34,211,238,.5));
}
.pec-countdown-text {
    flex: 1;
    min-width: 0;
}
.pec-countdown-text strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
    color: #a5b4fc;
    line-height: 1.2;
}
.pec-countdown-text span {
    display: block;
    font-size: 10.5px;
    color: var(--txt-2);
    margin-top: 2px;
    font-weight: 600;
}

/* ---- Live indicator ---- */
.pec-live {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    padding: 11px 14px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(245,158,11,.12), rgba(249,115,22,.08));
    border: 1px solid rgba(245,158,11,.35);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.2px;
    color: #fcd34d;
}
.pec-live-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--warn);
    box-shadow: 0 0 0 0 rgba(245,158,11,.7);
    animation: pulse-dot 1.5s infinite;
    flex-shrink: 0;
}
@keyframes pulse-dot {
    0%   { box-shadow: 0 0 0 0 rgba(245,158,11,.7); }
    70%  { box-shadow: 0 0 0 10px rgba(245,158,11,0); }
    100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
}

/* ---- Action Buttons ---- */
.pec-actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid var(--glass-brd);
}
.pec-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 9px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s;
}
.pec-action-btn i { font-size: 14px; color: var(--acc); }
.pec-action-btn:hover {
    background: rgba(99,102,241,.12);
    border-color: rgba(99,102,241,.35);
    color: var(--acc);
    transform: translateY(-1px);
}

/* ---- Empty States ---- */
.event-empty-card {
    text-align: center;
    padding: 60px 40px;
}
.event-empty-icon {
    position: relative;
    width: 80px; height: 80px;
    margin: 0 auto 20px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid; place-items: center;
    font-size: 36px; color: var(--acc);
}
.event-empty-icon.large {
    width: 100px; height: 100px;
    font-size: 44px;
}
.event-empty-spark {
    position: absolute;
    inset: -10px;
    border-radius: 34px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(12px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.event-empty-card h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -.4px;
}
.event-empty-card p {
    color: var(--txt-1);
    font-size: 14px;
    max-width: 44ch;
    margin: 0 auto 24px;
    line-height: 1.65;
}
.event-empty-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

/* ---- Archive Section ---- */
.archive-year-panel {
    animation: fade-up 0.4s both;
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .event-section-head { flex-direction: column; align-items: stretch; }
    .event-section-filter, .archive-year-tabs { overflow-x: auto; }
}
@media (max-width: 768px) {
    .event-hero { padding-top: 120px; }
    .event-hero-stats { gap: 10px; }
    .event-stat { padding: 12px 16px; gap: 10px; }
    .event-stat-icon { width: 38px; height: 38px; font-size: 17px; }
    .event-stat-info strong { font-size: 20px; }
    .pub-events { grid-template-columns: 1fr; }
    .pub-event-card { flex-direction: row; }
    .pec-date { min-width: 85px; padding: 18px 12px; }
    .pec-date strong { font-size: 28px; }
    .pec-date span { font-size: 11px; }
    .pec-date small { display: none; }
    .pec-date-live { font-size: 8px; padding: 2px 6px; }
    .event-orb { filter: blur(80px); opacity: .18; }
}
@media (max-width: 520px) {
    .event-stat-icon { display: none; }
    .event-stat { padding: 12px 14px; }
    .pec-actions { flex-direction: column; }
    .pec-action-btn { justify-content: center; }
}
</style>

<!-- SVG gradient untuk ring countdown -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs>
        <linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#6366f1"/>
            <stop offset="100%" stop-color="#22d3ee"/>
        </linearGradient>
    </defs>
</svg>

<script>
(function(){
    'use strict';

    /* ---- Counter animation untuk stats hero ---- */
    const counters = document.querySelectorAll('.event-stat-info strong[data-count]');
    if (counters.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (!en.isIntersecting) return;
                const el = en.target;
                const target = parseInt(el.dataset.count, 10) || 0;
                const t0 = performance.now(), dur = 1200;
                const tick = (t) => {
                    const p = Math.min(1, (t - t0) / dur);
                    const v = Math.floor(target * (1 - Math.pow(1 - p, 3)));
                    el.textContent = v.toLocaleString('id-ID');
                    if (p < 1) requestAnimationFrame(tick);
                    else el.textContent = target.toLocaleString('id-ID');
                };
                requestAnimationFrame(tick);
                obs.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(c => obs.observe(c));
    }

    /* ---- Active events filter ---- */
    const filterBtns = document.querySelectorAll('.event-filter-btn');
    const cards = document.querySelectorAll('#activeEvents .pub-event-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            
            filterBtns.forEach(b => {
                const on = (b === btn);
                b.classList.toggle('active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            
            cards.forEach((card, i) => {
                const status = card.dataset.status;
                const show = filter === 'all' || status === filter;
                card.style.display = show ? '' : 'none';
                if (show) {
                    card.style.animation = 'none';
                    card.offsetHeight; // trigger reflow
                    card.style.animation = `fade-up 0.4s ${i * 0.04}s both`;
                }
            });
        });
    });

    /* ---- Archive year tabs ---- */
    const yearTabs = document.querySelectorAll('.year-tab');
    const yearPanels = document.querySelectorAll('.archive-year-panel');
    
    yearTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const year = tab.dataset.year;
            yearTabs.forEach(t => {
                const on = (t === tab);
                t.classList.toggle('active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            yearPanels.forEach(p => {
                p.style.display = p.dataset.year === year ? '' : 'none';
            });
        });
    });
})();
</script>