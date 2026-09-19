<?php
/**
 * ============================================================
 * HALAMAN DETAIL ARTIKEL — ULTIMATE EDITION v5.9
 * Tampilan artikel dengan reading progress, auto-TOC,
 * share buttons, estimated read time, dan related articles.
 * ============================================================
 */

/** Renderer embed YouTube / audio dari isi artikel */
function render_article_content(string $content): string {
    $html = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

    // YouTube: https://youtu.be/ID atau https://www.youtube.com/watch?v=ID
    $html = preg_replace_callback(
        '#(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com/watch\?v=)([A-Za-z0-9_-]+)#i',
        fn($m) => '<div class="article-embed article-embed-youtube">
            <iframe src="https://www.youtube.com/embed/' . $m[1] . '"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen loading="lazy"></iframe></div>',
        $html
    );

    // Audio: https://.../file.mp3
    $html = preg_replace_callback(
        '#(https?://[^\s<]+\.(mp3|ogg|wav))#i',
        fn($m) => '<div class="article-embed article-embed-audio">
            <i class="ph ph-music-note"></i>
            <audio controls><source src="' . $m[1] . '">Browser tidak mendukung audio.</audio>
        </div>',
        $html
    );

    return $html;
}

// Hitung estimated read time (200 kata per menit)
$wordCount = str_word_count(strip_tags($article['content'] ?? ''));
$readTime = max(1, ceil($wordCount / 200));

// Generate slug untuk share
$shareUrl = urlencode(rtrim(BASE_URL, '/') . '/artikel/' . $article['id']);
$shareTitle = urlencode($article['title']);
$shareExcerpt = urlencode($article['excerpt'] ?? '');

$categorySlug = strtolower(str_replace(' ', '-', $article['category'] ?? 'umum'));
$articleDate = strtotime($article['created_at']);
?>

<!-- ========== READING PROGRESS BAR ========== -->
<div class="reading-progress" id="readingProgress" aria-hidden="true">
    <div class="reading-progress-bar"></div>
</div>

<!-- ========== ARTICLE CONTAINER ========== -->
<article class="article-detail-wrap">

    <!-- Back link -->
    <div class="article-detail-nav reveal">
        <a href="<?= url('artikel') ?>" class="article-back-link">
            <i class="ph ph-arrow-left"></i>
            <span>Kembali ke Semua Artikel</span>
        </a>
        <div class="article-breadcrumb">
            <a href="<?= url('') ?>">Beranda</a>
            <i class="ph ph-caret-right"></i>
            <a href="<?= url('artikel') ?>">Artikel</a>
            <i class="ph ph-caret-right"></i>
            <span><?= e($article['category']) ?></span>
        </div>
    </div>

    <!-- ========== ARTICLE HEADER ========== -->
    <header class="article-detail-header reveal">
        <!-- Category + Read time -->
        <div class="article-detail-badges">
            <a href="<?= url('artikel?kategori=' . urlencode($article['category'])) ?>" class="article-category-badge cat-<?= e($categorySlug) ?>">
                <i class="ph ph-tag"></i>
                <span><?= e($article['category']) ?></span>
            </a>
            <span class="article-read-time">
                <i class="ph ph-clock"></i>
                <span><?= $readTime ?> menit baca</span>
            </span>
            <span class="article-word-count">
                <i class="ph ph-text-aa"></i>
                <span><?= number_format($wordCount) ?> kata</span>
            </span>
        </div>

        <!-- Title -->
        <h1 class="article-detail-title"><?= e($article['title']) ?></h1>

        <!-- Excerpt / Lead -->
        <?php if (!empty($article['excerpt'])): ?>
            <p class="article-detail-lead"><?= e($article['excerpt']) ?></p>
        <?php endif; ?>

        <!-- Author + Meta -->
        <div class="article-detail-meta">
            <div class="article-author">
                <div class="article-author-avatar">
                    <?= strtoupper(substr($article['author_name'] ?? 'R', 0, 1)) ?>
                </div>
                <div class="article-author-info">
                    <strong><?= e($article['author_name'] ?? 'Redaksi') ?></strong>
                    <span>Penulis</span>
                </div>
            </div>
            <div class="article-meta-sep"></div>
            <div class="article-meta-item">
                <i class="ph ph-calendar-blank"></i>
                <time datetime="<?= date('Y-m-d', $articleDate) ?>">
                    <?= date('d M Y', $articleDate) ?>
                </time>
            </div>
            <div class="article-meta-item">
                <i class="ph ph-clock"></i>
                <span><?= date('H:i', $articleDate) ?> WIB</span>
            </div>
        </div>

        <!-- Share & Actions Bar -->
        <div class="article-detail-actions">
            <div class="article-share-group">
                <span class="article-share-label">Bagikan:</span>
                <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" 
                   target="_blank" rel="noopener" class="article-share-btn" aria-label="Bagikan ke Twitter">
                    <i class="ph ph-x-logo"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" 
                   target="_blank" rel="noopener" class="article-share-btn" aria-label="Bagikan ke Facebook">
                    <i class="ph ph-facebook-logo"></i>
                </a>
                <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" 
                   target="_blank" rel="noopener" class="article-share-btn" aria-label="Bagikan ke WhatsApp">
                    <i class="ph ph-whatsapp-logo"></i>
                </a>
                <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" 
                   target="_blank" rel="noopener" class="article-share-btn" aria-label="Bagikan ke Telegram">
                    <i class="ph ph-telegram-logo"></i>
                </a>
                <button class="article-share-btn" id="btnCopyLink" aria-label="Salin tautan">
                    <i class="ph ph-link"></i>
                </button>
            </div>
            <div class="article-extra-actions">
                <button class="article-action-btn" id="btnBookmark" aria-label="Simpan artikel">
                    <i class="ph ph-bookmark-simple"></i>
                    <span>Simpan</span>
                </button>
                <button class="article-action-btn" onclick="window.print()" aria-label="Cetak">
                    <i class="ph ph-printer"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </header>

    <!-- ========== ARTICLE COVER ========== -->
    <div class="article-detail-cover cat-<?= e($categorySlug) ?> reveal">
        <div class="article-cover-pattern"></div>
        <i class="ph ph-newspaper"></i>
        <div class="article-cover-overlay">
            <span class="article-cover-category"><?= e($article['category']) ?></span>
        </div>
    </div>

    <!-- ========== ARTICLE BODY (2 column: TOC + Content) ========== -->
    <div class="article-detail-body-wrap">
        
        <!-- Table of Contents (Sticky Sidebar) -->
        <aside class="article-toc" id="articleToc">
            <div class="article-toc-head">
                <i class="ph ph-list-bullets"></i>
                <span>Daftar Isi</span>
            </div>
            <nav class="article-toc-nav" id="tocNav">
                <!-- Auto-filled by JS -->
                <div class="article-toc-empty">
                    <i class="ph ph-text-align-left"></i>
                    <span>Daftar isi otomatis</span>
                </div>
            </nav>
            <div class="article-toc-progress">
                <div class="article-toc-progress-bar" id="tocProgressBar"></div>
            </div>
        </aside>

        <!-- Article Content -->
        <div class="article-detail-content-wrap">
            <div class="article-detail-content glass-card" id="articleContent">
                <div class="article-content-inner">
                    <?= render_article_content($article['content']) ?>
                </div>
                
                <!-- Article Tags -->
                <div class="article-tags">
                    <i class="ph ph-hash"></i>
                    <span class="article-tags-label">Topik:</span>
                    <a href="<?= url('artikel?kategori=' . urlencode($article['category'])) ?>" class="article-tag">
                        <?= e($article['category']) ?>
                    </a>
                    <a href="<?= url('artikel') ?>" class="article-tag">Artikel</a>
                    <a href="<?= url('') ?>" class="article-tag"><?= e(APP_NAME) ?></a>
                </div>
            </div>

            <!-- Author Card -->
            <section class="article-author-card glass-card reveal">
                <div class="author-card-avatar">
                    <?= strtoupper(substr($article['author_name'] ?? 'R', 0, 1)) ?>
                </div>
                <div class="author-card-info">
                    <span class="author-card-label">Ditulis oleh</span>
                    <h3><?= e($article['author_name'] ?? 'Redaksi') ?></h3>
                    <p>Penulis aktif di <?= e(APP_NAME) ?>. Menyukai berbagi informasi dan cerita inspiratif dari kegiatan organisasi.</p>
                    <div class="author-card-meta">
                        <span><i class="ph ph-newspaper"></i> Penulis Redaksi</span>
                    </div>
                </div>
            </section>

            <!-- Article Navigation (Prev/Next) -->
            <?php if (!empty($prevArticle) || !empty($nextArticle)): ?>
            <nav class="article-nav reveal">
                <?php if (!empty($prevArticle)): ?>
                <a href="<?= url('artikel/' . (int) $prevArticle['id']) ?>" class="article-nav-item prev">
                    <span class="article-nav-label">
                        <i class="ph ph-arrow-left"></i> Sebelumnya
                    </span>
                    <strong><?= e($prevArticle['title']) ?></strong>
                </a>
                <?php else: ?>
                <div class="article-nav-item disabled"></div>
                <?php endif; ?>
                
                <?php if (!empty($nextArticle)): ?>
                <a href="<?= url('artikel/' . (int) $nextArticle['id']) ?>" class="article-nav-item next">
                    <span class="article-nav-label">
                        Berikutnya <i class="ph ph-arrow-right"></i>
                    </span>
                    <strong><?= e($nextArticle['title']) ?></strong>
                </a>
                <?php else: ?>
                <div class="article-nav-item disabled"></div>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== RELATED ARTICLES ========== -->
    <?php if (!empty($related)): ?>
    <section class="article-related reveal">
        <div class="article-related-head">
            <div class="article-related-title">
                <i class="ph ph-sparkle"></i>
                <h2>Artikel Lainnya</h2>
            </div>
            <a href="<?= url('artikel') ?>" class="article-related-link">
                Lihat Semua <i class="ph ph-arrow-right"></i>
            </a>
        </div>
        <div class="pub-articles">
            <?php foreach ($related as $r): 
                $relCatSlug = strtolower(str_replace(' ', '-', $r['category']));
            ?>
            <a href="<?= url('artikel/' . (int) $r['id']) ?>" class="glass-card pub-article-card">
                <div class="pac-cover cat-<?= e($relCatSlug) ?>">
                    <i class="ph ph-newspaper"></i>
                    <span><?= e($r['category']) ?></span>
                </div>
                <div class="pac-body">
                    <div class="pac-meta">
                        <time><?= date('d M Y', strtotime($r['created_at'])) ?></time>
                        <span class="pac-read-time">
                            <i class="ph ph-clock"></i>
                            <?= max(1, ceil(str_word_count(strip_tags($r['content'] ?? '')) / 200)) ?> min
                        </span>
                    </div>
                    <h3><?= e($r['title']) ?></h3>
                    <?php if (!empty($r['excerpt'])): ?>
                    <p><?= e(mb_strimwidth($r['excerpt'], 0, 80, '…')) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</article>

<!-- ========== FLOATING SHARE (Mobile) ========== -->
<div class="article-floating-share" id="floatingShare">
    <button class="floating-share-btn" id="btnFloatingShare" aria-label="Bagikan">
        <i class="ph ph-share-network"></i>
    </button>
</div>

<!-- ========== SHARE MODAL (Mobile) ========== -->
<div class="share-modal" id="shareModal">
    <div class="share-modal-backdrop" id="shareModalBackdrop"></div>
    <div class="share-modal-content">
        <div class="share-modal-head">
            <h3>Bagikan Artikel</h3>
            <button class="share-modal-close" id="shareModalClose">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="share-modal-options">
            <a href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" class="share-option">
                <span class="share-option-icon twitter"><i class="ph ph-x-logo"></i></span>
                <span>Twitter</span>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-option">
                <span class="share-option-icon facebook"><i class="ph ph-facebook-logo"></i></span>
                <span>Facebook</span>
            </a>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener" class="share-option">
                <span class="share-option-icon whatsapp"><i class="ph ph-whatsapp-logo"></i></span>
                <span>WhatsApp</span>
            </a>
            <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" rel="noopener" class="share-option">
                <span class="share-option-icon telegram"><i class="ph ph-telegram-logo"></i></span>
                <span>Telegram</span>
            </a>
            <button class="share-option" id="btnCopyLink2">
                <span class="share-option-icon copy"><i class="ph ph-link"></i></span>
                <span>Salin Tautan</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== STYLING ========== -->
<style>
/* ---- Reading Progress Bar ---- */
.reading-progress {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(255,255,255,.05);
    z-index: 100;
}
.reading-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    transition: width 0.1s;
    box-shadow: 0 0 10px var(--acc);
}

/* ---- Article Container ---- */
.article-detail-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 120px 24px 80px;
    position: relative;
    z-index: 1;
}

/* ---- Article Nav ---- */
.article-detail-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.article-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 99px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}
.article-back-link:hover {
    color: var(--txt-0);
    border-color: var(--pri);
    background: rgba(99,102,241,.12);
    transform: translateX(-4px);
}
.article-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--txt-2);
}
.article-breadcrumb a {
    color: var(--txt-2);
    transition: color 0.2s;
}
.article-breadcrumb a:hover { color: var(--txt-0); }
.article-breadcrumb i { font-size: 10px; }
.article-breadcrumb span { color: var(--txt-1); font-weight: 600; }

/* ---- Article Header ---- */
.article-detail-header {
    margin-bottom: 36px;
}
.article-detail-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.article-category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fff;
    transition: transform 0.2s;
}
.article-category-badge:hover { transform: translateY(-2px); }
.article-category-badge i { font-size: 12px; }
.article-read-time,
.article-word-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.article-read-time i,
.article-word-count i {
    font-size: 13px;
    color: var(--acc);
}

.article-detail-title {
    font-size: clamp(30px, 5vw, 46px);
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -1.2px;
    margin-bottom: 20px;
    background: linear-gradient(180deg, #ffffff 0%, #c7d2fe 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.article-detail-lead {
    font-size: 18px;
    font-weight: 500;
    line-height: 1.65;
    color: var(--txt-1);
    margin-bottom: 28px;
    max-width: 60ch;
    font-style: italic;
}

/* Author + Meta */
.article-detail-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--glass-brd);
    flex-wrap: wrap;
}
.article-author {
    display: flex;
    align-items: center;
    gap: 12px;
}
.article-author-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 6px 16px rgba(99,102,241,.3);
}
.article-author-info strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
}
.article-author-info span {
    font-size: 12px;
    color: var(--txt-2);
}
.article-meta-sep {
    width: 1px;
    height: 30px;
    background: var(--glass-brd);
}
.article-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--txt-1);
}
.article-meta-item i {
    color: var(--acc);
    font-size: 15px;
}

/* Actions bar */
.article-detail-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    flex-wrap: wrap;
}
.article-share-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.article-share-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--txt-2);
    margin-right: 4px;
}
.article-share-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    display: grid;
    place-items: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}
.article-share-btn:hover {
    color: #fff;
    border-color: var(--pri);
    background: rgba(99,102,241,.15);
    transform: translateY(-2px);
}
.article-extra-actions {
    display: flex;
    gap: 8px;
}
.article-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 10px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.article-action-btn:hover {
    color: var(--txt-0);
    border-color: var(--glass-brd-2);
    background: rgba(255,255,255,.08);
}
.article-action-btn i { font-size: 15px; }
.article-action-btn.bookmarked {
    color: #fbbf24;
    border-color: rgba(245,158,11,.3);
    background: rgba(245,158,11,.1);
}

/* ---- Article Cover ---- */
.article-detail-cover {
    position: relative;
    height: 320px;
    border-radius: 24px;
    display: grid;
    place-items: center;
    font-size: 80px;
    color: rgba(255,255,255,.5);
    margin-bottom: 40px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(2,6,23,.5);
}
.article-cover-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 25px 25px, 35px 35px;
    opacity: 0.8;
}
.article-cover-overlay {
    position: absolute;
    bottom: 20px;
    left: 20px;
    z-index: 2;
}
.article-cover-category {
    padding: 6px 14px;
    border-radius: 99px;
    background: rgba(10,15,31,.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fff;
}

/* ---- Article Body (2 column layout) ---- */
.article-detail-body-wrap {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 40px;
    align-items: start;
    margin-bottom: 60px;
}

/* TOC Sidebar */
.article-toc {
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
}
.article-toc-head {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    margin-bottom: 12px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
}
.article-toc-head i {
    font-size: 15px;
    color: var(--acc);
}
.article-toc-nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.article-toc-link {
    display: block;
    padding: 8px 16px;
    border-left: 2px solid transparent;
    font-size: 12.5px;
    color: var(--txt-1);
    transition: all 0.2s;
    text-decoration: none;
    line-height: 1.4;
}
.article-toc-link:hover {
    color: var(--txt-0);
    border-left-color: var(--glass-brd-2);
    background: rgba(255,255,255,.02);
}
.article-toc-link.active {
    color: var(--acc);
    border-left-color: var(--acc);
    background: rgba(34,211,238,.05);
}
.article-toc-empty {
    padding: 16px;
    text-align: center;
    color: var(--txt-2);
    font-size: 12px;
}
.article-toc-empty i {
    font-size: 24px;
    display: block;
    margin-bottom: 6px;
    color: var(--txt-2);
}
.article-toc-progress {
    margin-top: 16px;
    height: 3px;
    background: rgba(255,255,255,.05);
    border-radius: 2px;
    overflow: hidden;
}
.article-toc-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    transition: width 0.1s;
}

/* Content */
.article-detail-content {
    padding: 40px;
    position: relative;
}
.article-content-inner {
    font-size: 15.5px;
    line-height: 1.85;
    color: var(--txt-1);
}
.article-content-inner h2 {
    font-size: 24px;
    font-weight: 800;
    color: var(--txt-0);
    margin: 40px 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--glass-brd);
    scroll-margin-top: 100px;
}
.article-content-inner h3 {
    font-size: 19px;
    font-weight: 700;
    color: var(--txt-0);
    margin: 32px 0 12px;
    scroll-margin-top: 100px;
}
.article-content-inner p {
    margin-bottom: 18px;
}
.article-content-inner strong,
.article-content-inner b {
    color: var(--txt-0);
    font-weight: 700;
}
.article-content-inner a {
    color: var(--acc);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.article-content-inner a:hover {
    color: #67e8f9;
}
.article-content-inner ul,
.article-content-inner ol {
    margin: 16px 0;
    padding-left: 28px;
}
.article-content-inner li {
    margin-bottom: 8px;
}
.article-content-inner blockquote {
    border-left: 3px solid var(--acc);
    padding: 12px 20px;
    margin: 20px 0;
    background: rgba(34,211,238,.05);
    border-radius: 0 10px 10px 0;
    font-style: italic;
    color: var(--txt-0);
}
.article-content-inner code {
    background: rgba(255,255,255,.08);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 14px;
    font-family: 'JetBrains Mono', monospace;
}
.article-content-inner pre {
    background: rgba(0,0,0,.3);
    padding: 16px 20px;
    border-radius: 10px;
    overflow-x: auto;
    margin: 20px 0;
}
.article-content-inner pre code {
    background: none;
    padding: 0;
}
.article-content-inner img {
    max-width: 100%;
    border-radius: 12px;
    margin: 20px 0;
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}

/* Embeds */
.article-embed {
    margin: 28px 0;
    border-radius: 14px;
    overflow: hidden;
}
.article-embed-youtube {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    box-shadow: 0 20px 50px rgba(2,6,23,.5);
}
.article-embed-youtube iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}
.article-embed-audio {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
}
.article-embed-audio i {
    font-size: 28px;
    color: var(--acc);
    flex-shrink: 0;
}
.article-embed-audio audio {
    flex: 1;
    border-radius: 8px;
}

/* Tags */
.article-tags {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 24px;
    margin-top: 32px;
    border-top: 1px solid var(--glass-brd);
}
.article-tags i {
    font-size: 18px;
    color: var(--acc);
}
.article-tags-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--txt-2);
    margin-right: 4px;
}
.article-tag {
    padding: 5px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--glass-brd);
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
    transition: all 0.2s;
}
.article-tag:hover {
    color: var(--txt-0);
    border-color: var(--pri);
    background: rgba(99,102,241,.12);
}

/* ---- Author Card ---- */
.article-author-card {
    display: flex;
    gap: 24px;
    padding: 28px;
    margin-bottom: 32px;
    align-items: center;
}
.author-card-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 12px 30px rgba(99,102,241,.35);
}
.author-card-info {
    flex: 1;
    min-width: 0;
}
.author-card-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
    margin-bottom: 4px;
}
.author-card-info h3 {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 6px;
}
.author-card-info p {
    font-size: 13.5px;
    color: var(--txt-1);
    line-height: 1.6;
    margin-bottom: 10px;
}
.author-card-meta {
    display: flex;
    gap: 14px;
    font-size: 12px;
    color: var(--txt-2);
}
.author-card-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.author-card-meta i { color: var(--acc); }

/* ---- Article Nav (Prev/Next) ---- */
.article-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 48px;
}
.article-nav-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 20px;
    border-radius: 16px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
    text-decoration: none;
}
.article-nav-item:hover:not(.disabled) {
    border-color: var(--glass-brd-2);
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(2,6,23,.4);
}
.article-nav-item.next {
    text-align: right;
}
.article-nav-item.disabled {
    opacity: 0;
    pointer-events: none;
}
.article-nav-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--acc);
}
.article-nav-item strong {
    font-size: 14px;
    font-weight: 700;
    color: var(--txt-0);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ---- Related Articles ---- */
.article-related {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid var(--glass-brd);
}
.article-related-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}
.article-related-title {
    display: flex;
    align-items: center;
    gap: 12px;
}
.article-related-title i {
    font-size: 24px;
    color: var(--warn);
}
.article-related-title h2 {
    font-size: 22px;
    font-weight: 800;
}
.article-related-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--acc);
    transition: all 0.2s;
}
.article-related-link:hover {
    color: #fff;
    gap: 10px;
}

/* Pac enhancements */
.pac-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}
.pac-meta time {
    color: var(--txt-2);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.pac-read-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--txt-2);
    padding-left: 12px;
    border-left: 1px solid var(--glass-brd);
}
.pac-read-time i {
    font-size: 12px;
    color: var(--acc);
}

/* ---- Floating Share (Mobile) ---- */
.article-floating-share {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 40;
    display: none;
}
.floating-share-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border: none;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    display: grid;
    place-items: center;
    box-shadow: 0 12px 30px rgba(99,102,241,.4);
    transition: all 0.3s;
}
.floating-share-btn:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 16px 40px rgba(34,211,238,.4);
}

/* ---- Share Modal ---- */
.share-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.share-modal.show {
    opacity: 1;
    visibility: visible;
}
.share-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4,8,20,.7);
    backdrop-filter: blur(8px);
}
.share-modal-content {
    position: relative;
    width: 100%;
    max-width: 400px;
    background: var(--bg-1);
    border: 1px solid var(--glass-brd);
    border-radius: 24px 24px 0 0;
    overflow: hidden;
    transform: translateY(100%);
    transition: transform 0.4s cubic-bezier(.22,1,.36,1);
}
.share-modal.show .share-modal-content {
    transform: translateY(0);
}
.share-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--glass-brd);
}
.share-modal-head h3 {
    font-size: 16px;
    font-weight: 800;
}
.share-modal-close {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 16px;
    transition: all 0.2s;
}
.share-modal-close:hover {
    background: rgba(239,68,68,.15);
    color: #fca5a5;
}
.share-modal-options {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    padding: 24px;
}
.share-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 14px 8px;
    border-radius: 14px;
    background: transparent;
    border: none;
    color: var(--txt-0);
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.share-option:hover {
    background: rgba(255,255,255,.05);
    transform: translateY(-2px);
}
.share-option-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
}
.share-option-icon.twitter { background: #1da1f2; }
.share-option-icon.facebook { background: #1877f2; }
.share-option-icon.whatsapp { background: #25d366; }
.share-option-icon.telegram { background: #0088cc; }
.share-option-icon.copy { background: linear-gradient(135deg, var(--pri), var(--acc)); }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .article-detail-body-wrap {
        grid-template-columns: 1fr;
    }
    .article-toc {
        display: none;
    }
    .article-floating-share {
        display: block;
    }
    .article-share-group {
        display: none;
    }
}

@media (max-width: 768px) {
    .article-detail-wrap {
        padding: 100px 16px 60px;
    }
    .article-detail-cover {
        height: 200px;
        font-size: 60px;
    }
    .article-detail-content {
        padding: 24px 20px;
    }
    .article-detail-meta {
        flex-direction: column;
        align-items: flex-start;
    }
    .article-meta-sep { display: none; }
    .article-nav {
        grid-template-columns: 1fr;
    }
    .article-author-card {
        flex-direction: column;
        text-align: center;
    }
    .share-modal-options {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* ---- Print Styles ---- */
@media print {
    .reading-progress,
    .article-detail-nav,
    .article-detail-actions,
    .article-toc,
    .article-floating-share,
    .article-related,
    .article-nav { display: none !important; }
    
    body { background: #fff; color: #000; }
    .article-detail-wrap { max-width: 100%; padding: 20px; }
    .glass-card { background: #fff; border: 1px solid #ddd; box-shadow: none; }
    .article-detail-title { color: #000; -webkit-text-fill-color: #000; }
    .article-content-inner { color: #333; }
}
</style>

<script>
(() => {
    'use strict';
    
    const articleContent = document.getElementById('articleContent');
    const readingBar = document.querySelector('.reading-progress-bar');
    const tocNav = document.getElementById('tocNav');
    const tocProgressBar = document.getElementById('tocProgressBar');
    
    if (!articleContent) return;
    
    // ---- 1. Reading Progress + TOC Progress ----
    function updateProgress() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = Math.min((scrollTop / docHeight) * 100, 100);
        
        if (readingBar) readingBar.style.width = progress + '%';
        if (tocProgressBar) tocProgressBar.style.width = progress + '%';
    }
    window.addEventListener('scroll', updateProgress, { passive: true });
    
    // ---- 2. Auto-generate TOC from H2/H3 ----
    const headings = articleContent.querySelectorAll('h2, h3');
    if (headings.length > 0 && tocNav) {
        tocNav.innerHTML = '';
        headings.forEach((h, i) => {
            const id = 'section-' + i;
            h.id = id;
            
            const link = document.createElement('a');
            link.href = '#' + id;
            link.className = 'article-toc-link' + (h.tagName === 'H3' ? ' sub' : '');
            link.textContent = h.textContent;
            link.style.paddingLeft = h.tagName === 'H3' ? '28px' : '16px';
            link.style.fontSize = h.tagName === 'H3' ? '11.5px' : '12.5px';
            tocNav.appendChild(link);
        });
    }
    
    // ---- 3. Active TOC link on scroll ----
    const tocLinks = document.querySelectorAll('.article-toc-link');
    if (tocLinks.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    tocLinks.forEach(link => {
                        link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                    });
                }
            });
        }, { rootMargin: '-100px 0px -70% 0px' });
        
        headings.forEach(h => observer.observe(h));
    }
    
    // ---- 4. Copy link ----
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            if (window.toast) window.toast('Tautan berhasil disalin!', 'success');
        });
    }
    document.getElementById('btnCopyLink')?.addEventListener('click', copyLink);
    document.getElementById('btnCopyLink2')?.addEventListener('click', copyLink);
    
    // ---- 5. Bookmark toggle ----
    const bookmarkBtn = document.getElementById('btnBookmark');
    if (bookmarkBtn) {
        const saved = localStorage.getItem('bookmarked_' + <?= (int)$article['id'] ?>);
        if (saved) bookmarkBtn.classList.add('bookmarked');
        
        bookmarkBtn.addEventListener('click', () => {
            bookmarkBtn.classList.toggle('bookmarked');
            const isBookmarked = bookmarkBtn.classList.contains('bookmarked');
            if (isBookmarked) {
                localStorage.setItem('bookmarked_' + <?= (int)$article['id'] ?>, '1');
                if (window.toast) window.toast('Artikel disimpan ke bookmark.', 'success');
            } else {
                localStorage.removeItem('bookmarked_' + <?= (int)$article['id'] ?>);
            }
        });
    }
    
    // ---- 6. Share modal (mobile) ----
    const shareModal = document.getElementById('shareModal');
    const btnFloatingShare = document.getElementById('btnFloatingShare');
    const shareModalBackdrop = document.getElementById('shareModalBackdrop');
    const shareModalClose = document.getElementById('shareModalClose');
    
    function openShareModal() { shareModal?.classList.add('show'); }
    function closeShareModal() { shareModal?.classList.remove('show'); }
    
    btnFloatingShare?.addEventListener('click', openShareModal);
    shareModalBackdrop?.addEventListener('click', closeShareModal);
    shareModalClose?.addEventListener('click', closeShareModal);
})();
</script>