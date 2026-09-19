<!-- File: views/pages/gallery.php -->
<section class="pub-page-head">
    <span class="page-eyebrow">Dokumentasi</span>
    <h1>Galeri Kegiatan</h1>
    <p>Kilasan momen dari berbagai kegiatan organisasi — <?= (int) $total ?> foto tercatat.</p>
</section>

<?php if (!empty($galleries)): ?>
<div class="masonry pub-articles-grid">
    <?php foreach ($galleries as $g): ?>
    <figure class="masonry-item gallery-item"
            data-full="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>"
            data-title="<?= e($g['title']) ?>">
        <img src="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
        <figcaption>
            <div class="gallery-caption">
                <strong><?= e($g['title']) ?></strong>
                <?php if (!empty($g['event_date']) || !empty($g['location'])): ?>
                <span>
                    <?php if (!empty($g['event_date'])): ?>
                        <i class="ph ph-calendar"></i> <?= date('d M Y', strtotime($g['event_date'])) ?>
                    <?php endif; ?>
                    <?php if (!empty($g['location'])): ?>
                        <i class="ph ph-map-pin"></i> <?= e($g['location']) ?>
                    <?php endif; ?>
                </span>
                <?php endif; ?>
            </div>
        </figcaption>
    </figure>
    <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
<div class="pub-pager">
    <?php if ($page > 1): ?>
        <a class="btn btn-ghost btn-sm" href="<?= url('galeri?halaman=' . ($page - 1)) ?>">
            <i class="ph ph-caret-left"></i><span class="btn-text">Sebelumnya</span>
        </a>
    <?php endif; ?>
    <span class="pager-current"><?= $page ?> / <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a class="btn btn-ghost btn-sm" href="<?= url('galeri?halaman=' . ($page + 1)) ?>">
            <span class="btn-text">Berikutnya</span><i class="ph ph-caret-right"></i>
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="glass-card pub-empty" style="max-width:640px;margin:0 auto 80px;">
    <i class="ph ph-image"></i>
    <p>Belum ada dokumentasi kegiatan.</p>
</div>
<?php endif; ?>

<style>
.gallery-caption {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.gallery-caption strong { font-size: 13px; font-weight: 700; }
.gallery-caption span {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: rgba(255,255,255,.75);
    font-weight: 600;
}
.gallery-caption i { font-size: 12px; }
</style>