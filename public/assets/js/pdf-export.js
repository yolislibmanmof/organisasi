// File: public/assets/js/pdf-export.js
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const btn  = document.getElementById('btnExportPdf');
    if (!btn) return;

    const appName = document.querySelector('.brand-text')?.textContent?.trim() || 'Organisasi';

    const fmtDate = (d) => d
        ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
        : '-';

    btn.addEventListener('click', async () => {
        btn.classList.add('is-loading');
        try {
            const res  = await fetch(BASE + 'api/members/export');
            const json = await res.json();
            const rows = json.data || [];
            if (!rows.length) { toast('Tidak ada data anggota untuk diekspor.', 'error'); return; }
            buildPdf(rows);
            toast('Laporan PDF berhasil diunduh.', 'success');
        } catch (e) {
            toast('Gagal membuat PDF. Periksa koneksi internet (pustaka CDN).', 'error');
        } finally {
            btn.classList.remove('is-loading');
        }
    });

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
        doc.setFillColor(79, 70, 229);
        doc.rect(0, 0, W, 34, 'F');
        doc.setFillColor(34, 211, 238);
        doc.rect(0, 34, W, 1.6, 'F');

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
            { label: 'Total Anggota',  value: total,        color: [79, 70, 229]  },
            { label: 'Status Aktif',   value: aktif,        color: [16, 185, 129] },
            { label: 'Status Non-aktif', value: total - aktif, color: [148, 163, 184] },
            { label: 'Baru Bulan Ini', value: baru,         color: [245, 158, 11] }
        ];
        const bw = (W - 28 - 3 * 6) / 4;
        boxes.forEach((b, i) => {
            const x = 14 + i * (bw + 6);
            doc.setFillColor(245, 246, 252);
            doc.roundedRect(x, 42, bw, 18, 2.5, 2.5, 'F');
            doc.setFillColor(b.color[0], b.color[1], b.color[2]);
            doc.roundedRect(x, 42, 1.6, 18, 0.8, 0.8, 'F');
            doc.setTextColor(30, 41, 59);
            doc.setFont('helvetica', 'bold'); doc.setFontSize(13);
            doc.text(String(b.value), x + 5, 51);
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
            styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2.6, textColor: [30, 41, 59] },
            headStyles: { fillColor: [79, 70, 229], textColor: 255, fontStyle: 'bold' },
            alternateRowStyles: { fillColor: [246, 247, 252] },
            columnStyles: {
                0: { cellWidth: 10 },
                5: { cellWidth: 20 },
                6: { cellWidth: 24 }
            },
            margin: { left: 14, right: 14 },
            didDrawPage: () => {
                doc.setFontSize(7.5);
                doc.setTextColor(148, 163, 184);
                doc.text('Dokumen dihasilkan otomatis oleh ' + appName + ' — bersifat rahasia dan untuk keperluan internal.', 14, H - 8);
            }
        });

        /* ---------- Penomoran halaman x dari y ---------- */
        const pages = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pages; i++) {
            doc.setPage(i);
            doc.setFontSize(7.5);
            doc.setTextColor(148, 163, 184);
            doc.text('Halaman ' + i + ' dari ' + pages, W - 14, H - 8, { align: 'right' });
        }

        doc.save('laporan-anggota-' + now.toISOString().slice(0, 10) + '.pdf');
    }
})();