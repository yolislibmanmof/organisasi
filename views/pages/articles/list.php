<!-- File: views/pages/articles/list.php -->
<section class="pub-page-head">
    <span class="page-eyebrow">Publikasi</span>
    <h1>Artikel & Informasi</h1>
    <p>Kabar, edukasi, dan cerita dari kegiatan organisasi — terbuka untuk umum.</p>
</section>

<div class="cat-chips">
    <a href="<?= url('artikel') ?>" class="chip <?= $active === '' ? 'chip-active' : '' ?>">Semua</a>
    <?php foreach ($categories as $c): ?>
        <a href="<?= url('artikel?kategori=' . urlencode($c)) ?>"
           class="chip <?= $active === $c ? 'chip-active' : '' ?>"><?= e($c) ?></a>
    <?php endforeach; ?>
</div>

<?php if (!empty($articles)): ?>
    <div class="pub-articles pub-articles-grid">
        <?php foreach ($articles as $a): ?>
            <a href="<?= url('artikel/' . (int) $a['id']) ?>" class="glass-card pub-article-card reveal revealed">
                <div class="pac-cover cat-<?= strtolower(str_replace(' ', '-', $a['category'])) ?>">
                    <i class="ph ph-newspaper"></i>
                    <span><?= e($a['category']) ?></span>
                </div>
                <div class="pac-body">
                    <time><?= date('d M Y', strtotime($a['created_at'])) ?></time>
                    <h3><?= e($a['title']) ?></h3>
                    <p><?= e(mb_strimwidth((string) ($a['excerpt'] ?? ''), 0, 110, '…')) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="pub-pager">
        <?php if ($page > 1): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . ($page - 1)) ?>">
                <i class="ph ph-caret-left"></i><span class="btn-text">Sebelumnya</span>
            </a>
        <?php endif; ?>
        <span class="pager-current"><?= $page ?> / <?= $pages ?></span>
        <?php if ($page < $pages): ?>
            <a class="btn btn-ghost btn-sm" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . ($page + 1)) ?>">
                <span class="btn-text">Berikutnya</span><i class="ph ph-caret-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="glass-card pub-empty" style="max-width:640px;margin:0 auto 80px;">
        <i class="ph ph-newspaper"></i>
        <p>Belum ada artikel pada kategori ini.</p>
    </div>
<?php endif; ?>