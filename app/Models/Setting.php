<?php
// File: app/Models/Setting.php (FINAL v7.0 — EXTENDED + PERSISTENT CACHE + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Setting — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature v5.6 + extension untuk fitur v7.0:
 * - Persistent cache (Core\Cache) + in-memory cache (per-request)
 * - Typed getters (bool, int, json, array)
 * - Atomic setMany dengan transaction
 * - Default values untuk reset
 * - Export/Import settings (JSON)
 * - Timestamp tracking (last updated)
 * - Complete key registry untuk semua section settings v7.0
 */
class Setting
{
    /* ============================================================
       IN-MEMORY CACHE (per-request)
       ============================================================ */

    /** @var array<string, string|null> */
    private static array $cache = [];

    /** Flag apakah sudah load dari DB / persistent cache */
    private static bool $loaded = false;

    /** Cache key untuk Core\Cache (persistent antar-request) */
    private const CACHE_KEY = 'settings|all';

    /** Cache TTL untuk persistent cache (10 menit) */
    private const CACHE_TTL = 600;

    /* ============================================================
       KEY CONSTANTS (Extended untuk v7.0)
       ============================================================ */

    /**
     * Semua key setting yang terdaftar.
     * Grouped by section untuk dokumentasi.
     */
    public const KEYS = [
        // Section 1: Identitas Organisasi
        'app_name',
        'logo',
        'favicon',

        // Section 2: Visi, Misi & Kabinet
        'cabinet_period',
        'visi',
        'misi',
        'motto',

        // Section 3: Kontak & Sosial Media
        'social_instagram',
        'social_youtube',
        'social_email',
        'social_phone',
        'social_address',

        // Section 4: Pengumuman & Banner (BARU v7.0)
        'announcement_active',
        'announcement_text',
        'announcement_link',

        // Section 5: SEO & Meta Tags (BARU v7.0)
        'meta_description',
        'meta_keywords',
        'meta_author',

        // System (internal)
        'last_updated',
    ];

    /**
     * Default values untuk setiap key (digunakan saat reset).
     */
    public const DEFAULTS = [
        'app_name'            => 'Organisasi',
        'logo'                => '',
        'favicon'             => '',
        'cabinet_period'      => '',
        'visi'                => '',
        'misi'                => '',
        'motto'               => '',
        'social_instagram'    => '',
        'social_youtube'      => '',
        'social_email'        => '',
        'social_phone'        => '',
        'social_address'      => '',
        'announcement_active' => '0',
        'announcement_text'   => '',
        'announcement_link'   => '',
        'meta_description'    => '',
        'meta_keywords'       => '',
        'meta_author'         => '',
        'last_updated'        => '',
    ];

    /**
     * Keys yang menyimpan boolean (untuk getBool).
     */
    private const BOOL_KEYS = [
        'announcement_active',
    ];

    /**
     * Keys yang menyimpan JSON (untuk getJson).
     * (reserved untuk masa depan)
     */
    private const JSON_KEYS = [];

    /* ============================================================
       LOAD / READ
       ============================================================ */

    /**
     * Muat seluruh setting ke in-memory cache.
     * Urutan: in-memory → persistent cache → database.
     */
    public static function loadAll(): void
    {
        if (self::$loaded) return;

        // 1. Coba ambil dari persistent cache
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached)) {
            self::$cache = $cached;
            self::$loaded = true;
            return;
        }

        // 2. Fallback ke database
        $rows = Database::getInstance()->query('SELECT `key`, `value` FROM settings')->fetchAll();
        foreach ($rows as $r) {
            self::$cache[$r['key']] = $r['value'];
        }

        // 3. Simpan ke persistent cache
        Cache::set(self::CACHE_KEY, self::$cache, self::CACHE_TTL);

        self::$loaded = true;
    }

    /**
     * Ambil nilai setting (dengan fallback).
     * Backward compat signature v5.6.
     */
    public static function get(string $key, string $default = ''): string
    {
        self::loadAll();
        $value = self::$cache[$key] ?? null;
        return $value !== null ? (string) $value : $default;
    }

    /**
     * Ambil nilai sebagai boolean.
     * Truthy: '1', 'true', 'yes', 'on', 'active'
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, '');
        if ($value === '') return $default;
        return in_array(strtolower($value), ['1', 'true', 'yes', 'on', 'active'], true);
    }

    /**
     * Ambil nilai sebagai integer.
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key, '');
        if ($value === '') return $default;
        return (int) $value;
    }

    /**
     * Ambil nilai sebagai array (JSON decoded).
     *
     * @return array<mixed>
     */
    public static function getJson(string $key, array $default = []): array
    {
        $value = self::get($key, '');
        if ($value === '') return $default;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $default;
    }

    /**
     * Ambil banyak key sekaligus (lebih efisien daripada panggil get() berulang).
     *
     * @param array<string> $keys
     * @param string $default
     * @return array<string, string>
     */
    public static function getMany(array $keys, string $default = ''): array
    {
        self::loadAll();
        $result = [];
        foreach ($keys as $key) {
            $value = self::$cache[$key] ?? null;
            $result[$key] = $value !== null ? (string) $value : $default;
        }
        return $result;
    }

    /**
     * Cek apakah key ada di database (tidak peduli value kosong atau tidak).
     */
    public static function has(string $key): bool
    {
        self::loadAll();
        return array_key_exists($key, self::$cache);
    }

    /**
     * Ambil semua setting sebagai array.
     * Backward compat signature v5.6.
     *
     * @return array<string, string|null>
     */
    public static function all(): array
    {
        self::loadAll();
        return self::$cache;
    }

    /**
     * Ambil semua key yang terdaftar dengan value-nya.
     * Berguna untuk form settings (pastikan semua field terisi).
     *
     * @return array<string, string>
     */
    public static function allWithDefaults(): array
    {
        self::loadAll();
        $result = self::DEFAULTS;
        foreach (self::$cache as $k => $v) {
            if (array_key_exists($k, $result)) {
                $result[$k] = $v ?? '';
            }
        }
        return $result;
    }

    /* ============================================================
       WRITE
       ============================================================ */

    /**
     * Simpan/update nilai setting.
     * Backward compat signature v5.6.
     */
    public static function set(string $key, ?string $value): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('REPLACE INTO settings (`key`, `value`) VALUES (:k, :v)');
        $stmt->execute([':k' => $key, ':v' => $value]);

        // Update in-memory cache
        self::$cache[$key] = $value;

        // Invalidate persistent cache
        self::invalidateCache();
    }

    /**
     * Simpan banyak sekaligus (ATOMIC dengan transaction).
     * Backward compat signature v5.6, tapi sekarang atomic.
     *
     * @param array<string, string|null> $pairs
     */
    public static function setMany(array $pairs): void
    {
        if (empty($pairs)) return;

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('REPLACE INTO settings (`key`, `value`) VALUES (:k, :v)');
            foreach ($pairs as $k => $v) {
                $stmt->execute([':k' => $k, ':v' => $v]);
                self::$cache[$k] = $v;
            }

            // Auto-update last_updated timestamp
            $now = date('Y-m-d H:i:s');
            $stmt->execute([':k' => 'last_updated', ':v' => $now]);
            self::$cache['last_updated'] = $now;

            $pdo->commit();
            self::invalidateCache();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Hapus setting dari database.
     */
    public static function delete(string $key): void
    {
        Database::getInstance()->prepare('DELETE FROM settings WHERE `key` = :k')
            ->execute([':k' => $key]);

        unset(self::$cache[$key]);
        self::invalidateCache();
    }

    /**
     * Hapus banyak key sekaligus.
     *
     * @param array<string> $keys
     */
    public static function deleteMany(array $keys): void
    {
        if (empty($keys)) return;

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM settings WHERE `key` = :k');
            foreach ($keys as $k) {
                $stmt->execute([':k' => $k]);
                unset(self::$cache[$k]);
            }
            $pdo->commit();
            self::invalidateCache();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /* ============================================================
       ADMIN ACTIONS
       ============================================================ */

    /**
     * Reset semua setting ke default.
     * Digunakan untuk fitur "Reset ke Default" di settings v7.0.
     *
     * @param array<string>|null $keys Jika null, reset semua key terdaftar
     * @return int Jumlah key yang direset
     */
    public static function resetToDefaults(?array $keys = null): int
    {
        $targetKeys = $keys ?? array_keys(self::DEFAULTS);
        $pairs = [];

        foreach ($targetKeys as $k) {
            if (array_key_exists($k, self::DEFAULTS)) {
                $pairs[$k] = self::DEFAULTS[$k];
            }
        }

        if (empty($pairs)) return 0;

        self::setMany($pairs);
        return count($pairs);
    }

    /**
     * Export semua settings sebagai array (untuk JSON export).
     * Digunakan untuk fitur "Ekspor Pengaturan" di settings v7.0.
     *
     * @param bool $includeEmpty Sertakan key dengan value kosong
     * @return array<string, string>
     */
    public static function exportAll(bool $includeEmpty = false): array
    {
        self::loadAll();
        $result = [];

        foreach (self::KEYS as $key) {
            if ($key === 'last_updated') continue; // Skip system key
            $value = self::$cache[$key] ?? '';
            if ($value !== '' || $includeEmpty) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Import settings dari array (untuk JSON import).
     * Hanya menerima key yang terdaftar di KEYS untuk keamanan.
     *
     * @param array<string, string> $data
     * @return int Jumlah key yang diimport
     */
    public static function importSettings(array $data): int
    {
        $pairs = [];
        foreach ($data as $k => $v) {
            // Hanya key yang terdaftar
            if (in_array($k, self::KEYS, true) && $k !== 'last_updated') {
                $pairs[$k] = is_string($v) ? $v : (string) $v;
            }
        }

        if (empty($pairs)) return 0;

        self::setMany($pairs);
        return count($pairs);
    }

    /**
     * Clear cache (force reload dari DB pada akses berikutnya).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
        self::$loaded = false;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Invalidate persistent cache (tapi pertahankan in-memory).
     * Dipanggil otomatis setelah write operation.
     */
    private static function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Touch last_updated timestamp.
     * Untuk fitur "Last saved" indicator di settings v7.0.
     */
    public static function touch(): void
    {
        $now = date('Y-m-d H:i:s');
        self::set('last_updated', $now);
    }

    /**
     * Get last updated timestamp (formatted).
     *
     * @return string Format "d M Y, H:i" atau "Belum pernah" jika kosong
     */
    public static function getLastUpdated(): string
    {
        $value = self::get('last_updated');
        if ($value === '') return 'Belum pernah';

        $ts = strtotime($value);
        if ($ts === false) return 'Belum pernah';

        return date('d M Y, H:i', $ts);
    }

    /**
     * Get last updated as ISO timestamp (untuk <time> tag).
     */
    public static function getLastUpdatedIso(): string
    {
        $value = self::get('last_updated');
        if ($value === '') return '';

        $ts = strtotime($value);
        if ($ts === false) return '';

        return date('c', $ts);
    }

    /* ============================================================
       HELPERS (untuk view)
       ============================================================ */

    /**
     * Cek apakah announcement banner aktif.
     * Convenience method untuk view publik.
     */
    public static function isAnnouncementActive(): bool
    {
        return self::getBool('announcement_active');
    }

    /**
     * Ambil social media links sebagai array (untuk footer).
     *
     * @return array<string, string> ['instagram' => 'url', ...] (hanya yang terisi)
     */
    public static function getSocialLinks(): array
    {
        $keys = ['social_instagram', 'social_youtube', 'social_email', 'social_phone'];
        $result = [];

        foreach ($keys as $key) {
            $value = self::get($key);
            if ($value !== '') {
                // Strip prefix 'social_' untuk key array
                $shortKey = substr($key, 7);
                $result[$shortKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Cek apakah logo sudah diupload.
     */
    public static function hasLogo(): bool
    {
        return self::get('logo') !== '';
    }

    /**
     * Cek apakah favicon sudah diupload.
     */
    public static function hasFavicon(): bool
    {
        return self::get('favicon') !== '';
    }

    /**
     * Get logo URL (jika ada).
     */
    public static function getLogoUrl(): ?string
    {
        $logo = self::get('logo');
        if ($logo === '') return null;

        return function_exists('url')
            ? url('assets/uploads/brand/' . $logo)
            : '/assets/uploads/brand/' . $logo;
    }

    /**
     * Get favicon URL (jika ada).
     */
    public static function getFaviconUrl(): ?string
    {
        $favicon = self::get('favicon');
        if ($favicon === '') return null;

        return function_exists('url')
            ? url('assets/uploads/brand/' . $favicon)
            : '/assets/uploads/brand/' . $favicon;
    }

    /**
     * Get SEO meta tags untuk <head> (untuk view publik).
     *
     * @return array{description: string, keywords: string, author: string}
     */
    public static function getSeoMeta(): array
    {
        return [
            'description' => self::get('meta_description'),
            'keywords'    => self::get('meta_keywords'),
            'author'      => self::get('meta_author', self::get('app_name', 'Organisasi')),
        ];
    }

    /**
     * Get asset delete helper (untuk remove logo/favicon).
     * Digunakan oleh settings.js untuk tombol "Hapus".
     */
    public static function removeAsset(string $key): bool
    {
        if (!in_array($key, ['logo', 'favicon'], true)) {
            return false;
        }

        $oldFile = self::get($key);
        if ($oldFile === '') return false;

        // Hapus dari database
        self::set($key, '');

        // Hapus file fisik (best-effort)
        $path = self::getAssetPath($oldFile);
        if ($path && is_file($path)) {
            @unlink($path);
        }

        return true;
    }

    /**
     * Get absolute path untuk asset brand (logo/favicon).
     */
    private static function getAssetPath(string $filename): ?string
    {
        if (function_exists('public_path')) {
            return public_path('assets/uploads/brand/' . $filename);
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return $base . '/assets/uploads/brand/' . $filename;
    }
}