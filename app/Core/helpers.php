<?php
// File: app/Core/helpers.php
declare(strict_types=1);

use Core\Session;

if (!function_exists('e')) {
    /** Escaping output untuk mencegah XSS */
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
    /** Mengirim respons JSON dan menghentikan eksekusi */
    function json(mixed $data, int $code = 200): never {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('avatar_tag')) {
    /** Menghasilkan tag avatar: foto profil bila ada, selain itu inisial nama */
    function avatar_tag(?array $user, string $extraClass = ''): string {
        $name = $user['name'] ?? 'U';
        if (!empty($user['photo'])) {
            return '<img src="' . e(url('assets/uploads/' . $user['photo'])) . '" alt="' . e($name) . '" class="avatar avatar-img ' . $extraClass . '">';
        }
        return '<span class="avatar ' . $extraClass . '">' . e(strtoupper(substr($name, 0, 1))) . '</span>';
    }
}