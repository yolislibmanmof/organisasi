<?php
// File: app/Controllers/ErrorController.php (FINAL v7.0.1 — RECURSION-SAFE)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Models\Setting;

/**
 * ErrorController — Ultimate Edition v7.0.1 (RECURSION-SAFE)
 *
 * PERBAIKAN KRITIS v7.0.1:
 * Sebelumnya terjadi fatal "Allowed memory size exhausted" karena:
 *   renderError() → View::render('pages/error') → view hilang →
 *   View::handleError() → serverError() → renderError() → LOOP.
 * Kini ada guard self::$rendering + plainFallback() yang TIDAK menyentuh
 * View/Setting/Session, sehingga rekursi mustahil terjadi.
 */
class ErrorController
{
    /** GUARD anti-rekursi: renderError hanya boleh jalan 1x per request */
    private static bool $rendering = false;

    /* ============================================================
       PUBLIC HANDLERS
       ============================================================ */

    public function notFound(): void
    {
        $this->renderError(404, [
            'code'        => '404',
            'title'       => 'Halaman Tidak Ditemukan',
            'subtitle'    => 'Oops! Halaman yang Anda cari tidak ada.',
            'description' => 'Halaman yang Anda tuju mungkin telah dipindahkan, dihapus, atau tidak pernah ada. Silakan kembali ke beranda atau gunakan menu navigasi.',
            'icon'        => 'ph-map-trifold',
        ]);
    }

    public function forbidden(): void
    {
        $this->renderError(403, [
            'code'        => '403',
            'title'       => 'Akses Ditolak',
            'subtitle'    => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
            'description' => 'Halaman ini memerlukan hak akses khusus. Jika Anda yakin seharusnya memiliki akses, silakan hubungi administrator.',
            'icon'        => 'ph-lock-key',
        ]);
    }

    public function serverError(\Throwable $exception = null): void
    {
        if ($exception !== null) {
            error_log(sprintf(
                '[ErrorController] 500: %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));
        }

        $this->renderError(500, [
            'code'        => '500',
            'title'       => 'Terjadi Kesalahan Server',
            'subtitle'    => 'Maaf, ada yang tidak beres di sisi kami.',
            'description' => 'Tim teknis telah diberitahu dan sedang memperbaiki masalah ini. Silakan coba lagi dalam beberapa saat.',
            'icon'        => 'ph-warning-octagon',
        ]);
    }

    public function csrfExpired(): void
    {
        $this->renderError(419, [
            'code'        => '419',
            'title'       => 'Sesi Telah Kedaluwarsa',
            'subtitle'    => 'Token keamanan Anda sudah tidak valid.',
            'description' => 'Demi keamanan, sesi formulir Anda telah kedaluwarsa. Silakan muat ulang halaman dan coba lagi.',
            'icon'        => 'ph-clock-countdown',
        ]);
    }

    public function generic(int $code = 500, string $message = ''): void
    {
        $this->renderError($code, [
            'code'        => (string) $code,
            'title'       => 'Terjadi Kesalahan',
            'subtitle'    => $message !== '' ? $message : 'Sesuatu yang tidak terduga terjadi.',
            'description' => 'Silakan kembali ke halaman sebelumnya atau hubungi administrator jika masalah berlanjut.',
            'icon'        => 'ph-warning',
        ]);
    }

    /* ============================================================
       RENDER (DENGAN GUARD ANTI-REKURSI)
       ============================================================ */

    private function renderError(int $httpCode, array $context): void
    {
        // ===== GUARD KRITIS: cegah rekursi tak terhingga =====
        if (self::$rendering) {
            self::plainFallback($httpCode, $context);
            return;
        }
        self::$rendering = true;

        try {
            if (!headers_sent()) {
                http_response_code($httpCode);
            }

            // Data defensif — semua punya default, tidak boleh throw
            $user    = $this->safeCall(fn() => Session::get('user'), null);
            $isAdmin = is_array($user) && (($user['role'] ?? '') === 'admin');
            $appName = $this->safeCall(fn() => Setting::get('app_name', 'Organisasi'), 'Organisasi');

            $data = [
                'title'            => (string) ($context['title'] ?? 'Terjadi Kesalahan'),
                'loggedIn'         => (bool) $user,
                'isAdmin'          => $isAdmin,
                'errorCode'        => (string) ($context['code'] ?? $httpCode),
                'errorTitle'       => (string) ($context['title'] ?? 'Terjadi Kesalahan'),
                'errorSubtitle'    => (string) ($context['subtitle'] ?? ''),
                'errorDescription' => (string) ($context['description'] ?? ''),
                'errorIcon'        => (string) ($context['icon'] ?? 'ph-warning'),
                'quickLinks'       => $this->getQuickLinks($httpCode, $isAdmin, (bool) $user),
                'previousUrl'      => $_SERVER['HTTP_REFERER'] ?? null,
                'appName'          => $appName,
                'currentYear'      => (int) date('Y'),
            ];

            // HANYA render via View jika file view error BENAR-BENAR ada.
            if (View::exists('pages/error')) {
                View::render('pages/error', $data, 'layouts/public');
            } else {
                // View error belum ada → fallback inline (tanpa rekursi)
                self::plainFallback($httpCode, $context);
            }
        } catch (\Throwable $e) {
            error_log('[ErrorController] render gagal: ' . $e->getMessage());
            self::plainFallback($httpCode, $context, $e);
        } finally {
            self::$rendering = false;
        }
    }

    /**
     * Fallback TERAKHIR: HTML inline murni.
     * TIDAK menyentuh View / Setting / Session → mustahil rekursi.
     */
    private static function plainFallback(int $code, array $context, ?\Throwable $e = null): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: text/html; charset=utf-8');
        }

        $title = htmlspecialchars((string) ($context['title'] ?? 'Terjadi Kesalahan'), ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars((string) ($context['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $home  = function_exists('url') ? htmlspecialchars(url(''), ENT_QUOTES, 'UTF-8') : '/';

        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . $code . ' — ' . $title . '</title>';
        echo '<style>body{font-family:-apple-system,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:24px}.c{text-align:center;max-width:560px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:48px 32px;backdrop-filter:blur(10px)}h1{font-size:64px;margin:0 0 8px;color:#f87171;font-weight:800}h2{font-size:20px;margin:0 0 12px}p{color:#94a3b8;line-height:1.6}a.b{display:inline-block;margin-top:20px;padding:12px 24px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;border-radius:8px;font-weight:600}pre{background:#1e293b;padding:14px;border-radius:8px;overflow:auto;text-align:left;font-size:12px;margin-top:16px}</style>';
        echo '</head><body><div class="c">';
        echo '<h1>' . $code . '</h1>';
        echo '<h2>' . $title . '</h2>';
        if ($desc !== '') {
            echo '<p>' . $desc . '</p>';
        }
        if (defined('APP_DEBUG') && APP_DEBUG && $e !== null) {
            echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        }
        echo '<a class="b" href="' . $home . '">Kembali ke Beranda</a>';
        echo '</div></body></html>';
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    private function getQuickLinks(int $httpCode, bool $isAdmin, bool $isLoggedIn): array
    {
        $url = function_exists('url')
            ? fn($p) => url($p)
            : fn($p) => '/' . ltrim($p, '/');

        $links = [[
            'label' => 'Beranda',
            'url'   => $url(''),
            'icon'  => 'ph-house',
        ]];

        if ($httpCode === 404) {
            $links[] = ['label' => 'Artikel',  'url' => $url('artikel'), 'icon' => 'ph-newspaper'];
            $links[] = ['label' => 'Event',    'url' => $url('event'),  'icon' => 'ph-calendar-blank'];
            $links[] = ['label' => 'Galeri',   'url' => $url('galeri'), 'icon' => 'ph-images'];
            $links[] = ['label' => 'Tentang',  'url' => $url('tentang'), 'icon' => 'ph-info'];
        }

        if ($httpCode === 403 && !$isLoggedIn) {
            $links[] = ['label' => 'Masuk', 'url' => $url('login'), 'icon' => 'ph-sign-in'];
        }

        if ($isAdmin) {
            $links[] = ['label' => 'Dashboard', 'url' => $url('dashboard'), 'icon' => 'ph-chart-line-up'];
        }

        return $links;
    }

    private function safeCall(callable $callable, mixed $fallback): mixed
    {
        try {
            return $callable();
        } catch (\Throwable $e) {
            return $fallback;
        }
    }
}