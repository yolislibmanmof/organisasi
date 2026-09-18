<?php
/**
 * ================================================================
 *  <?= APP_NAME ?> — FRONT CONTROLLER (PINTU MASUK UTAMA)
 * ================================================================
 *  Seluruh permintaan HTTP (GET/POST) WAJIB melewati file ini.
 *  Tidak ada satu pun file PHP lain yang dapat diakses langsung
 *  dari browser, sehingga seluruh alur aplikasi terpusat,
 *  terkontrol, dan aman.
 *
 *  Alur kerja:
 *  Config → Helpers → Autoloader → Security Headers
 *  → Error Handler → Session → Router → Dispatch
 */
declare(strict_types=1);

// Konstanta penanda eksekusi aman (memblokir direct access file lain)
define('APP_EXEC', true);

// ------------------------------------------------------------
// 1. MUAT KONFIGURASI GLOBAL
//    (DB, APP_NAME, BASE_URL, dan konstanta keamanan)
// ------------------------------------------------------------
require_once __DIR__ . '/../config/config.php';

// Mode debug: tampilkan detail error saat pengembangan.
// Ubah menjadi false saat aplikasi dipublikasikan ke server produksi.
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// ------------------------------------------------------------
// 2. MUAT FUNGSI BANTUAN GLOBAL
//    (e(), url(), asset(), redirect(), csrf_token(), csrf_field(), dll.)
// ------------------------------------------------------------
require_once __DIR__ . '/../app/Core/helpers.php';

// ------------------------------------------------------------
// 3. PSR-4 AUTOLOADER
//    Memetakan namespace ke folder app/ secara otomatis:
//      Core\Session        → app/Core/Session.php
//      Core\Router         → app/Core/Router.php
//      Controllers\X       → app/Controllers/X.php
//      Models\X            → app/Models/X.php
//      Middleware\X        → app/Middleware/X.php
// ------------------------------------------------------------
spl_autoload_register(static function (string $class): void {
    $file = __DIR__ . '/../app/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// ------------------------------------------------------------
// 4. KEPALA HTTP KEAMANAN (SECURITY HEADERS)
//    Melindungi aplikasi dari serangan clickjacking, MIME-sniffing,
//    dan kebocoran referrer pada tingkat server.
// ------------------------------------------------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ------------------------------------------------------------
// 5. PENANGAN GALAT GLOBAL (GLOBAL EXCEPTION HANDLER)
//    Menangkap seluruh exception yang tidak tertangani agar
//    aplikasi tidak pernah menampilkan "layar putih" kepada pengguna.
// ------------------------------------------------------------
set_exception_handler(static function (Throwable $e): void {
    error_log('[ORG-ULTIMATE] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);

    if (APP_DEBUG) {
        echo '<h1>Terjadi Kesalahan Sistem</h1>';
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }

    echo '<h1>500 — Kesalahan Server</h1>';
    echo '<p>Terjadi kesalahan tak terduga. Tim teknis telah menerima laporan ini.</p>';
});

// ------------------------------------------------------------
// 6. INISIALISASI SESI PENGUNA (dengan cookie berkeamanan tinggi)
// ------------------------------------------------------------
Core\Session::start();

// ------------------------------------------------------------
// 7. INISIALISASI ROUTER & MUAT DAFTAR RUTE
//    Daftar rute dipusatkan pada config/routes.php agar
//    index.php tetap bersih dan fokus sebagai bootstrapper.
// ------------------------------------------------------------
$router = new Core\Router();
require_once __DIR__ . '/../config/routes.php';

// ------------------------------------------------------------
// 8. EKSEKUSI ROUTER — Menjalankan controller sesuai URL permintaan
// ------------------------------------------------------------
$router->dispatch();