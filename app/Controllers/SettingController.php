<?php
// File: app/Controllers/SettingController.php (FINAL v7.0 — EXTENDED + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Setting;

/**
 * SettingController — Ultimate Edition v7.0
 *
 * Menangani pengaturan situs dengan fitur lengkap:
 * - Save all settings (termasuk announcement & SEO v7.0)
 * - Upload/remove logo & favicon dengan finfo MIME validation
 * - Reset to defaults
 * - Export/Import JSON
 * - Live SEO preview endpoint
 * - Max length validation untuk SEO fields
 * - Audit logging
 * - Graceful fallback
 */
class SettingController
{
    /** Upload directory untuk brand assets */
    private const BRAND_DIR = 'assets/uploads/brand/';

    /** Max file size (10 MB) */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /** Allowed MIME types untuk logo/favicon */
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'image/gif',
    ];

    /* ============================================================
       INDEX (VIEW)
       ============================================================ */

    public function index(): void
    {
        Auth::handle();
        AdminOnly::handle();

        // Load settings dengan graceful fallback
        $settings = $this->safeCall(
            fn() => Setting::allWithDefaults(),
            Setting::DEFAULTS
        );

        $lastUpdated = $this->safeCall(
            fn() => Setting::getLastUpdated(),
            'Belum pernah'
        );

        $lastUpdatedIso = $this->safeCall(
            fn() => Setting::getLastUpdatedIso(),
            ''
        );

        View::render('pages/settings', [
            'title'         => 'Pengaturan Situs',
            'user'          => Session::get('user'),
            'settings'      => $settings,
            'lastUpdated'   => $lastUpdated,
            'lastUpdatedIso' => $lastUpdatedIso,
            'defaults'      => Setting::DEFAULTS,
            'keys'          => Setting::KEYS,
        ], 'layouts/app');
    }

    /* ============================================================
       SAVE (UPDATE ALL SETTINGS)
       ============================================================ */

    public function save(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        // ===== KUMPULKAN PAIRS (exclude logo/favicon) =====
        $pairs = [];
        foreach (Setting::KEYS as $key) {
            if ($key === 'logo' || $key === 'favicon' || $key === 'last_updated') continue;
            $pairs[$key] = trim((string) ($_POST[$key] ?? ''));
        }

        // ===== VALIDATION =====
        $errors = $this->validateSettings($pairs);
        if (!empty($errors)) {
            json(['ok' => false, 'errors' => $errors, 'message' => 'Mohon perbaiki field yang bermasalah.'], 422);
            return;
        }

        // ===== HANDLE LOGO UPLOAD =====
        $logoRes = $this->handleUpload($_FILES['logo'] ?? null, 'logo');
        if (isset($logoRes['error'])) {
            json(['ok' => false, 'message' => $logoRes['error'], 'field' => 'logo'], 422);
            return;
        }
        if (!empty($logoRes['name'])) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo !== '') {
                $this->removeOldFile($oldLogo);
            }
            $pairs['logo'] = $logoRes['name'];
        }

        // ===== HANDLE FAVICON UPLOAD =====
        $favRes = $this->handleUpload($_FILES['favicon'] ?? null, 'favicon');
        if (isset($favRes['error'])) {
            json(['ok' => false, 'message' => $favRes['error'], 'field' => 'favicon'], 422);
            return;
        }
        if (!empty($favRes['name'])) {
            $oldFavicon = Setting::get('favicon');
            if ($oldFavicon !== '') {
                $this->removeOldFile($oldFavicon);
            }
            $pairs['favicon'] = $favRes['name'];
        }

        // ===== SAVE TO DATABASE =====
        try {
            Setting::setMany($pairs); // Auto-touch last_updated

            error_log(sprintf(
                '[SettingController] Settings saved: %d keys by=%s',
                count($pairs), Session::get('user')['username'] ?? 'unknown'
            ));

            $lastUpdated = Setting::getLastUpdated();

            json([
                'ok'          => true,
                'message'     => 'Pengaturan situs berhasil disimpan.',
                'lastUpdated' => $lastUpdated,
                'lastUpdatedIso' => Setting::getLastUpdatedIso(),
            ]);
        } catch (\Throwable $e) {
            error_log('[SettingController::save] ' . $e->getMessage());
            // Cleanup uploaded files jika DB gagal
            if (!empty($logoRes['name'])) $this->removeOldFile($logoRes['name']);
            if (!empty($favRes['name'])) $this->removeOldFile($favRes['name']);
            json(['ok' => false, 'message' => 'Gagal menyimpan pengaturan.'], 500);
        }
    }

    /* ============================================================
       REMOVE ASSET (logo/favicon)
       ============================================================ */

    public function removeAsset(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $type = (string) ($_POST['type'] ?? '');
        if (!in_array($type, ['logo', 'favicon'], true)) {
            json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
            return;
        }

        $current = Setting::get($type);
        if ($current === '') {
            json(['ok' => false, 'message' => 'Tidak ada aset untuk dihapus.'], 422);
            return;
        }

        try {
            $this->removeOldFile($current);
            Setting::set($type, '');

            error_log(sprintf(
                '[SettingController] Removed %s by=%s',
                $type, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'      => true,
                'message' => ucfirst($type) . ' telah dihapus.',
                'type'    => $type,
            ]);
        } catch (\Throwable $e) {
            error_log('[SettingController::removeAsset] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal menghapus aset.'], 500);
        }
    }

    /* ============================================================
       RESET TO DEFAULTS
       ============================================================ */

    /**
     * Reset settings ke default.
     * POST /admin/settings/reset
     * Body (optional): {keys: ['logo', 'favicon', ...]}
     */
    public function reset(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $keys = $input['keys'] ?? ($_POST['keys'] ?? null);

        // Convert ke array jika string (comma-separated)
        if (is_string($keys)) {
            $keys = array_map('trim', explode(',', $keys));
        }

        // Jika keys diberikan, filter hanya yang valid
        if (is_array($keys) && !empty($keys)) {
            $keys = array_intersect($keys, Setting::KEYS);
            // Jangan reset logo/favicon via endpoint ini (butuh file cleanup)
            $keys = array_diff($keys, ['logo', 'favicon', 'last_updated']);
        } else {
            // Reset semua kecuali logo/favicon (biar user explicit remove)
            $keys = array_diff(Setting::KEYS, ['logo', 'favicon', 'last_updated']);
        }

        if (empty($keys)) {
            json(['ok' => false, 'message' => 'Tidak ada key yang direset.'], 422);
            return;
        }

        try {
            $count = Setting::resetToDefaults(array_values($keys));

            error_log(sprintf(
                '[SettingController] Reset to defaults: %d keys by=%s',
                $count, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'      => true,
                'message' => "$count pengaturan berhasil direset ke default.",
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            error_log('[SettingController::reset] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mereset pengaturan.'], 500);
        }
    }

    /* ============================================================
       EXPORT JSON
       ============================================================ */

    /**
     * Export settings ke JSON file.
     * GET /admin/settings/export
     */
    public function export(): void
    {
        Auth::handle();
        AdminOnly::handle();

        try {
            $data = Setting::exportAll(true);
            $data['_exported_at'] = date('c');
            $data['_app_version'] = '7.0';
        } catch (\Throwable $e) {
            error_log('[SettingController::export] ' . $e->getMessage());
            http_response_code(500);
            echo 'Export gagal.';
            return;
        }

        $filename = 'settings-' . date('Y-m-d-His') . '.json';

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ============================================================
       IMPORT JSON
       ============================================================ */

    /**
     * Import settings dari JSON file.
     * POST /admin/settings/import
     * Form: file (JSON)
     */
    public function import(): void
    {
        Auth::handle();
        AdminOnly::handle();

        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
            return;
        }

        $file = $_FILES['file'] ?? null;
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            json(['ok' => false, 'message' => 'Tidak ada file yang diupload.'], 422);
            return;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            json(['ok' => false, 'message' => 'Gagal mengupload file.'], 422);
            return;
        }

        if ($file['size'] > 1024 * 1024) { // Max 1 MB
            json(['ok' => false, 'message' => 'File terlalu besar (maksimal 1 MB).'], 422);
            return;
        }

        // Baca konten
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            json(['ok' => false, 'message' => 'Gagal membaca file.'], 422);
            return;
        }

        // Parse JSON
        $data = json_decode($content, true);
        if (!is_array($data)) {
            json(['ok' => false, 'message' => 'Format JSON tidak valid.'], 422);
            return;
        }

        // Buang key system
        unset($data['_exported_at'], $data['_app_version'], $data['last_updated']);

        try {
            $count = Setting::importSettings($data); // Hanya key terdaftar

            error_log(sprintf(
                '[SettingController] Imported settings: %d keys by=%s',
                $count, Session::get('user')['username'] ?? 'unknown'
            ));

            json([
                'ok'      => true,
                'message' => "$count pengaturan berhasil diimport.",
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            error_log('[SettingController::import] ' . $e->getMessage());
            json(['ok' => false, 'message' => 'Gagal mengimport pengaturan.'], 500);
        }
    }

    /* ============================================================
       PREVIEW SEO (API untuk live preview)
       ============================================================ */

    /**
     * Generate SEO preview untuk Google search result preview.
     * POST /admin/settings/preview-seo
     */
    public function previewSeo(): void
    {
        Auth::handle();
        AdminOnly::handle();

        $title = trim((string) ($_POST['title'] ?? Setting::get('app_name')));
        $description = trim((string) ($_POST['description'] ?? Setting::get('meta_description')));
        $url = function_exists('url') ? url('') : $_SERVER['HTTP_HOST'] ?? 'example.com';

        // Google SERP limits
        $titleMax = 60;
        $descMax = 160;

        $titleTruncated = mb_strlen($title) > $titleMax
            ? mb_substr($title, 0, $titleMax - 3) . '...'
            : $title;

        $descTruncated = mb_strlen($description) > $descMax
            ? mb_substr($description, 0, $descMax - 3) . '...'
            : $description;

        json([
            'ok'   => true,
            'data' => [
                'title'            => $title,
                'title_truncated'  => $titleTruncated,
                'title_length'     => mb_strlen($title),
                'title_over'       => mb_strlen($title) > $titleMax,
                'description'      => $description,
                'desc_truncated'   => $descTruncated,
                'desc_length'      => mb_strlen($description),
                'desc_over'        => mb_strlen($description) > $descMax,
                'url'              => $url,
            ],
        ]);
    }

    /* ============================================================
       PRIVATE: VALIDATION
       ============================================================ */

    private function validateSettings(array $pairs): array
    {
        $errors = [];

        // App name: min 2, max 100
        if (mb_strlen($pairs['app_name'] ?? '') < 2) {
            $errors['app_name'] = 'Nama organisasi minimal 2 karakter.';
        } elseif (mb_strlen($pairs['app_name'] ?? '') > 100) {
            $errors['app_name'] = 'Nama organisasi maksimal 100 karakter.';
        }

        // Email: valid format jika diisi
        $email = $pairs['social_email'] ?? '';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['social_email'] = 'Format email kontak tidak valid.';
        }

        // Phone: numeric only jika diisi
        $phone = $pairs['social_phone'] ?? '';
        if ($phone !== '') {
            $digits = preg_replace('/\D/', '', $phone);
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                $errors['social_phone'] = 'Nomor telepon tidak valid (8-15 digit).';
            }
        }

        // Address: max 300
        if (mb_strlen($pairs['social_address'] ?? '') > 300) {
            $errors['social_address'] = 'Alamat maksimal 300 karakter.';
        }

        // Social URLs: valid URL jika diisi
        $urlFields = ['social_instagram', 'social_youtube'];
        foreach ($urlFields as $field) {
            $value = $pairs[$field] ?? '';
            if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                // Izinkan handle saja (misal @username)
                if (!preg_match('/^@?[a-zA-Z0-9._-]+$/', $value)) {
                    $errors[$field] = 'Format URL atau handle tidak valid.';
                }
            }
        }

        // Visi: max 500
        if (mb_strlen($pairs['visi'] ?? '') > 500) {
            $errors['visi'] = 'Visi maksimal 500 karakter.';
        }

        // Misi: max 2000
        if (mb_strlen($pairs['misi'] ?? '') > 2000) {
            $errors['misi'] = 'Misi maksimal 2000 karakter.';
        }

        // Motto: max 200
        if (mb_strlen($pairs['motto'] ?? '') > 200) {
            $errors['motto'] = 'Motto maksimal 200 karakter.';
        }

        // Cabinet period: max 100
        if (mb_strlen($pairs['cabinet_period'] ?? '') > 100) {
            $errors['cabinet_period'] = 'Periode kabinet maksimal 100 karakter.';
        }

        // Announcement: max 300 text, max 500 link
        if (mb_strlen($pairs['announcement_text'] ?? '') > 300) {
            $errors['announcement_text'] = 'Teks pengumuman maksimal 300 karakter.';
        }
        $annLink = $pairs['announcement_link'] ?? '';
        if ($annLink !== '' && !filter_var($annLink, FILTER_VALIDATE_URL)) {
            $errors['announcement_link'] = 'Link pengumuman harus URL valid.';
        } elseif (mb_strlen($annLink) > 500) {
            $errors['announcement_link'] = 'Link pengumuman maksimal 500 karakter.';
        }

        // SEO meta: Google SERP limits
        $metaDesc = $pairs['meta_description'] ?? '';
        if (mb_strlen($metaDesc) > 300) {
            $errors['meta_description'] = 'Meta description maksimal 300 karakter (disarankan 150-160).';
        }

        $metaKeywords = $pairs['meta_keywords'] ?? '';
        if (mb_strlen($metaKeywords) > 500) {
            $errors['meta_keywords'] = 'Meta keywords maksimal 500 karakter.';
        }

        $metaAuthor = $pairs['meta_author'] ?? '';
        if (mb_strlen($metaAuthor) > 100) {
            $errors['meta_author'] = 'Meta author maksimal 100 karakter.';
        }

        return $errors;
    }

    /* ============================================================
       PRIVATE: FILE HANDLING
       ============================================================ */

    /**
     * Handle upload logo/favicon dengan finfo MIME validation.
     */
    private function handleUpload(?array $file, string $type): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return []; // Tidak ada berkas baru
        }

        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'Berkas melebihi batas unggah server. Periksa php.ini.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Gagal mengunggah berkas (code: ' . $file['error'] . ').'];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['error' => 'Ukuran maksimal ' . (self::MAX_FILE_SIZE / 1048576) . ' MB.'];
        }

        // MIME validation dengan finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return ['error' => 'Format didukung: PNG, JPG, WEBP, GIF, atau ICO.'];
        }

        // Khusus favicon, izinkan ICO
        if ($type === 'favicon' && in_array($mime, ['image/x-icon', 'image/vnd.microsoft.icon'], true)) {
            $ext = 'ico';
        } else {
            // Validasi struktur gambar
            if (@getimagesize($file['tmp_name']) === false) {
                return ['error' => 'Berkas harus berupa gambar valid.'];
            }
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
                default      => 'bin',
            };
        }

        $name = $type . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            return ['error' => 'Gagal menyimpan berkas ke server.'];
        }

        return ['name' => $name];
    }

    private function removeOldFile(string $filename): void
    {
        if ($filename === '') return;
        $path = $this->getUploadDir() . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function getUploadDir(): string
    {
        if (function_exists('public_path')) {
            return rtrim(public_path(self::BRAND_DIR), '/') . '/';
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return rtrim($base . '/' . self::BRAND_DIR, '/') . '/';
    }

    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[SettingController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}