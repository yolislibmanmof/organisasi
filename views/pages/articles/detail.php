<!-- File: views/pages/articles/detail.php -->
<article class="pub-detail">
    <a href="<?= url('artikel') ?>" class="back-link">
        <i class="ph ph-arrow-left"></i> Semua Artikel
    </a>

    <div class="detail-head">
        <span class="chip chip-active"><?= e($article['category']) ?></span>
        <h1><?= e($article['title']) ?></h1>
        <div class="detail-meta">
            <span><i class="ph ph-user-circle"></i> <?= e($article['author_name'] ?? 'Redaksi') ?></span>
            <span><i class="ph ph-clock"></i> <?= date('d M Y, H:i', strtotime($article['created_at'])) ?> WIB</span>
        </div>
    </div>

    <div class="detail-cover cat-<?= strtolower(str_replace(' ', '-', $article['category'])) ?>">
        <i class="ph ph-newspaper"></i>
    </div>

    <div class="detail-body glass-card">
        <?php if (!empty($article['excerpt'])): ?>
            <p class="detail-lead"><?= e($article['excerpt']) ?></p>
        <?php endif; ?>
        <div class="detail-content"><?= nl2br(e($article['content'])) ?></div>
    </div>

    <?php if (!empty($related)): ?>
        <div class="related-block">
            <h3><i class="ph ph-sparkle"></i> Artikel Lainnya</h3>
            <div class="pub-articles">
                <?php foreach ($related as $r): ?>
                    <a href="<?= url('artikel/' . (int) $r['id']) ?>" class="glass-card pub-article-card">
                        <div class="pac-cover cat-<?= strtolower(str_replace(' ', '-', $r['category'])) ?>">
                            <i class="ph ph-newspaper"></i>
                            <span><?= e($r['category']) ?></span>
                        </div>
                        <div class="pac-body">
                            <time><?= date('d M Y', strtotime($r['created_at'])) ?></time>
                            <h3><?= e($r['title']) ?></h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</article>