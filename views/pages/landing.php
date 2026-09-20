<?php
/**
 * ============================================================
 * HALAMAN BERANDA PUBLIK — ULTIMATE EDITION v7.0
 * Premium, kaya visual, tanpa duplikasi, dengan micro-interactions
 * ============================================================
 */
$appName = setting('app_name', 'Organisasi');
$visi    = trim((string) setting('visi'));
$misiRaw = (string) setting('misi');
$misiList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $misiRaw))));
$motto   = trim((string) setting('motto'));
$period  = trim((string) setting('cabinet_period'));
$officersByDiv = \Models\Officer::groupByDivision();

// Fallback aman
$stats = $stats ?? ['members' => 0, 'events' => 0, 'upcoming' => 0, 'ongoing' => 0];
$events = $events ?? [];
$articles = $articles ?? [];
$testimonials = $testimonials ?? [];
$galleries = $galleries ?? [];
$totalGalleries = $totalGalleries ?? count($galleries);
?>

<style>
/* ============ LANDING ULTIMATE v7.0 ENHANCEMENTS (prefix ld-) ============ */

/* ---- Hero orbs animasi (premium) ---- */
.pub-hero { position: relative; overflow: hidden; }
.pub-hero::before, .pub-hero::after {
    content: ''; position: absolute; border-radius: 50%;
    filter: blur(120px); pointer-events: none; z-index: 0;
}
.pub-hero::before {
    width: 620px; height: 620px; background: #6366f1; opacity: .28;
    top: -200px; left: -160px; animation: ldOrb 24s ease-in-out infinite alternate;
}
.pub-hero::after {
    width: 520px; height: 520px; background: #22d3ee; opacity: .22;
    bottom: -180px; right: -140px; animation: ldOrb 28s ease-in-out infinite alternate-reverse;
}
@keyframes ldOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(80px, 60px) scale(1.18); }
}

/* ---- Trust stack ---- */
.ld-trust {
    display: flex; align-items: center; gap: 16px;
    margin-top: 28px; flex-wrap: wrap;
    animation: fade-up .7s .5s both;
}
.ld-avatars { display: flex; }
.ld-av {
    width: 36px; height: 36px; border-radius: 50%;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 800; color: #fff;
    border: 2px solid var(--bg-0);
    margin-left: -10px; transition: all .3s cubic-bezier(.22,1,.36,1);
    position: relative;
}
.ld-av:first-child { margin-left: 0; }
.ld-av:hover { transform: translateY(-4px) scale(1.12); z-index: 2; }
.ld-av.more {
    background: rgba(99,102,241,.3);
    backdrop-filter: blur(8px);
    color: #c7d2fe; font-size: 10px; font-weight: 800;
    border: 2px solid rgba(99,102,241,.4);
}
.ld-trust-text { display: flex; flex-direction: column; gap: 3px; }
.ld-stars { display: flex; gap: 2px; color: #fbbf24; font-size: 14px; }
.ld-stars i { filter: drop-shadow(0 2px 4px rgba(251,191,36,.4)); }
.ld-trust-text span { font-size: 12px; color: var(--txt-1); }
.ld-trust-text strong { color: var(--txt-0); font-weight: 800; }

/* ---- Division header (penomoran aman) ---- */
.ld-div-head {
    display: flex; align-items: center; gap: 14px;
    margin: 0 0 22px; padding-bottom: 16px;
    border-bottom: 1px solid var(--glass-brd);
}
.ld-div-num {
    width: 40px; height: 40px; border-radius: 11px;
    display: grid; place-items: center;
    font-size: 13px; font-weight: 900; color: #fff;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(99, 102, 241, .35);
    letter-spacing: .5px;
}
.ld-div-head h3 {
    font-size: 17px; font-weight: 800; color: var(--acc);
    display: flex; align-items: center; gap: 10px;
    flex: 1; margin: 0;
}
.ld-div-head h3 i { font-size: 20px; }
.ld-div-count {
    font-size: 11px; font-weight: 800; color: var(--txt-1);
    background: rgba(99,102,241,.1);
    border: 1px solid rgba(99,102,241,.25);
    padding: 5px 12px; border-radius: 99px;
    letter-spacing: .3px;
}

/* ---- Officer card premium enhancement ---- */
.officer-card {
    position: relative; overflow: hidden;
}
.officer-card::before {
    content: ''; position: absolute;
    inset: 0; border-radius: var(--rad-lg);
    background: linear-gradient(135deg, rgba(99,102,241,.0), rgba(34,211,238,.0));
    transition: background .4s;
    pointer-events: none; z-index: 1;
}
.officer-card:hover::before {
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
}
.officer-photo {
    transition: transform .5s cubic-bezier(.22,1,.36,1);
}
.officer-card:hover .officer-photo {
    transform: translateY(-4px);
}
.officer-card h3 {
    transition: color .3s;
}
.officer-card:hover h3 { color: var(--acc); }

/* ---- Officer profile button ---- */
.ld-officer-btn {
    margin-top: 14px; padding: 9px 16px;
    font-size: 11.5px; width: 100%;
    justify-content: center;
    border-radius: 10px;
    transition: all .25s;
}
.ld-officer-btn:hover {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff; border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
}

/* ---- Officer Lightbox Modal ---- */
.officer-lightbox {
    position: fixed; inset: 0; z-index: 150;
    background: rgba(4, 8, 20, .92);
    backdrop-filter: blur(16px);
    display: none;
    place-items: center; padding: 40px;
    animation: lb-fade .3s ease;
}
.officer-lightbox.show { display: grid; }
.officer-profile-card {
    max-width: 520px; width: 100%;
    padding: 40px;
    background: rgba(15, 21, 48, .95);
    backdrop-filter: blur(24px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    box-shadow: 0 40px 100px rgba(0,0,0,.6);
    text-align: center;
    animation: zoom-in .4s var(--ease-elastic);
}
.officer-profile-photo {
    width: 140px; height: 140px;
    border-radius: 36px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 56px; font-weight: 900; color: #fff;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(99,102,241,.4);
    border: 3px solid rgba(255,255,255,.15);
}
.officer-profile-photo img {
    width: 100%; height: 100%; object-fit: cover;
}
.officer-profile-card h2 {
    font-size: 22px; font-weight: 800;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.officer-profile-card .officer-position {
    margin-bottom: 18px; display: inline-block;
}
.officer-profile-card p {
    color: var(--txt-1); font-size: 14px; line-height: 1.75;
    margin: 0;
}
.officer-lightbox .lightbox-close {
    position: absolute; top: 24px; right: 28px;
}

/* ---- Motto Band ---- */
.motto-band {
    margin-top: 32px;
    padding: 32px 40px;
    border-radius: var(--rad-lg);
    background:
        radial-gradient(600px 200px at 10% 0%, rgba(99,102,241,.12), transparent 60%),
        var(--glass);
    border: 1px solid var(--glass-brd);
    text-align: center;
    display: flex; align-items: flex-start; gap: 20px;
    justify-content: center;
}
.motto-band i {
    font-size: 32px; color: var(--acc);
    flex-shrink: 0;
    filter: drop-shadow(0 4px 12px rgba(34,211,238,.3));
}
.motto-band p {
    font-family: 'Instrument Serif', serif;
    font-style: italic; font-size: clamp(18px, 2.2vw, 24px);
    line-height: 1.5; color: #e0e7ff;
    max-width: 70ch; margin: 0;
}

/* ---- Event Card Enhancement ---- */
.pub-event-card {
    position: relative; overflow: hidden;
}
.pub-event-card::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    opacity: 0; transition: opacity .3s;
}
.pub-event-card:hover::before { opacity: 1; }
.event-pill {
    display: inline-block;
    padding: 3px 10px; border-radius: 99px;
    font-size: 10px; font-weight: 800;
    letter-spacing: .5px; text-transform: uppercase;
    flex-shrink: 0;
}
.event-pill.ongoing {
    background: rgba(16,185,129,.15);
    color: #6ee7b7;
    border: 1px solid rgba(16,185,129,.3);
}
.event-pill.upcoming {
    background: rgba(99,102,241,.15);
    color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.3);
}
.event-meta {
    display: flex; gap: 14px; flex-wrap: wrap;
    color: var(--txt-1); font-size: 12px; font-weight: 600;
    margin-top: 8px;
}
.event-meta span {
    display: inline-flex; align-items: center; gap: 6px;
}
.event-meta i { color: var(--acc); font-size: 13px; }

/* ---- Article Card Enhancement ---- */
.pub-article-card { position: relative; }
.pac-read-time {
    position: absolute;
    top: 14px; right: 14px;
    padding: 4px 10px;
    background: rgba(10, 15, 31, .8);
    backdrop-filter: blur(8px);
    border-radius: 99px;
    font-size: 10px; font-weight: 700;
    color: var(--txt-1);
    display: inline-flex; align-items: center; gap: 4px;
    z-index: 2;
}
.pac-read-time i { font-size: 11px; color: var(--acc); }

/* ---- Gallery Enhancement ---- */
.masonry-item {
    position: relative; overflow: hidden;
}
.masonry-item::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(4,8,20,.85) 100%);
    opacity: 0; transition: opacity .4s;
    pointer-events: none;
}
.masonry-item:hover::after { opacity: 1; }

/* ---- Unified CTA Join Strip ---- */
.ld-join {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center; gap: 40px;
    padding: 50px;
    position: relative; overflow: hidden;
    background:
        radial-gradient(700px 320px at 92% -30%, rgba(34,211,238,.2), transparent 60%),
        radial-gradient(620px 280px at 8% 140%, rgba(139,92,246,.25), transparent 60%),
        var(--glass);
    border: 1px solid rgba(99,102,241,.2);
}
.ld-join::before {
    content: ''; position: absolute;
    top: -60%; right: -15%;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.2), transparent 70%);
    pointer-events: none;
    animation: ldOrb 20s ease-in-out infinite alternate;
}
.ld-join::after {
    content: ''; position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.4;
    pointer-events: none;
}
.ld-join-copy { position: relative; z-index: 1; }
.ld-join-copy h2 {
    font-size: clamp(24px, 3.2vw, 34px);
    font-weight: 800; letter-spacing: -.8px;
    margin: 10px 0 14px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.ld-join-copy p {
    color: var(--txt-1);
    font-size: 15px; line-height: 1.75;
    max-width: 60ch; margin: 0;
}
.ld-join-cta {
    display: flex; gap: 10px;
    margin-top: 22px;
    position: relative; z-index: 1;
    flex-wrap: wrap;
}
.ld-join-actions {
    display: flex; flex-direction: column; gap: 10px;
    position: relative; z-index: 1;
}
.ld-join-stat {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 12px;
    min-width: 200px;
}
.ld-join-stat i {
    font-size: 22px; color: var(--acc);
}
.ld-join-stat strong {
    display: block; font-size: 20px; font-weight: 800;
    color: var(--txt-0); font-variant-numeric: tabular-nums;
}
.ld-join-stat span {
    font-size: 11.5px; color: var(--txt-1); font-weight: 600;
}

/* ---- Section reveal stagger ---- */
.pub-section .section-head.reveal { animation: fade-up .7s both; }
.pub-section .section-head.reveal + * { animation-delay: .1s; }

/* ---- Empty state enhancement ---- */
.pub-empty {
    text-align: center;
    padding: 70px 40px;
    max-width: 560px;
    margin: 0 auto;
    position: relative;
}
.pub-empty::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 300px; height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.08), transparent 70%);
    pointer-events: none;
}
.pub-empty i {
    font-size: 56px;
    color: var(--txt-2);
    margin-bottom: 18px;
    display: block;
    position: relative;
    filter: drop-shadow(0 4px 12px rgba(99,102,241,.2));
}
.pub-empty h4 {
    font-size: 16px; font-weight: 800;
    color: var(--txt-0); margin-bottom: 8px;
    position: relative;
}
.pub-empty p {
    color: var(--txt-1);
    font-size: 14px; line-height: 1.7;
    max-width: 40ch;
    margin: 0 auto;
    position: relative;
}

/* ---- Live indicator pulse ---- */
.live-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16,185,129,.7);
    animation: live-pulse 2s infinite;
    margin-right: 6px;
}
@keyframes live-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(16,185,129,.7); }
    70%  { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
}

/* ---- Marquee hover pause ---- */
.marquee:hover .marquee-track { animation-play-state: paused; }
.quote-card {
    transition: all .3s cubic-bezier(.22,1,.36,1);
}
.quote-card:hover {
    transform: translateY(-6px);
    border-color: rgba(99,102,241,.3);
    box-shadow: 0 24px 60px rgba(2,6,23,.5);
}

/* ---- Button micro-interactions ---- */
.btn-primary { position: relative; overflow: hidden; }
.btn-primary::before {
    content: ''; position: absolute;
    top: 50%; left: 50%;
    width: 0; height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    transform: translate(-50%, -50%);
    transition: width .6s, height .6s;
}
.btn-primary:hover::before { width: 300px; height: 300px; }
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 44px rgba(99,102,241,.4);
}

/* ---- Stats band enhancement ---- */
.band-item { transition: transform .3s; }
.band-item:hover { transform: translateY(-3px); }
.band-num {
    transition: color .3s;
}
.band-item:hover .band-num {
    color: var(--acc);
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .ld-join {
        grid-template-columns: 1fr;
        padding: 36px;
        gap: 28px;
    }
    .ld-join-actions {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .ld-join-stat { flex: 1; min-width: 150px; }
}

@media (max-width: 720px) {
    .ld-div-head { flex-wrap: wrap; gap: 10px; }
    .ld-div-count { margin-left: auto; }
    .officer-profile-card { padding: 28px 22px; }
    .officer-profile-photo { width: 110px; height: 110px; border-radius: 28px; font-size: 44px; }
    .officer-profile-card h2 { font-size: 19px; }
    .motto-band {
        padding: 24px 22px;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .motto-band p { font-size: 17px; }
    .ld-join { padding: 28px 22px; }
}

@media (max-width: 520px) {
    .ld-trust { flex-direction: column; align-items: flex-start; gap: 12px; }
    .ld-join-actions { flex-direction: column; }
}
</style>

<!-- ============ HERO ============ -->
<section class="pub-hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <span class="hero-eyebrow">
                <span class="live-dot"></span>
                Situs Resmi <?= e($appName) ?>
            </span>
            <h1 class="hero-title">
                Tempat <em>pelajar & mahasiswa</em> bertumbuh dan berdedikasi.
            </h1>
            <p class="hero-sub">
                <?= e($appName) ?> adalah rumah kekeluargaan yang mewadahi seluruh pelajar dan mahasiswa
                untuk berkembang melalui kegiatan, pelatihan, dan pengabdian masyarakat.
            </p>
            <div class="hero-cta">
                <a href="<?= url('login') ?>" class="btn btn-primary">
                    <i class="ph ph-rocket-launch"></i>
                    <span class="btn-text">Masuk Sistem Anggota</span>
                </a>
                <a href="<?= url('event') ?>" class="btn btn-ghost">
                    <i class="ph ph-calendar-blank"></i>
                    <span class="btn-text">Lihat Semua Kegiatan</span>
                </a>
            </div>
            <div class="hero-mini">
                <span><i class="ph ph-shield-check"></i> Organisasi terverifikasi</span>
                <span><i class="ph ph-users-three"></i> <?= number_format($stats['members']) ?> anggota terdaftar</span>
            </div>

            <!-- Trust Stack -->
            <div class="ld-trust">
                <div class="ld-avatars">
                    <span class="ld-av" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">A</span>
                    <span class="ld-av" style="background:linear-gradient(135deg,#22d3ee,#06b6d4)">B</span>
                    <span class="ld-av" style="background:linear-gradient(135deg,#10b981,#34d399)">C</span>
                    <span class="ld-av" style="background:linear-gradient(135deg,#f59e0b,#fbbf24)">D</span>
                    <span class="ld-av more">+<?= number_format($stats['members']) ?></span>
                </div>
                <div class="ld-trust-text">
                    <div class="ld-stars">
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                    </div>
                    <span>Dipercaya oleh <strong><?= number_format($stats['members']) ?>+</strong> anggota aktif</span>
                </div>
            </div>
        </div>

        <div class="hero-visual" id="heroVisual">
            <div class="hero-ring"></div>
            <div class="hero-ring hero-ring-2"></div>
            <div class="float-card float-card-1 glass-card">
                <div class="stat-icon grad-1"><i class="ph ph-users-three"></i></div>
                <div><strong><?= number_format($stats['members']) ?></strong><span>Anggota</span></div>
            </div>
            <div class="float-card float-card-2 glass-card">
                <div class="stat-icon grad-4"><i class="ph ph-calendar-check"></i></div>
                <div><strong><?= number_format($stats['upcoming']) ?></strong><span>Event Mendatang</span></div>
            </div>
            <div class="float-card float-card-3 glass-card">
                <div class="stat-icon grad-3"><i class="ph ph-broadcast"></i></div>
                <div><strong><?= number_format($stats['ongoing']) ?></strong><span>Berlangsung</span></div>
            </div>
        </div>
    </div>
    <a href="#stats" class="scroll-hint" data-scroll aria-label="Gulir ke bawah">
        <i class="ph ph-caret-down"></i>
    </a>
</section>

<!-- ============ STATISTIK ============ -->
<section class="pub-stats" id="stats">
    <div class="stats-band glass-card">
        <div class="band-item">
            <strong class="band-num" data-count="<?= (int) $stats['members'] ?>">0</strong>
            <span>Anggota Terdaftar</span>
        </div>
        <div class="band-sep"></div>
        <div class="band-item">
            <strong class="band-num" data-count="<?= (int) $stats['events'] ?>">0</strong>
            <span>Total Event</span>
        </div>
        <div class="band-sep"></div>
        <div class="band-item">
            <strong class="band-num" data-count="<?= (int) $stats['upcoming'] ?>">0</strong>
            <span>Agenda Mendatang</span>
        </div>
        <div class="band-sep"></div>
        <div class="band-item">
            <strong class="band-num" data-count="<?= (int) $stats['ongoing'] ?>">0</strong>
            <span>Berlangsung Hari Ini</span>
        </div>
    </div>
</section>

<!-- ============ VISI & MISI ============ -->
<?php if ($visi !== '' || !empty($misiList)): ?>
<section class="pub-section" id="tentang">
    <div class="section-head reveal">
        <span class="page-eyebrow">Tentang Kami</span>
        <h2><?= $period !== '' ? e($period) : 'Visi & Misi' ?></h2>
        <p>Fondasi yang menuntun setiap langkah dan program organisasi.</p>
    </div>
    <div class="about-grid">
        <?php if ($visi !== ''): ?>
        <article class="glass-card about-card reveal">
            <div class="about-icon grad-1"><i class="ph ph-eye"></i></div>
            <h3>Visi</h3>
            <p><?= nl2br(e($visi)) ?></p>
        </article>
        <?php endif; ?>

        <?php if (!empty($misiList)): ?>
        <article class="glass-card about-card reveal">
            <div class="about-icon grad-3"><i class="ph ph-target"></i></div>
            <h3>Misi</h3>
            <ol class="mission-list">
                <?php foreach ($misiList as $m): ?>
                    <li><?= e($m) ?></li>
                <?php endforeach; ?>
            </ol>
        </article>
        <?php endif; ?>
    </div>
    <?php if ($motto !== ''): ?>
    <div class="motto-band glass-card reveal">
        <i class="ph ph-quotes"></i>
        <p><?= e($motto) ?></p>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ============ EVENT MENDATANG ============ -->
<section class="pub-section" id="event">
    <div class="section-head reveal">
        <span class="page-eyebrow">Agenda</span>
        <h2>Event Mendatang</h2>
        <p>Kegiatan terdekat yang terbuka bagi anggota dan masyarakat.</p>
    </div>

    <?php if (!empty($events)): ?>
        <div class="pub-events">
            <?php foreach ($events as $ev): ?>
                <?php
                    $statusClass = $ev['status'] === 'ongoing' ? 'ongoing' : 'upcoming';
                    $statusText  = $ev['status'] === 'ongoing' ? 'Berlangsung' : 'Akan Datang';
                ?>
                <article class="glass-card pub-event-card reveal">
                    <div class="pec-date">
                        <strong><?= date('d', strtotime($ev['event_date'])) ?></strong>
                        <span><?= date('M Y', strtotime($ev['event_date'])) ?></span>
                    </div>
                    <div class="pec-body">
                        <div class="pec-head">
                            <h3><?= e($ev['title']) ?></h3>
                            <span class="event-pill <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                        <div class="event-meta">
                            <span>
                                <i class="ph ph-clock"></i>
                                <?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : 'Menyusul' ?>
                            </span>
                            <span>
                                <i class="ph ph-map-pin"></i>
                                <?= e($ev['location'] ?: 'Menyusul') ?>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="pub-more">
            <a href="<?= url('event') ?>" class="btn btn-ghost">
                <span class="btn-text">Lihat Semua Event</span>
                <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    <?php else: ?>
        <div class="glass-card pub-empty reveal">
            <i class="ph ph-calendar-blank"></i>
            <h4>Belum Ada Agenda</h4>
            <p>Pantau terus halaman ini untuk informasi kegiatan terbaru dari organisasi.</p>
        </div>
    <?php endif; ?>
</section>

<!-- ============ KEUNGGULAN ============ -->
<section class="pub-section" id="keunggulan">
    <div class="section-head reveal">
        <span class="page-eyebrow">Keunggulan</span>
        <h2>Sistem Organisasi Modern</h2>
        <p>Manajemen keanggotaan digital yang transparan, cepat, dan aman.</p>
    </div>
    <div class="feat-grid">
        <article class="glass-card feat-card reveal">
            <div class="stat-icon grad-1"><i class="ph ph-users-three"></i></div>
            <h3>Manajemen Anggota Digital</h3>
            <p>Basis data anggota terkelola real-time dengan sistem akun terintegrasi.</p>
        </article>
        <article class="glass-card feat-card reveal">
            <div class="stat-icon grad-2"><i class="ph ph-chart-line-up"></i></div>
            <h3>Analitik Pertumbuhan</h3>
            <p>Pantau tren keanggotaan dan kegiatan melalui visualisasi interaktif.</p>
        </article>
        <article class="glass-card feat-card reveal">
            <div class="stat-icon grad-3"><i class="ph ph-shield-check"></i></div>
            <h3>Keamanan Data</h3>
            <p>Enkripsi kata sandi, proteksi sesi, dan akses terverifikasi bagi setiap anggota.</p>
        </article>
        <article class="glass-card feat-card reveal">
            <div class="stat-icon grad-4"><i class="ph ph-hand-heart"></i></div>
            <h3>Komunitas & Alumni</h3>
            <p>Jaringan lintas generasi yang terus berkontribusi bagi masyarakat.</p>
        </article>
    </div>
</section>

<!-- ============ ARTIKEL TERBARU ============ -->
<section class="pub-section" id="artikel-terbaru">
    <div class="section-head reveal">
        <span class="page-eyebrow">Publikasi</span>
        <h2>Artikel Terbaru</h2>
        <p>Informasi, edukasi, dan kabar terbaru dari organisasi.</p>
    </div>
    <?php if (!empty($articles)): ?>
        <div class="pub-articles">
            <?php foreach ($articles as $a):
                $contentWords = str_word_count(strip_tags($a['content'] ?? ''));
                $readTime = max(1, (int) ceil($contentWords / 200));
                $catSlug = strtolower(str_replace(' ', '-', $a['category']));
            ?>
                <a href="<?= url('artikel/' . (int) $a['id']) ?>" class="glass-card pub-article-card reveal">
                    <div class="pac-cover cat-<?= e($catSlug) ?>">
                        <i class="ph ph-newspaper"></i>
                        <span><?= e($a['category']) ?></span>
                        <span class="pac-read-time">
                            <i class="ph ph-clock"></i>
                            <?= $readTime ?> min baca
                        </span>
                    </div>
                    <div class="pac-body">
                        <time><?= date('d M Y', strtotime($a['created_at'])) ?></time>
                        <h3><?= e($a['title']) ?></h3>
                        <p><?= e(mb_strimwidth((string) ($a['excerpt'] ?? ''), 0, 110, '…')) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="pub-more">
            <a href="<?= url('artikel') ?>" class="btn btn-ghost">
                <span class="btn-text">Lihat Semua Artikel</span>
                <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    <?php else: ?>
        <div class="glass-card pub-empty reveal">
            <i class="ph ph-newspaper"></i>
            <h4>Belum Ada Artikel</h4>
            <p>Artikel dan publikasi terbaru akan segera hadir di halaman ini.</p>
        </div>
    <?php endif; ?>
</section>

<!-- ============ STRUKTUR KEPENGURUSAN ============ -->
<?php if (!empty($officersByDiv)): ?>
<section class="pub-section" id="pengurus">
    <div class="section-head reveal">
        <span class="page-eyebrow">Organisasi</span>
        <h2>Struktur Kepengurusan</h2>
        <p>Jajaran pengurus yang memimpin perjalanan organisasi periode ini.</p>
    </div>

    <?php $divNo = 0; foreach ($officersByDiv as $divName => $officers): $divNo++; ?>
    <div class="reveal" style="margin-bottom:36px;">
        <div class="ld-div-head">
            <span class="ld-div-num"><?= str_pad((string) $divNo, 2, '0', STR_PAD_LEFT) ?></span>
            <h3><i class="ph ph-buildings"></i> <?= e($divName) ?></h3>
            <span class="ld-div-count"><?= count($officers) ?> orang</span>
        </div>
        <div class="officer-grid">
            <?php foreach ($officers as $o): ?>
            <article class="glass-card officer-card reveal">
                <div class="officer-photo">
                    <?php if (!empty($o['photo'])): ?>
                        <img src="<?= url('assets/uploads/officers/' . e($o['photo'])) ?>" alt="<?= e($o['full_name']) ?>" loading="lazy">
                    <?php else: ?>
                        <span class="officer-initial">
                            <?= e(strtoupper(substr($o['full_name'], 0, 1))) ?>
                        </span>
                    <?php endif; ?>
                    <span class="officer-glow"></span>
                </div>
                <h3><?= e($o['full_name']) ?></h3>
                <span class="officer-position"><?= e($o['position']) ?></span>
                <?php if (!empty($o['bio'])): ?>
                <button type="button" class="btn btn-ghost ld-officer-btn officer-profile-btn"
                        data-name="<?= e($o['full_name']) ?>"
                        data-position="<?= e($o['position']) ?>"
                        data-photo="<?= !empty($o['photo']) ? e(url('assets/uploads/officers/' . $o['photo'])) : '' ?>"
                        data-initial="<?= e(strtoupper(substr($o['full_name'], 0, 1))) ?>"
                        data-bio="<?= e($o['bio']) ?>">
                    <i class="ph ph-user-circle"></i>
                    <span class="btn-text">Lihat Profile</span>
                </button>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<!-- ============ KATA ALUMNI ============ -->
<?php if (!empty($testimonials)): ?>
<section class="pub-section" id="alumni">
    <div class="section-head reveal">
        <span class="page-eyebrow">Testimoni</span>
        <h2>Kata Alumni</h2>
        <p>Cerita dan kesan dari mereka yang pernah bertumbuh bersama.</p>
    </div>
    <div class="marquee reveal">
        <div class="marquee-track">
            <?php foreach (array_merge($testimonials, $testimonials) as $t): ?>
            <article class="glass-card quote-card">
                <i class="ph ph-quotes quote-mark"></i>
                <p><?= e($t['quote']) ?></p>
                <div class="quote-who">
                    <span class="avatar avatar-sm"><?= e(strtoupper(substr($t['name'], 0, 1))) ?></span>
                    <div>
                        <strong><?= e($t['name']) ?></strong>
                        <small><?= e($t['role'] ?? 'Alumni') ?></small>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ GALERI ============ -->
<?php if (!empty($galleries)): ?>
<section class="pub-section" id="galeri">
    <div class="section-head reveal">
        <span class="page-eyebrow">Dokumentasi</span>
        <h2>Galeri Kegiatan</h2>
        <p>Kilasan momen dari berbagai kegiatan organisasi.</p>
    </div>
    <div class="masonry reveal">
        <?php foreach ($galleries as $g): ?>
        <figure class="masonry-item gallery-item"
                data-full="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>"
                data-title="<?= e($g['title']) ?>">
            <img src="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>"
                 alt="<?= e($g['title']) ?>" loading="lazy">
            <figcaption>
                <i class="ph ph-magnifying-glass-plus"></i>
                <?= e($g['title']) ?>
            </figcaption>
        </figure>
        <?php endforeach; ?>
    </div>
    <?php if ($totalGalleries > 9): ?>
    <div class="pub-more">
        <a href="<?= url('galeri') ?>" class="btn btn-ghost">
            <span class="btn-text">Lihat Semua Galeri</span>
            <i class="ph ph-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ============ UNIFIED CTA: JOIN + SENSUS ============ -->
<section class="pub-section">
    <div class="ld-join glass-card reveal">
        <div class="ld-join-copy">
            <span class="page-eyebrow">Bergabunglah</span>
            <h2>Jadilah bagian dari perjalanan kami.</h2>
            <p>Masuk sebagai anggota resmi atau bantu kami memperbarui database melalui sensus digital.
               Setiap kontribusi Anda memperkuat jaringan lintas generasi yang berdampak nyata.</p>
            <div class="ld-join-cta">
                <a href="<?= url('sensus') ?>" class="btn btn-primary">
                    <i class="ph ph-rocket-launch"></i>
                    <span class="btn-text">Daftar Anggota Baru</span>
                </a>
                <a href="<?= url('login') ?>" class="btn btn-ghost">
                    <i class="ph ph-sign-in"></i>
                    <span class="btn-text">Masuk Anggota</span>
                </a>
            </div>
        </div>
        <div class="ld-join-actions">
            <div class="ld-join-stat">
                <i class="ph ph-users-three"></i>
                <div>
                    <strong data-count="<?= (int) $stats['members'] ?>"><?= number_format($stats['members']) ?></strong>
                    <span>Anggota Terdaftar</span>
                </div>
            </div>
            <div class="ld-join-stat">
                <i class="ph ph-calendar-check"></i>
                <div>
                    <strong data-count="<?= (int) $stats['events'] ?>"><?= number_format($stats['events']) ?></strong>
                    <span>Total Kegiatan</span>
                </div>
            </div>
            <div class="ld-join-stat">
                <i class="ph ph-globe-hemisphere-east"></i>
                <div>
                    <strong>Lintas Generasi</strong>
                    <span>Jaringan Alumni</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ CTA FINAL ============ -->
<section class="pub-cta">
    <div class="cta-card glass-card reveal">
        <h2>Siap menjadi bagian dari perubahan?</h2>
        <p>Bergabunglah dengan <?= e($appName) ?> dan dedikasikan karya terbaikmu bagi masyarakat.</p>
        <div class="hero-cta">
            <a href="<?= url('login') ?>" class="btn btn-primary">
                <i class="ph ph-sign-in"></i>
                <span class="btn-text">Masuk Sekarang</span>
            </a>
            <a href="#kontak" class="btn btn-ghost" data-scroll>
                <i class="ph ph-chats"></i>
                <span class="btn-text">Hubungi Kami</span>
            </a>
        </div>
    </div>
</section>

<!-- ============ MODAL PROFIL PENGURUS ============ -->
<div class="officer-lightbox" id="officerLightbox" role="dialog" aria-label="Profil pengurus">
    <button class="lightbox-close" id="officerLightboxClose" aria-label="Tutup">
        <i class="ph ph-x"></i>
    </button>
    <div class="officer-profile-card">
        <div class="officer-profile-photo" id="officerProfilePhoto"></div>
        <h2 id="officerProfileName"></h2>
        <span class="officer-position" id="officerProfilePosition"></span>
        <p id="officerProfileBio"></p>
    </div>
</div>

<script>
(function(){
    'use strict';
    const lb = document.getElementById('officerLightbox');
    if (!lb) return;
    const lbClose = document.getElementById('officerLightboxClose');
    const lbPhoto = document.getElementById('officerProfilePhoto');
    const lbName  = document.getElementById('officerProfileName');
    const lbPos   = document.getElementById('officerProfilePosition');
    const lbBio   = document.getElementById('officerProfileBio');

    const closeLB = () => {
        lb.classList.remove('show');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.officer-profile-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const photo = btn.dataset.photo;
            lbPhoto.innerHTML = photo
                ? '<img src="' + photo + '" alt="Foto profil">'
                : btn.dataset.initial;
            lbName.textContent = btn.dataset.name;
            lbPos.textContent  = btn.dataset.position;
            lbBio.textContent  = btn.dataset.bio;
            lb.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    lbClose?.addEventListener('click', closeLB);
    lb.addEventListener('click', (e) => {
        if (e.target === lb) closeLB();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lb.classList.contains('show')) closeLB();
    });
})();
</script>