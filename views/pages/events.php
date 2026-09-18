<!-- File: views/pages/events.php -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Modul Kegiatan</div>
        <h2 class="page-title">Event & Kegiatan</h2>
        <p class="page-sub">Linimasa seluruh kegiatan organisasi, dari yang telah lalu hingga yang akan datang.</p>
    </div>
    <div class="page-head-actions">
        <button class="btn btn-primary" id="btnAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event</span>
        </button>
    </div>
</section>

<section class="mini-stats">
    <article class="mini-stat glass-card">
        <i class="ph ph-calendar-check"></i>
        <div><strong id="miniUpcoming">0</strong><span>Akan Datang</span></div>
    </article>
    <article class="mini-stat glass-card">
        <i class="ph ph-broadcast"></i>
        <div><strong id="miniOngoing">0</strong><span>Berlangsung Hari Ini</span></div>
    </article>
    <article class="mini-stat glass-card">
        <i class="ph ph-check-circle"></i>
        <div><strong id="miniDone">0</strong><span>Selesai</span></div>
    </article>
</section>

<section class="glass-card event-panel">
    <div class="table-tools">
        <div class="search-wrap">
            <i class="ph ph-magnifying-glass"></i>
            <input type="text" id="eventSearch" placeholder="Cari nama event atau lokasi…">
        </div>
        <div class="table-tools-right">
            <span class="table-info" id="eventInfo"></span>
            <button class="icon-btn" id="btnEventRefresh" title="Segarkan"><i class="ph ph-arrows-clockwise"></i></button>
        </div>
    </div>

    <div class="timeline" id="eventTimeline"></div>

    <div class="empty-rich" id="emptyEvents" style="display:none;">
        <div class="empty-illustration"><i class="ph ph-calendar-blank"></i><span class="empty-spark"></span></div>
        <h3 id="emptyEventTitle">Belum Ada Event</h3>
        <p id="emptyEventText">Jadwalkan kegiatan pertama organisasi Anda dan pantau linimasanya di sini.</p>
        <button class="btn btn-primary" id="emptyAddEvent">
            <i class="ph ph-calendar-plus"></i>
            <span class="btn-text">Buat Event Pertama</span>
        </button>
    </div>

    <div class="table-foot">
        <span id="eventPageInfo"></span>
        <div class="pager">
            <button class="pager-btn" id="eventPrev"><i class="ph ph-caret-left"></i></button>
            <span class="pager-current" id="eventPagerCur">1 / 1</span>
            <button class="pager-btn" id="eventNext"><i class="ph ph-caret-right"></i></button>
        </div>
    </div>
</section>

<!-- Modal Form Event -->
<div class="modal-backdrop" id="eventModal">
    <div class="modal glass-card modal-lg">
        <div class="modal-head">
            <div class="modal-head-title">
                <span class="modal-icon grad-2"><i class="ph ph-calendar-plus"></i></span>
                <div>
                    <h3 id="eventModalTitle">Buat Event Baru</h3>
                    <p class="modal-sub">Lengkapi detail kegiatan di bawah ini.</p>
                </div>
            </div>
            <button type="button" class="modal-close" data-close-modal><i class="ph ph-x"></i></button>
        </div>
        <form id="eventForm">
            <?= csrf_field() ?>
            <input type="hidden" id="eId" value="">
            <div class="modal-body">
                <label class="field">
                    <span class="field-label">Nama Event <em>*</em></span>
                    <input type="text" name="title" id="eTitle" placeholder="Contoh: Musyawarah Anggota Tahunan" required>
                    <span class="field-error" data-error="title"></span>
                </label>
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
                <label class="field">
                    <span class="field-label">Lokasi</span>
                    <input type="text" name="location" id="eLocation" placeholder="Contoh: Aula Desa Cianjur">
                </label>
                <label class="field">
                    <span class="field-label">Deskripsi</span>
                    <textarea name="description" id="eDesc" rows="3" placeholder="Ringkasan agenda kegiatan…"></textarea>
                </label>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
                <button type="submit" class="btn btn-primary"><span class="btn-text">Simpan Event</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-backdrop" id="eventDeleteModal">
    <div class="modal modal-sm glass-card">
        <div class="modal-head modal-head-danger">
            <span class="modal-icon danger-icon"><i class="ph ph-warning-circle"></i></span>
            <div>
                <h3>Hapus Event?</h3>
                <p class="modal-sub">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
        </div>
        <div class="modal-body"><p class="modal-text" id="eventDeleteText"></p></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" data-close-modal><span class="btn-text">Batal</span></button>
            <button type="button" class="btn btn-danger" id="btnConfirmEventDelete"><span class="btn-text">Ya, Hapus</span></button>
        </div>
    </div>
</div>

<div class="toast-zone" id="toastZone"></div>
<script src="<?= asset('js/events.js') ?>"></script>