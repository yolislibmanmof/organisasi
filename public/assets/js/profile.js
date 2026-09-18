// File: public/assets/js/profile.js
(() => {
    'use strict';

    const BASE = document.body.dataset.base || '/';
    const api  = (path) => BASE + path;

    /* ---------- Deteksi peramban ---------- */
    const ua = navigator.userAgent;
    let browser = 'Peramban tidak dikenal';
    if (ua.includes('Edg/'))          browser = 'Microsoft Edge';
    else if (ua.includes('OPR/'))     browser = 'Opera';
    else if (ua.includes('Firefox/')) browser = 'Mozilla Firefox';
    else if (ua.includes('Chrome/'))  browser = 'Google Chrome';
    else if (ua.includes('Safari/'))  browser = 'Safari';
    const uaEl = document.getElementById('uaName');
    if (uaEl) uaEl.textContent = browser;

    /* ---------- Pratinjau & pemilihan foto ---------- */
    const zone  = document.getElementById('uploadZone');
    const input = document.getElementById('photoInput');
    zone?.addEventListener('click', () => input.click());
    input?.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            toast('Ukuran foto maksimal 2 MB.', 'error');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            let img = zone.querySelector('.avatar');
            if (img.tagName !== 'IMG') {
                const next = document.createElement('img');
                next.className = img.className + ' avatar-img';
                img.replaceWith(next);
                img = next;
            }
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    /* ---------- Pengukur kekuatan kata sandi ---------- */
    const newPass = document.getElementById('newPassword');
    const meter   = document.getElementById('strengthMeter');
    const label   = document.getElementById('strengthLabel');
    newPass?.addEventListener('input', () => {
        const v = newPass.value;
        let s = 0;
        if (v.length >= 8) s++;
        if (v.length >= 12) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;

        meter.className = 'strength-meter';
        if (v === '') { label.textContent = ''; meter.querySelector('span').style.width = '0'; return; }
        if (s <= 2)      { meter.classList.add('strength-weak');   label.textContent = 'Lemah — tambahkan huruf besar, angka, atau simbol.'; }
        else if (s <= 3) { meter.classList.add('strength-fair');   label.textContent = 'Cukup — hampir aman.'; }
        else             { meter.classList.add('strength-strong'); label.textContent = 'Kuat — kata sandi sangat aman.'; }
        meter.querySelector('span').style.width = Math.min(100, (s / 5) * 100) + '%';
    });

    /* ---------- Pengirim form generik ---------- */
    const bindForm = (formId, url, onSuccess) => {
        const form = document.getElementById(formId);
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            const btn = form.querySelector('button[type="submit"]');
            btn.classList.add('is-loading');

            const res  = await fetch(url, { method: 'POST', body: new FormData(form) });
            const json = await res.json();
            btn.classList.remove('is-loading');

            if (json.ok) {
                toast(json.message, 'success');
                if (onSuccess) onSuccess();
            } else if (json.errors) {
                Object.entries(json.errors).forEach(([k, v]) => {
                    const err = form.querySelector('[data-error="' + k + '"]');
                    if (err) err.textContent = v;
                });
                toast('Mohon periksa kembali isian Anda.', 'error');
            } else {
                toast(json.message || 'Terjadi kesalahan.', 'error');
            }
        });
    };

    bindForm('profileForm', api('profile/update'), () => setTimeout(() => location.reload(), 900));
    bindForm('passwordForm', api('profile/password'), () => document.getElementById('passwordForm').reset());
})();