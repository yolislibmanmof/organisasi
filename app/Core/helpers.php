<?php
// File: app/Core/helpers.php (FINAL v7.0.1 — DUPlikAT DIPERBAIKI)
declare(strict_types=1);

use Core\Session;
use Core\Security;

/* ============================================================
   ESCAPING & OUTPUT
   ============================================================ */

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('raw')) {
    function raw(mixed $value): string
    {
        return (string) $value;
    }
}

/* ============================================================
   URL & ASSET GENERATION
   ============================================================ */

if (!function_exists('url')) {
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
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    function upload_url(string $folder, string $filename): string
    {
        return url('assets/uploads/' . trim($folder, '/') . '/' . $filename);
    }
}

/* ============================================================
   PATH HELPERS (PORTABLE)
   ============================================================ */

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = defined('BASE_PATH')
            ? BASE_PATH
            : dirname(__DIR__, 2);

        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('view_path')) {
    function view_path(string $path = ''): string
    {
        return base_path('views' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

/* ============================================================
   CONFIG ACCESS
   ============================================================ */

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return defined($key) ? constant($key) : $default;
    }
}

/* ============================================================
   REDIRECT & RESPONSE
   ============================================================ */

if (!function_exists('redirect')) {
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
    function redirect_back(?string $flashMessage = null, string $flashType = 'info', string $fallback = ''): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $target = $referer !== '' ? $referer : ($fallback !== '' ? $fallback : url(''));
        redirect($target, $flashMessage, $flashType);
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $code = 200, array $headers = []): never
    {
        http_response_code($code);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        foreach ($headers as $name => $value) {
            header("$name: $value");
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('download')) {
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
    function csrf_field(): string
    {
        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        return '<input type="hidden" name="' . $tokenName . '" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
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
    function old(string $key, mixed $default = ''): mixed
    {
        $oldInput = Session::get('_old_input', []);
        if (is_array($oldInput) && array_key_exists($key, $oldInput)) {
            return $oldInput[$key];
        }

        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('flash_old_input')) {
    function flash_old_input(?array $input = null): void
    {
        $data = $input ?? $_POST;
        unset($data['password'], $data['password_confirmation'], $data[CSRF_TOKEN_NAME ?? 'csrf_token']);
        Session::set('_old_input', $data);
    }
}

if (!function_exists('selected')) {
    function selected(mixed $value, mixed $compare): string
    {
        return ((string) $value === (string) $compare) ? 'selected' : '';
    }
}

if (!function_exists('checked')) {
    function checked(mixed $value): string
    {
        return !empty($value) ? 'checked' : '';
    }
}

/* ============================================================
   AVATAR & USER HELPERS
   ============================================================ */

if (!function_exists('avatar_tag')) {
    function avatar_tag(?array $user, string $extraClass = ''): string
    {
        if ($user === null) {
            return '<span class="avatar avatar-placeholder ' . e($extraClass) . '">?</span>';
        }

        $name = $user['name'] ?? ($user['full_name'] ?? ($user['username'] ?? 'U'));
        $initial = $user['initial'] ?? strtoupper(mb_substr($name, 0, 1));
        $colorClass = $user['avatar_color'] ?? 'grad-1';

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
    function current_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Session::get('user') !== null;
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        $user = current_user();
        return $user !== null && ($user['role'] ?? '') === 'admin';
    }
}

/* ============================================================
   SETTINGS HELPER (TUNGGAL — DUPlikAT DIHAPUS)
   ============================================================ */

if (!function_exists('setting')) {
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
    function time_ago(?string $datetime): string
    {
        if ($datetime === null || $datetime === '') return '—';

        $ts = strtotime($datetime);
        if ($ts === false) return '—';

        $diff = time() - $ts;

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
    function num_id(int|float $n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }
}

if (!function_exists('currency_id')) {
    function currency_id(int|float $n): string
    {
        return 'Rp ' . num_id($n);
    }
}

/* ============================================================
   DEBUG HELPERS
   ============================================================ */

if (!function_exists('dd')) {
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
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) return $value;
        return mb_substr($value, 0, $limit - mb_strlen($end)) . $end;
    }
}

if (!function_exists('str_slug')) {
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
    function str_contains_any(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, (string) $needle)) return true;
        }
        return false;
    }
}