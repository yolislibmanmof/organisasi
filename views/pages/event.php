<!-- File: views/pages/event.php (BARU - TAHAP 5.7) -->
<section class="pub-page-head">
    <span class="page-eyebrow">Agenda</span>
    <h1>Event & Kegiatan</h1>
    <p>Seluruh kegiatan organisasi — yang sedang berlangsung, mendatang, serta arsip kegiatan lalu.</p>
</section>

<div style="max-width:1180px;margin:0 auto 80px;padding:0 24px;">

    <?php if (!empty($active)): ?>
    <h2 class="dx-ev-group">Sedang Berlangsung & Mendatang</h2>
    <div class="pub-events" style="margin-bottom:48px;">
        <?php foreach ($active as $ev): ?>
        <article class="glass-card pub-event-card">
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
    <div class="glass-card pub-empty" style="margin-bottom:48px;">
        <i class="ph ph-calendar-blank"></i>
        <p>Belum ada event yang berlangsung atau mendatang. Pantau terus halaman ini!</p>
    </div>
    <?php endif; ?>

    <?php if (!empty($done)): ?>
    <h2 class="dx-ev-group">Arsip Kegiatan Lalu</h2>
    <div class="pub-events" style="opacity:.72;">
        <?php foreach ($done as $ev): ?>
        <article class="glass-card pub-event-card">
            <div class="pec-date">
                <strong><?= date('d', strtotime($ev['event_date'])) ?></strong>
                <span><?= date('M Y', strtotime($ev['event_date'])) ?></span>
            </div>
            <div class="pec-body">
                <div class="pec-head">
                    <h3><?= e($ev['title']) ?></h3>
                    <span class="event-pill done">Selesai</span>
                </div>
                <div class="event-meta">
                    <span><i class="ph ph-clock"></i> <?= $ev['event_time'] ? e(substr($ev['event_time'], 0, 5)) . ' WIB' : '-' ?></span>
                    <span><i class="ph ph-map-pin"></i> <?= e($ev['location'] ?: '-') ?></span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
    .dx-ev-group {
        font-size: 18px; font-weight: 800;
        margin: 0 0 18px;
        display: flex; align-items: center; gap: 14px;
    }
    .dx-ev-group::after { content: ''; flex: 1; height: 1px; background: var(--glass-brd); }
</style>