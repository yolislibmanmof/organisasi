<!-- File: views/pages/articles/detail.php (FINAL - TAHAP 5.7) -->
<?php
/** Renderer embed YouTube / audio dari isi artikel */
function render_article_content(string $content): string {
    $html = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

    // YouTube: https://youtu.be/ID atau https://www.youtube.com/watch?v=ID
    $html = preg_replace_callback(
        '#(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com/watch\?v=)([A-Za-z0-9_-]+)#i',
        fn($m) => '<div style="position:relative;padding-bottom:56.25%;height:0;border-radius:14px;overflow:hidden;margin:20px 0;box-shadow:0 20px 50px rgba(2,6,23,.5);">
            <iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                    src="https://www.youtube.com/embed/' . $m[1] . '"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe></div>',
        $html
    );

    // Audio: https://.../file.mp3
    $html = preg_replace_callback(
        '#(https?://[^\s<]+\.(mp3|ogg|wav))#i',
        fn($m) => '<audio controls style="width:100%;margin:16px 0;border-radius:10px;">
            <source src="' . $m[1] . '">Browser tidak mendukung audio.</audio>',
        $html
    );

    return $html;
}
?>
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
        <div class="detail-content"><?= render_article_content($article['content']) ?></div>
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