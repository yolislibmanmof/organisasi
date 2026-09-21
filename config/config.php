<?php
// File: config/config.php (FINAL v7.0.1 — AUTO-DETECT BASE_URL)
declare(strict_types=1);

if (!defined('APP_EXEC')) { die('Akses tidak diizinkan.'); }

// Pengaturan Waktu & Zona
date_default_timezone_set('Asia/Jakarta');

// Debug mode (set true saat development)
define('APP_DEBUG', false);

// Konfigurasi Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'organisasi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Konfigurasi Aplikasi
define('APP_NAME', 'Organisasi Wewur');

// BASE_URL Auto-Detect (portable, tidak perlu edit manual)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $protocol . '://' . $host . $scriptDir . '/');

// Konfigurasi Keamanan
define('CSRF_TOKEN_NAME', 'csrf_token');