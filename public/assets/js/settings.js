// File: public/assets/js/settings.js
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const form = document.getElementById('settingsForm');
    if (!form) return;

    /* ---------- Preview berkas saat dipilih ---------- */
    const bindPreview = (inputId, previewId, defaultHTML) => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" alt="">`;
            };
            reader.readAsDataURL(file);
        });
    };
    bindPreview('logoInput', 'logoPreview');
    bindPreview('faviconInput', 'faviconPreview');

    /* ---------- Hapus logo/favicon via API ---------- */
    document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.remove;
            if (!confirm('Hapus ' + type + ' saat ini?')) return;

            const fd = new FormData();
            fd.append('csrf_token', form.querySelector('input[name="csrf_token"]').value);
            fd.append('type', type);

            btn.classList.add('is-loading');
            const res  = await fetch(BASE + 'settings/remove-asset', { method: 'POST', body: fd });
            const json = await res.json();
            btn.classList.remove('is-loading');

            if (json.ok) {
                toast(json.message, 'success');
                setTimeout(() => location.reload(), 700);
            } else {
                toast(json.message || 'Gagal menghapus.', 'error');
            }
        });
    });

    /* ---------- Simpan form ---------- */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        btn.classList.add('is-loading');

        const res  = await fetch(BASE + 'settings/save', { method: 'POST', body: new FormData(form) });
        const json = await res.json();
        btn.classList.remove('is-loading');

        if (json.ok) {
            toast(json.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            toast(json.message || 'Gagal menyimpan.', 'error');
        }
    });
})();