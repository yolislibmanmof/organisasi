<?php
declare(strict_types=1);

namespace Core;

class Security {
    private static array $rateLimits = [];

    /**
     * Rate limiting untuk mencegah brute force
     */
    public static function rateLimit(string $key, int $maxAttempts = 5, int $windowSeconds = 300): bool {
        $now = time();
        $windowStart = $now - $windowSeconds;

        if (!isset(self::$rateLimits[$key])) {
            self::$rateLimits[$key] = [];
        }

        // Bersihkan attempt lama
        self::$rateLimits[$key] = array_filter(
            self::$rateLimits[$key],
            fn($timestamp) => $timestamp >= $windowStart
        );

        // Cek apakah melebihi limit
        if (count(self::$rateLimits[$key]) >= $maxAttempts) {
            return false;
        }

        // Tambahkan attempt baru
        self::$rateLimits[$key][] = $now;
        return true;
    }

    /**
     * Sanitasi input string
     */
    public static function sanitize(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Sanitasi email
     */
    public static function sanitizeEmail(string $email): string {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validasi email
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitasi URL
     */
    public static function sanitizeUrl(string $url): string {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Validasi URL
     */
    public static function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Sanitasi integer
     */
    public static function sanitizeInt($value): int {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitasi float
     */
    public static function sanitizeFloat($value): float {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Generate random token
     */
    public static function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check password strength
     */
    public static function checkPasswordStrength(string $password): array {
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
            'score' => $score,
            'strength' => $score >= 4 ? 'strong' : ($score >= 3 ? 'good' : ($score >= 2 ? 'fair' : 'weak')),
            'feedback' => $feedback,
        ];
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken(64);
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Regenerate CSRF token
     */
    public static function regenerateCsrfToken(): string {
        $_SESSION['csrf_token'] = self::generateToken(64);
        return $_SESSION['csrf_token'];
    }

    /**
     * Escape output untuk HTML
     */
    public static function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape untuk JavaScript
     */
    public static function escapeJs(string $string): string {
        return json_encode($string, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Escape untuk URL
     */
    public static function escapeUrl(string $string): string {
        return rawurlencode($string);
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                   'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Check if IP is blocked
     */
    public static function isIpBlocked(string $ip): bool {
        // Implementasi blacklist IP bisa ditambahkan di sini
        return false;
    }

    /**
     * Log security event
     */
    public static function logEvent(string $event, array $context = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'context' => $context,
        ];

        $logFile = __DIR__ . '/../../storage/logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        @file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}