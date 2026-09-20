<?php
/**
 * ============================================================
 * HALAMAN LIST ARTIKEL PUBLIK — ULTIMATE EDITION v7.0
 * Daftar artikel dengan featured article, filter kategori,
 * search, empty state kaya, dan pengalaman visual sinematik.
 * ============================================================
 */

$articles = $articles ?? [];
$categories = $categories ?? [];
$active = $active ?? '';
$search = $search ?? '';
$page = $page ?? 1;
$pages = $pages ?? 1;
$totalArticles = $totalArticles ?? 0;

// Hitung count per kategori
$categoryCounts = [];
foreach ($categories as $c) {
    $categoryCounts[$c] = 0;
}
// Jika backend provide counts, pakai itu
if (!empty($categoryCountsData)) {
    $categoryCounts = $categoryCountsData;
}

// Featured article (artikel pertama di halaman 1 tanpa filter/search)
$featured = ($page == 1 && $active === '' && $search === '' && !empty($articles)) ? array_shift($articles) : null;
?>

<!-- ========== HERO SECTION ========== -->
<section class="articles-hero">
    <!-- Decorative orbs -->
    <div class="articles-orbs" aria-hidden="true">
        <span class="articles-orb orb-1"></span>
        <span class="articles-orb orb-2"></span>
        <span class="articles-orb orb-3"></span>
    </div>

    <div class="articles-hero-content reveal">
        <span class="page-eyebrow">
            <i class="ph ph-newspaper"></i>
            Publikasi & Informasi
        </span>
        <h1 class="articles-hero-title">Artikel & Informasi</h1>
        <p class="articles-hero-sub">
            Kabar, edukasi, dan cerita inspiratif dari kegiatan organisasi — 
            terbuka untuk umum dan selalu diperbarui.
        </p>
    </div>
    
    <!-- Search Bar (enhanced dengan clear button) -->
    <div class="articles-search reveal">
        <form action="<?= url('artikel') ?>" method="get" class="articles-search-form" id="articleSearchForm">
            <?php if ($active !== ''): ?>
            <input type="hidden" name="kategori" value="<?= e($active) ?>">
            <?php endif; ?>
            <div class="articles-search-box">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" 
                       name="q"
                       id="articleSearchInput" 
                       placeholder="Cari artikel berdasarkan judul atau topik..."
                       value="<?= e($search) ?>"
                       aria-label="Cari artikel"
                       autocomplete="off">
                <button type="button" class="articles-search-clear" id="btnSearchClear" style="display:none" aria-label="Hapus pencarian">
                    <i class="ph ph-x"></i>
                </button>
                <button type="submit" class="articles-search-btn" id="btnArticleSearch" aria-label="Cari">
                    <i class="ph ph-magnifying-glass"></i>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- ========== CATEGORY CHIPS (dengan count) ========== -->
<div class="articles-filter-section reveal">
    <div class="cat-chips" role="tablist" aria-label="Filter kategori artikel">
        <a href="<?= url('artikel') ?>" 
           class="chip <?= $active === '' && $search === '' ? 'chip-active' : '' ?>"
           role="tab" 
           aria-selected="<?= $active === '' && $search === '' ? 'true' : 'false' ?>">
            <i class="ph ph-squares-four"></i>
            <span>Semua</span>
            <span class="chip-count"><?= $totalArticles ?></span>
        </a>
        <?php foreach ($categories as $c): 
            $catSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($c)));
            $count = $categoryCounts[$c] ?? 0;
        ?>
        <a href="<?= url('artikel?kategori=' . urlencode($c)) ?>" 
           class="chip chip-cat-<?= e($catSlug) ?> <?= $active === $c ? 'chip-active' : '' ?>"
           role="tab"
           aria-selected="<?= $active === $c ? 'true' : 'false' ?>">
            <i class="ph ph-tag"></i>
            <span><?= e($c) ?></span>
            <span class="chip-count"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Active filter indicator -->
    <?php if ($active !== '' || $search !== ''): ?>
    <div class="articles-filter-active">
        <span>
            <i class="ph ph-funnel"></i>
            <?php if ($search !== '' && $active !== ''): ?>
                Mencari "<strong><?= e($search) ?></strong>" di kategori <strong><?= e($active) ?></strong>
            <?php elseif ($search !== ''): ?>
                Mencari "<strong><?= e($search) ?></strong>" di semua kategori
            <?php else: ?>
                Menampilkan artikel kategori: <strong><?= e($active) ?></strong>
            <?php endif; ?>
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
        $featCatSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($featured['category'])));
        $featReadTime = max(1, ceil(str_word_count(strip_tags($featured['content'] ?? '')) / 200));
        $featCoverImg = !empty($featured['cover_image']) ? url('assets/uploads/articles/' . e($featured['cover_image'])) : '';
    ?>
    <!-- FEATURED ARTICLE (enhanced) -->
    <section class="featured-article reveal">
        <a href="<?= url('artikel/' . (int) $featured['id']) ?>" class="featured-article-card glass-card">
            <div class="featured-cover cat-<?= e($featCatSlug) ?>">
                <?php if ($featCoverImg): ?>
                <img src="<?= $featCoverImg ?>" alt="<?= e($featured['title']) ?>" class="featured-cover-img" loading="lazy">
                <?php endif; ?>
                <div class="featured-cover-orbs" aria-hidden="true">
                    <span class="feat-orb feat-orb-1"></span>
                    <span class="feat-orb feat-orb-2"></span>
                </div>
                <div class="featured-cover-pattern"></div>
                <?php if (!$featCoverImg): ?>
                <i class="ph ph-newspaper featured-cover-icon"></i>
                <?php endif; ?>
                <span class="featured-badge">
                    <i class="ph ph-star-four"></i>
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
                        <time datetime="<?= date('Y-m-d', strtotime($featured['created_at'])) ?>">
                            <?= date('d M Y', strtotime($featured['created_at'])) ?>
                        </time>
                    </span>
                    <span class="featured-read-time">
                        <i class="ph ph-clock"></i>
                        <?= $featReadTime ?> menit baca
                    </span>
                    <?php if (!empty($featured['views'])): ?>
                    <span class="featured-views">
                        <i class="ph ph-eye"></i>
                        <?= number_format($featured['views']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <h2 class="featured-title"><?= e($featured['title']) ?></h2>
                <?php if (!empty($featured['excerpt'])): ?>
                <p class="featured-excerpt"><?= e($featured['excerpt']) ?></p>
                <?php endif; ?>
                <div class="featured-footer">
                    <span class="featured-author">
                        <?php if (!empty($featured['author_photo'])): ?>
                            <img src="<?= url('assets/uploads/users/' . e($featured['author_photo'])) ?>" alt="" class="featured-author-img">
                        <?php else: ?>
                            <span class="featured-author-avatar"><?= strtoupper(substr($featured['author_name'] ?? 'R', 0, 1)) ?></span>
                        <?php endif; ?>
                        <span>
                            <strong><?= e($featured['author_name'] ?? 'Redaksi') ?></strong>
                            <small>Penulis</small>
                        </span>
                    </span>
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
            <span class="articles-section-count">
                <span><?= count($articles) ?></span> artikel
            </span>
        </div>
        <?php endif; ?>
        
        <div class="pub-articles pub-articles-grid">
            <?php foreach ($articles as $i => $a): 
                $catSlug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($a['category'])));
                $readTime = max(1, ceil(str_word_count(strip_tags($a['content'] ?? '')) / 200));
                $coverImg = !empty($a['cover_image']) ? url('assets/uploads/articles/' . e($a['cover_image'])) : '';
            ?>
            <a href="<?= url('artikel/' . (int) $a['id']) ?>" 
               class="glass-card pub-article-card" 
               style="animation-delay: <?= min($i * 0.04, 0.5) ?>s">
                <div class="pac-cover cat-<?= e($catSlug) ?>">
                    <?php if ($coverImg): ?>
                    <img src="<?= $coverImg ?>" alt="<?= e($a['title']) ?>" class="pac-cover-img" loading="lazy">
                    <?php endif; ?>
                    <div class="pac-cover-pattern"></div>
                    <?php if (!$coverImg): ?>
                    <i class="ph ph-newspaper pac-cover-icon"></i>
                    <?php endif; ?>
                    <span class="pac-category"><?= e($a['category']) ?></span>
                    <?php if (!empty($a['views'])): ?>
                    <span class="pac-views">
                        <i class="ph ph-eye"></i>
                        <?= number_format($a['views']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="pac-body">
                    <div class="pac-meta">
                        <time datetime="<?= date('Y-m-d', strtotime($a['created_at'])) ?>">
                            <i class="ph ph-calendar-blank"></i>
                            <?= date('d M Y', strtotime($a['created_at'])) ?>
                        </time>
                        <span class="pac-read-time">
                            <i class="ph ph-clock"></i>
                            <?= $readTime ?> min
                        </span>
                    </div>
                    <h3 class="pac-title"><?= e($a['title']) ?></h3>
                    <?php if (!empty($a['excerpt'])): ?>
                    <p class="pac-excerpt"><?= e(mb_strimwidth($a['excerpt'], 0, 110, '…')) ?></p>
                    <?php endif; ?>
                    <div class="pac-footer">
                        <span class="pac-author">
                            <?php if (!empty($a['author_photo'])): ?>
                                <img src="<?= url('assets/uploads/users/' . e($a['author_photo'])) ?>" alt="" class="pac-author-img">
                            <?php else: ?>
                                <span class="pac-author-avatar"><?= strtoupper(substr($a['author_name'] ?? 'R', 0, 1)) ?></span>
                            <?php endif; ?>
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

    <!-- PAGINATION (dengan First/Last) -->
    <?php if ($pages > 1): ?>
    <nav class="articles-pager reveal" aria-label="Navigasi halaman artikel">
        <div class="articles-pager-info">
            <i class="ph ph-books"></i>
            <span>Halaman <strong><?= $page ?></strong> dari <strong><?= $pages ?></strong></span>
        </div>
        <div class="articles-pager-nav">
            <?php
            $baseQuery = [];
            if ($active !== '') $baseQuery['kategori'] = $active;
            if ($search !== '') $baseQuery['q'] = $search;
            
            $pagerUrl = function($p) use ($baseQuery) {
                $q = $baseQuery;
                $q['halaman'] = $p;
                return url('artikel?' . http_build_query($q));
            };
            ?>
            
            <?php if ($page > 1): ?>
            <a class="articles-pager-btn" href="<?= $pagerUrl(1) ?>" aria-label="Halaman pertama">
                <i class="ph ph-caret-double-left"></i>
            </a>
            <a class="articles-pager-btn" href="<?= $pagerUrl($page - 1) ?>" aria-label="Halaman sebelumnya">
                <i class="ph ph-caret-left"></i>
                <span>Sebelumnya</span>
            </a>
            <?php else: ?>
            <span class="articles-pager-btn disabled"><i class="ph ph-caret-double-left"></i></span>
            <span class="articles-pager-btn disabled">
                <i class="ph ph-caret-left"></i>
                <span>Sebelumnya</span>
            </span>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            
            if ($start > 1): ?>
                <a class="articles-pager-btn num" href="<?= $pagerUrl(1) ?>">1</a>
                <?php if ($start > 2): ?><span class="articles-pager-dots">•••</span><?php endif; ?>
            <?php endif;
            
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="articles-pager-btn num active" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                <a class="articles-pager-btn num" href="<?= $pagerUrl($i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor;
            
            if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="articles-pager-dots">•••</span><?php endif; ?>
                <a class="articles-pager-btn num" href="<?= $pagerUrl($pages) ?>"><?= $pages ?></a>
            <?php endif; ?>
            
            <?php if ($page < $pages): ?>
            <a class="articles-pager-btn" href="<?= $pagerUrl($page + 1) ?>" aria-label="Halaman berikutnya">
                <span>Berikutnya</span>
                <i class="ph ph-caret-right"></i>
            </a>
            <a class="articles-pager-btn" href="<?= $pagerUrl($pages) ?>" aria-label="Halaman terakhir">
                <i class="ph ph-caret-double-right"></i>
            </a>
            <?php else: ?>
            <span class="articles-pager-btn disabled">
                <span>Berikutnya</span>
                <i class="ph ph-caret-right"></i>
            </span>
            <span class="articles-pager-btn disabled"><i class="ph ph-caret-double-right"></i></span>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    
    <?php elseif ($active === '' && $search === '' && !$featured): ?>
    <!-- TOTAL EMPTY STATE (kaya) -->
    <section class="articles-empty reveal">
        <div class="articles-empty-card glass-card">
            <div class="articles-empty-illustration">
                <div class="empty-orb empty-orb-1"></div>
                <div class="empty-orb empty-orb-2"></div>
                <i class="ph ph-newspaper-clipping"></i>
                <span class="articles-empty-spark"></span>
            </div>
            <h3>Belum Ada Artikel</h3>
            <p>Saat ini belum ada artikel yang dipublikasikan. Artikel terbaru akan muncul di sini setelah diterbitkan oleh redaksi.</p>
            <div class="articles-empty-features">
                <span><i class="ph ph-check-circle"></i> Update berkala</span>
                <span><i class="ph ph-check-circle"></i> Berbagai kategori</span>
                <span><i class="ph ph-check-circle"></i> Terbuka untuk umum</span>
            </div>
            <div class="articles-empty-actions">
                <a href="<?= url('') ?>" class="btn btn-primary">
                    <i class="ph ph-house"></i>
                    <span class="btn-text">Kembali ke Beranda</span>
                </a>
                <a href="<?= url('event') ?>" class="btn btn-ghost">
                    <i class="ph ph-calendar-blank"></i>
                    <span class="btn-text">Lihat Event</span>
                </a>
            </div>
        </div>
    </section>
    
    <?php else: ?>
    <!-- EMPTY FOR CATEGORY/SEARCH -->
    <section class="articles-empty reveal">
        <div class="articles-empty-card glass-card">
            <div class="articles-empty-illustration">
                <div class="empty-orb empty-orb-1"></div>
                <div class="empty-orb empty-orb-2"></div>
                <i class="ph ph-magnifying-glass"></i>
                <span class="articles-empty-spark"></span>
            </div>
            <h3>Tidak Ada Hasil</h3>
            <p>
                <?php if ($search !== '' && $active !== ''): ?>
                    Tidak ada artikel untuk pencarian "<strong><?= e($search) ?></strong>" di kategori <strong><?= e($active) ?></strong>.
                <?php elseif ($search !== ''): ?>
                    Tidak ada artikel untuk pencarian "<strong><?= e($search) ?></strong>".
                <?php else: ?>
                    Belum ada artikel pada kategori <strong>"<?= e($active) ?>"</strong>.
                <?php endif; ?>
                Coba kata kunci lain atau lihat semua artikel.
            </p>
            <div class="articles-empty-actions">
                <a href="<?= url('artikel') ?>" class="btn btn-primary">
                    <i class="ph ph-squares-four"></i>
                    <span class="btn-text">Lihat Semua Artikel</span>
                </a>
                <?php if (!empty($categories)): ?>
                <a href="<?= url('artikel?kategori=' . urlencode($categories[array_rand($categories)])) ?>" class="btn btn-ghost">
                    <i class="ph ph-shuffle"></i>
                    <span class="btn-text">Coba Kategori Lain</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdSearch"><kbd>/</kbd> Cari Artikel</span>
    <span class="kbd-item"><kbd>Esc</kbd> Reset Filter</span>
</div>

<style>
/* ---- Hero Section ---- */
.articles-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 50px;
    max-width: 880px;
    margin: 0 auto;
    overflow: hidden;
}
.articles-hero-content { position: relative; z-index: 1; }

/* Decorative orbs */
.articles-orbs {
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
}
.articles-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: .25;
}
.articles-orb.orb-1 {
    width: 420px; height: 420px;
    background: #6366f1;
    top: -100px; left: -100px;
    animation: articlesOrb 22s ease-in-out infinite alternate;
}
.articles-orb.orb-2 {
    width: 340px; height: 340px;
    background: #22d3ee;
    bottom: -80px; right: -80px;
    animation: articlesOrb 26s ease-in-out infinite alternate-reverse;
}
.articles-orb.orb-3 {
    width: 240px; height: 240px;
    background: #8b5cf6;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    animation: articlesOrb 30s ease-in-out infinite;
}
@keyframes articlesOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.15); }
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

/* Search (enhanced dengan clear button) */
.articles-search {
    max-width: 560px;
    margin: 36px auto 0;
    position: relative;
    z-index: 1;
}
.articles-search-form { display: block; }
.articles-search-box {
    position: relative;
    display: flex;
    align-items: center;
}
.articles-search-box > i {
    position: absolute;
    left: 18px;
    font-size: 18px;
    color: var(--txt-2);
    pointer-events: none;
    z-index: 1;
}
.articles-search-box input {
    width: 100%;
    padding: 16px 110px 16px 50px;
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
.articles-search-box input::placeholder { color: var(--txt-2); }

.articles-search-clear {
    position: absolute;
    right: 60px;
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 14px;
    transition: all .2s;
    z-index: 1;
}
.articles-search-clear:hover {
    background: rgba(239,68,68,.15);
    color: var(--danger);
}
.articles-search-btn {
    position: absolute;
    right: 6px;
    width: 44px; height: 44px;
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
.articles-search-btn:active { transform: scale(0.98); }

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
    padding: 10px 16px;
    border-radius: 99px;
    background: var(--glass);
    backdrop-filter: blur(14px);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
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
.chip-count {
    min-width: 20px;
    padding: 1px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.chip-active .chip-count { background: rgba(255,255,255,.25); }

/* Category-specific colors */
.chip-cat-berita:not(.chip-active) { color: #a5b4fc; border-color: rgba(99,102,241,.3); }
.chip-cat-berita:not(.chip-active) .chip-count { background: rgba(99,102,241,.15); color: #a5b4fc; }
.chip-cat-tips:not(.chip-active),
.chip-cat-tips-dan-trik:not(.chip-active) { color: #67e8f9; border-color: rgba(34,211,238,.3); }
.chip-cat-tips:not(.chip-active) .chip-count,
.chip-cat-tips-dan-trik:not(.chip-active) .chip-count { background: rgba(34,211,238,.15); color: #67e8f9; }
.chip-cat-pengumuman:not(.chip-active) { color: #fcd34d; border-color: rgba(245,158,11,.3); }
.chip-cat-pengumuman:not(.chip-active) .chip-count { background: rgba(245,158,11,.15); color: #fcd34d; }
.chip-cat-event:not(.chip-active),
.chip-cat-kegiatan:not(.chip-active) { color: #6ee7b7; border-color: rgba(16,185,129,.3); }
.chip-cat-event:not(.chip-active) .chip-count,
.chip-cat-kegiatan:not(.chip-active) .chip-count { background: rgba(16,185,129,.15); color: #6ee7b7; }
.chip-cat-opini:not(.chip-active) { color: #f9a8d4; border-color: rgba(236,72,153,.3); }
.chip-cat-opini:not(.chip-active) .chip-count { background: rgba(236,72,153,.15); color: #f9a8d4; }

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
    animation: filter-in 0.4s cubic-bezier(.22,1,.36,1);
    flex-wrap: wrap;
}
@keyframes filter-in {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.articles-filter-active i { color: var(--pri); margin-right: 6px; }
.articles-filter-active strong { color: var(--txt-0); }
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
    text-decoration: none;
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

/* ---- Featured Article (enhanced) ---- */
.featured-article { margin-bottom: 48px; }
.featured-article-card {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(.22,1,.36,1);
    text-decoration: none;
    color: inherit;
    position: relative;
}
.featured-article-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 80px rgba(2,6,23,.5);
    border-color: rgba(99,102,241,.35);
}
.featured-cover {
    position: relative;
    min-height: 320px;
    display: grid;
    place-items: center;
    font-size: 80px;
    color: rgba(255,255,255,.4);
    overflow: hidden;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #22d3ee 100%);
}

/* Category-specific featured cover colors */
.cat-berita { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
.cat-tips, .cat-tips-dan-trik { background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%); }
.cat-pengumuman { background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); }
.cat-event, .cat-kegiatan { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.cat-opini { background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); }

.featured-cover-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .6s cubic-bezier(.22,1,.36,1);
    z-index: 1;
}
.featured-article-card:hover .featured-cover-img { transform: scale(1.06); }
.featured-cover::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(4,8,20,.4) 100%);
    z-index: 2;
    pointer-events: none;
}
.featured-cover-orbs {
    position: absolute; inset: 0;
    pointer-events: none;
    z-index: 2;
}
.feat-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: .4;
}
.feat-orb-1 {
    width: 180px; height: 180px;
    background: rgba(255,255,255,.4);
    top: -40px; right: -40px;
    animation: featOrb 12s ease-in-out infinite alternate;
}
.feat-orb-2 {
    width: 140px; height: 140px;
    background: rgba(255,255,255,.3);
    bottom: -30px; left: -30px;
    animation: featOrb 15s ease-in-out infinite alternate-reverse;
}
@keyframes featOrb {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(30px, 20px) scale(1.2); }
}

.featured-cover-pattern {
    position: absolute; inset: 0;
    background-image: 
        radial-gradient(circle at 30% 40%, rgba(255,255,255,0.12) 1px, transparent 1px),
        radial-gradient(circle at 70% 60%, rgba(255,255,255,0.08) 1px, transparent 1px);
    background-size: 30px 30px, 40px 40px;
    z-index: 3;
}
.featured-cover-icon {
    position: relative;
    z-index: 4;
    filter: drop-shadow(0 8px 20px rgba(0,0,0,.3));
    animation: feat-icon 4s ease-in-out infinite;
}
@keyframes feat-icon {
    0%, 100% { transform: scale(1); opacity: .5; }
    50% { transform: scale(1.05); opacity: .7; }
}
.featured-badge {
    position: absolute;
    top: 20px; left: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 99px;
    background: rgba(10,15,31,.65);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.2);
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #fbbf24;
    z-index: 5;
    box-shadow: 0 6px 16px rgba(0,0,0,.3);
}
.featured-badge i { font-size: 12px; }

.featured-body {
    padding: 36px;
    display: flex;
    flex-direction: column;
}
.featured-meta {
    display: flex;
    align-items: center;
    gap: 10px;
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
.featured-read-time,
.featured-views {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: var(--txt-2);
    font-weight: 600;
}
.featured-date i,
.featured-read-time i,
.featured-views i {
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
    transition: color .3s;
}
.featured-article-card:hover .featured-title { color: var(--acc); }

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

.featured-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-top: 18px;
    border-top: 1px solid var(--glass-brd);
    margin-top: auto;
    flex-wrap: wrap;
}
.featured-author {
    display: flex;
    align-items: center;
    gap: 10px;
}
.featured-author-img,
.featured-author-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.featured-author-avatar {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    display: grid;
    place-items: center;
}
.featured-author span {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}
.featured-author strong {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--txt-0);
}
.featured-author small {
    font-size: 10.5px;
    color: var(--txt-2);
}

.featured-read-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--acc);
    transition: all 0.2s;
    padding: 8px 16px;
    border-radius: 99px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
}
.featured-article-card:hover .featured-read-btn {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    gap: 12px;
    box-shadow: 0 8px 20px rgba(99,102,241,.35);
}

/* ---- Articles Section ---- */
.articles-section { margin-bottom: 40px; }
.articles-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
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
    letter-spacing: -.3px;
}
.articles-section-title i { font-size: 22px; color: var(--acc); }
.articles-section-count {
    padding: 4px 12px;
    border-radius: 99px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    font-size: 11px;
    font-weight: 700;
    color: var(--txt-1);
}
.articles-section-count span { color: var(--acc); font-weight: 800; }

/* ---- Article Card Enhancements ---- */
.pub-article-card {
    text-decoration: none;
    color: inherit;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    animation: card-in 0.5s cubic-bezier(.22,1,.36,1) both;
    position: relative;
    overflow: hidden;
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
    overflow: hidden;
    min-height: 180px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}
.pac-cover-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .6s cubic-bezier(.22,1,.36,1);
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
    font-size: 50px;
    color: rgba(255,255,255,.5);
    filter: drop-shadow(0 4px 12px rgba(0,0,0,.2));
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
.pac-views {
    position: absolute;
    top: 12px; right: 12px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(10,15,31,.65);
    backdrop-filter: blur(8px);
    font-size: 10px;
    font-weight: 700;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    z-index: 5;
}
.pac-views i { font-size: 11px; color: var(--acc); }

.pac-body { padding: 20px; }
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
.pac-meta time i { font-size: 13px; color: var(--acc); }
.pac-read-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--txt-2);
    font-size: 11px;
    font-weight: 600;
}
.pac-read-time i { font-size: 12px; color: var(--acc); }

.pac-title {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.35;
    margin-bottom: 10px;
    letter-spacing: -.2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color .3s;
}
.pub-article-card:hover .pac-title { color: var(--acc); }

.pac-excerpt {
    font-size: 13px;
    line-height: 1.6;
    color: var(--txt-1);
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
    gap: 8px;
    font-size: 11.5px;
    color: var(--txt-2);
    font-weight: 600;
}
.pac-author-img,
.pac-author-avatar {
    width: 24px; height: 24px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.pac-author-avatar {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    display: grid;
    place-items: center;
}
.pac-author i { font-size: 14px; color: var(--acc); }
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
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--txt-1);
}
.articles-pager-info i { font-size: 18px; color: var(--acc); }
.articles-pager-info strong { color: var(--txt-0); font-weight: 800; }
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
    transition: all 0.2s cubic-bezier(.22,1,.36,1);
}
.articles-pager-btn:hover:not(.disabled):not(.active) {
    background: rgba(255,255,255,.08);
    color: var(--txt-0);
    border-color: var(--glass-brd-2);
    transform: translateY(-1px);
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
    font-weight: 800;
}
.articles-pager-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.articles-pager-dots {
    color: var(--txt-2);
    padding: 0 4px;
    font-size: 11px;
}

/* ---- Empty State (kaya) ---- */
.articles-empty {
    display: flex;
    justify-content: center;
    padding: 40px 0 80px;
}
.articles-empty-card {
    text-align: center;
    padding: 60px 40px;
    max-width: 520px;
    width: 100%;
}
.articles-empty-illustration {
    position: relative;
    width: 100px; height: 100px;
    margin: 0 auto 24px;
    border-radius: 28px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid;
    place-items: center;
    font-size: 42px;
    color: var(--acc);
    overflow: hidden;
    box-shadow: 0 14px 34px rgba(99,102,241,.25);
}
.empty-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(20px);
    opacity: 0.5;
    pointer-events: none;
}
.empty-orb-1 {
    width: 70px; height: 70px;
    background: var(--pri);
    top: -25px; left: -25px;
    animation: emptyOrb 4s ease-in-out infinite;
}
.empty-orb-2 {
    width: 60px; height: 60px;
    background: var(--acc);
    bottom: -20px; right: -20px;
    animation: emptyOrb 5s ease-in-out infinite reverse;
}
@keyframes emptyOrb {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10px, -10px); }
}
.articles-empty-spark {
    position: absolute;
    inset: -12px;
    border-radius: 40px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(14px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.articles-empty-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -.3px;
}
.articles-empty-card p {
    color: var(--txt-1);
    font-size: 14.5px;
    margin-bottom: 20px;
    max-width: 44ch;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.65;
}
.articles-empty-features {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.articles-empty-features span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
}
.articles-empty-features i { font-size: 14px; color: var(--ok); }
.articles-empty-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    max-width: 1180px;
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
    cursor: pointer;
    transition: all .2s;
}
.kbd-item:hover {
    background: rgba(99,102,241,.08);
    border-color: rgba(99,102,241,.3);
    color: var(--acc);
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
    .featured-article-card { grid-template-columns: 1fr; }
    .featured-cover { min-height: 240px; }
    .featured-title { font-size: 24px; }
}

@media (max-width: 768px) {
    .articles-hero { padding-top: 120px; }
    .articles-orb { filter: blur(80px); opacity: .18; }
    .articles-pager {
        flex-direction: column;
        text-align: center;
        gap: 14px;
    }
    .articles-pager-nav { justify-content: center; }
    .featured-body { padding: 24px; }
    .featured-footer { flex-direction: column; align-items: flex-start; gap: 12px; }
    .featured-read-btn { align-self: stretch; justify-content: center; }
    .keyboard-hints { display: none; }
}

@media (max-width: 520px) {
    .featured-cover { min-height: 200px; }
    .featured-title { font-size: 20px; }
    .featured-excerpt { font-size: 13px; -webkit-line-clamp: 2; }
    .articles-empty-card { padding: 40px 24px; }
    .articles-empty-features { flex-direction: column; gap: 8px; align-items: center; }
    .articles-empty-actions { flex-direction: column; width: 100%; }
    .articles-empty-actions .btn { width: 100%; justify-content: center; }
    .pac-title { font-size: 14.5px; }
    .articles-search-box input { padding: 14px 100px 14px 44px; font-size: 13px; }
    .articles-search-btn { width: 40px; height: 40px; font-size: 16px; }
    .articles-search-clear { right: 52px; width: 28px; height: 28px; }
}
</style>

<script>
(function(){
    'use strict';
    
    /* ---- Search dengan clear button ---- */
    const searchInput = document.getElementById('articleSearchInput');
    const clearBtn = document.getElementById('btnSearchClear');
    const searchForm = document.getElementById('articleSearchForm');
    
    if (searchInput && clearBtn) {
        const updateClearBtn = () => {
            clearBtn.style.display = searchInput.value ? 'grid' : 'none';
        };
        searchInput.addEventListener('input', updateClearBtn);
        updateClearBtn();
        
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
        });
    }
    
    /* ---- Search button loading state ---- */
    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            const btn = document.getElementById('btnArticleSearch');
            if (btn && searchInput?.value.trim()) {
                btn.innerHTML = '<i class="ph ph-spinner" style="animation:spin 1s linear infinite"></i>';
                btn.disabled = true;
            }
        });
    }
    
    /* ---- Keyboard shortcuts ---- */
    document.getElementById('kbdSearch')?.addEventListener('click', () => {
        searchInput?.focus();
    });
    
    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
        
        // / = focus search
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey && !inInput) {
            e.preventDefault();
            searchInput?.focus();
            searchInput?.select();
        }
        
        // Esc = reset filter (jika ada filter aktif)
        if (e.key === 'Escape' && !inInput) {
            const clearLink = document.querySelector('.articles-filter-clear');
            if (clearLink) {
                e.preventDefault();
                window.location.href = clearLink.href;
            }
        }
    });
})();
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>