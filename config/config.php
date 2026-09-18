<?php
// File: config/config.php
declare(strict_types=1);

// Prevent direct access
if (!defined('APP_EXEC')) { die('Akses tidak diizinkan.'); }

// Pengaturan Waktu & Zona
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi Database (Sesuaikan dengan server lokal Anda)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'organisasi');
define('DB_USER', 'root');
define('DB_PASS', ''); // Kosongkan jika pakai XAMPP default

// Konfigurasi Aplikasi
define('APP_NAME', 'Organisasi Wewur');
// Sesuaikan BASE_URL dengan folder proyek Anda di htdocs
define('BASE_URL', 'http://localhost/organisasi/public/'); 

// Konfigurasi Keamanan
define('CSRF_TOKEN_NAME', 'csrf_token');