<?php
// File: app/Core/Cache.php (FINAL v5.9 — SELF-INITIALIZING & FAIL-SAFE)
declare(strict_types=1);

namespace Core;

/**
 * File-based cache dengan inisialisasi otomatis.
 * - Tidak PERLU memanggil Cache::init() (lazy init).
 * - Bila folder cache gagal dibuat/tidak writable, cache otomatis
 *   dinonaktifkan sehingga aplikasi TIDAK PERNAH error.
 */
class Cache {
    private static ?string $dir = null;
    private static bool $enabled = true;

    /** Inisialisasi otomatis folder cache (dipanggil internal) */
    private static function dir(): string {
        if (self::$dir === null) {
            self::$dir = dirname(__DIR__, 2) . '/storage/cache/';
            if (!is_dir(self::$dir)) {
                if (!@mkdir(self::$dir, 0755, true)) {
                    self::$enabled = false;
                }
            }
            if (self::$enabled && !is_writable(self::$dir)) {
                self::$enabled = false;
            }
        }
        return self::$dir;
    }

    /** Opsional: boleh dipanggil manual di index.php, aman bila dipanggil berulang */
    public static function init(): void {
        self::dir();
    }

    public static function enable(): void  { self::$enabled = true; }
    public static function disable(): void { self::$enabled = false; }
    public static function isEnabled(): bool { return self::$enabled; }

    /** Ambil data cache; return null bila tidak ada / kadaluarsa */
    public static function get(string $key): ?array {
        if (!self::$enabled) return null;
        $file = self::filePath($key);
        if (!is_file($file)) return null;

        $raw = @file_get_contents($file);
        if ($raw === false) return null;

        $data = @unserialize($raw);
        if (!is_array($data) || !isset($data['data'])) return null;

        if (isset($data['expires']) && $data['expires'] < time()) {
            @unlink($file);
            return null;
        }
        return $data['data'];
    }

    /** Simpan data cache dengan TTL (detik) */
    public static function set(string $key, $data, int $ttl = 300): bool {
        if (!self::$enabled) return false;
        $payload = [
            'data'    => $data,
            'expires' => time() + $ttl,
            'created' => time(),
        ];
        return @file_put_contents(self::filePath($key), serialize($payload), LOCK_EX) !== false;
    }

    /** Hapus satu kunci cache */
    public static function delete(string $key): bool {
        if (!self::$enabled) return true;
        $file = self::filePath($key);
        return is_file($file) ? @unlink($file) : true;
    }

    /** Hapus semua cache dengan prefix tertentu (kosongkan = semua) */
    public static function flush(string $prefix = ''): int {
        if (!self::$enabled) return 0;
        $safe  = preg_replace('/[^A-Za-z0-9_]/', '', $prefix);
        $files = @glob(self::dir() . $safe . '_*.cache');
        if ($files === false) return 0;

        $count = 0;
        foreach ($files as $file) {
            if (@unlink($file)) $count++;
        }
        return $count;
    }

    /** Statistik cache (untuk halaman sistem/admin) */
    public static function stats(): array {
        $files = @glob(self::dir() . '*.cache');
        $count = 0;
        $size  = 0;
        if ($files !== false) {
            foreach ($files as $file) {
                $count++;
                $size += (int) @filesize($file);
            }
        }
        return [
            'enabled' => self::$enabled,
            'count'   => $count,
            'size'    => $size,
        ];
    }

    /** Nama file cache: prefix_ + md5(kunci) */
    private static function filePath(string $key): string {
        $safe = preg_replace('/[^A-Za-z0-9_]/', '', explode('|', $key)[0]);
        return self::dir() . $safe . '_' . md5($key) . '.cache';
    }
}