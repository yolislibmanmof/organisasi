<?php
// File: app/Middleware/AdminOnly.php (FINAL v7.0 — EXTENDED + AUDIT + AJAX-SAFE)
declare(strict_types=1);

namespace Middleware;

use Core\Security;
use Core\Session;

/**
 * AdminOnly Middleware — Ultimate Edition v7.0
 *
 * Authorization guard untuk halaman admin:
 * - Cek role admin (pakai convenience flag `is_admin`)
 * - Support AJAX/JSON response (403 Forbidden)
 * - Flash message untuk non-AJAX
 * - Audit logging percobaan akses
 * - Smart redirect (ke beranda untuk member, login untuk guest)
 * - Backward compatible dengan signature v5.x
 */
class AdminOnly
{
    /**
     * Handle middleware.
     * Dipanggil oleh Router v7.0.
     *
     * @return bool True jika lolos, false jika blocked (response sudah dikirim)
     */
    public static function handle(): bool
    {
        $user = Session::get('user');

        // ===== CASE 1: Belum login =====
        if ($user === null) {
            self::logAttempt('admin_access_guest');

            if (Security::isAjax() || Security::isJsonRequest()) {
                self::sendJsonResponse(401, 'Autentikasi diperlukan.');
                return false;
            }

            Session::flash('login_error', 'Silakan masuk sebagai administrator untuk mengakses halaman tersebut.', 'warning');
            self::saveIntendedUrl();
            redirect('login');
            return false;
        }

        // ===== CASE 2: Sudah login tapi bukan admin =====
        $isAdmin = $user['is_admin'] ?? (($user['role'] ?? '') === 'admin');

        if (!$isAdmin) {
            self::logAttempt('admin_access_denied', [
                'user_id'  => $user['id'] ?? 0,
                'username' => $user['username'] ?? '',
                'role'     => $user['role'] ?? '',
            ]);

            if (Security::isAjax() || Security::isJsonRequest()) {
                self::sendJsonResponse(403, 'Akses ditolak. Halaman ini hanya untuk administrator.');
                return false;
            }

            Session::flash('error_message', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.', 'danger');

            // Redirect ke beranda (bukan dashboard, karena dashboard juga butuh admin)
            redirect('');
            return false;
        }

        // ===== CASE 3: Cek status active =====
        $isActive = $user['is_active'] ?? (($user['status'] ?? 'active') === 'active');

        if (!$isActive) {
            self::logAttempt('admin_access_inactive', [
                'user_id'  => $user['id'] ?? 0,
                'username' => $user['username'] ?? '',
            ]);

            if (Security::isAjax() || Security::isJsonRequest()) {
                self::sendJsonResponse(403, 'Akun Anda tidak aktif.');
                return false;
            }

            Session::flash('error_message', 'Akun Anda tidak aktif. Hubungi administrator.', 'danger');
            Session::destroy();
            redirect('login');
            return false;
        }

        // Lolos semua check
        return true;
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Save intended URL untuk redirect setelah login.
     */
    private static function saveIntendedUrl(): void
    {
        $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
        if ($currentUrl !== '' && !str_contains($currentUrl, '/login')) {
            Session::set('intended_url', $currentUrl);
        }
    }

    /**
     * Log percobaan akses admin.
     */
    private static function logAttempt(string $event, array $context = []): void
    {
        try {
            $context['path'] = $_SERVER['REQUEST_URI'] ?? '';
            $context['method'] = $_SERVER['REQUEST_METHOD'] ?? '';
            Security::logEvent($event, $context);
        } catch (\Throwable $e) {
            // Silent fail — jangan block request karena logging error
            error_log('[AdminOnly] Log failed: ' . $e->getMessage());
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
            'ok'      => false,
            'message' => $message,
            'code'    => $code,
            'redirect' => $code === 401 ? (function_exists('url') ? url('login') : '/login') : null,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}