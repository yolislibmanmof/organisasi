<?php
/**
 * ============================================================
 * HALAMAN DETAIL ARTIKEL — ULTIMATE EDITION v7.0
 * Tampilan artikel dengan reading progress, auto-TOC collapsible,
 * share buttons, reading mode, image lightbox, dan related articles.
 * ============================================================
 */

/** Renderer embed YouTube / audio dari isi artikel */
function render_article_content(string $content): string {
    $html = nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));

    // Markdown-style headings
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    
    // Bold & Italic
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
    
    // Links [text](url)
    $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $html);

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
            <audio controls preload="metadata"><source src="' . $m[1] . '">Browser tidak mendukung audio.</audio>
        </div>',
        $html
    );
    
    // Images ![alt](url)
    $html = preg_replace('/!\[(.+?)\]\((.+?)\)/', '<figure class="article-figure"><img src="$2" alt="$1" loading="lazy"><figcaption>$1</figcaption></figure>', $html);

    return $html;
}

// Hitung estimated read time (200 kata per menit)
$wordCount = str_word_count(strip_tags($article['content'] ?? ''));
$readTime = max(1, ceil($wordCount / 200));

// Generate slug untuk share
$shareUrl = urlencode(rtrim(BASE_URL, '/') . '/artikel/' . $article['id']);
$shareTitle = urlencode($article['title']);
$shareExcerpt = urlencode($article['excerpt'] ?? '');

$categorySlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($article['category'] ?? 'umum')));
$articleDate = strtotime($article['created_at']);

// Cover image support
$coverImage = !empty($article['cover_image']) ? url('assets/uploads/articles/' . e($article['cover_image'])) : '';
$authorPhoto = !empty($article['author_photo']) ? url('assets/uploads/users/' . e($article['author_photo'])) : '';
?>

<!-- ========== STRUCTURED DATA (SEO) ========== -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": <?= json_encode($article['title']) ?>,
    "description": <?= json_encode($article['excerpt'] ?? '') ?>,
    "image": <?= json_encode($coverImage) ?>,
    "datePublished": <?= json_encode(date('c', $articleDate)) ?>,
    "author": {
        "@type": "Person",
        "name": <?= json_encode($article['author_name'] ?? 'Redaksi') ?>
    },
    "publisher": {
        "@type": "Organization",
        "name": <?= json_encode(APP_NAME) ?>
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": <?= json_encode(rtrim(BASE_URL, '/') . '/artikel/' . $article['id']) ?>
    }
}
</script>

<!-- ========== READING PROGRESS BAR ========== -->
<div class="reading-progress" id="readingProgress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="Progress membaca">
    <div class="reading-progress-bar"></div>
    <span class="reading-progress-percent" id="readingPercent">0%</span>
</div>

<!-- ========== READING MODE TOGGLE ========== -->
<button class="reading-mode-toggle" id="btnReadingMode" title="Mode baca fokus (F)" aria-label="Toggle mode baca">
    <i class="ph ph-book-open-text"></i>
</button>

<!-- ========== FONT SIZE CONTROLS ========== -->
<div class="font-size-controls" id="fontSizeControls">
    <button class="font-size-btn" id="btnFontDecrease" title="Perkecil font" aria-label="Perkecil font">
        <i class="ph ph-text-aa" style="font-size:12px"></i>
    </button>
    <span class="font-size-label" id="fontSizeLabel">100%</span>
    <button class="font-size-btn" id="btnFontIncrease" title="Perbesar font" aria-label="Perbesar font">
        <i class="ph ph-text-aa" style="font-size:18px"></i>
    </button>
</div>

<!-- ========== ARTICLE CONTAINER ========== -->
<article class="article-detail-wrap" itemscope itemtype="https://schema.org/Article">

    <!-- Back link + Breadcrumb -->
    <nav class="article-detail-nav reveal" aria-label="Navigasi artikel">
        <a href="<?= url('artikel') ?>" class="article-back-link">
            <i class="ph ph-arrow-left"></i>
            <span>Kembali ke Semua Artikel</span>
        </a>
        <ol class="article-breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?= url('') ?>"><span itemprop="name">Beranda</span></a>
                <meta itemprop="position" content="1" />
            </li>
            <li><i class="ph ph-caret-right"></i></li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a itemprop="item" href="<?= url('artikel') ?>"><span itemprop="name">Artikel</span></a>
                <meta itemprop="position" content="2" />
            </li>
            <li><i class="ph ph-caret-right"></i></li>
            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name"><?= e($article['category']) ?></span>
                <meta itemprop="position" content="3" />
            </li>
        </ol>
    </nav>

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
            <?php if (!empty($article['views'])): ?>
            <span class="article-views">
                <i class="ph ph-eye"></i>
                <span><?= number_format($article['views']) ?> dibaca</span>
            </span>
            <?php endif; ?>
        </div>

        <!-- Title -->
        <h1 class="article-detail-title" itemprop="headline"><?= e($article['title']) ?></h1>

        <!-- Excerpt / Lead -->
        <?php if (!empty($article['excerpt'])): ?>
            <p class="article-detail-lead" itemprop="description"><?= e($article['excerpt']) ?></p>
        <?php endif; ?>

        <!-- Author + Meta -->
        <div class="article-detail-meta" itemprop="author" itemscope itemtype="https://schema.org/Person">
            <div class="article-author">
                <div class="article-author-avatar">
                    <?php if ($authorPhoto): ?>
                        <img src="<?= $authorPhoto ?>" alt="<?= e($article['author_name'] ?? 'Redaksi') ?>" itemprop="image">
                    <?php else: ?>
                        <?= strtoupper(substr($article['author_name'] ?? 'R', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="article-author-info">
                    <strong itemprop="name"><?= e($article['author_name'] ?? 'Redaksi') ?></strong>
                    <span>Penulis</span>
                </div>
            </div>
            <div class="article-meta-sep"></div>
            <div class="article-meta-item">
                <i class="ph ph-calendar-blank"></i>
                <time datetime="<?= date('Y-m-d', $articleDate) ?>" itemprop="datePublished">
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
                   target="_blank" rel="noopener" class="article-share-btn share-twitter" aria-label="Bagikan ke Twitter">
                    <i class="ph ph-x-logo"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" 
                   target="_blank" rel="noopener" class="article-share-btn share-facebook" aria-label="Bagikan ke Facebook">
                    <i class="ph ph-facebook-logo"></i>
                </a>
                <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" 
                   target="_blank" rel="noopener" class="article-share-btn share-whatsapp" aria-label="Bagikan ke WhatsApp">
                    <i class="ph ph-whatsapp-logo"></i>
                </a>
                <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" 
                   target="_blank" rel="noopener" class="article-share-btn share-telegram" aria-label="Bagikan ke Telegram">
                    <i class="ph ph-telegram-logo"></i>
                </a>
                <button class="article-share-btn share-copy" id="btnCopyLink" aria-label="Salin tautan">
                    <i class="ph ph-link"></i>
                </button>
                <?php if (function_exists('share_native_supported')): ?>
                <button class="article-share-btn share-native" id="btnNativeShare" aria-label="Bagikan (native)">
                    <i class="ph ph-share-network"></i>
                </button>
                <?php endif; ?>
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

    <!-- ========== ARTICLE COVER (Enhanced) ========== -->
    <div class="article-detail-cover cat-<?= e($categorySlug) ?> reveal">
        <?php if ($coverImage): ?>
        <img src="<?= $coverImage ?>" alt="<?= e($article['title']) ?>" class="article-cover-img" loading="eager">
        <?php endif; ?>
        <div class="article-cover-orbs" aria-hidden="true">
            <span class="cover-orb cover-orb-1"></span>
            <span class="cover-orb cover-orb-2"></span>
            <span class="cover-orb cover-orb-3"></span>
        </div>
        <div class="article-cover-pattern"></div>
        <?php if (!$coverImage): ?>
        <i class="ph ph-newspaper article-cover-icon"></i>
        <?php endif; ?>
        <div class="article-cover-overlay">
            <span class="article-cover-category"><?= e($article['category']) ?></span>
        </div>
    </div>

    <!-- ========== ARTICLE BODY (2 column: TOC + Content) ========== -->
    <div class="article-detail-body-wrap">
        
        <!-- TOC Toggle (Mobile) -->
        <button class="toc-toggle-mobile" id="btnTocToggle" aria-label="Tampilkan daftar isi" aria-expanded="false">
            <i class="ph ph-list-bullets"></i>
            <span>Daftar Isi</span>
            <i class="ph ph-caret-down"></i>
        </button>

        <!-- Table of Contents (Sticky Sidebar) -->
        <aside class="article-toc" id="articleToc" role="complementary" aria-label="Daftar isi">
            <div class="article-toc-head">
                <i class="ph ph-list-bullets"></i>
                <span>Daftar Isi</span>
                <button class="toc-collapse-btn" id="btnTocCollapse" title="Ciutkan" aria-label="Ciutkan daftar isi">
                    <i class="ph ph-caret-left"></i>
                </button>
            </div>
            <nav class="article-toc-nav" id="tocNav" aria-label="Navigasi bagian artikel">
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
            <div class="article-detail-content glass-card" id="articleContent" itemprop="articleBody">
                <div class="article-content-inner">
                    <?= render_article_content($article['content']) ?>
                </div>
                
                <!-- Article Rating -->
                <div class="article-rating">
                    <span class="article-rating-label">Apakah artikel ini membantu?</span>
                    <div class="article-rating-buttons">
                        <button class="rating-btn rating-yes" data-rating="yes">
                            <i class="ph ph-thumbs-up"></i>
                            <span>Ya</span>
                        </button>
                        <button class="rating-btn rating-no" data-rating="no">
                            <i class="ph ph-thumbs-down"></i>
                            <span>Tidak</span>
                        </button>
                    </div>
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

            <!-- Author Card (Enhanced) -->
            <section class="article-author-card glass-card reveal" itemscope itemtype="https://schema.org/Person">
                <div class="author-card-avatar">
                    <?php if ($authorPhoto): ?>
                        <img src="<?= $authorPhoto ?>" alt="<?= e($article['author_name'] ?? 'Redaksi') ?>" itemprop="image">
                    <?php else: ?>
                        <?= strtoupper(substr($article['author_name'] ?? 'R', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="author-card-info">
                    <span class="author-card-label">Ditulis oleh</span>
                    <h3 itemprop="name"><?= e($article['author_name'] ?? 'Redaksi') ?></h3>
                    <p itemprop="description">Penulis aktif di <?= e(APP_NAME) ?>. Menyukai berbagi informasi dan cerita inspiratif dari kegiatan organisasi.</p>
                    <div class="author-card-meta">
                        <span><i class="ph ph-newspaper"></i> Penulis Redaksi</span>
                        <?php if (!empty($article['author_email'])): ?>
                        <a href="mailto:<?= e($article['author_email']) ?>"><i class="ph ph-envelope-simple"></i> Email</a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Article Navigation (Prev/Next) Enhanced -->
            <?php if (!empty($prevArticle) || !empty($nextArticle)): ?>
            <nav class="article-nav reveal" aria-label="Navigasi artikel">
                <?php if (!empty($prevArticle)): 
                    $prevCatSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($prevArticle['category'])));
                ?>
                <a href="<?= url('artikel/' . (int) $prevArticle['id']) ?>" class="article-nav-item prev">
                    <div class="article-nav-cover cat-<?= e($prevCatSlug) ?>">
                        <i class="ph ph-newspaper"></i>
                    </div>
                    <div class="article-nav-content">
                        <span class="article-nav-label">
                            <i class="ph ph-arrow-left"></i> Sebelumnya
                        </span>
                        <strong><?= e($prevArticle['title']) ?></strong>
                        <span class="article-nav-meta">
                            <i class="ph ph-clock"></i>
                            <?= max(1, ceil(str_word_count(strip_tags($prevArticle['content'] ?? '')) / 200)) ?> min
                        </span>
                    </div>
                </a>
                <?php else: ?>
                <div class="article-nav-item disabled"></div>
                <?php endif; ?>
                
                <?php if (!empty($nextArticle)): 
                    $nextCatSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($nextArticle['category'])));
                ?>
                <a href="<?= url('artikel/' . (int) $nextArticle['id']) ?>" class="article-nav-item next">
                    <div class="article-nav-content">
                        <span class="article-nav-label">
                            Berikutnya <i class="ph ph-arrow-right"></i>
                        </span>
                        <strong><?= e($nextArticle['title']) ?></strong>
                        <span class="article-nav-meta">
                            <i class="ph ph-clock"></i>
                            <?= max(1, ceil(str_word_count(strip_tags($nextArticle['content'] ?? '')) / 200)) ?> min
                        </span>
                    </div>
                    <div class="article-nav-cover cat-<?= e($nextCatSlug) ?>">
                        <i class="ph ph-newspaper"></i>
                    </div>
                </a>
                <?php else: ?>
                <div class="article-nav-item disabled"></div>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== RELATED ARTICLES (Enhanced) ========== -->
    <?php if (!empty($related)): ?>
    <section class="article-related reveal" aria-label="Artikel terkait">
        <div class="article-related-head">
            <div class="article-related-title">
                <i class="ph ph-sparkle"></i>
                <h2>Artikel Lainnya</h2>
            </div>
            <a href="<?= url('artikel') ?>" class="article-related-link">
                Lihat Semua <i class="ph ph-arrow-right"></i>
            </a>
        </div>
        <div class="pub-articles pub-articles-grid">
            <?php foreach ($related as $i => $r): 
                $relCatSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($r['category'])));
                $relCover = !empty($r['cover_image']) ? url('assets/uploads/articles/' . e($r['cover_image'])) : '';
            ?>
            <a href="<?= url('artikel/' . (int) $r['id']) ?>" class="glass-card pub-article-card" style="animation-delay: <?= $i * 0.1 ?>s">
                <div class="pac-cover cat-<?= e($relCatSlug) ?>">
                    <?php if ($relCover): ?>
                    <img src="<?= $relCover ?>" alt="<?= e($r['title']) ?>" class="pac-cover-img" loading="lazy">
                    <?php endif; ?>
                    <div class="pac-cover-pattern"></div>
                    <?php if (!$relCover): ?>
                    <i class="ph ph-newspaper pac-cover-icon"></i>
                    <?php endif; ?>
                    <span class="pac-category"><?= e($r['category']) ?></span>
                </div>
                <div class="pac-body">
                    <div class="pac-meta">
                        <time><?= date('d M Y', strtotime($r['created_at'])) ?></time>
                        <span class="pac-read-time">
                            <i class="ph ph-clock"></i>
                            <?= max(1, ceil(str_word_count(strip_tags($r['content'] ?? '')) / 200)) ?> min
                        </span>
                    </div>
                    <h3 class="pac-title"><?= e($r['title']) ?></h3>
                    <?php if (!empty($r['excerpt'])): ?>
                    <p class="pac-excerpt"><?= e(mb_strimwidth($r['excerpt'], 0, 80, '…')) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</article>

<!-- ========== BACK TO TOP ========== -->
<button class="back-to-top" id="btnBackToTop" title="Kembali ke atas" aria-label="Kembali ke atas">
    <i class="ph ph-arrow-up"></i>
</button>

<!-- ========== FLOATING SHARE (Mobile) ========== -->
<div class="article-floating-share" id="floatingShare">
    <button class="floating-share-btn" id="btnFloatingShare" aria-label="Bagikan">
        <i class="ph ph-share-network"></i>
    </button>
</div>

<!-- ========== SHARE MODAL (Mobile) ========== -->
<div class="share-modal" id="shareModal" role="dialog" aria-labelledby="shareModalTitle" aria-modal="true">
    <div class="share-modal-backdrop" id="shareModalBackdrop"></div>
    <div class="share-modal-content">
        <div class="share-modal-head">
            <h3 id="shareModalTitle">Bagikan Artikel</h3>
            <button class="share-modal-close" id="shareModalClose" aria-label="Tutup">
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

<!-- ========== IMAGE LIGHTBOX ========== -->
<div class="image-lightbox" id="imageLightbox" role="dialog" aria-label="Perbesar gambar" aria-modal="true">
    <div class="image-lightbox-backdrop" id="lightboxBackdrop"></div>
    <div class="image-lightbox-content">
        <button class="image-lightbox-close" id="lightboxClose" aria-label="Tutup">
            <i class="ph ph-x"></i>
        </button>
        <img src="" alt="" id="lightboxImage" class="image-lightbox-img">
        <div class="image-lightbox-caption" id="lightboxCaption"></div>
    </div>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item"><kbd>F</kbd> Mode Baca</span>
    <span class="kbd-item"><kbd>J</kbd><kbd>K</kbd> Navigasi</span>
    <span class="kbd-item"><kbd>T</kbd> Ke Atas</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup</span>
</div>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Reading Progress Bar ---- */
.reading-progress {
    position: fixed;
    top: 0; left: 0; right: 0;
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
.reading-progress-percent {
    position: absolute;
    top: 8px;
    right: 16px;
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(10,15,31,.8);
    backdrop-filter: blur(8px);
    border: 1px solid var(--glass-brd);
    font-size: 10px;
    font-weight: 700;
    color: var(--acc);
    font-variant-numeric: tabular-nums;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}
.reading-progress:hover .reading-progress-percent { opacity: 1; }

/* ---- Reading Mode Toggle ---- */
.reading-mode-toggle {
    position: fixed;
    bottom: 100px;
    right: 24px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(10,15,31,.8);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 20px;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.3s;
    z-index: 50;
    opacity: 0;
    visibility: hidden;
}
.reading-mode-toggle.show { opacity: 1; visibility: visible; }
.reading-mode-toggle:hover {
    background: var(--pri);
    border-color: var(--pri);
    color: #fff;
    transform: scale(1.1);
}
body.reading-mode .reading-mode-toggle {
    background: var(--pri);
    color: #fff;
}

/* ---- Font Size Controls ---- */
.font-size-controls {
    position: fixed;
    bottom: 160px;
    right: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px;
    border-radius: 99px;
    background: rgba(10,15,31,.8);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-brd);
    z-index: 50;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.font-size-controls.show { opacity: 1; visibility: visible; }
.font-size-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.font-size-btn:hover {
    background: var(--pri);
    color: #fff;
}
.font-size-label {
    font-size: 9px;
    font-weight: 700;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
}

/* ---- Back to Top ---- */
.back-to-top {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border: none;
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    display: grid;
    place-items: center;
    box-shadow: 0 12px 30px rgba(99,102,241,.4);
    transition: all 0.3s;
    z-index: 50;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
}
.back-to-top.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.back-to-top:hover {
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 16px 40px rgba(34,211,238,.4);
}

/* ---- Article Container ---- */
.article-detail-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 120px 24px 80px;
    position: relative;
    z-index: 1;
    transition: max-width 0.3s;
}
body.reading-mode .article-detail-wrap {
    max-width: 680px;
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
    text-decoration: none;
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
    list-style: none;
    margin: 0;
    padding: 0;
}
.article-breadcrumb li { display: inline-flex; align-items: center; gap: 8px; }
.article-breadcrumb a {
    color: var(--txt-2);
    text-decoration: none;
    transition: color 0.2s;
}
.article-breadcrumb a:hover { color: var(--txt-0); }
.article-breadcrumb i { font-size: 10px; }
.article-breadcrumb li:last-child span { color: var(--txt-1); font-weight: 600; }

/* ---- Article Header ---- */
.article-detail-header { margin-bottom: 36px; }
.article-detail-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

/* Category-specific colors */
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
    text-decoration: none;
    transition: all 0.2s;
}
.article-category-badge:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.2); }
.article-category-badge i { font-size: 12px; }

.cat-berita { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.cat-tips, .cat-tips-dan-trik, .cat-edukasi { background: linear-gradient(135deg, #06b6d4, #22d3ee); }
.cat-pengumuman, .cat-hari-besar { background: linear-gradient(135deg, #f59e0b, #f97316); }
.cat-event, .cat-kegiatan, .cat-podcast { background: linear-gradient(135deg, #10b981, #34d399); }
.cat-opini, .cat-artikel { background: linear-gradient(135deg, #ec4899, #f43f5e); }

.article-read-time,
.article-word-count,
.article-views {
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
.article-word-count i,
.article-views i { font-size: 13px; color: var(--acc); }

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
    padding-left: 20px;
    border-left: 3px solid var(--acc);
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
.article-author { display: flex; align-items: center; gap: 12px; }
.article-author-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 16px;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 6px 16px rgba(99,102,241,.3);
    overflow: hidden;
}
.article-author-avatar img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.article-author-info strong { display: block; font-size: 14px; font-weight: 700; }
.article-author-info span { font-size: 12px; color: var(--txt-2); }
.article-meta-sep { width: 1px; height: 30px; background: var(--glass-brd); }
.article-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--txt-1);
}
.article-meta-item i { color: var(--acc); font-size: 15px; }

/* Actions bar */
.article-detail-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    flex-wrap: wrap;
}
.article-share-group { display: flex; align-items: center; gap: 6px; }
.article-share-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--txt-2);
    margin-right: 4px;
}
.article-share-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    display: grid;
    place-items: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.article-share-btn:hover { transform: translateY(-2px); color: #fff; }

/* Brand-specific hover colors */
.article-share-btn.share-twitter:hover { background: #1da1f2; border-color: #1da1f2; }
.article-share-btn.share-facebook:hover { background: #1877f2; border-color: #1877f2; }
.article-share-btn.share-whatsapp:hover { background: #25d366; border-color: #25d366; }
.article-share-btn.share-telegram:hover { background: #0088cc; border-color: #0088cc; }
.article-share-btn.share-copy:hover { background: var(--pri); border-color: var(--pri); }
.article-share-btn.share-copy.copied { background: var(--ok); border-color: var(--ok); }

.article-extra-actions { display: flex; gap: 8px; }
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

/* ---- Article Cover (Enhanced) ---- */
.article-detail-cover {
    position: relative;
    height: 360px;
    border-radius: 24px;
    display: grid;
    place-items: center;
    font-size: 80px;
    color: rgba(255,255,255,.5);
    margin-bottom: 40px;
    overflow: hidden;
    box-shadow: 0 30px 80px rgba(2,6,23,.5);
}
.article-cover-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: 1;
}
.article-cover-orbs {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
}
.cover-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.5;
}
.cover-orb-1 {
    width: 200px; height: 200px;
    background: rgba(255,255,255,.3);
    top: -50px; right: -50px;
    animation: coverOrb 12s ease-in-out infinite alternate;
}
.cover-orb-2 {
    width: 150px; height: 150px;
    background: rgba(255,255,255,.25);
    bottom: -40px; left: -40px;
    animation: coverOrb 15s ease-in-out infinite alternate-reverse;
}
.cover-orb-3 {
    width: 120px; height: 120px;
    background: rgba(255,255,255,.2);
    top: 40%; left: 50%;
    transform: translate(-50%, -50%);
    animation: coverOrb 18s ease-in-out infinite;
}
@keyframes coverOrb {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(30px, 20px) scale(1.15); }
}

.article-cover-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 25px 25px, 35px 35px;
    opacity: 0.8;
    z-index: 3;
}
.article-cover-icon {
    position: relative;
    z-index: 4;
    filter: drop-shadow(0 8px 20px rgba(0,0,0,.3));
    animation: cover-icon 4s ease-in-out infinite;
}
@keyframes cover-icon {
    0%, 100% { transform: scale(1); opacity: .5; }
    50% { transform: scale(1.05); opacity: .7; }
}
.article-cover-overlay {
    position: absolute;
    bottom: 20px; left: 20px;
    z-index: 5;
}
.article-cover-category {
    padding: 6px 14px;
    border-radius: 99px;
    background: rgba(10,15,31,.65);
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

/* TOC Toggle Mobile */
.toc-toggle-mobile {
    display: none;
    width: 100%;
    padding: 14px 20px;
    border-radius: 12px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-0);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 20px;
    transition: all 0.2s;
}
.toc-toggle-mobile i:first-child { color: var(--acc); }
.toc-toggle-mobile i:last-child { transition: transform 0.3s; }
.toc-toggle-mobile[aria-expanded="true"] i:last-child { transform: rotate(180deg); }

/* TOC Sidebar */
.article-toc {
    position: sticky;
    top: 100px;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    transition: all 0.3s;
}
.article-toc.collapsed {
    width: 40px;
    overflow: hidden;
}
.article-toc.collapsed .article-toc-nav,
.article-toc.collapsed .article-toc-progress,
.article-toc.collapsed .article-toc-head span { display: none; }
.article-toc.collapsed .article-toc-head {
    padding: 12px 8px;
    justify-content: center;
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
.article-toc-head i { font-size: 15px; color: var(--acc); }
.article-toc-head span { flex: 1; }
.toc-collapse-btn {
    width: 24px; height: 24px;
    border-radius: 6px;
    background: rgba(255,255,255,.06);
    border: none;
    color: var(--txt-2);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 12px;
    transition: all 0.2s;
}
.toc-collapse-btn:hover { background: rgba(255,255,255,.1); color: var(--txt-0); }

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
.article-toc-link.sub {
    padding-left: 28px;
    font-size: 11.5px;
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
    transition: font-size 0.3s;
}

/* Typography */
.article-content-inner h2 {
    font-size: 24px;
    font-weight: 800;
    color: var(--txt-0);
    margin: 40px 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--glass-brd);
    scroll-margin-top: 100px;
    letter-spacing: -0.3px;
}
.article-content-inner h3 {
    font-size: 19px;
    font-weight: 700;
    color: var(--txt-0);
    margin: 32px 0 12px;
    scroll-margin-top: 100px;
}
.article-content-inner p { margin-bottom: 18px; }
.article-content-inner strong,
.article-content-inner b { color: var(--txt-0); font-weight: 700; }
.article-content-inner a {
    color: var(--acc);
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color 0.2s;
}
.article-content-inner a:hover { color: #67e8f9; }
.article-content-inner ul,
.article-content-inner ol { margin: 16px 0; padding-left: 28px; }
.article-content-inner li { margin-bottom: 8px; }
.article-content-inner blockquote {
    border-left: 3px solid var(--acc);
    padding: 16px 20px;
    margin: 24px 0;
    background: rgba(34,211,238,.05);
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: var(--txt-0);
}
.article-content-inner code {
    background: rgba(255,255,255,.08);
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 14px;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
}
.article-content-inner pre {
    background: rgba(0,0,0,.3);
    padding: 16px 20px;
    border-radius: 12px;
    overflow-x: auto;
    margin: 20px 0;
}
.article-content-inner pre code { background: none; padding: 0; }
.article-content-inner img {
    max-width: 100%;
    border-radius: 12px;
    margin: 20px 0;
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
    cursor: zoom-in;
    transition: transform 0.3s;
}
.article-content-inner img:hover { transform: scale(1.02); }

.article-figure {
    margin: 24px 0;
    text-align: center;
}
.article-figure img {
    margin: 0;
    cursor: zoom-in;
}
.article-figure figcaption {
    margin-top: 10px;
    font-size: 12px;
    color: var(--txt-2);
    font-style: italic;
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
    width: 100%; height: 100%;
    border: 0;
}
.article-embed-audio {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 12px;
}
.article-embed-audio i {
    font-size: 28px;
    color: var(--acc);
    flex-shrink: 0;
}
.article-embed-audio audio { flex: 1; border-radius: 8px; }

/* Article Rating */
.article-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 24px;
    margin-top: 32px;
    border-top: 1px solid var(--glass-brd);
    border-bottom: 1px solid var(--glass-brd);
}
.article-rating-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--txt-1);
}
.article-rating-buttons { display: flex; gap: 8px; }
.rating-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 99px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.rating-btn:hover { transform: translateY(-2px); }
.rating-btn.rating-yes:hover {
    background: rgba(16,185,129,.15);
    border-color: var(--ok);
    color: var(--ok);
}
.rating-btn.rating-no:hover {
    background: rgba(239,68,68,.15);
    border-color: var(--danger);
    color: var(--danger);
}
.rating-btn.selected.rating-yes {
    background: var(--ok);
    border-color: var(--ok);
    color: #fff;
}
.rating-btn.selected.rating-no {
    background: var(--danger);
    border-color: var(--danger);
    color: #fff;
}

/* Tags */
.article-tags {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    padding-top: 24px;
    margin-top: 0;
    border-top: none;
}
.article-tags i { font-size: 18px; color: var(--acc); }
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
    text-decoration: none;
    transition: all 0.2s;
}
.article-tag:hover {
    color: var(--txt-0);
    border-color: var(--pri);
    background: rgba(99,102,241,.12);
}

/* ---- Author Card (Enhanced) ---- */
.article-author-card {
    display: flex;
    gap: 24px;
    padding: 28px;
    margin-bottom: 32px;
    align-items: center;
}
.author-card-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    display: grid;
    place-items: center;
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 12px 30px rgba(99,102,241,.35);
    overflow: hidden;
}
.author-card-avatar img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.author-card-info { flex: 1; min-width: 0; }
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
    flex-wrap: wrap;
}
.author-card-meta span,
.author-card-meta a {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--txt-2);
    text-decoration: none;
    transition: color 0.2s;
}
.author-card-meta a:hover { color: var(--acc); }
.author-card-meta i { color: var(--acc); }

/* ---- Article Nav (Prev/Next) Enhanced ---- */
.article-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 48px;
}
.article-nav-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-radius: 16px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
    text-decoration: none;
    align-items: center;
}
.article-nav-item:hover:not(.disabled) {
    border-color: var(--glass-brd-2);
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(2,6,23,.4);
}
.article-nav-item.next { flex-direction: row-reverse; text-align: right; }
.article-nav-item.disabled { opacity: 0; pointer-events: none; }

.article-nav-cover {
    width: 60px; height: 60px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 24px;
    color: rgba(255,255,255,.6);
    flex-shrink: 0;
    overflow: hidden;
}
.article-nav-cover i { filter: drop-shadow(0 4px 8px rgba(0,0,0,.2)); }

.article-nav-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.article-nav-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: var(--acc);
}
.article-nav-item strong {
    font-size: 13px;
    font-weight: 700;
    color: var(--txt-0);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.article-nav-meta {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    color: var(--txt-2);
}
.article-nav-meta i { font-size: 11px; color: var(--acc); }

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
.article-related-title i { font-size: 24px; color: var(--warn); }
.article-related-title h2 { font-size: 22px; font-weight: 800; margin: 0; }
.article-related-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: var(--acc);
    text-decoration: none;
    transition: all 0.2s;
}
.article-related-link:hover { color: #fff; gap: 10px; }

/* Related article cards */
.pub-articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}
.pub-article-card {
    text-decoration: none;
    color: inherit;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    animation: card-in 0.5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes card-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.pub-article-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 26px 60px rgba(2,6,23,.5);
    border-color: rgba(99,102,241,.35);
}
.pac-cover {
    position: relative;
    min-height: 160px;
    display: grid;
    place-items: center;
    overflow: hidden;
}
.pac-cover-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.6s cubic-bezier(.22,1,.36,1);
    z-index: 1;
}
.pub-article-card:hover .pac-cover-img { transform: scale(1.08); }
.pac-cover::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 50%, rgba(4,8,20,.3) 100%);
    z-index: 2;
}
.pac-cover-pattern {
    position: absolute; inset: 0;
    background-image: 
        radial-gradient(circle at 25% 50%, rgba(255,255,255,0.12) 1px, transparent 1px),
        radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 24px 24px, 32px 32px;
    z-index: 3;
}
.pac-cover-icon {
    position: relative;
    z-index: 4;
    font-size: 40px;
    color: rgba(255,255,255,.5);
}
.pac-category {
    position: absolute;
    top: 12px; left: 12px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(10,15,31,.65);
    backdrop-filter: blur(8px);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fff;
    z-index: 5;
}
.pac-body { padding: 18px; }
.pac-meta {
    display: flex;
    align-items: center;
    gap: 10px;
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
}
.pac-read-time i { font-size: 12px; color: var(--acc); }
.pac-title {
    font-size: 14.5px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 8px;
    color: var(--txt-0);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s;
}
.pub-article-card:hover .pac-title { color: var(--acc); }
.pac-excerpt {
    font-size: 12px;
    color: var(--txt-1);
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ---- Image Lightbox ---- */
.image-lightbox {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}
.image-lightbox.show {
    display: flex;
    animation: lb-fade 0.3s ease;
}
@keyframes lb-fade { from { opacity: 0; } to { opacity: 1; } }
.image-lightbox-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 8, 20, 0.95);
    backdrop-filter: blur(20px);
}
.image-lightbox-content {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    z-index: 1;
}
.image-lightbox-close {
    position: absolute;
    top: -40px; right: 0;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 16px;
    transition: all 0.2s;
}
.image-lightbox-close:hover {
    background: var(--danger);
    border-color: var(--danger);
}
.image-lightbox-img {
    max-width: 100%;
    max-height: 85vh;
    border-radius: 12px;
    box-shadow: 0 40px 100px rgba(0,0,0,.6);
    animation: lightbox-zoom 0.3s cubic-bezier(.22,1,.36,1);
}
@keyframes lightbox-zoom {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.image-lightbox-caption {
    text-align: center;
    padding: 12px;
    color: #fff;
    font-size: 13px;
    font-style: italic;
}

/* ---- Floating Share (Mobile) ---- */
.article-floating-share {
    position: fixed;
    bottom: 24px;
    right: 90px;
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
.share-modal.show { opacity: 1; visibility: visible; }
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
.share-modal.show .share-modal-content { transform: translateY(0); }
.share-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--glass-brd);
}
.share-modal-head h3 { font-size: 16px; font-weight: 800; }
.share-modal-close {
    width: 32px; height: 32px;
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
.share-modal-close:hover { background: rgba(239,68,68,.15); color: #fca5a5; }
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
.share-option:hover { background: rgba(255,255,255,.05); transform: translateY(-2px); }
.share-option-icon {
    width: 48px; height: 48px;
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

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    max-width: 860px;
    margin: 20px auto 0;
    padding: 0 24px;
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    font-size: 11.5px;
    color: var(--txt-2);
}
.kbd-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 8px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
}
.kbd-item kbd {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 1px 0 rgba(0,0,0,.25);
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .article-detail-body-wrap { grid-template-columns: 1fr; }
    .article-toc { display: none; }
    .article-toc.mobile-show {
        display: block;
        position: static;
        max-height: none;
        margin-bottom: 24px;
        padding: 16px;
        border-radius: 16px;
        background: var(--glass);
        border: 1px solid var(--glass-brd);
    }
    .toc-toggle-mobile { display: flex; }
    .article-floating-share { display: block; }
    .article-share-group { display: none; }
    .reading-mode-toggle { bottom: 90px; right: 90px; }
    .font-size-controls { bottom: 150px; right: 90px; }
}

@media (max-width: 768px) {
    .article-detail-wrap { padding: 100px 16px 60px; }
    .article-detail-cover { height: 220px; font-size: 60px; }
    .article-detail-content { padding: 24px 20px; }
    .article-detail-meta { flex-direction: column; align-items: flex-start; gap: 12px; }
    .article-meta-sep { display: none; }
    .article-nav { grid-template-columns: 1fr; }
    .article-author-card { flex-direction: column; text-align: center; }
    .share-modal-options { grid-template-columns: repeat(2, 1fr); }
    .keyboard-hints { display: none; }
    .reading-mode-toggle { bottom: 90px; right: 24px; }
    .font-size-controls { bottom: 150px; right: 24px; }
    .article-floating-share { right: 24px; bottom: 24px; }
}

@media (max-width: 520px) {
    .article-detail-cover { height: 180px; }
    .article-detail-title { font-size: 24px; }
    .article-detail-lead { font-size: 15px; }
    .article-detail-badges { gap: 6px; }
    .article-category-badge { padding: 5px 10px; font-size: 10px; }
    .article-read-time, .article-word-count, .article-views { padding: 5px 10px; font-size: 10.5px; }
    .article-author-avatar { width: 38px; height: 38px; font-size: 14px; }
    .author-card-avatar { width: 60px; height: 60px; font-size: 22px; }
    .article-rating { flex-direction: column; gap: 12px; }
    .pub-articles-grid { grid-template-columns: 1fr; }
}

/* ---- Reading Mode Styles ---- */
body.reading-mode .article-detail-nav,
body.reading-mode .article-detail-actions,
body.reading-mode .article-detail-cover,
body.reading-mode .article-related,
body.reading-mode .keyboard-hints { display: none; }
body.reading-mode .article-detail-header { margin-bottom: 24px; }
body.reading-mode .article-detail-content { padding: 20px; }

/* ---- Print Styles ---- */
@media print {
    .reading-progress,
    .reading-mode-toggle,
    .font-size-controls,
    .back-to-top,
    .article-detail-nav,
    .article-detail-actions,
    .article-toc,
    .toc-toggle-mobile,
    .article-floating-share,
    .article-related,
    .article-nav,
    .keyboard-hints,
    .image-lightbox,
    .share-modal { display: none !important; }
    
    body { background: #fff; color: #000; }
    body.reading-mode .article-detail-wrap { max-width: 100%; }
    .article-detail-wrap { max-width: 100%; padding: 20px; }
    .glass-card { background: #fff; border: 1px solid #ddd; box-shadow: none; }
    .article-detail-title { color: #000; -webkit-text-fill-color: #000; }
    .article-content-inner { color: #333; }
    .article-content-inner h2,
    .article-content-inner h3 { color: #000; page-break-after: avoid; }
    .article-content-inner p { orphans: 3; widows: 3; }
    .article-content-inner img { page-break-inside: avoid; }
    .article-embed { display: none; }
    .article-content-inner a { color: #000; text-decoration: underline; }
    .article-content-inner a::after { content: " (" attr(href) ")"; font-size: 10px; color: #666; }
}
</style>

<!-- ========== SCRIPT (v7.0) ========== -->
<script>
(function(){
    'use strict';
    
    const articleContent = document.getElementById('articleContent');
    const readingBar = document.querySelector('.reading-progress-bar');
    const readingPercent = document.getElementById('readingPercent');
    const tocNav = document.getElementById('tocNav');
    const tocProgressBar = document.getElementById('tocProgressBar');
    const articleToc = document.getElementById('articleToc');
    const btnBackToTop = document.getElementById('btnBackToTop');
    const btnReadingMode = document.getElementById('btnReadingMode');
    const fontSizeControls = document.getElementById('fontSizeControls');
    const btnFontIncrease = document.getElementById('btnFontIncrease');
    const btnFontDecrease = document.getElementById('btnFontDecrease');
    const fontSizeLabel = document.getElementById('fontSizeLabel');
    const btnTocToggle = document.getElementById('btnTocToggle');
    const btnTocCollapse = document.getElementById('btnTocCollapse');
    
    if (!articleContent) return;
    
    let currentFontSize = 100;
    const FONT_SIZE_KEY = 'article_font_size';
    const READING_MODE_KEY = 'article_reading_mode';
    
    /* ---- 1. Reading Progress + TOC Progress + Back to Top ---- */
    let ticking = false;
    function updateProgress() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = docHeight > 0 ? Math.min((scrollTop / docHeight) * 100, 100) : 0;
        
        if (readingBar) readingBar.style.width = progress + '%';
        if (tocProgressBar) tocProgressBar.style.width = progress + '%';
        if (readingPercent) readingPercent.textContent = Math.round(progress) + '%';
        
        const readingProgress = document.getElementById('readingProgress');
        if (readingProgress) readingProgress.setAttribute('aria-valuenow', Math.round(progress));
        
        // Back to top visibility
        if (btnBackToTop) {
            btnBackToTop.classList.toggle('show', scrollTop > 400);
        }
        
        // Reading mode toggle visibility
        if (btnReadingMode) {
            btnReadingMode.classList.toggle('show', scrollTop > 300);
        }
        if (fontSizeControls) {
            fontSizeControls.classList.toggle('show', scrollTop > 300);
        }
        
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateProgress);
            ticking = true;
        }
    }, { passive: true });
    
    // Back to top click
    btnBackToTop?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    /* ---- 2. Auto-generate TOC from H2/H3 ---- */
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
            tocNav.appendChild(link);
            
            // Smooth scroll
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.getElementById(id);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, '', '#' + id);
                }
            });
        });
    }
    
    /* ---- 3. Active TOC link on scroll ---- */
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
    
    /* ---- 4. Copy link with visual feedback ---- */
    function copyLink(btn) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            if (window.toast) window.toast('Tautan berhasil disalin!', 'success');
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.classList.add('copied');
                btn.innerHTML = '<i class="ph ph-check"></i>';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = originalHTML;
                }, 2000);
            }
        });
    }
    document.getElementById('btnCopyLink')?.addEventListener('click', (e) => copyLink(e.currentTarget));
    document.getElementById('btnCopyLink2')?.addEventListener('click', (e) => copyLink(e.currentTarget));
    
    /* ---- 5. Native Share API ---- */
    document.getElementById('btnNativeShare')?.addEventListener('click', async () => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: <?= json_encode($article['title']) ?>,
                    text: <?= json_encode($article['excerpt'] ?? '') ?>,
                    url: window.location.href
                });
            } catch (e) { /* User cancelled */ }
        }
    });
    
    /* ---- 6. Bookmark toggle ---- */
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
                if (window.toast) window.toast('Artikel dihapus dari bookmark.', 'info');
            }
        });
    }
    
    /* ---- 7. Article Rating ---- */
    document.querySelectorAll('.rating-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = btn.dataset.rating;
            const articleId = <?= (int)$article['id'] ?>;
            const key = 'rated_' + articleId;
            
            // Remove previous selection
            document.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            
            localStorage.setItem(key, rating);
            if (window.toast) {
                window.toast(rating === 'yes' ? 'Terima kasih atas feedback Anda!' : 'Kami akan berusaha memperbaiki.', 'success');
            }
        });
        
        // Load previous rating
        const articleId = <?= (int)$article['id'] ?>;
        const savedRating = localStorage.getItem('rated_' + articleId);
        if (savedRating === btn.dataset.rating) {
            btn.classList.add('selected');
        }
    });
    
    /* ---- 8. Share modal (mobile) ---- */
    const shareModal = document.getElementById('shareModal');
    const btnFloatingShare = document.getElementById('btnFloatingShare');
    const shareModalBackdrop = document.getElementById('shareModalBackdrop');
    const shareModalClose = document.getElementById('shareModalClose');
    
    function openShareModal() { 
        shareModal?.classList.add('show'); 
        document.body.style.overflow = 'hidden';
    }
    function closeShareModal() { 
        shareModal?.classList.remove('show'); 
        document.body.style.overflow = '';
    }
    
    btnFloatingShare?.addEventListener('click', openShareModal);
    shareModalBackdrop?.addEventListener('click', closeShareModal);
    shareModalClose?.addEventListener('click', closeShareModal);
    
    /* ---- 9. Reading Mode Toggle ---- */
    function toggleReadingMode() {
        document.body.classList.toggle('reading-mode');
        const isReadingMode = document.body.classList.contains('reading-mode');
        localStorage.setItem(READING_MODE_KEY, isReadingMode ? '1' : '0');
        
        if (btnReadingMode) {
            btnReadingMode.innerHTML = isReadingMode 
                ? '<i class="ph ph-x"></i>' 
                : '<i class="ph ph-book-open-text"></i>';
            btnReadingMode.title = isReadingMode ? 'Keluar mode baca (F)' : 'Mode baca fokus (F)';
        }
    }
    
    // Restore reading mode
    if (localStorage.getItem(READING_MODE_KEY) === '1') {
        document.body.classList.add('reading-mode');
        if (btnReadingMode) {
            btnReadingMode.innerHTML = '<i class="ph ph-x"></i>';
            btnReadingMode.title = 'Keluar mode baca (F)';
        }
    }
    
    btnReadingMode?.addEventListener('click', toggleReadingMode);
    
    /* ---- 10. Font Size Controls ---- */
    function updateFontSize(size) {
        currentFontSize = Math.max(80, Math.min(140, size));
        const contentInner = document.querySelector('.article-content-inner');
        if (contentInner) {
            contentInner.style.fontSize = (currentFontSize / 100 * 15.5) + 'px';
        }
        if (fontSizeLabel) {
            fontSizeLabel.textContent = currentFontSize + '%';
        }
        localStorage.setItem(FONT_SIZE_KEY, currentFontSize);
    }
    
    // Restore font size
    const savedFontSize = parseInt(localStorage.getItem(FONT_SIZE_KEY), 10);
    if (savedFontSize && savedFontSize !== 100) {
        updateFontSize(savedFontSize);
    }
    
    btnFontIncrease?.addEventListener('click', () => updateFontSize(currentFontSize + 10));
    btnFontDecrease?.addEventListener('click', () => updateFontSize(currentFontSize - 10));
    
    /* ---- 11. TOC Toggle (Mobile) ---- */
    btnTocToggle?.addEventListener('click', () => {
        const isExpanded = btnTocToggle.getAttribute('aria-expanded') === 'true';
        btnTocToggle.setAttribute('aria-expanded', !isExpanded);
        articleToc?.classList.toggle('mobile-show', !isExpanded);
    });
    
    /* ---- 12. TOC Collapse (Desktop) ---- */
    btnTocCollapse?.addEventListener('click', () => {
        articleToc?.classList.toggle('collapsed');
        const isCollapsed = articleToc?.classList.contains('collapsed');
        if (btnTocCollapse) {
            btnTocCollapse.innerHTML = isCollapsed 
                ? '<i class="ph ph-caret-right"></i>' 
                : '<i class="ph ph-caret-left"></i>';
            btnTocCollapse.title = isCollapsed ? 'Buka' : 'Ciutkan';
        }
    });
    
    /* ---- 13. Image Lightbox ---- */
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxBackdrop = document.getElementById('lightboxBackdrop');
    
    function openLightbox(src, alt) {
        if (!lightbox || !lightboxImg) return;
        lightboxImg.src = src;
        lightboxImg.alt = alt;
        if (lightboxCaption) lightboxCaption.textContent = alt;
        lightbox.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        lightbox?.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Bind to all images in article content
    articleContent.querySelectorAll('img').forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', () => {
            openLightbox(img.src, img.alt || '');
        });
    });
    
    lightboxClose?.addEventListener('click', closeLightbox);
    lightboxBackdrop?.addEventListener('click', closeLightbox);
    
    /* ---- 14. Keyboard Shortcuts ---- */
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
        
        // Esc closes modals
        if (e.key === 'Escape') {
            if (lightbox?.classList.contains('show')) { closeLightbox(); return; }
            if (shareModal?.classList.contains('show')) { closeShareModal(); return; }
        }
        
        if (inInput) return;
        
        // F = toggle reading mode
        if (e.key.toLowerCase() === 'f' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            toggleReadingMode();
            return;
        }
        
        // T = back to top
        if (e.key.toLowerCase() === 't' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        
        // J = next section
        if (e.key.toLowerCase() === 'j' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            const activeLink = document.querySelector('.article-toc-link.active');
            if (activeLink) {
                const next = activeLink.nextElementSibling;
                if (next) next.click();
            } else if (headings.length > 0) {
                headings[0].scrollIntoView({ behavior: 'smooth' });
            }
            return;
        }
        
        // K = previous section
        if (e.key.toLowerCase() === 'k' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            const activeLink = document.querySelector('.article-toc-link.active');
            if (activeLink) {
                const prev = activeLink.previousElementSibling;
                if (prev) prev.click();
            }
            return;
        }
        
        // + / - = font size
        if (e.key === '+' || e.key === '=') {
            updateFontSize(currentFontSize + 10);
            return;
        }
        if (e.key === '-') {
            updateFontSize(currentFontSize - 10);
            return;
        }
    });
    
    /* ---- 15. Handle hash on load ---- */
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);
        }
    }
    
    // Initial progress update
    updateProgress();
})();
</script>