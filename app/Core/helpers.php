<?php
// File: app/Core/helpers.php (FINAL v7.0 — EXTENDED + PORTABLE + RICH)
declare(strict_types=1);

use Core\Session;
use Core\Security;

/* ============================================================
   ESCAPING & OUTPUT
   ============================================================ */

if (!function_exists('e')) {
    /**
     * Escape HTML (htmlspecialchars wrapper).
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('raw')) {
    /**
     * Bypass escaping (untuk trusted HTML).
     * Hati-hati: hanya gunakan untuk konten yang sudah di-sanitize.
     */
    function raw(mixed $value): string
    {
        return (string) $value;
    }
}

/* ============================================================
   URL & ASSET GENERATION
   ============================================================ */

if (!function_exists('url')) {
    /**
     * Generate full URL dari path.
     * Handle BASE_URL dengan atau tanpa trailing slash.
     *
     * @param string $path Relative path
     * @param array<string, mixed> $query Query parameters (optional)
     */
    function url(string $path = '', array $query = []): string
    {
        $base = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
        $url = $base . '/' . ltrim($path, '/');

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL (CSS, JS, images).
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    /**
     * Generate URL untuk file upload.
     *
     * @param string $folder Subfolder (users, articles, galleries, dll)
     * @param string $filename Nama file
     */
    function upload_url(string $folder, string $filename): string
    {
        return url('assets/uploads/' . trim($folder, '/') . '/' . $filename);
    }
}

/* ============================================================
   PATH HELPERS (PORTABLE)
   ============================================================ */

if (!function_exists('base_path')) {
    /**
     * Get absolute path ke root proyek.
     */
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH')
            ? BASE_PATH
            : dirname(__DIR__, 2);

        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get absolute path ke folder public.
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get absolute path ke folder storage (logs, cache, uploads).
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('app_path')) {
    /**
     * Get absolute path ke folder app.
     */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('view_path')) {
    /**
     * Get absolute path ke folder views.
     */
    function view_path(string $path = ''): string
    {
        return base_path('views' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

/* ============================================================
   CONFIG ACCESS
   ============================================================ */

if (!function_exists('config')) {
    /**
     * Access config constants dengan fallback.
     *
     * @param string $key Constant name (APP_NAME, DB_HOST, dll)
     * @param mixed $default Default value jika tidak ada
     */
    function config(string $key, mixed $default = null): mixed
    {
        return defined($key) ? constant($key) : $default;
    }
}

/* ============================================================
   REDIRECT & RESPONSE
   ============================================================ */

if (!function_exists('redirect')) {
    /**
     * Redirect ke URL/path dengan optional flash message.
     *
     * @param string $path Target path atau full URL
     * @param string|null $flashMessage Flash message (optional)
     * @param string $flashType Flash type (success, error, warning, info)
     */
    function redirect(string $path, ?string $flashMessage = null, string $flashType = 'info'): never
    {
        if ($flashMessage !== null) {
            $flashKey = $flashType === 'success' ? 'success_message' : 'error_message';
            Session::flash($flashKey, $flashMessage, $flashType);
        }

        $target = (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            ? $path
            : url($path);

        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('redirect_back')) {
    /**
     * Redirect kembali ke halaman sebelumnya (HTTP_REFERER).
     *
     * @param string|null $flashMessage Optional flash
     * @param string $flashType Flash type
     * @param string $fallback Fallback URL jika tidak ada referer
     */
    function redirect_back(?string $flashMessage = null, string $flashType = 'info', string $fallback = ''): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $target = $referer !== '' ? $referer : ($fallback !== '' ? $fallback : url(''));
        redirect($target, $flashMessage, $flashType);
    }
}

if (!function_exists('json')) {
    /**
     * Send JSON response dengan optional custom headers.
     *
     * @param mixed $data Data to encode
     * @param int $code HTTP status code
     * @param array<string, string> $headers Custom headers
     */
    function json(mixed $data, int $code = 200, array $headers = []): never
    {
        http_response_code($code);

        // Default headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        // Custom headers
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('download')) {
    /**
     * Send file download response.
     *
     * @param string $filePath Absolute path ke file
     * @param string $filename Nama file yang didownload
     * @param string $contentType MIME type
     */
    function download(string $filePath, string $filename = '', string $contentType = 'application/octet-stream'): never
    {
        if (!is_file($filePath)) {
            http_response_code(404);
            exit('File not found');
        }

        $filename = $filename !== '' ? $filename : basename($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        ob_clean();
        flush();
        readfile($filePath);
        exit;
    }
}

/* ============================================================
   CSRF HELPERS
   ============================================================ */

if (!function_exists('csrf_token')) {
    /**
     * Get current CSRF token (auto-generate jika belum ada).
     */
    function csrf_token(): string
    {
        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        $token = Session::get($tokenName, '');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set($tokenName, $token);
        }

        return $token;
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate hidden input field dengan CSRF token.
     */
    function csrf_field(): string
    {
        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        return '<input type="hidden" name="' . $tokenName . '" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify CSRF token dari request.
     */
    function csrf_verify(?string $token): bool
    {
        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        $stored = Session::get($tokenName, '');

        return is_string($stored) && $stored !== ''
            && is_string($token) && $token !== ''
            && hash_equals($stored, $token);
    }
}

/* ============================================================
   FORM HELPERS
   ============================================================ */

if (!function_exists('old')) {
    /**
     * Get old input value (untuk form repopulation setelah validasi gagal).
     * Data disimpan di session flash '_old_input'.
     *
     * @param string $key Input name
     * @param mixed $default Default value
     */
    function old(string $key, mixed $default = ''): mixed
    {
        // Cek di flash session (setelah redirect dari validasi gagal)
        $oldInput = Session::get('_old_input', []);
        if (is_array($oldInput) && array_key_exists($key, $oldInput)) {
            return $oldInput[$key];
        }

        // Fallback ke $_POST/$_GET saat ini
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('flash_old_input')) {
    /**
     * Simpan current input ke session untuk repopulation.
     * Biasanya dipanggil sebelum redirect setelah validasi gagal.
     *
     * @param array<string, mixed>|null $input Custom input (default: $_POST)
     */
    function flash_old_input(?array $input = null): void
    {
        $data = $input ?? $_POST;
        // Hapus field sensitif
        unset($data['password'], $data['password_confirmation'], $data[CSRF_TOKEN_NAME ?? 'csrf_token']);
        Session::set('_old_input', $data);
    }
}

if (!function_exists('selected')) {
    /**
     * Return 'selected' attribute jika value match (untuk <select>).
     */
    function selected(mixed $value, mixed $compare): string
    {
        return ((string) $value === (string) $compare) ? 'selected' : '';
    }
}

if (!function_exists('checked')) {
    /**
     * Return 'checked' attribute jika truthy (untuk checkbox/radio).
     */
    function checked(mixed $value): string
    {
        return !empty($value) ? 'checked' : '';
    }
}

/* ============================================================
   AVATAR & USER HELPERS
   ============================================================ */

if (!function_exists('avatar_tag')) {
    /**
     * Generate avatar HTML tag dengan support photo_url dari decorator.
     * Backward compat signature v5.x.
     *
     * Support 2 format:
     * - Legacy: ['name' => 'Budi', 'photo' => 'file.jpg']
     * - v7.0 decorated: ['name' => 'Budi', 'photo_url' => 'https://...', 'initial' => 'B', 'avatar_color' => 'grad-1']
     */
    function avatar_tag(?array $user, string $extraClass = ''): string
    {
        if ($user === null) {
            return '<span class="avatar avatar-placeholder ' . e($extraClass) . '">?</span>';
        }

        $name = $user['name'] ?? ($user['full_name'] ?? ($user['username'] ?? 'U'));
        $initial = $user['initial'] ?? strtoupper(mb_substr($name, 0, 1));
        $colorClass = $user['avatar_color'] ?? 'grad-1';

        // Cek photo URL (v7.0 decorator) atau photo filename (legacy)
        $photoUrl = null;
        if (!empty($user['photo_url'])) {
            $photoUrl = $user['photo_url'];
        } elseif (!empty($user['photo'])) {
            $folder = $user['photo_folder'] ?? 'users';
            $photoUrl = upload_url($folder, $user['photo']);
        }

        if ($photoUrl !== null) {
            return sprintf(
                '<img src="%s" alt="%s" class="avatar avatar-img %s" loading="lazy">',
                e($photoUrl),
                e($name),
                e($extraClass)
            );
        }

        return sprintf(
            '<span class="avatar avatar-placeholder %s %s">%s</span>',
            e($colorClass),
            e($extraClass),
            e($initial)
        );
    }
}

if (!function_exists('current_user')) {
    /**
     * Get current logged-in user dari session.
     */
    function current_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('is_logged_in')) {
    /**
     * Check apakah user sudah login.
     */
    function is_logged_in(): bool
    {
        return Session::get('user') !== null;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check apakah current user adalah admin.
     */
    function is_admin(): bool
    {
        $user = current_user();
        return $user !== null && ($user['role'] ?? '') === 'admin';
    }
}

/* ============================================================
   SETTINGS HELPER
   ============================================================ */

if (!function_exists('setting')) {
    /**
     * Get setting value (dengan caching awareness).
     * Trigger Setting::loadAll() sekali per request.
     */
    function setting(string $key, string $default = ''): string
    {
        try {
            if (class_exists('\Models\Setting')) {
                \Models\Setting::loadAll();
                return \Models\Setting::get($key, $default);
            }
        } catch (\Throwable $e) {
            // Silent fail, return default
        }
        return $default;
    }
}

/* ============================================================
   DATE & TIME HELPERS
   ============================================================ */

if (!function_exists('time_ago')) {
    /**
     * Relative time dalam Bahasa Indonesia.
     * Support past ("2 jam lalu") dan future ("dalam 3 hari").
     * Backward compat signature v5.x.
     */
    function time_ago(?string $datetime): string
    {
        if ($datetime === null || $datetime === '') return '—';

        $ts = strtotime($datetime);
        if ($ts === false) return '—';

        $diff = time() - $ts;

        // Future
        if ($diff < 0) {
            $diff = abs($diff);
            if ($diff < 60)     return 'sebentar lagi';
            if ($diff < 3600)   return 'dalam ' . (int) floor($diff / 60) . ' menit';
            if ($diff < 86400)  return 'hari ini';
            if ($diff < 172800) return 'besok';
            if ($diff < 604800) return 'dalam ' . (int) floor($diff / 86400) . ' hari';
            if ($diff < 2592000) return 'dalam ' . (int) floor($diff / 604800) . ' minggu';
            return date('d M Y', $ts);
        }

        // Past
        if ($diff < 60)     return 'baru saja';
        if ($diff < 3600)   return (int) floor($diff / 60) . ' menit lalu';
        if ($diff < 86400)  return (int) floor($diff / 3600) . ' jam lalu';
        if ($diff < 172800) return 'kemarin';
        if ($diff < 604800) return (int) floor($diff / 86400) . ' hari lalu';
        if ($diff < 2592000) return (int) floor($diff / 604800) . ' minggu lalu';
        if ($diff < 31536000) return (int) floor($diff / 2592000) . ' bulan lalu';
        return (int) floor($diff / 31536000) . ' tahun lalu';
    }
}

if (!function_exists('format_date_id')) {
    /**
     * Format tanggal Bahasa Indonesia (20 September 2026).
     */
    function format_date_id(?string $datetime, string $format = 'j F Y'): string
    {
        if ($datetime === null || $datetime === '') return '—';

        $ts = strtotime($datetime);
        if ($ts === false) return '—';

        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $result = $format;
        $result = str_replace('F', $months[(int) date('n', $ts)], $result);
        $result = str_replace('M', substr($months[(int) date('n', $ts)], 0, 3), $result);
        $result = str_replace('j', date('j', $ts), $result);
        $result = str_replace('d', date('d', $ts), $result);
        $result = str_replace('Y', date('Y', $ts), $result);
        $result = str_replace('y', date('y', $ts), $result);
        $result = str_replace('H', date('H', $ts), $result);
        $result = str_replace('i', date('i', $ts), $result);

        return $result;
    }
}

if (!function_exists('num_id')) {
    /**
     * Format angka dengan pemisah ribuan Indonesia (titik).
     */
    function num_id(int|float $n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }
}

if (!function_exists('currency_id')) {
    /**
     * Format mata uang Rupiah.
     */
    function currency_id(int|float $n): string
    {
        return 'Rp ' . num_id($n);
    }
}

/* ============================================================
   DEBUG HELPERS
   ============================================================ */

if (!function_exists('dd')) {
    /**
     * Dump and Die (untuk debugging).
     * Tampilkan var_dump dengan styling, lalu exit.
     */
    function dd(mixed ...$vars): never
    {
        $isCli = PHP_SAPI === 'cli';

        if (!$isCli) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow:auto;font-family:Monaco,Menlo,monospace;font-size:13px;line-height:1.5;">';
        }

        foreach ($vars as $var) {
            var_dump($var);
            echo "\n";
        }

        if (!$isCli) {
            echo '</pre>';
        }

        exit(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump tanpa die (untuk multiple inspection).
     */
    function dump(mixed ...$vars): void
    {
        $isCli = PHP_SAPI === 'cli';

        if (!$isCli) {
            echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow:auto;font-family:Monaco,Menlo,monospace;font-size:13px;line-height:1.5;margin:8px 0;">';
        }

        foreach ($vars as $var) {
            var_dump($var);
            echo "\n";
        }

        if (!$isCli) {
            echo '</pre>';
        }
    }
}

/* ============================================================
   STRING HELPERS
   ============================================================ */

if (!function_exists('str_limit')) {
    /**
     * Truncate string dengan ellipsis.
     */
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) return $value;
        return mb_substr($value, 0, $limit - mb_strlen($end)) . $end;
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate URL-friendly slug.
     */
    function str_slug(string $text): string
    {
        if (function_exists('transliterator_transliterate')) {
            $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
        } else {
            $text = strtolower($text);
        }
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string) $text, '-');
    }
}

if (!function_exists('str_contains_any')) {
    /**
     * Check apakah string mengandung salah satu needle.
     */
    function str_contains_any(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, (string) $needle)) return true;
        }
        return false;
    }
}