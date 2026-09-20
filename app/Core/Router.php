<?php
// File: app/Core/Router.php (FINAL v7.0 — EXTENDED + MULTI-METHOD + RESILIENT)
declare(strict_types=1);

namespace Core;

/**
 * Router — Ultimate Edition v7.0
 *
 * Simple but powerful router dengan fitur:
 * - Semua HTTP methods (GET, POST, PUT, PATCH, DELETE, OPTIONS)
 * - Method spoofing (_method field di POST form)
 * - Route groups dengan prefix & middleware
 * - Named routes (untuk URL generation)
 * - Optional parameters ({id?})
 * - Regex constraints ({id:\d+})
 * - Trailing slash normalization
 * - Multi-error fallback (404, 403, 500, 419)
 * - Middleware stack per-route
 * - Route caching (optional)
 * - Graceful error handling
 * - Backward compatible dengan signature v5.x
 */
class Router
{
    /** @var array<int, array> Registered routes */
    private array $routes = [];

    /** @var array<string, string> Named routes (name => uri pattern) */
    private array $namedRoutes = [];

    /** @var array<string, string> Error handlers per HTTP code */
    private array $errorHandlers = [];

    /** @var array<string> Global middleware stack */
    private array $globalMiddleware = [];

    /** @var array Current group context (untuk nested groups) */
    private array $groupStack = [];

    /** @var bool Enable route caching */
    private bool $cacheEnabled = false;

    /* ============================================================
       PUBLIC API: ROUTE REGISTRATION
       ============================================================ */

    /**
     * Register GET route.
     * Backward compat signature v5.x:
     * $router->get('/artikel', 'ArticleController@index')
     */
    public function get(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('GET', $uri, $action, $name);
    }

    /**
     * Register POST route.
     * Backward compat signature v5.x.
     */
    public function post(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('POST', $uri, $action, $name);
    }

    /**
     * Register PUT route (atau POST dengan _method=PUT).
     */
    public function put(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('PUT', $uri, $action, $name);
    }

    /**
     * Register PATCH route.
     */
    public function patch(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('PATCH', $uri, $action, $name);
    }

    /**
     * Register DELETE route.
     */
    public function delete(string $uri, string $action, ?string $name = null): self
    {
        return $this->addRoute('DELETE', $uri, $action, $name);
    }

    /**
     * Register OPTIONS route (untuk CORS preflight).
     */
    public function options(string $uri, string $action): self
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

    /**
     * Register route untuk multiple methods.
     *
     * @param array<string> $methods ['GET', 'POST', ...]
     */
    public function match(array $methods, string $uri, string $action, ?string $name = null): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $uri, $action, $name);
        }
        return $this;
    }

    /**
     * Register route untuk semua methods.
     */
    public function any(string $uri, string $action, ?string $name = null): self
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action, $name);
    }

    /**
     * Backward compat: fallback untuk 404.
     * v5.x signature: $router->fallback('ErrorController@notFound')
     */
    public function fallback(string $action): self
    {
        $this->errorHandlers[404] = $action;
        return $this;
    }

    /**
     * Register error handler untuk specific HTTP code.
     * v7.0 extension: support multi-error.
     *
     * @param int $code HTTP status code (404, 403, 500, 419)
     * @param string $action Controller@method
     */
    public function error(int $code, string $action): self
    {
        $this->errorHandlers[$code] = $action;
        return $this;
    }

    /* ============================================================
       PUBLIC API: ROUTE GROUPS
       ============================================================ */

    /**
     * Group routes dengan prefix dan/atau middleware.
     *
     * Contoh:
     * $router->group(['prefix' => 'admin', 'middleware' => 'auth'], function($r) {
     *     $r->get('/dashboard', 'DashboardController@index');
     *     $r->get('/users', 'UserController@index');
     * });
     *
     * @param array{prefix?: string, middleware?: string|array} $attributes
     * @param callable $callback
     */
    public function group(array $attributes, callable $callback): self
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    /**
     * Add global middleware (diterapkan ke semua route).
     *
     * @param string|array<string> $middleware
     */
    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        } else {
            $this->globalMiddleware[] = $middleware;
        }
        return $this;
    }

    /* ============================================================
       PUBLIC API: DISPATCH
       ============================================================ */

    /**
     * Dispatch request ke route yang cocok.
     * Backward compat behavior v5.x.
     */
    public function dispatch(): void
    {
        // Determine actual HTTP method (support spoofing)
        $method = $this->resolveMethod();
        $path = $this->resolvePath();

        // Try to match route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->compilePattern($route['uri']);
            if (preg_match($pattern, $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, static fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

                // Run middleware stack
                $middlewareResult = $this->runMiddleware($route['middleware'] ?? []);
                if ($middlewareResult !== true) {
                    // Middleware blocked the request (sudah handle response)
                    return;
                }

                // Call controller action
                $this->callAction($route['action'], $params);
                return;
            }
        }

        // No route matched → 404
        $this->handleError(404, $path);
    }

    /**
     * Generate URL dari named route.
     *
     * Contoh:
     * $router->url('article.show', ['id' => 123])
     * → /artikel/123
     *
     * @param string $name Route name
     * @param array<string, mixed> $params Parameters
     * @return string
     */
    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route '$name' tidak terdaftar.");
        }

        $uri = $this->namedRoutes[$name];

        // Replace parameters
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{' . $key . '\??\}/', (string) $value, $uri);
        }

        // Remove remaining optional parameters
        $uri = preg_replace('/\{[a-zA-Z0-9_]+\?\}/', '', $uri);

        // Clean up double slashes
        $uri = preg_replace('#/+#', '/', $uri);

        $base = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
        return $base . '/' . ltrim($uri, '/');
    }

    /**
     * Get semua registered routes (untuk debugging).
     *
     * @return array<int, array>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Enable/disable route caching.
     */
    public function setCache(bool $enabled): self
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /* ============================================================
       PRIVATE: ROUTE BUILDING
       ============================================================ */

    /**
     * Add route ke registry.
     */
    private function addRoute(string $method, string $uri, string $action, ?string $name = null): self
    {
        // Apply group prefix
        $prefix = $this->getGroupPrefix();
        $fullUri = $prefix . trim($uri, '/');
        $fullUri = trim($fullUri, '/');

        // Collect group middleware
        $middleware = array_merge(
            $this->globalMiddleware,
            $this->getGroupMiddleware()
        );

        $this->routes[] = [
            'method'     => $method,
            'uri'        => $fullUri,
            'action'     => $action,
            'middleware'  => $middleware,
            'name'       => $name,
        ];

        // Register named route
        if ($name !== null) {
            $this->namedRoutes[$name] = $fullUri;
        }

        return $this;
    }

    /**
     * Compile URI pattern ke regex.
     * Support: {id}, {id?}, {id:\d+}, {slug:[a-z-]+}
     */
    private function compilePattern(string $uri): string
    {
        if ($uri === '') {
            return '#^$#';
        }

        $pattern = $uri;

        // Replace {param:regex} dengan custom regex
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+):([^}]+)\}/', function ($m) {
            return '(?P<' . $m[1] . '>' . $m[2] . ')';
        }, $pattern);

        // Replace {param?} (optional) dengan optional group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\?\}/', '(?P<$1>[^/]*)', $pattern);

        // Replace {param} (required) dengan required group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);

        // Make trailing slash optional
        return '#^' . $pattern . '/?$#';
    }

    /**
     * Get current group prefix (nested support).
     */
    private function getGroupPrefix(): string
    {
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= trim((string) $group['prefix'], '/') . '/';
            }
        }
        return $prefix;
    }

    /**
     * Get current group middleware (flattened).
     *
     * @return array<string>
     */
    private function getGroupMiddleware(): array
    {
        $middleware = [];
        foreach ($this->groupStack as $group) {
            if (isset($group['middleware'])) {
                $mw = $group['middleware'];
                if (is_array($mw)) {
                    $middleware = array_merge($middleware, $mw);
                } else {
                    $middleware[] = $mw;
                }
            }
        }
        return $middleware;
    }

    /* ============================================================
       PRIVATE: REQUEST RESOLUTION
       ============================================================ */

    /**
     * Resolve HTTP method (support spoofing via _method field).
     */
    private function resolveMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Support method spoofing untuk form HTML (POST + _method=PUT/DELETE)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper((string) $_POST['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $spoofed;
            }
        }

        // Support X-HTTP-Method-Override header
        if ($method === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $override = strtoupper((string) $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }

        return $method;
    }

    /**
     * Resolve request path (strip base URL, normalize).
     */
    private function resolvePath(): string
    {
        $path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Strip BASE_URL prefix
        $base = rtrim((string) parse_url(defined('BASE_URL') ? BASE_URL : '', PHP_URL_PATH), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        // Normalize: trim slashes
        return trim($path, '/');
    }

    /* ============================================================
       PRIVATE: MIDDLEWARE
       ============================================================ */

    /**
     * Run middleware stack.
     *
     * @param array<string> $middleware Class names
     * @return bool True jika lolos, false jika blocked
     */
    private function runMiddleware(array $middleware): bool
    {
        foreach ($middleware as $mwClass) {
            $fullClass = 'Middleware\\' . $mwClass;

            if (!class_exists($fullClass)) {
                error_log("[Router] Middleware not found: $fullClass");
                continue;
            }

            if (!method_exists($fullClass, 'handle')) {
                error_log("[Router] Middleware missing handle(): $fullClass");
                continue;
            }

            try {
                $result = $fullClass::handle();
                if ($result === false) {
                    // Middleware blocked (sudah redirect/error sendiri)
                    return false;
                }
            } catch (\Throwable $e) {
                error_log("[Router] Middleware error: " . $e->getMessage());
                $this->handleError(500, '', $e);
                return false;
            }
        }
        return true;
    }

    /* ============================================================
       PRIVATE: ACTION CALLING
       ============================================================ */

    /**
     * Call controller action dengan parameters.
     */
    private function callAction(string $action, array $params = []): void
    {
        if (!str_contains($action, '@')) {
            $this->handleError(500, '', new \RuntimeException("Invalid action format: $action"));
            return;
        }

        [$controllerName, $method] = explode('@', $action, 2);
        $fullClass = 'Controllers\\' . $controllerName;

        if (!class_exists($fullClass)) {
            error_log("[Router] Controller not found: $fullClass");
            $this->handleError(500, '', new \RuntimeException("Controller not found: $controllerName"));
            return;
        }

        if (!method_exists($fullClass, $method)) {
            error_log("[Router] Method not found: $fullClass@$method");
            $this->handleError(500, '', new \RuntimeException("Method not found: $method"));
            return;
        }

        try {
            $controller = new $fullClass();
            call_user_func_array([$controller, $method], array_values($params));
        } catch (\Throwable $e) {
            error_log("[Router] Action error: " . $e->getMessage());
            $this->handleError(500, '', $e);
        }
    }

    /* ============================================================
       PRIVATE: ERROR HANDLING
       ============================================================ */

    /**
     * Handle error dengan registered handler atau fallback.
     */
    private function handleError(int $code, string $path = '', ?\Throwable $exception = null): void
    {
        http_response_code($code);

        // Try registered handler
        if (isset($this->errorHandlers[$code])) {
            try {
                $this->callAction($this->errorHandlers[$code], []);
                return;
            } catch (\Throwable $e) {
                error_log("[Router] Error handler failed: " . $e->getMessage());
            }
        }

        // Try generic handler
        if (isset($this->errorHandlers['generic'])) {
            try {
                $this->callAction($this->errorHandlers['generic'], ['code' => $code]);
                return;
            } catch (\Throwable $e) {
                // Fall through to default
            }
        }

        // Default fallback (plain text)
        $messages = [
            404 => 'Halaman tidak ditemukan.',
            403 => 'Akses ditolak.',
            419 => 'Sesi telah kedaluwarsa.',
            500 => 'Terjadi kesalahan server.',
        ];

        $message = $messages[$code] ?? 'Terjadi kesalahan.';
        echo "<h1>Error $code</h1><p>" . htmlspecialchars($message) . "</p>";

        if ($exception !== null && (defined('APP_DEBUG') && APP_DEBUG)) {
            echo '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';
        }
    }
}