<?php
// File: app/Core/Router.php (FINAL - TAHAP 5.7)
declare(strict_types=1);

namespace Core;

class Router {
    /** @var array<int, array{method:string, uri:string, action:string}> */
    private array $routes = [];

    /** @var string|null Aksi fallback bila tidak ada rute yang cocok (404) */
    private ?string $fallback = null;

    public function get(string $uri, string $action): void {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void {
        $this->add('POST', $uri, $action);
    }

    /** Daftarkan aksi untuk seluruh URL yang tidak cocok (halaman 404 bermerek) */
    public function fallback(string $action): void {
        $this->fallback = $action;
    }

    private function add(string $method, string $uri, string $action): void {
        $this->routes[] = [
            'method' => $method,
            'uri'    => trim($uri, '/'),
            'action' => $action,
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path   = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Buang prefix subfolder instalasi (BASE_URL) dari URL permintaan
        $base = rtrim((string) parse_url(BASE_URL, PHP_URL_PATH), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            $pattern = '#^' . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['uri']) . '$#';
            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, static fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                $this->callAction($route['action'], $params);
                return;
            }
        }

        // Tidak ada rute yang cocok → 404
        http_response_code(404);
        if ($this->fallback !== null) {
            $this->callAction($this->fallback, []);
            return;
        }
        die('<h1>Error 404</h1><p>Halaman tidak ditemukan.</p>');
    }

    private function callAction(string $action, array $params = []): void {
        [$class, $method] = explode('@', $action);
        $class = 'Controllers\\' . $class;
        if (!class_exists($class) || !method_exists($class, $method)) {
            http_response_code(500);
            die('<h1>Error 500</h1><p>Controller atau method tidak ditemukan: ' . htmlspecialchars($action) . '</p>');
        }
        call_user_func_array([new $class(), $method], array_values($params));
    }
}