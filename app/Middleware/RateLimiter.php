<?php
declare(strict_types=1);

namespace Middleware;

use Core\Security;

class RateLimiter {
    private int $maxAttempts;
    private int $windowSeconds;
    private string $key;

    public function __construct(string $key, int $maxAttempts = 5, int $windowSeconds = 300) {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
    }

    public function handle(callable $next) {
        $ip = Security::getClientIp();
        $limitKey = $this->key . '_' . $ip;

        if (!Security::rateLimit($limitKey, $this->maxAttempts, $this->windowSeconds)) {
            http_response_code(429);
            
            if (Security::isAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Silakan coba lagi nanti.',
                    'retry_after' => $this->windowSeconds,
                ]);
            } else {
                echo '<!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <title>429 - Too Many Requests</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                        h1 { color: #ef4444; }
                    </style>
                </head>
                <body>
                    <h1>429 - Terlalu Banyak Percobaan</h1>
                    <p>Anda telah melakukan terlalu banyak percobaan dalam waktu singkat.</p>
                    <p>Silakan coba lagi dalam ' . $this->windowSeconds . ' detik.</p>
                </body>
                </html>';
            }
            
            exit;
        }

        return $next();
    }

    public static function forLogin(): self {
        return new self('login', 5, 300); // 5 attempts per 5 minutes
    }

    public static function forRegistration(): self {
        return new self('register', 3, 600); // 3 attempts per 10 minutes
    }

    public static function forForm(): self {
        return new self('form', 10, 60); // 10 attempts per minute
    }

    public static function forApi(): self {
        return new self('api', 60, 60); // 60 requests per minute
    }
}