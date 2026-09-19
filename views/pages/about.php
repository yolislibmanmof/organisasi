<!-- File: views/pages/about.php (FINAL - TAHAP 5.8) -->
<?php
    $appName = setting('app_name', 'Organisasi');
    $visi    = trim((string) setting('visi'));
    $misiRaw = (string) setting('misi');
    $misiList = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $misiRaw))));
    $motto   = trim((string) setting('motto'));
    $period  = trim((string) setting('cabinet_period'));
?>

<section class="pub-page-head">
    <span class="page-eyebrow">Tentang Kami</span>
    <h1><?= e($period !== '' ? $period : 'Profil ' . $appName) ?></h1>
    <p>Mengenal lebih dekat identitas, visi, dan perjalanan organisasi kami.</p>
</section>

<div style="max-width:880px;margin:0 auto 80px;padding:0 24px;">
    <!-- Cerita Organisasi -->
    <section class="glass-card reveal" style="padding:36px 38px;margin-bottom:24px;">
        <h2 style="font-size:20px;font-weight:800;margin-bottom:12px;">Cerita Kami</h2>
        <p style="color:var(--txt-1);line-height:1.8;font-size:14.5px;">
            <?= e($appName) ?> adalah rumah kekeluargaan yang mewadahi seluruh pelajar dan mahasiswa untuk berkembang melalui kegiatan, pelatihan, dan pengabdian masyarakat. Kami percaya bahwa pertumbuhan terbaik terjadi ketika individu-individu saling mendukung dan berkolaborasi dalam satu visi bersama.
        </p>
        <p style="color:var(--txt-1);line-height:1.8;font-size:14.5px;margin-top:12px;">
            Melalui berbagai program unggulan dan jaringan alumni lintas generasi, kami berkomitmen untuk mencetak insan akademis yang berintegritas, berkompeten, dan berdedikasi bagi kemajuan masyarakat — khususnya di daerah kami.
        </p>
    </section>

    <!-- Visi & Misi -->
    <?php if ($visi !== '' || !empty($misiList)): ?>
    <section class="about-grid reveal" style="margin-bottom:24px;">
        <?php if ($visi !== ''): ?>
        <article class="glass-card about-card">
            <div class="about-icon grad-1"><i class="ph ph-eye"></i></div>
            <h3>Visi</h3>
            <p><?= nl2br(e($visi)) ?></p>
        </article>
        <?php endif; ?>
        <?php if (!empty($misiList)): ?>
        <article class="glass-card about-card">
            <div class="about-icon grad-3"><i class="ph ph-target"></i></div>
            <h3>Misi</h3>
            <ol class="mission-list">
                <?php foreach ($misiList as $m): ?>
                    <li><?= e($m) ?></li>
                <?php endforeach; ?>
            </ol>
        </article>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($motto !== ''): ?>
    <div class="motto-band reveal" style="margin-bottom:24px;">
        <i class="ph ph-quotes"></i>
        <p><?= e($motto) ?></p>
    </div>
    <?php endif; ?>

    <!-- Struktur Pengurus Per Bidang -->
    <?php
        $officersByDiv = \Models\Officer::groupByDivision();
        if (!empty($officersByDiv)):
    ?>
    <section class="glass-card reveal" style="padding:36px 38px;">
        <h2 style="font-size:20px;font-weight:800;margin-bottom:6px;">Struktur Kepengurusan</h2>
        <p style="color:var(--txt-1);font-size:13.5px;margin-bottom:24px;">Jajaran pengurus yang memimpin perjalanan organisasi periode ini.</p>

        <?php foreach ($officersByDiv as $divName => $officers): ?>
        <div style="margin-bottom:32px;">
            <h3 style="font-size:15px;font-weight:700;color:var(--acc);margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                <i class="ph ph-buildings"></i> <?= e($divName) ?>
            </h3>
            <div class="officer-grid">
                <?php foreach ($officers as $o): ?>
                <article class="glass-card officer-card">
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
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>

<style>
    .motto-band {
        padding: 28px 36px;
        border-radius: var(--rad-lg);
        background: linear-gradient(135deg, rgba(99,102,241,.12), rgba(34,211,238,.08));
        border: 1px solid rgba(99,102,241,.25);
        display: flex;
        align-items: flex-start;
        gap: 18px;
    }
    .motto-band i { font-size: 32px; color: var(--acc); flex-shrink: 0; }
    .motto-band p { font-size: 16px; font-weight: 600; line-height: 1.7; color: var(--txt-0); font-style: italic; margin: 0; }
    @media (max-width: 720px) { .motto-band { padding: 22px; } .motto-band p { font-size: 14px; } }
</style>