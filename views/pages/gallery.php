<?php
/**
 * ============================================================
 * GALERI KEGIATAN PUBLIK — ULTIMATE EDITION v7.0
 * Galeri masonry dengan lightbox zoom, filter animasi,
 * share integration, dan pengalaman visual sinematik.
 * ============================================================
 */

$total = $total ?? 0;
$galleries = $galleries ?? [];
$page = $page ?? 1;
$pages = $pages ?? 1;

// Ekstrak tahun unik untuk filter (dengan count)
$yearCounts = [];
foreach ($galleries as $g) {
    if (!empty($g['event_date'])) {
        $year = date('Y', strtotime($g['event_date']));
        $yearCounts[$year] = ($yearCounts[$year] ?? 0) + 1;
    }
}
krsort($yearCounts); // Newest first

// Ekstrak lokasi unik untuk filter (dengan count)
$locationCounts = [];
foreach ($galleries as $g) {
    if (!empty($g['location'])) {
        $loc = $g['location'];
        $locationCounts[$loc] = ($locationCounts[$loc] ?? 0) + 1;
    }
}
arsort($locationCounts); // Most frequent first

// Stats tambahan
$totalYears = count($yearCounts);
$totalLocations = count($locationCounts);
?>

<!-- ========== HERO SECTION ========== -->
<section class="gallery-hero">
    <!-- Decorative orbs -->
    <div class="gallery-orbs" aria-hidden="true">
        <span class="gallery-orb orb-1"></span>
        <span class="gallery-orb orb-2"></span>
        <span class="gallery-orb orb-3"></span>
    </div>

    <div class="gallery-hero-content reveal">
        <span class="page-eyebrow">
            <i class="ph ph-images"></i>
            Dokumentasi Visual
        </span>
        <h1 class="gallery-hero-title">Galeri Kegiatan</h1>
        <p class="gallery-hero-sub">
            Kilasan momen dari berbagai kegiatan organisasi — 
            <strong><?= number_format($total) ?></strong> foto tercatat dalam arsip digital kami.
        </p>
    </div>
    
    <!-- Gallery Stats (animated) -->
    <?php if ($total > 0): ?>
    <div class="gallery-stats reveal">
        <div class="gallery-stat stat-photos">
            <div class="gallery-stat-icon"><i class="ph ph-images"></i></div>
            <div class="gallery-stat-info">
                <strong data-count="<?= $total ?>">0</strong>
                <span>Total Foto</span>
            </div>
        </div>
        <div class="gallery-stat stat-years">
            <div class="gallery-stat-icon"><i class="ph ph-calendar"></i></div>
            <div class="gallery-stat-info">
                <strong data-count="<?= $totalYears ?>">0</strong>
                <span>Tahun Arsip</span>
            </div>
        </div>
        <div class="gallery-stat stat-locations">
            <div class="gallery-stat-icon"><i class="ph ph-map-pin"></i></div>
            <div class="gallery-stat-info">
                <strong data-count="<?= $totalLocations ?>">0</strong>
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
        <div class="gallery-filter-group" id="filterGroup">
            <button class="gallery-filter-btn active" data-filter="all" role="tab" aria-selected="true">
                <i class="ph ph-images"></i>
                <span>Semua</span>
                <span class="filter-count" id="filterCountAll"><?= $total ?></span>
            </button>
            <?php foreach ($yearCounts as $year => $count): ?>
            <button class="gallery-filter-btn" data-filter="year" data-value="<?= $year ?>" role="tab" aria-selected="false">
                <i class="ph ph-calendar-blank"></i>
                <span><?= $year ?></span>
                <span class="filter-count"><?= $count ?></span>
            </button>
            <?php endforeach; ?>
            
            <?php if (count($locationCounts) > 0 && count($locationCounts) <= 5): ?>
            <span class="filter-divider"></span>
            <?php foreach ($locationCounts as $loc => $count): ?>
            <button class="gallery-filter-btn filter-location" data-filter="location" data-value="<?= e($loc) ?>" role="tab" aria-selected="false">
                <i class="ph ph-map-pin"></i>
                <span><?= e($loc) ?></span>
                <span class="filter-count"><?= $count ?></span>
            </button>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="gallery-filter-actions">
            <div class="gallery-view-toggle" role="tablist" aria-label="Mode tampilan">
                <button class="gallery-view-btn active" data-view="masonry" title="Tampilan Masonry" role="tab" aria-selected="true">
                    <i class="ph ph-squares-four"></i>
                </button>
                <button class="gallery-view-btn" data-view="list" title="Tampilan List" role="tab" aria-selected="false">
                    <i class="ph ph-list"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="gallery-filter-info" id="galleryFilterInfo" aria-live="polite">
        Menampilkan <strong id="galleryVisibleCount"><?= $total ?></strong> dari <?= $total ?> foto
    </div>
</section>
<?php endif; ?>

<!-- ========== GALLERY GRID ========== -->
<div class="gallery-container">
    <?php if (!empty($galleries)): ?>
    <div class="masonry gallery-grid" id="galleryGrid">
        <?php foreach ($galleries as $i => $g): 
            $year = !empty($g['event_date']) ? date('Y', strtotime($g['event_date'])) : '';
            $location = $g['location'] ?? '';
            $aspect = $g['aspect_ratio'] ?? ''; // optional field jika ada
        ?>
        <figure class="masonry-item gallery-item"
                style="animation-delay: <?= min($i * 0.04, 0.6) ?>s"
                data-year="<?= $year ?>"
                data-location="<?= e($location) ?>"
                data-full="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>"
                data-title="<?= e($g['title']) ?>"
                data-date="<?= !empty($g['event_date']) ? date('d M Y', strtotime($g['event_date'])) : '' ?>"
                data-location-text="<?= e($location) ?>"
                data-index="<?= $i ?>">
            <!-- Skeleton placeholder -->
            <div class="gallery-item-skeleton" aria-hidden="true">
                <div class="skel-shimmer"></div>
                <i class="ph ph-image"></i>
            </div>
            <img src="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>" 
                 alt="<?= e($g['title']) ?>" 
                 loading="lazy"
                 onload="this.parentElement.classList.add('loaded')">
            <div class="gallery-item-overlay">
                <div class="gallery-item-actions">
                    <button class="gallery-action-btn" data-action="zoom" aria-label="Perbesar" title="Perbesar">
                        <i class="ph ph-magnifying-glass-plus"></i>
                    </button>
                    <a href="<?= url('assets/uploads/galleries/' . e($g['image'])) ?>" 
                       target="_blank" 
                       rel="noopener"
                       class="gallery-action-btn" 
                       data-action="open"
                       aria-label="Buka di tab baru"
                       title="Buka di tab baru"
                       onclick="event.stopPropagation()">
                        <i class="ph ph-arrow-square-out"></i>
                    </a>
                </div>
            </div>
            <figcaption>
                <div class="gallery-caption">
                    <strong><?= e($g['title']) ?></strong>
                    <?php if (!empty($g['event_date']) || !empty($location)): ?>
                    <span class="gallery-caption-meta">
                        <?php if (!empty($g['event_date'])): ?>
                            <span><i class="ph ph-calendar"></i> <?= date('d M Y', strtotime($g['event_date'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($location)): ?>
                            <span><i class="ph ph-map-pin"></i> <?= e($location) ?></span>
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
            <i class="ph ph-books"></i>
            <span>Halaman <strong><?= $page ?></strong> dari <strong><?= $pages ?></strong></span>
        </div>
        <div class="gallery-pager-nav">
            <?php if ($page > 1): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=1') ?>" aria-label="Halaman pertama">
                    <i class="ph ph-caret-double-left"></i>
                </a>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . ($page - 1)) ?>" aria-label="Halaman sebelumnya">
                    <i class="ph ph-caret-left"></i>
                </a>
            <?php else: ?>
                <span class="gallery-pager-btn disabled"><i class="ph ph-caret-double-left"></i></span>
                <span class="gallery-pager-btn disabled"><i class="ph ph-caret-left"></i></span>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($pages, $page + 2);
            if ($start > 1): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=1') ?>">1</a>
                <?php if ($start > 2): ?><span class="gallery-pager-dots">•••</span><?php endif; ?>
            <?php endif;
            
            for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="gallery-pager-btn active" aria-current="page"><?= $i ?></span>
                <?php else: ?>
                    <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . $i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor;
            
            if ($end < $pages): ?>
                <?php if ($end < $pages - 1): ?><span class="gallery-pager-dots">•••</span><?php endif; ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . $pages) ?>"><?= $pages ?></a>
            <?php endif; ?>
            
            <?php if ($page < $pages): ?>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . ($page + 1)) ?>" aria-label="Halaman berikutnya">
                    <i class="ph ph-caret-right"></i>
                </a>
                <a class="gallery-pager-btn" href="<?= url('galeri?halaman=' . $pages) ?>" aria-label="Halaman terakhir">
                    <i class="ph ph-caret-double-right"></i>
                </a>
            <?php else: ?>
                <span class="gallery-pager-btn disabled"><i class="ph ph-caret-right"></i></span>
                <span class="gallery-pager-btn disabled"><i class="ph ph-caret-double-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty state -->
    <section class="gallery-empty reveal">
        <div class="gallery-empty-card glass-card">
            <div class="gallery-empty-illustration">
                <div class="empty-orb empty-orb-1"></div>
                <div class="empty-orb empty-orb-2"></div>
                <i class="ph ph-image-broken"></i>
                <span class="empty-spark"></span>
            </div>
            <h3>Belum Ada Dokumentasi</h3>
            <p>Saat ini belum ada foto kegiatan yang diunggah. Dokumentasi akan muncul setelah kegiatan pertama terlaksana.</p>
            <div class="gallery-empty-features">
                <span><i class="ph ph-check-circle"></i> Resolusi tinggi</span>
                <span><i class="ph ph-check-circle"></i> Terorganisir</span>
                <span><i class="ph ph-check-circle"></i> Mudah dicari</span>
            </div>
            <div class="gallery-empty-actions">
                <a href="<?= url('event') ?>" class="btn btn-primary">
                    <i class="ph ph-calendar-blank"></i>
                    <span class="btn-text">Lihat Jadwal Event</span>
                </a>
                <a href="<?= url('') ?>" class="btn btn-ghost">
                    <i class="ph ph-house"></i>
                    <span class="btn-text">Kembali ke Beranda</span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- ========== ENHANCED LIGHTBOX v7.0 ========== -->
<div class="gallery-lightbox" id="galleryLightbox" role="dialog" aria-label="Lightbox galeri" aria-modal="true">
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
                <button class="gallery-lightbox-btn" id="lightboxZoomIn" title="Perbesar (Ctrl +)" aria-label="Perbesar">
                    <i class="ph ph-magnifying-glass-plus"></i>
                </button>
                <button class="gallery-lightbox-btn" id="lightboxZoomOut" title="Perkecil (Ctrl -)" aria-label="Perkecil">
                    <i class="ph ph-magnifying-glass-minus"></i>
                </button>
                <button class="gallery-lightbox-btn" id="lightboxZoomReset" title="Reset zoom" aria-label="Reset zoom">
                    <i class="ph ph-arrows-out-simple"></i>
                </button>
                <div class="lightbox-divider"></div>
                <a href="#" class="gallery-lightbox-btn" id="lightboxDownload" download title="Unduh foto" aria-label="Unduh">
                    <i class="ph ph-download-simple"></i>
                </a>
                <button class="gallery-lightbox-btn" id="lightboxShare" title="Bagikan" aria-label="Bagikan">
                    <i class="ph ph-share-network"></i>
                </button>
                <a href="#" target="_blank" rel="noopener" class="gallery-lightbox-btn" id="lightboxOpenNew" title="Buka di tab baru" aria-label="Buka di tab baru">
                    <i class="ph ph-arrow-square-out"></i>
                </a>
                <div class="lightbox-divider"></div>
                <button class="gallery-lightbox-btn" id="lightboxClose" title="Tutup (Esc)" aria-label="Tutup">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
        
        <!-- Image with zoom -->
        <div class="gallery-lightbox-image" id="lightboxImageWrap">
            <button class="gallery-lightbox-nav prev" id="lightboxPrev" aria-label="Sebelumnya (←)" title="Sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <div class="gallery-lightbox-img-wrap" id="lightboxImgWrap">
                <img src="" alt="" id="lightboxImg" class="gallery-lightbox-img" draggable="false">
                <div class="gallery-lightbox-zoom-indicator" id="lightboxZoomIndicator">
                    <i class="ph ph-magnifying-glass"></i>
                    <span id="lightboxZoomLevel">100%</span>
                </div>
            </div>
            <button class="gallery-lightbox-nav next" id="lightboxNext" aria-label="Berikutnya (→)" title="Berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
        </div>
        
        <!-- Footer -->
        <div class="gallery-lightbox-footer" id="lightboxFooter">
            <div class="gallery-lightbox-meta">
                <span id="lightboxDate"><i class="ph ph-calendar"></i> <em>—</em></span>
                <span id="lightboxLocation"><i class="ph ph-map-pin"></i> <em>—</em></span>
            </div>
            <div class="gallery-lightbox-hints">
                <span><kbd>←</kbd><kbd>→</kbd> Navigasi</span>
                <span><kbd>+</kbd><kbd>-</kbd> Zoom</span>
                <span><kbd>Esc</kbd> Tutup</span>
            </div>
        </div>
    </div>
</div>

<!-- Share modal -->
<div class="lightbox-share-modal" id="shareModal">
    <div class="share-modal-card glass-card">
        <div class="share-modal-head">
            <h4><i class="ph ph-share-network"></i> Bagikan Foto</h4>
            <button class="share-modal-close" id="shareModalClose" aria-label="Tutup"><i class="ph ph-x"></i></button>
        </div>
        <div class="share-modal-body">
            <a href="#" target="_blank" rel="noopener" class="share-option share-twitter" id="shareTwitter">
                <i class="ph ph-x-logo"></i>
                <span>Twitter / X</span>
            </a>
            <a href="#" target="_blank" rel="noopener" class="share-option share-facebook" id="shareFacebook">
                <i class="ph ph-facebook-logo"></i>
                <span>Facebook</span>
            </a>
            <a href="#" target="_blank" rel="noopener" class="share-option share-whatsapp" id="shareWhatsApp">
                <i class="ph ph-whatsapp-logo"></i>
                <span>WhatsApp</span>
            </a>
            <button class="share-option share-copy" id="shareCopy">
                <i class="ph ph-link"></i>
                <span>Salin Tautan</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Hero Section ---- */
.gallery-hero {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 140px 24px 50px;
    max-width: 880px;
    margin: 0 auto;
    overflow: hidden;
}
.gallery-hero-content { position: relative; z-index: 1; }

/* Decorative orbs */
.gallery-orbs {
    position: absolute; inset: 0;
    pointer-events: none; z-index: 0;
}
.gallery-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: .25;
}
.gallery-orb.orb-1 {
    width: 440px; height: 440px;
    background: #6366f1;
    top: -100px; left: -120px;
    animation: galleryOrb 22s ease-in-out infinite alternate;
}
.gallery-orb.orb-2 {
    width: 360px; height: 360px;
    background: #22d3ee;
    bottom: -80px; right: -100px;
    animation: galleryOrb 26s ease-in-out infinite alternate-reverse;
}
.gallery-orb.orb-3 {
    width: 260px; height: 260px;
    background: #8b5cf6;
    top: 40%; left: 50%;
    transform: translate(-50%, -50%);
    animation: galleryOrb 30s ease-in-out infinite;
}
@keyframes galleryOrb {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(60px, 40px) scale(1.15); }
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
.gallery-hero-sub strong { color: var(--txt-0); }

/* ---- Gallery Stats ---- */
.gallery-stats {
    display: flex;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 40px;
    position: relative;
    z-index: 1;
}
.gallery-stat {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 24px;
    border-radius: var(--rad-lg);
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
    position: relative;
    overflow: hidden;
}
.gallery-stat::before {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: var(--stat-glow, rgba(99,102,241,.2));
    pointer-events: none;
    transition: transform .4s;
}
.gallery-stat:hover::before { transform: scale(1.4); }
.gallery-stat:hover {
    transform: translateY(-4px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 14px 34px rgba(2,6,23,.35);
}
.stat-photos   { --stat-glow: rgba(99,102,241,.25); }
.stat-years    { --stat-glow: rgba(139,92,246,.25); }
.stat-locations { --stat-glow: rgba(34,211,238,.25); }

.gallery-stat-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: grid; place-items: center;
    font-size: 20px; color: #fff;
    flex-shrink: 0;
    position: relative; z-index: 1;
    transition: transform .3s;
}
.gallery-stat:hover .gallery-stat-icon { transform: scale(1.08) rotate(-5deg); }
.stat-photos .gallery-stat-icon    { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-years .gallery-stat-icon      { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.stat-locations .gallery-stat-icon  { background: linear-gradient(135deg, #22d3ee, #06b6d4); }

.gallery-stat-info { position: relative; z-index: 1; }
.gallery-stat-info strong {
    display: block;
    font-size: 24px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
}
.gallery-stat-info span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-1);
    margin-top: 2px;
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
    gap: 16px;
    padding: 12px 16px;
    flex-wrap: wrap;
}
.gallery-filter-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    flex: 1;
    min-width: 0;
}
.filter-divider {
    width: 1px;
    height: 28px;
    background: var(--glass-brd);
    margin: 0 6px;
    align-self: center;
}
.gallery-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 14px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid transparent;
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    white-space: nowrap;
}
.gallery-filter-btn i { font-size: 14px; }
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
.gallery-filter-btn.filter-location:not(.active) {
    color: var(--txt-2);
}
.filter-count {
    min-width: 20px;
    padding: 1px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.gallery-filter-btn.active .filter-count { background: rgba(255,255,255,.25); }
.gallery-filter-btn.filter-location:not(.active) .filter-count {
    background: rgba(34,211,238,.12);
    color: #67e8f9;
}

.gallery-filter-actions {
    display: flex;
    gap: 4px;
}
.gallery-view-toggle {
    display: flex;
    gap: 2px;
    padding: 3px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    border-radius: 10px;
}
.gallery-view-btn {
    width: 34px; height: 34px;
    border-radius: 7px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid; place-items: center;
    transition: all 0.25s;
}
.gallery-view-btn i { font-size: 16px; }
.gallery-view-btn:hover { color: var(--txt-0); background: rgba(255,255,255,.05); }
.gallery-view-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 2px 8px rgba(99,102,241,.3);
}

.gallery-filter-info {
    text-align: center;
    margin-top: 16px;
    font-size: 12.5px;
    color: var(--txt-1);
    font-weight: 600;
}
.gallery-filter-info strong { color: var(--txt-0); }

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
    background: var(--glass);
    border: 1px solid var(--glass-brd);
    border-radius: 16px;
    margin-bottom: 18px;
    break-inside: avoid;
    transition: all 0.35s cubic-bezier(.22,1,.36,1);
    animation: fade-up 0.5s both;
}
.gallery-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.5);
    border-color: rgba(99,102,241,.35);
}

/* Skeleton loading */
.gallery-item-skeleton {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,.03);
    color: var(--txt-2);
    font-size: 36px;
    z-index: 0;
    overflow: hidden;
}
.skel-shimmer {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent);
    animation: shimmer 2s infinite;
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.gallery-item.loaded .gallery-item-skeleton { display: none; }

.gallery-item img {
    display: block;
    width: 100%;
    height: auto;
    transition: transform 0.6s cubic-bezier(.22,1,.36,1), opacity 0.3s;
    opacity: 0;
    position: relative;
    z-index: 1;
}
.gallery-item.loaded img { opacity: 1; }
.gallery-item:hover img { transform: scale(1.06); }

.gallery-item-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,.1) 0%, transparent 30%, transparent 50%, rgba(0,0,0,.75) 100%);
    opacity: 0;
    transition: opacity 0.35s;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 14px;
    z-index: 2;
}
.gallery-item:hover .gallery-item-overlay { opacity: 1; }

.gallery-item-actions {
    display: flex;
    gap: 6px;
}
.gallery-action-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.25);
    color: #fff;
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 16px;
    transition: all 0.25s;
    text-decoration: none;
    transform: translateY(-6px);
    opacity: 0;
}
.gallery-item:hover .gallery-action-btn {
    transform: translateY(0);
    opacity: 1;
}
.gallery-item:hover .gallery-action-btn:nth-child(2) { transition-delay: 0.05s; }
.gallery-action-btn:hover {
    background: var(--pri);
    border-color: var(--pri);
    transform: scale(1.08);
}

.gallery-item figcaption {
    position: absolute;
    left: 0; right: 0; bottom: 0;
    padding: 40px 16px 14px;
    background: linear-gradient(180deg, transparent, rgba(4,8,20,.9));
    z-index: 2;
    transform: translateY(10px);
    opacity: 0;
    transition: all 0.35s;
}
.gallery-item:hover figcaption {
    transform: translateY(0);
    opacity: 1;
}
.gallery-caption {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.gallery-caption strong {
    font-size: 13.5px;
    font-weight: 700;
    color: #fff;
}
.gallery-caption-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 11px;
    color: rgba(255,255,255,.85);
    font-weight: 600;
}
.gallery-caption-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.gallery-caption-meta i {
    font-size: 12px;
    color: var(--acc);
}

/* ---- List View (smooth transition) ---- */
.gallery-grid {
    transition: all 0.4s cubic-bezier(.22,1,.36,1);
}
.gallery-grid.list-view {
    columns: 1 !important;
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
    min-height: 140px;
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
    opacity: 1;
    transform: none;
    flex: 1;
}
.gallery-grid.list-view .gallery-caption strong {
    font-size: 16px;
    color: var(--txt-0);
}
.gallery-grid.list-view .gallery-caption-meta {
    color: var(--txt-1);
}
.gallery-grid.list-view .gallery-item-overlay {
    border-radius: 12px;
    inset: 0 0 0 0;
    right: calc(100% - 200px);
}

/* ---- Gallery Pager ---- */
.gallery-pager {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-top: 48px;
    padding: 18px 24px;
    background: var(--glass);
    backdrop-filter: blur(18px);
    border: 1px solid var(--glass-brd);
    border-radius: var(--rad-lg);
    flex-wrap: wrap;
}
.gallery-pager-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: var(--txt-1);
}
.gallery-pager-info i { color: var(--acc); font-size: 18px; }
.gallery-pager-info strong { color: var(--txt-0); font-weight: 800; }
.gallery-pager-nav {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}
.gallery-pager-btn {
    min-width: 36px; height: 36px;
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
    transform: translateY(-1px);
}
.gallery-pager-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
    font-weight: 800;
}
.gallery-pager-btn.disabled { opacity: 0.35; cursor: not-allowed; }
.gallery-pager-dots {
    color: var(--txt-2);
    padding: 0 2px;
    font-size: 11px;
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
    max-width: 520px;
    width: 100%;
}
.gallery-empty-illustration {
    position: relative;
    width: 100px; height: 100px;
    margin: 0 auto 24px;
    border-radius: 28px;
    background: linear-gradient(135deg, rgba(99,102,241,.2), rgba(34,211,238,.15));
    display: grid; place-items: center;
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
.empty-spark {
    position: absolute;
    inset: -12px;
    border-radius: 40px;
    background: conic-gradient(from 0deg, transparent, rgba(99,102,241,.4), transparent);
    filter: blur(14px);
    animation: spin 4s linear infinite;
    z-index: -1;
}
.gallery-empty-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -.4px;
}
.gallery-empty-card > p {
    color: var(--txt-1);
    font-size: 14px;
    margin-bottom: 20px;
    max-width: 40ch;
    margin-left: auto;
    margin-right: auto;
}
.gallery-empty-features {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.gallery-empty-features span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
}
.gallery-empty-features i { font-size: 14px; color: var(--ok); }
.gallery-empty-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

/* ---- Enhanced Lightbox ---- */
.gallery-lightbox {
    position: fixed; inset: 0;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}
.gallery-lightbox.show {
    display: flex;
    animation: lb-fade 0.3s ease;
}
@keyframes lb-fade { from { opacity: 0; } to { opacity: 1; } }

.gallery-lightbox-backdrop {
    position: absolute; inset: 0;
    background: rgba(4, 8, 20, 0.96);
    backdrop-filter: blur(20px);
}
.gallery-lightbox-content {
    position: relative;
    width: 100%; height: 100%;
    display: flex;
    flex-direction: column;
    padding: 20px;
    z-index: 1;
}
.gallery-lightbox-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 16px;
    gap: 16px;
    flex-wrap: wrap;
}
.gallery-lightbox-info {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
    flex: 1;
}
.gallery-lightbox-counter {
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.15);
    font-size: 12px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    color: #fff;
    white-space: nowrap;
}
.gallery-lightbox-title {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    max-width: 500px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.gallery-lightbox-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}
.lightbox-divider {
    width: 1px; height: 24px;
    background: rgba(255,255,255,.15);
    margin: 0 2px;
}
.gallery-lightbox-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    color: #fff;
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.2s;
}
.gallery-lightbox-btn:hover {
    background: rgba(255,255,255,.18);
    border-color: rgba(255,255,255,.3);
    transform: translateY(-1px);
}
.gallery-lightbox-btn:active { transform: translateY(0); }

.gallery-lightbox-image {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding: 20px 70px;
    overflow: hidden;
}
.gallery-lightbox-img-wrap {
    position: relative;
    max-width: 100%;
    max-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
    cursor: zoom-in;
    transition: transform 0.3s cubic-bezier(.22,1,.36,1);
}
.gallery-lightbox-img-wrap.is-zoomed { cursor: grab; }
.gallery-lightbox-img-wrap.is-dragging { cursor: grabbing; }
.gallery-lightbox-img {
    max-width: 100%;
    max-height: calc(100vh - 220px);
    object-fit: contain;
    border-radius: 14px;
    box-shadow: 0 40px 100px rgba(0,0,0,.6);
    animation: lightbox-zoom 0.35s cubic-bezier(.22,1,.36,1);
    transition: transform 0.3s cubic-bezier(.22,1,.36,1);
    transform-origin: center center;
    user-select: none;
    -webkit-user-drag: none;
}
@keyframes lightbox-zoom {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.gallery-lightbox-zoom-indicator {
    position: absolute;
    bottom: 16px; left: 50%;
    transform: translateX(-50%);
    padding: 6px 14px;
    border-radius: 99px;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.15);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0;
    transition: opacity .3s;
    pointer-events: none;
}
.gallery-lightbox-img-wrap.is-zoomed .gallery-lightbox-zoom-indicator { opacity: 1; }
.gallery-lightbox-zoom-indicator i { color: var(--acc); font-size: 13px; }

.gallery-lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px; height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.2);
    color: #fff;
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 22px;
    transition: all 0.25s;
    z-index: 3;
}
.gallery-lightbox-nav:hover {
    background: var(--pri);
    border-color: var(--pri);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 10px 24px rgba(99,102,241,.4);
}
.gallery-lightbox-nav.prev { left: 20px; }
.gallery-lightbox-nav.next { right: 20px; }

.gallery-lightbox-footer {
    padding: 8px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.gallery-lightbox-meta {
    display: flex;
    gap: 18px;
    font-size: 13px;
    color: var(--txt-1);
    flex-wrap: wrap;
}
.gallery-lightbox-meta span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.gallery-lightbox-meta i { color: var(--acc); font-size: 15px; }
.gallery-lightbox-meta em { font-style: normal; color: var(--txt-2); }

.gallery-lightbox-hints {
    display: flex;
    gap: 14px;
    font-size: 11px;
    color: var(--txt-2);
    font-weight: 600;
}
.gallery-lightbox-hints span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.gallery-lightbox-hints kbd {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 1px 0 rgba(0,0,0,.25);
}

/* ---- Share Modal ---- */
.lightbox-share-modal {
    position: fixed; inset: 0;
    z-index: 1100;
    background: rgba(4,8,20,.7);
    backdrop-filter: blur(8px);
    display: none;
    place-items: center;
    padding: 20px;
}
.lightbox-share-modal.show {
    display: grid;
    animation: lb-fade .25s ease;
}
.share-modal-card {
    max-width: 420px;
    width: 100%;
    padding: 24px;
    border-radius: 20px;
    animation: zoom-in .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes zoom-in {
    from { transform: scale(.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.share-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--glass-brd);
}
.share-modal-head h4 {
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.share-modal-head i { color: var(--acc); }
.share-modal-close {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--glass-brd);
    color: var(--txt-1);
    cursor: pointer;
    display: grid; place-items: center;
    transition: all .2s;
}
.share-modal-close:hover {
    background: rgba(239,68,68,.15);
    border-color: var(--danger);
    color: var(--danger);
}
.share-modal-body {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.share-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-0);
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: all .25s;
}
.share-option:hover {
    transform: translateY(-2px);
    border-color: var(--glass-brd-2);
    box-shadow: 0 8px 20px rgba(2,6,23,.4);
}
.share-option i { font-size: 20px; }
.share-twitter:hover   { background: rgba(29,161,242,.12); border-color: rgba(29,161,242,.4); }
.share-twitter i       { color: #1da1f2; }
.share-facebook:hover  { background: rgba(24,119,242,.12); border-color: rgba(24,119,242,.4); }
.share-facebook i      { color: #1877f2; }
.share-whatsapp:hover  { background: rgba(37,211,102,.12); border-color: rgba(37,211,102,.4); }
.share-whatsapp i      { color: #25d366; }
.share-copy:hover      { background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.4); }
.share-copy i          { color: var(--acc); }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .gallery-lightbox-hints { display: none; }
    .lightbox-divider { display: none; }
}
@media (max-width: 768px) {
    .gallery-hero { padding-top: 120px; }
    .gallery-stats { gap: 10px; }
    .gallery-stat { padding: 12px 18px; gap: 10px; }
    .gallery-stat-icon { width: 38px; height: 38px; font-size: 17px; }
    .gallery-stat-info strong { font-size: 20px; }
    .gallery-filter-bar { flex-direction: column; align-items: stretch; }
    .gallery-filter-group { justify-content: flex-start; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 4px; }
    .gallery-view-toggle { align-self: stretch; justify-content: center; }
    .gallery-view-btn { flex: 1; }
    .gallery-pager { flex-direction: column; gap: 16px; }
    .gallery-pager-nav { justify-content: center; }
    .gallery-orb { filter: blur(80px); opacity: .18; }
    .gallery-lightbox-header { padding: 8px 10px; }
    .gallery-lightbox-title { max-width: 150px; font-size: 13px; }
    .gallery-lightbox-image { padding: 10px 50px; }
    .gallery-lightbox-nav { width: 42px; height: 42px; font-size: 18px; }
    .gallery-lightbox-nav.prev { left: 6px; }
    .gallery-lightbox-nav.next { right: 6px; }
    .gallery-lightbox-meta { justify-content: center; width: 100%; }
    .gallery-grid.list-view .gallery-item { flex-direction: column; }
    .gallery-grid.list-view .gallery-item img { width: 100%; height: auto; }
    .gallery-grid.list-view .gallery-item-overlay { right: 0; }
    .share-modal-body { grid-template-columns: 1fr; }
}
@media (max-width: 520px) {
    .gallery-lightbox-actions { gap: 4px; }
    .gallery-lightbox-btn { width: 34px; height: 34px; font-size: 14px; }
    .gallery-lightbox-footer { flex-direction: column; gap: 8px; padding: 8px 10px; }
    .gallery-lightbox-meta { font-size: 12px; }
    .gallery-empty-card { padding: 40px 24px; }
    .gallery-empty-actions { flex-direction: column; width: 100%; }
    .gallery-empty-actions .btn { width: 100%; justify-content: center; }
    .gallery-empty-features { flex-direction: column; gap: 8px; align-items: center; }
}
</style>

<!-- ========== SCRIPT v7.0 ========== -->
<script>
(function(){
    'use strict';

    /* ---- Counter animation untuk stats hero ---- */
    const counters = document.querySelectorAll('.gallery-stat-info strong[data-count]');
    if (counters.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(en => {
                if (!en.isIntersecting) return;
                const el = en.target;
                const target = parseInt(el.dataset.count, 10) || 0;
                const t0 = performance.now(), dur = 1200;
                const tick = (t) => {
                    const p = Math.min(1, (t - t0) / dur);
                    const v = Math.floor(target * (1 - Math.pow(1 - p, 3)));
                    el.textContent = v.toLocaleString('id-ID');
                    if (p < 1) requestAnimationFrame(tick);
                    else el.textContent = target.toLocaleString('id-ID');
                };
                requestAnimationFrame(tick);
                obs.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(c => obs.observe(c));
    }

    /* ---- Filter functionality dengan counter update ---- */
    const items = document.querySelectorAll('.gallery-item');
    const filterBtns = document.querySelectorAll('.gallery-filter-btn');
    const viewBtns = document.querySelectorAll('.gallery-view-btn');
    const grid = document.getElementById('galleryGrid');
    const visibleCount = document.getElementById('galleryVisibleCount');
    
    function applyFilter() {
        const active = document.querySelector('.gallery-filter-btn.active');
        if (!active) return;
        const filter = active.dataset.filter;
        const value = active.dataset.value;
        
        let visible = 0;
        items.forEach(item => {
            let show = false;
            if (filter === 'all') show = true;
            else if (filter === 'year') show = item.dataset.year === value;
            else if (filter === 'location') show = item.dataset.location === value;
            
            if (show) {
                item.style.display = '';
                visible++;
            } else {
                item.style.display = 'none';
            }
        });
        
        if (visibleCount) visibleCount.textContent = visible.toLocaleString('id-ID');
    }
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => {
                const on = (b === btn);
                b.classList.toggle('active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            applyFilter();
        });
    });
    
    /* ---- View toggle dengan smooth transition ---- */
    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            viewBtns.forEach(b => {
                const on = (b === btn);
                b.classList.toggle('active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            if (grid) {
                grid.classList.toggle('list-view', btn.dataset.view === 'list');
                // Re-trigger animation
                items.forEach((item, i) => {
                    item.style.animation = 'none';
                    item.offsetHeight;
                    item.style.animation = `fade-up 0.4s ${i * 0.03}s both`;
                });
            }
        });
    });
    
    /* ---- Enhanced Lightbox dengan Zoom ---- */
    const lightbox = document.getElementById('galleryLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const lightboxImgWrap = document.getElementById('lightboxImgWrap');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxCurrent = document.getElementById('lightboxCurrent');
    const lightboxTotal = document.getElementById('lightboxTotal');
    const lightboxDate = document.getElementById('lightboxDate');
    const lightboxLocation = document.getElementById('lightboxLocation');
    const lightboxDownload = document.getElementById('lightboxDownload');
    const lightboxOpenNew = document.getElementById('lightboxOpenNew');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxBackdrop = document.getElementById('lightboxBackdrop');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');
    const lightboxZoomIn = document.getElementById('lightboxZoomIn');
    const lightboxZoomOut = document.getElementById('lightboxZoomOut');
    const lightboxZoomReset = document.getElementById('lightboxZoomReset');
    const lightboxZoomLevel = document.getElementById('lightboxZoomLevel');
    const lightboxShare = document.getElementById('lightboxShare');
    
    // Share modal
    const shareModal = document.getElementById('shareModal');
    const shareModalClose = document.getElementById('shareModalClose');
    const shareTwitter = document.getElementById('shareTwitter');
    const shareFacebook = document.getElementById('shareFacebook');
    const shareWhatsApp = document.getElementById('shareWhatsApp');
    const shareCopy = document.getElementById('shareCopy');
    
    let currentIndex = 0;
    let visibleItems = [];
    let zoomScale = 1;
    let posX = 0, posY = 0;
    let isDragging = false;
    let dragStart = { x: 0, y: 0 };
    
    function getVisibleItems() {
        return Array.from(items).filter(item => item.style.display !== 'none');
    }
    
    function resetZoom() {
        zoomScale = 1; posX = 0; posY = 0;
        applyTransform();
        lightboxImgWrap.classList.remove('is-zoomed', 'is-dragging');
    }
    
    function applyTransform() {
        lightboxImg.style.transform = `scale(${zoomScale}) translate(${posX}px, ${posY}px)`;
        if (lightboxZoomLevel) lightboxZoomLevel.textContent = Math.round(zoomScale * 100) + '%';
    }
    
    function showImage(index) {
        visibleItems = getVisibleItems();
        if (index < 0 || index >= visibleItems.length) return;
        
        resetZoom();
        currentIndex = index;
        const item = visibleItems[currentIndex];
        
        lightboxImg.src = item.dataset.full;
        lightboxImg.alt = item.dataset.title;
        lightboxTitle.textContent = item.dataset.title || '—';
        lightboxCurrent.textContent = currentIndex + 1;
        lightboxTotal.textContent = visibleItems.length;
        
        const date = item.dataset.date;
        const loc = item.dataset.locationText;
        lightboxDate.innerHTML = `<i class="ph ph-calendar"></i> ${date ? '<span>' + date + '</span>' : '<em>Tanggal tidak tercatat</em>'}`;
        lightboxLocation.innerHTML = `<i class="ph ph-map-pin"></i> ${loc ? '<span>' + loc + '</span>' : '<em>Lokasi tidak tercatat</em>'}`;
        
        lightboxDownload.href = item.dataset.full;
        lightboxDownload.download = (item.dataset.title || 'foto') + '.jpg';
        lightboxOpenNew.href = item.dataset.full;
        
        // Nav buttons
        lightboxPrev.style.visibility = currentIndex > 0 ? 'visible' : 'hidden';
        lightboxNext.style.visibility = currentIndex < visibleItems.length - 1 ? 'visible' : 'hidden';
        
        // Re-trigger animation
        lightboxImg.style.animation = 'none';
        lightboxImg.offsetHeight;
        lightboxImg.style.animation = 'lightbox-zoom 0.35s cubic-bezier(.22,1,.36,1)';
    }
    
    function openLightbox(index) {
        visibleItems = getVisibleItems();
        showImage(index);
        lightbox.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeLightbox() {
        lightbox.classList.remove('show');
        document.body.style.overflow = '';
        resetZoom();
    }
    
    /* ---- Bind clicks ---- */
    items.forEach(item => {
        item.addEventListener('click', (e) => {
            if (e.target.closest('.gallery-action-btn[data-action="open"]')) return;
            visibleItems = getVisibleItems();
            const visibleIndex = visibleItems.indexOf(item);
            openLightbox(visibleIndex);
        });
    });
    
    lightboxClose?.addEventListener('click', closeLightbox);
    lightboxBackdrop?.addEventListener('click', closeLightbox);
    lightboxPrev?.addEventListener('click', () => showImage(currentIndex - 1));
    lightboxNext?.addEventListener('click', () => showImage(currentIndex + 1));
    
    /* ---- Zoom controls ---- */
    function zoomIn() {
        if (zoomScale < 4) {
            zoomScale = Math.min(4, zoomScale + 0.5);
            lightboxImgWrap.classList.toggle('is-zoomed', zoomScale > 1);
            applyTransform();
        }
    }
    function zoomOut() {
        if (zoomScale > 1) {
            zoomScale = Math.max(1, zoomScale - 0.5);
            if (zoomScale === 1) { posX = 0; posY = 0; }
            lightboxImgWrap.classList.toggle('is-zoomed', zoomScale > 1);
            applyTransform();
        }
    }
    
    lightboxZoomIn?.addEventListener('click', zoomIn);
    lightboxZoomOut?.addEventListener('click', zoomOut);
    lightboxZoomReset?.addEventListener('click', resetZoom);
    
    // Click image to zoom toggle
    lightboxImg?.addEventListener('click', (e) => {
        if (isDragging) return;
        if (zoomScale === 1) {
            zoomScale = 2;
            lightboxImgWrap.classList.add('is-zoomed');
            applyTransform();
        } else {
            resetZoom();
        }
    });
    
    // Drag when zoomed
    lightboxImgWrap?.addEventListener('mousedown', (e) => {
        if (zoomScale > 1) {
            isDragging = true;
            lightboxImgWrap.classList.add('is-dragging');
            dragStart = { x: e.clientX - posX, y: e.clientY - posY };
            e.preventDefault();
        }
    });
    document.addEventListener('mousemove', (e) => {
        if (isDragging) {
            posX = e.clientX - dragStart.x;
            posY = e.clientY - dragStart.y;
            applyTransform();
        }
    });
    document.addEventListener('mouseup', () => {
        isDragging = false;
        lightboxImgWrap?.classList.remove('is-dragging');
    });
    
    // Mouse wheel zoom
    lightboxImgWrap?.addEventListener('wheel', (e) => {
        if (!lightbox.classList.contains('show')) return;
        e.preventDefault();
        if (e.deltaY < 0) zoomIn();
        else zoomOut();
    }, { passive: false });
    
    // Double-click to zoom
    lightboxImg?.addEventListener('dblclick', (e) => {
        e.preventDefault();
        if (zoomScale === 1) { zoomScale = 2.5; lightboxImgWrap.classList.add('is-zoomed'); }
        else resetZoom();
        applyTransform();
    });
    
    /* ---- Touch support ---- */
    let touchStartX = 0, touchStartY = 0;
    let lastTouchDist = 0;
    
    lightboxImgWrap?.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
            if (zoomScale > 1) {
                dragStart = { x: touchStartX - posX, y: touchStartY - posY };
                isDragging = true;
            }
        } else if (e.touches.length === 2) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            lastTouchDist = Math.hypot(dx, dy);
        }
    });
    
    lightboxImgWrap?.addEventListener('touchmove', (e) => {
        if (e.touches.length === 1 && isDragging && zoomScale > 1) {
            posX = e.touches[0].clientX - dragStart.x;
            posY = e.touches[0].clientY - dragStart.y;
            applyTransform();
            e.preventDefault();
        } else if (e.touches.length === 2) {
            const dx = e.touches[0].clientX - e.touches[1].clientX;
            const dy = e.touches[0].clientY - e.touches[1].clientY;
            const dist = Math.hypot(dx, dy);
            if (lastTouchDist > 0) {
                const diff = dist - lastTouchDist;
                if (diff > 3) zoomIn();
                else if (diff < -3) zoomOut();
            }
            lastTouchDist = dist;
            e.preventDefault();
        }
    }, { passive: false });
    
    lightboxImgWrap?.addEventListener('touchend', (e) => {
        if (e.touches.length === 0 && zoomScale === 1) {
            const diff = touchStartX - (e.changedTouches[0]?.clientX || touchStartX);
            if (Math.abs(diff) > 50 && Math.abs(touchStartY - (e.changedTouches[0]?.clientY || touchStartY)) < 80) {
                if (diff > 0) showImage(currentIndex + 1);
                else showImage(currentIndex - 1);
            }
        }
        isDragging = false;
        lastTouchDist = 0;
    });
    
    /* ---- Share modal ---- */
    function openShare() {
        const item = visibleItems[currentIndex];
        if (!item) return;
        const url = item.dataset.full;
        const title = item.dataset.title || 'Foto dari galeri';
        const text = encodeURIComponent(title + ' — ' + window.location.href);
        const encUrl = encodeURIComponent(url);
        
        if (shareTwitter)   shareTwitter.href = `https://twitter.com/intent/tweet?url=${encUrl}&text=${text}`;
        if (shareFacebook)  shareFacebook.href = `https://www.facebook.com/sharer/sharer.php?u=${encUrl}`;
        if (shareWhatsApp)  shareWhatsApp.href = `https://wa.me/?text=${text}%20${encUrl}`;
        
        shareModal.classList.add('show');
    }
    
    lightboxShare?.addEventListener('click', openShare);
    shareModalClose?.addEventListener('click', () => shareModal.classList.remove('show'));
    shareModal?.addEventListener('click', (e) => {
        if (e.target === shareModal) shareModal.classList.remove('show');
    });
    shareCopy?.addEventListener('click', (e) => {
        e.preventDefault();
        const item = visibleItems[currentIndex];
        if (!item) return;
        navigator.clipboard.writeText(item.dataset.full).then(() => {
            if (window.toast) window.toast('Tautan foto berhasil disalin!', 'success');
            const orig = shareCopy.innerHTML;
            shareCopy.innerHTML = '<i class="ph ph-check-circle"></i><span>Tersalin!</span>';
            setTimeout(() => { shareCopy.innerHTML = orig; }, 2000);
        });
    });
    
    /* ---- Keyboard navigation ---- */
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('show')) return;
        if (shareModal?.classList.contains('show')) {
            if (e.key === 'Escape') shareModal.classList.remove('show');
            return;
        }
        switch(e.key) {
            case 'Escape': closeLightbox(); break;
            case 'ArrowLeft': showImage(currentIndex - 1); break;
            case 'ArrowRight': showImage(currentIndex + 1); break;
            case '+': case '=': if (e.ctrlKey || e.metaKey) { e.preventDefault(); zoomIn(); } break;
            case '-': if (e.ctrlKey || e.metaKey) { e.preventDefault(); zoomOut(); } break;
            case '0': if (e.ctrlKey || e.metaKey) { e.preventDefault(); resetZoom(); } break;
        }
    });
    
    // Prevent image drag
    lightboxImg?.addEventListener('dragstart', (e) => e.preventDefault());
})();
</script>