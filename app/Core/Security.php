<?php
// File: app/Core/Security.php (FINAL v7.0 — EXTENDED + PERSISTENT + OBSERVABLE)
declare(strict_types=1);

namespace Core;

/**
 * Security Helper — Ultimate Edition v7.0
 *
 * Centralized security utilities:
 * - Persistent rate limiting (session-based, bukan in-memory)
 * - IP detection dengan fallback handling
 * - Security event logging dengan rotation
 * - Input sanitization
 * - Password hashing & strength check
 * - CSRF (delegated ke Session class)
 * - Token generation
 * - Backward compatible dengan signature v5.x
 */
class Security
{
    /** @var array IP whitelist (never rate-limited) */
    private static array $ipWhitelist = ['127.0.0.1', '::1'];

    /** @var int Max log file size sebelum rotation (10 MB) */
    private const MAX_LOG_SIZE = 10485760;

    /** @var int Max jumlah rotated log files */
    private const MAX_LOG_FILES = 5;

    /* ============================================================
       RATE LIMITING (PERSISTENT - SESSION-BASED)
       ============================================================ */

    /**
     * Rate limiting dengan session-based storage.
     * Backward compat signature v5.x.
     *
     * @param string $key Unique key (IP, user_id, endpoint)
     * @param int $maxAttempts Maximum attempts dalam window
     * @param int $windowSeconds Window time (default 5 menit)
     * @return bool True jika diizinkan, false jika rate-limited
     */
    public static function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool
    {
        // Skip untuk whitelisted IP
        if (self::isWhitelisted(self::getClientIp())) {
            return true;
        }

        $now = time();
        $windowStart = $now - $windowSeconds;
        $sessionKey = '_rate_limits';

        // Ambil attempts dari session
        $attempts = $_SESSION[$sessionKey][$key] ?? [];

        // Bersihkan attempt lama
        $attempts = array_filter($attempts, fn($ts) => $ts >= $windowStart);

        // Cek limit
        if (count($attempts) >= $maxAttempts) {
            self::logEvent('rate_limit_exceeded', [
                'key'           => $key,
                'attempts'      => count($attempts),
                'max_attempts'  => $maxAttempts,
                'window'        => $windowSeconds,
            ]);
            return false;
        }

        // Tambahkan attempt baru
        $attempts[] = $now;
        $_SESSION[$sessionKey][$key] = $attempts;

        return true;
    }

    /**
     * Clear rate limit untuk key tertentu (setelah sukses login, dll).
     */
    public static function clearRateLimit(string $key): void
    {
        unset($_SESSION['_rate_limits'][$key]);
    }

    /**
     * Clear semua rate limits (logout, dll).
     */
    public static function clearAllRateLimits(): void
    {
        unset($_SESSION['_rate_limits']);
    }

    /**
     * Get remaining attempts untuk key.
     */
    public static function getRemainingAttempts(string $key, int $maxAttempts = 5, int $windowSeconds = 300): int
    {
        $now = time();
        $windowStart = $now - $windowSeconds;
        $attempts = $_SESSION['_rate_limits'][$key] ?? [];
        $attempts = array_filter($attempts, fn($ts) => $ts >= $windowStart);

        return max(0, $maxAttempts - count($attempts));
    }

    /**
     * Get seconds until rate limit reset.
     */
    public static function getRetryAfter(string $key, int $windowSeconds = 300): int
    {
        $attempts = $_SESSION['_rate_limits'][$key] ?? [];
        if (empty($attempts)) return 0;

        $oldest = min($attempts);
        $retryAt = $oldest + $windowSeconds;
        $now = time();

        return max(0, $retryAt - $now);
    }

    /**
     * Add IP ke whitelist (never rate-limited).
     */
    public static function whitelistIp(string $ip): void
    {
        if (!in_array($ip, self::$ipWhitelist, true)) {
            self::$ipWhitelist[] = $ip;
        }
    }

    /**
     * Check apakah IP ada di whitelist.
     */
    public static function isWhitelisted(string $ip): bool
    {
        return in_array($ip, self::$ipWhitelist, true);
    }

    /* ============================================================
       INPUT SANITIZATION
       ============================================================ */

    /**
     * Sanitasi string (htmlspecialchars + trim + stripslashes).
     * Backward compat signature v5.x.
     */
    public static function sanitize(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitasi email.
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: '';
    }

    /**
     * Validasi email.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitasi URL.
     */
    public static function sanitizeUrl(string $url): string
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL) ?: '';
    }

    /**
     * Validasi URL.
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Sanitasi integer.
     */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitasi float.
     */
    public static function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitasi untuk filename (prevent directory traversal).
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Hapus path traversal
        $filename = basename($filename);
        // Hanya alphanumeric, dash, underscore, dot
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) ?: 'file';
    }

    /* ============================================================
       TOKEN GENERATION
       ============================================================ */

    /**
     * Generate random token (hex).
     * Backward compat signature v5.x.
     */
    public static function generateToken(int $length = 32): string
    {
        $length = max(2, $length);
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }

    /**
     * Generate URL-safe token (base64url).
     */
    public static function generateUrlSafeToken(int $length = 32): string
    {
        return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
    }

    /* ============================================================
       PASSWORD HASHING
       ============================================================ */

    /**
     * Hash password dengan bcrypt cost 12.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Verify password terhadap hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check apakah password perlu rehash (algorithm upgrade).
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Check password strength.
     * Backward compat signature v5.x.
     *
     * @return array{score: int, strength: string, feedback: array<string>}
     */
    public static function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        if (strlen($password) >= 8) $score++;
        else $feedback[] = 'Minimal 8 karakter';

        if (preg_match('/[A-Z]/', $password)) $score++;
        else $feedback[] = 'Minimal 1 huruf besar';

        if (preg_match('/[a-z]/', $password)) $score++;
        else $feedback[] = 'Minimal 1 huruf kecil';

        if (preg_match('/[0-9]/', $password)) $score++;
        else $feedback[] = 'Minimal 1 angka';

        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
        else $feedback[] = 'Minimal 1 karakter spesial';

        return [
            'score'    => $score,
            'strength' => $score >= 4 ? 'strong' : ($score >= 3 ? 'good' : ($score >= 2 ? 'fair' : 'weak')),
            'feedback' => $feedback,
        ];
    }

    /* ============================================================
       CSRF (DELEGATED KE Session CLASS)
       ============================================================ */

    /**
     * Generate CSRF token (delegate ke Session).
     */
    public static function generateCsrfToken(): string
    {
        return csrf_token();
    }

    /**
     * Verify CSRF token (delegate ke Session).
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return csrf_verify($token);
    }

    /**
     * Regenerate CSRF token.
     */
    public static function regenerateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        Session::set($tokenName, $token);
        return $token;
    }

    /* ============================================================
       OUTPUT ESCAPING
       ============================================================ */

    /**
     * Escape untuk HTML (alias untuk `e()`).
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape untuk JavaScript (JSON encode).
     */
    public static function escapeJs(string $string): string
    {
        return json_encode($string, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '""';
    }

    /**
     * Escape untuk URL (rawurlencode).
     */
    public static function escapeUrl(string $string): string
    {
        return rawurlencode($string);
    }

    /* ============================================================
       REQUEST DETECTION
       ============================================================ */

    /**
     * Check apakah request AJAX.
     */
    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check apakah request JSON (Accept header).
     */
    public static function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }

    /**
     * Check apakah HTTPS.
     */
    public static function isSecure(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
        if ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443) return true;
        return false;
    }

    /* ============================================================
       IP DETECTION
       ============================================================ */

    /**
     * Get client IP address (proxy-aware dengan fallback aman).
     * Backward compat signature v5.x, tapi dengan logic lebih robust.
     */
    public static function getClientIp(): string
    {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipKeys as $key) {
            if (!isset($_SERVER[$key])) continue;

            $ips = explode(',', (string) $_SERVER[$key]);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                // Validasi format IP (IPv4 atau IPv6)
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get client User-Agent.
     */
    public static function getUserAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 500);
    }

    /**
     * Check apakah IP diblokir (bisa di-extend dengan database).
     * Backward compat signature v5.x.
     */
    public static function isIpBlocked(string $ip): bool
    {
        // Default: tidak ada blok.
        // Bisa di-extend dengan query ke tabel `blocked_ips` atau file config.
        return false;
    }

    /* ============================================================
       SECURITY EVENT LOGGING (DENGAN ROTATION)
       ============================================================ */

    /**
     * Log security event ke file (JSON lines format dengan rotation).
     * Backward compat signature v5.x.
     *
     * @param string $event Event name (login_failed, csrf_invalid, dll)
     * @param array<string, mixed> $context Context data
     */
    public static function logEvent(string $event, array $context = []): void
    {
        $logEntry = [
            'timestamp'  => date('Y-m-d\TH:i:sP'),
            'event'      => $event,
            'ip'         => self::getClientIp(),
            'user_agent' => self::getUserAgent(),
            'context'    => $context,
        ];

        $logDir = self::getLogDir();
        if ($logDir === null) return; // Gagal buat dir, silent fail

        $logFile = $logDir . '/security.log';

        // Log rotation jika file terlalu besar
        self::rotateLogIfNeeded($logFile);

        @file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Get log directory path (portable).
     */
    private static function getLogDir(): ?string
    {
        if (function_exists('storage_path')) {
            $dir = storage_path('logs');
        } else {
            $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
            $dir = $base . '/storage/logs';
        }

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return null;
            }
        }

        return $dir;
    }

    /**
     * Rotate log file jika melebihi MAX_LOG_SIZE.
     */
    private static function rotateLogIfNeeded(string $logFile): void
    {
        if (!is_file($logFile)) return;
        if (@filesize($logFile) < self::MAX_LOG_SIZE) return;

        // Rotate: security.log → security.log.1 → .2 → ... → delete oldest
        for ($i = self::MAX_LOG_FILES - 1; $i >= 1; $i--) {
            $from = $logFile . '.' . $i;
            $to = $logFile . '.' . ($i + 1);
            if (is_file($from)) {
                @rename($from, $to);
            }
        }

        @rename($logFile, $logFile . '.1');

        // Hapus file tertua jika melebihi MAX_LOG_FILES
        $oldest = $logFile . '.' . (self::MAX_LOG_FILES + 1);
        if (is_file($oldest)) {
            @unlink($oldest);
        }
    }

    /**
     * Get recent security events (untuk admin dashboard).
     *
     * @param int $limit Max events
     * @return array<int, array>
     */
    public static function getRecentEvents(int $limit = 50): array
    {
        $logDir = self::getLogDir();
        if ($logDir === null) return [];

        $logFile = $logDir . '/security.log';
        if (!is_file($logFile)) return [];

        $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return [];

        $events = [];
        $lines = array_slice($lines, -$limit); // Ambil N terakhir

        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $events[] = $decoded;
            }
        }

        return array_reverse($events);
    }
}