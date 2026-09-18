<!-- File: views/pages/landing.php -->
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