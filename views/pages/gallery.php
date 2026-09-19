<?php
/**
 * ============================================================
 * GALERI KEGIATAN PUBLIK — ULTIMATE EDITION v5.9
 * Galeri masonry dengan lightbox, filter, dan animasi reveal.
 * ============================================================
 */

$total = $total ?? 0;
$galleries = $galleries ?? [];
$page = $page ?? 1;
$pages = $pages ?? 1;

// Ekstrak tahun unik untuk filter
$years = [];
foreach ($galleries as $g) {
    if (!empty($g['event_date'])) {
        $years[] = date('Y', strtotime($g['event_date']));
    }
}
$years = array_unique($years);
rsort($years);

// Ekstrak lokasi unik untuk filter
$locations = [];
foreach ($galleries as $g) {
    if (!empty($g['location'])) {
        $locations[] = $g['location'];
    }
}
$locations = array_unique($locations);
?>

<!-- ========== HERO SECTION ========== -->
<section class="gallery-hero">
    <div class="gallery-hero-content reveal">
        <span class="page-eyebrow">Dokumentasi Visual</span>
        <h1 class="gallery-hero-title">Galeri Kegiatan</h1>
        <p class="gallery-hero-sub">
            Kilasan momen dari berbagai kegiatan organisasi — 
            <strong><?= number_format($total) ?></strong> foto tercatat dalam arsip digital kami.
        </p>
    </div>
    
    <!-- Gallery Stats -->
    <?php if ($total > 0): ?>
    <div class="gallery-stats reveal">
        <div class="gallery-stat">
            <i class="ph ph-images"></i>
            <div>
                <strong><?= number_format($total) ?></strong>
                <span>Total Foto</span>
            </div>
        </div>
        <div class="gallery-stat">
            <i class="ph ph-calendar"></i>
            <div>
                <strong><?= count($years) ?></strong>
                <span>Tahun Arsip</span>
            </div>
        </div>
        <div class="gallery-stat">
            <i class="ph ph-map-pin"></i>
            <div>
                <strong><?= count($locations) ?></strong>
                <span>Lokasi</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- ========== FILTER BAR ========== -->
<?php if (!empty($galleries)): ?>
<section class="gallery-filter-section reveal">
    <div class="gallery-filter-bar glass-card">
        <div class="gallery-filter-group">
            <button class="gallery-filter-btn active" data-filter="all">
                <i class="ph ph-images"></i>
                <span>Semua</span>
                <span class="filter-count"><?= $total ?></span>
            </button>
            <?php foreach ($years as $year): ?>
            <button class="gallery-filter-btn" data-filter="year" data-value="<?= $year ?>">
                <i class="ph ph-calendar-blank"></i>
                <span><?= $year ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <div class="gallery-filter-actions">
            <button class="gallery-view-btn active" data-view="masonry" title="Tampilan Grid">
                <i class="ph ph-squares-four"></i>
            </button>
            <button class="gallery-view-btn" data-view="list" title="Tampilan List">
                <i class="ph ph-list"></i>
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ========== GALLERY GRID ========== -->
<div class="gallery-container">
    <?php if (!empty($galleries)): ?>
    <div class="masonry pub-articles-grid gallery-grid" id="galleryGrid">
        <?php foreach ($galleries as $i => $g): 
            $year = !empty($g['event_date']) ? date('Y', strtotime($g['event_date'])) : '';
            $location = $g['location'] ?? '';
        ?>
        <figure class="masonry-item gallery-item reveal"
                style="animation-delay: <?= min($i * 0.05, 0.5) ?>s"
                data-year="<?= $year ?>"
                data-location="<?= e($location) ?>"
                data-full="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>"
                data-title="<?= e($g['title']) ?>"
                data-date="<?= !empty($g['event_date']) ? date('d M Y', strtotime($g['event_date'])) : '' ?>"
                data-location-text="<?= e($location) ?>">
            <img src="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>" 
                 alt="<?= e($g['title']) ?>" 
                 loading="lazy"
                 onload="this.classList.add('loaded')">
            <div class="gallery-item-overlay">
                <button class="gallery-zoom-btn" aria-label="Perbesar">
                    <i class="ph ph-magnifying-glass-plus"></i>
                </button>
            </div>
            <figcaption>
                <div class="gallery-caption">
                    <strong><?= e($g['title']) ?></strong>
                    <?php if (!empty($g['event_date']) || !empty($location)): ?>
                    <span>
                        <?php if (!empty($g['event_date'])): ?>
                            <i class="ph ph-calendar"></i> <?= date('d M Y', strtotime($g['event_date'])) ?>
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <i class="ph ph-map-pin"></i> <?= e($location) ?>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                </div>
            </figcaption>
        </figure>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="gallery-pager reveal">
        <div class="gallery-pager-info">
            <span>Halaman <?= $page ?> dari <?= $pages ?></span>
        </div>
        <div class="gallery-pager-nav">
            <?php if ($page > 1): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . ($page - 1)) ?>" aria-label="Halaman sebelumnya">
                    <i class="ph ph-caret-left"></i>
                </a>
            <?php else: ?>
                <span class="gallery-pager-btn disabled">
                    <i class="ph ph-caret-left"></i>
                </span>
            <?php endif; ?>
            
            <!-- Page numbers -->
            <?php
            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            if ($start > 1): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=1') ?>">1</a>
                <?php if ($start > 2): ?><span class="gallery-pager-dots">...</span><?php endif; ?>
            <?php endif;
            
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="gallery-pager-btn active"><?= $i ?></span>
                <?php else: ?>
                    <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor;
            
            if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="gallery-pager-dots">...</span><?php endif; ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . $pages) ?>"><?= $pages ?></a>
            <?php endif; ?>
            
            <?php if ($page < $pages): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . ($page + 1)) ?>" aria-label="Halaman berikutnya">
                    <i class="ph ph-caret-right"></i>
                </a>
            <?php else: ?>
                <span class="gallery-pager-btn disabled">
                    <i class="ph ph-caret-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty state -->
    <section class="gallery-empty reveal">
        <div class="gallery-empty-card glass-card">
            <div class="gallery-empty-icon">
                <i class="ph ph-image-broken"></i>
                <span class="gallery-empty-spark"></span>
            </div>
            <h3>Belum Ada Dokumentasi</h3>
            <p>Saat ini belum ada foto kegiatan yang diunggah. Dokumentasi akan muncul setelah kegiatan pertama terlaksana.</p>
            <a href="<?= url('event') ?>" class="btn btn-ghost">
                <i class="ph ph-calendar-blank"></i>
                <span class="btn-text">Lihat Jadwal Event</span>
            </a>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- ========== ENHANCED LIGHTBOX ========== -->
<div class="gallery-lightbox" id="galleryLightbox" role="dialog" aria-label="Lightbox galeri">
    <div class="gallery-lightbox-backdrop" id="lightboxBackdrop"></div>
    <div class="gallery-lightbox-content">
        <!-- Header -->
        <div class="gallery-lightbox-header">
            <div class="gallery-lightbox-info">
                <span class="gallery-lightbox-counter">
                    <span id="lightboxCurrent">1</span> / <span id="lightboxTotal">1</span>
                </span>
                <span class="gallery-lightbox-title" id="lightboxTitle">—</span>
            </div>
            <div class="gallery-lightbox-actions">
                <a href="#" class="gallery-lightbox-btn" id="lightboxDownload" download title="Unduh foto">
                    <i class="ph ph-download-simple"></i>
                </a>
                <button class="gallery-lightbox-btn" id="lightboxClose" aria-label="Tutup">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
        
        <!-- Image -->
        <div class="gallery-lightbox-image">
            <button class="gallery-lightbox-nav prev" id="lightboxPrev" aria-label="Sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <img src="" alt="" id="lightboxImg" class="gallery-lightbox-img">
            <button class="gallery-lightbox-nav next" id="lightboxNext" aria-label="Berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
        </div>
        
        <!-- Footer info -->
        <div class="gallery-lightbox-footer" id="lightboxFooter">
            <div class="gallery-lightbox-meta">
                <span id="lightboxDate"><i class="ph ph-calendar"></i> —</span>
                <span id="lightboxLocation"><i class="ph ph-map-pin"></i> —</span>
            </div>
        </div>
    </div>
</div>

<!-- ========== STYLING ========== -->
<style>
/* ---- Hero Section ---- */
.gallery-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 50px;
    max-width: 800px;
    margin: 0 auto;
}
.gallery-hero-title {
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
.gallery-hero-sub {
    color: var(--txt-1);
    font-size: 16px;
    line-height: 1.7;
    max-width: 52ch;
    margin: 0 auto;
}
.gallery-hero-sub strong {
    color: var(--txt-0);
}

/* ---- Gallery Stats ---- */
.gallery-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 36px;
}
.gallery-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s;
}
.gallery-stat:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 12px 30px rgba(2,6,23,.3);
}
.gallery-stat i {
    font-size: 22px;
    color: var(--acc);
}
.gallery-stat strong {
    display: block;
    font-size: 22px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.gallery-stat span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-1);
}

/* ---- Filter Bar ---- */
.gallery-filter-section {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px 30px;
}
.gallery-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    padding: 12px 16px;
    flex-wrap: wrap;
}
.gallery-filter-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.gallery-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid transparent;
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.gallery-filter-btn i { font-size: 15px; }
.gallery-filter-btn:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
    border-color: var(--glass-brd);
}
.gallery-filter-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.filter-count {
    padding: 2px 8px;
    border-radius: 99px;
    background: rgba(255,255,255,.15);
    font-size: 10px;
    font-weight: 700;
}
.gallery-filter-btn.active .filter-count {
    background: rgba(255,255,255,.25);
}

.gallery-filter-actions {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.gallery-view-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.gallery-view-btn i { font-size: 16px; }
.gallery-view-btn:hover { color: var(--txt-0); }
.gallery-view-btn.active {
    background: var(--glass);
    color: var(--acc);
}

/* ---- Gallery Container ---- */
.gallery-container {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px 80px;
}

/* ---- Gallery Item Enhancements ---- */
.gallery-item {
    position: relative;
    cursor: pointer;
    overflow: hidden;
}
.gallery-item img {
    transition: transform 0.5s cubic-bezier(.22,1,.36,1), opacity 0.3s;
    opacity: 0;
}
.gallery-item img.loaded {
    opacity: 1;
}
.gallery-item:hover img {
    transform: scale(1.08);
}
.gallery-item-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,.6) 100%);
    opacity: 0;
    transition: opacity 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}
.gallery-item:hover .gallery-item-overlay {
    opacity: 1;
}
.gallery-zoom-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.3);
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 20px;
    transition: all 0.3s;
    transform: scale(0.8);
    opacity: 0;
}
.gallery-item:hover .gallery-zoom-btn {
    transform: scale(1);
    opacity: 1;
}
.gallery-zoom-btn:hover {
    background: var(--pri);
    border-color: var(--pri);
    transform: scale(1.1);
}

.gallery-caption {
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    z-index: 2;
}
.gallery-caption strong {
    font-size: 13px;
    font-weight: 700;
}
.gallery-caption span {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: rgba(255,255,255,.8);
    font-weight: 600;
}
.gallery-caption i {
    font-size: 12px;
    color: var(--acc);
}

/* ---- Gallery Pager ---- */
.gallery-pager {
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
}
.gallery-pager-info {
    font-size: 13px;
    color: var(--txt-1);
}
.gallery-pager-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}
.gallery-pager-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 10px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.gallery-pager-btn:hover:not(.disabled):not(.active) {
    background: rgba(255,255,255,.1);
    color: var(--txt-0);
    border-color: var(--glass-brd-2);
}
.gallery-pager-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.gallery-pager-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.gallery-pager-dots {
    color: var(--txt-2);
    padding: 0 4px;
}

/* ---- Empty State ---- */
.gallery-empty {
    display: flex;
    justify-content: center;
    padding: 60px 24px;
}
.gallery-empty-card {
    text-align: center;
    padding: 60px 40px;
    max-width: 480px;
}
.gallery-empty-icon {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid;
    place-items: center;
    font-size: 36px;
    color: var(--acc);
}
.gallery-empty-spark {
    position: absolute;
    inset: -10px;
    border-radius: 34px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(12px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.gallery-empty-card h3 {
    font-size: 20px;
    font-weight: 800;
    margin-bottom: 10px;
}
.gallery-empty-card p {
    color: var(--txt-1);
    font-size: 14px;
    margin-bottom: 24px;
}

/* ---- Enhanced Lightbox ---- */
.gallery-lightbox {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, visibility 0.3s;
}
.gallery-lightbox.show {
    opacity: 1;
    visibility: visible;
}
.gallery-lightbox-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(4, 8, 20, 0.95);
    backdrop-filter: blur(20px);
}
.gallery-lightbox-content {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 20px;
}
.gallery-lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    z-index: 2;
}
.gallery-lightbox-info {
    display: flex;
    align-items: center;
    gap: 16px;
}
.gallery-lightbox-counter {
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: var(--txt-0);
}
.gallery-lightbox-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--txt-0);
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.gallery-lightbox-actions {
    display: flex;
    gap: 8px;
}
.gallery-lightbox-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.2s;
}
.gallery-lightbox-btn:hover {
    background: rgba(255,255,255,.2);
    transform: scale(1.05);
}
.gallery-lightbox-image {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 20px 60px;
}
.gallery-lightbox-img {
    max-width: 100%;
    max-height: calc(100vh - 200px);
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 40px 100px rgba(0,0,0,.5);
    animation: lightbox-zoom 0.3s cubic-bezier(.22,1,.36,1);
}
@keyframes lightbox-zoom {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.gallery-lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 22px;
    transition: all 0.2s;
}
.gallery-lightbox-nav:hover {
    background: var(--pri);
    border-color: var(--pri);
    transform: translateY(-50%) scale(1.1);
}
.gallery-lightbox-nav.prev { left: 20px; }
.gallery-lightbox-nav.next { right: 20px; }
.gallery-lightbox-footer {
    padding: 10px 20px;
    z-index: 2;
}
.gallery-lightbox-meta {
    display: flex;
    gap: 20px;
    justify-content: center;
    font-size: 13px;
    color: var(--txt-1);
}
.gallery-lightbox-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.gallery-lightbox-meta i {
    color: var(--acc);
}

/* ---- List View ---- */
.gallery-grid.list-view {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.gallery-grid.list-view .gallery-item {
    display: flex;
    gap: 20px;
    width: 100%;
    margin: 0;
    break-inside: auto;
}
.gallery-grid.list-view .gallery-item img {
    width: 200px;
    height: 140px;
    object-fit: cover;
    border-radius: 12px;
    flex-shrink: 0;
}
.gallery-grid.list-view figcaption {
    position: static;
    background: none;
    padding: 10px 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.gallery-grid.list-view .gallery-caption strong {
    font-size: 16px;
}
.gallery-grid.list-view .gallery-item-overlay {
    border-radius: 12px;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .gallery-hero { padding-top: 120px; }
    .gallery-stats { gap: 12px; }
    .gallery-stat { padding: 12px 18px; }
    .gallery-stat strong { font-size: 18px; }
    .gallery-filter-bar { flex-direction: column; align-items: stretch; }
    .gallery-filter-group { justify-content: center; }
    .gallery-filter-actions { justify-content: center; }
    .gallery-pager { flex-direction: column; gap: 16px; }
    .gallery-lightbox-header { flex-direction: column; gap: 10px; }
    .gallery-lightbox-title { max-width: 200px; font-size: 13px; }
    .gallery-lightbox-image { padding: 10px; }
    .gallery-lightbox-nav { width: 40px; height: 40px; }
    .gallery-lightbox-nav.prev { left: 10px; }
    .gallery-lightbox-nav.next { right: 10px; }
    .gallery-grid.list-view .gallery-item { flex-direction: column; }
    .gallery-grid.list-view .gallery-item img { width: 100%; height: auto; }
}
</style>

<script>
/* =========================================================
   GALLERY ULTIMATE — Filter, Lightbox, View Toggle
   ========================================================= */
(() => {
    'use strict';
    
    const items = document.querySelectorAll('.gallery-item');
    const filterBtns = document.querySelectorAll('.gallery-filter-btn');
    const viewBtns = document.querySelectorAll('.gallery-view-btn');
    const grid = document.getElementById('galleryGrid');
    
    // ---- Filter functionality ----
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filter = btn.dataset.filter;
            const value = btn.dataset.value;
            
            items.forEach(item => {
                let show = false;
                if (filter === 'all') {
                    show = true;
                } else if (filter === 'year') {
                    show = item.dataset.year === value;
                } else if (filter === 'location') {
                    show = item.dataset.location === value;
                }
                
                if (show) {
                    item.style.display = '';
                    item.style.animation = 'fade-up 0.4s both';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // ---- View toggle ----
    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            viewBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            if (grid) {
                grid.classList.toggle('list-view', btn.dataset.view === 'list');
            }
        });
    });
    
    // ---- Enhanced Lightbox ----
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxCurrent = document.getElementById('lightboxCurrent');
    const lightboxTotal = document.getElementById('lightboxTotal');
    const lightboxDate = document.getElementById('lightboxDate');
    const lightboxLocation = document.getElementById('lightboxLocation');
    const lightboxDownload = document.getElementById('lightboxDownload');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxBackdrop = document.getElementById('lightboxBackdrop');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');
    
    let currentIndex = 0;
    let visibleItems = [];
    
    function updateVisibleItems() {
        visibleItems = Array.from(items).filter(item => item.style.display !== 'none');
    }
    
    function showImage(index) {
        updateVisibleItems();
        if (index < 0 || index >= visibleItems.length) return;
        
        currentIndex = index;
        const item = visibleItems[currentIndex];
        
        lightboxImg.src = item.dataset.full;
        lightboxImg.alt = item.dataset.title;
        lightboxTitle.textContent = item.dataset.title || '—';
        lightboxCurrent.textContent = currentIndex + 1;
        lightboxTotal.textContent = visibleItems.length;
        
        const date = item.dataset.date;
        const loc = item.dataset.locationText;
        lightboxDate.innerHTML = `<i class="ph ph-calendar"></i> ${date || '—'}`;
        lightboxLocation.innerHTML = `<i class="ph ph-map-pin"></i> ${loc || '—'}`;
        
        lightboxDownload.href = item.dataset.full;
        
        // Update nav buttons
        lightboxPrev.style.visibility = currentIndex > 0 ? 'visible' : 'hidden';
        lightboxNext.style.visibility = currentIndex < visibleItems.length - 1 ? 'visible' : 'hidden';
        
        // Re-trigger animation
        lightboxImg.style.animation = 'none';
        lightboxImg.offsetHeight; // force reflow
        lightboxImg.style.animation = 'lightbox-zoom 0.3s cubic-bezier(.22,1,.36,1)';
    }
    
    function openLightbox(index) {
        updateVisibleItems();
        showImage(index);
        lightbox.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeLightbox() {
        lightbox.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Bind click events
    items.forEach((item, i) => {
        item.addEventListener('click', () => {
            updateVisibleItems();
            const visibleIndex = visibleItems.indexOf(item);
            openLightbox(visibleIndex);
        });
    });
    
    lightboxClose?.addEventListener('click', closeLightbox);
    lightboxBackdrop?.addEventListener('click', closeLightbox);
    lightboxPrev?.addEventListener('click', () => showImage(currentIndex - 1));
    lightboxNext?.addEventListener('click', () => showImage(currentIndex + 1));
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('show')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
        if (e.key === 'ArrowRight') showImage(currentIndex + 1);
    });
    
    // Touch swipe for mobile
    let touchStartX = 0;
    lightboxImg?.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    });
    lightboxImg?.addEventListener('touchend', (e) => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) showImage(currentIndex + 1);
            else showImage(currentIndex - 1);
        }
    });
})();
</script>