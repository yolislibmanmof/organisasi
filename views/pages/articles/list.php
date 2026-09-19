<?php
/**
 * ============================================================
 * HALAMAN LIST ARTIKEL PUBLIK — ULTIMATE EDITION v5.9
 * Daftar artikel dengan featured article, filter kategori,
 * search, dan empty state yang informatif.
 * ============================================================
 */

$articles = $articles ?? [];
$categories = $categories ?? [];
$active = $active ?? '';
$page = $page ?? 1;
$pages = $pages ?? 1;

// Featured article (artikel pertama di halaman 1 tanpa filter)
$featured = ($page == 1 && $active === '' && !empty($articles)) ? array_shift($articles) : null;
?>

<!-- ========== HERO SECTION ========== -->
<section class="articles-hero">
    <div class="articles-hero-content reveal">
        <span class="page-eyebrow">Publikasi & Informasi</span>
        <h1 class="articles-hero-title">Artikel & Informasi</h1>
        <p class="articles-hero-sub">
            Kabar, edukasi, dan cerita inspiratif dari kegiatan organisasi — 
            terbuka untuk umum dan selalu diperbarui.
        </p>
    </div>
    
    <!-- Search Bar -->
    <div class="articles-search reveal">
        <div class="articles-search-box">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" 
                   id="articleSearchInput" 
                   placeholder="Cari artikel berdasarkan judul atau topik..."
                   value="<?= e($active) ?>">
            <button class="articles-search-btn" id="btnArticleSearch">
                <i class="ph ph-magnifying-glass"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== CATEGORY CHIPS ========== -->
<div class="articles-filter-section reveal">
    <div class="cat-chips">
        <a href="<?= url('artikel') ?>" class="chip <?= $active === '' ? 'chip-active' : '' ?>">
            <i class="ph ph-squares-four"></i>
            <span>Semua</span>
        </a>
        <?php foreach ($categories as $c): 
            $catSlug = strtolower(str_replace(' ', '-', $c));
        ?>
        <a href="<?= url('artikel?kategori=' . urlencode($c)) ?>" 
           class="chip chip-cat-<?= e($catSlug) ?> <?= $active === $c ? 'chip-active' : '' ?>">
            <i class="ph ph-tag"></i>
            <span><?= e($c) ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Active filter indicator -->
    <?php if ($active !== ''): ?>
    <div class="articles-filter-active">
        <span>
            <i class="ph ph-funnel"></i>
            Menampilkan artikel kategori: <strong><?= e($active) ?></strong>
        </span>
        <a href="<?= url('artikel') ?>" class="articles-filter-clear">
            <i class="ph ph-x"></i> Hapus filter
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- ========== MAIN CONTENT ========== -->
<div class="articles-container">
    
    <?php if ($featured): 
        $featCatSlug = strtolower(str_replace(' ', '-', $featured['category']));
        $featReadTime = max(1, ceil(str_word_count(strip_tags($featured['content'] ?? '')) / 200));
    ?>
    <!-- FEATURED ARTICLE -->
    <section class="featured-article reveal">
        <a href="<?= url('artikel/' . (int) $featured['id']) ?>" class="featured-article-card glass-card">
            <div class="featured-cover cat-<?= e($featCatSlug) ?>">
                <div class="featured-cover-pattern"></div>
                <i class="ph ph-newspaper"></i>
                <span class="featured-badge">
                    <i class="ph ph-star"></i>
                    <span>Artikel Utama</span>
                </span>
            </div>
            <div class="featured-body">
                <div class="featured-meta">
                    <span class="featured-category cat-<?= e($featCatSlug) ?>">
                        <i class="ph ph-tag"></i> <?= e($featured['category']) ?>
                    </span>
                    <span class="featured-date">
                        <i class="ph ph-calendar-blank"></i>
                        <?= date('d M Y', strtotime($featured['created_at'])) ?>
                    </span>
                    <span class="featured-read-time">
                        <i class="ph ph-clock"></i>
                        <?= $featReadTime ?> menit baca
                    </span>
                </div>
                <h2 class="featured-title"><?= e($featured['title']) ?></h2>
                <?php if (!empty($featured['excerpt'])): ?>
                <p class="featured-excerpt"><?= e($featured['excerpt']) ?></p>
                <?php endif; ?>
                <div class="featured-cta">
                    <span class="featured-read-btn">
                        Baca Selengkapnya
                        <i class="ph ph-arrow-right"></i>
                    </span>
                </div>
            </div>
        </a>
    </section>
    <?php endif; ?>
    
    <!-- ARTICLES GRID -->
    <?php if (!empty($articles)): ?>
    <section class="articles-section reveal">
        <?php if ($featured): ?>
        <div class="articles-section-head">
            <h3 class="articles-section-title">
                <i class="ph ph-newspaper"></i>
                Artikel Terbaru
            </h3>
        </div>
        <?php endif; ?>
        
        <div class="pub-articles pub-articles-grid">
            <?php foreach ($articles as $i => $a): 
                $catSlug = strtolower(str_replace(' ', '-', $a['category']));
                $readTime = max(1, ceil(str_word_count(strip_tags($a['content'] ?? '')) / 200));
            ?>
            <a href="<?= url('artikel/' . (int) $a['id']) ?>" 
               class="glass-card pub-article-card reveal" 
               style="animation-delay: <?= min($i * 0.05, 0.4) ?>s">
                <div class="pac-cover cat-<?= e($catSlug) ?>">
                    <div class="pac-cover-pattern"></div>
                    <i class="ph ph-newspaper"></i>
                    <span class="pac-category"><?= e($a['category']) ?></span>
                </div>
                <div class="pac-body">
                    <div class="pac-meta">
                        <time>
                            <i class="ph ph-calendar-blank"></i>
                            <?= date('d M Y', strtotime($a['created_at'])) ?>
                        </time>
                        <span class="pac-read-time">
                            <i class="ph ph-clock"></i>
                            <?= $readTime ?> min
                        </span>
                    </div>
                    <h3><?= e($a['title']) ?></h3>
                    <?php if (!empty($a['excerpt'])): ?>
                    <p><?= e(mb_strimwidth($a['excerpt'], 0, 110, '…')) ?></p>
                    <?php endif; ?>
                    <div class="pac-footer">
                        <span class="pac-author">
                            <i class="ph ph-user-circle"></i>
                            <?= e($a['author_name'] ?? 'Redaksi') ?>
                        </span>
                        <span class="pac-read-more">
                            Baca <i class="ph ph-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- PAGINATION -->
    <?php if ($pages > 1): ?>
    <nav class="articles-pager reveal">
        <div class="articles-pager-info">
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
        </div>
        <div class="articles-pager-nav">
            <?php if ($page > 1): ?>
            <a class="articles-pager-btn" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . ($page - 1)) ?>">
                <i class="ph ph-caret-left"></i>
                <span>Sebelumnya</span>
            </a>
            <?php else: ?>
            <span class="articles-pager-btn disabled">
                <i class="ph ph-caret-left"></i>
                <span>Sebelumnya</span>
            </span>
            <?php endif; ?>
            
            <!-- Page numbers -->
            <?php
            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            
            if ($start > 1): ?>
                <a class="articles-pager-btn num" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=1') ?>">1</a>
                <?php if ($start > 2): ?><span class="articles-pager-dots">...</span><?php endif; ?>
            <?php endif;
            
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="articles-pager-btn num active"><?= $i ?></span>
                <?php else: ?>
                <a class="articles-pager-btn num" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor;
            
            if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="articles-pager-dots">...</span><?php endif; ?>
                <a class="articles-pager-btn num" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . $pages) ?>"><?= $pages ?></a>
            <?php endif; ?>
            
            <?php if ($page < $pages): ?>
            <a class="articles-pager-btn" href="<?= url('artikel?kategori=' . urlencode($active) . '&halaman=' . ($page + 1)) ?>">
                <span>Berikutnya</span>
                <i class="ph ph-caret-right"></i>
            </a>
            <?php else: ?>
            <span class="articles-pager-btn disabled">
                <span>Berikutnya</span>
                <i class="ph ph-caret-right"></i>
            </span>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    
    <?php elseif ($active === '' && !$featured): ?>
    <!-- TOTAL EMPTY STATE -->
    <section class="articles-empty reveal">
        <div class="articles-empty-card glass-card">
            <div class="articles-empty-icon">
                <i class="ph ph-newspaper-clipping"></i>
                <span class="articles-empty-spark"></span>
            </div>
            <h3>Belum Ada Artikel</h3>
            <p>Saat ini belum ada artikel yang dipublikasikan. Artikel terbaru akan muncul di sini setelah diterbitkan oleh redaksi.</p>
            <a href="<?= url('') ?>" class="btn btn-ghost">
                <i class="ph ph-house"></i>
                <span class="btn-text">Kembali ke Beranda</span>
            </a>
        </div>
    </section>
    
    <?php else: ?>
    <!-- EMPTY FOR CATEGORY -->
    <section class="articles-empty reveal">
        <div class="articles-empty-card glass-card">
            <div class="articles-empty-icon">
                <i class="ph ph-magnifying-glass"></i>
                <span class="articles-empty-spark"></span>
            </div>
            <h3>Tidak Ada Artikel</h3>
            <p>Belum ada artikel pada kategori <strong>"<?= e($active) ?>"</strong>. Coba kategori lain atau lihat semua artikel.</p>
            <div class="articles-empty-actions">
                <a href="<?= url('artikel') ?>" class="btn btn-primary">
                    <i class="ph ph-squares-four"></i>
                    <span class="btn-text">Lihat Semua Artikel</span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
/* ---- Hero Section ---- */
.articles-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 50px;
    max-width: 720px;
    margin: 0 auto;
}
.articles-hero-title {
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
.articles-hero-sub {
    color: var(--txt-1);
    font-size: 16px;
    line-height: 1.7;
    max-width: 52ch;
    margin: 0 auto;
}

/* Search */
.articles-search {
    max-width: 520px;
    margin: 36px auto 0;
}
.articles-search-box {
    position: relative;
    display: flex;
    align-items: center;
}
.articles-search-box i {
    position: absolute;
    left: 18px;
    font-size: 18px;
    color: var(--txt-2);
    pointer-events: none;
}
.articles-search-box input {
    width: 100%;
    padding: 16px 60px 16px 50px;
    border-radius: 99px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-0);
    font-size: 14px;
    outline: none;
    transition: all 0.3s;
}
.articles-search-box input:focus {
    border-color: var(--pri);
    box-shadow: 0 0 0 4px rgba(99,102,241,.15);
}
.articles-search-box input::placeholder {
    color: var(--txt-2);
}
.articles-search-btn {
    position: absolute;
    right: 6px;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    border: none;
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 18px;
    transition: all 0.2s;
}
.articles-search-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(99,102,241,.4);
}

/* ---- Filter Section ---- */
.articles-filter-section {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px 40px;
}
.cat-chips {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}
.chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 99px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    transition: all 0.25s;
}
.chip i { font-size: 14px; }
.chip:hover {
    color: var(--txt-0);
    border-color: var(--pri);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99,102,241,.2);
}
.chip-active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(99,102,241,.35);
}

.articles-filter-active {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 20px;
    padding: 12px 20px;
    border-radius: 12px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    font-size: 13px;
    color: var(--txt-1);
}
.articles-filter-active i {
    color: var(--pri);
    margin-right: 6px;
}
.articles-filter-active strong {
    color: var(--txt-0);
}
.articles-filter-clear {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.06);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}
.articles-filter-clear:hover {
    color: #fca5a5;
    border-color: var(--danger);
    background: rgba(239,68,68,.1);
}

/* ---- Container ---- */
.articles-container {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px 80px;
}

/* ---- Featured Article ---- */
.featured-article {
    margin-bottom: 48px;
}
.featured-article-card {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    overflow: hidden;
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
}
.featured-article-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 80px rgba(2,6,23,.5);
}
.featured-cover {
    position: relative;
    min-height: 320px;
    display: grid;
    place-items: center;
    font-size: 80px;
    color: rgba(255,255,255,.4);
    overflow: hidden;
}
.featured-cover-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 30% 40%, rgba(255,255,255,0.12) 1px, transparent 1px),
        radial-gradient(circle at 70% 60%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 30px 30px, 40px 40px;
}
.featured-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 99px;
    background: rgba(10,15,31,.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fbbf24;
}
.featured-badge i {
    font-size: 12px;
}

.featured-body {
    padding: 36px;
    display: flex;
    flex-direction: column;
}
.featured-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.featured-category {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 99px;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fff;
}
.featured-category i { font-size: 11px; }
.featured-date,
.featured-read-time {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--txt-2);
}
.featured-date i,
.featured-read-time i {
    font-size: 14px;
    color: var(--acc);
}

.featured-title {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.8px;
    line-height: 1.2;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.featured-excerpt {
    font-size: 14.5px;
    line-height: 1.65;
    color: var(--txt-1);
    margin-bottom: 24px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.featured-cta {
    margin-top: auto;
}
.featured-read-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--acc);
    transition: all 0.2s;
}
.featured-article-card:hover .featured-read-btn {
    color: #fff;
    gap: 12px;
}

/* ---- Articles Section ---- */
.articles-section {
    margin-bottom: 40px;
}
.articles-section-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--glass-brd);
}
.articles-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 800;
}
.articles-section-title i {
    font-size: 22px;
    color: var(--acc);
}

/* ---- Article Card Enhancements ---- */
.pub-article-card {
    text-decoration: none;
    color: inherit;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.pac-cover {
    position: relative;
    overflow: hidden;
}
.pac-cover-pattern {
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 25% 50%, rgba(255,255,255,0.12) 1px, transparent 1px),
        radial-gradient(circle at 75% 30%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 24px 24px, 32px 32px;
}
.pac-category {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(10,15,31,.5);
    backdrop-filter: blur(8px);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fff;
}
.pac-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.pac-meta time {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--txt-2);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.pac-meta time i {
    font-size: 13px;
    color: var(--acc);
}
.pac-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--glass-brd);
}
.pac-author {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    color: var(--txt-2);
}
.pac-author i {
    font-size: 14px;
    color: var(--acc);
}
.pac-read-more {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 700;
    color: var(--acc);
    transition: all 0.2s;
}
.pub-article-card:hover .pac-read-more {
    color: #fff;
    gap: 8px;
}

/* ---- Pagination ---- */
.articles-pager {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-top: 48px;
    padding: 20px 24px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    flex-wrap: wrap;
}
.articles-pager-info {
    font-size: 13px;
    color: var(--txt-1);
}
.articles-pager-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.articles-pager-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 10px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.articles-pager-btn:hover:not(.disabled):not(.active) {
    background: rgba(255,255,255,.08);
    color: var(--txt-0);
    border-color: var(--glass-brd-2);
}
.articles-pager-btn.num {
    min-width: 40px;
    justify-content: center;
    padding: 10px;
}
.articles-pager-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.articles-pager-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.articles-pager-dots {
    color: var(--txt-2);
    padding: 0 4px;
}

/* ---- Empty State ---- */
.articles-empty {
    display: flex;
    justify-content: center;
    padding: 40px 0 80px;
}
.articles-empty-card {
    text-align: center;
    padding: 60px 40px;
    max-width: 480px;
}
.articles-empty-icon {
    position: relative;
    width: 88px;
    height: 88px;
    margin: 0 auto 24px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid;
    place-items: center;
    font-size: 40px;
    color: var(--acc);
}
.articles-empty-spark {
    position: absolute;
    inset: -12px;
    border-radius: 34px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(14px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.articles-empty-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 10px;
}
.articles-empty-card p {
    color: var(--txt-1);
    font-size: 14.5px;
    margin-bottom: 28px;
    max-width: 40ch;
    margin-left: auto;
    margin-right: auto;
}
.articles-empty-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .featured-article-card {
        grid-template-columns: 1fr;
    }
    .featured-cover {
        min-height: 200px;
    }
}

@media (max-width: 768px) {
    .articles-hero { padding-top: 120px; }
    .articles-pager {
        flex-direction: column;
        text-align: center;
    }
    .articles-pager-nav { justify-content: center; }
}
</style>

<script>
(() => {
    'use strict';
    
    // Search functionality
    const searchInput = document.getElementById('articleSearchInput');
    const searchBtn = document.getElementById('btnArticleSearch');
    
    function doSearch() {
        const query = searchInput?.value.trim();
        if (query) {
            window.location.href = '<?= url('artikel') ?>?kategori=' + encodeURIComponent(query);
        }
    }
    
    searchBtn?.addEventListener('click', doSearch);
    searchInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') doSearch();
    });
})();
</script>