<?php
/**
 * ============================================================
 * MANAJEMEN ARTIKEL (ADMIN) — ULTIMATE EDITION v7.0
 * Panel admin untuk menulis, menyunting, dan mengelola artikel
 * dengan auto-save, live preview, dan keyboard shortcuts.
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
        <span class="last-updated" id="articlesLastUpdated" title="Waktu sinkronisasi terakhir">
            <i class="ph ph-clock"></i>
            <span>Baru saja</span>
        </span>
        <a href="<?= url('artikel') ?>" target="_blank" class="btn btn-ghost" rel="noopener">
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

<!-- ========== STAT CARDS (ANIMATED) ========== -->
<section class="articles-stats">
    <article class="article-stat-card glass-card stat-total">
        <div class="article-stat-glow"></div>
        <div class="article-stat-icon">
            <i class="ph ph-newspaper"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statTotal" data-count="0" data-v="0">0</strong>
            <span>Total Artikel</span>
        </div>
        <div class="article-stat-trend trend-total"><i class="ph ph-books"></i></div>
    </article>
    
    <article class="article-stat-card glass-card stat-published">
        <div class="article-stat-glow"></div>
        <div class="article-stat-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statPublished" data-count="0" data-v="0">0</strong>
            <span>Diterbitkan</span>
        </div>
        <div class="article-stat-trend trend-published"><i class="ph ph-broadcast"></i></div>
    </article>
    
    <article class="article-stat-card glass-card stat-draft">
        <div class="article-stat-glow"></div>
        <div class="article-stat-icon">
            <i class="ph ph-file-dashed"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statDraft" data-count="0" data-v="0">0</strong>
            <span>Draft</span>
        </div>
        <div class="article-stat-trend trend-draft"><i class="ph ph-note-pencil"></i></div>
    </article>
    
    <article class="article-stat-card glass-card stat-views">
        <div class="article-stat-glow"></div>
        <div class="article-stat-icon">
            <i class="ph ph-eye"></i>
        </div>
        <div class="article-stat-info">
            <strong id="statViews" data-count="0" data-v="0">0</strong>
            <span>Total Pembaca</span>
        </div>
        <div class="article-stat-trend trend-views"><i class="ph ph-trend-up"></i></div>
    </article>
</section>

<!-- ========== TABLE CARD ========== -->
<section class="glass-card table-card articles-table-wrap">
    
    <!-- Toolbar -->
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="artSearch" placeholder="Cari judul atau kategori..." aria-label="Cari artikel">
            <button class="search-clear" id="artSearchClear" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="artInfo" aria-live="polite">Memuat...</span>
            <button class="icon-btn" id="btnArtRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills (dengan count) -->
    <div class="articles-filter-pills" role="tablist" aria-label="Filter artikel">
        <button class="article-pill-btn active" data-filter="all" role="tab" aria-selected="true">
            <i class="ph ph-list"></i>
            Semua
            <span class="pill-count" id="pillCountAll">0</span>
        </button>
        <button class="article-pill-btn" data-filter="published" role="tab" aria-selected="false">
            <i class="ph ph-check-circle"></i>
            Diterbitkan
            <span class="pill-count count-published" id="pillCountPublished">0</span>
        </button>
        <button class="article-pill-btn" data-filter="draft" role="tab" aria-selected="false">
            <i class="ph ph-file-dashed"></i>
            Draft
            <span class="pill-count count-draft" id="pillCountDraft">0</span>
        </button>
        <span class="pill-divider"></span>
        <?php foreach ($categories as $c): ?>
        <button class="article-pill-btn pill-category" data-filter="cat:<?= e(strtolower($c)) ?>" role="tab" aria-selected="false">
            <i class="ph ph-tag"></i>
            <?= e($c) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Active filter indicator -->
    <div class="filter-indicator" id="artFilterIndicator" style="display:none;">
        <span><i class="ph ph-funnel"></i> Filter aktif: "<span id="artFilterText"></span>"</span>
        <button class="filter-clear" id="btnClearArtFilter">Bersihkan</button>
    </div>

    <!-- Table -->
    <div class="table-scroll">
        <table class="data-table articles-table">
            <caption class="visually-hidden">Daftar artikel organisasi</caption>
            <thead>
                <tr>
                    <th style="width:40%">Judul</th>
                    <th style="width:15%">Kategori</th>
                    <th style="width:12%">Penulis</th>
                    <th style="width:10%">Status</th>
                    <th style="width:13%">Tanggal</th>
                    <th style="width:10%; text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody id="articleRows">
                <!-- Skeleton kaya (5 variasi) -->
                <tr class="skeleton-row skel-var-1">
                    <td><div class="skel-col"><div class="skel" style="height:14px;width:75%"></div><div class="skel" style="height:10px;width:50%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:80px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:90px"></div></td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:80px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-2">
                    <td><div class="skel-col"><div class="skel" style="height:14px;width:68%"></div><div class="skel" style="height:10px;width:45%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:85px"></div></td>
                    <td><div class="skel" style="height:22px;width:65px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:75px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-1">
                    <td><div class="skel-col"><div class="skel" style="height:14px;width:82%"></div><div class="skel" style="height:10px;width:55%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:85px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:88px"></div></td>
                    <td><div class="skel" style="height:22px;width:72px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:82px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-2">
                    <td><div class="skel-col"><div class="skel" style="height:14px;width:72%"></div><div class="skel" style="height:10px;width:48%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:75px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:92px"></div></td>
                    <td><div class="skel" style="height:22px;width:68px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:78px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
                <tr class="skeleton-row skel-var-1">
                    <td><div class="skel-col"><div class="skel" style="height:14px;width:78%"></div><div class="skel" style="height:10px;width:52%;margin-top:6px"></div></div></td>
                    <td><div class="skel" style="height:22px;width:82px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:86px"></div></td>
                    <td><div class="skel" style="height:22px;width:70px;border-radius:99px"></div></td>
                    <td><div class="skel" style="height:12px;width:80px"></div></td>
                    <td><div class="skel" style="height:28px;width:76px;margin-left:auto"></div></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Empty state (kaya) -->
    <div class="empty-rich" id="emptyArticles" style="display:none;">
        <div class="empty-illustration">
            <div class="empty-orb empty-orb-1"></div>
            <div class="empty-orb empty-orb-2"></div>
            <i class="ph ph-newspaper"></i>
            <span class="empty-spark"></span>
        </div>
        <h3>Belum Ada Artikel</h3>
        <p>Mulai publikasikan cerita dan informasi organisasi Anda.</p>
        <div class="empty-features">
            <span><i class="ph ph-check-circle"></i> Editor markdown</span>
            <span><i class="ph ph-check-circle"></i> Auto-save draft</span>
            <span><i class="ph ph-check-circle"></i> Langsung publish</span>
        </div>
        <button class="btn btn-primary" id="emptyAddArticle">
            <i class="ph ph-plus"></i>
            <span class="btn-text">Tulis Artikel Pertama</span>
        </button>
    </div>

    <!-- No-match state -->
    <div class="empty-rich articles-nomatch" id="artNoMatch" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-magnifying-glass"></i>
        </div>
        <h3>Tidak Ada Hasil</h3>
        <p>Tidak ada artikel yang cocok dengan filter atau kata kunci saat ini.</p>
        <button class="btn btn-ghost btn-sm" id="btnResetArtFilter">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <span class="btn-text">Reset Filter</span>
        </button>
    </div>

    <!-- Pagination (dengan First/Last) -->
    <div class="table-foot">
        <span id="artPageInfo">Memuat...</span>
        <div class="pager">
            <button class="pager-btn icon-btn" id="artFirst" disabled aria-label="Halaman pertama">
                <i class="ph ph-caret-double-left"></i>
            </button>
            <button class="pager-btn icon-btn" id="artPrev" disabled aria-label="Sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <span class="pager-current" id="artPagerCur">1 / 1</span>
            <button class="pager-btn icon-btn" id="artNext" disabled aria-label="Berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
            <button class="pager-btn icon-btn" id="artLast" disabled aria-label="Halaman terakhir">
                <i class="ph ph-caret-double-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== MODAL FORM ARTIKEL (ENHANCED) ========== -->
<div class="modal-backdrop" id="articleModal" role="dialog" aria-labelledby="articleModalTitle" aria-modal="true">
    <div class="modal glass-card modal-xl">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-newspaper" id="articleModalIcon"></i></span>
                <div>
                    <h3 id="articleModalTitle">Tulis Artikel Baru</h3>
                    <p class="modal-sub" id="articleModalSub">Artikel yang diterbitkan langsung terlihat oleh publik.</p>
                </div>
            </div>
            <div class="modal-head-actions">
                <span class="autosave-indicator" id="articleAutosave" aria-live="polite">
                    <i class="ph ph-cloud-check"></i>
                    <span id="articleAutosaveText">Draft tersimpan</span>
                </span>
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
                                       class="article-title-input"
                                       data-autosave>
                                <span class="field-focus-ring"></span>
                            </div>
                            <div class="field-footer">
                                <span class="field-error" data-error="title"></span>
                                <div class="char-progress-wrap">
                                    <span class="char-counter" id="titleCounter">0 / 200</span>
                                    <div class="char-progress"><div class="char-progress-fill" id="titleBar"></div></div>
                                </div>
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
                                      maxlength="300"
                                      data-autosave></textarea>
                            <div class="field-footer">
                                <small class="asset-hint">Muncul di kartu artikel di halaman publik.</small>
                                <div class="char-progress-wrap">
                                    <span class="char-counter" id="excerptCounter">0 / 300</span>
                                    <div class="char-progress"><div class="char-progress-fill" id="excerptBar"></div></div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Content Editor -->
                    <div class="form-section">
                        <div class="article-editor-wrap">
                            <div class="article-editor-toolbar" role="toolbar" aria-label="Toolbar editor">
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2" aria-label="Heading 2">
                                        <i class="ph ph-text-h-two"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3" aria-label="Heading 3">
                                        <i class="ph ph-text-h-three"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="bold" title="Bold (Ctrl+B)" aria-label="Bold">
                                        <i class="ph ph-text-b"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="italic" title="Italic (Ctrl+I)" aria-label="Italic">
                                        <i class="ph ph-text-italic"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="ul" title="Bullet List" aria-label="Bullet list">
                                        <i class="ph ph-list-bullets"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="ol" title="Numbered List" aria-label="Numbered list">
                                        <i class="ph ph-list-numbers"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="quote" title="Quote" aria-label="Quote">
                                        <i class="ph ph-quotes"></i>
                                    </button>
                                </div>
                                <div class="toolbar-sep"></div>
                                <div class="toolbar-group">
                                    <button type="button" class="toolbar-btn" data-action="link" title="Tautan (Ctrl+K)" aria-label="Insert link">
                                        <i class="ph ph-link"></i>
                                    </button>
                                    <button type="button" class="toolbar-btn" data-action="youtube" title="Embed YouTube" aria-label="Embed YouTube">
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
                                          class="article-content-textarea"
                                          data-autosave></textarea>
                                <span class="field-error" data-error="content"></span>
                            </label>
                        </div>
                        <div class="field-footer">
                            <div class="editor-stats">
                                <span id="wordCount">0 kata</span>
                                <span class="editor-stats-sep">•</span>
                                <span id="readTime">0 menit baca</span>
                            </div>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="contentCounter">0 / 50000</span>
                                <div class="char-progress"><div class="char-progress-fill" id="contentBar"></div></div>
                            </div>
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
                        <select name="category" id="aCategory" class="field-select" data-autosave>
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
                    <button type="submit" class="btn btn-primary" id="btnPublishArticle">
                        <i class="ph ph-paper-plane-tilt"></i>
                        <span class="btn-text">Terbitkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL PREVIEW (ENHANCED) ========== -->
<div class="modal-backdrop" id="articlePreviewModal" role="dialog" aria-labelledby="previewModalTitle" aria-modal="true">
    <div class="modal glass-card modal-xl modal-fullheight">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-eye"></i></span>
                <div>
                    <h3 id="previewModalTitle">Pratinjau Artikel</h3>
                    <p class="modal-sub">Begini tampilan artikel di halaman publik.</p>
                </div>
            </div>
            <div class="modal-head-actions">
                <button type="button" class="btn btn-ghost btn-xs" id="btnCopyPreviewLink" title="Salin link">
                    <i class="ph ph-link"></i>
                    <span class="btn-text">Salin Link</span>
                </button>
                <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                    <i class="ph ph-x"></i>
                </button>
            </div>
        </div>
        <div class="modal-body article-preview-body" id="articlePreviewContent">
            <!-- Filled by JS -->
        </div>
    </div>
</div>

<!-- ========== MODAL KONFIRMASI HAPUS (ENHANCED) ========== -->
<div class="modal-backdrop" id="articleDeleteModal" role="dialog" aria-labelledby="deleteArticleTitle" aria-modal="true">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            </div>
            <div>
                <h3 id="deleteArticleTitle">Hapus Artikel?</h3>
                <p class="modal-sub">Artikel akan hilang dari halaman publik.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="delete-item-preview" id="articleDeletePreview">
                <i class="ph ph-newspaper"></i>
                <span id="articleDeleteText">—</span>
            </div>
            <div class="danger-note">
                <div class="danger-note-icon"><i class="ph ph-warning"></i></div>
                <div class="danger-note-content">
                    <strong>Peringatan</strong>
                    <p>Tindakan ini permanen dan tidak dapat dibatalkan. Artikel yang sudah dihapus tidak dapat dipulihkan.</p>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmArticleDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus Permanen</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== UNSAVED CHANGES MODAL ========== -->
<div class="modal-backdrop" id="unsavedModal" role="dialog" aria-labelledby="unsavedTitle" aria-modal="true">
    <div class="modal modal-sm glass-card">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-3"><i class="ph ph-warning"></i></span>
                <div>
                    <h3 id="unsavedTitle">Perubahan Belum Disimpan</h3>
                    <p class="modal-sub">Apa yang ingin Anda lakukan?</p>
                </div>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text">Anda memiliki perubahan yang belum disimpan. Perubahan akan hilang jika Anda menutup formulir.</p>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" id="btnUnsavedStay">
                <span class="btn-text">Tetap di Sini</span>
            </button>
            <button type="button" class="btn btn-danger-ghost" id="btnUnsavedDiscard">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Buang Perubahan</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdNew"><kbd>N</kbd> Tulis Artikel</span>
    <span class="kbd-item" id="kbdRefresh"><kbd>R</kbd> Segarkan</span>
    <span class="kbd-item" id="kbdSearch"><kbd>/</kbd> Cari</span>
    <span class="kbd-item"><kbd>Ctrl</kbd>+<kbd>S</kbd> Simpan</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup</span>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<script src="<?= asset('js/articles.js') ?>"></script>

<!-- ========== ENHANCER v7.0 (self-contained) ========== -->
<script>
(function(){
    'use strict';

    const DRAFT_KEY = 'article_draft';
    let isDirty = false;
    let saveTimeout = null;

    /* ---- Helper: Animate counter ---- */
    function animate(el, to) {
        if (!el) return;
        const from = parseInt(el.dataset.v || '0', 10) || 0;
        el.dataset.v = to;
        if (from === to) { el.textContent = to.toLocaleString('id-ID'); return; }
        const t0 = performance.now(), dur = 700;
        const tick = t => {
            const p = Math.min(1, (t - t0) / dur);
            const v = Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3)));
            el.textContent = v.toLocaleString('id-ID');
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    /* ---- 1. Sync stats + pill counts dari rows ---- */
    function syncStats() {
        const rows = document.querySelectorAll('#articleRows tr:not(.skeleton-row)');
        let total = 0, published = 0, draft = 0, views = 0;

        rows.forEach(tr => {
            total++;
            const statusCell = tr.querySelector('td:nth-child(4)');
            const status = statusCell?.textContent.trim().toLowerCase() || '';
            if (status.includes('terbit') || status.includes('publish')) published++;
            else if (status.includes('draft')) draft++;
            
            // Parse views dari data attribute atau text
            const viewsAttr = tr.dataset.views;
            if (viewsAttr) views += parseInt(viewsAttr, 10) || 0;
        });

        animate(document.getElementById('statTotal'), total);
        animate(document.getElementById('statPublished'), published);
        animate(document.getElementById('statDraft'), draft);
        animate(document.getElementById('statViews'), views);

        const pillAll = document.getElementById('pillCountAll');
        const pillPub = document.getElementById('pillCountPublished');
        const pillDraft = document.getElementById('pillCountDraft');
        if (pillAll) pillAll.textContent = total;
        if (pillPub) pillPub.textContent = published;
        if (pillDraft) pillDraft.textContent = draft;

        // Empty state vs no-match
        const empty = document.getElementById('emptyArticles');
        const noMatch = document.getElementById('artNoMatch');
        const visible = Array.from(rows).filter(r => r.style.display !== 'none').length;
        if (empty) empty.style.display = total === 0 ? 'flex' : 'none';
        if (noMatch) noMatch.style.display = (total > 0 && visible === 0) ? 'flex' : 'none';

        // Last updated
        const lastEl = document.getElementById('articlesLastUpdated');
        if (lastEl) {
            const span = lastEl.querySelector('span');
            if (span) {
                const now = new Date();
                span.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                lastEl.title = 'Terakhir diperbarui: ' + now.toLocaleString('id-ID');
            }
        }
    }

    /* ---- 2. Character counters dengan progress bar ---- */
    function setupCounter(inputId, counterId, barId, max) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        const bar = document.getElementById(barId);
        if (!input || !counter || !bar) return;
        
        const update = () => {
            const len = input.value.length;
            const pct = Math.min(100, (len / max) * 100);
            counter.textContent = len + ' / ' + max;
            bar.style.width = pct + '%';
            counter.className = 'char-counter';
            bar.className = 'char-progress-fill';
            if (len > max * 0.85) { counter.classList.add('warn'); bar.classList.add('warn'); }
            if (len > max * 0.95) { counter.classList.add('danger'); bar.classList.add('danger'); }
        };
        input.addEventListener('input', update);
        update();
    }

    setupCounter('aTitle', 'titleCounter', 'titleBar', 200);
    setupCounter('aExcerpt', 'excerptCounter', 'excerptBar', 300);
    setupCounter('aContent', 'contentCounter', 'contentBar', 50000);

    /* ---- 3. Word count & read time ---- */
    const contentEl = document.getElementById('aContent');
    const wordCountEl = document.getElementById('wordCount');
    const readTimeEl = document.getElementById('readTime');
    
    function updateWordCount() {
        if (!contentEl || !wordCountEl || !readTimeEl) return;
        const words = contentEl.value.trim().split(/\s+/).filter(w => w.length > 0).length;
        wordCountEl.textContent = words.toLocaleString('id-ID') + ' kata';
        readTimeEl.textContent = Math.max(1, Math.ceil(words / 200)) + ' menit baca';
    }
    contentEl?.addEventListener('input', updateWordCount);

    /* ---- 4. Live preview (side card) ---- */
    const titleEl = document.getElementById('aTitle');
    const excerptEl = document.getElementById('aExcerpt');
    const categoryEl = document.getElementById('aCategory');
    const previewTitle = document.getElementById('previewTitle');
    const previewExcerpt = document.getElementById('previewExcerpt');
    const previewCategory = document.getElementById('previewCategory');
    
    function updatePreview() {
        if (previewTitle) previewTitle.textContent = titleEl?.value || 'Judul artikel Anda...';
        if (previewExcerpt) previewExcerpt.textContent = excerptEl?.value || 'Ringkasan akan muncul di sini...';
        if (previewCategory) previewCategory.textContent = categoryEl?.value || 'Kategori';
    }
    
    [titleEl, excerptEl, categoryEl].forEach(el => {
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    /* ---- 5. Status selector ---- */
    document.querySelectorAll('.status-option-compact').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.status-option-compact').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            const radio = opt.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
            markDirty();
        });
    });

    /* ---- 6. Editor toolbar actions (dengan visual feedback) ---- */
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const textarea = document.getElementById('aContent');
            if (!textarea) return;
            
            // Visual feedback
            btn.classList.add('active');
            setTimeout(() => btn.classList.remove('active'), 200);
            
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
                markDirty();
            }
        });
    });

    /* ---- 7. Search clear button ---- */
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

    /* ---- 8. Filter pills + indicator ---- */
    const filterPills = document.querySelectorAll('.article-pill-btn');
    const filterIndicator = document.getElementById('artFilterIndicator');
    const filterText = document.getElementById('artFilterText');

    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            filterPills.forEach(p => {
                const on = (p === pill);
                p.classList.toggle('active', on);
                p.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            const filter = pill.dataset.filter;
            if (filterIndicator && filterText) {
                if (filter === 'all') {
                    filterIndicator.style.display = 'none';
                } else {
                    filterText.textContent = pill.textContent.trim().replace(/\d+/g, '').trim();
                    filterIndicator.style.display = 'flex';
                }
            }
            window.dispatchEvent(new CustomEvent('filterArticles', { detail: { filter } }));
        });
    });

    /* ---- Clear filter ---- */
    const clearFilterBtn = document.getElementById('btnClearArtFilter');
    const resetFilterBtn = document.getElementById('btnResetArtFilter');
    function resetArtFilters() {
        filterPills.forEach(p => {
            const on = p.dataset.filter === 'all';
            p.classList.toggle('active', on);
            p.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        if (filterIndicator) filterIndicator.style.display = 'none';
        if (searchInput) {
            searchInput.value = '';
            if (searchClear) searchClear.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
        }
        window.dispatchEvent(new CustomEvent('filterArticles', { detail: { filter: 'all' } }));
    }
    clearFilterBtn?.addEventListener('click', resetArtFilters);
    resetFilterBtn?.addEventListener('click', resetArtFilters);

    /* ---- 9. Auto-save draft ke localStorage ---- */
    const articleModal = document.getElementById('articleModal');
    const articleForm = document.getElementById('articleForm');
    const autosaveIndicator = document.getElementById('articleAutosave');
    const autosaveText = document.getElementById('articleAutosaveText');

    function saveDraft() {
        if (saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            try {
                const data = {
                    id: document.getElementById('aId')?.value || '',
                    title: titleEl?.value || '',
                    excerpt: excerptEl?.value || '',
                    content: contentEl?.value || '',
                    category: categoryEl?.value || '',
                    status: articleForm?.querySelector('input[name="status"]:checked')?.value || 'draft',
                    savedAt: Date.now()
                };
                localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
                
                if (autosaveIndicator && autosaveText) {
                    autosaveIndicator.classList.add('show');
                    autosaveText.textContent = 'Draft tersimpan';
                    setTimeout(() => {
                        autosaveIndicator.classList.remove('show');
                    }, 2000);
                }
            } catch (e) { /* quota exceeded */ }
        }, 800);
    }

    function loadDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            const data = JSON.parse(raw);
            
            // Hanya load jika draft belum terlalu lama (24 jam)
            if (Date.now() - data.savedAt > 86400000) {
                localStorage.removeItem(DRAFT_KEY);
                return false;
            }
            
            if (titleEl) titleEl.value = data.title || '';
            if (excerptEl) excerptEl.value = data.excerpt || '';
            if (contentEl) contentEl.value = data.content || '';
            if (categoryEl) categoryEl.value = data.category || '';
            
            // Update counters
            [titleEl, excerptEl, contentEl].forEach(el => el?.dispatchEvent(new Event('input')));
            updateWordCount();
            updatePreview();
            
            if (autosaveIndicator && autosaveText) {
                autosaveIndicator.classList.add('show');
                autosaveText.textContent = 'Draft dipulihkan';
                setTimeout(() => autosaveIndicator.classList.remove('show'), 2500);
            }
            
            return true;
        } catch (e) { return false; }
    }

    function clearDraft() {
        localStorage.removeItem(DRAFT_KEY);
    }

    // Auto-save saat mengetik
    articleForm?.querySelectorAll('[data-autosave]').forEach(el => {
        el.addEventListener('input', () => {
            markDirty();
            saveDraft();
        });
        el.addEventListener('change', saveDraft);
    });

    /* ---- 10. Dirty state detection ---- */
    function markDirty() {
        isDirty = true;
        const btnPublish = document.getElementById('btnPublishArticle');
        const btnSaveDraft = document.getElementById('btnSaveDraft');
        if (btnPublish) btnPublish.disabled = false;
        if (btnSaveDraft) btnSaveDraft.disabled = false;
    }

    function clearDirty() {
        isDirty = false;
    }

    /* ---- 11. Unsaved changes modal ---- */
    const unsavedModal = document.getElementById('unsavedModal');
    const btnUnsavedStay = document.getElementById('btnUnsavedStay');
    const btnUnsavedDiscard = document.getElementById('btnUnsavedDiscard');
    let pendingClose = null;

    function showUnsavedModal(callback) {
        pendingClose = callback;
        unsavedModal?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function hideUnsavedModal() {
        unsavedModal?.classList.remove('show');
        document.body.style.overflow = '';
        pendingClose = null;
    }

    btnUnsavedStay?.addEventListener('click', hideUnsavedModal);
    btnUnsavedDiscard?.addEventListener('click', () => {
        clearDraft();
        hideUnsavedModal();
        if (pendingClose) pendingClose();
    });

    // Intercept modal close
    articleModal?.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isDirty) {
                showUnsavedModal(() => {
                    articleModal.classList.remove('show');
                    document.body.style.overflow = '';
                });
            } else {
                articleModal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    // Clear draft saat modal dibuka untuk artikel baru
    document.getElementById('btnAddArticle')?.addEventListener('click', () => {
        clearDraft();
        setTimeout(loadDraft, 100);
    });

    // Clear draft saat submit berhasil
    articleForm?.addEventListener('submit', () => {
        clearDraft();
        clearDirty();
    });

    // Save draft button
    document.getElementById('btnSaveDraft')?.addEventListener('click', () => {
        saveDraft();
        if (window.toast) window.toast('Draft disimpan.', 'success');
    });

    /* ---- 12. Preview modal dengan konten ---- */
    const previewModal = document.getElementById('articlePreviewModal');
    const previewContent = document.getElementById('articlePreviewContent');
    const btnPreview = document.getElementById('btnPreviewArticle');
    const btnCopyLink = document.getElementById('btnCopyPreviewLink');

    function openPreviewModal() {
        if (!previewContent) return;
        
        const title = titleEl?.value || 'Judul belum diisi';
        const excerpt = excerptEl?.value || '';
        const content = contentEl?.value || '';
        const category = categoryEl?.value || 'Kategori';
        const words = content.trim().split(/\s+/).filter(w => w.length > 0).length;
        const readTime = Math.max(1, Math.ceil(words / 200));
        
        // Simple markdown to HTML conversion
        let html = content
            .replace(/^### (.+)$/gm, '<h3>$1</h3>')
            .replace(/^## (.+)$/gm, '<h2>$1</h2>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank">$1</a>')
            .replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>')
            .replace(/^- (.+)$/gm, '<li>$1</li>')
            .replace(/^(\d+)\. (.+)$/gm, '<li>$2</li>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');
        
        html = '<p>' + html + '</p>';
        html = html.replace(/<p><li>/g, '<ul><li>').replace(/<\/li><\/p>/g, '</li></ul>');
        
        // YouTube embed
        html = html.replace(/https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/g, 
            '<div class="preview-youtube"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>');
        
        previewContent.innerHTML = `
            <article class="preview-article">
                <header class="preview-article-header">
                    <span class="preview-article-category">${category}</span>
                    <h1 class="preview-article-title">${title}</h1>
                    <div class="preview-article-meta">
                        <span><i class="ph ph-user-circle"></i> Admin</span>
                        <span><i class="ph ph-calendar-blank"></i> ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</span>
                        <span><i class="ph ph-clock"></i> ${readTime} menit baca</span>
                        <span><i class="ph ph-text-align-left"></i> ${words.toLocaleString('id-ID')} kata</span>
                    </div>
                    ${excerpt ? `<p class="preview-article-excerpt">${excerpt}</p>` : ''}
                </header>
                <div class="preview-article-content">${html}</div>
            </article>
        `;
        
        previewModal?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    btnPreview?.addEventListener('click', openPreviewModal);
    previewModal?.querySelector('[data-close-modal]')?.addEventListener('click', () => {
        previewModal.classList.remove('show');
        document.body.style.overflow = '';
    });
    previewModal?.addEventListener('click', (e) => {
        if (e.target === previewModal) {
            previewModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    btnCopyLink?.addEventListener('click', () => {
        if (window.toast) window.toast('Link pratinjau disalin.', 'success');
    });

    /* ---- 13. Keyboard shortcuts ---- */
    document.getElementById('kbdNew')?.addEventListener('click', () => {
        document.getElementById('btnAddArticle')?.click();
    });
    document.getElementById('kbdRefresh')?.addEventListener('click', () => {
        document.getElementById('btnArtRefresh')?.click();
    });
    document.getElementById('kbdSearch')?.addEventListener('click', () => {
        searchInput?.focus();
    });

    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        const inInput = (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT');
        
        // Esc closes modals
        if (e.key === 'Escape') {
            if (unsavedModal?.classList.contains('show')) { hideUnsavedModal(); return; }
            if (previewModal?.classList.contains('show')) {
                previewModal.classList.remove('show');
                document.body.style.overflow = '';
                return;
            }
            if (articleModal?.classList.contains('show')) {
                if (isDirty) {
                    showUnsavedModal(() => {
                        articleModal.classList.remove('show');
                        document.body.style.overflow = '';
                    });
                } else {
                    articleModal.classList.remove('show');
                    document.body.style.overflow = '';
                }
                return;
            }
        }
        
        // Ctrl+S = save (dalam modal)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            if (articleModal?.classList.contains('show')) {
                e.preventDefault();
                document.getElementById('btnSaveDraft')?.click();
                return;
            }
        }
        
        // Ctrl+B = bold (dalam textarea)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b' && inInput) {
            if (document.activeElement.id === 'aContent') {
                e.preventDefault();
                document.querySelector('.toolbar-btn[data-action="bold"]')?.click();
                return;
            }
        }
        
        // Ctrl+I = italic (dalam textarea)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'i' && inInput) {
            if (document.activeElement.id === 'aContent') {
                e.preventDefault();
                document.querySelector('.toolbar-btn[data-action="italic"]')?.click();
                return;
            }
        }
        
        // Ctrl+K = link (dalam textarea)
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k' && inInput) {
            if (document.activeElement.id === 'aContent') {
                e.preventDefault();
                document.querySelector('.toolbar-btn[data-action="link"]')?.click();
                return;
            }
        }
        
        if (inInput) return;
        
        // N = new article
        if (e.key.toLowerCase() === 'n' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnAddArticle')?.click();
        }
        // R = refresh
        if (e.key.toLowerCase() === 'r' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnArtRefresh')?.click();
        }
        // / = search
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            searchInput?.focus();
        }
    });

    /* ---- 14. MutationObserver untuk sync stats ---- */
    const articleRows = document.getElementById('articleRows');
    if (articleRows) {
        new MutationObserver(() => {
            syncStats();
        }).observe(articleRows, { childList: true, subtree: true, attributes: true, attributeFilter: ['style'] });
    }

    /* ---- 15. Warn before unload jika dirty ---- */
    window.addEventListener('beforeunload', (e) => {
        if (isDirty && articleModal?.classList.contains('show')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ---- Initial sync ---- */
    syncStats();
    updateWordCount();
})();
</script>

<!-- ========== STYLING (v7.0) ========== -->
<style>
/* ---- Last Updated ---- */
.last-updated {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 99px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    font-size: 11.5px;
    font-weight: 600;
    color: var(--txt-1);
}
.last-updated i { color: var(--acc); font-size: 13px; }

/* ---- Visually hidden ---- */
.visually-hidden {
    position: absolute; width: 1px; height: 1px;
    margin: -1px; padding: 0; overflow: hidden;
    clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}

/* ---- Articles Stats (enhanced) ---- */
.articles-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.article-stat-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 24px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.article-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}
.article-stat-glow {
    position: absolute;
    top: -30px; right: -30px;
    width: 140px; height: 140px;
    border-radius: 50%;
    pointer-events: none;
    transition: transform .5s;
    opacity: 0.8;
}
.article-stat-card:hover .article-stat-glow { transform: scale(1.3); }
.stat-total .article-stat-glow { background: radial-gradient(circle, rgba(99,102,241,.25), transparent 70%); }
.stat-published .article-stat-glow { background: radial-gradient(circle, rgba(16,185,129,.25), transparent 70%); }
.stat-draft .article-stat-glow { background: radial-gradient(circle, rgba(245,158,11,.25), transparent 70%); }
.stat-views .article-stat-glow { background: radial-gradient(circle, rgba(14,165,233,.25), transparent 70%); }

.article-stat-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 22px; color: #fff;
    flex-shrink: 0;
    position: relative; z-index: 1;
    transition: transform .3s;
}
.article-stat-card:hover .article-stat-icon { transform: scale(1.08) rotate(-5deg); }
.stat-total .article-stat-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-published .article-stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-draft .article-stat-icon { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-views .article-stat-icon { background: linear-gradient(135deg, #0ea5e9, #22d3ee); }

.article-stat-info { flex: 1; min-width: 0; position: relative; z-index: 1; }
.article-stat-info strong {
    display: block;
    font-size: 26px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.article-stat-info span {
    display: block;
    font-size: 12px;
    color: var(--txt-1);
    font-weight: 600;
    margin-top: 3px;
}

/* Trend icon */
.article-stat-trend {
    position: absolute;
    top: 14px; right: 14px;
    width: 28px; height: 28px;
    border-radius: 9px;
    display: grid; place-items: center;
    font-size: 14px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    color: var(--txt-2);
}
.article-stat-trend.trend-total { color: #a5b4fc; background: rgba(99,102,241,.1); border-color: rgba(99,102,241,.25); }
.article-stat-trend.trend-published { color: #6ee7b7; background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.25); }
.article-stat-trend.trend-draft { color: #fcd34d; background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.25); }
.article-stat-trend.trend-views { color: #67e8f9; background: rgba(14,165,233,.1); border-color: rgba(14,165,233,.25); }

/* ---- Filter Pills (dengan count) ---- */
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
.pill-divider {
    width: 1px;
    height: 24px;
    background: var(--glass-brd);
    align-self: center;
    margin: 0 4px;
}
.pill-count {
    min-width: 20px;
    padding: 1px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
}
.article-pill-btn.active .pill-count { background: rgba(255,255,255,.25); }
.pill-count.count-published { background: rgba(16,185,129,.2); color: #6ee7b7; }
.pill-count.count-draft { background: rgba(245,158,11,.2); color: #fcd34d; }
.article-pill-btn.active .pill-count.count-published,
.article-pill-btn.active .pill-count.count-draft {
    background: rgba(255,255,255,.25);
    color: #fff;
}
.pill-category:not(.active) { color: var(--txt-2); }

/* ---- Filter Indicator ---- */
.filter-indicator {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 22px 8px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(99,102,241,.08);
    border: 1px solid rgba(99,102,241,.2);
    font-size: 12px;
    color: var(--txt-1);
    animation: filter-in 0.3s ease;
}
@keyframes filter-in {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.filter-indicator > span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.filter-indicator i { color: var(--acc); font-size: 14px; }
.filter-clear {
    background: transparent;
    border: 1px solid rgba(99,102,241,.3);
    color: var(--acc);
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.filter-clear:hover {
    background: rgba(99,102,241,.15);
    border-color: var(--pri);
}

/* ---- XL Modal ---- */
.modal-xl { width: min(1100px, 95vw); max-height: 92vh; }
.modal-fullheight { max-height: 95vh; }
.modal-head-actions { display: flex; gap: 8px; align-items: center; }

/* ---- Autosave indicator ---- */
.autosave-indicator {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 99px;
    background: rgba(16,185,129,.1);
    border: 1px solid rgba(16,185,129,.25);
    font-size: 11px;
    font-weight: 600;
    color: #6ee7b7;
    opacity: 0;
    transform: translateY(-4px);
    transition: all .3s;
}
.autosave-indicator.show { opacity: 1; transform: translateY(0); }
.autosave-indicator i { font-size: 13px; }

/* ---- Article Form Body (2 column) ---- */
.article-form-body {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    padding: 24px;
    align-items: start;
}
.article-form-main { display: flex; flex-direction: column; gap: 20px; }
.form-section { margin-bottom: 0; }

/* Title input large */
.article-title-input {
    font-size: 20px !important;
    font-weight: 700;
    padding: 16px 18px !important;
}

/* Editor toolbar (dengan active state) */
.article-editor-wrap {
    border-radius: 14px;
    border: 1.5px solid var(--glass-brd);
    overflow: hidden;
    background: rgba(255,255,255,.02);
    transition: border-color 0.25s;
}
.article-editor-wrap:focus-within { border-color: var(--pri); }
.article-editor-toolbar {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 12px;
    background: rgba(255,255,255,.03);
    border-bottom: 1px solid var(--glass-brd);
    flex-wrap: wrap;
}
.toolbar-group { display: flex; gap: 2px; }
.toolbar-btn {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    cursor: pointer;
    display: grid; place-items: center;
    font-size: 15px;
    transition: all 0.15s;
}
.toolbar-btn:hover {
    background: rgba(255,255,255,.08);
    color: var(--txt-0);
}
.toolbar-btn.active {
    background: rgba(99,102,241,.2);
    color: var(--acc);
}
.toolbar-sep { width: 1px; height: 20px; background: var(--glass-brd); margin: 0 4px; }
.toolbar-help { margin-left: auto; }
.toolbar-hint {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--txt-2);
}
.toolbar-hint i { color: var(--acc); }
.article-content-textarea {
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    min-height: 300px;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 13.5px;
    line-height: 1.7;
}
.article-content-textarea:focus { box-shadow: none !important; }

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
.side-section-title i { font-size: 14px; color: var(--acc); }

/* Compact status selector */
.status-selector.compact { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
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
.status-option-compact:hover { background: rgba(255,255,255,.05); }
.status-option-compact.active {
    border-color: var(--pri);
    background: rgba(99,102,241,.1);
    color: var(--txt-0);
}
.status-option-icon {
    width: 24px; height: 24px;
    border-radius: 6px;
    background: rgba(255,255,255,.06);
    display: grid; place-items: center;
    font-size: 13px;
    color: var(--txt-1);
}
.status-option-compact.active .status-option-icon {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
}

/* Tips list */
.tips-list { list-style: none; display: flex; flex-direction: column; gap: 8px; margin: 0; padding: 0; }
.tips-list li {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--txt-1);
}
.tips-list i { font-size: 14px; color: #10b981; }

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
.preview-body { padding: 14px; }
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

/* Preview modal content */
.article-preview-body {
    padding: 32px;
    max-height: 70vh;
    overflow-y: auto;
}
.preview-article { max-width: 720px; margin: 0 auto; }
.preview-article-header { margin-bottom: 32px; }
.preview-article-category {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 99px;
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 16px;
}
.preview-article-title {
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.8px;
    line-height: 1.2;
    margin-bottom: 16px;
}
.preview-article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 12.5px;
    color: var(--txt-1);
    padding-bottom: 16px;
    border-bottom: 1px solid var(--glass-brd);
}
.preview-article-meta span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.preview-article-meta i { color: var(--acc); }
.preview-article-excerpt {
    font-size: 16px;
    font-style: italic;
    color: var(--txt-1);
    line-height: 1.6;
    margin-top: 16px;
    padding-left: 16px;
    border-left: 3px solid var(--acc);
}
.preview-article-content {
    font-size: 15px;
    line-height: 1.8;
    color: var(--txt-0);
}
.preview-article-content h2 {
    font-size: 24px;
    font-weight: 800;
    margin: 32px 0 16px;
    letter-spacing: -0.3px;
}
.preview-article-content h3 {
    font-size: 18px;
    font-weight: 700;
    margin: 24px 0 12px;
}
.preview-article-content p { margin-bottom: 16px; }
.preview-article-content strong { font-weight: 700; }
.preview-article-content em { font-style: italic; }
.preview-article-content a {
    color: var(--acc);
    text-decoration: underline;
}
.preview-article-content blockquote {
    padding: 16px 20px;
    margin: 20px 0;
    border-left: 4px solid var(--acc);
    background: rgba(99,102,241,.05);
    border-radius: 0 8px 8px 0;
    font-style: italic;
}
.preview-article-content ul, .preview-article-content ol {
    padding-left: 24px;
    margin-bottom: 16px;
}
.preview-article-content li { margin-bottom: 8px; }
.preview-youtube {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    margin: 24px 0;
    border-radius: 12px;
}
.preview-youtube iframe {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border-radius: 12px;
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
.field-enhanced .field-label i { font-size: 15px; color: var(--acc); }
.field-input-wrap { position: relative; }
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
.field-enhanced input:focus ~ .field-focus-ring { opacity: 1; }

/* Character Counter dengan Progress Bar */
.field-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
    gap: 12px;
}
.char-progress-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 100px;
}
.char-progress {
    flex: 1;
    height: 4px;
    background: rgba(255,255,255,.06);
    border-radius: 99px;
    overflow: hidden;
    min-width: 40px;
}
.char-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--pri), var(--acc));
    border-radius: 99px;
    transition: width .3s, background .3s;
    width: 0%;
}
.char-progress-fill.warn { background: linear-gradient(90deg, var(--warn), #fb923c); }
.char-progress-fill.danger { background: linear-gradient(90deg, var(--danger), #f87171); }
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    transition: color 0.2s;
}
.char-counter.warn { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

.modal-foot-actions { display: flex; gap: 10px; }
.modal-foot-actions .btn:disabled { opacity: 0.5; cursor: not-allowed; }

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

/* ---- Empty State (kaya) ---- */
.empty-rich { padding: 50px 30px; text-align: center; }
.empty-illustration {
    position: relative;
    width: 90px; height: 90px;
    margin: 0 auto 20px;
    display: grid; place-items: center;
    background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(34,211,238,.1));
    border-radius: 26px;
    font-size: 36px;
    color: var(--acc);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(99,102,241,.2);
}
.empty-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(20px);
    opacity: 0.5;
    pointer-events: none;
}
.empty-orb-1 {
    width: 60px; height: 60px;
    background: var(--pri);
    top: -20px; left: -20px;
    animation: emptyOrb 4s ease-in-out infinite;
}
.empty-orb-2 {
    width: 50px; height: 50px;
    background: var(--acc);
    bottom: -15px; right: -15px;
    animation: emptyOrb 5s ease-in-out infinite reverse;
}
@keyframes emptyOrb {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10px, -10px); }
}
.empty-rich h3 { font-size: 18px; font-weight: 800; margin-bottom: 8px; letter-spacing: -.3px; }
.empty-rich p {
    color: var(--txt-1);
    font-size: 13.5px;
    max-width: 40ch;
    margin: 0 auto 16px;
    line-height: 1.65;
}
.empty-features {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.empty-features span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
}
.empty-features i { font-size: 14px; color: var(--ok); }

/* ---- No Match ---- */
.articles-nomatch { padding: 40px 24px; }
.articles-nomatch .empty-illustration { width: 80px; height: 80px; font-size: 32px; }

/* ---- Delete Modal Enhanced ---- */
.danger-icon-wrap {
    position: relative;
    width: 48px; height: 48px;
    display: grid; place-items: center;
    flex-shrink: 0;
}
.danger-icon-pulse {
    position: absolute; inset: 0;
    border-radius: 50%;
    background: rgba(239,68,68,.25);
    animation: danger-pulse 2s ease-in-out infinite;
}
@keyframes danger-pulse {
    0%, 100% { transform: scale(1); opacity: 0.4; }
    50% { transform: scale(1.4); opacity: 0; }
}
.delete-item-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--glass-brd);
    margin-bottom: 16px;
    color: var(--txt-0);
    font-size: 13px;
    font-weight: 600;
}
.delete-item-preview i { font-size: 18px; color: var(--acc); flex-shrink: 0; }
.danger-note {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(239,68,68,.06);
    border: 1px solid rgba(239,68,68,.2);
}
.danger-note-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: rgba(239,68,68,.12);
    display: grid; place-items: center;
    font-size: 18px;
    color: var(--danger);
    flex-shrink: 0;
}
.danger-note-content strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #fca5a5;
    margin-bottom: 3px;
}
.danger-note-content p {
    margin: 0;
    font-size: 12px;
    color: var(--txt-1);
    line-height: 1.5;
}

/* ---- Unsaved Modal ---- */
.modal-text {
    font-size: 13px;
    color: var(--txt-1);
    line-height: 1.6;
    margin: 0;
}

/* ---- Keyboard Hints ---- */
.keyboard-hints {
    margin-top: 20px;
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

/* ---- Skeleton ---- */
.skeleton-row.skel-var-2 { animation-delay: 0.1s; }

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .article-form-body { grid-template-columns: 1fr; }
    .article-form-side { position: static; }
    .articles-stats { grid-template-columns: repeat(2, 1fr); }
    .article-stat-trend { display: none; }
}
@media (max-width: 768px) {
    .articles-stats { grid-template-columns: 1fr; }
    .last-updated { display: none; }
    .keyboard-hints { display: none; }
    .toolbar-help { display: none; }
}
@media (max-width: 520px) {
    .article-stat-card { padding: 18px 20px; }
    .article-stat-icon { width: 42px; height: 42px; font-size: 19px; }
    .article-stat-info strong { font-size: 22px; }
    .articles-filter-pills { gap: 4px; padding: 3px; }
    .article-pill-btn { padding: 7px 10px; font-size: 11px; }
    .pill-divider { display: none; }
    .empty-features { flex-direction: column; gap: 8px; align-items: center; }
}
</style>