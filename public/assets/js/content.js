// File: public/assets/js/content.js (ULTIMATE EDITION - TAHAP 5.9)
(() => {
    'use strict';
    const BASE = document.body.dataset.base || '/';
    const api  = (p) => BASE + p;

    let type = 'officers';
    let currentData = [];
    let deleteId = null;

    const grid     = document.getElementById('contentGrid');
    const emptyEl  = document.getElementById('emptyContent');
    const infoEl   = document.getElementById('contentInfo');
    const addBtn   = document.getElementById('btnAddContent');
    const addLbl   = document.getElementById('btnAddLabel');
    const delModal = document.getElementById('contentDeleteModal');

    const LABELS = { officers: 'Pengurus', testimonials: 'Testimoni', galleries: 'Foto Galeri' };
    const MODALS = { officers: 'officerModal', testimonials: 'testimonialModal', galleries: 'galleryModal' };
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const formatDate = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '';

    /* ========== 1. FETCH JSON DENGAN DIAGNOSTIK ========== */
    async function fetchJSON(url, options) {
        const res = await fetch(url, options);
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('[CONTENT] Respons bukan JSON (' + url + '):', text.slice(0, 300));
            throw new Error('Respons server tidak valid.');
        }
    }

    /* ========== 2. MODAL HANDLING ========== */
    const openModal  = (id) => document.getElementById(id)?.classList.add('show');
    const closeModal = (m) => m.classList.remove('show');
    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd); }));

    /* ========== 3. TAB SWITCHING DENGAN ANIMASI ========== */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-active'));
            btn.classList.add('tab-active');
            
            // Animasi transisi grid
            grid.style.opacity = '0';
            grid.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                type = btn.dataset.tab;
                addLbl.textContent = 'Tambah ' + LABELS[type];
                load();
                setTimeout(() => {
                    grid.style.opacity = '1';
                    grid.style.transform = 'translateY(0)';
                }, 50);
            }, 200);
        });
    });

    /* ========== 4. LOAD DATA ========== */
    async function load() {
        grid.style.transition = 'opacity .3s, transform .3s';
        grid.innerHTML = Array.from({ length: 4 }, () => 
            '<div class="content-card-skeleton"><div class="skel" style="height:130px;border-radius:16px 16px 0 0"></div><div style="padding:14px"><div class="skel" style="height:14px;width:80%;margin-bottom:8px"></div><div class="skel" style="height:10px;width:60%"></div></div></div>'
        ).join('');
        try {
            const json = await fetchJSON(api('api/content/' + type));
            currentData = json.data || [];
            render();
        } catch (err) {
            grid.innerHTML = '';
            emptyEl.style.display = 'flex';
            document.getElementById('emptyContentText').textContent = 'Gagal memuat data. Periksa konsol untuk detail.';
        }
    }

    /* ========== 5. RENDER GRID ========== */
    function render() {
        infoEl.textContent = currentData.length + ' ' + LABELS[type].toLowerCase() + ' terdaftar';
        if (!currentData.length) { grid.innerHTML = ''; emptyEl.style.display = 'flex'; return; }
        emptyEl.style.display = 'none';

        grid.innerHTML = currentData.map((item, i) => {
            let thumb = '', title = '', sub = '';
            if (type === 'officers') {
                thumb = item.photo
                    ? `<img src="${BASE}assets/uploads/officers/${esc(item.photo)}" alt="">`
                    : `<i class="ph ph-user-circle"></i>`;
                title = item.full_name;
                sub = [item.position, item.division].filter(Boolean).join(' · ');
            } else if (type === 'testimonials') {
                thumb = `<i class="ph ph-quotes"></i>`;
                title = item.name; sub = (item.role || 'Alumni');
            } else {
                thumb = `<img src="${BASE}assets/uploads/galleries/${esc(item.image)}" alt="">`;
                title = item.title;
                const meta = [item.event_date ? formatDate(item.event_date) : '', item.location || '']
                    .filter(Boolean).join(' · ');
                sub = meta || 'Foto kegiatan';
            }
            return `
            <article class="content-card" style="animation-delay:${i * 40}ms">
                <div class="content-actions">
                    <button class="icon-btn has-tooltip" data-tooltip="Ubah" data-act="edit" data-id="${item.id}"><i class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${item.id}"><i class="ph ph-trash"></i></button>
                </div>
                <div class="content-thumb">${thumb}</div>
                <div class="content-info"><strong>${esc(title)}</strong><small>${esc(sub)}</small></div>
            </article>`;
        }).join('');

        // Bind efek 3D tilt pada setiap kartu
        bindCardEffects();
    }

    /* ========== 6. EFEK 3D TILT PADA CONTENT CARDS ========== */
    function bindCardEffects() {
        grid.querySelectorAll('.content-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 25;
                const rotateY = (centerX - x) / 25;
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Bind ripple pada tombol aksi
        grid.querySelectorAll('.icon-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'btn-ripple';
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top = (e.clientY - rect.top) + 'px';
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    /* ========== 7. TAMBAH KONTEN ========== */
    addBtn.addEventListener('click', () => {
        resetForms();
        if (type === 'officers') document.getElementById('officerModalTitle').textContent = 'Tambah Pengurus';
        if (type === 'testimonials') document.getElementById('testimonialModalTitle').textContent = 'Tambah Testimoni';
        if (type === 'galleries') document.getElementById('galleryModalTitle').textContent = 'Tambah Foto Galeri';
        openModal(MODALS[type]);
    });

    function resetForms() {
        ['officerForm', 'testimonialForm', 'galleryForm'].forEach(id => document.getElementById(id)?.reset());
        ['oId', 'tId', 'gId'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
        document.querySelectorAll('.field-error').forEach(el => {
            el.textContent = '';
            el.closest('.field')?.classList.remove('has-error');
        });
    }

    /* ========== 8. AKSI BARIS (EDIT/DELETE) ========== */
    grid.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-act]');
        if (!btn) return;
        const item = currentData.find(x => String(x.id) === btn.dataset.id);
        if (!item) return;

        if (btn.dataset.act === 'edit') {
            resetForms();
            if (type === 'officers') {
                document.getElementById('oId').value = item.id;
                document.getElementById('oName').value = item.full_name;
                document.getElementById('oPosition').value = item.position;
                document.getElementById('oDivision').value = item.division || '';
                document.getElementById('oOrder').value = item.sort_order || 0;
                document.getElementById('oBio').value = item.bio || '';
                document.getElementById('officerModalTitle').textContent = 'Sunting Pengurus';
            } else if (type === 'testimonials') {
                document.getElementById('tId').value = item.id;
                document.getElementById('tName').value = item.name;
                document.getElementById('tRole').value = item.role || '';
                document.getElementById('tQuote').value = item.quote;
                document.getElementById('testimonialModalTitle').textContent = 'Sunting Testimoni';
            } else {
                document.getElementById('gId').value = item.id;
                document.getElementById('gTitle').value = item.title;
                document.getElementById('gDate').value = item.event_date || '';
                document.getElementById('gLocation').value = item.location || '';
                document.getElementById('galleryModalTitle').textContent = 'Sunting Foto Galeri';
            }
            openModal(MODALS[type]);
        }

        if (btn.dataset.act === 'del') {
            deleteId = item.id;
            const name = type === 'officers' ? item.full_name : (type === 'testimonials' ? item.name : item.title);
            document.getElementById('contentDeleteText').textContent = 'Konten "' + name + '" akan dihapus permanen.';
            openModal('contentDeleteModal');
        }
    });

    /* ========== 9. SIMPAN FORM (3 FORM) ========== */
    const bind = (formId, idEl) => {
        const form = document.getElementById(formId);
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id  = document.getElementById(idEl).value;
            const url = id ? api('content/update/' + id) : api('content/store');
            const btn = form.querySelector('button[type="submit"]');
            btn.classList.add('is-loading');
            form.querySelectorAll('.field-error').forEach(el => {
                el.textContent = '';
                el.closest('.field')?.classList.remove('has-error');
            });

            form.style.opacity = '0.7';
            try {
                const json = await fetchJSON(url, { method: 'POST', body: new FormData(form) });
                btn.classList.remove('is-loading');
                form.style.opacity = '1';

                if (json.ok) {
                    form.style.borderColor = 'var(--ok)';
                    setTimeout(() => form.style.borderColor = '', 1000);
                    closeModal(form.closest('.modal-backdrop'));
                    toast(json.message, 'success');
                    createConfetti();
                    load();
                } else if (json.errors) {
                    Object.entries(json.errors).forEach(([k, v]) => {
                        const err = form.querySelector('[data-error="' + k + '"]');
                        if (err) {
                            err.textContent = v;
                            err.closest('.field')?.classList.add('has-error');
                        }
                    });
                    toast('Periksa kembali isian Anda.', 'error');
                    form.style.animation = 'shake .4s';
                    setTimeout(() => form.style.animation = '', 400);
                } else {
                    toast(json.message || 'Gagal menyimpan.', 'error');
                }
            } catch (err) {
                btn.classList.remove('is-loading');
                form.style.opacity = '1';
                toast(err.message || 'Koneksi ke server gagal.', 'error');
            }
        });
    };
    bind('officerForm', 'oId');
    bind('testimonialForm', 'tId');
    bind('galleryForm', 'gId');

    /* ========== 10. KONFETI ========== */
    function createConfetti() {
        const colors = ['#6366f1', '#22d3ee', '#10b981', '#f59e0b'];
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

    /* ========== 11. HAPUS KONTEN ========== */
    document.getElementById('btnConfirmContentDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', document.querySelector('#officerForm input[name="csrf_token"]').value);
        fd.append('type', type);
        const btn = document.getElementById('btnConfirmContentDelete');
        btn.classList.add('is-loading');
        try {
            const json = await fetchJSON(api('content/delete/' + deleteId), { method: 'POST', body: fd });
            btn.classList.remove('is-loading');
            closeModal(delModal);
            toast(json.message, json.ok ? 'success' : 'error');
            if (json.ok) load();
        } catch (err) {
            btn.classList.remove('is-loading');
            toast(err.message || 'Koneksi ke server gagal.', 'error');
        }
        deleteId = null;
    });

    load();
})();