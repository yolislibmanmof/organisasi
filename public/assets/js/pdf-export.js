// File: public/assets/js/pdf-export.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const btn  = document.getElementById('btnExportPdf');
    if (!btn) return;

    const appName = document.querySelector('.brand-text')?.textContent?.trim() || 'Organisasi';

    const fmtDate = (d) => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : '-';

    /* ========== 1. PROGRESS BAR UI ========== */
    const progressWrap = document.createElement('div');
    progressWrap.className = 'pdf-progress-wrap';
    progressWrap.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 280px;
        padding: 14px 18px;
        background: rgba(15,21,48,.95);
        backdrop-filter: blur(14px);
        border: 1px solid var(--glass-brd-2);
        border-radius: 14px;
        box-shadow: 0 20px 50px rgba(0,0,0,.4);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all .3s var(--ease-smooth);
    `;
    progressWrap.innerHTML = `
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <i class="ph ph-file-pdf" style="font-size:18px;color:var(--danger-2)"></i>
            <strong style="font-size:12.5px">Membuat Laporan PDF</strong>
        </div>
        <div style="height:4px;border-radius:2px;background:rgba(255,255,255,.08);overflow:hidden">
            <div class="pdf-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,var(--pri),var(--acc));border-radius:2px;transition:width .3s"></div>
        </div>
        <small class="pdf-progress-text" style="display:block;margin-top:6px;font-size:11px;color:var(--txt-1)">Memuat data...</small>
    `;
    document.body.appendChild(progressWrap);

    const progressBar = progressWrap.querySelector('.pdf-progress-bar');
    const progressText = progressWrap.querySelector('.pdf-progress-text');

    function showProgress(text, percent) {
        progressWrap.style.opacity = '1';
        progressWrap.style.visibility = 'visible';
        progressWrap.style.transform = 'translateY(0)';
        progressBar.style.width = percent + '%';
        progressText.textContent = text;
    }

    function hideProgress() {
        progressWrap.style.opacity = '0';
        progressWrap.style.visibility = 'hidden';
        progressWrap.style.transform = 'translateY(20px)';
        setTimeout(() => { progressBar.style.width = '0%'; }, 300);
    }

    /* ========== 2. EXPORT HANDLER ========== */
    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        showProgress('Memuat data anggota...', 10);

        try {
            showProgress('Mengambil data dari server...', 30);
            const res  = await fetch(BASE + 'api/members/export');
            const json = await res.json();
            const rows = json.data || [];
            
            if (!rows.length) {
                hideProgress();
                toast('Tidak ada data anggota untuk diekspor.', 'error');
                return;
            }

            showProgress('Membuat dokumen PDF...', 60);
            await new Promise(r => setTimeout(r, 300));
            
            buildPdf(rows);
            
            showProgress('Selesai!', 100);
            await new Promise(r => setTimeout(r, 500));
            hideProgress();
            
            toast('Laporan PDF berhasil diunduh.', 'success');
            createConfetti();
        } catch (e) {
            hideProgress();
            toast('Gagal membuat PDF. Periksa koneksi internet (pustaka CDN).', 'error');
        } finally {
            btn.classList.remove('is-loading');
        }
    });

    /* ========== 3. BUILD PDF ========== */
    function buildPdf(rows) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: 'mm', format: 'a4' });
        const W = doc.internal.pageSize.getWidth();
        const H = doc.internal.pageSize.getHeight();

        /* ---------- Statistik ringkas ---------- */
        const now   = new Date();
        const total = rows.length;
        const aktif = rows.filter(r => r.status === 'active').length;
        const baru  = rows.filter(r => {
            const d = new Date(r.join_date + 'T00:00:00');
            return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
        }).length;

        /* ---------- Kop laporan ---------- */
        // Gradient header
        doc.setFillColor(79, 70, 229);
        doc.rect(0, 0, W, 34, 'F');
        
        // Accent line
        const gradient = doc.setDrawColor(34, 211, 238);
        doc.setLineWidth(1.6);
        doc.line(0, 34, W, 34);

        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(16);
        doc.text(appName, 14, 14);
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9);
        doc.text('Sistem Manajemen Keanggotaan Organisasi', 14, 20);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(11);
        doc.text('LAPORAN DATA ANGGOTA', 14, 28);

        doc.setFont('helvetica', 'normal'); doc.setFontSize(8.5);
        doc.text('Dicetak : ' + now.toLocaleString('id-ID'), W - 14, 14, { align: 'right' });
        doc.text('Klasifikasi : Dokumen Internal', W - 14, 20, { align: 'right' });

        /* ---------- Kotak ringkasan ---------- */
        const boxes = [
            { label: 'Total Anggota',  value: total,        color: [79, 70, 229], icon: '👥' },
            { label: 'Status Aktif',   value: aktif,        color: [16, 185, 129], icon: '✓' },
            { label: 'Status Non-aktif', value: total - aktif, color: [148, 163, 184], icon: '○' },
            { label: 'Baru Bulan Ini', value: baru,         color: [245, 158, 11], icon: '★' }
        ];
        const bw = (W - 28 - 3 * 6) / 4;
        boxes.forEach((b, i) => {
            const x = 14 + i * (bw + 6);
            
            // Card background
            doc.setFillColor(245, 246, 252);
            doc.roundedRect(x, 42, bw, 18, 2.5, 2.5, 'F');
            
            // Left accent
            doc.setFillColor(b.color[0], b.color[1], b.color[2]);
            doc.roundedRect(x, 42, 1.6, 18, 0.8, 0.8, 'F');
            
            // Value
            doc.setTextColor(30, 41, 59);
            doc.setFont('helvetica', 'bold'); doc.setFontSize(13);
            doc.text(String(b.value), x + 5, 51);
            
            // Label
            doc.setFont('helvetica', 'normal'); doc.setFontSize(7.5);
            doc.setTextColor(100, 116, 139);
            doc.text(b.label, x + 5, 56);
        });

        /* ---------- Tabel data ---------- */
        doc.autoTable({
            startY: 68,
            head: [['No', 'Nama Lengkap', 'Username', 'Email', 'Telepon', 'Status', 'Bergabung']],
            body: rows.map((r, i) => [
                i + 1,
                r.full_name,
                r.username,
                r.email,
                r.phone || '-',
                r.status === 'active' ? 'Aktif' : 'Non-aktif',
                fmtDate(r.join_date)
            ]),
            styles: { 
                font: 'helvetica', 
                fontSize: 8.5, 
                cellPadding: 2.6, 
                textColor: [30, 41, 59],
                lineColor: [226, 232, 240],
                lineWidth: 0.1
            },
            headStyles: { 
                fillColor: [79, 70, 229], 
                textColor: 255, 
                fontStyle: 'bold',
                fontSize: 9
            },
            alternateRowStyles: { fillColor: [246, 247, 252] },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                5: { cellWidth: 20, halign: 'center' },
                6: { cellWidth: 24, halign: 'center' }
            },
            margin: { left: 14, right: 14 },
            didDrawPage: (data) => {
                // Footer
                doc.setFontSize(7.5);
                doc.setTextColor(148, 163, 184);
                doc.text('Dokumen dihasilkan otomatis oleh ' + appName + ' — bersifat rahasia dan untuk keperluan internal.', 14, H - 8);
                
                // Watermark subtle
                if (data.pageNumber === 1) {
                    doc.setTextColor(240, 240, 245);
                    doc.setFontSize(60);
                    doc.text('INTERNAL', W / 2, H / 2, { 
                        align: 'center', 
                        angle: 45,
                        renderingMode: 'fill'
                    });
                }
            }
        });

        /* ---------- Halaman terakhir - Tanda tangan ---------- */
        const pageCount = doc.internal.getNumberOfPages();
        doc.setPage(pageCount);
        
        const lastY = doc.lastAutoTable.finalY + 20;
        
        // Garis tanda tangan
        doc.setDrawColor(200, 200, 210);
        doc.setLineWidth(0.3);
        doc.line(W - 70, lastY + 20, W - 14, lastY + 20);
        
        // Label
        doc.setFontSize(8);
        doc.setTextColor(100, 116, 139);
        doc.text('Administrator', W - 42, lastY + 25, { align: 'center' });
        doc.text(appName, W - 42, lastY + 29, { align: 'center' });

        /* ---------- Penomoran halaman ---------- */
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(7.5);
            doc.setTextColor(148, 163, 184);
            doc.text('Halaman ' + i + ' dari ' + pageCount, W - 14, H - 8, { align: 'right' });
        }

        doc.save('laporan-anggota-' + now.toISOString().slice(0, 10) + '.pdf');
    }

    /* ========== 4. KONFETI ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#8b5cf6'];
        for (let i = 0; i < 20; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.cssText = `
                position: fixed;
                width: 7px; height: 7px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                top: -10px; left: ${Math.random() * 100}vw;
                border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
                pointer-events: none; z-index: 9999;
                animation: confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 4000);
        }
    }

})();