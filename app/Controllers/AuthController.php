<?php
// File: app/Controllers/AuthController.php (FINAL v7.0 — EXTENDED + SECURE + RESILIENT)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Member;
use Models\Setting;
use Models\User;

/**
 * AuthController — Ultimate Edition v7.0
 *
 * Menangani autentikasi user dengan fitur keamanan modern:
 * - CSRF protection
 * - Password rehash otomatis (algorithm upgrade)
 * - Rate limiting berbasis session (anti brute force)
 * - Login attempt logging (audit trail)
 * - Session fixation prevention
 * - Graceful fallback jika Member/Setting fail
 * - SEO meta & announcement banner dari Setting
 * - Smart redirect (admin → dashboard, member → profil)
 * - Remember me support (extended session)
 * - IP & timestamp tracking
 */
class AuthController
{
    /** Rate limit: max 5 percobaan login gagal per 15 menit */
    private const RATE_LIMIT_MAX = 5;
    private const RATE_LIMIT_WINDOW = 900; // 15 menit dalam detik

    /** Session lifetime untuk "Remember Me" (30 hari) */
    private const REMEMBER_LIFETIME = 2592000; // 30 hari

    /** Default session lifetime (24 jam) */
    private const DEFAULT_LIFETIME = 86400;

    /* ============================================================
       LOGIN PAGE
       ============================================================ */

    /**
     * Tampilkan form login.
     * Redirect jika sudah login.
     */
    public function showLogin(): void
    {
        // Redirect jika sudah login
        if (Session::get('user')) {
            $this->redirectByRole();
            return;
        }

        Setting::loadAll();

        $appName = Setting::get('app_name', 'Organisasi');

        // SEO meta
        $seo = Setting::getSeoMeta();
        $seo['title'] = 'Masuk — ' . $appName;
        if (empty($seo['description'])) {
            $seo['description'] = 'Masuk ke akun ' . $appName . ' untuk mengakses dashboard dan fitur anggota.';
        }
        $seo['og_image'] = Setting::getLogoUrl();
        $seo['og_url'] = function_exists('url') ? url('login') : '/login';

        View::render('pages/login', [
            'title'        => 'Masuk',
            'seo'          => $seo,
            'announcement' => [
                'active' => Setting::isAnnouncementActive(),
                'text'   => Setting::get('announcement_text'),
                'link'   => Setting::get('announcement_link'),
            ],
            'org' => [
                'name'        => $appName,
                'logo_url'    => Setting::getLogoUrl(),
                'favicon_url' => Setting::getFaviconUrl(),
                'motto'       => Setting::get('motto'),
            ],
            'socialLinks' => Setting::getSocialLinks(),
            'currentYear' => (int) date('Y'),
        ], 'layouts/auth');
    }

    /* ============================================================
       PROCESS LOGIN
       ============================================================ */

    /**
     * Proses login dengan keamanan berlapis:
     * 1. CSRF verification
     * 2. Rate limiting check
     * 3. User lookup & password verify
     * 4. Status check (active only)
     * 5. Password rehash if needed
     * 6. Session regeneration (anti-fixation)
     * 7. Session data setup
     * 8. Login attempt logging
     */
    public function processLogin(): void
    {
        // Redirect jika sudah login
        if (Session::get('user')) {
            $this->redirectByRole();
            return;
        }

        // 1. Verifikasi CSRF
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) {
            Session::flash('login_error', 'Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.');
            redirect('login');
            return;
        }

        $login      = trim((string) ($_POST['username'] ?? ''));
        $password   = (string) ($_POST['password'] ?? '');
        $remember   = !empty($_POST['remember']);
        $clientIp   = $this->getClientIp();

        // 2. Rate limiting check
        if ($this->isRateLimited($login, $clientIp)) {
            Session::flash('login_error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.');
            redirect('login');
            return;
        }

        // Validasi input kosong
        if ($login === '' || $password === '') {
            $this->recordFailedAttempt($login, $clientIp, 'empty_input');
            Session::flash('login_error', 'Username dan kata sandi wajib diisi.');
            redirect('login');
            return;
        }

        // 3. Cari user (raw row dengan password)
        $user = null;
        try {
            $user = User::findByLogin($login);
        } catch (\Throwable $e) {
            error_log('[AuthController] User lookup failed: ' . $e->getMessage());
            Session::flash('login_error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            redirect('login');
            return;
        }

        // 4. Verifikasi password
        if ($user === null || empty($user['password']) || !password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($login, $clientIp, 'invalid_credentials');
            Session::flash('login_error', 'Username atau kata sandi yang Anda masukkan salah.');
            redirect('login');
            return;
        }

        // 5. Cek status akun
        $status = $user['status'] ?? User::STATUS_ACTIVE;
        if ($status !== User::STATUS_ACTIVE) {
            $this->recordFailedAttempt($login, $clientIp, 'inactive_account');
            Session::flash('login_error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            redirect('login');
            return;
        }

        // 6. Password rehash otomatis (jika algorithm outdated)
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            try {
                User::updatePassword((int) $user['id'], $password);
            } catch (\Throwable $e) {
                // Non-fatal, lanjutkan login
                error_log('[AuthController] Password rehash failed: ' . $e->getMessage());
            }
        }

        // 7. Cegah Session Fixation: regenerasi ID sesi
        session_regenerate_id(true);

        // Setup session lifetime untuk "Remember Me"
        if ($remember) {
            $this->extendSessionLifetime(self::REMEMBER_LIFETIME);
        } else {
            $this->extendSessionLifetime(self::DEFAULT_LIFETIME);
        }

        // 8. Ambil profile member (graceful fallback)
        $profile = $this->safeCall(
            fn() => Member::findByUserId((int) $user['id']),
            null
        );

        // 9. Simpan data user lengkap ke session
        // Include semua field yang dibutuhkan view profile v7.0
        Session::set('user', [
            'id'             => (int) $user['id'],
            'username'       => $user['username'],
            'name'           => $profile['full_name'] ?? ($user['username'] ?? 'User'),
            'full_name'      => $profile['full_name'] ?? null,
            'email'          => $user['email'] ?? '',
            'role'           => $user['role'] ?? User::ROLE_MEMBER,
            'photo'          => $profile['photo'] ?? ($user['photo'] ?? null),
            'phone'          => $profile['phone'] ?? null,
            'address'        => $profile['address'] ?? null,
            'join_date'      => $profile['join_date'] ?? null,
            'status'         => $user['status'] ?? User::STATUS_ACTIVE,
            'is_admin'       => ($user['role'] ?? '') === User::ROLE_ADMIN,
            'avatar_color'   => $profile['avatar_color'] ?? ($user['avatar_color'] ?? 'grad-1'),
        ]);
        Session::set('login_at', time());
        Session::set('login_ip', $clientIp);
        Session::set('remember_me', $remember);

        // 10. Log successful login (untuk audit trail)
        $this->recordSuccessfulLogin((int) $user['id'], $login, $clientIp);

        // 11. Clear rate limit counter
        $this->clearRateLimit($login, $clientIp);

        // 12. Flash success message
        Session::flash('login_success', 'Selamat datang, ' . ($profile['full_name'] ?? $user['username']) . '!');

        // 13. Smart redirect by role
        $this->redirectByRole();
    }

    /* ============================================================
       LOGOUT
       ============================================================ */

    /**
     * Logout user: destroy session + redirect ke login.
     */
    public function logout(): void
    {
        // Log logout untuk audit
        $user = Session::get('user');
        if ($user && isset($user['id'])) {
            error_log(sprintf(
                '[AuthController] User logout: user_id=%d username=%s ip=%s',
                $user['id'],
                $user['username'] ?? '',
                $this->getClientIp()
            ));
        }

        Session::destroy();

        // Regenerate session ID untuk keamanan
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        session_destroy();

        redirect('login');
    }

    /* ============================================================
       RATE LIMITING
       ============================================================ */

    /**
     * Cek apakah user/IP sudah mencapai rate limit.
     */
    private function isRateLimited(string $login, string $ip): bool
    {
        $key = $this->getRateLimitKey($login, $ip);
        $attempts = $_SESSION['login_attempts'][$key] ?? null;

        if ($attempts === null) return false;

        // Cek apakah masih dalam window
        if (time() - $attempts['first_at'] > self::RATE_LIMIT_WINDOW) {
            // Window sudah lewat, reset
            unset($_SESSION['login_attempts'][$key]);
            return false;
        }

        return $attempts['count'] >= self::RATE_LIMIT_MAX;
    }

    /**
     * Catat percobaan login gagal.
     */
    private function recordFailedAttempt(string $login, string $ip, string $reason): void
    {
        $key = $this->getRateLimitKey($login, $ip);
        $now = time();

        if (!isset($_SESSION['login_attempts'][$key])) {
            $_SESSION['login_attempts'][$key] = [
                'count'    => 0,
                'first_at' => $now,
            ];
        }

        // Reset window jika sudah lewat
        if ($now - $_SESSION['login_attempts'][$key]['first_at'] > self::RATE_LIMIT_WINDOW) {
            $_SESSION['login_attempts'][$key] = [
                'count'    => 0,
                'first_at' => $now,
            ];
        }

        $_SESSION['login_attempts'][$key]['count']++;

        // Log attempt
        error_log(sprintf(
            '[AuthController] Login failed: login=%s ip=%s reason=%s count=%d',
            $login,
            $ip,
            $reason,
            $_SESSION['login_attempts'][$key]['count']
        ));
    }

    /**
     * Clear rate limit setelah login sukses.
     */
    private function clearRateLimit(string $login, string $ip): void
    {
        $key = $this->getRateLimitKey($login, $ip);
        unset($_SESSION['login_attempts'][$key]);
    }

    /**
     * Generate rate limit key berdasarkan login + IP.
     */
    private function getRateLimitKey(string $login, string $ip): string
    {
        return md5(strtolower($login) . '|' . $ip);
    }

    /* ============================================================
       AUDIT LOGGING
       ============================================================ */

    /**
     * Log successful login (untuk audit trail).
     */
    private function recordSuccessfulLogin(int $userId, string $login, string $ip): void
    {
        error_log(sprintf(
            '[AuthController] Login success: user_id=%d login=%s ip=%s time=%s',
            $userId,
            $login,
            $ip,
            date('Y-m-d H:i:s')
        ));

        // Optional: simpan ke database untuk audit trail persisten
        // try {
        //     Database::getInstance()->prepare(
        //         'INSERT INTO login_logs (user_id, ip_address, user_agent, logged_in_at) VALUES (?, ?, ?, NOW())'
        //     )->execute([$userId, $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']);
        // } catch (\Throwable $e) {
        //     error_log('[AuthController] Failed to log login: ' . $e->getMessage());
        // }
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    /**
     * Redirect berdasarkan role user.
     * - Admin → dashboard
     * - Member → profil atau beranda
     */
    private function redirectByRole(): void
    {
        $user = Session::get('user');
        $role = $user['role'] ?? User::ROLE_MEMBER;

        if ($role === User::ROLE_ADMIN) {
            redirect('dashboard');
        } else {
            // Member bisa ke profil mereka atau beranda
            redirect('profil');
        }
    }

    /**
     * Extend session lifetime (untuk "Remember Me").
     */
    private function extendSessionLifetime(int $seconds): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Update cookie lifetime
        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                [
                    'expires'  => time() + $seconds,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly'  => $params['httponly'],
                    'samesite'  => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        // Update session GC maxlifetime
        ini_set('session.gc_maxlifetime', (string) $seconds);
    }

    /**
     * Get client IP (handle proxy).
     */
    private function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', (string) $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Safe call wrapper — graceful fallback.
     *
     * @template T
     * @param callable(): T $callable
     * @param T $fallback
     * @return T
     */
    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            error_log('[AuthController] Safe call failed: ' . $e->getMessage());
            return $fallback;
        }
    }
}