<?php
// File: app/Controllers/SettingController.php
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Setting;

class SettingController {
    private const BRAND_DIR = __DIR__ . '/../../public/assets/uploads/brand/';

    public function index(): void {
        Auth::handle();
        AdminOnly::handle();
        View::render('pages/settings', [
            'title' => 'Pengaturan Situs',
            'user'  => Session::get('user'),
            'settings' => Setting::all(),
        ], 'layouts/app');
    }

    public function save(): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }

        $pairs = [];
        foreach (Setting::KEYS as $key) {
            if ($key === 'logo' || $key === 'favicon') continue; // ditangani terpisah
            $pairs[$key] = trim((string) ($_POST[$key] ?? ''));
        }

        // Validasi minimal
        if (mb_strlen($pairs['app_name']) < 2) {
            json(['ok' => false, 'message' => 'Nama organisasi minimal 2 karakter.'], 422);
        }
        if (!filter_var($pairs['social_email'], FILTER_VALIDATE_EMAIL) && $pairs['social_email'] !== '') {
            json(['ok' => false, 'message' => 'Format email kontak tidak valid.'], 422);
        }

        // Handle logo
        $logoRes = $this->handleUpload($_FILES['logo'] ?? null, 'logo');
        if (isset($logoRes['error'])) json(['ok' => false, 'message' => $logoRes['error']], 422);
        if (!empty($logoRes['name'])) {
            $this->removeOldFile(Setting::get('logo'));
            $pairs['logo'] = $logoRes['name'];
        }

        // Handle favicon
        $favRes = $this->handleUpload($_FILES['favicon'] ?? null, 'favicon');
        if (isset($favRes['error'])) json(['ok' => false, 'message' => $favRes['error']], 422);
        if (!empty($favRes['name'])) {
            $this->removeOldFile(Setting::get('favicon'));
            $pairs['favicon'] = $favRes['name'];
        }

        Setting::setMany($pairs);
        json(['ok' => true, 'message' => 'Pengaturan situs berhasil disimpan.']);
    }

    public function removeAsset(): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        }
        $type = (string) ($_POST['type'] ?? '');
        if (!in_array($type, ['logo', 'favicon'], true)) {
            json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
        }

        $current = Setting::get($type);
        if ($current !== '') {
            $this->removeOldFile($current);
            Setting::set($type, '');
            json(['ok' => true, 'message' => ucfirst($type) . ' telah dihapus.']);
        }
        json(['ok' => false, 'message' => 'Tidak ada aset untuk dihapus.'], 422);
    }

    private function handleUpload(?array $file, string $type): array {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return []; // tidak ada berkas baru, pertahankan yang lama
        }
        // Berkas ditolak oleh php.ini (melebihi upload_max_filesize / post_max_size)
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'Berkas melebihi batas unggah server. Periksa php.ini (upload_max_filesize & post_max_size).'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Gagal mengunggah berkas.'];
        }
        // BATAS BARU: 10 MB
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['error' => 'Ukuran maksimal 10 MB.'];
        }
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return ['error' => 'Berkas harus berupa gambar.'];
        }
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];
        if (!in_array($info['mime'], $allowed, true)) {
            return ['error' => 'Format didukung: PNG, JPG, WEBP, atau ICO.'];
        }

        $ext = match ($info['mime']) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'ico',
        };
        $name = $type . '-' . time() . '.' . $ext;

        if (!is_dir(self::BRAND_DIR)) @mkdir(self::BRAND_DIR, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], self::BRAND_DIR . $name)) {
            return ['error' => 'Gagal menyimpan berkas.'];
        }
        return ['name' => $name];
    }

    private function removeOldFile(string $filename): void {
        if ($filename === '') return;
        $path = self::BRAND_DIR . $filename;
        if (is_file($path)) @unlink($path);
    }
}