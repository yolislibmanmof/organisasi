<?php
// File: app/Core/Session.php
declare(strict_types=1);

namespace Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Konfigurasi keamanan cookie sesi
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            session_set_cookie_params([
                'lifetime' => 86400,
                'domain' => $_SERVER['HTTP_HOST'],
                'path' => '/',
                'secure' => false, // Ubah ke true jika pakai HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function delete(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        session_unset();
        session_destroy();
    }

    // Flash Message (Notifikasi)
    public static function flash(string $name, string $message, string $class = 'success'): void {
        if (!self::get($name)) {
            self::set($name, ['message' => $message, 'class' => $class]);
        }
    }

    public static function getFlash(string $name): ?array {
        if (self::get($name)) {
            $flash = self::get($name);
            self::delete($name);
            return $flash;
        }
        return null;
    }
}