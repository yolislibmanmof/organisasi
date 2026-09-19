<?php
/**
 * ============================================================
 * MANAJEMEN EVENT (ADMIN) — ULTIMATE EDITION v5.9
 * Linimasa event dengan mini stats, search, filter,
 * dan modal CRUD yang terstruktur.
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
        <button class="btn btn-primary" id="btnAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event</span>
        </button>
    </div>
</section>

<!-- ========== MINI STATS ========== -->
<section class="event-stats">
    <article class="event-stat-card glass-card stat-upcoming">
        <div class="event-stat-icon">
            <i class="ph ph-calendar-check"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniUpcoming" data-count="0">0</strong>
            <span>Akan Datang</span>
        </div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="upcomingBar"></div>
        </div>
    </article>
    
    <article class="event-stat-card glass-card stat-ongoing">
        <div class="event-stat-icon">
            <i class="ph ph-broadcast"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniOngoing" data-count="0">0</strong>
            <span>Berlangsung Hari Ini</span>
        </div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="ongoingBar"></div>
        </div>
    </article>
    
    <article class="event-stat-card glass-card stat-done">
        <div class="event-stat-icon">
            <i class="ph ph-check-circle"></i>
        </div>
        <div class="event-stat-info">
            <strong id="miniDone" data-count="0">0</strong>
            <span>Selesai</span>
        </div>
        <div class="event-stat-bar">
            <div class="event-stat-bar-fill" id="doneBar"></div>
        </div>
    </article>
</section>

<!-- ========== TIMELINE PANEL ========== -->
<section class="glass-card event-panel">
    <!-- Toolbar -->
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="eventSearch" placeholder="Cari nama event atau lokasi…">
            <button class="search-clear" id="eventSearchClear" style="display:none" aria-label="Hapus pencarian">
                <i class="ph ph-x"></i>
            </button>
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="eventInfo">Memuat...</span>
            <button class="icon-btn" id="btnEventRefresh" title="Segarkan data">
                <i class="ph ph-arrows-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="event-filter-pills">
        <button class="event-pill-btn active" data-status="all">
            <i class="ph ph-list"></i> Semua
        </button>
        <button class="event-pill-btn" data-status="upcoming">
            <i class="ph ph-calendar-plus"></i> Akan Datang
        </button>
        <button class="event-pill-btn" data-status="ongoing">
            <i class="ph ph-broadcast"></i> Berlangsung
        </button>
        <button class="event-pill-btn" data-status="done">
            <i class="ph ph-check-circle"></i> Selesai
        </button>
    </div>

    <!-- Timeline -->
    <div class="timeline" id="eventTimeline">
        <!-- Skeleton loading -->
        <div class="timeline-item skeleton-timeline">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:30px;height:18px"></div>
                <div class="skel" style="width:40px;height:10px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 20px">
                <div class="skel" style="width:60%;height:16px;margin-bottom:10px"></div>
                <div class="skel" style="width:80%;height:12px;margin-bottom:8px"></div>
                <div class="skel" style="width:50%;height:12px"></div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:30px;height:18px"></div>
                <div class="skel" style="width:40px;height:10px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 20px">
                <div class="skel" style="width:70%;height:16px;margin-bottom:10px"></div>
                <div class="skel" style="width:75%;height:12px;margin-bottom:8px"></div>
                <div class="skel" style="width:45%;height:12px"></div>
            </div>
        </div>
        <div class="timeline-item skeleton-timeline">
            <div class="timeline-node skeleton-node">
                <div class="skel" style="width:30px;height:18px"></div>
                <div class="skel" style="width:40px;height:10px;margin-top:4px"></div>
            </div>
            <div class="timeline-card glass-card" style="padding:18px 20px">
                <div class="skel" style="width:55%;height:16px;margin-bottom:10px"></div>
                <div class="skel" style="width:85%;height:12px;margin-bottom:8px"></div>
                <div class="skel" style="width:60%;height:12px"></div>
            </div>
        </div>
    </div>

    <!-- Empty state -->
    <div class="empty-rich" id="emptyEvents" style="display:none;">
        <div class="empty-illustration">
            <i class="ph ph-calendar-blank"></i>
            <span class="empty-spark"></span>
        </div>
        <h3 id="emptyEventTitle">Belum Ada Event</h3>
        <p id="emptyEventText">Jadwalkan kegiatan pertama organisasi Anda dan pantau linimasanya di sini.</p>
        <button class="btn btn-primary" id="emptyAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event Pertama</span>
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

<!-- ========== MODAL FORM EVENT ========== -->
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
                            <span class="char-counter" id="eDescCounter">0 / 1000</span>
                        </div>
                    </label>
                </div>

                <!-- Section: Status Preview -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="ph ph-eye"></i> Pratinjau Status
                    </div>
                    <div class="event-status-preview" id="eventStatusPreview">
                        <div class="event-status-info">
                            <i class="ph ph-calendar-check"></i>
                            <span>Status akan otomatis ditentukan berdasarkan tanggal event.</span>
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

<!-- ========== MODAL HAPUS ========== -->
<div class="modal-backdrop" id="eventDeleteModal" role="dialog" aria-labelledby="deleteTitle">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3 id="deleteTitle">Hapus Event?</h3>
                <p class="modal-sub">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body">
            <p class="modal-text" id="eventDeleteText"></p>
            <div class="danger-note">
                <i class="ph ph-warning"></i>
                <span>Event akan dihapus dari linimasa dan tidak dapat dipulihkan.</span>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal>
                <span class="btn-text">Batal</span>
            </button>
            <button type="button" class="btn btn-danger" id="btnConfirmEventDelete">
                <i class="ph ph-trash"></i>
                <span class="btn-text">Ya, Hapus</span>
            </button>
        </div>
    </div>
</div>

<!-- ========== TOAST ZONE ========== -->
<div class="toast-zone" id="toastZone"></div>

<!-- ========== SCRIPTS ========== -->
<script src="<?= asset('js/events.js') ?>"></script>

<!-- ========== STYLING ========== -->
<style>
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
.event-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    transform: translate(30%, -30%);
    pointer-events: none;
    transition: transform 0.5s;
}
.event-stat-card:hover::before { transform: translate(30%, -30%) scale(1.2); }

.stat-upcoming::before { background: radial-gradient(circle, rgba(99,102,241,.2), transparent 70%); }
.stat-ongoing::before { background: radial-gradient(circle, rgba(245,158,11,.2), transparent 70%); }
.stat-done::before { background: radial-gradient(circle, rgba(16,185,129,.2), transparent 70%); }

.event-stat-icon {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 22px;
    color: #fff;
    box-shadow: 0 8px 20px rgba(0,0,0,.25);
}
.stat-upcoming .event-stat-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.stat-ongoing .event-stat-icon { background: linear-gradient(135deg, #f59e0b, #f97316); }
.stat-done .event-stat-icon { background: linear-gradient(135deg, #10b981, #34d399); }

.event-stat-info {
    flex: 1;
    min-width: 0;
}
.event-stat-info strong {
    display: block;
    font-size: 28px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.5px;
}
.event-stat-info span {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--txt-1);
    margin-top: 2px;
}

/* Progress bar */
.event-stat-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: rgba(255,255,255,.05);
    overflow: hidden;
}
.event-stat-bar-fill {
    height: 100%;
    width: 0%;
    border-radius: 0 2px 2px 0;
    transition: width 0.8s cubic-bezier(.22,1,.36,1);
}
.stat-upcoming .event-stat-bar-fill { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.stat-ongoing .event-stat-bar-fill { background: linear-gradient(90deg, #f59e0b, #f97316); }
.stat-done .event-stat-bar-fill { background: linear-gradient(90deg, #10b981, #34d399); }

/* ---- Filter Pills ---- */
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
    padding: 10px 18px;
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

/* ---- Skeleton Timeline ---- */
.skeleton-timeline {
    opacity: 0.7;
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
}

/* ---- Status Preview ---- */
.event-status-preview {
    padding: 16px 20px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--glass-brd);
}
.event-status-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--txt-1);
}
.event-status-info i {
    font-size: 20px;
    color: var(--acc);
}
.event-status-preview.status-upcoming .event-status-info {
    color: #67e8f9;
}
.event-status-preview.status-upcoming .event-status-info i {
    color: #22d3ee;
}
.event-status-preview.status-ongoing .event-status-info {
    color: #fcd34d;
}
.event-status-preview.status-ongoing .event-status-info i {
    color: var(--warn);
}
.event-status-preview.status-done .event-status-info {
    color: #6ee7b7;
}
.event-status-preview.status-done .event-status-info i {
    color: var(--ok);
}

/* ---- Form Section ---- */
.form-section {
    margin-bottom: 24px;
}
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
.form-section-title i {
    font-size: 15px;
    color: var(--acc);
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

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .event-stats {
        grid-template-columns: 1fr;
    }
    .event-filter-pills {
        margin: 12px 16px;
    }
}
</style>

<script>
/* ---- Character counter ---- */
(() => {
    const descField = document.getElementById('eDesc');
    const descCounter = document.getElementById('eDescCounter');
    
    if (descField && descCounter) {
        descField.addEventListener('input', () => {
            const len = descField.value.length;
            descCounter.textContent = `${len} / 1000`;
            descCounter.className = 'char-counter';
            if (len > 900) descCounter.classList.add('warn');
            if (len > 950) descCounter.classList.add('danger');
        });
    }
})();

/* ---- Status preview ---- */
(() => {
    const dateField = document.getElementById('eDate');
    const preview = document.getElementById('eventStatusPreview');
    const info = preview?.querySelector('.event-status-info');
    
    if (dateField && preview && info) {
        dateField.addEventListener('change', () => {
            const eventDate = new Date(dateField.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            preview.className = 'event-status-preview';
            
            if (!dateField.value) {
                info.innerHTML = '<i class="ph ph-calendar-check"></i><span>Status akan otomatis ditentukan berdasarkan tanggal event.</span>';
                return;
            }
            
            const diffDays = Math.ceil((eventDate - today) / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) {
                preview.classList.add('status-ongoing');
                info.innerHTML = '<i class="ph ph-broadcast"></i><span><strong>Berlangsung hari ini</strong> — Event akan muncul di kategori "Berlangsung".</span>';
            } else if (diffDays > 0) {
                preview.classList.add('status-upcoming');
                info.innerHTML = `<i class="ph ph-calendar-plus"></i><span><strong>Akan datang</strong> — ${diffDays} hari lagi dari hari ini.</span>`;
            } else {
                preview.classList.add('status-done');
                info.innerHTML = '<i class="ph ph-check-circle"></i><span><strong>Selesai</strong> — Event ini sudah lewat dan akan masuk arsip.</span>';
            }
        });
    }
})();

/* ---- Search clear button ---- */
(() => {
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
})();
</script>