<?php
// File: app/Core/Router.php
declare(strict_types=1);

namespace Core;

class Router {
    private array $routes = [];

    public function get(string $uri, string $action): void {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, string $action): void {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, string $action): void {
        // Mengubah {id} menjadi regex (?P<id>[^/]+) agar bisa menangkap parameter dinamis
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        $this->routes[] = [
            'method'  => $method,
            'pattern' => '#^' . $pattern . '$#',
            'action'  => $action
        ];
    }

    public function dispatch(): void {
        $requestUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Menghapus base path jika diinstal di sub-folder
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\') {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        $requestUri = rtrim($requestUri, '/');
        if ($requestUri === '') $requestUri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $requestUri, $matches)) {
                // Ekstrak parameter dinamis (misal: 'id' => '12')
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // PERBAIKAN: Panggil aksi, lalu hentikan fungsi TANPA mengembalikan nilai
                $this->callAction($route['action'], $params);
                return;
            }
        }

        $this->abort(404, "Halaman tidak ditemukan");
    }

    private function callAction(string $action, array $params): void {
        list($controllerName, $methodName) = explode('@', $action);
        $controllerClass = "Controllers\\" . $controllerName;

        if (!class_exists($controllerClass)) {
            $this->abort(500, "Controller {$controllerName} tidak ditemukan.");
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $methodName)) {
            $this->abort(500, "Method {$methodName} tidak ada di {$controllerName}.");
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    private function abort(int $code, string $message = ''): void {
        http_response_code($code);
        die("<h1>Error {$code}</h1><p>{$message}</p>");
    }
}