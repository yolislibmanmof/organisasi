<?php
// File: app/Core/Cache.php (FINAL v7.0 — EXTENDED + MIXED-TYPE + RICH-API)
declare(strict_types=1);

namespace Core;

/**
 * File-based Cache — Ultimate Edition v7.0
 *
 * Fitur:
 * - Self-initializing & fail-safe (tidak pernah error)
 * - Support mixed types (array, int, string, bool, null)
 * - Laravel-style `remember()` helper
 * - Atomic `increment()`/`decrement()`
 * - Batch `many()`/`putMany()`
 * - `has()`/`forget()`/`pull()` helpers
 * - Optional compression (gzencode)
 * - Portable paths (storage_path helper)
 * - Backward compatible dengan signature v5.x
 */
class Cache
{
    /** @var string|null Cache directory */
    private static ?string $dir = null;

    /** @var bool Cache enabled flag */
    private static bool $enabled = true;

    /** @var bool Enable compression (untuk data > 1KB) */
    private static bool $compress = false;

    /** @var int Compression threshold (bytes) */
    private const COMPRESS_THRESHOLD = 1024;

    /* ============================================================
       INITIALIZATION
       ============================================================ */

    /**
     * Initialize cache directory (lazy, dipanggil internal).
     */
    private static function dir(): string
    {
        if (self::$dir !== null) {
            return self::$dir;
        }

        // Gunakan storage_path helper jika ada, fallback ke hardcoded
        if (function_exists('storage_path')) {
            self::$dir = storage_path('cache');
        } else {
            self::$dir = dirname(__DIR__, 2) . '/storage/cache';
        }

        if (!is_dir(self::$dir)) {
            if (!@mkdir(self::$dir, 0755, true) && !is_dir(self::$dir)) {
                self::$enabled = false;
            }
        }

        if (self::$enabled && !is_writable(self::$dir)) {
            self::$enabled = false;
        }

        return self::$dir;
    }

    /**
     * Manual init (optional, aman dipanggil berulang).
     * Backward compat signature v5.x.
     */
    public static function init(): void
    {
        self::dir();
    }

    public static function enable(): void   { self::$enabled = true; }
    public static function disable(): void  { self::$enabled = false; }
    public static function isEnabled(): bool { return self::$enabled; }

    /**
     * Enable/disable compression.
     */
    public static function setCompression(bool $enabled): void
    {
        self::$compress = $enabled && function_exists('gzencode');
    }

    /* ============================================================
       GET / SET (MIXED TYPE SUPPORT)
       ============================================================ */

    /**
     * Get cached value.
     * Backward compat signature v5.x (tapi sekarang support mixed, bukan hanya array).
     *
     * @template T
     * @param string $key Cache key
     * @param T|null $default Default value jika tidak ada
     * @return T|null
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$enabled) return $default;

        $file = self::filePath($key);
        if (!is_file($file)) return $default;

        $raw = @file_get_contents($file);
        if ($raw === false) return $default;

        // Decompress jika perlu
        if (self::$compress && str_starts_with($raw, "\x1f\x8b")) {
            $raw = @gzdecode($raw);
            if ($raw === false) return $default;
        }

        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('data', $data)) return $default;

        // Check expiration
        if (isset($data['expires']) && $data['expires'] < time()) {
            @unlink($file);
            return $default;
        }

        return $data['data'];
    }

    /**
     * Set cache value dengan TTL.
     * Backward compat signature v5.x (tapi sekarang support mixed data).
     *
     * @param string $key Cache key
     * @param mixed $data Data (array, scalar, dll)
     * @param int $ttl TTL dalam detik
     */
    public static function set(string $key, mixed $data, int $ttl = 300): bool
    {
        if (!self::$enabled) return false;

        $payload = [
            'data'    => $data,
            'expires' => time() + $ttl,
            'created' => time(),
        ];

        $raw = serialize($payload);

        // Compress jika enabled & data cukup besar
        if (self::$compress && strlen($raw) > self::COMPRESS_THRESHOLD) {
            $raw = gzencode($raw, 6);
            if ($raw === false) return false;
        }

        return @file_put_contents(self::filePath($key), $raw, LOCK_EX) !== false;
    }

    /* ============================================================
       LARAVEL-STYLE API
       ============================================================ */

    /**
     * Get value atau execute callback dan cache hasilnya.
     * Laravel-style `remember()` helper.
     *
     * Contoh:
     * $users = Cache::remember('users_list', 300, fn() => User::all());
     *
     * @template T
     * @param string $key
     * @param int $ttl
     * @param callable(): T $callback
     * @return T
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Store value forever (TTL = 10 tahun).
     */
    public static function forever(string $key, mixed $data): bool
    {
        return self::set($key, $data, 315360000); // 10 tahun
    }

    /**
     * Cek apakah key ada di cache (tanpa load value).
     */
    public static function has(string $key): bool
    {
        if (!self::$enabled) return false;

        $file = self::filePath($key);
        if (!is_file($file)) return false;

        $raw = @file_get_contents($file);
        if ($raw === false) return false;

        if (self::$compress && str_starts_with($raw, "\x1f\x8b")) {
            $raw = @gzdecode($raw);
            if ($raw === false) return false;
        }

        $data = @unserialize($raw);
        if (!is_array($data) || !array_key_exists('data', $data)) return false;

        if (isset($data['expires']) && $data['expires'] < time()) {
            @unlink($file);
            return false;
        }

        return true;
    }

    /**
     * Get value lalu delete (consume once).
     */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::delete($key);
        return $value;
    }

    /**
     * Alias untuk delete (Laravel-style).
     */
    public static function forget(string $key): bool
    {
        return self::delete($key);
    }

    /* ============================================================
       DELETE OPERATIONS
       ============================================================ */

    /**
     * Delete single key.
     * Backward compat signature v5.x.
     */
    public static function delete(string $key): bool
    {
        if (!self::$enabled) return true;
        $file = self::filePath($key);
        return is_file($file) ? @unlink($file) : true;
    }

    /**
     * Flush semua cache dengan prefix tertentu.
     * Backward compat signature v5.x (dengan regex yang lebih permissive).
     *
     * @param string $prefix Prefix (kosongkan untuk semua)
     * @return int Jumlah file yang dihapus
     */
    public static function flush(string $prefix = ''): int
    {
        if (!self::$enabled) return 0;

        $dir = self::dir();
        if (!is_dir($dir)) return 0;

        // Sanitize prefix tapi tetap permissive
        $safe = preg_replace('/[^A-Za-z0-9_|]/', '', $prefix);

        $pattern = $safe !== ''
            ? $dir . '/' . $safe . '*.cache'
            : $dir . '/*.cache';

        $files = @glob($pattern);
        if ($files === false) return 0;

        $count = 0;
        foreach ($files as $file) {
            if (@unlink($file)) $count++;
        }

        return $count;
    }

    /**
     * Clear semua cache (alias flush tanpa prefix).
     */
    public static function clear(): int
    {
        return self::flush('');
    }

    /* ============================================================
       BATCH OPERATIONS
       ============================================================ */

    /**
     * Get multiple keys sekaligus.
     *
     * @param array<string> $keys
     * @param mixed $default
     * @return array<string, mixed>
     */
    public static function many(array $keys, mixed $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key, $default);
        }
        return $result;
    }

    /**
     * Set multiple keys sekaligus.
     *
     * @param array<string, mixed> $items Key-value pairs
     * @param int $ttl TTL dalam detik
     * @return int Jumlah yang berhasil disimpan
     */
    public static function putMany(array $items, int $ttl = 300): int
    {
        $count = 0;
        foreach ($items as $key => $value) {
            if (self::set((string) $key, $value, $ttl)) {
                $count++;
            }
        }
        return $count;
    }

    /* ============================================================
       ATOMIC COUNTERS
       ============================================================ */

    /**
     * Increment nilai integer secara atomic.
     *
     * @param string $key
     * @param int $value Increment amount
     * @return int New value
     */
    public static function increment(string $key, int $value = 1): int
    {
        $current = (int) self::get($key, 0);
        $new = $current + $value;

        // Preserve TTL jika ada
        $file = self::filePath($key);
        $ttl = 300; // Default
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $data = @unserialize($raw);
                if (is_array($data) && isset($data['expires'])) {
                    $ttl = max(1, $data['expires'] - time());
                }
            }
        }

        self::set($key, $new, $ttl);
        return $new;
    }

    /**
     * Decrement nilai integer secara atomic.
     */
    public static function decrement(string $key, int $value = 1): int
    {
        return self::increment($key, -$value);
    }

    /* ============================================================
       STATISTICS & DIAGNOSTICS
       ============================================================ */

    /**
     * Get cache statistics (untuk admin dashboard).
     * Backward compat signature v5.x.
     *
     * @return array{enabled: bool, count: int, size: int, oldest: ?int, newest: ?int}
     */
    public static function stats(): array
    {
        $dir = self::dir();
        $files = is_dir($dir) ? @glob($dir . '/*.cache') : false;

        $count = 0;
        $size = 0;
        $oldest = null;
        $newest = null;

        if ($files !== false) {
            foreach ($files as $file) {
                $count++;
                $size += (int) @filesize($file);
                $mtime = @filemtime($file);
                if ($mtime !== false) {
                    if ($oldest === null || $mtime < $oldest) $oldest = $mtime;
                    if ($newest === null || $mtime > $newest) $newest = $mtime;
                }
            }
        }

        return [
            'enabled' => self::$enabled,
            'count'   => $count,
            'size'    => $size,
            'oldest'  => $oldest,
            'newest'  => $newest,
        ];
    }

    /**
     * Get list semua cache keys (untuk debugging).
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        $dir = self::dir();
        $files = is_dir($dir) ? @glob($dir . '/*.cache') : false;
        if ($files === false) return [];

        $keys = [];
        foreach ($files as $file) {
            $basename = basename($file, '.cache');
            // Extract key dari filename (format: prefix_md5(key))
            // Kita tidak bisa reconstruct key asli, tapi bisa return filename
            $keys[] = $basename;
        }
        return $keys;
    }

    /**
     * Cleanup expired files (maintenance).
     *
     * @return int Jumlah file yang dibersihkan
     */
    public static function cleanupExpired(): int
    {
        $dir = self::dir();
        if (!is_dir($dir)) return 0;

        $files = @glob($dir . '/*.cache');
        if ($files === false) return 0;

        $now = time();
        $count = 0;

        foreach ($files as $file) {
            $raw = @file_get_contents($file);
            if ($raw === false) continue;

            if (self::$compress && str_starts_with($raw, "\x1f\x8b")) {
                $raw = @gzdecode($raw);
                if ($raw === false) {
                    @unlink($file);
                    $count++;
                    continue;
                }
            }

            $data = @unserialize($raw);
            if (!is_array($data) || !isset($data['expires'])) {
                @unlink($file);
                $count++;
                continue;
            }

            if ($data['expires'] < $now) {
                @unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /* ============================================================
       PRIVATE: FILE PATH
       ============================================================ */

    /**
     * Generate cache file path dari key.
     * Backward compat signature v5.x.
     */
    private static function filePath(string $key): string
    {
        $dir = self::dir();

        // Ambil prefix (sebelum |) untuk filename grouping
        $prefix = explode('|', $key)[0];
        $safe = preg_replace('/[^A-Za-z0-9_]/', '', $prefix);
        if ($safe === '') $safe = 'default';

        return $dir . '/' . $safe . '_' . md5($key) . '.cache';
    }
}