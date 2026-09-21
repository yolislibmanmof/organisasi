<?php
/**
 * ORGANISASI v7.0 — FRONT CONTROLLER (FINAL v7.0.1)
 */
declare(strict_types=1);

define('APP_EXEC', true);
define('REQUEST_ID', substr(bin2hex(random_bytes(8)), 0, 16));
define('REQUEST_START', microtime(true));

// 1. MUAT KONFIGURASI GLOBAL
require_once __DIR__ . '/../config/config.php';

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'Asia/Jakarta');
}
date_default_timezone_set(APP_TIMEZONE);

// Maintenance mode
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    http_response_code(503);
    header('Retry-After: 3600');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Maintenance</title>';
    echo '<style>body{font-family:sans-serif;text-align:center;padding:80px;background:#0f172a;color:#e2e8f0}';
    echo 'h1{font-size:48px;margin:0}p{color:#94a3b8}</style></head><body>';
    echo '<h1>🔧 Maintenance</h1><p>Situs sedang dalam pemeliharaan.</p>';
    echo '</body></html>';
    exit;
}

// 2. MUAT FUNGSI BANTUAN
require_once __DIR__ . '/../app/Core/helpers.php';

// 3. PSR-4 AUTOLOADER
spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
        return;
    }

    if (APP_DEBUG) {
        error_log(sprintf('[%s] Class not found: %s (expected: %s)', REQUEST_ID, $class, $file));
    }
});

// 4. SECURITY HEADERS (DIPERBAIKI — UNPKG.COM DITAMBAHKAN)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Request-ID: ' . REQUEST_ID);

if (\Core\Security::isSecure()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');

// CSP DIPERBAIKI: unpkg.com untuk Phosphor Icons, Google Fonts, CDN
$cspDirectives = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
    "img-src 'self' data: https: blob:",
    "font-src 'self' https://fonts.gstatic.com https://unpkg.com",
    "connect-src 'self'",
    "frame-ancestors 'self'",
    "base-uri 'self'",
    "form-action 'self'",
];
header('Content-Security-Policy: ' . implode('; ', $cspDirectives));

header_remove('X-Powered-By');
header_remove('Server');

// 5. GLOBAL EXCEPTION & ERROR HANDLERS
function renderErrorPage(int $code, ?Throwable $exception = null): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    try {
        if (class_exists('\Controllers\ErrorController')) {
            $controller = new \Controllers\ErrorController();
            match ($code) {
                403 => $controller->forbidden(),
                404 => $controller->notFound(),
                419 => $controller->csrfExpired(),
                default => $controller->serverError($exception),
            };
            return;
        }
    } catch (Throwable $e) {
        error_log(sprintf('[%s] ErrorController failed: %s', REQUEST_ID, $e->getMessage()));
    }

    http_response_code($code);
    $messages = [
        403 => 'Akses Ditolak',
        404 => 'Halaman Tidak Ditemukan',
        419 => 'Sesi Kedaluwarsa',
        500 => 'Kesalahan Server',
    ];
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Error ' . $code . '</title>';
    echo '<style>body{font-family:sans-serif;padding:40px;background:#0f172a;color:#e2e8f0}';
    echo 'h1{color:#f87171;font-size:72px;margin:0}p{color:#94a3b8}</style></head><body>';
    echo '<h1>' . $code . '</h1>';
    echo '<p>' . ($messages[$code] ?? 'Terjadi Kesalahan') . '</p>';
    if (APP_DEBUG && $exception !== null) {
        echo '<pre style="background:#1e293b;padding:16px;border-radius:8px;overflow:auto">';
        echo htmlspecialchars($exception->getMessage() . "\n\n" . $exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    }
    echo '</body></html>';
}

set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf(
        '[%s] Uncaught Exception: %s in %s:%d | URL: %s %s | IP: %s',
        REQUEST_ID,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $_SERVER['REQUEST_METHOD'] ?? '?',
        $_SERVER['REQUEST_URI'] ?? '?',
        \Core\Security::getClientIp()
    ));

    renderErrorPage(500, $e);
});

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    error_log(sprintf(
        '[%s] PHP Error [%d]: %s in %s:%d',
        REQUEST_ID, $severity, $message, $file, $line
    ));

    if ($severity & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    return true;
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log(sprintf(
            '[%s] Fatal Error: %s in %s:%d',
            REQUEST_ID, $error['message'], $error['file'], $error['line']
        ));

        if (!headers_sent()) {
            renderErrorPage(500);
        }
    }
});

// 6. CACHE INITIALIZATION
try {
    \Core\Cache::init();
} catch (Throwable $e) {
    error_log(sprintf('[%s] Cache init failed: %s', REQUEST_ID, $e->getMessage()));
    \Core\Cache::disable();
}

// 7. SESSION INITIALIZATION
try {
    \Core\Session::start();
} catch (Throwable $e) {
    error_log(sprintf('[%s] Session start failed: %s', REQUEST_ID, $e->getMessage()));

    if (APP_DEBUG && !headers_sent()) {
        header('X-Session-Error: ' . $e->getMessage());
    }
}

// 8. OUTPUT BUFFERING
ob_start();

// 9. DATABASE QUERY LOGGING
if (APP_DEBUG && class_exists('\Core\Database')) {
    try {
        \Core\Database::enableQueryLog(true);
    } catch (Throwable $e) {
        // Silent
    }
}

// 10. ROUTER INITIALIZATION
$router = new Core\Router();
require_once __DIR__ . '/../config/routes.php';

// 11. ROUTER DISPATCH
try {
    $router->dispatch();
} catch (Throwable $e) {
    error_log(sprintf(
        '[%s] Router dispatch failed: %s',
        REQUEST_ID, $e->getMessage()
    ));
    renderErrorPage(500, $e);
}

// 12. SHUTDOWN HANDLER
register_shutdown_function(static function (): void {
    try {
        if (class_exists('\Core\Session') && session_status() === PHP_SESSION_ACTIVE) {
            \Core\Session::ageFlashData();
        }
    } catch (Throwable $e) {
        // Silent
    }

    $executionTime = (microtime(true) - REQUEST_START) * 1000;

    if ($executionTime > 1000 || APP_DEBUG) {
        $queryCount = class_exists('\Core\Database') ? \Core\Database::getQueryCount() : 0;
        $queryTime = class_exists('\Core\Database') ? \Core\Database::getTotalQueryTime() : 0;
        $memoryUsage = round(memory_get_peak_usage(true) / 1048576, 2);

        error_log(sprintf(
            '[%s] Request completed: %s %s | %.2fms | %d queries (%.2fms) | %.2fMB | IP: %s',
            REQUEST_ID,
            $_SERVER['REQUEST_METHOD'] ?? '?',
            $_SERVER['REQUEST_URI'] ?? '?',
            $executionTime,
            $queryCount,
            $queryTime,
            $memoryUsage,
            \Core\Security::getClientIp()
        ));

        if (APP_DEBUG && $queryCount > 0) {
            $slowQueries = array_filter(
                \Core\Database::getQueryLog(),
                fn($q) => ($q['time'] ?? 0) > 100
            );
            if (!empty($slowQueries)) {
                error_log(sprintf(
                    '[%s] Slow queries detected: %d',
                    REQUEST_ID, count($slowQueries)
                ));
            }
        }
    }

    if (ob_get_level() > 0) {
        ob_end_flush();
    }
});