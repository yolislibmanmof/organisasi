<!-- File: views/pages/landing.php (FINAL - TAHAP 5.6) -->
<?php
    $appName = setting('app_name', 'Organisasi');
    $visi    = trim((string) setting('visi'));
    $misiRaw = (string) setting('misi');
    $misiList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $misiRaw))));
    $motto   = trim((string) setting('motto'));
    $period  = trim((string) setting('cabinet_period'));
?>

<!-- ============ HERO ============ -->
<section class="pub-hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <span class="hero-eyebrow"><span class="live-dot"></span> Situs Resmi Organisasi</span>
            <h1 class="hero-title">Tempat <em>pelajar & mahasiswa</em> bertumbuh dan berdedikasi.</h1>
            <p class="hero-sub">
                <?= e($appName) ?> adalah rumah kekeluargaan yang mewadahi seluruh pelajar dan mahasiswa
                untuk berkembang melalui kegiatan, pelatihan, dan pengabdian masyarakat.
            </p>
            <div class="hero-cta">
                <a href="<?= url('login') ?>" class="btn btn-primary">
                    <i class="ph ph-rocket-launch"></i><span class="btn-text">Masuk Sistem Anggota</span>
                </a>
                <a href="<?= url('event') ?>" class="btn btn-ghost">
                    <i class="ph ph-calendar-blank"></i><span class="btn-text">Lihat Semua Kegiatan</span>
                </a>
            </div>
            <div class="hero-mini">
                <span><i class="ph ph-shield-check"></i> Organisasi terverifikasi</span>
                <span><i class="ph ph-users-three"></i> <?= (int) $stats['members'] ?> anggota terdaftar</span>
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
    <a href="#stats" class="scroll-hint" data-scroll aria-label="Gulir ke bawah"><i class="ph ph-caret-down"></i></a>
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

<!-- ============ VISI & MISI + PERIODE KABINET + SEMBOYAN ============ -->
<?php if ($visi !== '' || !empty($misiList)): ?>
<section class="pub-section" id="tentang">
    <div class="section-head reveal">
        <span class="page-eyebrow">Tentang Kami</span>
        <?php if ($period !== ''): ?>
            <h2><?= e($period) ?></h2>
        <?php else: ?>
            <h2>Visi & Misi</h2>
        <?php endif; ?>
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
                            <span><i class="ph ph-clock"></i> <?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : 'Menyusul' ?></span>
                            <span><i class="ph ph-map-pin"></i> <?= e($ev['location'] ?: 'Menyusul') ?></span>
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
                <span class="btn-text">Lihat Semua Artikel</span><i class="ph ph-arrow-right"></i>
            </a>
        </div>
    <?php else: ?>
        <div class="glass-card pub-empty reveal" style="max-width:540px;margin:0 auto;">
            <i class="ph ph-newspaper"></i>
            <p>Belum ada artikel yang diterbitkan. Pantau terus halaman ini!</p>
        </div>
    <?php endif; ?>
</section>

<!-- ============ STRUKTUR KEPENGURUSAN (DENGAN TOMBOL PROFIL) ============ -->
<?php if (!empty($officers)): ?>
<section class="pub-section" id="pengurus">
    <div class="section-head reveal">
        <span class="page-eyebrow">Organisasi</span>
        <h2>Struktur Kepengurusan</h2>
        <p>Jajaran pengurus yang memimpin perjalanan organisasi periode ini.</p>
    </div>
    <div class="officer-grid">
        <?php foreach ($officers as $o): ?>
        <article class="glass-card officer-card reveal">
            <div class="officer-photo">
                <?php if (!empty($o['photo'])): ?>
                    <img src="<?= url('assets/uploads/officers/' . e($o['photo'])) ?>" alt="<?= e($o['full_name']) ?>">
                <?php else: ?>
                    <span class="officer-initial"><?= e(strtoupper(substr($o['full_name'], 0, 1))) ?></span>
                <?php endif; ?>
                <span class="officer-glow"></span>
            </div>
            <h3><?= e($o['full_name']) ?></h3>
            <span class="officer-position"><?= e($o['position']) ?></span>
            <?php if (!empty($o['bio'])): ?>
            <button class="btn btn-ghost btn-xs officer-profile-btn"
                    data-name="<?= e($o['full_name']) ?>"
                    data-position="<?= e($o['position']) ?>"
                    data-photo="<?= !empty($o['photo']) ? e(url('assets/uploads/officers/' . $o['photo'])) : '' ?>"
                    data-initial="<?= e(strtoupper(substr($o['full_name'], 0, 1))) ?>"
                    data-bio="<?= e($o['bio']) ?>">
                <i class="ph ph-user-circle"></i><span class="btn-text">Lihat Profile</span>
            </button>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============ KATA ALUMNI (MARQUEE) ============ -->
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
                    <div><strong><?= e($t['name']) ?></strong><small><?= e($t['role'] ?? 'Alumni') ?></small></div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ GALERI KEGIATAN + TOMBOL LIHAT SEMUA ============ -->
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
            <img src="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
            <figcaption><i class="ph ph-magnifying-glass-plus"></i> <?= e($g['title']) ?></figcaption>
        </figure>
        <?php endforeach; ?>
    </div>
    <?php if (($totalGalleries ?? 0) > 9): ?>
    <div class="pub-more">
        <a href="<?= url('galeri') ?>" class="btn btn-ghost">
            <span class="btn-text">Lihat Semua Galeri</span><i class="ph ph-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<!-- ============ KENAPA BERGABUNG? ============ -->
<section class="pub-section">
    <div class="join-strip glass-card reveal">
        <div class="join-copy">
            <span class="page-eyebrow">Bergabunglah</span>
            <h2>Kenapa harus masuk organisasi?</h2>
            <p>Bergabung dengan kami — lebih dari ribuan alumni mahasiswa dan pelajar yang telah berkontribusi nyata di daerahnya. Jaringan, pengalaman, dan ilmu yang Anda dapatkan akan menjadi bekal karier seumur hidup.</p>
        </div>
        <a href="<?= url('sensus') ?>" class="btn btn-primary btn-lg">
            <i class="ph ph-rocket-launch"></i><span class="btn-text">Daftar Sekarang</span>
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
            <i class="ph ph-clipboard-text"></i><span class="btn-text">Isi Sensus Sekarang</span>
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
                <i class="ph ph-sign-in"></i><span class="btn-text">Masuk Sekarang</span>
            </a>
            <a href="#kontak" class="btn btn-ghost" data-scroll>
                <i class="ph ph-chats"></i><span class="btn-text">Hubungi Kami</span>
            </a>
        </div>
    </div>
</section>

<!-- ============ MODAL PROFIL PENGURUS ============ -->
<div class="officer-lightbox" id="officerLightbox">
    <button class="lightbox-close" id="officerLightboxClose"><i class="ph ph-x"></i></button>
    <div class="officer-profile-card">
        <div class="officer-profile-photo" id="officerProfilePhoto"></div>
        <h2 id="officerProfileName"></h2>
        <span class="officer-position" id="officerProfilePosition"></span>
        <p id="officerProfileBio"></p>
    </div>
</div>

<!-- ============ GAYA INTERNAL TAHAP 5.6 ============ -->
<style>
    .motto-band {
        margin-top: 36px;
        padding: 28px 36px;
        border-radius: var(--rad-lg);
        background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(34,211,238,.08));
        border: 1px solid rgba(99,102,241,.25);
        display: flex;
        align-items: flex-start;
        gap: 18px;
        text-align: left;
    }
    .motto-band i { font-size: 32px; color: var(--acc); flex-shrink: 0; margin-top: 4px; }
    .motto-band p { font-size: 16px; font-weight: 600; line-height: 1.7; color: var(--txt-0); font-style: italic; margin: 0; }

    .officer-profile-btn {
        margin-top: 12px;
        padding: 8px 16px;
        font-size: 11.5px;
        width: 100%;
        justify-content: center;
    }

    .join-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        padding: 44px;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(600px 260px at 92% -30%, rgba(34,211,238,.18), transparent 60%),
            radial-gradient(520px 240px at 8% 140%, rgba(139,92,246,.22), transparent 60%),
            var(--glass);
    }
    .join-copy { flex: 1; }
    .join-copy h2 {
        font-size: clamp(22px, 3vw, 32px);
        font-weight: 800;
        letter-spacing: -.6px;
        margin: 8px 0 12px;
        background: linear-gradient(135deg, #fff, #c7d2fe);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .join-copy p {
        color: var(--txt-1);
        font-size: 14.5px;
        line-height: 1.7;
        max-width: 60ch;
    }

    .officer-lightbox {
        position: fixed;
        inset: 0;
        z-index: 130;
        background: rgba(4,8,20,.92);
        backdrop-filter: blur(14px);
        display: grid;
        place-items: center;
        padding: 40px;
        opacity: 0;
        visibility: hidden;
        transition: .3s;
    }
    .officer-lightbox.show { opacity: 1; visibility: visible; }
    .officer-profile-card {
        max-width: 480px;
        width: 100%;
        padding: 40px 36px;
        border-radius: var(--rad-lg);
        background: var(--glass);
        border: 1px solid var(--glass-brd);
        backdrop-filter: blur(24px);
        text-align: center;
        position: relative;
        animation: fade-up .4s both;
    }
    .officer-profile-photo {
        width: 120px;
        height: 120px;
        border-radius: 32px;
        margin: 0 auto 18px;
        background: linear-gradient(135deg, var(--pri), var(--acc));
        display: grid;
        place-items: center;
        font-size: 42px;
        font-weight: 800;
        color: #fff;
        overflow: hidden;
        border: 3px solid rgba(255,255,255,.1);
        box-shadow: 0 20px 50px rgba(99,102,241,.4);
    }
    .officer-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
    .officer-profile-card h2 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
    .officer-profile-card p {
        margin-top: 18px;
        color: var(--txt-1);
        font-size: 13.5px;
        line-height: 1.7;
        text-align: left;
        padding-top: 18px;
        border-top: 1px solid var(--glass-brd);
    }

    @media (max-width: 720px) {
        .join-strip { flex-direction: column; align-items: flex-start; padding: 28px; }
        .motto-band { padding: 22px; }
        .motto-band p { font-size: 14px; }
    }
</style>

<script>
(function(){
    const lb = document.getElementById('officerLightbox');
    const lbClose = document.getElementById('officerLightboxClose');
    const lbPhoto = document.getElementById('officerProfilePhoto');
    const lbName = document.getElementById('officerProfileName');
    const lbPos = document.getElementById('officerProfilePosition');
    const lbBio = document.getElementById('officerProfileBio');

    if (!lb) return;

    document.querySelectorAll('.officer-profile-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const photo = btn.dataset.photo;
            const initial = btn.dataset.initial;
            lbPhoto.innerHTML = photo ? '<img src="' + photo + '" alt="">' : initial;
            lbName.textContent = btn.dataset.name;
            lbPos.textContent = btn.dataset.position;
            lbBio.textContent = btn.dataset.bio;
            lb.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    const closeLB = () => {
        lb.classList.remove('show');
        document.body.style.overflow = '';
    };
    lbClose?.addEventListener('click', closeLB);
    lb?.addEventListener('click', (e) => { if (e.target === lb) closeLB(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && lb.classList.contains('show')) closeLB(); });
})();
</script>