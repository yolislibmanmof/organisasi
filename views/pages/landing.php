<?php
/**
 * ============================================================
 * HALAMAN BERANDA PUBLIK — ULTIMATE EDITION v5.9
 * Stabil, rapi, dan kaya visual. Semua class mengikuti landing.css
 * ============================================================
 */
$appName = setting('app_name', 'Organisasi');
$visi    = trim((string) setting('visi'));
$misiRaw = (string) setting('misi');
$misiList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $misiRaw))));
$motto   = trim((string) setting('motto'));
$period  = trim((string) setting('cabinet_period'));
$officersByDiv = \Models\Officer::groupByDivision();

// Fallback aman untuk semua variabel
$stats = $stats ?? ['members' => 0, 'events' => 0, 'upcoming' => 0, 'ongoing' => 0];
$events = $events ?? [];
$articles = $articles ?? [];
$testimonials = $testimonials ?? [];
$galleries = $galleries ?? [];
$totalGalleries = $totalGalleries ?? count($galleries);
?>

<style>
/* ============ LANDING ULTIMATE ENHANCEMENTS (prefix ld-) ============ */

/* ---- Hero orbs animasi ---- */
.pub-hero { position: relative; overflow: hidden; }
.pub-hero::before, .pub-hero::after {
    content: ''; position: absolute; border-radius: 50%;
    filter: blur(120px); pointer-events: none; z-index: 0;
}
.pub-hero::before {
    width: 560px; height: 560px; background: #6366f1; opacity: .32;
    top: -180px; left: -140px; animation: ldOrb 22s ease-in-out infinite alternate;
}
.pub-hero::after {
    width: 460px; height: 460px; background: #22d3ee; opacity: .25;
    bottom: -160px; right: -120px; animation: ldOrb 26s ease-in-out infinite alternate-reverse;
}
@keyframes ldOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(70px, 50px) scale(1.15); }
}

/* ---- Trust stack ---- */
.ld-trust {
    display: flex; align-items: center; gap: 14px;
    margin-top: 26px; flex-wrap: wrap;
    animation: fade-up .7s .45s both;
}
.ld-avatars { display: flex; }
.ld-av {
    width: 34px; height: 34px; border-radius: 50%;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 800; color: #fff;
    border: 2px solid var(--bg-0);
    margin-left: -8px; transition: transform .2s;
}
.ld-av:first-child { margin-left: 0; }
.ld-av:hover { transform: translateY(-3px) scale(1.08); z-index: 2; }
.ld-av.more {
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(8px);
    color: #c7d2fe; font-size: 10px;
}
.ld-trust-text { display: flex; flex-direction: column; gap: 2px; }
.ld-stars { display: flex; gap: 2px; color: #fbbf24; font-size: 13px; }
.ld-trust-text span { font-size: 12px; color: var(--txt-1); }
.ld-trust-text strong { color: var(--txt-0); }

/* ---- Division header (penomoran aman) ---- */
.ld-div-head {
    display: flex; align-items: center; gap: 12px;
    margin: 0 0 18px; padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-brd);
}
.ld-div-num {
    width: 36px; height: 36px; border-radius: 10px;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(99, 102, 241, .3);
}
.ld-div-head h3 {
    font-size: 16px; font-weight: 700; color: var(--acc);
    display: flex; align-items: center; gap: 10px;
    flex: 1; margin: 0;
}
.ld-div-head h3 i { font-size: 20px; }
.ld-div-count {
    font-size: 11px; font-weight: 700; color: var(--txt-2);
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    padding: 4px 10px; border-radius: 99px;
}

/* ---- Officer profile button ---- */
.ld-officer-btn {
    margin-top: 12px; padding: 8px 16px;
    font-size: 11.5px; width: 100%;
    justify-content: center;
}

/* ---- Join strip (gradient card) ---- */
.ld-join {
    display: flex; align-items: center;
    justify-content: space-between; gap: 40px;
    padding: 44px; position: relative; overflow: hidden;
    background:
        radial-gradient(600px 260px at 92% -30%, rgba(34,211,238,.18), transparent 60%),
        radial-gradient(520px 240px at 8% 140%, rgba(139,92,246,.22), transparent 60%),
        var(--glass);
}
.ld-join::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.15), transparent 70%);
    pointer-events: none;
}
.ld-join-copy { flex: 1; position: relative; z-index: 1; }
.ld-join-copy h2 {
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 800; letter-spacing: -.6px;
    margin: 8px 0 12px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.ld-join-copy p {
    color: var(--txt-1);
    font-size: 14.5px; line-height: 1.7;
    max-width: 60ch;
}

/* ---- Section reveal stagger ---- */
.pub-section .section-head.reveal { animation: fade-up .6s both; }

/* ---- Empty state enhancement ---- */
.pub-empty {
    text-align: center;
    padding: 60px 40px;
    max-width: 540px;
    margin: 0 auto;
}
.pub-empty i {
    font-size: 48px;
    color: var(--txt-2);
    margin-bottom: 16px;
    display: block;
}
.pub-empty p {
    color: var(--txt-1);
    font-size: 14.5px;
    max-width: 40ch;
    margin: 0 auto;
}

/* ---- Responsive ---- */
@media (max-width: 720px) {
    .ld-join {
        flex-direction: column;
        align-items: flex-start;
        padding: 28px;
    }
    .ld-div-head { flex-wrap: wrap; }
}
</style>

<!-- ============ HERO ============ -->
<section class="pub-hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <span class="hero-eyebrow">
                <span class="live-dot"></span>
                Situs Resmi Organisasi
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
                <span><i class="ph ph-users-three"></i> <?= (int) $stats['members'] ?> anggota terdaftar</span>
            </div>

            <!-- Trust Stack -->
            <div class="ld-trust">
                <div class="ld-avatars">
                    <span class="ld-av" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">A</span>
                    <span class="ld-av" style="background:linear-gradient(135deg,#22d3ee,#06b6d4)">B</span>
                    <span class="ld-av" style="background:linear-gradient(135deg,#10b981,#34d399)">C</span>
                    <span class="ld-av more">+<?= (int) $stats['members'] ?></span>
                </div>
                <div class="ld-trust-text">
                    <div class="ld-stars">
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                        <i class="ph-fill ph-star"></i>
                    </div>
                    <span>Dipercaya oleh <strong><?= (int) $stats['members'] ?>+</strong> anggota aktif</span>
                </div>
            </div>
        </div>

        <div class="hero-visual" id="heroVisual">
            <div class="hero-ring"></div>
            <div class="hero-ring hero-ring-2"></div>
            <div class="float-card float-card-1 glass-card">
                <div class="stat-icon grad-1"><i class="ph ph-users-three"></i></div>
                <div><strong><?= (int) $stats['members'] ?></strong><span>Anggota</span></div>
            </div>
            <div class="float-card float-card-2 glass-card">
                <div class="stat-icon grad-4"><i class="ph ph-calendar-check"></i></div>
                <div><strong><?= (int) $stats['upcoming'] ?></strong><span>Event Mendatang</span></div>
            </div>
            <div class="float-card float-card-3 glass-card">
                <div class="stat-icon grad-3"><i class="ph ph-broadcast"></i></div>
                <div><strong><?= (int) $stats['ongoing'] ?></strong><span>Berlangsung</span></div>
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
    <div class="motto-band reveal">
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
                <article class="glass-card pub-event-card reveal">
                    <div class="pec-date">
                        <strong><?= date('d', strtotime($ev['event_date'])) ?></strong>
                        <span><?= date('M Y', strtotime($ev['event_date'])) ?></span>
                    </div>
                    <div class="pec-body">
                        <div class="pec-head">
                            <h3><?= e($ev['title']) ?></h3>
                            <span class="event-pill <?= e($ev['status']) ?>">
                                <?= $ev['status'] === 'ongoing' ? 'Berlangsung' : 'Akan Datang' ?>
                            </span>
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
    <?php else: ?>
        <div class="glass-card pub-empty reveal">
            <i class="ph ph-calendar-blank"></i>
            <p>Belum ada agenda mendatang. Pantau terus halaman ini!</p>
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
            <?php foreach ($articles as $a): ?>
                <a href="<?= url('artikel/' . (int) $a['id']) ?>" class="glass-card pub-article-card reveal">
                    <div class="pac-cover cat-<?= strtolower(str_replace(' ', '-', $a['category'])) ?>">
                        <i class="ph ph-newspaper"></i>
                        <span><?= e($a['category']) ?></span>
                    </div>
                    <div class="pac-body">
                        <time><?= date('d M Y', strtotime($a['created_at'])) ?></time>
                        <h3><?= e($a['title']) ?></h3>
                        <p><?= e(mb_strimwidth((string) ($a['excerpt'] ?? ''), 0, 100, '…')) ?></p>
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
            <p>Belum ada artikel yang diterbitkan. Pantau terus halaman ini!</p>
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
    <div class="reveal" style="margin-bottom:32px;">
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
                        <img src="<?= url('assets/uploads/officers/' . e($o['photo'])) ?>" alt="<?= e($o['full_name']) ?>">
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
                <button class="btn btn-ghost btn-xs ld-officer-btn officer-profile-btn"
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

<!-- ============ KENAPA BERGABUNG ============ -->
<section class="pub-section">
    <div class="ld-join glass-card reveal">
        <div class="ld-join-copy">
            <span class="page-eyebrow">Bergabunglah</span>
            <h2>Kenapa harus masuk organisasi?</h2>
            <p>Bergabung dengan kami — jaringan alumni lintas generasi yang telah berkontribusi nyata.
               Pengalaman, pelatihan, dan relasi yang Anda dapatkan akan menjadi bekal karier seumur hidup.</p>
        </div>
        <a href="<?= url('sensus') ?>" class="btn btn-primary btn-lg">
            <i class="ph ph-rocket-launch"></i>
            <span class="btn-text">Daftar Sekarang</span>
        </a>
    </div>
</section>

<!-- ============ BANNER SENSUS ============ -->
<section class="pub-section">
    <div class="census-band glass-card reveal">
        <div class="census-copy">
            <span class="page-eyebrow">Sensus Anggota</span>
            <h2>Dalam rangka digitalisasi database anggota.</h2>
            <p>Bantu kami merekap seluruh anggota maupun alumni agar terhubung dalam satu sistem terpadu.</p>
        </div>
        <a href="<?= url('sensus') ?>" class="btn btn-primary btn-lg">
            <i class="ph ph-clipboard-text"></i>
            <span class="btn-text">Isi Sensus Sekarang</span>
        </a>
    </div>
</section>

<!-- ============ CTA ============ -->
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
<div class="officer-lightbox" id="officerLightbox">
    <button class="lightbox-close" id="officerLightboxClose">
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
    const lb = document.getElementById('officerLightbox');
    if (!lb) return;
    const lbClose = document.getElementById('officerLightboxClose');
    const lbPhoto = document.getElementById('officerProfilePhoto');
    const lbName  = document.getElementById('officerProfileName');
    const lbPos   = document.getElementById('officerProfilePosition');
    const lbBio   = document.getElementById('officerProfileBio');

    document.querySelectorAll('.officer-profile-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const photo = btn.dataset.photo;
            lbPhoto.innerHTML = photo
                ? '<img src="' + photo + '" alt="">'
                : btn.dataset.initial;
            lbName.textContent = btn.dataset.name;
            lbPos.textContent  = btn.dataset.position;
            lbBio.textContent  = btn.dataset.bio;
            lb.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    const closeLB = () => {
        lb.classList.remove('show');
        document.body.style.overflow = '';
    };
    lbClose?.addEventListener('click', closeLB);
    lb.addEventListener('click', (e) => {
        if (e.target === lb) closeLB();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lb.classList.contains('show')) closeLB();
    });
})();
</script>