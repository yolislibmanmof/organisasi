<?php
// File: app/Core/helpers.php (FINAL - TAHAP 5.5)
declare(strict_types=1);

use Core\Session;

if (!function_exists('e')) {
    function e(mixed $value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return BASE_URL . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never {
        $target = str_starts_with($path, 'http') ? $path : url($path);
        header('Location: ' . $target);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        $token = Session::get(CSRF_TOKEN_NAME, '');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(CSRF_TOKEN_NAME, $token);
        }
        return $token;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(?string $token): bool {
        $stored = Session::get(CSRF_TOKEN_NAME, '');
        return is_string($stored) && $stored !== ''
            && is_string($token) && $token !== ''
            && hash_equals($stored, $token);
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $code = 200): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('avatar_tag')) {
    function avatar_tag(?array $user, string $extraClass = ''): string {
        $name = $user['name'] ?? 'U';
        if (!empty($user['photo'])) {
            return '<img src="' . e(url('assets/uploads/' . $user['photo'])) . '" alt="' . e($name) . '" class="avatar avatar-img ' . $extraClass . '">';
        }
        return '<span class="avatar ' . $extraClass . '">' . e(strtoupper(substr($name, 0, 1))) . '</span>';
    }
}

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string {
        return \Models\Setting::get($key, $default);
    }
}

/* ============================================================
   HELPER BARU: Waktu relatif berbahasa Indonesia
   Menangani masa lalu ("2 jam lalu") DAN masa depan ("dalam 3 hari")
   ============================================================ */
if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string {
        if ($datetime === null || $datetime === '') return '-';
        $ts = strtotime($datetime);
        if ($ts === false) return '-';

        $diff = time() - $ts;

        // Masa depan
        if ($diff < 0) {
            $diff = abs($diff);
            if ($diff < 86400)    return 'hari ini';
            if ($diff < 172800)   return 'besok';
            if ($diff < 604800)   return 'dalam ' . (int) floor($diff / 86400) . ' hari';
            if ($diff < 2592000)  return 'dalam ' . (int) floor($diff / 604800) . ' minggu';
            return date('d M Y', $ts);
        }

        // Masa lalu
        if ($diff < 60)     return 'baru saja';
        if ($diff < 3600)   return (int) floor($diff / 60) . ' menit lalu';
        if ($diff < 86400)  return (int) floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return (int) floor($diff / 86400) . ' hari lalu';
        if ($diff < 2592000) return (int) floor($diff / 604800) . ' minggu lalu';
        return date('d M Y', $ts);
    }
}

/* ============================================================
   HELPER BARU: Format angka Indonesia (titik sebagai pemisah ribu)
   ============================================================ */
if (!function_exists('num_id')) {
    function num_id(int|float $n): string {
        return number_format((float) $n, 0, ',', '.');
    }
}