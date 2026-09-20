<?php
// File: app/Core/Session.php (FINAL v7.0 — EXTENDED + SECURE + FLEXIBLE)
declare(strict_types=1);

namespace Core;

/**
 * Session Manager — Ultimate Edition v7.0
 *
 * Secure session handling dengan fitur:
 * - Flash messages (string, array, structured data)
 * - Flash now (current request only)
 * - Session regeneration helper
 * - Configurable lifetime (support Remember Me)
 * - Auto-detect HTTPS
 * - Session fingerprint (user agent binding)
 * - Pull (get + delete)
 * - Backward compatible dengan signature v5.x
 */
class Session
{
    /** Default session lifetime (24 jam) */
    private const DEFAULT_LIFETIME = 86400;

    /** Remember me lifetime (30 hari) */
    private const REMEMBER_LIFETIME = 2592000;

    /** @var bool Flag apakah sudah start */
    private static bool $started = false;

    /* ============================================================
       SESSION LIFECYCLE
       ============================================================ */

    /**
     * Start session dengan konfigurasi aman.
     * Backward compat signature v5.x.
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            // Security settings
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_httponly', '1');

            // Auto-detect HTTPS
            $isSecure = self::isSecure();

            session_set_cookie_params([
                'lifetime' => self::DEFAULT_LIFETIME,
                'path'     => '/',
                'domain'   => self::getCookieDomain(),
                'secure'   => $isSecure,
                'httponly'  => true,
                'samesite'  => 'Lax',
            ]);

            session_start();
            self::$started = true;

            // Validate session fingerprint
            self::validateFingerprint();
        }
    }

    /**
     * Regenerate session ID (untuk post-login).
     *
     * @param bool $deleteOldSession Hapus session lama
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
            // Reset fingerprint untuk session baru
            self::setFingerprint();
        }
    }

    /**
     * Destroy session sepenuhnya.
     * Backward compat signature v5.x.
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            // Hapus cookie session
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    [
                        'expires'  => time() - 42000,
                        'path'     => $params['path'],
                        'domain'   => $params['domain'],
                        'secure'   => $params['secure'],
                        'httponly'  => $params['httponly'],
                        'samesite'  => $params['samesite'] ?? 'Lax',
                    ]
                );
            }

            session_destroy();
        }
        self::$started = false;
    }

    /**
     * Extend session lifetime (untuk Remember Me).
     *
     * @param int $seconds Lifetime dalam detik
     */
    public static function extendLifetime(int $seconds): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return;

        // Update GC maxlifetime
        ini_set('session.gc_maxlifetime', (string) $seconds);

        // Update cookie lifetime
        $params = session_get_cookie_params();
        $isSecure = self::isSecure();

        setcookie(
            session_name(),
            session_id(),
            [
                'expires'  => time() + $seconds,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $isSecure,
                'httponly'  => true,
                'samesite'  => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    /**
     * Set lifetime ke "Remember Me" (30 hari).
     */
    public static function rememberMe(): void
    {
        self::extendLifetime(self::REMEMBER_LIFETIME);
    }

    /* ============================================================
       BASIC GET/SET
       ============================================================ */

    /**
     * Set session value.
     * Backward compat signature v5.x.
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value dengan default.
     * Backward compat signature v5.x.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Cek apakah key ada di session.
     */
    public static function has(string $key): bool
    {
        self::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Delete session key.
     * Backward compat signature v5.x.
     */
    public static function delete(string $key): void
    {
        self::ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Get value lalu delete (pull).
     * Berguna untuk one-time reads.
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::delete($key);
        return $value;
    }

    /**
     * Get semua session data.
     */
    public static function all(): array
    {
        self::ensureStarted();
        return $_SESSION;
    }

    /**
     * Clear semua session data (tapi keep session active).
     */
    public static function flush(): void
    {
        self::ensureStarted();
        $_SESSION = [];
    }

    /* ============================================================
       FLASH MESSAGES (EXTENDED)
       ============================================================ */

    /**
     * Set flash message untuk next request.
     *
     * Backward compat signature v5.x:
     * Session::flash('login_error', 'Pesan error', 'danger')
     *
     * Extended v7.0 - support structured data:
     * Session::flash('sensus_err', ['message' => '...', 'errors' => [...]])
     *
     * @param string $name Flash key
     * @param string|array<string, mixed> $message Message atau structured data
     * @param string $class CSS class (untuk backward compat, diabaikan jika $message array)
     */
    public static function flash(string $name, string|array $message, string $class = 'success'): void
    {
        self::ensureStarted();

        // Jangan override flash yang sudah ada (prevent double-set)
        if (isset($_SESSION['_flash'][$name])) {
            return;
        }

        if (is_array($message)) {
            // Structured flash (v7.0)
            $_SESSION['_flash'][$name] = $message;
        } else {
            // Legacy string flash (backward compat)
            $_SESSION['_flash'][$name] = [
                'message' => $message,
                'class'   => $class,
            ];
        }
    }

    /**
     * Set flash untuk current request only (tidak persist ke next request).
     * Berguna untuk immediate feedback dalam request yang sama.
     */
    public static function flashNow(string $name, string|array $message, string $class = 'success'): void
    {
        self::ensureStarted();

        if (is_array($message)) {
            $_SESSION['_flash_now'][$name] = $message;
        } else {
            $_SESSION['_flash_now'][$name] = [
                'message' => $message,
                'class'   => $class,
            ];
        }
    }

    /**
     * Get flash message dan delete (consume).
     *
     * Backward compat: return ?array (bisa null jika tidak ada)
     * v7.0: return array yang konsisten dengan struktur ['message' => ..., 'class' => ...]
     *
     * @return array<string, mixed>|null
     */
    public static function getFlash(string $name): ?array
    {
        self::ensureStarted();

        // Cek flash_now dulu (current request)
        if (isset($_SESSION['_flash_now'][$name])) {
            $flash = $_SESSION['_flash_now'][$name];
            unset($_SESSION['_flash_now'][$name]);
            return is_array($flash) ? $flash : ['message' => $flash, 'class' => 'info'];
        }

        // Cek flash (next request)
        if (isset($_SESSION['_flash'][$name])) {
            $flash = $_SESSION['_flash'][$name];
            unset($_SESSION['_flash'][$name]);
            return is_array($flash) ? $flash : ['message' => $flash, 'class' => 'info'];
        }

        return null;
    }

    /**
     * Peek flash tanpa delete.
     * Berguna untuk cek keberadaan flash tanpa consume.
     */
    public static function peekFlash(string $name): ?array
    {
        self::ensureStarted();

        if (isset($_SESSION['_flash_now'][$name])) {
            $flash = $_SESSION['_flash_now'][$name];
            return is_array($flash) ? $flash : ['message' => $flash, 'class' => 'info'];
        }

        if (isset($_SESSION['_flash'][$name])) {
            $flash = $_SESSION['_flash'][$name];
            return is_array($flash) ? $flash : ['message' => $flash, 'class' => 'info'];
        }

        return null;
    }

    /**
     * Cek apakah flash ada (tanpa consume).
     */
    public static function hasFlash(string $name): bool
    {
        self::ensureStarted();
        return isset($_SESSION['_flash'][$name]) || isset($_SESSION['_flash_now'][$name]);
    }

    /**
     * Keep flash untuk request berikutnya (re-flash).
     * Berguna saat redirect chain.
     */
    public static function reflash(): void
    {
        self::ensureStarted();
        if (isset($_SESSION['_flash'])) {
            // Flash sudah di-set untuk next request, tidak perlu apa-apa
            return;
        }
    }

    /**
     * Keep specific flash key untuk next request.
     */
    public static function keep(string $name): void
    {
        self::ensureStarted();
        // Jika ada di flash_now, pindahkan ke flash
        if (isset($_SESSION['_flash_now'][$name])) {
            $_SESSION['_flash'][$name] = $_SESSION['_flash_now'][$name];
            unset($_SESSION['_flash_now'][$name]);
        }
    }

    /**
     * Age flash data (panggil di akhir request).
     * Pindahkan flash_now ke trash, flash tetap untuk next request.
     * Biasanya dipanggil otomatis oleh framework di shutdown.
     */
    public static function ageFlashData(): void
    {
        self::ensureStarted();
        // Hapus flash_now (sudah dipakai di current request)
        unset($_SESSION['_flash_now']);
    }

    /* ============================================================
       SECURITY: FINGERPRINT
       ============================================================ */

    /**
     * Set session fingerprint (bind ke user agent).
     */
    private static function setFingerprint(): void
    {
        $_SESSION['_fingerprint'] = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . session_id());
    }

    /**
     * Validate session fingerprint.
     * Jika tidak match, regenerate session (anti-hijack).
     */
    private static function validateFingerprint(): void
    {
        if (!isset($_SESSION['_fingerprint'])) {
            self::setFingerprint();
            return;
        }

        $expected = hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . session_id());
        if (!hash_equals($_SESSION['_fingerprint'], $expected)) {
            // Fingerprint mismatch - possible session hijack
            error_log('[Session] Fingerprint mismatch - regenerating session');
            self::regenerate(true);
            self::setFingerprint();
        }
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    /**
     * Ensure session sudah start.
     */
    private static function ensureStarted(): void
    {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            self::start();
        }
    }

    /**
     * Detect apakah request via HTTPS.
     */
    private static function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            return true;
        }
        if ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443) {
            return true;
        }
        return false;
    }

    /**
     * Get cookie domain (strip port dari HTTP_HOST).
     */
    private static function getCookieDomain(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Strip port jika ada (example.com:8080 → example.com)
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }
        // Jangan set domain untuk localhost atau IP
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return '';
        }
        return $host;
    }
}