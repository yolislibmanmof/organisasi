<?php
// File: app/Models/Setting.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class Setting {
    /** Cache in-memory agar tidak query berulang dalam satu request */
    private static array $cache = [];
    private static bool $loaded = false;

    /** Muat seluruh setting ke cache */
    public static function loadAll(): void {
        if (self::$loaded) return;
        $rows = Database::getInstance()->query('SELECT `key`, `value` FROM settings')->fetchAll();
        foreach ($rows as $r) {
            self::$cache[$r['key']] = $r['value'];
        }
        self::$loaded = true;
    }

    /** Ambil nilai setting (dengan fallback) */
    public static function get(string $key, string $default = ''): string {
        self::loadAll();
        return self::$cache[$key] ?? $default;
    }

    /** Ambil semua sebagai array */
    public static function all(): array {
        self::loadAll();
        return self::$cache;
    }

    /** Simpan/update nilai setting */
    public static function set(string $key, ?string $value): void {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('REPLACE INTO settings (`key`, `value`) VALUES (:k, :v)');
        $stmt->execute([':k' => $key, ':v' => $value]);
        self::$cache[$key] = $value;
    }

    /** Simpan banyak sekaligus */
    public static function setMany(array $pairs): void {
        foreach ($pairs as $k => $v) {
            self::set($k, $v);
        }
    }

    /** Konstanta kunci setting */
    public const KEYS = [
        'app_name', 'visi', 'misi',
        'social_instagram', 'social_youtube', 'social_email',
        'social_phone', 'social_address',
        'logo', 'favicon',
    ];
}