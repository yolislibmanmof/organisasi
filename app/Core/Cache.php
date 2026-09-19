<?php
declare(strict_types=1);

namespace Core;

class Cache {
    private static string $cacheDir;
    private static bool $enabled = true;

    public static function init(): void {
        self::$cacheDir = __DIR__ . '/../../storage/cache/';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    public static function enable(): void {
        self::$enabled = true;
    }

    public static function disable(): void {
        self::$enabled = false;
    }

    public static function get(string $key): ?array {
        if (!self::$enabled) return null;

        $file = self::getFilePath($key);
        if (!file_exists($file)) return null;

        $data = @file_get_contents($file);
        if ($data === false) return null;

        $cached = @unserialize($data);
        if ($cached === false) return null;

        if (isset($cached['expires']) && $cached['expires'] < time()) {
            @unlink($file);
            return null;
        }

        return $cached['data'] ?? null;
    }

    public static function set(string $key, $data, int $ttl = 300): bool {
        if (!self::$enabled) return false;

        $file = self::getFilePath($key);
        $cached = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        return @file_put_contents($file, serialize($cached), LOCK_EX) !== false;
    }

    public static function delete(string $key): bool {
        $file = self::getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    public static function flush(string $prefix = ''): int {
        $count = 0;
        $files = glob(self::$cacheDir . $prefix . '*.cache');
        
        if ($files !== false) {
            foreach ($files as $file) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    public static function clear(): int {
        return self::flush('');
    }

    public static function stats(): array {
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $count = 0;
        
        if ($files !== false) {
            foreach ($files as $file) {
                $totalSize += filesize($file);
                $count++;
            }
        }
        
        return [
            'count' => $count,
            'size' => $totalSize,
            'size_human' => self::formatBytes($totalSize),
        ];
    }

    private static function getFilePath(string $key): string {
        return self::$cacheDir . md5($key) . '.cache';
    }

    private static function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}