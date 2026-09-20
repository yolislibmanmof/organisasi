<?php
/**
 * ============================================================
 * MANAJEMEN EVENT (ADMIN) — ULTIMATE EDITION v7.0
 * Linimasa event dengan stats animasi, filter pills ber-count,
 * modal CRUD kaya, dan self-contained enhancer.
 * ============================================================
 */
?>

<!-- ========== PAGE HEADER ========== -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Modul Kegiatan</div>
        <h2 class="page-title">
            <i class="ph ph-calendar-blank" style="color:var(--acc);margin-right:8px"></i>
            Event & Kegiatan
        </h2>
        <p class="page-sub">Linimasa seluruh kegiatan organisasi, dari yang telah lalu hingga yang akan datang.</p>
    </div>
    <div class="page-head-actions">
        <span class="last-updated" id="eventLastUpdated" title="Waktu sinkronisasi terakhir">
            <i class="ph ph-clock"></i>
            <span>Baru saja</span>
        </span>
        <button class="btn btn-primary" id="btnAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event</span>
        </button>
    </div>
</section>

<!-- ========== MINI STATS (ANIMATED) ========== -->
<section class="event-stats">
    <article class="event-stat-card glass-card stat-upcoming">
        <div class="event-stat-glow"></div>
        <div class="event-stat-icon">
            <i class="ph ph-calendar-check"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniUpcoming" data-count="0">0</strong>
            <span>Akan Datang</span>
        </div>
        <div class="event-stat-trend trend-up"><i class="ph ph-trend-up"></i></div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="upcomingBar" style="width:0%"></div>
        </div>
    </article>
    
    <article class="event-stat-card glass-card stat-ongoing">
        <div class="event-stat-glow"></div>
        <div class="event-stat-icon">
            <i class="ph ph-broadcast"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniOngoing" data-count="0">0</strong>
            <span>Berlangsung Hari Ini</span>
        </div>
        <div class="event-stat-trend trend-live"><i class="ph ph-broadcast"></i></div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="ongoingBar" style="width:0%"></div>
        </div>
    </article>
    
    <article class="event-stat-card glass-card stat-done">
        <div class="event-stat-glow"></div>
        <div class="event-stat-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniDone" data-count="0">0</strong>
            <span>Selesai</span>
        </div>
        <div class="event-stat-trend trend-ok"><i class="ph ph-check"></i></div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="doneBar" style="width:0%"></div>
        </div>
    </article>
</section>

<!-- ========== TIMELINE PANEL ========== -->
<section class="glass-card event-panel">
    <!-- Toolbar -->
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="eventSearch" placeholder="Cari nama event atau lokasi…" aria-label="Cari event">
            <button class="search-clear" id="eventSearchClear" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="eventInfo" aria-live="polite">Memuat...</span>
            <button class="icon-btn" id="btnEventRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills (dengan count) -->
    <div class="event-filter-pills" role="tablist" aria-label="Filter status event">
        <button class="event-pill-btn active" data-status="all" role="tab" aria-selected="true">
            <i class="ph ph-list"></i>
            Semua
            <span class="pill-count" id="pillCountAll">0</span>
        </button>
        <button class="event-pill-btn" data-status="upcoming" role="tab" aria-selected="false">
            <i class="ph ph-calendar-plus"></i>
            Akan Datang
            <span class="pill-count count-upcoming" id="pillCountUpcoming">0</span>
        </button>
        <button class="event-pill-btn" data-status="ongoing" role="tab" aria-selected="false">
            <i class="ph ph-broadcast"></i>
            Berlangsung
            <span class="pill-count count-ongoing" id="pillCountOngoing">0</span>
        </button>
        <button class="event-pill-btn" data-status="done" role="tab" aria-selected="false">
            <i class="ph ph-check-circle"></i>
            Selesai
            <span class="pill-count count-done" id="pillCountDone">0</span>
        </button>
    </div>

    <!-- Timeline -->
    <div class="timeline" id="eventTimeline">
        <!-- Skeleton kaya (5 item variasi) -->
        <div class="timeline-item skeleton-timeline skel-var-1">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:32px;height:20px"></div>
                <div class="skel" style="width:42px;height:11px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 22px">
                <div class="skel-row">
                    <div class="skel" style="width:65%;height:17px"></div>
                    <div class="skel" style="width:70px;height:22px;border-radius:99px"></div>
                </div>
                <div class="skel" style="width:85%;height:12px;margin:10px 0 8px"></div>
                <div class="skel-row">
                    <div class="skel" style="width:45%;height:12px"></div>
                    <div class="skel" style="width:35%;height:12px"></div>
                </div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline skel-var-2">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:30px;height:20px"></div>
                <div class="skel" style="width:40px;height:11px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 22px">
                <div class="skel-row">
                    <div class="skel" style="width:72%;height:17px"></div>
                    <div class="skel" style="width:80px;height:22px;border-radius:99px"></div>
                </div>
                <div class="skel" style="width:78%;height:12px;margin:10px 0 8px"></div>
                <div class="skel-row">
                    <div class="skel" style="width:50%;height:12px"></div>
                    <div class="skel" style="width:40%;height:12px"></div>
                </div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline skel-var-1">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:28px;height:20px"></div>
                <div class="skel" style="width:38px;height:11px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 22px">
                <div class="skel-row">
                    <div class="skel" style="width:58%;height:17px"></div>
                    <div class="skel" style="width:65px;height:22px;border-radius:99px"></div>
                </div>
                <div class="skel" style="width:82%;height:12px;margin:10px 0 8px"></div>
                <div class="skel-row">
                    <div class="skel" style="width:48%;height:12px"></div>
                    <div class="skel" style="width:38%;height:12px"></div>
                </div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline skel-var-2">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:31px;height:20px"></div>
                <div class="skel" style="width:41px;height:11px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 22px">
                <div class="skel-row">
                    <div class="skel" style="width:68%;height:17px"></div>
                    <div class="skel" style="width:75px;height:22px;border-radius:99px"></div>
                </div>
                <div class="skel" style="width:80%;height:12px;margin:10px 0 8px"></div>
                <div class="skel-row">
                    <div class="skel" style="width:52%;height:12px"></div>
                    <div class="skel" style="width:42%;height:12px"></div>
                </div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline skel-var-1">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:29px;height:20px"></div>
                <div class="skel" style="width:39px;height:11px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 22px">
                <div class="skel-row">
                    <div class="skel" style="width:62%;height:17px"></div>
                    <div class="skel" style="width:72px;height:22px;border-radius:99px"></div>
                </div>
                <div class="skel" style="width:88%;height:12px;margin:10px 0 8px"></div>
                <div class="skel-row">
                    <div class="skel" style="width:46%;height:12px"></div>
                    <div class="skel" style="width:36%;height:12px"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty state (belum ada event) -->
    <div class="empty-rich" id="emptyEvents" style="display:none;">
        <div class="empty-illustration">
            <div class="empty-orb empty-orb-1"></div>
            <div class="empty-orb empty-orb-2"></div>
            <i class="ph ph-calendar-blank"></i>
            <span class="empty-spark"></span>
        </div>
        <h3 id="emptyEventTitle">Belum Ada Event</h3>
        <p id="emptyEventText">Jadwalkan kegiatan pertama organisasi Anda dan pantau linimasanya di sini.</p>
        <div class="empty-features">
            <span><i class="ph ph-check-circle"></i> Jadwal fleksibel</span>
            <span><i class="ph ph-check-circle"></i> Tampil di situs publik</span>
            <span><i class="ph ph-check-circle"></i> Edit kapan saja</span>
        </div>
        <button class="btn btn-primary" id="emptyAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event Pertama</span>
        </button>
    </div>

    <!-- No-match state (filter/search kosong) -->
    <div class="empty-rich event-nomatch" id="eventNoMatch" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-magnifying-glass"></i>
        </div>
        <h3>Tidak Ada Event</h3>
        <p>Tidak ada event yang cocok dengan filter atau kata kunci saat ini.</p>
        <button class="btn btn-ghost btn-sm" id="btnEventReset">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <span class="btn-text">Reset Filter</span>
        </button>
    </div>

    <!-- Pagination -->
    <div class="table-foot">
        <span id="eventPageInfo">Memuat data...</span>
        <div class="pager">
            <button class="pager-btn icon-btn" id="eventPrev" disabled aria-label="Halaman sebelumnya">
                <i class="ph ph-caret-left"></i>
            </button>
            <span class="pager-current" id="eventPagerCur">1 / 1</span>
            <button class="pager-btn icon-btn" id="eventNext" disabled aria-label="Halaman berikutnya">
                <i class="ph ph-caret-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- ========== MODAL FORM EVENT (ENHANCED) ========== -->
<div class="modal-backdrop" id="eventModal" role="dialog" aria-labelledby="eventModalTitle">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-calendar-plus"></i></span>
                <div>
                    <h3 id="eventModalTitle">Buat Event Baru</h3>
                    <p class="modal-sub">Lengkapi detail kegiatan di bawah ini.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal aria-label="Tutup">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <form id="eventForm">
            <?= csrf_field() ?>
            <input type="hidden" id="eId" value="">
            <div class="modal-body">
                <!-- Section: Informasi Dasar -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-info"></i> Informasi Dasar
                    </div>
                    <label class="field">
                        <span class="field-label">Nama Event <em>*</em></span>
                        <input type="text" name="title" id="eTitle" placeholder="Contoh: Musyawarah Anggota Tahunan" required>
                        <span class="field-error" data-error="title"></span>
                    </label>
                </div>

                <!-- Section: Jadwal -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-clock"></i> Jadwal
                    </div>
                    <div class="field-row">
                        <label class="field">
                            <span class="field-label">Tanggal <em>*</em></span>
                            <input type="date" name="event_date" id="eDate" required>
                            <span class="field-error" data-error="event_date"></span>
                        </label>
                        <label class="field">
                            <span class="field-label">Jam Mulai</span>
                            <input type="time" name="event_time" id="eTime">
                        </label>
                    </div>
                </div>

                <!-- Section: Lokasi & Deskripsi -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-map-pin"></i> Lokasi & Deskripsi
                    </div>
                    <label class="field">
                        <span class="field-label">Lokasi</span>
                        <input type="text" name="location" id="eLocation" placeholder="Contoh: Aula Desa Cianjur">
                    </label>
                    <label class="field">
                        <span class="field-label">Deskripsi</span>
                        <textarea name="description" id="eDesc" rows="4" placeholder="Ringkasan agenda kegiatan..." maxlength="1000"></textarea>
                        <div class="field-footer">
                            <small class="asset-hint">Opsional — deskripsi akan muncul di detail event.</small>
                            <div class="char-progress-wrap">
                                <span class="char-counter" id="eDescCounter">0 / 1000</span>
                                <div class="char-progress">
                                    <div class="char-progress-fill" id="eDescBar" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Section: Status Preview (Enhanced) -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-eye"></i> Pratinjau Status
                    </div>
                    <div class="event-status-preview" id="eventStatusPreview">
                        <div class="event-status-info">
                            <i class="ph ph-calendar-check"></i>
                            <div class="event-status-text">
                                <strong>Status akan otomatis ditentukan</strong>
                                <span>Pilih tanggal untuk melihat status event</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal>
                    <span class="btn-text">Batal</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-check"></i>
                    <span class="btn-text">Simpan Event</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========== MODAL HAPUS (ENHANCED) ========== -->
<div class="modal-backdrop" id="eventDeleteModal" role="dialog" aria-labelledby="deleteTitle">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <div class="danger-icon-wrap">
                <span class="danger-icon-pulse"></span>
                <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            </div>
            <div>
                <h3 id="deleteTitle">Hapus Event?</h3>
                <p class="modal-sub">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <div class="delete-item-preview" id="eventDeletePreview">
                <i class="ph ph-calendar-blank"></i>
                <span id="eventDeleteText">—</span>
            </div>
            <div class="danger-note">
                <div class="danger-note-icon"><i class="ph ph-warning"></i></div>
                <div class="danger-note-content">
                    <strong>Peringatan</strong>
                    <p>Event akan dihapus dari linimasa dan situs publik. Data yang sudah dihapus tidak dapat dipulihkan.</p>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmEventDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus Permanen</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== KEYBOARD HINTS ========== -->
<div class="keyboard-hints" aria-label="Pintasan keyboard">
    <span class="kbd-item" id="kbdNew"><kbd>N</kbd> Buat Event</span>
    <span class="kbd-item" id="kbdRefresh"><kbd>R</kbd> Segarkan</span>
    <span class="kbd-item" id="kbdSearch"><kbd>/</kbd> Cari</span>
    <span class="kbd-item"><kbd>Esc</kbd> Tutup Modal</span>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/events.js') ?>"></script>

<!-- ========== ENHANCER v7.0 (self-contained, tidak mengubah events.js) ========== -->
<script>
(function(){
    'use strict';

    /* ---- Helper: Format tanggal Indonesia ---- */
    const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const HARI  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    
    function formatTanggalID(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '';
        return HARI[d.getDay()] + ', ' + d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + d.getFullYear();
    }

    /* ---- Counter animation ---- */
    function animate(el, to) {
        if (!el) return;
        const from = parseInt(el.dataset.v || el.textContent || '0', 10) || 0;
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

    /* ---- Update stats + pill counts dari timeline cards ---- */
    function syncStats() {
        const timeline = document.getElementById('eventTimeline');
        if (!timeline) return;

        const cards = timeline.querySelectorAll('.timeline-item:not(.skeleton-timeline)');
        let u = 0, o = 0, d = 0;
        cards.forEach(card => {
            const s = card.dataset.status;
            if (s === 'upcoming') u++;
            else if (s === 'ongoing') o++;
            else if (s === 'done') d++;
        });
        const total = u + o + d;

        // Animate numbers
        animate(document.getElementById('miniUpcoming'), u);
        animate(document.getElementById('miniOngoing'), o);
        animate(document.getElementById('miniDone'), d);

        // Progress bars (percentage of total)
        const upBar = document.getElementById('upcomingBar');
        const onBar = document.getElementById('ongoingBar');
        const dnBar = document.getElementById('doneBar');
        if (upBar) upBar.style.width = (total ? (u / total * 100) : 0) + '%';
        if (onBar) onBar.style.width = (total ? (o / total * 100) : 0) + '%';
        if (dnBar) dnBar.style.width = (total ? (d / total * 100) : 0) + '%';

        // Pill counts
        const pillAll = document.getElementById('pillCountAll');
        const pillU = document.getElementById('pillCountUpcoming');
        const pillO = document.getElementById('pillCountOngoing');
        const pillD = document.getElementById('pillCountDone');
        if (pillAll) pillAll.textContent = total;
        if (pillU) pillU.textContent = u;
        if (pillO) pillO.textContent = o;
        if (pillD) pillD.textContent = d;

        // Update last updated
        const lastEl = document.getElementById('eventLastUpdated');
        if (lastEl) {
            const span = lastEl.querySelector('span');
            if (span) {
                const now = new Date();
                span.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                lastEl.title = 'Terakhir diperbarui: ' + now.toLocaleString('id-ID');
            }
        }
    }

    /* ---- Character counter dengan progress bar ---- */
    const eDesc = document.getElementById('eDesc');
    const eDescCounter = document.getElementById('eDescCounter');
    const eDescBar = document.getElementById('eDescBar');
    
    if (eDesc && eDescCounter && eDescBar) {
        eDesc.addEventListener('input', () => {
            const len = eDesc.value.length;
            const max = 1000;
            const pct = Math.min(100, (len / max) * 100);
            eDescCounter.textContent = len + ' / ' + max;
            eDescBar.style.width = pct + '%';
            
            eDescCounter.className = 'char-counter';
            eDescBar.className = 'char-progress-fill';
            if (len > max * 0.85) { eDescCounter.classList.add('warn'); eDescBar.classList.add('warn'); }
            if (len > max * 0.95) { eDescCounter.classList.add('danger'); eDescBar.classList.add('danger'); }
        });
    }

    /* ---- Status preview dengan format tanggal Indonesia + countdown ---- */
    const eDate = document.getElementById('eDate');
    const eTime = document.getElementById('eTime');
    const preview = document.getElementById('eventStatusPreview');
    
    function updatePreview() {
        if (!eDate || !preview) return;
        const info = preview.querySelector('.event-status-info');
        if (!info) return;
        
        preview.className = 'event-status-preview';
        
        if (!eDate.value) {
            info.innerHTML = '<i class="ph ph-calendar-check"></i>' +
                '<div class="event-status-text"><strong>Status akan otomatis ditentukan</strong>' +
                '<span>Pilih tanggal untuk melihat status event</span></div>';
            return;
        }
        
        const eventDate = new Date(eDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const diffDays = Math.ceil((eventDate - today) / (1000 * 60 * 60 * 24));
        const dateStr = formatTanggalID(eDate.value);
        const timeStr = eTime?.value ? ' pukul ' + eTime.value + ' WIB' : '';
        
        if (diffDays === 0) {
            preview.classList.add('status-ongoing');
            info.innerHTML = '<i class="ph ph-broadcast"></i>' +
                '<div class="event-status-text"><strong>Berlangsung Hari Ini</strong>' +
                '<span>' + dateStr + timeStr + '</span></div>';
        } else if (diffDays > 0) {
            preview.classList.add('status-upcoming');
            let countdown = '';
            if (diffDays === 1) countdown = 'Besok';
            else if (diffDays <= 7) countdown = diffDays + ' hari lagi';
            else if (diffDays <= 30) countdown = Math.floor(diffDays / 7) + ' minggu lagi';
            else countdown = Math.floor(diffDays / 30) + ' bulan lagi';
            
            info.innerHTML = '<i class="ph ph-calendar-plus"></i>' +
                '<div class="event-status-text"><strong>Akan Datang</strong>' +
                '<span>' + dateStr + timeStr + ' • ' + countdown + '</span></div>';
        } else {
            preview.classList.add('status-done');
            const absDays = Math.abs(diffDays);
            let past = '';
            if (absDays === 1) past = 'Kemarin';
            else if (absDays <= 7) past = absDays + ' hari lalu';
            else if (absDays <= 30) past = Math.floor(absDays / 7) + ' minggu lalu';
            else past = Math.floor(absDays / 30) + ' bulan lalu';
            
            info.innerHTML = '<i class="ph ph-check-circle"></i>' +
                '<div class="event-status-text"><strong>Selesai</strong>' +
                '<span>' + dateStr + timeStr + ' • ' + past + '</span></div>';
        }
    }
    
    if (eDate) eDate.addEventListener('change', updatePreview);
    if (eTime) eTime.addEventListener('change', updatePreview);

    /* ---- Search clear button ---- */
    const searchInput = document.getElementById('eventSearch');
    const clearBtn = document.getElementById('eventSearchClear');
    
    if (searchInput && clearBtn) {
        searchInput.addEventListener('input', () => {
            clearBtn.style.display = searchInput.value ? 'grid' : 'none';
        });
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }

    /* ---- Reset filter button (no-match) ---- */
    document.getElementById('btnEventReset')?.addEventListener('click', () => {
        // Reset search
        if (searchInput) {
            searchInput.value = '';
            if (clearBtn) clearBtn.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
        }
        // Reset pills ke "Semua"
        document.querySelectorAll('.event-pill-btn').forEach(b => {
            const on = b.dataset.status === 'all';
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', on ? 'true' : 'false');
            if (on) b.click();
        });
    });

    /* ---- Keyboard shortcuts ---- */
    document.getElementById('kbdNew')?.addEventListener('click', () => {
        document.getElementById('btnAddEvent')?.click();
    });
    document.getElementById('kbdRefresh')?.addEventListener('click', () => {
        document.getElementById('btnEventRefresh')?.click();
    });
    document.getElementById('kbdSearch')?.addEventListener('click', () => {
        searchInput?.focus();
    });

    document.addEventListener('keydown', (e) => {
        const tag = document.activeElement.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
        
        // N = new event
        if (e.key.toLowerCase() === 'n' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnAddEvent')?.click();
        }
        // R = refresh
        if (e.key.toLowerCase() === 'r' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            document.getElementById('btnEventRefresh')?.click();
        }
        // / = search
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
            e.preventDefault();
            searchInput?.focus();
        }
    });

    /* ---- MutationObserver untuk sync stats saat events.js re-render ---- */
    const timeline = document.getElementById('eventTimeline');
    if (timeline) {
        new MutationObserver(() => {
            syncStats();
            // Check no-match
            const cards = timeline.querySelectorAll('.timeline-item:not(.skeleton-timeline)');
            const visible = Array.from(cards).filter(c => c.style.display !== 'none').length;
            const noMatch = document.getElementById('eventNoMatch');
            if (noMatch) {
                noMatch.style.display = (cards.length > 0 && visible === 0) ? 'flex' : 'none';
            }
        }).observe(timeline, { childList: true, subtree: true, attributes: true, attributeFilter: ['style'] });
    }

    /* ---- Initial sync ---- */
    syncStats();
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

/* ---- Event Stats ---- */
.event-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.event-stat-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 24px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(.22,1,.36,1);
}
.event-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 50px rgba(2,6,23,.4);
}
.event-stat-glow {
    position: absolute;
    top: -30px; right: -30px;
    width: 140px; height: 140px;
    border-radius: 50%;
    pointer-events: none;
    transition: transform .5s;
    opacity: 0.8;
}
.event-stat-card:hover .event-stat-glow { transform: scale(1.25); }

.stat-upcoming .event-stat-glow { background: radial-gradient(circle, rgba(99,102,241,.25), transparent 70%); }
.stat-ongoing .event-stat-glow  { background: radial-gradient(circle, rgba(245,158,11,.25), transparent 70%); }
.stat-done .event-stat-glow     { background: radial-gradient(circle, rgba(16,185,129,.25), transparent 70%); }

.event-stat-icon {
    width: 52px; height: 52px;
    flex-shrink: 0;
    border-radius: 14px;
    display: grid; place-items: center;
    font-size: 22px; color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
    position: relative; z-index: 1;
    transition: transform .3s;
}
.event-stat-card:hover .event-stat-icon { transform: scale(1.08) rotate(-5deg); }
.stat-upcoming .event-stat-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-ongoing .event-stat-icon  { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-done .event-stat-icon     { background: linear-gradient(135deg, #10b981, #34d399); }

.event-stat-info { flex: 1; min-width: 0; position: relative; z-index: 1; }
.event-stat-info strong {
    display: block;
    font-size: 28px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
    line-height: 1.1;
}
.event-stat-info span {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
    margin-top: 3px;
}

/* Trend icon */
.event-stat-trend {
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
.event-stat-trend.trend-up   { color: #a5b4fc; background: rgba(99,102,241,.1); border-color: rgba(99,102,241,.25); }
.event-stat-trend.trend-live { color: #fcd34d; background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.25); animation: pulse-soft 2s infinite; }
.event-stat-trend.trend-ok   { color: #6ee7b7; background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.25); }

@keyframes pulse-soft {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,.4); }
    50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); }
}

/* Progress bar */
.event-stat-bar {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4px;
    background: rgba(255,255,255,.05);
    overflow: hidden;
}
.event-stat-bar-fill {
    height: 100%;
    border-radius: 0 2px 2px 0;
    transition: width 0.8s cubic-bezier(.22,1,.36,1);
}
.stat-upcoming .event-stat-bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.stat-ongoing .event-stat-bar-fill  { background: linear-gradient(90deg, #f59e0b, #f97316); }
.stat-done .event-stat-bar-fill     { background: linear-gradient(90deg, #10b981, #34d399); }

/* ---- Filter Pills (dengan count) ---- */
.event-filter-pills {
    display: flex;
    gap: 8px;
    padding: 4px;
    margin: 16px 22px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    border-radius: 12px;
    overflow-x: auto;
}
.event-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    background: transparent;
    border: none;
    color: var(--txt-1);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.event-pill-btn i { font-size: 15px; }
.event-pill-btn:hover {
    color: var(--txt-0);
    background: rgba(255,255,255,.05);
}
.event-pill-btn.active {
    background: linear-gradient(135deg, var(--pri), var(--acc));
    color: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.3);
}
.pill-count {
    min-width: 22px;
    padding: 2px 7px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    font-size: 10px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    text-align: center;
    transition: all .2s;
}
.event-pill-btn.active .pill-count { background: rgba(255,255,255,.25); }
.pill-count.count-upcoming { background: rgba(99,102,241,.2); color: #a5b4fc; }
.pill-count.count-ongoing  { background: rgba(245,158,11,.2); color: #fcd34d; }
.pill-count.count-done     { background: rgba(16,185,129,.2); color: #6ee7b7; }
.event-pill-btn.active .pill-count.count-upcoming,
.event-pill-btn.active .pill-count.count-ongoing,
.event-pill-btn.active .pill-count.count-done {
    background: rgba(255,255,255,.25);
    color: #fff;
}

/* ---- Search Clear ---- */
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

/* ---- Skeleton Timeline (kaya) ---- */
.skeleton-timeline {
    opacity: 0.75;
}
.skeleton-timeline.skel-var-2 {
    animation-delay: 0.15s;
}
.skeleton-node {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 10px;
    animation: skeleton-pulse 1.5s ease-in-out infinite;
}
.timeline-card .skel-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

@keyframes skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* ---- Status Preview (Enhanced) ---- */
.event-status-preview {
    padding: 18px 22px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
    transition: all .3s;
}
.event-status-info {
    display: flex;
    align-items: center;
    gap: 14px;
}
.event-status-info > i {
    font-size: 24px;
    color: var(--acc);
    flex-shrink: 0;
}
.event-status-text { flex: 1; }
.event-status-text strong {
    display: block;
    font-size: 13.5px;
    font-weight: 800;
    color: var(--txt-0);
    margin-bottom: 2px;
}
.event-status-text span {
    display: block;
    font-size: 12px;
    color: var(--txt-1);
    line-height: 1.5;
}

.event-status-preview.status-upcoming {
    background: linear-gradient(135deg, rgba(34,211,238,.08), rgba(99,102,241,.05));
    border-color: rgba(34,211,238,.3);
}
.event-status-preview.status-upcoming .event-status-info > i { color: #22d3ee; }
.event-status-preview.status-upcoming .event-status-text strong { color: #67e8f9; }

.event-status-preview.status-ongoing {
    background: linear-gradient(135deg, rgba(245,158,11,.08), rgba(249,115,22,.05));
    border-color: rgba(245,158,11,.3);
}
.event-status-preview.status-ongoing .event-status-info > i { color: var(--warn); }
.event-status-preview.status-ongoing .event-status-text strong { color: #fcd34d; }

.event-status-preview.status-done {
    background: linear-gradient(135deg, rgba(16,185,129,.08), rgba(52,211,153,.05));
    border-color: rgba(16,185,129,.3);
}
.event-status-preview.status-done .event-status-info > i { color: var(--ok); }
.event-status-preview.status-done .event-status-text strong { color: #6ee7b7; }

/* ---- Character Counter dengan Progress Bar ---- */
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
    min-width: 110px;
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
}
.char-progress-fill.warn   { background: linear-gradient(90deg, var(--warn), #fb923c); }
.char-progress-fill.danger { background: linear-gradient(90deg, var(--danger), #f87171); }
.char-counter {
    font-size: 11px;
    font-weight: 600;
    color: var(--txt-2);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    transition: color 0.2s;
}
.char-counter.warn   { color: var(--warn); }
.char-counter.danger { color: var(--danger); }

/* ---- Form Section ---- */
.form-section { margin-bottom: 24px; }
.form-section:last-child { margin-bottom: 0; }
.form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2);
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--glass-brd);
}
.form-section-title i { font-size: 15px; color: var(--acc); }

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

/* ---- Empty State Enhanced ---- */
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
.empty-rich h3 {
    font-size: 18px;
    font-weight: 800;
    margin-bottom: 8px;
    letter-spacing: -.3px;
}
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

/* ---- No Match State ---- */
.event-nomatch { padding: 40px 24px; }
.event-nomatch .empty-illustration {
    width: 80px; height: 80px;
    font-size: 32px;
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
    gap: 8px;
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
    padding: 2px 8px;
    border-radius: 5px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    font-family: inherit;
    font-size: 10.5px;
    font-weight: 800;
    color: var(--txt-0);
    box-shadow: 0 2px 0 rgba(0,0,0,.25);
}

/* ---- Responsive ---- */
@media (max-width: 980px) {
    .event-filter-pills { gap: 6px; padding: 3px; }
    .event-pill-btn { padding: 8px 12px; font-size: 12px; }
}
@media (max-width: 768px) {
    .event-stats { grid-template-columns: 1fr; }
    .event-filter-pills { margin: 12px 16px; }
    .event-stat-trend { display: none; }
    .keyboard-hints { display: none; }
    .last-updated { display: none; }
    .event-status-preview { padding: 14px 16px; }
    .event-status-text strong { font-size: 13px; }
    .event-status-text span { font-size: 11.5px; }
}
@media (max-width: 520px) {
    .event-stat-card { padding: 18px 20px; }
    .event-stat-icon { width: 46px; height: 46px; font-size: 20px; }
    .event-stat-info strong { font-size: 24px; }
    .empty-features { flex-direction: column; gap: 8px; align-items: center; }
}
</style>