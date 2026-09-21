<?php
// File: app/Models/Gallery.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Gallery — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature lama + extension untuk fitur v7.0:
 * - Decorator kaya (date formatted, image URL, aspect ratio)
 * - Admin stats, list, search, filter (year/location), pagination
 * - Bulk delete dengan cleanup file
 * - Years & locations dengan count (untuk filter badges)
 * - Cache yang konsisten & granular
 */
class Gallery
{
    /** Cache TTL default (5 menit) */
    private const CACHE_TTL = 300;
    private const CACHE_TTL_LONG = 600;

    /** Upload directory (relative to public path) */
    private const UPLOAD_DIR = 'assets/uploads/galleries/';

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - date_formatted (format Indonesia: "20 September 2026")
     * - date_short (format pendek: "20 Sep 2026")
     * - date_iso (ISO 8601 untuk <time> tag)
     * - year (ekstrak tahun untuk filter)
     * - relative_time ("3 hari lalu", "2 minggu lalu")
     * - image_url (URL lengkap ke gambar)
     * - thumbnail_url (jika ada thumbnail)
     * - aspect_ratio (default '1:1' jika tidak ada)
     * - initial (huruf pertama title, untuk placeholder)
     * - location_slug (untuk filter URL)
     */
    private static function decorate(array $row): array
    {
        // Date formatting
        if (!empty($row['event_date'])) {
            $ts = strtotime($row['event_date']);
            if ($ts !== false) {
                $row['date_formatted'] = self::formatDateId($ts);
                $row['date_short']     = date('d M Y', $ts);
                $row['date_iso']       = date('Y-m-d', $ts);
                $row['year']           = (int) date('Y', $ts);
                $row['relative_time']  = self::relativeTime($ts);
            } else {
                $row['date_formatted'] = '—';
                $row['date_short']     = '—';
                $row['date_iso']       = '';
                $row['year']           = null;
                $row['relative_time']  = '—';
            }
        } else {
            $row['date_formatted'] = '—';
            $row['date_short']     = '—';
            $row['date_iso']       = '';
            $row['year']           = null;
            $row['relative_time']  = '—';
        }

        // Image URL builder
        if (!empty($row['image'])) {
            if (function_exists('url')) {
                $row['image_url'] = url(self::UPLOAD_DIR . $row['image']);
            } else {
                $row['image_url'] = '/' . self::UPLOAD_DIR . $row['image'];
            }
        } else {
            $row['image_url'] = null;
        }

        // Thumbnail URL (jika ada kolom thumbnail, fallback ke image)
        if (!empty($row['thumbnail'])) {
            $row['thumbnail_url'] = function_exists('url')
                ? url(self::UPLOAD_DIR . $row['thumbnail'])
                : '/' . self::UPLOAD_DIR . $row['thumbnail'];
        } else {
            $row['thumbnail_url'] = $row['image_url'];
        }

        // Aspect ratio (default 1:1 untuk masonry fallback)
        $row['aspect_ratio'] = $row['aspect_ratio'] ?? '1:1';

        // Initial untuk placeholder
        $title = $row['title'] ?? '';
        $row['initial'] = strtoupper(mb_substr($title, 0, 1));

        // Location slug untuk filter URL
        if (!empty($row['location'])) {
            $row['location_slug'] = self::slugify($row['location']);
        } else {
            $row['location_slug'] = '';
        }

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * Format tanggal Bahasa Indonesia.
     * Contoh: "20 September 2026"
     */
    private static function formatDateId(int $timestamp): string
    {
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $day   = date('j', $timestamp);
        $month = $months[(int) date('n', $timestamp)];
        $year  = date('Y', $timestamp);
        return "$day $month $year";
    }

    /**
     * Relative time Bahasa Indonesia.
     * Contoh: "3 hari lalu", "2 minggu lalu"
     */
    private static function relativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;
        $days = (int) floor($diff / 86400);

        if ($days === 0) return 'Hari ini';
        if ($days === 1) return 'Kemarin';
        if ($days < 7) return "$days hari lalu";
        if ($days < 14) return 'Minggu lalu';
        if ($days < 30) return (int) floor($days / 7) . ' minggu lalu';
        if ($days < 60) return 'Bulan lalu';
        if ($days < 365) return (int) floor($days / 30) . ' bulan lalu';
        return (int) floor($days / 365) . ' tahun lalu';
    }

    /**
     * Slugify untuk URL.
     */
    private static function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'galleries|' . $suffix;
    }

    private static function clearCache(): void
    {
        // Flush semua cache galleries (backward compat dengan pattern lama)
        Cache::flush('galleries');
        Cache::flush('galleries_');
    }

    /* ============================================================
       PUBLIC METHODS (BACKWARD COMPAT)
       ============================================================ */

    /**
     * Ambil semua galeri (admin, dengan limit opsional).
     * Backward compat signature, dengan decorator.
     */
    public static function all(int $limit = 0): array
    {
        $cacheKey = self::cacheKey('all_' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $sql = 'SELECT * FROM galleries ORDER BY event_date DESC, id DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;

        $result = self::decorateAll(Database::getInstance()->query($sql)->fetchAll());
        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Count total galeri.
     */
    public static function countAll(): int
    {
        $cacheKey = self::cacheKey('count_all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM galleries')
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Alias countAll untuk backward compat.
     */
    public static function countPublic(): int
    {
        return self::countAll();
    }

    /**
     * Galeri publik dengan pagination.
     * Backward compat signature, dengan decorator.
     */
    public static function publicAll(int $page = 1, int $perPage = 12): array
    {
        $cacheKey = self::cacheKey('public_' . $page . '_' . $perPage);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries ORDER BY event_date DESC, id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute();
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Search galeri.
     * Backward compat signature, dengan decorator.
     */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 12): array
    {
        $like   = '%' . $keyword . '%';
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries
             WHERE title LIKE :q1 OR location LIKE :q2
             ORDER BY event_date DESC, id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM galleries WHERE title LIKE :q1 OR location LIKE :q2'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Tahun unik (backward compat signature).
     * Return: array tahun [2026, 2025, 2024, ...]
     */
    public static function uniqueYears(): array
    {
        $cacheKey = self::cacheKey('years');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            'SELECT DISTINCT YEAR(event_date) as year FROM galleries WHERE event_date IS NOT NULL ORDER BY year DESC'
        );
        $result = array_map('intval', array_column($stmt->fetchAll(), 'year'));

        Cache::set($cacheKey, $result, self::CACHE_TTL_LONG);
        return $result;
    }

    /**
     * Tahun unik dengan count (untuk view badges).
     *
     * @return array<int, int> [2026 => 15, 2025 => 23, ...]
     */
    public static function yearsWithCount(): array
    {
        $cacheKey = self::cacheKey('years_count');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            'SELECT YEAR(event_date) as year, COUNT(*) as total
             FROM galleries
             WHERE event_date IS NOT NULL
             GROUP BY YEAR(event_date)
             ORDER BY year DESC'
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['year']] = (int) $row['total'];
        }

        Cache::set($cacheKey, $result, self::CACHE_TTL_LONG);
        return $result;
    }

    /**
     * Lokasi unik (backward compat signature).
     * Return: array lokasi ['Jakarta', 'Bandung', ...]
     */
    public static function uniqueLocations(): array
    {
        $cacheKey = self::cacheKey('locations');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            'SELECT DISTINCT location FROM galleries WHERE location IS NOT NULL AND location != "" ORDER BY location'
        );
        $result = array_column($stmt->fetchAll(), 'location');

        Cache::set($cacheKey, $result, self::CACHE_TTL_LONG);
        return $result;
    }

    /**
     * Lokasi unik dengan count (untuk view badges).
     *
     * @return array<string, int> ['Jakarta' => 15, 'Bandung' => 8, ...]
     */
    public static function locationsWithCount(): array
    {
        $cacheKey = self::cacheKey('locations_count');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            'SELECT location, COUNT(*) as total
             FROM galleries
             WHERE location IS NOT NULL AND location != ""
             GROUP BY location
             ORDER BY total DESC, location ASC'
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['location']] = (int) $row['total'];
        }

        Cache::set($cacheKey, $result, self::CACHE_TTL_LONG);
        return $result;
    }

    /**
     * Find by ID (dengan decorator).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM galleries WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Alias find (untuk konsistensi dengan model lain).
     */
    public static function findAny(int $id): ?array
    {
        return self::find($id);
    }

    /**
     * Galeri by year dengan pagination.
     * Backward compat signature, dengan decorator.
     */
    public static function byYear(int $year, int $page = 1, int $perPage = 12): array
    {
        $cacheKey = self::cacheKey('year_' . $year . '_' . $page . '_' . $perPage);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries WHERE YEAR(event_date) = :year ORDER BY event_date DESC, id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':year' => $year]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    public static function countByYear(int $year): int
    {
        $cacheKey = self::cacheKey('count_year_' . $year);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM galleries WHERE YEAR(event_date) = :year'
        );
        $stmt->execute([':year' => $year]);
        $count = (int) $stmt->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Galeri by location dengan pagination (baru v7.0).
     */
    public static function byLocation(string $location, int $page = 1, int $perPage = 12): array
    {
        $cacheKey = self::cacheKey('loc_' . md5($location) . '_' . $page . '_' . $perPage);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $offset = max(0, ($page - 1) * $perPage);
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries WHERE location = :loc ORDER BY event_date DESC, id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':loc' => $location]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    public static function countByLocation(string $location): int
    {
        $cacheKey = self::cacheKey('count_loc_' . md5($location));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM galleries WHERE location = :loc'
        );
        $stmt->execute([':loc' => $location]);
        $count = (int) $stmt->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /* ============================================================
       CRUD (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Create galeri.
     * Extended: support aspect_ratio.
     */
    public static function create(array $d): int
    {
        $aspectRatio = $d['aspect_ratio'] ?? '1:1';

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO galleries (title, image, event_date, location, aspect_ratio)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['title'],
            $d['image'],
            $d['event_date'] ?: null,
            $d['location'] ?: null,
            $aspectRatio,
        ]);

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update galeri.
     * Extended: support aspect_ratio.
     */
    public static function update(int $id, array $d): void
    {
        $aspectRatio = $d['aspect_ratio'] ?? '1:1';

        Database::getInstance()->prepare(
            'UPDATE galleries SET title = ?, image = ?, event_date = ?, location = ?, aspect_ratio = ? WHERE id = ?'
        )->execute([
            $d['title'],
            $d['image'],
            $d['event_date'] ?: null,
            $d['location'] ?: null,
            $aspectRatio,
            $id,
        ]);

        self::clearCache();
    }

    /**
     * Delete galeri dengan cleanup file.
     */
    public static function delete(int $id): void
    {
        // Ambil row dulu untuk cleanup
        $row = self::find($id);

        Database::getInstance()->prepare('DELETE FROM galleries WHERE id = ?')->execute([$id]);

        // Cleanup file image (best-effort)
        if ($row && !empty($row['image'])) {
            $path = self::getUploadPath($row['image']);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /* ============================================================
       ADMIN METHODS (BARU v7.0)
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard galeri.
     *
     * @return array{
     *     total: int,
     *     years: int,
     *     locations: int,
     *     this_year: int,
     *     latest_date: ?string
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();

        $total = (int) $db->query('SELECT COUNT(*) FROM galleries')->fetchColumn();
        $years = count(self::uniqueYears());
        $locations = count(self::uniqueLocations());

        // Query this_year (clean & single execution)
        $stmt = $db->prepare('SELECT COUNT(*) FROM galleries WHERE YEAR(event_date) = YEAR(CURDATE())');
        $stmt->execute();
        $thisYear = (int) $stmt->fetchColumn();

        $latestDate = $db->query('SELECT MAX(event_date) FROM galleries')->fetchColumn();

        $stats = [
            'total'       => $total,
            'years'       => $years,
            'locations'   => $locations,
            'this_year'   => $thisYear,
            'latest_date' => $latestDate ?: null,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * List galeri untuk admin (filter + search + pagination).
     *
     * @param string $year     Filter tahun (YYYY atau '')
     * @param string $location Filter lokasi ('' = semua)
     * @param string $search   Search di title/location
     * @param int    $page     Halaman (1-based)
     * @param int    $perPage  Item per halaman
     */
    public static function adminList(
        string $year = '',
        string $location = '',
        string $search = '',
        int $page = 1,
        int $perPage = 12
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM galleries';

        if ($year !== '' && preg_match('/^\d{4}$/', $year)) {
            $where[] = 'YEAR(event_date) = :year';
            $params[':year'] = (int) $year;
        }

        if ($location !== '') {
            $where[] = 'location = :loc';
            $params[':loc'] = $location;
        }

        if ($search !== '') {
            $where[] = '(title LIKE :q1 OR location LIKE :q2)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY event_date DESC, id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList.
     */
    public static function countAdminList(
        string $year = '',
        string $location = '',
        string $search = ''
    ): int {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM galleries';

        if ($year !== '' && preg_match('/^\d{4}$/', $year)) {
            $where[] = 'YEAR(event_date) = :year';
            $params[':year'] = (int) $year;
        }

        if ($location !== '') {
            $where[] = 'location = :loc';
            $params[':loc'] = $location;
        }

        if ($search !== '') {
            $where[] = '(title LIKE :q1 OR location LIKE :q2)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Bulk delete dengan cleanup file.
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        // Cleanup file images
        foreach ($ids as $id) {
            $row = self::find($id);
            if ($row && !empty($row['image'])) {
                $path = self::getUploadPath($row['image']);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM galleries WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    /**
     * Get absolute path untuk file upload.
     */
    private static function getUploadPath(string $filename): ?string
    {
        if (function_exists('public_path')) {
            return public_path(self::UPLOAD_DIR . $filename);
        }
        // Fallback: relative to current dir
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return $base . '/' . self::UPLOAD_DIR . $filename;
    }

    /* ============================================================
       MAINTENANCE
       ============================================================ */

    /**
     * Cleanup orphan files (file yang tidak ada di database).
     * Gunakan hati-hati, sebaiknya dry-run dulu.
     *
     * @param bool $dryRun Jika true, hanya return list file orphan tanpa hapus
     * @return array List file yang orphan (atau yang dihapus)
     */
    public static function cleanupOrphanFiles(bool $dryRun = true): array
    {
        $uploadPath = self::getUploadPath('');
        if (!$uploadPath || !is_dir($uploadPath)) return [];

        // Ambil semua filename dari database
        $dbFiles = array_column(
            Database::getInstance()->query('SELECT image FROM galleries')->fetchAll(),
            'image'
        );
        $dbFiles = array_filter($dbFiles);

        // Scan directory
        $diskFiles = array_diff(scandir($uploadPath), ['.', '..']);

        // Cari orphan
        $orphans = array_diff($diskFiles, $dbFiles);

        if (!$dryRun) {
            foreach ($orphans as $file) {
                $path = $uploadPath . '/' . $file;
                if (is_file($path)) @unlink($path);
            }
        }

        return array_values($orphans);
    }
}