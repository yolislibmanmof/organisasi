// File: public/assets/js/content.js
(() => {
    'use strict';
    const BASE = document.body.dataset.base || '/';
    const api  = (p) => BASE + p;

    let type = 'officers';
    let currentData = [];
    let deleteId = null;

    const grid    = document.getElementById('contentGrid');
    const emptyEl = document.getElementById('emptyContent');
    const infoEl  = document.getElementById('contentInfo');
    const addBtn  = document.getElementById('btnAddContent');
    const addLbl  = document.getElementById('btnAddLabel');
    const delModal = document.getElementById('contentDeleteModal');

    const LABELS = { officers: 'Pengurus', testimonials: 'Testimoni', galleries: 'Foto Galeri' };
    const MODALS = { officers: 'officerModal', testimonials: 'testimonialModal', galleries: 'galleryModal' };
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const openModal  = (id) => document.getElementById(id)?.classList.add('show');
    const closeModal = (m) => m.classList.remove('show');
    document.querySelectorAll('[data-close-modal]').forEach(b =>
        b.addEventListener('click', () => closeModal(b.closest('.modal-backdrop'))));
    document.querySelectorAll('.modal-backdrop').forEach(bd =>
        bd.addEventListener('click', (e) => { if (e.target === bd) closeModal(bd); }));

    /* ---------- Tab ---------- */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-active'));
            btn.classList.add('tab-active');
            type = btn.dataset.tab;
            addLbl.textContent = 'Tambah ' + LABELS[type];
            load();
        });
    });

    async function load() {
        grid.innerHTML = Array.from({ length: 4 }, () => '<div class="skel" style="height:190px;border-radius:16px"></div>').join('');
        const res  = await fetch(api('api/content/' + type));
        const json = await res.json();
        currentData = json.data || [];
        render();
    }

    function render() {
        infoEl.textContent = currentData.length + ' ' + LABELS[type].toLowerCase() + ' terdaftar';
        if (!currentData.length) { grid.innerHTML = ''; emptyEl.style.display = 'flex'; return; }
        emptyEl.style.display = 'none';

        grid.innerHTML = currentData.map(item => {
            let thumb = '', title = '', sub = '';
            if (type === 'officers') {
                thumb = item.photo
                    ? `<img src="${BASE}assets/uploads/officers/${esc(item.photo)}" alt="">`
                    : `<i class="ph ph-user-circle"></i>`;
                title = item.full_name; sub = item.position;
            } else if (type === 'testimonials') {
                thumb = `<i class="ph ph-quotes"></i>`;
                title = item.name; sub = (item.role || 'Alumni');
            } else {
                thumb = `<img src="${BASE}assets/uploads/galleries/${esc(item.image)}" alt="">`;
                title = item.title; sub = 'Foto kegiatan';
            }
            return `
            <article class="content-card">
                <div class="content-actions">
                    <button class="icon-btn has-tooltip" data-tooltip="Ubah" data-act="edit" data-id="${item.id}"><i class="ph ph-pencil-simple"></i></button>
                    <button class="icon-btn danger has-tooltip" data-tooltip="Hapus" data-act="del" data-id="${item.id}"><i class="ph ph-trash"></i></button>
                </div>
                <div class="content-thumb">${thumb}</div>
                <div class="content-info"><strong>${esc(title)}</strong><small>${esc(sub)}</small></div>
            </article>`;
        }).join('');
    }

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
        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    }

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
                document.getElementById('oOrder').value = item.sort_order || 0;
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

    /* ---------- Simpan (3 form) ---------- */
    const bind = (formId, idEl) => {
        const form = document.getElementById(formId);
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id  = document.getElementById(idEl).value;
            const url = id ? api('content/update/' + id) : api('content/store');
            const btn = form.querySelector('button[type="submit"]');
            btn.classList.add('is-loading');
            form.querySelectorAll('.field-error').forEach(el => el.textContent = '');

            const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            btn.classList.remove('is-loading');

            if (json.ok) {
                closeModal(form.closest('.modal-backdrop'));
                toast(json.message, 'success');
                load();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = form.querySelector('[data-error="' + k + '"]');
                    if (err) err.textContent = v;
                });
                toast('Periksa kembali isian Anda.', 'error');
            } else {
                toast(json.message || 'Gagal menyimpan.', 'error');
            }
        });
    };
    bind('officerForm', 'oId');
    bind('testimonialForm', 'tId');
    bind('galleryForm', 'gId');

    /* ---------- Hapus ---------- */
    document.getElementById('btnConfirmContentDelete').addEventListener('click', async () => {
        if (!deleteId) return;
        const fd = new FormData();
        fd.append('csrf_token', document.querySelector('#officerForm input[name="csrf_token"]').value);
        fd.append('type', type);
        const btn = document.getElementById('btnConfirmContentDelete');
        btn.classList.add('is-loading');
        const res  = await fetch(api('content/delete/' + deleteId), { method: 'POST', body: fd });
        const json = await res.json();
        btn.classList.remove('is-loading');
        closeModal(delModal);
        toast(json.message, json.ok ? 'success' : 'error');
        if (json.ok) load();
        deleteId = null;
    });

    load();
})();