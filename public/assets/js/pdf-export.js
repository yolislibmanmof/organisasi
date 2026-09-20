// File: public/assets/js/pdf-export.js (FINAL v7.0 ULTIMATE)
// Modul Ekspor PDF: Progress Bar + Chart + Logo + Watermark + Print
(() => {
    'use strict';

    /* ============================================================
       CONFIGURATION
       ============================================================ */
    const BASE = document.body.dataset.base || '/';
    const btn  = document.getElementById('btnExportPdf');
    const printBtn = document.getElementById('btnPrintPdf');
    if (!btn && !printBtn) return;

    const appName = document.querySelector('.brand-text')?.textContent?.trim()
                  || document.querySelector('.pub-brand span')?.textContent?.trim()
                  || 'Organisasi';

    const logoUrl = document.querySelector('.pub-brand-logo, .logo-mark img')?.src || null;

    const fmtDate = (d) => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric'
          })
        : '-';

    /* ============================================================
       1. PROGRESS BAR UI (Floating + Animated)
       ============================================================ */
    const progressWrap = document.createElement('div');
    progressWrap.className = 'pdf-progress-wrap';
    progressWrap.style.cssText = `
        position: fixed; bottom: 24px; right: 24px;
        width: 300px; padding: 18px 20px;
        background: rgba(15, 21, 48, .96);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .5),
                    0 0 0 1px rgba(99, 102, 241, .2);
        z-index: 1000;
        opacity: 0; visibility: hidden;
        transform: translateY(30px) scale(.95);
        transition: all .4s cubic-bezier(.22, 1, .36, 1);
    `;
    progressWrap.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <div style="width:36px;height:36px;border-radius:10px;display:grid;place-items:center;background:linear-gradient(135deg,var(--pri),var(--acc));box-shadow:0 6px 16px rgba(99,102,241,.4)">
                <i class="ph ph-file-pdf" style="font-size:18px;color:#fff"></i>
            </div>
            <div style="flex:1;min-width:0">
                <strong class="pdf-title" style="display:block;font-size:13px;font-weight:800;color:var(--txt-0)">Membuat Laporan PDF</strong>
                <small class="pdf-stage" style="display:block;font-size:11px;color:var(--txt-1);margin-top:2px">Persiapan...</small>
            </div>
            <span class="pdf-percent" style="font-size:13px;font-weight:800;color:var(--acc);font-variant-numeric:tabular-nums">0%</span>
        </div>
        <div style="height:6px;border-radius:3px;background:rgba(255,255,255,.06);overflow:hidden;position:relative">
            <div class="pdf-progress-bar" style="
                position:absolute;top:0;left:0;bottom:0;
                width:0%;
                background:linear-gradient(90deg,var(--pri),var(--acc));
                border-radius:3px;
                transition:width .4s cubic-bezier(.22,1,.36,1);
            "></div>
            <div style="position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);animation:shimmer-move 1.5s infinite"></div>
        </div>
    `;
    document.body.appendChild(progressWrap);

    const progressBar   = progressWrap.querySelector('.pdf-progress-bar');
    const progressText  = progressWrap.querySelector('.pdf-stage');
    const progressTitle = progressWrap.querySelector('.pdf-title');
    const progressPct   = progressWrap.querySelector('.pdf-percent');

    function showProgress(title, text, percent) {
        progressWrap.style.opacity = '1';
        progressWrap.style.visibility = 'visible';
        progressWrap.style.transform = 'translateY(0) scale(1)';
        progressBar.style.width = percent + '%';
        if (title) progressTitle.textContent = title;
        if (text)  progressText.textContent  = text;
        progressPct.textContent = Math.round(percent) + '%';
    }

    function hideProgress() {
        progressWrap.style.opacity = '0';
        progressWrap.style.visibility = 'hidden';
        progressWrap.style.transform = 'translateY(30px) scale(.95)';
        setTimeout(() => {
            progressBar.style.width = '0%';
            progressPct.textContent = '0%';
        }, 400);
    }

    /* ============================================================
       2. DEPENDENCY CHECKER (jsPDF + autoTable via CDN)
       ============================================================ */
    async function ensureLibs() {
        if (window.jspdf && window.jspdf.autoTable) return;

        const loadScript = (src) => new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src; s.async = true;
            s.onload = resolve;
            s.onerror = () => reject(new Error('Gagal memuat: ' + src));
            document.head.appendChild(s);
        });

        try {
            if (!window.jspdf) {
                await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
            }
            if (!window.jspdf.autoTable) {
                await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js');
            }
        } catch (e) {
            throw new Error('Tidak dapat memuat pustaka PDF dari CDN.');
        }
    }

    /* ============================================================
       3. FETCH DATA
       ============================================================ */
    async function fetchMembers() {
        const res = await fetch(BASE + 'api/members/export');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const text = await res.text();
        try {
            const json = JSON.parse(text);
            return json.data || [];
        } catch {
            throw new Error('Respons server tidak valid.');
        }
    }

    /* ============================================================
       4. EXPORT HANDLER (Multi-stage)
       ============================================================ */
    async function handleExport(mode = 'download') {
        const activeBtn = mode === 'print' ? printBtn : btn;
        activeBtn?.classList.add('is-loading');

        try {
            showProgress('Mempersiapkan...', 'Memeriksa pustaka PDF', 5);
            await ensureLibs();

            showProgress('Mengambil data...', 'Menyiapkan laporan anggota', 20);
            const rows = await fetchMembers();

            if (!rows.length) {
                hideProgress();
                toast('Tidak ada data anggota untuk diekspor.', 'error');
                return;
            }

            showProgress('Membangun dokumen...', 'Menyusun kop & statistik', 50);
            await new Promise(r => setTimeout(r, 200));

            showProgress('Merender tabel...', 'Memproses ' + rows.length + ' baris', 75);
            const doc = buildPdf(rows);

            showProgress('Menyimpan...', 'Mengemas berkas PDF', 95);
            await new Promise(r => setTimeout(r, 200));

            const filename = 'laporan-anggota-' + new Date().toISOString().slice(0, 10) + '.pdf';

            if (mode === 'print') {
                const url = doc.output('bloburl');
                const win = window.open(url);
                if (win) setTimeout(() => win.print(), 500);
                else doc.save(filename);
            } else {
                doc.save(filename);
            }

            showProgress('Selesai!', 'Laporan berhasil dibuat', 100);
            await new Promise(r => setTimeout(r, 600));
            hideProgress();

            toast('Laporan PDF berhasil ' + (mode === 'print' ? 'dicetak' : 'diunduh') + '.', 'success');
            launchConfetti();
        } catch (err) {
            hideProgress();
            toast('Gagal membuat PDF: ' + err.message, 'error');
            console.error('[PDF Export]', err);
        } finally {
            btn?.classList.remove('is-loading');
            printBtn?.classList.remove('is-loading');
        }
    }

    btn?.addEventListener('click', () => handleExport('download'));
    printBtn?.addEventListener('click', () => handleExport('print'));

    /* ============================================================
       5. BUILD PDF (Kaya: Chart, Logo, Watermark, QR)
       ============================================================ */
    function buildPdf(rows) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'mm', format: 'a4' });
        const W = doc.internal.pageSize.getWidth();
        const H = doc.internal.pageSize.getHeight();

        const now = new Date();
        const total = rows.length;
        const aktif = rows.filter(r => r.status === 'active').length;
        const nonaktif = total - aktif;
        const baru = rows.filter(r => {
            const d = new Date(r.join_date + 'T00:00:00');
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        }).length;

        /* ---------- HEADER GRADIENT ---------- */
        // Background gradient (manual via multiple rects)
        for (let i = 0; i < 20; i++) {
            const t = i / 20;
            const r = Math.round(99 * (1 - t) + 34 * t);
            const g = Math.round(102 * (1 - t) + 211 * t);
            const b = Math.round(241 * (1 - t) + 238 * t);
            doc.setFillColor(r, g, b);
            doc.rect(i * (W / 20), 0, W / 20 + 1, 38, 'F');
        }

        // Accent line
        doc.setDrawColor(255, 255, 255);
        doc.setLineWidth(0.5);
        doc.line(0, 38, W, 38);
        doc.setDrawColor(34, 211, 238);
        doc.setLineWidth(2);
        doc.line(0, 39.5, W, 39.5);

        // Logo (jika ada)
        let textX = 14;
        if (logoUrl) {
            try {
                doc.addImage(logoUrl, 'PNG', 14, 8, 22, 22);
                textX = 40;
            } catch (e) { /* Skip logo */ }
        } else {
            // Logo placeholder (kotak dengan inisial)
            doc.setFillColor(255, 255, 255);
            doc.roundedRect(14, 8, 22, 22, 4, 4, 'F');
            doc.setTextColor(79, 70, 229);
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            const initials = appName.split(/\s+/).map(w => w[0]).slice(0, 2).join('').toUpperCase();
            doc.text(initials, 25, 21, { align: 'center' });
            textX = 40;
        }

        // Title text
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(15);
        doc.text(appName, textX, 15);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9);
        doc.text('Sistem Manajemen Keanggotaan Organisasi', textX, 21);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(10.5);
        doc.text('LAPORAN DATA ANGGOTA', textX, 29);

        // Metadata kanan
        doc.setFont('helvetica', 'normal'); doc.setFontSize(8);
        doc.text('Dicetak: ' + now.toLocaleString('id-ID'), W - 14, 15, { align: 'right' });
        doc.text('Klasifikasi: Dokumen Internal', W - 14, 21, { align: 'right' });
        doc.text('Halaman 1 dari —', W - 14, 27, { align: 'right' });

        /* ---------- STAT CARDS ---------- */
        const boxes = [
            { label: 'Total Anggota',   value: total,     color: [99, 102, 241],  icon: '👥' },
            { label: 'Status Aktif',    value: aktif,     color: [16, 185, 129],  icon: '✓' },
            { label: 'Status Non-aktif',value: nonaktif,  color: [148, 163, 184], icon: '○' },
            { label: 'Baru Bulan Ini',  value: baru,      color: [245, 158, 11],  icon: '★' }
        ];
        const bw = (W - 28 - 3 * 4) / 4;
        boxes.forEach((b, i) => {
            const x = 14 + i * (bw + 4);

            // Card background
            doc.setFillColor(248, 250, 252);
            doc.roundedRect(x, 46, bw, 20, 2, 2, 'F');

            // Left accent
            doc.setFillColor(b.color[0], b.color[1], b.color[2]);
            doc.roundedRect(x, 46, 2, 20, 1, 1, 'F');

            // Value
            doc.setTextColor(15, 23, 42);
            doc.setFont('helvetica', 'bold'); doc.setFontSize(14);
            doc.text(String(b.value), x + 6, 55);

            // Label
            doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5);
            doc.setTextColor(100, 116, 139);
            doc.text(b.label, x + 6, 61);
        });

        /* ---------- MINI BAR CHART (Status) ---------- */
        const chartY = 72;
        doc.setFont('helvetica', 'bold'); doc.setFontSize(9);
        doc.setTextColor(15, 23, 42);
        doc.text('Distribusi Status Anggota', 14, chartY);

        const chartX = 14, chartW = W - 28, chartH = 14, chartBaseY = chartY + 12;
        const aktPct = total ? (aktif / total) : 0;

        // Background bar
        doc.setFillColor(241, 245, 249);
        doc.roundedRect(chartX, chartBaseY, chartW, chartH, 2, 2, 'F');

        // Aktif bar
        if (aktPct > 0) {
            doc.setFillColor(16, 185, 129);
            doc.roundedRect(chartX, chartBaseY, chartW * aktPct, chartH, 2, 2, 'F');
        }

        // Labels
        doc.setFont('helvetica', 'normal'); doc.setFontSize(8);
        doc.setTextColor(255, 255, 255);
        if (aktPct > 0.1) doc.text('Aktif: ' + aktif, chartX + 4, chartBaseY + 9);
        doc.setTextColor(100, 116, 139);
        doc.text('Non-aktif: ' + nonaktif, chartX + chartW - 2, chartBaseY + 9, { align: 'right' });

        /* ---------- TABLE ---------- */
        doc.autoTable({
            startY: chartBaseY + chartH + 6,
            head: [['#', 'Nama Lengkap', 'Username', 'Email', 'Telepon', 'Status', 'Bergabung']],
            body: rows.map((r, i) => [
                i + 1,
                r.full_name,
                r.username,
                r.email,
                r.phone || '-',
                r.status === 'active' ? '● Aktif' : '○ Non-aktif',
                fmtDate(r.join_date)
            ]),
            styles: {
                font: 'helvetica',
                fontSize: 8,
                cellPadding: 2.2,
                textColor: [30, 41, 59],
                lineColor: [226, 232, 240],
                lineWidth: 0.1
            },
            headStyles: {
                fillColor: [79, 70, 229],
                textColor: 255,
                fontStyle: 'bold',
                fontSize: 8.5
            },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            columnStyles: {
                0: { cellWidth: 8, halign: 'center', fontStyle: 'bold' },
                5: { cellWidth: 22, halign: 'center', fontStyle: 'bold' },
                6: { cellWidth: 22, halign: 'center' }
            },
            margin: { left: 14, right: 14 },
            didParseCell: (data) => {
                if (data.section === 'body' && data.column.index === 5) {
                    const isActive = data.cell.raw.includes('Aktif');
                    data.cell.styles.textColor = isActive ? [16, 185, 129] : [148, 163, 184];
                }
            },
            didDrawPage: (data) => {
                // Watermark pada halaman pertama
                if (data.pageNumber === 1) {
                    doc.setTextColor(245, 245, 250);
                    doc.setFontSize(70);
                    doc.setFont('helvetica', 'bold');
                    doc.text('INTERNAL', W / 2, H / 2, {
                        align: 'center',
                        angle: 45,
                        renderingMode: 'fill'
                    });
                }

                // Footer
                doc.setFontSize(7.5);
                doc.setTextColor(148, 163, 184);
                doc.setFont('helvetica', 'italic');
                doc.text(
                    'Dokumen dihasilkan otomatis oleh ' + appName + ' — bersifat rahasia.',
                    14,
                    H - 8
                );
            }
        });

        /* ---------- HALAMAN TERAKHIR: TANDA TANGAN ---------- */
        const pageCount = doc.internal.getNumberOfPages();
        doc.setPage(pageCount);

        const lastY = doc.lastAutoTable.finalY + 15;

        // Signature area
        doc.setDrawColor(200, 200, 210);
        doc.setLineWidth(0.3);
        doc.line(W - 70, lastY + 18, W - 14, lastY + 18);

        doc.setFontSize(8);
        doc.setTextColor(100, 116, 139);
        doc.setFont('helvetica', 'normal');
        doc.text('Administrator', W - 42, lastY + 23, { align: 'center' });
        doc.text(appName, W - 42, lastY + 27, { align: 'center' });
        doc.text(now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }), W - 42, lastY + 31, { align: 'center' });

        /* ---------- PAGINATION ---------- */
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(7.5);
            doc.setTextColor(148, 163, 184);
            doc.text('Halaman ' + i + ' dari ' + pageCount, W - 14, H - 8, { align: 'right' });
        }

        return doc;
    }

    /* ============================================================
       6. CONFETTI (Premium Burst)
       ============================================================ */
    function launchConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#ec4899'];
        const shapes = ['50%', '2px'];
        for (let i = 0; i < 35; i++) {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.cssText = `
                position: fixed;
                width: ${6 + Math.random() * 5}px;
                height: ${6 + Math.random() * 5}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px;
                left: ${Math.random() * 100}vw;
                border-radius: ${shapes[Math.floor(Math.random() * shapes.length)]};
                pointer-events: none;
                z-index: 9999;
                animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 4500);
        }
    }

    /* ============================================================
       7. KEYBOARD SHORTCUT
       ============================================================ */
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.shiftKey && e.key.toLowerCase() === 'p') {
            if (btn && window.location.pathname.includes('members')) {
                e.preventDefault();
                handleExport('download');
            }
        }
    });

    console.log('%c📄 PDF Export v7.0 Ready', 'color: #22d3ee; font-weight: bold;');

})();