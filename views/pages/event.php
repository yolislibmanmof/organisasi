<?php
/**
 * ============================================================
 * HALAMAN EVENT PUBLIK — ULTIMATE EDITION v5.9
 * Daftar kegiatan dengan filter, countdown, dan visual
 * hierarchy yang engaging untuk pengunjung.
 * ============================================================
 */

$active = $active ?? [];
$done   = $done ?? [];
$totalEvents = count($active) + count($done);
$upcomingCount = count(array_filter($active, fn($e) => $e['status'] === 'upcoming'));
$ongoingCount  = count(array_filter($active, fn($e) => $e['status'] === 'ongoing'));
?>

<!-- ========== HERO SECTION ========== -->
<section class="event-hero">
    <div class="event-hero-content reveal">
        <span class="page-eyebrow">Agenda & Kegiatan</span>
        <h1 class="event-hero-title">Event & Kegiatan</h1>
        <p class="event-hero-sub">
            Seluruh kegiatan organisasi — yang sedang berlangsung, mendatang, 
            serta arsip kegiatan yang telah terlaksana.
        </p>
        
        <!-- Quick stats -->
        <div class="event-hero-stats">
            <div class="event-stat">
                <i class="ph ph-calendar-plus"></i>
                <div>
                    <strong><?= $upcomingCount ?></strong>
                    <span>Akan Datang</span>
                </div>
            </div>
            <div class="event-stat">
                <i class="ph ph-broadcast"></i>
                <div>
                    <strong><?= $ongoingCount ?></strong>
                    <span>Berlangsung</span>
                </div>
            </div>
            <div class="event-stat">
                <i class="ph ph-check-circle"></i>
                <div>
                    <strong><?= count($done) ?></strong>
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
            <div class="event-section-filter">
                <button class="event-filter-btn active" data-filter="all">Semua</button>
                <?php if ($ongoingCount > 0): ?>
                <button class="event-filter-btn" data-filter="ongoing">Berlangsung</button>
                <?php endif; ?>
                <?php if ($upcomingCount > 0): ?>
                <button class="event-filter-btn" data-filter="upcoming">Akan Datang</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="pub-events" id="activeEvents">
            <?php foreach ($active as $ev): 
                $isOngoing = $ev['status'] === 'ongoing';
                $eventDate = strtotime($ev['event_date']);
                $daysUntil = ceil(($eventDate - time()) / 86400);
            ?>
            <article class="glass-card pub-event-card <?= $isOngoing ? 'is-ongoing' : '' ?>" data-status="<?= $ev['status'] ?>">
                <!-- Date badge -->
                <div class="pec-date">
                    <strong><?= date('d', $eventDate) ?></strong>
                    <span><?= date('M', $eventDate) ?></span>
                    <small><?= date('Y', $eventDate) ?></small>
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
                    
                    <!-- Countdown / Status -->
                    <?php if (!$isOngoing && $daysUntil > 0 && $daysUntil <= 30): ?>
                    <div class="pec-countdown">
                        <i class="ph ph-timer"></i>
                        <span>
                            <?php if ($daysUntil === 1): ?>
                                Besok
                            <?php elseif ($daysUntil <= 7): ?>
                                <?= $daysUntil ?> hari lagi
                            <?php else: ?>
                                <?= floor($daysUntil / 7) ?> minggu lagi
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($isOngoing): ?>
                    <div class="pec-live">
                        <span class="pec-live-dot"></span>
                        <span>SEDANG BERLANGSUNG</span>
                    </div>
                    <?php endif; ?>
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
            </div>
            <h3>Belum Ada Event Aktif</h3>
            <p>Saat ini belum ada event yang sedang berlangsung atau akan datang. Pantau terus halaman ini untuk update terbaru!</p>
            <a href="<?= url('sensus') ?>" class="btn btn-primary btn-sm">
                <i class="ph ph-user-plus"></i>
                <span class="btn-text">Daftar Sebagai Anggota</span>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Archive Events ===== -->
    <?php if (!empty($done)): ?>
    <section class="event-section event-section-archive reveal">
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
        </div>
        
        <div class="pub-events pub-events-archive">
            <?php foreach ($done as $ev): ?>
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
            <p>Organisasi belum memiliki event yang terjadwal. Hubungi kami untuk informasi lebih lanjut.</p>
            <a href="<?= url('') ?>#kontak" class="btn btn-ghost" data-scroll>
                <i class="ph ph-envelope-simple"></i>
                <span class="btn-text">Hubungi Kami</span>
            </a>
        </div>
    </section>
    <?php endif; ?>

</div>

<!-- ========== STYLING ========== -->
<style>
/* ---- Event Hero ---- */
.event-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 800px;
    margin: 0 auto;
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
    margin: 0 auto 32px;
}

/* ---- Hero Stats ---- */
.event-hero-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}
.event-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
}
.event-stat:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 12px 30px rgba(2,6,23,.3);
}
.event-stat i {
    font-size: 22px;
    color: var(--acc);
}
.event-stat strong {
    display: block;
    font-size: 22px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.event-stat span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-1);
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

/* ---- Section ---- */
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
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 24px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
}
.event-section-icon.archive {
    background: linear-gradient(135deg, #6b7394, #9aa3c7);
}
.event-section-title h2 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 4px;
}
.event-section-title p {
    font-size: 13px;
    color: var(--txt-1);
}

/* ---- Filter Buttons ---- */
.event-section-filter {
    display: flex;
    gap: 6px;
    padding: 4px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.event-filter-btn {
    padding: 8px 16px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.event-filter-btn:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.event-filter-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}

/* ---- Event Cards ---- */
.pub-events {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.pub-events-archive {
    opacity: 0.8;
}

.pub-event-card {
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    position: relative;
}
.pub-event-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 26px 60px rgba(2,6,23,.5);
}
.pub-event-card.is-ongoing {
    border-color: rgba(245,158,11,.4);
}
.pub-event-card.is-ongoing::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--warn), #f97316);
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
    padding: 20px;
    background: linear-gradient(135deg, rgba(99,102,241,.3), rgba(34,211,238,.2));
    border-bottom: 1px solid var(--glass-brd);
    min-width: 90px;
}
.pec-date.done {
    background: linear-gradient(135deg, rgba(107,115,148,.3), rgba(154,163,199,.2));
}
.pec-date strong {
    font-size: 34px;
    font-weight: 800;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.pec-date span {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #c7d2fe;
    margin-top: 4px;
}
.pec-date small {
    font-size: 11px;
    color: var(--txt-2);
    margin-top: 2px;
}

.pec-body {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
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
}

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
.event-pill.done {
    color: var(--txt-1);
    background: rgba(154,163,199,.08);
    border-color: rgba(154,163,199,.25);
}

.pec-desc {
    color: var(--txt-1);
    font-size: 13px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    margin-top: auto;
    padding-top: 12px;
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

/* ---- Countdown ---- */
.pec-countdown {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    padding: 8px 14px;
    border-radius: 10px;
    background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.2);
    font-size: 12px;
    font-weight: 700;
    color: #a5b4fc;
}
.pec-countdown i {
    font-size: 15px;
    color: var(--acc);
}

/* ---- Live indicator ---- */
.pec-live {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(245,158,11,.1);
    border: 1px solid rgba(245,158,11,.3);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    color: #fcd34d;
}
.pec-live-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--warn);
    animation: pulse-dot 1.5s infinite;
}

/* ---- Empty States ---- */
.event-empty-card {
    text-align: center;
    padding: 60px 40px;
}
.event-empty-icon {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid;
    place-items: center;
    font-size: 36px;
    color: var(--acc);
}
.event-empty-icon.large {
    width: 100px;
    height: 100px;
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
}
.event-empty-card p {
    color: var(--txt-1);
    font-size: 14px;
    max-width: 40ch;
    margin: 0 auto 24px;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .event-hero { padding-top: 120px; }
    .event-hero-stats { gap: 12px; }
    .event-stat { padding: 12px 18px; }
    .event-stat strong { font-size: 18px; }
    .event-section-head { flex-direction: column; }
    .pub-events { grid-template-columns: 1fr; }
    .pub-event-card { flex-direction: row; }
    .pec-date { min-width: 80px; }
    .pec-date strong { font-size: 28px; }
}
</style>

<script>
/* ---- Event filter ---- */
(() => {
    const filterBtns = document.querySelectorAll('.event-filter-btn');
    const cards = document.querySelectorAll('#activeEvents .pub-event-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;
            
            // Update active state
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Filter cards
            cards.forEach(card => {
                const status = card.dataset.status;
                if (filter === 'all' || status === filter) {
                    card.style.display = '';
                    card.style.animation = 'fade-up 0.4s both';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
})();
</script>