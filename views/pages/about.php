<?php
/**
 * ============================================================
 * HALAMAN TENTANG — ULTIMATE EDITION v7.0
 * Profil organisasi: cerita, visi-misi, nilai, pengurus, CTA
 * Dengan counter animation, premium effects, dan modal anti-bocor
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
$totalOfficers = 0;
foreach ($officersByDiv as $officers) {
    if (is_array($officers)) $totalOfficers += count($officers);
}
?>

<!-- ========== HERO SECTION ========== -->
<section class="about-hero">
    <!-- Decorative orbs -->
    <div class="about-orbs" aria-hidden="true">
        <span class="about-orb orb-1"></span>
        <span class="about-orb orb-2"></span>
    </div>

    <div class="about-hero-content reveal">
        <span class="page-eyebrow">
            <span class="live-dot"></span>
            Tentang Kami
        </span>
        <h1 class="about-hero-title">
            <?= e($period !== '' ? $period : 'Profil ' . $appName) ?>
        </h1>
        <p class="about-hero-sub">
            Mengenal lebih dekat identitas, visi, dan perjalanan organisasi kami
            dalam membangun generasi yang berintegritas dan berdedikasi.
        </p>
    </div>

    <!-- Animated stats band -->
    <div class="about-stats reveal">
        <div class="about-stat">
            <strong data-count="<?= $stats['members'] ?>">0</strong>
            <span>Anggota Terdaftar</span>
        </div>
        <div class="about-stat-sep"></div>
        <div class="about-stat">
            <strong data-count="<?= $stats['events'] ?>">0</strong>
            <span>Event Terlaksana</span>
        </div>
        <div class="about-stat-sep"></div>
        <div class="about-stat">
            <strong data-count="<?= (int) $stats['years'] ?>">0</strong>
            <span>Tahun Berdiri</span>
        </div>
        <div class="about-stat-sep"></div>
        <div class="about-stat">
            <strong data-count="<?= $totalOfficers ?>">0</strong>
            <span>Pengurus Aktif</span>
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
            <div class="motto-decor" aria-hidden="true">
                <i class="ph ph-star-four"></i>
                <i class="ph ph-sparkle"></i>
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

    <!-- ===== Struktur Pengurus ===== -->
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
                    <button type="button" class="btn btn-ghost about-officer-btn officer-profile-btn"
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

    <!-- ===== CTA Section ===== -->
    <section class="about-cta glass-card reveal">
        <div class="about-cta-decor" aria-hidden="true">
            <i class="ph ph-users-three"></i>
            <i class="ph ph-hand-heart"></i>
            <i class="ph ph-rocket-launch"></i>
        </div>
        <div class="cta-content">
            <span class="page-eyebrow">Bergabunglah</span>
            <h2>Siap menjadi bagian dari keluarga besar kami?</h2>
            <p>Jadilah bagian dari <?= e($appName) ?> dan berkontribusi untuk masa depan yang lebih baik bersama jaringan lintas generasi.</p>
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

<!-- ========== MODAL PROFIL PENGURUS (ANTI-BOCOR) ========== -->
<div class="officer-lightbox" id="officerLightbox" role="dialog" aria-label="Profil pengurus" aria-modal="true">
    <button class="lightbox-close" id="officerLightboxClose" aria-label="Tutup profil">
        <i class="ph ph-x"></i>
    </button>
    <div class="officer-profile-card">
        <div class="officer-profile-photo" id="officerProfilePhoto"></div>
        <h2 id="officerProfileName"></h2>
        <span class="officer-position" id="officerProfilePosition"></span>
        <p id="officerProfileBio"></p>
    </div>
</div>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ============ ABOUT PAGE v7.0 ============ */

/* ---- Hero Section ---- */
.about-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 60px;
    max-width: 880px;
    margin: 0 auto;
    overflow: hidden;
}

/* Decorative orbs */
.about-orbs {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.about-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: .25;
}
.about-orb.orb-1 {
    width: 420px; height: 420px;
    background: #6366f1;
    top: -80px; left: -100px;
    animation: aboutOrb 20s ease-in-out infinite alternate;
}
.about-orb.orb-2 {
    width: 360px; height: 360px;
    background: #22d3ee;
    bottom: -60px; right: -80px;
    animation: aboutOrb 24s ease-in-out infinite alternate-reverse;
}
@keyframes aboutOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.15); }
}

.about-hero-content {
    position: relative;
    z-index: 1;
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

/* Live dot indicator */
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

/* ---- Animated Stats Band ---- */
.about-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 28px;
    padding: 28px 44px;
    margin-top: 44px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    box-shadow: 0 20px 60px rgba(2,6,23,.3);
    position: relative;
    z-index: 1;
}
.about-stat { text-align: center; }
.about-stat strong {
    display: block;
    font-size: 34px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    transition: transform .3s;
}
.about-stat:hover strong {
    transform: scale(1.08);
}
.about-stat span {
    display: block;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    margin-top: 6px;
    letter-spacing: .3px;
}
.about-stat-sep { width: 1px; height: 40px; background: var(--glass-brd); }

/* ---- Container ---- */
.about-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 24px 80px;
    display: flex;
    flex-direction: column;
    gap: 36px;
}

/* ---- Story Section ---- */
.about-story {
    display: flex;
    gap: 28px;
    padding: 40px;
    align-items: flex-start;
    position: relative;
    overflow: hidden;
}
.about-story::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 300px; height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.1), transparent 70%);
    pointer-events: none;
}
.about-story-icon {
    width: 68px;
    height: 68px;
    flex-shrink: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(34,211,238,.2));
    display: grid;
    place-items: center;
    font-size: 30px;
    color: var(--acc);
    box-shadow: 0 10px 24px rgba(99,102,241,.2);
    position: relative;
    z-index: 1;
}
.about-story-content { position: relative; z-index: 1; }
.about-story-content h2 { font-size: 24px; font-weight: 800; margin-bottom: 16px; }
.about-story-content p { color: var(--txt-1); font-size: 15px; line-height: 1.85; margin-bottom: 12px; }
.about-story-content p:last-child { margin-bottom: 0; }
.about-story-content strong { color: var(--txt-0); }

/* ---- Visi Misi ---- */
.about-vm {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}
.vm-card {
    padding: 36px 32px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
    transition: all .3s var(--ease-smooth);
}
.vm-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 26px 60px rgba(2,6,23,.45);
}
.vm-icon {
    width: 60px; height: 60px; flex-shrink: 0;
    border-radius: 16px;
    display: grid; place-items: center;
    font-size: 26px; color: #fff;
    box-shadow: 0 10px 24px rgba(0,0,0,.25);
    transition: transform .3s;
}
.vm-card:hover .vm-icon { transform: scale(1.08) rotate(-5deg); }
.vm-content h3 { font-size: 19px; font-weight: 800; margin-bottom: 12px; }
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
    padding-left: 40px;
    color: var(--txt-1);
    font-size: 14px;
    line-height: 1.65;
    animation: fade-up 0.5s both;
    transition: transform .2s;
}
.mission-list li:hover {
    transform: translateX(4px);
}
.mission-list li::before {
    content: counter(misi, decimal-leading-zero);
    position: absolute; left: 0; top: 2px;
    width: 28px; height: 28px; border-radius: 9px;
    display: grid; place-items: center;
    background: rgba(16,185,129,.14);
    border: 1px solid rgba(16,185,129,.3);
    color: #6ee7b7;
    font-size: 11px; font-weight: 800;
}

/* ---- Motto Section ---- */
.motto-section { margin: 8px 0; }
.motto-band {
    display: flex;
    gap: 22px;
    align-items: center;
    padding: 36px 44px;
    border-radius: var(--rad-lg);
    background:
        radial-gradient(600px 200px at 90% 0%, rgba(34,211,238,.12), transparent 60%),
        linear-gradient(135deg, rgba(99,102,241,.14), rgba(34,211,238,.1));
    border: 1px solid rgba(99,102,241,.25);
    position: relative;
    overflow: hidden;
}
.motto-band::before {
    content: ''; position: absolute;
    top: -50%; right: -10%;
    width: 280px; height: 280px; border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,.15), transparent 70%);
    pointer-events: none;
}
.motto-icon {
    width: 60px; height: 60px; flex-shrink: 0;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 10px 24px rgba(99,102,241,.35);
}
.motto-label {
    display: block;
    font-size: 11px; font-weight: 800;
    letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--acc); margin-bottom: 8px;
}
.motto-content { flex: 1; }
.motto-content p {
    font-family: 'Instrument Serif', serif;
    font-size: 20px;
    font-weight: 400;
    font-style: italic;
    line-height: 1.6;
    color: var(--txt-0);
    margin: 0;
}
.motto-decor {
    display: flex;
    flex-direction: column;
    gap: 12px;
    color: var(--acc);
    font-size: 22px;
    opacity: .5;
}
.motto-decor i {
    animation: star-twinkle 3s ease-in-out infinite;
}
.motto-decor i:nth-child(2) { animation-delay: -1.5s; }
@keyframes star-twinkle {
    0%, 100% { opacity: .4; transform: scale(1) rotate(0deg); }
    50% { opacity: .9; transform: scale(1.2) rotate(15deg); }
}

/* ---- Values Section ---- */
.values-section .section-head { text-align: center; margin-bottom: 40px; }
.values-section .section-head h2 { font-size: 30px; font-weight: 800; margin-bottom: 10px; letter-spacing: -.8px; }
.values-section .section-head p { color: var(--txt-1); font-size: 15px; max-width: 50ch; margin: 0 auto; }
.values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 18px;
}
.value-card {
    padding: 32px 26px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    text-align: center;
    transition: all 0.35s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}
.value-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
    opacity: 0;
    transition: opacity .3s;
}
.value-card:hover::before { opacity: 1; }
.value-card:hover {
    transform: translateY(-8px);
    border-color: rgba(99,102,241,.4);
    box-shadow: 0 24px 60px rgba(2,6,23,.5);
}
.value-icon {
    position: relative;
    z-index: 1;
    width: 56px; height: 56px;
    margin: 0 auto 18px;
    border-radius: 15px;
    background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(34,211,238,.2));
    display: grid; place-items: center;
    font-size: 26px; color: var(--acc);
    transition: transform 0.4s var(--ease-elastic);
    box-shadow: 0 8px 20px rgba(99,102,241,.2);
}
.value-card:hover .value-icon {
    transform: scale(1.15) rotate(-8deg);
    box-shadow: 0 12px 30px rgba(99,102,241,.35);
}
.value-card h4 {
    position: relative;
    z-index: 1;
    font-size: 16px; font-weight: 800; margin-bottom: 10px;
    color: var(--txt-0);
}
.value-card p {
    position: relative;
    z-index: 1;
    color: var(--txt-1); font-size: 13px; line-height: 1.65; margin: 0;
}

/* ---- Structure Section ---- */
.structure-section .section-head { text-align: center; margin-bottom: 44px; }
.structure-section .section-head h2 { font-size: 30px; font-weight: 800; margin-bottom: 10px; letter-spacing: -.8px; }
.structure-section .section-head p { color: var(--txt-1); font-size: 15px; max-width: 50ch; margin: 0 auto; }

.division-block { margin-bottom: 44px; }
.division-block:last-child { margin-bottom: 0; }

.division-header {
    display: flex; align-items: center; gap: 14px;
    margin-bottom: 22px; padding-bottom: 16px;
    border-bottom: 1px solid var(--glass-brd);
}
.division-num {
    width: 38px; height: 38px; flex-shrink: 0;
    border-radius: 11px;
    display: grid; place-items: center;
    font-size: 12px; font-weight: 900; color: #fff;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    box-shadow: 0 8px 20px rgba(99,102,241,.35);
    letter-spacing: .5px;
}
.division-header h3 {
    flex: 1;
    font-size: 17px; font-weight: 800; color: var(--acc);
    display: flex; align-items: center; gap: 10px;
    margin: 0;
}
.division-header h3 i { font-size: 20px; }
.division-count {
    font-size: 11px; font-weight: 800;
    padding: 5px 12px; border-radius: 99px;
    background: rgba(34,211,238,.12);
    border: 1px solid rgba(34,211,238,.3);
    color: #67e8f9;
    letter-spacing: .3px;
}

/* ---- Officer Card Premium ---- */
.officer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
}
.officer-card {
    position: relative;
    padding: 28px 22px;
    text-align: center;
    overflow: hidden;
    transition: all .35s var(--ease-smooth);
}
.officer-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(99,102,241,.0), rgba(34,211,238,.0));
    transition: background .4s;
    pointer-events: none;
    z-index: 0;
}
.officer-card:hover::before {
    background: linear-gradient(135deg, rgba(99,102,241,.08), rgba(34,211,238,.05));
}
.officer-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 70px rgba(2,6,23,.55);
}
.officer-photo {
    position: relative;
    width: 100px; height: 100px;
    margin: 0 auto 18px;
    z-index: 1;
    transition: transform .4s var(--ease-smooth);
}
.officer-card:hover .officer-photo {
    transform: translateY(-4px) scale(1.03);
}
.officer-photo img, .officer-initial {
    width: 100%; height: 100%;
    border-radius: 28px;
    object-fit: cover;
    display: grid; place-items: center;
    font-size: 36px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    position: relative;
    z-index: 1;
    border: 3px solid rgba(255,255,255,.12);
    transition: transform .3s;
}
.officer-card:hover .officer-photo img,
.officer-card:hover .officer-initial {
    transform: scale(1.05);
}
.officer-glow {
    position: absolute;
    inset: -8px;
    border-radius: 36px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    filter: blur(18px);
    opacity: .35;
    z-index: 0;
    transition: opacity .4s;
}
.officer-card:hover .officer-glow { opacity: .7; }
.officer-card h4 {
    font-size: 15px; font-weight: 800;
    margin-bottom: 8px;
    position: relative; z-index: 1;
    transition: color .3s;
}
.officer-card:hover h4 { color: var(--acc); }
.officer-position {
    display: inline-block;
    font-size: 10.5px; font-weight: 800;
    letter-spacing: .8px; text-transform: uppercase;
    color: #67e8f9;
    background: rgba(34,211,238,.1);
    border: 1px solid rgba(34,211,238,.3);
    padding: 5px 12px; border-radius: 99px;
    position: relative; z-index: 1;
}

/* ---- Officer Profile Button ---- */
.about-officer-btn {
    margin-top: 14px;
    width: 100%;
    justify-content: center;
    font-size: 12px;
    position: relative; z-index: 1;
}
.about-officer-btn:hover {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border-color: transparent;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99,102,241,.3);
}

/* ---- Modal Officer (ANTI-BOCOR) ---- */
.officer-lightbox {
    position: fixed; inset: 0; z-index: 130;
    background: rgba(4,8,20,.92);
    backdrop-filter: blur(16px);
    display: none;
    place-items: center;
    padding: 40px;
}
.officer-lightbox.show {
    display: grid;
    animation: lb-fade .3s ease;
}
@keyframes lb-fade { from { opacity: 0; } to { opacity: 1; } }
.officer-profile-card {
    max-width: 520px; width: 100%;
    padding: 44px 38px;
    border-radius: var(--rad-lg);
    background: rgba(15, 21, 48, .95);
    border: 1px solid var(--glass-brd);
    backdrop-filter: blur(24px);
    text-align: center;
    position: relative;
    animation: zoom-in .4s var(--ease-elastic);
    box-shadow: 0 40px 100px rgba(0,0,0,.6);
}
@keyframes zoom-in {
    from { transform: scale(.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.officer-profile-photo {
    width: 130px; height: 130px;
    border-radius: 34px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid; place-items: center;
    font-size: 48px; font-weight: 900; color: #fff;
    overflow: hidden;
    border: 3px solid rgba(255,255,255,.15);
    box-shadow: 0 20px 50px rgba(99,102,241,.45);
}
.officer-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
.officer-profile-card h2 {
    font-size: 24px; font-weight: 800; margin-bottom: 8px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.officer-profile-card .officer-position {
    margin-bottom: 18px;
}
.officer-profile-card p {
    margin-top: 18px;
    color: var(--txt-1); font-size: 14px; line-height: 1.75;
    text-align: left; padding-top: 18px;
    border-top: 1px solid var(--glass-brd);
}

/* ---- CTA Section ---- */
.about-cta {
    text-align: center;
    padding: 56px 44px;
    background:
        radial-gradient(700px 280px at 50% -20%, rgba(99,102,241,.25), transparent 70%),
        radial-gradient(500px 200px at 20% 120%, rgba(34,211,238,.15), transparent 60%),
        var(--glass);
    border: 1px solid rgba(99,102,241,.25);
    position: relative;
    overflow: hidden;
}
.about-cta-decor {
    position: absolute;
    inset: 0;
    pointer-events: none;
    color: rgba(99,102,241,.08);
    font-size: 80px;
    display: flex;
    justify-content: space-around;
    align-items: center;
}
.about-cta-decor i {
    animation: cta-float 8s ease-in-out infinite;
}
.about-cta-decor i:nth-child(2) { animation-delay: -2.5s; }
.about-cta-decor i:nth-child(3) { animation-delay: -5s; }
@keyframes cta-float {
    0%, 100% { transform: translateY(0) rotate(0deg); opacity: .08; }
    50% { transform: translateY(-20px) rotate(10deg); opacity: .15; }
}
.cta-content {
    position: relative;
    z-index: 1;
}
.cta-content .page-eyebrow {
    display: inline-block;
    margin-bottom: 12px;
}
.cta-content h2 {
    font-size: 30px; font-weight: 800;
    margin-bottom: 14px;
    letter-spacing: -.8px;
    background: linear-gradient(135deg, #fff, #c7d2fe);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
}
.cta-content p {
    color: var(--txt-1); font-size: 15px; line-height: 1.7;
    max-width: 54ch; margin: 0 auto 28px;
}
.cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .about-stats { gap: 20px; padding: 24px 32px; flex-wrap: wrap; }
    .about-stat-sep { display: none; }
    .about-stat { flex: 1; min-width: 120px; }
}
@media (max-width: 720px) {
    .about-hero { padding-top: 120px; }
    .about-stats {
        flex-direction: column;
        gap: 18px;
        padding: 24px;
    }
    .about-stat-sep { width: 40px; height: 1px; display: block; margin: 0 auto; }
    .about-story { flex-direction: column; text-align: center; padding: 32px 24px; gap: 18px; }
    .about-story-icon { margin: 0 auto; }
    .vm-card { flex-direction: column; text-align: center; gap: 14px; }
    .vm-icon { margin: 0 auto; }
    .motto-band { flex-direction: column; text-align: center; padding: 28px 24px; gap: 16px; }
    .motto-decor { flex-direction: row; justify-content: center; gap: 20px; }
    .motto-content p { font-size: 17px; }
    .division-header { flex-wrap: wrap; }
    .division-count { margin-left: auto; }
    .about-cta { padding: 40px 24px; }
    .cta-content h2 { font-size: 24px; }
    .officer-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; }
    .officer-photo { width: 80px; height: 80px; }
    .officer-photo img, .officer-initial { font-size: 28px; border-radius: 22px; }
    .officer-profile-card { padding: 32px 24px; }
    .officer-profile-photo { width: 100px; height: 100px; border-radius: 28px; font-size: 38px; }
}
@media (max-width: 520px) {
    .values-grid { grid-template-columns: 1fr 1fr; gap: 12px; }
    .value-card { padding: 24px 18px; }
    .value-icon { width: 48px; height: 48px; font-size: 22px; }
    .value-card h4 { font-size: 14px; }
    .value-card p { font-size: 12px; }
    .about-orb { filter: blur(80px); opacity: .18; }
}
</style>

<script>
(function(){
    'use strict';

    // ---- Counter animation untuk stats ----
    const counters = document.querySelectorAll('.about-stat strong[data-count]');
    if (counters.length > 0) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (!en.isIntersecting) return;
                const el = en.target;
                const target = parseInt(el.dataset.count, 10) || 0;
                const t0 = performance.now();
                const dur = 1400;
                const tick = (t) => {
                    const p = Math.min(1, (t - t0) / dur);
                    const val = Math.floor(target * (1 - Math.pow(1 - p, 3)));
                    el.textContent = val.toLocaleString('id-ID');
                    if (p < 1) requestAnimationFrame(tick);
                    else el.textContent = target.toLocaleString('id-ID');
                };
                requestAnimationFrame(tick);
                obs.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(c => obs.observe(c));
    }

    // ---- Modal Profil Pengurus (ANTI-BOCOR) ----
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
                ? '<img src="' + photo + '" alt="Foto profil ' + btn.dataset.name + '">'
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