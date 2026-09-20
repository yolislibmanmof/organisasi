<?php
// File: app/Core/View.php (FINAL v7.0.2 — DUPLICATE-PROPERTY FIXED)
declare(strict_types=1);

namespace Core;

/**
 * View Renderer — Ultimate Edition v7.0.2
 *
 * PERBAIKAN KRITIS v7.0.2:
 * - Hapus deklarasi properti $basePathOverride yang duplikat
 *   (duplikat properti = fatal error compile-time: seluruh class View
 *   gagal dimuat → SEMUA halaman jadi 500).
 *
 * Fitur:
 * - Auto-inject helper functions (e, url, asset, csrf_token, dll)
 * - Auto-inject layout data (SEO, announcement, org info dari Setting)
 * - Error handling dengan graceful fallback + guard anti-rekursi
 * - Partial/component rendering
 * - Debug mode untuk development
 * - Backward compatible dengan signature v5.x
 */
class View
{
    /** @var bool Debug mode (tampilkan stack trace di error) */
    private static bool $debug = false;

    /** @var array<string, mixed> Global data yang selalu di-inject ke semua view */
    private static array $sharedData = [];

    /** @var string|null Base path override (untuk testing) */
    private static ?string $basePathOverride = null;

    /** @var bool GUARD anti-rekursi saat render halaman error */
    private static bool $inErrorRender = false;

    /* ============================================================
       PUBLIC API
       ============================================================ */

    /**
     * Render view dengan layout.
     *
     * Backward compat signature v5.x:
     * View::render('pages/login', ['title' => 'Login'], 'layouts/auth')
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        $basePath = self::getBasePath();
        $viewFile = $basePath . '/views/' . $view . '.php';
        $layoutFile = $basePath . '/views/' . $layout . '.php';

        // Validasi view file
        if (!is_file($viewFile)) {
            self::handleError("View file tidak ditemukan: $view", $viewFile);
            return;
        }

        // Validasi layout file
        if (!is_file($layoutFile)) {
            self::handleError("Layout file tidak ditemukan: $layout", $layoutFile);
            return;
        }

        // Merge shared data (lower priority, tidak override explicit data)
        $mergedData = array_merge(self::$sharedData, $data);

        // Inject helpers ke data
        $mergedData = self::injectHelpers($mergedData);

        // Inject CSRF token jika belum ada
        if (!isset($mergedData['csrf_token'])) {
            $mergedData['csrf_token'] = self::generateCsrfToken();
        }

        // Inject layout defaults (SEO, announcement, org) jika belum ada
        $mergedData = self::injectLayoutDefaults($mergedData);

        // 1. Render isi halaman terlebih dahulu
        $content = '';
        try {
            ob_start();
            extract($mergedData, EXTR_SKIP);
            require $viewFile;
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) ob_end_clean();
            self::handleError('Error rendering view: ' . $view, $viewFile, $e);
            return;
        }

        // 2. Bungkus dengan layout
        try {
            extract($mergedData, EXTR_SKIP);
            require $layoutFile;
        } catch (\Throwable $e) {
            self::handleError('Error rendering layout: ' . $layout, $layoutFile, $e);
        }
    }

    /**
     * Render partial/component tanpa layout.
     *
     * @return string Rendered HTML
     */
    public static function partial(string $view, array $data = []): string
    {
        $basePath = self::getBasePath();
        $viewFile = $basePath . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            return '<!-- Partial not found: ' . htmlspecialchars($view) . ' -->';
        }

        $mergedData = array_merge(self::$sharedData, $data);
        $mergedData = self::injectHelpers($mergedData);

        try {
            ob_start();
            extract($mergedData, EXTR_SKIP);
            require $viewFile;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) ob_end_clean();
            return '<!-- Error rendering partial: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }
    }

    /**
     * Render view ke string (tanpa output langsung).
     */
    public static function renderToString(string $view, array $data = [], ?string $layout = null): string
    {
        $basePath = self::getBasePath();
        $viewFile = $basePath . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            return '<!-- View not found: ' . htmlspecialchars($view) . ' -->';
        }

        $mergedData = array_merge(self::$sharedData, $data);
        $mergedData = self::injectHelpers($mergedData);

        if (!isset($mergedData['csrf_token'])) {
            $mergedData['csrf_token'] = self::generateCsrfToken();
        }

        $content = '';
        try {
            ob_start();
            extract($mergedData, EXTR_SKIP);
            require $viewFile;
            $content = ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) ob_end_clean();
            return '<!-- Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }

        if ($layout === null) {
            return $content;
        }

        $layoutFile = $basePath . '/views/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            return $content;
        }

        try {
            ob_start();
            extract($mergedData, EXTR_SKIP);
            require $layoutFile;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > 0) ob_end_clean();
            return $content;
        }
    }

    /**
     * Share data ke semua view (global).
     */
    public static function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            self::$sharedData = array_merge(self::$sharedData, $key);
        } else {
            self::$sharedData[$key] = $value;
        }
    }

    /**
     * Enable/disable debug mode.
     */
    public static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }

    /**
     * Override base path (untuk testing atau custom structure).
     */
    public static function setBasePath(string $path): void
    {
        self::$basePathOverride = rtrim($path, '/');
    }

    /**
     * Cek apakah view file exists.
     */
    public static function exists(string $view): bool
    {
        $basePath = self::getBasePath();
        return is_file($basePath . '/views/' . $view . '.php');
    }

    /* ============================================================
       PRIVATE: HELPERS
       ============================================================ */

    private static function injectHelpers(array $data): array
    {
        if (!isset($data['e'])) {
            $data['e'] = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        }

        if (!isset($data['url'])) {
            $data['url'] = static function ($path = '') {
                $base = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
                return $base . '/' . ltrim((string) $path, '/');
            };
        }

        if (!isset($data['asset'])) {
            $data['asset'] = static function ($path) {
                $base = defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
                return $base . '/assets/' . ltrim((string) $path, '/');
            };
        }

        if (!isset($data['csrf_token'])) {
            $data['csrf_token'] = self::generateCsrfToken();
        }

        if (!isset($data['csrf_field'])) {
            $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
            $token = $data['csrf_token'];
            $data['csrf_field'] = '<input type="hidden" name="' . $tokenName . '" value="' . htmlspecialchars($token) . '">';
        }

        if (!isset($data['dd'])) {
            $data['dd'] = static function (...$vars) {
                echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:8px;overflow:auto;">';
                foreach ($vars as $var) {
                    var_dump($var);
                }
                echo '</pre>';
                exit;
            };
        }

        return $data;
    }

    /**
     * Inject layout defaults (SEO, announcement, org info).
     */
    private static function injectLayoutDefaults(array $data): array
    {
        try {
            if (class_exists('\Models\Setting')) {
                \Models\Setting::loadAll();

                if (!isset($data['seo'])) {
                    $data['seo'] = [
                        'title'       => $data['title'] ?? \Models\Setting::get('app_name', 'Organisasi'),
                        'description' => \Models\Setting::get('meta_description'),
                        'keywords'    => \Models\Setting::get('meta_keywords'),
                        'author'      => \Models\Setting::get('meta_author'),
                        'og_image'    => \Models\Setting::getLogoUrl(),
                    ];
                }

                if (!isset($data['announcement'])) {
                    $data['announcement'] = [
                        'active' => \Models\Setting::isAnnouncementActive(),
                        'text'   => \Models\Setting::get('announcement_text'),
                        'link'   => \Models\Setting::get('announcement_link'),
                    ];
                }

                if (!isset($data['org'])) {
                    $data['org'] = [
                        'name'        => \Models\Setting::get('app_name', 'Organisasi'),
                        'logo_url'    => \Models\Setting::getLogoUrl(),
                        'favicon_url' => \Models\Setting::getFaviconUrl(),
                    ];
                }

                if (!isset($data['socialLinks'])) {
                    $data['socialLinks'] = \Models\Setting::getSocialLinks();
                }

                if (!isset($data['currentYear'])) {
                    $data['currentYear'] = (int) date('Y');
                }
            }
        } catch (\Throwable $e) {
            error_log('[View] Failed to load Setting defaults: ' . $e->getMessage());
        }

        return $data;
    }

    private static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            return '';
        }

        $tokenName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';

        if (empty($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$tokenName];
    }

    private static function getBasePath(): string
    {
        if (self::$basePathOverride !== null) {
            return self::$basePathOverride;
        }
        return dirname(__DIR__, 2); // Akar proyek
    }

    /**
     * Handle error rendering dengan graceful fallback + GUARD anti-rekursi.
     */
    private static function handleError(string $message, string $file, ?\Throwable $exception = null): void
    {
        error_log('[View] ' . $message . ' (' . $file . ')');
        if ($exception !== null) {
            error_log('[View] Exception: ' . $exception->getMessage());
        }

        // GUARD KRITIS: jika sudah berada di alur render error,
        // JANGAN panggil ErrorController lagi (mencegah memory exhausted).
        if (self::$inErrorRender) {
            self::plainErrorOutput($message, $exception);
            return;
        }

        if (self::$debug) {
            if (!headers_sent()) http_response_code(500);
            echo '<h1>View Error</h1>';
            echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p>File: ' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</p>';
            if ($exception !== null) {
                echo '<pre>' . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            }
            return;
        }

        self::$inErrorRender = true;
        try {
            if (class_exists('\Controllers\ErrorController')) {
                (new \Controllers\ErrorController())->serverError($exception);
                return;
            }
        } catch (\Throwable $e) {
            error_log('[View] ErrorController gagal: ' . $e->getMessage());
        } finally {
            self::$inErrorRender = false;
        }

        self::plainErrorOutput($message, $exception);
    }

    /**
     * Output error inline murni (tanpa View/Model) — mustahil rekursi.
     */
    private static function plainErrorOutput(string $message, ?\Throwable $exception = null): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>500</title></head>';
        echo '<body style="font-family:sans-serif;background:#0f172a;color:#e2e8f0;padding:40px">';
        echo '<h1 style="color:#f87171">500 — Kesalahan Server</h1>';
        echo '<p style="color:#94a3b8">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        if (defined('APP_DEBUG') && APP_DEBUG && $exception !== null) {
            echo '<pre style="background:#1e293b;padding:16px;border-radius:8px;overflow:auto">'
                . htmlspecialchars($exception->getMessage() . "\n" . $exception->getTraceAsString(), ENT_QUOTES, 'UTF-8')
                . '</pre>';
        }
        echo '</body></html>';
    }
}