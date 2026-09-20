<?php
// File: app/Models/Testimonial.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Testimonial — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature lama + extension untuk fitur v7.0:
 * - Decorator kaya (initial, avatar_color, photo_url, quote_short, rating, status_label)
 * - Admin stats (total/published/draft/pending/avg_rating)
 * - Admin list dengan filter status/search/sort/pagination
 * - Bulk actions (delete & update status)
 * - Photo support dengan cleanup
 * - Rating/stars field (1-5)
 * - Status workflow (pending/published/draft)
 * - Cache yang konsisten & granular
 * - Export capabilities
 */
class Testimonial
{
    /** Status constants */
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT     = 'draft';
    public const STATUSES = [self::STATUS_PENDING, self::STATUS_PUBLISHED, self::STATUS_DRAFT];

    /** Rating range */
    public const MIN_RATING = 1;
    public const MAX_RATING = 5;

    /** Cache TTL (5 menit) */
    private const CACHE_TTL = 300;

    /** Upload directory untuk photo testimonial */
    private const PHOTO_DIR = 'assets/uploads/testimonials/';

    /** Sort columns whitelist */
    private const SORT_WHITELIST = ['name', 'rating', 'created_at', 'id'];

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - initial (huruf pertama nama, untuk avatar placeholder)
     * - photo_url (URL lengkap photo)
     * - avatar_color (gradient class berdasarkan hash nama)
     * - quote_short (quote truncated untuk card preview)
     * - quote_html (quote dengan line breaks preserved)
     * - status_label ("Tertunda" / "Diterbitkan" / "Draft")
     * - status_color (CSS class untuk badge)
     * - date_formatted ("20 September 2026")
     * - date_short ("20 Sep 2026")
     * - date_iso (ISO 8601 untuk <time> tag)
     * - relative_time ("3 hari lalu")
     * - rating_stars (array [1,1,1,1,0] untuk render bintang)
     * - is_published, is_pending, is_draft (boolean flags)
     */
    private static function decorate(array $row): array
    {
        // Initial untuk avatar placeholder
        $name = $row['name'] ?? '';
        $row['initial'] = strtoupper(mb_substr(trim($name), 0, 1));

        // Avatar color (deterministic berdasarkan nama)
        $row['avatar_color'] = self::avatarColorClass($name);

        // Photo URL
        if (!empty($row['photo'])) {
            $row['photo_url'] = function_exists('url')
                ? url(self::PHOTO_DIR . $row['photo'])
                : '/' . self::PHOTO_DIR . $row['photo'];
        } else {
            $row['photo_url'] = null;
        }

        // Quote formatting
        $quote = $row['quote'] ?? '';
        $row['quote_short'] = mb_strlen($quote) > 150
            ? mb_substr($quote, 0, 147) . '...'
            : $quote;
        $row['quote_html'] = nl2br(e($quote));

        // Status handling
        $status = $row['status'] ?? self::STATUS_PUBLISHED;
        $row['status'] = $status;
        $row['status_label'] = match ($status) {
            self::STATUS_PUBLISHED => 'Diterbitkan',
            self::STATUS_PENDING   => 'Menunggu Review',
            self::STATUS_DRAFT     => 'Draft',
            default                => ucfirst($status),
        };
        $row['status_color'] = match ($status) {
            self::STATUS_PUBLISHED => 'ok',
            self::STATUS_PENDING   => 'warn',
            self::STATUS_DRAFT     => 'info',
            default                => 'default',
        };
        $row['is_published'] = ($status === self::STATUS_PUBLISHED);
        $row['is_pending']   = ($status === self::STATUS_PENDING);
        $row['is_draft']     = ($status === self::STATUS_DRAFT);

        // Rating handling
        $rating = max(self::MIN_RATING, min(self::MAX_RATING, (int) ($row['rating'] ?? 5)));
        $row['rating'] = $rating;
        $row['rating_stars'] = array_fill(0, self::MAX_RATING, 0);
        for ($i = 0; $i < $rating; $i++) {
            $row['rating_stars'][$i] = 1;
        }

        // Date formatting
        if (!empty($row['created_at'])) {
            $ts = strtotime($row['created_at']);
            if ($ts !== false) {
                $row['date_formatted'] = self::formatDateId($ts);
                $row['date_short']     = date('d M Y', $ts);
                $row['date_iso']       = date('Y-m-d', $ts);
                $row['relative_time']  = self::relativeTime($ts);
            } else {
                $row['date_formatted'] = '—';
                $row['date_short']     = '—';
                $row['date_iso']       = '';
                $row['relative_time']  = '—';
            }
        } else {
            $row['date_formatted'] = '—';
            $row['date_short']     = '—';
            $row['date_iso']       = '';
            $row['relative_time']  = '—';
        }

        // Role fallback
        $row['role'] = $row['role'] ?? '';

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /**
     * Deterministic avatar color class berdasarkan nama.
     * Return: grad-1 sampai grad-6
     */
    private static function avatarColorClass(string $name): string
    {
        $hash = crc32(strtolower(trim($name)));
        $index = (abs($hash) % 6) + 1;
        return 'grad-' . $index;
    }

    /**
     * Format tanggal Bahasa Indonesia.
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

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'testimonials|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('testimonials');
        Cache::flush('testimonials_'); // Backward compat
    }

    /* ============================================================
       PUBLIC METHODS (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Semua testimonial (backward compat + decorator).
     */
    public static function all(): array
    {
        $cacheKey = self::cacheKey('all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $result = self::decorateAll(
            Database::getInstance()->query(
                'SELECT * FROM testimonials ORDER BY id DESC'
            )->fetchAll()
        );

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Count total testimonial (backward compat + cached).
     */
    public static function countAll(): int
    {
        $cacheKey = self::cacheKey('count_all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM testimonials')
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Testimonial terbaru (backward compat + decorator + cached).
     * Default hanya ambil yang published.
     */
    public static function latest(int $limit = 10, bool $onlyPublished = true): array
    {
        $suffix = 'latest_' . $limit . ($onlyPublished ? '_pub' : '_all');
        $cacheKey = self::cacheKey($suffix);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $sql = 'SELECT * FROM testimonials';
        $params = [];

        if ($onlyPublished) {
            $sql .= ' WHERE status = :status';
            $params[':status'] = self::STATUS_PUBLISHED;
        }

        $sql .= ' ORDER BY rating DESC, id DESC LIMIT ' . (int) $limit;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Featured testimonials (rating tertinggi + published).
     * Untuk section hero/unggulan di halaman publik.
     */
    public static function featured(int $limit = 3): array
    {
        $cacheKey = self::cacheKey('featured_' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM testimonials
             WHERE status = :status
             ORDER BY rating DESC, id DESC
             LIMIT ' . (int) $limit
        );
        $stmt->execute([':status' => self::STATUS_PUBLISHED]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Search testimonial (backward compat + decorator).
     */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 10): array
    {
        $like   = '%' . $keyword . '%';
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM testimonials
             WHERE (name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3)
               AND status = :status
             ORDER BY id DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([
            ':q1' => $like, ':q2' => $like, ':q3' => $like,
            ':status' => self::STATUS_PUBLISHED,
        ]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM testimonials
             WHERE (name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3)
               AND status = :status'
        );
        $stmt->execute([
            ':q1' => $like, ':q2' => $like, ':q3' => $like,
            ':status' => self::STATUS_PUBLISHED,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Find by ID (backward compat + decorator).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Alias find untuk konsistensi dengan model lain.
     */
    public static function findAny(int $id): ?array
    {
        return self::find($id);
    }

    /* ============================================================
       ADMIN METHODS (BARU v7.0)
       ============================================================ */

    /**
     * Statistik lengkap untuk admin dashboard testimonials.
     *
     * @return array{
     *     total: int,
     *     published: int,
     *     pending: int,
     *     draft: int,
     *     avg_rating: float,
     *     with_photo: int,
     *     five_star: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();

        // Count by status (single query)
        $stmt = $db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
                AVG(rating) AS avg_rating,
                SUM(CASE WHEN photo IS NOT NULL AND photo != '' THEN 1 ELSE 0 END) AS with_photo,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS five_star
             FROM testimonials"
        );
        $row = $stmt->fetch();

        $stats = [
            'total'      => (int) ($row['total'] ?? 0),
            'published'  => (int) ($row['published'] ?? 0),
            'pending'    => (int) ($row['pending'] ?? 0),
            'draft'      => (int) ($row['draft'] ?? 0),
            'avg_rating' => round((float) ($row['avg_rating'] ?? 0), 2),
            'with_photo' => (int) ($row['with_photo'] ?? 0),
            'five_star'  => (int) ($row['five_star'] ?? 0),
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * Count by status (untuk filter pills dengan badges).
     *
     * @return array{published: int, pending: int, draft: int, total: int}
     */
    public static function countByStatus(): array
    {
        $stats = self::adminStats();
        return [
            'published' => $stats['published'],
            'pending'   => $stats['pending'],
            'draft'     => $stats['draft'],
            'total'     => $stats['total'],
        ];
    }

    /**
     * List testimonial untuk admin (filter + search + pagination + sort).
     *
     * @param string $status  Filter: all/published/pending/draft
     * @param string $search  Search di name/role/quote
     * @param int    $rating  Filter rating minimum (0 = semua)
     * @param string $sort    Kolom sort (name/rating/created_at/id)
     * @param string $order   Arah sort (asc/desc)
     * @param int    $page    Halaman (1-based)
     * @param int    $perPage Item per halaman
     */
    public static function adminList(
        string $status = '',
        string $search = '',
        int $rating = 0,
        string $sort = 'created_at',
        string $order = 'desc',
        int $page = 1,
        int $perPage = 10
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM testimonials';

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($rating > 0 && $rating <= self::MAX_RATING) {
            $where[] = 'rating >= :rating';
            $params[':rating'] = $rating;
        }

        if ($search !== '') {
            $where[] = '(name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort
        if (!in_array($sort, self::SORT_WHITELIST, true)) $sort = 'created_at';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sort $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList (filter yang sama).
     */
    public static function countAdminList(
        string $status = '',
        string $search = '',
        int $rating = 0
    ): int {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM testimonials';

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $where[] = 'status = :status';
            $params[':status'] = $status;
        }

        if ($rating > 0 && $rating <= self::MAX_RATING) {
            $where[] = 'rating >= :rating';
            $params[':rating'] = $rating;
        }

        if ($search !== '') {
            $where[] = '(name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ============================================================
       CRUD (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Create testimonial (backward compat + extended).
     * Extended: support photo, rating, status.
     */
    public static function create(array $d): int
    {
        $rating = max(self::MIN_RATING, min(self::MAX_RATING, (int) ($d['rating'] ?? 5)));
        $status = in_array($d['status'] ?? '', self::STATUSES, true)
            ? $d['status']
            : self::STATUS_PENDING;

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO testimonials (name, role, quote, photo, rating, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $d['name'],
            $d['role'] ?? null,
            $d['quote'],
            $d['photo'] ?? null,
            $rating,
            $status,
        ]);

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update testimonial (backward compat + extended).
     * Extended: support photo, rating, status.
     */
    public static function update(int $id, array $d): void
    {
        $rating = max(self::MIN_RATING, min(self::MAX_RATING, (int) ($d['rating'] ?? 5)));
        $status = in_array($d['status'] ?? '', self::STATUSES, true)
            ? $d['status']
            : self::STATUS_PUBLISHED;

        if (isset($d['photo'])) {
            Database::getInstance()->prepare(
                'UPDATE testimonials SET name = ?, role = ?, quote = ?, photo = ?, rating = ?, status = ? WHERE id = ?'
            )->execute([
                $d['name'],
                $d['role'] ?? null,
                $d['quote'],
                $d['photo'],
                $rating,
                $status,
                $id,
            ]);
        } else {
            Database::getInstance()->prepare(
                'UPDATE testimonials SET name = ?, role = ?, quote = ?, rating = ?, status = ? WHERE id = ?'
            )->execute([
                $d['name'],
                $d['role'] ?? null,
                $d['quote'],
                $rating,
                $status,
                $id,
            ]);
        }

        self::clearCache();
    }

    /**
     * Delete testimonial dengan cleanup photo (backward compat + extended).
     */
    public static function delete(int $id): void
    {
        // Ambil row dulu untuk cleanup photo
        $row = self::find($id);

        Database::getInstance()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);

        // Cleanup photo file (best-effort)
        if ($row && !empty($row['photo'])) {
            $path = self::getPhotoPath($row['photo']);
            if ($path && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /**
     * Update status testimonial.
     */
    public static function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) return;

        Database::getInstance()->prepare(
            'UPDATE testimonials SET status = ? WHERE id = ?'
        )->execute([$status, $id]);

        self::clearCache();
    }

    /* ============================================================
       BULK ACTIONS (BARU v7.0)
       ============================================================ */

    /**
     * Bulk delete dengan cleanup photo.
     *
     * @return int Jumlah yang berhasil dihapus
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        // Ambil photos untuk cleanup
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "SELECT id, photo FROM testimonials WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $rows = $stmt->fetchAll();

        if (empty($rows)) return 0;

        // Delete
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM testimonials WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $affected = $stmt->rowCount();

        // Cleanup photos
        foreach ($rows as $row) {
            if (!empty($row['photo'])) {
                $path = self::getPhotoPath($row['photo']);
                if ($path && is_file($path)) {
                    @unlink($path);
                }
            }
        }

        self::clearCache();
        return $affected;
    }

    /**
     * Bulk update status.
     *
     * @return int Jumlah yang berhasil diupdate
     */
    public static function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) return 0;
        if (!in_array($status, self::STATUSES, true)) return 0;

        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE testimonials SET status = ? WHERE id IN ($placeholders)"
        );
        $params = array_merge([$status], array_values($ids));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       EXPORT
       ============================================================ */

    /**
     * Semua testimonial untuk export (dengan decorator).
     */
    public static function allForExport(string $status = ''): array
    {
        $params = [];
        $sql = 'SELECT * FROM testimonials';

        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $sql .= ' WHERE status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY rating DESC, id DESC';

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Convert row ke format CSV-friendly.
     */
    public static function toCsvRow(array $row): array
    {
        return [
            'ID'        => $row['id'] ?? '',
            'Nama'      => $row['name'] ?? '',
            'Jabatan'   => $row['role'] ?? '',
            'Rating'    => $row['rating'] ?? '',
            'Testimoni' => $row['quote'] ?? '',
            'Status'    => $row['status_label'] ?? '',
            'Tanggal'   => $row['date_short'] ?? '',
        ];
    }

    /* ============================================================
       SUBMISSION (untuk form publik)
       ============================================================ */

    /**
     * Submit testimonial dari form publik.
     * Default status = pending (butuh approval admin).
     *
     * @return int ID testimonial baru
     */
    public static function submit(array $d): int
    {
        $d['status'] = self::STATUS_PENDING;
        return self::create($d);
    }

    /**
     * Approve testimonial (publish).
     */
    public static function approve(int $id): void
    {
        self::updateStatus($id, self::STATUS_PUBLISHED);
    }

    /**
     * Reject testimonial (kembalikan ke draft).
     */
    public static function reject(int $id): void
    {
        self::updateStatus($id, self::STATUS_DRAFT);
    }

    /* ============================================================
       HELPERS
       ============================================================ */

    /**
     * Get absolute path untuk file photo.
     */
    private static function getPhotoPath(string $filename): ?string
    {
        if (function_exists('public_path')) {
            return public_path(self::PHOTO_DIR . $filename);
        }
        $base = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2) . '/public';
        return $base . '/' . self::PHOTO_DIR . $filename;
    }

    /**
     * Get average rating (published only).
     */
    public static function averageRating(): float
    {
        $stats = self::adminStats();
        return $stats['avg_rating'];
    }

    /**
     * Count pending testimonials (untuk notification badge admin).
     */
    public static function countPending(): int
    {
        $stats = self::adminStats();
        return $stats['pending'];
    }

    /* ============================================================
       MAINTENANCE
       ============================================================ */

    /**
     * Cleanup orphan files.
     *
     * @param bool $dryRun Jika true, hanya return list file
     * @return array List file orphan
     */
    public static function cleanupOrphanFiles(bool $dryRun = true): array
    {
        $uploadPath = self::getPhotoPath('');
        if (!$uploadPath || !is_dir($uploadPath)) return [];

        $dbFiles = array_column(
            Database::getInstance()->query('SELECT photo FROM testimonials')->fetchAll(),
            'photo'
        );
        $dbFiles = array_filter($dbFiles);

        $diskFiles = array_diff(scandir($uploadPath), ['.', '..']);
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