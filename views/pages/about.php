<?php
/**
 * ============================================================
 * HALAMAN TENTANG — ULTIMATE EDITION v5.9 (TERKOREKSI)
 * Perbaikan:
 *  - Statistik memakai method model yang benar (countAll / countByStatus)
 *  - Loop bidang memakai counter integer (menghindari TypeError string+int)
 *  - Nama bidang diambil dari kunci array groupByDivision()
 *  - Tombol "Lihat Profile" pengurus + modal profil
 * ============================================================
 */

$appName = setting('app_name', 'Organisasi');
$visi    = trim((string) setting('visi'));
$misiRaw = (string) setting('misi');
$misiList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $misiRaw))));
$motto   = trim((string) setting('motto'));
$period  = trim((string) setting('cabinet_period'));

/* ---- Statistik aman (fallback bila method gagal) ---- */
$safe = static function (callable $fn, $fallback = 0) {
    try { return $fn(); } catch (\Throwable $e) { return $fallback; }
};

$stats = [
    'members' => (int) $safe(fn() => \Models\Member::countAll()),
    'events'  => (int) $safe(fn() => array_sum(\Models\Event::countByStatus())),
    'years'   => max(1, (int) setting('established_years', 5)),
];

$officersByDiv = \Models\Officer::groupByDivision();
?>

<!-- ========== HERO SECTION ========== -->
<section class="about-hero">
    <div class="about-hero-content reveal">
        <span class="page-eyebrow">Tentang Kami</span>
        <h1 class="about-hero-title">
            <?= e($period !== '' ? $period : 'Profil ' . $appName) ?>
        </h1>
        <p class="about-hero-sub">
            Mengenal lebih dekat identitas, visi, dan perjalanan organisasi kami
            dalam membangun generasi yang berintegritas dan berdedikasi.
        </p>
    </div>

    <!-- Mini stats band -->
    <div class="about-stats reveal">
        <div class="about-stat">
            <strong><?= number_format($stats['members']) ?></strong>
            <span>Anggota Terdaftar</span>
        </div>
        <div class="about-stat-sep"></div>
        <div class="about-stat">
            <strong><?= number_format($stats['events']) ?>+</strong>
            <span>Event Terlaksana</span>
        </div>
        <div class="about-stat-sep"></div>
        <div class="about-stat">
            <strong><?= (int) $stats['years'] ?></strong>
            <span>Tahun Berdiri</span>
        </div>
    </div>
</section>

<!-- ========== MAIN CONTENT ========== -->
<div class="about-container">

    <!-- ===== Cerita Organisasi ===== -->
    <section class="about-story glass-card reveal">
        <div class="about-story-icon" aria-hidden="true">
            <i class="ph ph-book-open-text"></i>
        </div>
        <div class="about-story-content">
            <h2>Cerita Kami</h2>
            <p>
                <strong><?= e($appName) ?></strong> adalah rumah kekeluargaan yang mewadahi seluruh pelajar
                dan mahasiswa untuk berkembang melalui kegiatan, pelatihan, dan pengabdian masyarakat.
                Kami percaya bahwa pertumbuhan terbaik terjadi ketika individu-individu saling mendukung
                dan berkolaborasi dalam satu visi bersama.
            </p>
            <p>
                Melalui berbagai program unggulan dan jaringan alumni lintas generasi, kami berkomitmen
                untuk mencetak insan akademis yang berintegritas, berkompeten, dan berdedikasi bagi
                kemajuan masyarakat — khususnya di daerah kami.
            </p>
        </div>
    </section>

    <!-- ===== Visi & Misi ===== -->
    <?php if ($visi !== '' || !empty($misiList)): ?>
    <section class="about-vm reveal">
        <?php if ($visi !== ''): ?>
        <article class="vm-card glass-card">
            <div class="vm-icon grad-1" aria-hidden="true">
                <i class="ph ph-eye"></i>
            </div>
            <div class="vm-content">
                <h3>Visi</h3>
                <p class="vm-text"><?= nl2br(e($visi)) ?></p>
            </div>
        </article>
        <?php endif; ?>

        <?php if (!empty($misiList)): ?>
        <article class="vm-card glass-card">
            <div class="vm-icon grad-3" aria-hidden="true">
                <i class="ph ph-target"></i>
            </div>
            <div class="vm-content">
                <h3>Misi</h3>
                <ol class="mission-list">
                    <?php foreach ($misiList as $i => $m): ?>
                    <li style="animation-delay: <?= $i * 0.08 ?>s"><?= e($m) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </article>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- ===== Motto Band ===== -->
    <?php if ($motto !== ''): ?>
    <section class="motto-section reveal">
        <div class="motto-band">
            <div class="motto-icon" aria-hidden="true">
                <i class="ph ph-quotes"></i>
            </div>
            <div class="motto-content">
                <span class="motto-label">Motto Kami</span>
                <p><?= e($motto) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===== Nilai-Nilai Organisasi ===== -->
    <section class="values-section reveal">
        <div class="section-head">
            <h2>Nilai-Nilai Kami</h2>
            <p>Prinsip-prinsip yang menjadi fondasi setiap langkah organisasi.</p>
        </div>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon"><i class="ph ph-handshake"></i></div>
                <h4>Kekeluargaan</h4>
                <p>Membangun ikatan yang kuat antar anggota melalui rasa saling peduli dan mendukung.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="ph ph-graduation-cap"></i></div>
                <h4>Integritas</h4>
                <p>Menjunjung tinggi kejujuran, tanggung jawab, dan konsistensi dalam setiap tindakan.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="ph ph-rocket-launch"></i></div>
                <h4>Inovasi</h4>
                <p>Terus beradaptasi dan menciptakan solusi kreatif untuk tantangan zaman.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="ph ph-heart"></i></div>
                <h4>Pengabdian</h4>
                <p>Berkontribusi nyata bagi masyarakat melalui aksi dan kolaborasi yang berkelanjutan.</p>
            </div>
        </div>
    </section>

    <!-- ===== Struktur Pengurus (loop terkoreksi) ===== -->
    <?php if (!empty($officersByDiv)): ?>
    <section class="structure-section reveal">
        <div class="section-head">
            <h2>Struktur Kepengurusan</h2>
            <p>Jajaran pengurus yang memimpin perjalanan organisasi periode ini.</p>
        </div>

        <?php $divNo = 0; foreach ($officersByDiv as $divName => $officers):
            if (!is_array($officers) || empty($officers)) continue;
            $divNo++;
        ?>
        <div class="division-block">
            <div class="division-header">
                <span class="division-num"><?= str_pad((string) $divNo, 2, '0', STR_PAD_LEFT) ?></span>
                <h3>
                    <i class="ph ph-buildings"></i>
                    <?= e((string) $divName) ?>
                </h3>
                <span class="division-count"><?= count($officers) ?> orang</span>
            </div>
            <div class="officer-grid">
                <?php foreach ($officers as $o): ?>
                <article class="glass-card officer-card">
                    <div class="officer-photo">
                        <?php if (!empty($o['photo'])): ?>
                            <img src="<?= url('assets/uploads/officers/' . e($o['photo'])) ?>" alt="<?= e($o['full_name']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="officer-initial"><?= e(strtoupper(substr($o['full_name'], 0, 1))) ?></span>
                        <?php endif; ?>
                        <span class="officer-glow"></span>
                    </div>
                    <h4><?= e($o['full_name']) ?></h4>
                    <?php if (!empty($o['position'])): ?>
                    <span class="officer-position"><?= e($o['position']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($o['bio'])): ?>
                    <button class="btn btn-ghost btn-xs about-officer-btn officer-profile-btn"
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
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- ===== CTA Section ===== -->
    <section class="about-cta glass-card reveal">
        <div class="cta-content">
            <h2>Bergabung Bersama Kami?</h2>
            <p>Jadilah bagian dari keluarga besar <?= e($appName) ?> dan berkontribusi untuk masa depan yang lebih baik.</p>
            <div class="cta-actions">
                <a href="<?= url('sensus') ?>" class="btn btn-primary btn-lg">
                    <i class="ph ph-user-plus"></i>
                    <span class="btn-text">Daftar Sekarang</span>
                </a>
                <a href="<?= url('') ?>#kontak" class="btn btn-ghost" data-scroll>
                    <i class="ph ph-envelope-simple"></i>
                    <span class="btn-text">Hubungi Kami</span>
                </a>
            </div>
        </div>
    </section>
</div>

<!-- ========== MODAL PROFIL PENGURUS ========== -->
<div class="officer-lightbox" id="officerLightbox">
    <button class="lightbox-close" id="officerLightboxClose"><i class="ph ph-x"></i></button>
    <div class="officer-profile-card">
        <div class="officer-profile-photo" id="officerProfilePhoto"></div>
        <h2 id="officerProfileName"></h2>
        <span class="officer-position" id="officerProfilePosition"></span>
        <p id="officerProfileBio"></p>
    </div>
</div>

<!-- ========== STYLING ========== -->
<style>
/* ---- Hero Section ---- */
.about-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 800px;
    margin: 0 auto;
}
.about-hero-title {
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
.about-hero-sub {
    color: var(--txt-1);
    font-size: 16px;
    line-height: 1.7;
    max-width: 52ch;
    margin: 0 auto;
}

/* ---- Stats Band ---- */
.about-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    padding: 24px 40px;
    margin-top: 40px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    box-shadow: 0 20px 60px rgba(2,6,23,.3);
}
.about-stat { text-align: center; }
.about-stat strong {
    display: block;
    font-size: 32px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.about-stat span {
    display: block;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
}
.about-stat-sep { width: 1px; height: 40px; background: var(--glass-brd); }

/* ---- Container ---- */
.about-container {
    max-width: 920px;
    margin: 0 auto;
    padding: 0 24px 80px;
    display: flex;
    flex-direction: column;
    gap: 32px;
}

/* ---- Story Section ---- */
.about-story {
    display: flex;
    gap: 28px;
    padding: 40px;
    align-items: flex-start;
}
.about-story-icon {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid;
    place-items: center;
    font-size: 28px;
    color: var(--acc);
}
.about-story-content h2 { font-size: 22px; font-weight: 800; margin-bottom: 14px; }
.about-story-content p { color: var(--txt-1); font-size: 15px; line-height: 1.85; margin-bottom: 12px; }
.about-story-content p:last-child { margin-bottom: 0; }
.about-story-content strong { color: var(--txt-0); }

/* ---- Visi Misi ---- */
.about-vm {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}
.vm-card { padding: 36px 32px; display: flex; gap: 20px; align-items: flex-start; }
.vm-icon {
    width: 56px; height: 56px; flex-shrink: 0;
    border-radius: 16px;
    display: grid; place-items: center;
    font-size: 24px; color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
}
.vm-content h3 { font-size: 18px; font-weight: 800; margin-bottom: 12px; }
.vm-text { color: var(--txt-1); font-size: 14px; line-height: 1.8; }

.mission-list {
    list-style: none;
    counter-reset: misi;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.mission-list li {
    counter-increment: misi;
    position: relative;
    padding-left: 36px;
    color: var(--txt-1);
    font-size: 14px;
    line-height: 1.65;
    animation: fade-up 0.5s both;
}
.mission-list li::before {
    content: counter(misi, decimal-leading-zero);
    position: absolute; left: 0; top: 2px;
    width: 26px; height: 26px; border-radius: 8px;
    display: grid; place-items: center;
    background: rgba(16,185,129,.12);
    border: 1px solid rgba(16,185,129,.3);
    color: #6ee7b7;
    font-size: 10px; font-weight: 800;
}

/* ---- Motto Section ---- */
.motto-section { margin: 8px 0; }
.motto-band {
    display: flex; gap: 20px; align-items: center;
    padding: 32px 40px;
    border-radius: var(--rad-lg);
    background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(34,211,238,.08));
    border: 1px solid rgba(99,102,241,.25);
    position: relative; overflow: hidden;
}
.motto-band::before {
    content: ''; position: absolute;
    top: -50%; right: -10%;
    width: 200px; height: 200px; border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.15), transparent 70%);
    pointer-events: none;
}
.motto-icon {
    width: 56px; height: 56px; flex-shrink: 0;
    border-radius: 16px;
    background: rgba(255,255,255,.06);
    display: grid; place-items: center;
    font-size: 28px; color: var(--acc);
}
.motto-label {
    display: block;
    font-size: 11px; font-weight: 800;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--acc); margin-bottom: 8px;
}
.motto-content p {
    font-size: 18px; font-weight: 600; line-height: 1.7;
    color: var(--txt-0); font-style: italic; margin: 0;
}

/* ---- Values Section ---- */
.values-section .section-head { text-align: center; margin-bottom: 36px; }
.values-section .section-head h2 { font-size: 28px; font-weight: 800; margin-bottom: 10px; }
.values-section .section-head p { color: var(--txt-1); font-size: 14.5px; }
.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
.value-card {
    padding: 28px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    text-align: center;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.value-card:hover {
    transform: translateY(-6px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}
.value-icon {
    width: 52px; height: 52px;
    margin: 0 auto 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid; place-items: center;
    font-size: 24px; color: var(--acc);
    transition: transform 0.3s;
}
.value-card:hover .value-icon { transform: scale(1.1) rotate(-5deg); }
.value-card h4 { font-size: 15px; font-weight: 800; margin-bottom: 8px; }
.value-card p { color: var(--txt-1); font-size: 13px; line-height: 1.6; margin: 0; }

/* ---- Structure Section ---- */
.structure-section .section-head { text-align: center; margin-bottom: 40px; }
.structure-section .section-head h2 { font-size: 28px; font-weight: 800; margin-bottom: 10px; }
.structure-section .section-head p { color: var(--txt-1); font-size: 14.5px; }

.division-block { margin-bottom: 40px; }
.division-block:last-child { margin-bottom: 0; }

.division-header {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 20px; padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-brd);
}
.division-num {
    width: 34px; height: 34px; flex-shrink: 0;
    border-radius: 10px;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    box-shadow: 0 6px 16px rgba(99,102,241,.3);
}
.division-header h3 {
    flex: 1;
    font-size: 16px; font-weight: 700; color: var(--acc);
    display: flex; align-items: center; gap: 10px;
    margin: 0;
}
.division-header h3 i { font-size: 20px; }
.division-count {
    font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 99px;
    background: rgba(34,211,238,.1);
    border: 1px solid rgba(34,211,238,.25);
    color: #67e8f9;
}

/* ---- Tombol profil pengurus ---- */
.about-officer-btn {
    margin-top: 12px;
    width: 100%;
    justify-content: center;
}

/* ---- Modal profil pengurus ---- */
.officer-lightbox {
    position: fixed; inset: 0; z-index: 130;
    background: rgba(4,8,20,.92);
    backdrop-filter: blur(14px);
    display: grid; place-items: center;
    padding: 40px;
    opacity: 0; visibility: hidden;
    transition: .3s;
}
.officer-lightbox.show { opacity: 1; visibility: visible; }
.officer-profile-card {
    max-width: 480px; width: 100%;
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
    width: 120px; height: 120px;
    border-radius: 32px;
    margin: 0 auto 18px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 42px; font-weight: 800; color: #fff;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,.1);
    box-shadow: 0 20px 50px rgba(99,102,241,.4);
}
.officer-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
.officer-profile-card h2 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
.officer-profile-card p {
    margin-top: 18px;
    color: var(--txt-1); font-size: 13.5px; line-height: 1.7;
    text-align: left; padding-top: 18px;
    border-top: 1px solid var(--glass-brd);
}

/* ---- CTA Section ---- */
.about-cta {
    text-align: center;
    padding: 48px 40px;
    background: radial-gradient(600px 200px at 50% -20%, rgba(99,102,241,.2), transparent 70%), var(--glass);
}
.cta-content h2 { font-size: 26px; font-weight: 800; margin-bottom: 12px; }
.cta-content p { color: var(--txt-1); font-size: 14.5px; max-width: 48ch; margin: 0 auto 24px; }
.cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

/* ---- Responsive ---- */
@media (max-width: 720px) {
    .about-hero { padding-top: 120px; }
    .about-stats { flex-direction: column; gap: 16px; padding: 24px; }
    .about-stat-sep { width: 40px; height: 1px; }
    .about-story { flex-direction: column; text-align: center; padding: 32px 24px; }
    .about-story-icon { margin: 0 auto; }
    .vm-card { flex-direction: column; text-align: center; }
    .vm-icon { margin: 0 auto; }
    .motto-band { flex-direction: column; text-align: center; padding: 28px 24px; }
    .motto-content p { font-size: 15px; }
    .division-header { flex-wrap: wrap; }
    .about-cta { padding: 36px 24px; }
}
</style>

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
            lbPhoto.innerHTML = photo ? '<img src="' + photo + '" alt="">' : btn.dataset.initial;
            lbName.textContent = btn.dataset.name;
            lbPos.textContent  = btn.dataset.position;
            lbBio.textContent  = btn.dataset.bio;
            lb.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    });

    const closeLB = () => { lb.classList.remove('show'); document.body.style.overflow = ''; };
    lbClose?.addEventListener('click', closeLB);
    lb.addEventListener('click', (e) => { if (e.target === lb) closeLB(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lb.classList.contains('show')) closeLB();
    });
})();
</script>