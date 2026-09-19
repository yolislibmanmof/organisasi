<!-- File: views/pages/landing.php (FINAL - TAHAP 5.2) -->
<!-- ============ HERO ============ -->
<section class="pub-hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <span class="hero-eyebrow"><span class="live-dot"></span> Situs Resmi Organisasi</span>
            <h1 class="hero-title">Tempat <em>pelajar & mahasiswa</em> bertumbuh dan berdedikasi.</h1>
            <p class="hero-sub">
                <?= e(APP_NAME) ?> adalah rumah kekeluargaan yang mewadahi seluruh pelajar dan mahasiswa
                untuk berkembang melalui kegiatan, pelatihan, dan pengabdian masyarakat.
            </p>
            <div class="hero-cta">
                <a href="<?= url('login') ?>" class="btn btn-primary">
                    <i class="ph ph-rocket-launch"></i><span class="btn-text">Masuk Sistem Anggota</span>
                </a>
                <a href="#event" class="btn btn-ghost" data-scroll>
                    <i class="ph ph-calendar-blank"></i><span class="btn-text">Lihat Kegiatan</span>
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

<!-- ============ VISI & MISI ============ -->
<section class="pub-section" id="tentang">
    <div class="section-head reveal">
        <span class="page-eyebrow">Tentang Kami</span>
        <h2>Visi & Misi</h2>
        <p>Fondasi yang menuntun setiap langkah dan program organisasi.</p>
    </div>
    <div class="about-grid">
        <article class="glass-card about-card reveal">
            <div class="about-icon grad-1"><i class="ph ph-eye"></i></div>
            <h3>Visi</h3>
            <p>
                Menjadikan <?= e(APP_NAME) ?> sebagai wadah pembentukan insan akademis yang berintegritas,
                berkompeten, dan berdedikasi — katalisator perubahan positif bagi masyarakat.
            </p>
        </article>
        <article class="glass-card about-card reveal">
            <div class="about-icon grad-3"><i class="ph ph-target"></i></div>
            <h3>Misi</h3>
            <ol class="mission-list">
                <li>Menyelenggarakan kegiatan akademis yang menumbuhkan berpikir kritis, rasional, dan empiris.</li>
                <li>Menumbuhkan nilai kejujuran, disiplin, tanggung jawab, dan konsisten.</li>
                <li>Mengasah keterampilan, minat, dan bakat melalui kegiatan serta pelatihan.</li>
                <li>Mendorong pengabdian anggota bagi kemajuan organisasi dan masyarakat.</li>
            </ol>
        </article>
    </div>
</section>

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

<!-- ============ STRUKTUR KEPENGURUSAN ============ -->
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

<!-- ============ GALERI KEGIATAN (MASONRY + LIGHTBOX) ============ -->
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
</section>
<?php endif; ?>

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
        <p>Bergabunglah dengan <?= e(APP_NAME) ?> dan dedikasikan karya terbaikmu bagi masyarakat.</p>
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