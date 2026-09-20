<?php
// File: app/Middleware/RateLimiter.php (FINAL v7.0 — STATIC-API + MULTI-PROFILE + ROUTER-COMPAT)
declare(strict_types=1);

namespace Middleware;

use Core\Security;

/**
 * RateLimiter Middleware — Ultimate Edition v7.0
 *
 * Rate limiting middleware untuk berbagai use case.
 * Compatible dengan Router v7.0 (static `handle()` pattern).
 *
 * Tersedia 4 profile sebagai class terpisah:
 * - RateLimiter       (default API, 60 req/min)
 * - RateLimitLogin    (login, 5 attempts/5 min)
 * - RateLimitRegister (register, 3 attempts/10 min)
 * - RateLimitForm     (form umum, 10 req/min)
 *
 * Fitur:
 * - Static handle() untuk Router v7.0 compatibility
 * - AJAX-aware response (JSON vs HTML)
 * - Retry-After header (RFC 6585)
 * - Audit logging
 * - Whitelisted IP bypass
 * - Backward compatible dengan signature v5.x (constructor)
 */
class RateLimiter
{
    /* ============================================================
       KONFIGURASI DEFAULT (untuk instance-based, backward compat)
       ============================================================ */

    protected int $maxAttempts = 60;
    protected int $windowSeconds = 60;
    protected string $keyPrefix = 'api';

    /**
     * Constructor (backward compat signature v5.x).
     * Untuk instance-based usage di luar Router.
     */
    public function __construct(string $key = 'api', int $maxAttempts = 60, int $windowSeconds = 60)
    {
        $this->keyPrefix = $key;
        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
    }

    /* ============================================================
       STATIC HANDLE (untuk Router v7.0)
       ============================================================ */

    /**
     * Handle middleware untuk Router v7.0.
     * Default profile: API rate limiting (60 req/min).
     *
     * @return bool True jika lolos, false jika blocked
     */
    public static function handle(): bool
    {
        return static::run(static::getKeyPrefix(), static::getMaxAttempts(), static::getWindowSeconds());
    }

    /* ============================================================
       FACTORY METHODS (backward compat signature v5.x)
       ============================================================ */

    /**
     * Factory: rate limiter untuk login (5 attempts / 5 menit).
     * Backward compat signature v5.x.
     */
    public static function forLogin(): self
    {
        return new self('login', 5, 300);
    }

    /**
     * Factory: rate limiter untuk registration (3 attempts / 10 menit).
     */
    public static function forRegistration(): self
    {
        return new self('register', 3, 600);
    }

    /**
     * Factory: rate limiter untuk form submission (10 req / menit).
     */
    public static function forForm(): self
    {
        return new self('form', 10, 60);
    }

    /**
     * Factory: rate limiter untuk API (60 req / menit).
     */
    public static function forApi(): self
    {
        return new self('api', 60, 60);
    }

    /* ============================================================
       INSTANCE-BASED HANDLE (backward compat)
       ============================================================ */

    /**
     * Instance-based handle (pipeline pattern v5.x).
     * Untuk backward compat dengan kode lama.
     *
     * @param callable $next Next middleware/controller
     * @return mixed Result dari $next()
     */
    public function handlePipeline(callable $next): mixed
    {
        $ip = Security::getClientIp();
        $limitKey = $this->keyPrefix . '_' . $ip;

        if (!Security::rateLimit($limitKey, $this->maxAttempts, $this->windowSeconds)) {
            $this->sendBlockedResponse();
            return null;
        }

        return $next();
    }

    /* ============================================================
       PROTECTED: OVERRIDABLE CONFIG
       ============================================================ */

    /**
     * Get key prefix (bisa di-override subclass).
     */
    protected static function getKeyPrefix(): string
    {
        return 'api';
    }

    /**
     * Get max attempts (bisa di-override subclass).
     */
    protected static function getMaxAttempts(): int
    {
        return 60;
    }

    /**
     * Get window seconds (bisa di-override subclass).
     */
    protected static function getWindowSeconds(): int
    {
        return 60;
    }

    /* ============================================================
       PRIVATE: CORE LOGIC
       ============================================================ */

    /**
     * Run rate limiting logic (static version).
     *
     * @return bool True jika lolos, false jika blocked
     */
    protected static function run(string $keyPrefix, int $maxAttempts, int $windowSeconds): bool
    {
        $ip = Security::getClientIp();

        // Whitelisted IP bypass
        if (Security::isWhitelisted($ip)) {
            return true;
        }

        $limitKey = $keyPrefix . '_' . $ip;

        if (!Security::rateLimit($limitKey, $maxAttempts, $windowSeconds)) {
            // Audit log
            try {
                Security::logEvent('rate_limited', [
                    'key_prefix'    => $keyPrefix,
                    'max_attempts'  => $maxAttempts,
                    'window'        => $windowSeconds,
                    'path'          => $_SERVER['REQUEST_URI'] ?? '',
                ]);
            } catch (\Throwable $e) {
                error_log('[RateLimiter] Log failed: ' . $e->getMessage());
            }

            $retryAfter = Security::getRetryAfter($limitKey, $windowSeconds);
            $remaining = Security::getRemainingAttempts($limitKey, $maxAttempts, $windowSeconds);

            static::sendBlockedResponseStatic($retryAfter, $remaining, $windowSeconds, $maxAttempts);
            return false;
        }

        return true;
    }

    /**
     * Send blocked response (static version, untuk Router v7.0).
     */
    protected static function sendBlockedResponseStatic(
        int $retryAfter = 0,
        int $remaining = 0,
        int $windowSeconds = 60,
        int $maxAttempts = 60,
    ): void {
        http_response_code(429);
        header('Retry-After: ' . max(1, $retryAfter));
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . $remaining);
        header('X-RateLimit-Reset: ' . (time() + $retryAfter));

        if (Security::isAjax() || Security::isJsonRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'          => false,
                'message'     => 'Terlalu banyak percobaan. Silakan coba lagi nanti.',
                'code'        => 429,
                'retry_after' => max(1, $retryAfter),
                'limit'       => $maxAttempts,
                'remaining'   => $remaining,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // HTML response (pakai inline styling, tidak depend on view)
        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Terlalu Banyak Percobaan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            text-align: center;
            max-width: 500px;
            background: rgba(255,255,255,0.05);
            padding: 48px 32px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        h1 {
            color: #f87171;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        h2 {
            font-size: 20px;
            margin-bottom: 16px;
            color: #f1f5f9;
        }
        p {
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .countdown {
            font-size: 32px;
            font-weight: 700;
            color: #60a5fa;
            margin: 24px 0;
            font-variant-numeric: tabular-nums;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 16px;
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⏱️</div>
        <h1>429</h1>
        <h2>Terlalu Banyak Percobaan</h2>
        <p>Anda telah melakukan terlalu banyak permintaan dalam waktu singkat.</p>
        <p>Silakan coba lagi dalam:</p>
        <div class="countdown" id="countdown">' . max(1, $retryAfter) . ' detik</div>
        <a href="/" class="btn">Kembali ke Beranda</a>
    </div>
    <script>
        let seconds = ' . max(1, $retryAfter) . ';
        const el = document.getElementById("countdown");
        const timer = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timer);
                location.reload();
            } else {
                el.textContent = seconds + " detik";
            }
        }, 1000);
    </script>
</body>
</html>';
    }

    /**
     * Send blocked response (instance version, backward compat).
     */
    protected function sendBlockedResponse(): void
    {
        $ip = Security::getClientIp();
        $limitKey = $this->keyPrefix . '_' . $ip;
        $retryAfter = Security::getRetryAfter($limitKey, $this->windowSeconds);
        $remaining = Security::getRemainingAttempts($limitKey, $this->maxAttempts, $this->windowSeconds);

        static::sendBlockedResponseStatic($retryAfter, $remaining, $this->windowSeconds, $this->maxAttempts);
    }
}

/* ============================================================
   SUBCLASS: LOGIN RATE LIMITER
   ============================================================ */

/**
 * Rate limiter khusus untuk login endpoint.
 * 5 attempts per 5 menit.
 */
class RateLimitLogin extends RateLimiter
{
    protected static function getKeyPrefix(): string    { return 'login'; }
    protected static function getMaxAttempts(): int     { return 5; }
    protected static function getWindowSeconds(): int   { return 300; }
}

/* ============================================================
   SUBCLASS: REGISTRATION RATE LIMITER
   ============================================================ */

/**
 * Rate limiter khusus untuk registration endpoint.
 * 3 attempts per 10 menit.
 */
class RateLimitRegister extends RateLimiter
{
    protected static function getKeyPrefix(): string    { return 'register'; }
    protected static function getMaxAttempts(): int     { return 3; }
    protected static function getWindowSeconds(): int   { return 600; }
}

/* ============================================================
   SUBCLASS: FORM RATE LIMITER
   ============================================================ */

/**
 * Rate limiter untuk form submission umum.
 * 10 requests per menit.
 */
class RateLimitForm extends RateLimiter
{
    protected static function getKeyPrefix(): string    { return 'form'; }
    protected static function getMaxAttempts(): int     { return 10; }
    protected static function getWindowSeconds(): int   { return 60; }
}

/* ============================================================
   SUBCLASS: CENSUS SUBMISSION RATE LIMITER
   ============================================================ */

/**
 * Rate limiter untuk form sensus publik.
 * 3 submissions per 10 menit (anti-spam).
 */
class RateLimitCensus extends RateLimiter
{
    protected static function getKeyPrefix(): string    { return 'census'; }
    protected static function getMaxAttempts(): int     { return 3; }
    protected static function getWindowSeconds(): int   { return 600; }
}

/* ============================================================
   SUBCLASS: STRICT API RATE LIMITER
   ============================================================ */

/**
 * Rate limiter ketat untuk endpoint sensitif.
 * 30 requests per menit.
 */
class RateLimitStrict extends RateLimiter
{
    protected static function getKeyPrefix(): string    { return 'strict'; }
    protected static function getMaxAttempts(): int     { return 30; }
    protected static function getWindowSeconds(): int   { return 60; }
}