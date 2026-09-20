<?php
// File: app/Middleware/Auth.php (FINAL v7.0 — EXTENDED + INTENDED-URL + AUDIT)
declare(strict_types=1);

namespace Middleware;

use Core\Security;
use Core\Session;

/**
 * Auth Middleware — Ultimate Edition v7.0
 *
 * Authentication guard untuk halaman wajib-login:
 * - Cek user logged in
 * - Cek status active (user inactive ditolak)
 * - Save intended URL untuk redirect setelah login
 * - Support AJAX/JSON response (401 Unauthorized)
 * - Flash message informatif
 * - Avoid infinite loop (jika sudah di login page)
 * - Audit logging
 * - Backward compatible dengan signature v5.x
 */
class Auth
{
    /** @var array Path yang dikecualikan dari intended URL saving */
    private const EXCLUDED_PATHS = ['/login', '/logout', '/register'];

    /**
     * Handle middleware.
     * Dipanggil oleh Router v7.0.
     *
     * @return bool True jika lolos, false jika blocked
     */
    public static function handle(): bool
    {
        $user = Session::get('user');
        $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // ===== CASE 1: Belum login =====
        if ($user === null) {
            self::logAttempt('auth_required_guest');

            if (Security::isAjax() || Security::isJsonRequest()) {
                self::sendJsonResponse(401, 'Autentikasi diperlukan untuk mengakses resource ini.');
                return false;
            }

            // Avoid flash loop jika sudah di login page
            if (!self::isLoginPage($currentPath)) {
                Session::flash('login_error', 'Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.', 'warning');
                self::saveIntendedUrl($currentPath);
            }

            redirect('login');
            return false;
        }

        // ===== CASE 2: Sudah login, cek status active =====
        $isActive = $user['is_active'] ?? (($user['status'] ?? 'active') === 'active');

        if (!$isActive) {
            self::logAttempt('auth_inactive_user', [
                'user_id'  => $user['id'] ?? 0,
                'username' => $user['username'] ?? '',
            ]);

            if (Security::isAjax() || Security::isJsonRequest()) {
                self::sendJsonResponse(403, 'Akun Anda tidak aktif. Hubungi administrator.');
                return false;
            }

            Session::flash('login_error', 'Akun Anda tidak aktif. Silakan hubungi administrator.', 'danger');
            Session::destroy();
            redirect('login');
            return false;
        }

        // ===== CASE 3: Lolos =====
        return true;
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Save intended URL untuk redirect setelah login.
     * Hanya simpan untuk GET request (hindari POST/PUT/DELETE).
     */
    private static function saveIntendedUrl(string $currentPath): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Hanya simpan GET request
        if ($method !== 'GET') return;

        // Skip path yang dikecualikan
        foreach (self::EXCLUDED_PATHS as $excluded) {
            if (str_contains($currentPath, $excluded)) return;
        }

        // Skip AJAX request
        if (Security::isAjax() || Security::isJsonRequest()) return;

        $fullUrl = $_SERVER['REQUEST_URI'] ?? '';
        if ($fullUrl !== '') {
            Session::set('intended_url', $fullUrl);
        }
    }

    /**
     * Cek apakah current path adalah login page.
     */
    private static function isLoginPage(string $path): bool
    {
        return str_contains($path, '/login') || str_contains($path, '/register');
    }

    /**
     * Log percobaan akses unauthorized.
     */
    private static function logAttempt(string $event, array $context = []): void
    {
        try {
            $context['path'] = $_SERVER['REQUEST_URI'] ?? '';
            $context['method'] = $_SERVER['REQUEST_METHOD'] ?? '';
            Security::logEvent($event, $context);
        } catch (\Throwable $e) {
            error_log('[Auth] Log failed: ' . $e->getMessage());
        }
    }

    /**
     * Send JSON response untuk AJAX request.
     */
    private static function sendJsonResponse(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'       => false,
            'message'  => $message,
            'code'     => $code,
            'redirect' => function_exists('url') ? url('login') : '/login',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}