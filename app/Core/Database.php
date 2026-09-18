<?php
// File: app/Core/Database.php
declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    // Private constructor mencegah instansiasi langsung
    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Mencegah SQL Injection tingkat lanjut
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}