<?php
/**
 * ============================================================
 * MANAJEMEN ARTIKEL (ADMIN) — ULTIMATE EDITION v5.9
 * Panel admin untuk menulis, menyunting, dan mengelola artikel
 * dengan stat cards, filter, rich editor, dan preview.
 * ============================================================
 */

$categories = $categories ?? ['Artikel', 'Berita', 'Edukasi', 'Podcast', 'Hari Besar'];
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Publikasi</div>
        <h2 class="page-title">
            <i class="ph ph-newspaper" style="color:var(--acc);margin-right:8px"></i>
            Manajemen Artikel
        </h2>
        <p class="page-sub">Tulis, sunting, dan terbitkan artikel yang dapat dibaca publik.</p>
    </div>
    <div class="page-head-actions">
        <a href="<?= url('artikel') ?>" target="_blank" class="btn btn-ghost">
            <i class="ph ph-eye"></i>
            <span class="btn-text">Lihat Halaman Publik</span>
            <i class="ph ph-arrow-square-out" style="font-size:13px"></i>
        </a>
        <button class="btn btn-primary" id="btnAddArticle">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tulis Artikel</span>
        </button>
    </div>
</section>

<!-- ========== STAT CARDS ========== -->
<section class="articles-stats">
    <article class="article-stat-card glass-card stat-total">
        <div class="article-stat-icon">
            <i class="ph ph-newspaper"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statTotal">—</strong>
            <span>Total Artikel</span>
        </div>
    </article>
    
    <article class="article-stat-card glass-card stat-published">
        <div class="article-stat-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statPublished">—</strong>
            <span>Diterbitkan</span>
        </div>
    </article>
    
    <article class="article-stat-card glass-card stat-categories">
        <div class="article-stat-icon">
            <i class="ph ph-tag"></i>
        </div>
        <div class="article-stat-info">
            <strong><?= count($categories) ?></strong>
            <span>Kategori</span>
        </div>
    </article>
    
    <article class="article-stat-card glass-card stat-views">
        <div class="article-stat-icon">
            <i class="ph ph-eye"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statViews">—</strong>
            <span>Total Pembaca</span>
        </div>
    </article>
</section>

<!-- ========== TABLE CARD ========== -->
<section class="glass-card table-card articles-table-wrap">
    
    <!-- Toolbar -->
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="artSearch" placeholder="Cari judul atau kategori...">
            <button class="search-clear" id="artSearchClear" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="artInfo">Memuat...</span>
            <button class="icon-btn" id="btnArtRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="articles-filter-pills">
        <button class="article-pill-btn active" data-filter="all">
            <i class="ph ph-list"></i> Semua
        </button>
        <?php foreach ($categories as $c): ?>
        <button class="article-pill-btn" data-filter="<?= e(strtolower($c)) ?>">
            <i class="ph ph-tag"></i> <?= e($c) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="table-scroll">
        <table class="data-table articles-table">
            <thead>
                <tr>
                    <th style="width:40%">Judul</th>
                    <th style="width:15%">Kategori</th>
                    <th style="width:15%">Penulis</th>
                    <th style="width:15%">Tanggal</th>
                    <th style="width:15%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="articleRows">
                <!-- Skeleton -->
                <tr class="skeleton-row"><td colspan="5"><div class="skel" style="height:40px"></div></td></tr>
                <tr class="skeleton-row"><td colspan="5"><div class="skel" style="height:40px"></div></td></tr>
                <tr class="skeleton-row"><td colspan="5"><div class="skel" style="height:40px"></div></td></tr>
            </tbody>
        </table>
    </div>

    <!-- Empty state -->
    <div class="empty-rich" id="emptyArticles" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-newspaper"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Belum Ada Artikel</h3>
        <p>Mulai publikasikan cerita dan informasi organisasi Anda.</p>
        <button class="btn btn-primary" id="emptyAddArticle">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tulis Artikel Pertama</span>
        </button>
    </div>

    <!-- Pagination -->
    <div class="table-foot">
        <span id="artPageInfo">Memuat...</span>
        <div class="pager">
            <button class="pager-btn icon-btn" id="artPrev" disabled aria-label="Sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <span class="pager-current" id="artPagerCur">1 / 1</span>
            <button class="pager-btn icon-btn" id="artNext" disabled aria-label="Berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== MODAL FORM ARTIKEL ========== -->
<div class="modal-backdrop" id="articleModal" role="dialog" aria-labelledby="articleModalTitle">
    <div class="modal glass-card modal-xl">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-newspaper"></i></span>
                <div>
                    <h3 id="articleModalTitle">Tulis Artikel Baru</h3>
                    <p class="modal-sub">Artikel yang diterbitkan langsung terlihat oleh publik.</p>
                </div>
            </div>
            <div class="modal-head-actions">
                <button type="button" class="btn btn-ghost btn-xs" id="btnPreviewArticle">
                    <i class="ph ph-eye"></i>
                    <span class="btn-text">Pratinjau</span>
                </button>
                <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
        <form id="articleForm">
            <?= csrf_field() ?>
            <input type="hidden" id="aId" value="">
            
            <div class="modal-body article-form-body">
                <!-- Left Column: Main Content -->
                <div class="article-form-main">
                    <!-- Title -->
                    <div class="form-section">
                        <label class="field field-enhanced">
                            <span class="field-label">
                                <i class="ph ph-text-aa"></i> Judul Artikel <em>*</em>
                            </span>
                            <div class="field-input-wrap">
                                <input type="text" name="title" id="aTitle" 
                                       placeholder="Contoh: Perjalanan Bakti Sosial 2026" 
                                       required maxlength="200"
                                       class="article-title-input">
                                <span class="field-focus-ring"></span>
                            </div>
                            <div class="field-footer">
                                <span class="field-error" data-error="title"></span>
                                <span class="char-counter" id="titleCounter">0 / 200</span>
                            </div>
                        </label>
                    </div>

                    <!-- Excerpt -->
                    <div class="form-section">
                        <label class="field">
                            <span class="field-label">
                                <i class="ph ph-text-align-left" style="color:var(--acc)"></i>
                                Ringkasan
                            </span>
                            <textarea name="excerpt" id="aExcerpt" rows="2" 
                                      placeholder="Satu hingga dua kalimat penarik minat baca..." 
                                      maxlength="300"></textarea>
                            <div class="field-footer">
                                <small class="asset-hint">Muncul di kartu artikel di halaman publik.</small>
                                <span class="char-counter" id="excerptCounter">0 / 300</span>
                            </div>
                        </label>
                    </div>

                    <!-- Content Editor -->
                    <div class="form-section">
                        <div class="article-editor-wrap">
                            <div class="article-editor-toolbar">
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2">
                                        <i class="ph ph-text-h-two"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3">
                                        <i class="ph ph-text-h-three"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                                        <i class="ph ph-text-b"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                                        <i class="ph ph-text-italic"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="ul" title="Bullet List">
                                        <i class="ph ph-list-bullets"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="ol" title="Numbered List">
                                        <i class="ph ph-list-numbers"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="quote" title="Quote">
                                        <i class="ph ph-quotes"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="link" title="Tautan">
                                        <i class="ph ph-link"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="youtube" title="Embed YouTube">
                                        <i class="ph ph-youtube-logo"></i>
                                    </button>
                                </div>
                                <div class="toolbar-help">
                                    <span class="toolbar-hint">
                                        <i class="ph ph-info"></i>
                                        Tip: Tempel URL YouTube langsung di konten untuk embed otomatis.
                                    </span>
                                </div>
                            </div>
                            <label class="field">
                                <span class="field-label visually-hidden">Isi Artikel</span>
                                <textarea name="content" id="aContent" rows="14" 
                                          placeholder="Tulis isi artikel di sini...&#10;&#10;Tip: Gunakan heading (##) untuk membuat daftar isi otomatis.&#10;Tempel URL YouTube untuk embed video." 
                                          required 
                                          class="article-content-textarea"></textarea>
                                <span class="field-error" data-error="content"></span>
                            </label>
                        </div>
                        <div class="field-footer">
                            <div class="editor-stats">
                                <span id="wordCount">0 kata</span>
                                <span class="editor-stats-sep">•</span>
                                <span id="readTime">0 menit baca</span>
                            </div>
                            <span class="char-counter" id="contentCounter">0 / 50000</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings -->
                <div class="article-form-side">
                    <!-- Category -->
                    <div class="form-section side-section">
                        <div class="side-section-title">
                            <i class="ph ph-tag"></i>
                            <span>Kategori</span>
                        </div>
                        <select name="category" id="aCategory" class="field-select">
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="field-error" data-error="category"></span>
                    </div>

                    <!-- Status -->
                    <div class="form-section side-section">
                        <div class="side-section-title">
                            <i class="ph ph-broadcast"></i>
                            <span>Status</span>
                        </div>
                        <div class="status-selector compact">
                            <label class="status-option-compact active" data-status="published">
                                <input type="radio" name="status" value="published" checked>
                                <span class="status-option-icon">
                                    <i class="ph ph-check-circle"></i>
                                </span>
                                <span>Terbitkan</span>
                            </label>
                            <label class="status-option-compact" data-status="draft">
                                <input type="radio" name="status" value="draft">
                                <span class="status-option-icon">
                                    <i class="ph ph-file"></i>
                                </span>
                                <span>Draft</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="form-section side-section tips-section">
                        <div class="side-section-title">
                            <i class="ph ph-lightbulb"></i>
                            <span>Tips Menulis</span>
                        </div>
                        <ul class="tips-list">
                            <li><i class="ph ph-check"></i> Gunakan heading untuk struktur</li>
                            <li><i class="ph ph-check"></i> Tulis ringkasan yang menarik</li>
                            <li><i class="ph ph-check"></i> 300-800 kata ideal untuk artikel</li>
                            <li><i class="ph ph-check"></i> Tambahkan video YouTube jika relevan</li>
                        </ul>
                    </div>

                    <!-- Preview snippet -->
                    <div class="form-section side-section preview-section">
                        <div class="side-section-title">
                            <i class="ph ph-eye"></i>
                            <span>Pratinjau Kartu</span>
                        </div>
                        <div class="article-preview-card">
                            <div class="preview-cover" id="previewCover">
                                <i class="ph ph-newspaper"></i>
                            </div>
                            <div class="preview-body">
                                <span class="preview-category" id="previewCategory">Kategori</span>
                                <h4 class="preview-title" id="previewTitle">Judul artikel Anda...</h4>
                                <p class="preview-excerpt" id="previewExcerpt">Ringkasan akan muncul di sini...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <div class="modal-foot-actions">
                    <button type="button" class="btn btn-ghost" id="btnSaveDraft">
                        <i class="ph ph-file"></i>
                        <span class="btn-text">Simpan Draft</span>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-paper-plane-tilt"></i>
                        <span class="btn-text">Terbitkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL PREVIEW ========== -->
<div class="modal-backdrop" id="articlePreviewModal">
    <div class="modal glass-card modal-xl modal-fullheight">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-eye"></i></span>
                <div>
                    <h3>Pratinjau Artikel</h3>
                    <p class="modal-sub">Begini tampilan artikel di halaman publik.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal>
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="modal-body article-preview-body" id="articlePreviewContent">
            <!-- Filled by JS -->
        </div>
    </div>
</div>

<!-- ========== MODAL KONFIRMASI HAPUS ========== -->
<div class="modal-backdrop" id="articleDeleteModal" role="dialog">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3>Hapus Artikel?</h3>
                <p class="modal-sub">Artikel akan hilang dari halaman publik.</p>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text" id="articleDeleteText"></p>
            <div class="danger-note">
                <i class="ph ph-warning"></i>
                <span>Tindakan ini tidak dapat dibatalkan.</span>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmArticleDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/articles.js') ?>"></script>

<style>
/* ---- Articles Stats ---- */
.articles-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.article-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    transition: all 0.3s;
}
.article-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(2,6,23,.4);
}
.article-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    flex-shrink: 0;
}
.stat-total .article-stat-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-published .article-stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-categories .article-stat-icon { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-views .article-stat-icon { background: linear-gradient(135deg, #0ea5e9, #22d3ee); }

.article-stat-info strong {
    display: block;
    font-size: 24px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.article-stat-info span {
    font-size: 12px;
    color: var(--txt-1);
    font-weight: 600;
}

/* ---- Filter Pills ---- */
.articles-filter-pills {
    display: flex;
    gap: 6px;
    padding: 4px;
    margin: 16px 22px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    border-radius: 12px;
    overflow-x: auto;
}
.article-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.article-pill-btn i { font-size: 14px; }
.article-pill-btn:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.article-pill-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}

/* ---- XL Modal ---- */
.modal-xl {
    width: min(1100px, 95vw);
    max-height: 92vh;
}
.modal-fullheight {
    max-height: 95vh;
}

.modal-head-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

/* ---- Article Form Body (2 column) ---- */
.article-form-body {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    padding: 24px;
    align-items: start;
}
.article-form-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Form sections */
.form-section {
    margin-bottom: 0;
}

/* Title input large */
.article-title-input {
    font-size: 20px !important;
    font-weight: 700;
    padding: 16px 18px !important;
}

/* Editor toolbar */
.article-editor-wrap {
    border-radius: 14px;
    border: 1.5px solid var(--glass-brd);
    overflow: hidden;
    background: rgba(255,255,255,.02);
    transition: border-color 0.25s;
}
.article-editor-wrap:focus-within {
    border-color: var(--pri);
}
.article-editor-toolbar {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 12px;
    background: rgba(255,255,255,.03);
    border-bottom: 1px solid var(--glass-brd);
    flex-wrap: wrap;
}
.toolbar-group {
    display: flex;
    gap: 2px;
}
.toolbar-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 15px;
    transition: all 0.15s;
}
.toolbar-btn:hover {
    background: rgba(255,255,255,.08);
    color: var(--txt-0);
}
.toolbar-sep {
    width: 1px;
    height: 20px;
    background: var(--glass-brd);
    margin: 0 4px;
}
.toolbar-help {
    margin-left: auto;
}
.toolbar-hint {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--txt-2);
}
.toolbar-hint i {
    color: var(--acc);
}
.article-content-textarea {
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    min-height: 300px;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 13.5px;
    line-height: 1.7;
}
.article-content-textarea:focus {
    box-shadow: none !important;
}

/* Editor stats */
.editor-stats {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    color: var(--txt-2);
}
.editor-stats-sep { opacity: 0.4; }

/* Side sections */
.article-form-side {
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: sticky;
    top: 24px;
}
.side-section {
    padding: 18px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid var(--glass-brd);
}
.side-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
    margin-bottom: 12px;
}
.side-section-title i {
    font-size: 14px;
    color: var(--acc);
}

/* Compact status selector */
.status-selector.compact {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.status-option-compact {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(255,255,255,.03);
    border: 2px solid var(--glass-brd);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12.5px;
    font-weight: 600;
}
.status-option-compact input { display: none; }
.status-option-compact:hover {
    background: rgba(255,255,255,.05);
}
.status-option-compact.active {
    border-color: var(--pri);
    background: rgba(99,102,241,.1);
    color: var(--txt-0);
}
.status-option-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: rgba(255,255,255,.06);
    display: grid;
    place-items: center;
    font-size: 13px;
    color: var(--txt-1);
}
.status-option-compact.active .status-option-icon {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}

/* Tips list */
.tips-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 0;
}
.tips-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--txt-1);
}
.tips-list i {
    font-size: 14px;
    color: #10b981;
}

/* Preview card */
.article-preview-card {
    border-radius: 12px;
    overflow: hidden;
    background: rgba(0,0,0,.2);
    border: 1px solid var(--glass-brd);
}
.preview-cover {
    height: 80px;
    display: grid;
    place-items: center;
    font-size: 28px;
    color: rgba(255,255,255,.4);
    background: linear-gradient(135deg, var(--pri), var(--acc));
}
.preview-body {
    padding: 14px;
}
.preview-category {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.preview-title {
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.preview-excerpt {
    font-size: 11px;
    color: var(--txt-2);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

/* Preview body modal */
.article-preview-body {
    padding: 32px;
    max-height: 70vh;
    overflow-y: auto;
}

/* Field enhanced */
.field-enhanced .field-label {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--txt-0);
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 12.5px;
}
.field-enhanced .field-label i {
    font-size: 15px;
    color: var(--acc);
}
.field-input-wrap {
    position: relative;
}
.field-enhanced input {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.03);
    border: 1.5px solid rgba(255,255,255,0.08);
    transition: border-color 0.25s, background 0.25s;
}
.field-enhanced input:focus {
    background: rgba(255,255,255,0.06);
    border-color: transparent;
    box-shadow: none;
}
.field-focus-ring {
    position: absolute;
    inset: -2px;
    border-radius: 12px;
    padding: 2px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    opacity: 0;
    transition: opacity 0.25s;
    pointer-events: none;
    z-index: 0;
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}
.field-enhanced input:focus ~ .field-focus-ring {
    opacity: 1;
}

.field-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
}
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
}
.char-counter.warn { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
}

.modal-foot-actions {
    display: flex;
    gap: 10px;
}

/* Search clear */
.search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: 7px;
    background: rgba(255,255,255,.06);
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s;
}
.search-clear:hover {
    background: rgba(239,68,68,.15);
    color: var(--danger);
}

/* Responsive */
@media (max-width: 980px) {
    .article-form-body {
        grid-template-columns: 1fr;
    }
    .article-form-side {
        position: static;
    }
    .articles-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .articles-stats {
        grid-template-columns: 1fr;
    }
    .toolbar-help { display: none; }
}
</style>

<script>
(() => {
    'use strict';
    
    // ---- 1. Character counters ----
    const counters = [
        { input: 'aTitle', counter: 'titleCounter', max: 200 },
        { input: 'aExcerpt', counter: 'excerptCounter', max: 300 },
        { input: 'aContent', counter: 'contentCounter', max: 50000 }
    ];
    
    counters.forEach(({ input, counter, max }) => {
        const inputEl = document.getElementById(input);
        const counterEl = document.getElementById(counter);
        if (!inputEl || !counterEl) return;
        
        inputEl.addEventListener('input', () => {
            const len = inputEl.value.length;
            counterEl.textContent = `${len} / ${max}`;
            counterEl.className = 'char-counter';
            if (len > max * 0.9) counterEl.classList.add('warn');
            if (len > max * 0.95) counterEl.classList.add('danger');
        });
    });
    
    // ---- 2. Word count & read time ----
    const contentEl = document.getElementById('aContent');
    const wordCountEl = document.getElementById('wordCount');
    const readTimeEl = document.getElementById('readTime');
    
    if (contentEl && wordCountEl && readTimeEl) {
        contentEl.addEventListener('input', () => {
            const words = contentEl.value.trim().split(/\s+/).filter(w => w.length > 0).length;
            wordCountEl.textContent = `${words} kata`;
            readTimeEl.textContent = `${Math.max(1, Math.ceil(words / 200))} menit baca`;
        });
    }
    
    // ---- 3. Live preview (side card) ----
    const titleEl = document.getElementById('aTitle');
    const excerptEl = document.getElementById('aExcerpt');
    const categoryEl = document.getElementById('aCategory');
    const previewTitle = document.getElementById('previewTitle');
    const previewExcerpt = document.getElementById('previewExcerpt');
    const previewCategory = document.getElementById('previewCategory');
    
    function updatePreview() {
        if (previewTitle) previewTitle.textContent = titleEl.value || 'Judul artikel Anda...';
        if (previewExcerpt) previewExcerpt.textContent = excerptEl.value || 'Ringkasan akan muncul di sini...';
        if (previewCategory) previewCategory.textContent = categoryEl.value || 'Kategori';
    }
    
    [titleEl, excerptEl, categoryEl].forEach(el => {
        if (el) el.addEventListener('input', updatePreview);
        if (el) el.addEventListener('change', updatePreview);
    });
    
    // ---- 4. Status selector ----
    document.querySelectorAll('.status-option-compact').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.status-option-compact').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    
    // ---- 5. Editor toolbar actions ----
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const textarea = document.getElementById('aContent');
            if (!textarea) return;
            
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selected = textarea.value.substring(start, end);
            let insert = '';
            
            switch (action) {
                case 'h2': insert = `## ${selected || 'Heading'}`; break;
                case 'h3': insert = `### ${selected || 'Subheading'}`; break;
                case 'bold': insert = `**${selected || 'teks tebal'}**`; break;
                case 'italic': insert = `*${selected || 'teks miring'}*`; break;
                case 'ul': insert = `\n- ${selected || 'Item'}`; break;
                case 'ol': insert = `\n1. ${selected || 'Item'}`; break;
                case 'quote': insert = `\n> ${selected || 'Kutipan'}`; break;
                case 'link': 
                    const url = prompt('Masukkan URL:', 'https://');
                    if (url) insert = `[${selected || 'Teks tautan'}](${url})`;
                    break;
                case 'youtube':
                    const ytUrl = prompt('Tempel URL YouTube:', 'https://youtube.com/watch?v=...');
                    if (ytUrl) insert = `\n${ytUrl}\n`;
                    break;
            }
            
            if (insert) {
                textarea.value = textarea.value.substring(0, start) + insert + textarea.value.substring(end);
                textarea.focus();
                textarea.dispatchEvent(new Event('input'));
            }
        });
    });
    
    // ---- 6. Search clear button ----
    const searchInput = document.getElementById('artSearch');
    const searchClear = document.getElementById('artSearchClear');
    
    if (searchInput && searchClear) {
        searchInput.addEventListener('input', () => {
            searchClear.style.display = searchInput.value ? 'grid' : 'none';
        });
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchClear.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }
    
    // ---- 7. Filter pills ----
    document.querySelectorAll('.article-pill-btn').forEach(pill => {
        pill.addEventListener('click', () => {
            document.querySelectorAll('.article-pill-btn').forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            const filter = pill.dataset.filter;
            window.dispatchEvent(new CustomEvent('filterArticles', { detail: { filter } }));
        });
    });
})();
</script>