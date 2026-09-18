<!-- File: views/pages/dashboard.php (FINAL - TERINTEGRASI TAHAP 4) -->
<section class="page-head">
    <div class="page-head-info">
        <div class="page-eyebrow">Pusat Kendali</div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-sub">Ringkasan aktivitas dan pertumbuhan organisasi Anda.</p>
    </div>
    <div class="page-head-actions">
        <span class="live-indicator">
            <span class="live-dot"></span>
            Data Real-time
        </span>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card glass-card">
        <div class="stat-icon grad-1"><i class="ph ph-users-three"></i></div>
        <div class="stat-body">
            <strong class="stat-num" data-count="<?= (int) $totalMembers ?>">0</strong>
            <span class="stat-label">Total Anggota</span>
            <span class="stat-trend up"><i class="ph ph-trend-up"></i> Aktif</span>
        </div>
    </article>
    <article class="stat-card glass-card">
        <div class="stat-icon grad-2"><i class="ph ph-user-circle"></i></div>
        <div class="stat-body">
            <strong class="stat-num" data-count="<?= (int) $totalUsers ?>">0</strong>
            <span class="stat-label">Total Pengguna</span>
            <span class="stat-trend neutral"><i class="ph ph-minus"></i> Stabil</span>
        </div>
    </article>
    <article class="stat-card glass-card">
        <div class="stat-icon grad-3"><i class="ph ph-shield-check"></i></div>
        <div class="stat-body">
            <strong class="stat-num" data-count="<?= (int) $activeUsers ?>">0</strong>
            <span class="stat-label">Akun Aktif</span>
            <span class="stat-trend up"><i class="ph ph-trend-up"></i> <?= round(($activeUsers / max(1, $totalUsers)) * 100) ?>%</span>
        </div>
    </article>
    <article class="stat-card glass-card">
        <div class="stat-icon grad-4"><i class="ph ph-calendar-blank"></i></div>
        <div class="stat-body">
            <strong class="stat-num" data-count="<?= (int) ($eventThisMonth ?? 0) ?>">0</strong>
            <span class="stat-label">Event Bulan Ini</span>
            <span class="stat-trend up"><i class="ph ph-broadcast"></i> Live</span>
        </div>
    </article>
</section>

<section class="glass-card chart-card">
    <div class="chart-head">
        <div>
            <h3>Pertumbuhan Anggota</h3>
            <p class="chart-sub">Tren pendaftaran dalam 6 bulan terakhir</p>
        </div>
        <div class="chart-head-right">
            <span class="chart-badge"><i class="ph ph-chart-line-up"></i> 6 bulan terakhir</span>
            <div class="chart-legend">
                <span class="legend-dot"></span>
                <span>Pendaftar Baru</span>
            </div>
        </div>
    </div>
    <div id="chartRegistrations" class="chart-area"></div>
</section>

<section class="dashboard-grid-two">
    <section class="glass-card activity-card">
        <div class="card-head">
            <h3><i class="ph ph-clock-countdown"></i> Aktivitas Terkini</h3>
            <a href="<?= url('members') ?>" class="link-soft">Lihat semua <i class="ph ph-arrow-right"></i></a>
        </div>
        <ul class="activity-list" id="activityList">
            <li class="activity-empty">
                <i class="ph ph-clock-countdown"></i>
                <span>Belum ada aktivitas terbaru yang tercatat.</span>
            </li>
        </ul>
    </section>

    <section class="glass-card welcome-card">
        <div class="welcome-inner">
            <div class="welcome-emoji">👋</div>
            <h2>Selamat datang kembali, <?= e($user['name']) ?>!</h2>
            <p>
                Panel kendali <strong><?= e(APP_NAME) ?></strong> siap digunakan. Seluruh angka statistik
                dan grafik dibaca langsung dari database <em>organisasi</em> secara real-time.
            </p>
            <div class="welcome-quick">
                <a href="<?= url('members') ?>" class="quick-link">
                    <i class="ph ph-users-three"></i>
                    <span>Kelola Anggota</span>
                </a>
                <a href="<?= url('events') ?>" class="quick-link">
                    <i class="ph ph-calendar-plus"></i>
                    <span>Buat Event</span>
                </a>
            </div>
        </div>
    </section>
</section>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function(){
    const chartEl = document.querySelector('#chartRegistrations');
    if (!chartEl) return;
    fetch('<?= url('api/stats/registrations') ?>')
        .then(r => r.json())
        .then(d => {
            new ApexCharts(chartEl, {
                chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif', animations: { easing: 'easeinout', speed: 900 } },
                series: [{ name: 'Anggota Baru', data: d.totals }],
                xaxis: {
                    categories: d.labels,
                    labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#9aa3c7', fontSize: '11px', fontWeight: 600 } }, forceIntegers: true
                },
                colors: ['#22d3ee'],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.02, stops: [0, 90, 100],
                        colorStops: [
                            { offset: 0, opacity: 0.5, color: '#22d3ee' },
                            { offset: 100, opacity: 0.0, color: '#22d3ee' }
                        ]
                    }
                },
                grid: { borderColor: 'rgba(255,255,255,0.06)', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                markers: { size: 5, colors: ['#22d3ee'], strokeColors: '#0b1020', strokeWidth: 2, hover: { size: 8 } },
                dataLabels: { enabled: false },
                tooltip: {
                    theme: 'dark',
                    style: { fontSize: '12px' },
                    y: { formatter: (v) => v + ' anggota' },
                    marker: { show: true }
                },
                theme: { mode: 'dark' }
            }).render();
        });
})();
</script>