<?php
// File: app/Models/Officer.php (FINAL v7.0 — EXTENDED + CACHED + BACKWARD COMPAT)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Officer — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature lama + extension untuk fitur v7.0:
 * - Decorator kaya (initial, photo_url, division_slug, avatar_color)
 * - Admin stats, list, search, filter (division), pagination
 * - Bulk actions (delete & update division)
 * - Divisions dengan count (untuk filter pills)
 * - Cache yang konsisten & granular
 * - Photo cleanup saat delete
 * - Social media support
 * - Reorder yang aman
 */
class Officer
{
    /** Cache TTL default (5 menit) */
    private const CACHE_TTL = 300;

    /** Upload directory untuk foto pengurus */
    private const PHOTO_DIR = 'assets/uploads/officers/';

    /** Sort columns whitelist */
    private const SORT_WHITELIST = ['full_name', 'position', 'division', 'sort_order', 'created_at'];

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Decorate row dengan informasi tambahan untuk UI.
     *
     * Menambahkan:
     * - initial (huruf pertama nama, untuk avatar placeholder)
     * - photo_url (URL lengkap photo)
     * - division_slug (untuk filter URL)
     * - avatar_color (gradient class berdasarkan hash nama)
     * - position_short (singkatan jika terlalu panjang)
     * - has_social (boolean, ada social media)
     * - social_links (parsed array dari JSON atau string)
     * - bio_short (bio truncated)
     */
    private static function decorate(array $row): array
    {
        // Initial untuk avatar placeholder
        $name = $row['full_name'] ?? '';
        $row['initial'] = strtoupper(mb_substr(trim($name), 0, 1));

        // Photo URL
        if (!empty($row['photo'])) {
            $row['photo_url'] = function_exists('url')
                ? url(self::PHOTO_DIR . $row['photo'])
                : '/' . self::PHOTO_DIR . $row['photo'];
        } else {
            $row['photo_url'] = null;
        }

        // Avatar color (deterministic berdasarkan nama)
        $row['avatar_color'] = self::avatarColorClass($name);

        // Division slug untuk filter URL
        $division = $row['division'] ?? '';
        $row['division'] = $division ?: 'Lainnya';
        $row['division_slug'] = self::slugify($division ?: 'lainnya');

        // Position short (truncate jika > 30 char)
        $position = $row['position'] ?? '';
        $row['position_short'] = mb_strlen($position) > 30
            ? mb_substr($position, 0, 27) . '...'
            : $position;

        // Bio short (truncate jika > 100 char)
        $bio = $row['bio'] ?? '';
        $row['bio_short'] = mb_strlen($bio) > 100
            ? mb_substr($bio, 0, 97) . '...'
            : $bio;

        // Social links parsing
        $row['social_links'] = self::parseSocialLinks($row);
        $row['has_social'] = !empty($row['social_links']);

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
     * Slugify untuk URL.
     */
    private static function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Parse social links dari berbagai format.
     * Support: JSON string, serialized array, atau kolom terpisah.
     *
     * @return array<string, string> ['instagram' => 'url', 'twitter' => 'url', ...]
     */
    private static function parseSocialLinks(array $row): array
    {
        $links = [];

        // Coba parse dari kolom social (JSON)
        if (!empty($row['social'])) {
            $decoded = json_decode($row['social'], true);
            if (is_array($decoded)) {
                $links = $decoded;
            }
        }

        // Fallback: kolom terpisah (jika ada)
        $socialFields = ['instagram', 'twitter', 'facebook', 'linkedin', 'email', 'phone'];
        foreach ($socialFields as $field) {
            if (!empty($row[$field]) && !isset($links[$field])) {
                $links[$field] = $row[$field];
            }
        }

        // Filter empty
        return array_filter($links, fn($v) => !empty($v));
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    private static function cacheKey(string $suffix): string
    {
        return 'officers|' . $suffix;
    }

    private static function clearCache(): void
    {
        Cache::flush('officers');
        Cache::flush('officers_'); // Backward compat
    }

    /* ============================================================
       PUBLIC METHODS (BACKWARD COMPAT + EXTENDED)
       ============================================================ */

    /**
     * Semua pengurus (backward compat + decorator).
     */
    public static function all(): array
    {
        $cacheKey = self::cacheKey('all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $result = self::decorateAll(
            Database::getInstance()->query(
                'SELECT * FROM officers ORDER BY sort_order ASC, id ASC'
            )->fetchAll()
        );

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Count total pengurus (backward compat + cached).
     */
    public static function countAll(): int
    {
        $cacheKey = self::cacheKey('count_all');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $count = (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM officers')
            ->fetchColumn();

        Cache::set($cacheKey, $count, self::CACHE_TTL);
        return $count;
    }

    /**
     * Count per division (backward compat + cached).
     *
     * @return array<string, int> ['Ketua' => 1, 'Sekretaris' => 2, ...]
     */
    public static function countByDivision(): array
    {
        $cacheKey = self::cacheKey('count_by_division');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->query(
            'SELECT division, COUNT(*) as count FROM officers
             GROUP BY division ORDER BY count DESC'
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $divName = $row['division'] ?: 'Lainnya';
            $result[$divName] = (int) $row['count'];
        }

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Divisions dengan count (untuk filter pills badges di v7.0).
     * Alias dari countByDivision() untuk konsistensi dengan model lain.
     *
     * @return array<string, int>
     */
    public static function divisionsWithCount(): array
    {
        return self::countByDivision();
    }

    /**
     * Daftar nama division (backward compat).
     */
    public static function divisions(): array
    {
        $counts = self::countByDivision();
        return array_keys($counts);
    }

    /**
     * Group pengurus by division (backward compat + cached + decorator).
     *
     * @return array<string, array> ['Ketua' => [...], 'Sekretaris' => [...]]
     */
    public static function groupByDivision(): array
    {
        $cacheKey = self::cacheKey('by_division');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $all = self::all();
        $groups = [];
        foreach ($all as $o) {
            $div = !empty($o['division']) ? $o['division'] : 'Lainnya';
            if (!isset($groups[$div])) $groups[$div] = [];
            $groups[$div][] = $o;
        }

        Cache::set($cacheKey, $groups, self::CACHE_TTL);
        return $groups;
    }

    /**
     * Pengurus by division (backward compat + cached + decorator).
     */
    public static function byDivision(string $division): array
    {
        $cacheKey = self::cacheKey('by_div_' . md5($division));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM officers WHERE division = :div ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([':div' => $division]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Search pengurus (backward compat + decorator).
     */
    public static function search(string $keyword = '', int $page = 1, int $perPage = 10): array
    {
        $like   = '%' . $keyword . '%';
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM officers
             WHERE full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3
             ORDER BY sort_order ASC, id ASC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = ''): int
    {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM officers WHERE full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Find by ID (backward compat + decorator).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare('SELECT * FROM officers WHERE id = :id LIMIT 1');
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
     * Statistik lengkap untuk admin dashboard officers.
     *
     * @return array{
     *     total: int,
     *     divisions: int,
     *     with_photo: int,
     *     without_photo: int,
     *     recent_count: int
     * }
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();

        $total = (int) $db->query('SELECT COUNT(*) FROM officers')->fetchColumn();
        $divisions = count(self::divisions());

        $withPhoto = (int) $db->query(
            "SELECT COUNT(*) FROM officers WHERE photo IS NOT NULL AND photo != ''"
        )->fetchColumn();

        $withoutPhoto = $total - $withPhoto;

        // Recent (added in last 30 days) - jika ada kolom created_at
        $recentCount = 0;
        try {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM officers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stmt->execute();
            $recentCount = (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            // Kolom created_at mungkin belum ada
            $recentCount = 0;
        }

        $stats = [
            'total'         => $total,
            'divisions'     => $divisions,
            'with_photo'    => $withPhoto,
            'without_photo' => $withoutPhoto,
            'recent_count'  => $recentCount,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * List pengurus untuk admin (filter + search + pagination + sort).
     *
     * @param string $division Filter division ('' = semua)
     * @param string $search   Search di nama/position/division
     * @param string $sort     Kolom sort
     * @param string $order    Arah sort (asc/desc)
     * @param int    $page     Halaman (1-based)
     * @param int    $perPage  Item per halaman
     */
    public static function adminList(
        string $division = '',
        string $search = '',
        string $sort = 'sort_order',
        string $order = 'asc',
        int $page = 1,
        int $perPage = 12
    ): array {
        $offset = max(0, ($page - 1) * $perPage);
        $params = [];
        $where  = [];

        $sql = 'SELECT * FROM officers';

        if ($division !== '') {
            $where[] = 'division = :div';
            $params[':div'] = $division;
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort
        if (!in_array($sort, self::SORT_WHITELIST, true)) $sort = 'sort_order';
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sort $order, id ASC LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList.
     */
    public static function countAdminList(string $division = '', string $search = ''): int
    {
        $params = [];
        $where  = [];

        $sql = 'SELECT COUNT(*) FROM officers';

        if ($division !== '') {
            $where[] = 'division = :div';
            $params[':div'] = $division;
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3)';
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
     * Create pengurus (backward compat + extended).
     * Extended: support social media (JSON).
     */
    public static function create(array $d): int
    {
        $social = !empty($d['social']) ? (is_array($d['social']) ? json_encode($d['social']) : $d['social']) : null;

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO officers (full_name, position, division, photo, sort_order, bio, social)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['full_name'],
            $d['position'],
            $d['division'] ?? null,
            $d['photo'] ?? null,
            (int) ($d['sort_order'] ?? 0),
            $d['bio'] ?? null,
            $social,
        ]);

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update pengurus (backward compat + extended).
     * Extended: support social media (JSON).
     */
    public static function update(int $id, array $d): void
    {
        $social = !empty($d['social']) ? (is_array($d['social']) ? json_encode($d['social']) : $d['social']) : null;

        Database::getInstance()->prepare(
            'UPDATE officers SET full_name = ?, position = ?, division = ?, photo = ?, sort_order = ?, bio = ?, social = ? WHERE id = ?'
        )->execute([
            $d['full_name'],
            $d['position'],
            $d['division'] ?? null,
            $d['photo'] ?? null,
            (int) ($d['sort_order'] ?? 0),
            $d['bio'] ?? null,
            $social,
            $id,
        ]);

        self::clearCache();
    }

    /**
     * Delete pengurus dengan cleanup photo (backward compat + extended).
     */
    public static function delete(int $id): void
    {
        // Ambil row dulu untuk cleanup
        $row = self::find($id);

        Database::getInstance()->prepare('DELETE FROM officers WHERE id = ?')->execute([$id]);

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
     * Reorder pengurus (backward compat + extended validation).
     * Extended: validate input + return affected count.
     *
     * @param array<int> $ids Array of IDs in new order
     * @return int Jumlah yang berhasil diupdate
     */
    public static function reorder(array $ids): int
    {
        if (empty($ids)) return 0;

        // Validate & sanitize
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) return 0;

        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $affected = 0;
            foreach ($ids as $order => $id) {
                $stmt = $pdo->prepare('UPDATE officers SET sort_order = ? WHERE id = ?');
                $stmt->execute([$order, $id]);
                $affected += $stmt->rowCount();
            }
            $pdo->commit();
            self::clearCache();
            return $affected;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
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
            "SELECT id, photo FROM officers WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));
        $rows = $stmt->fetchAll();

        if (empty($rows)) return 0;

        // Delete
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM officers WHERE id IN ($placeholders)"
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
     * Bulk update division.
     *
     * @return int Jumlah yang berhasil diupdate
     */
    public static function bulkUpdateDivision(array $ids, string $division): int
    {
        if (empty($ids)) return 0;
        $ids = array_map('intval', array_filter($ids));
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE officers SET division = ? WHERE id IN ($placeholders)"
        );
        $params = array_merge([$division], array_values($ids));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }

    /* ============================================================
       EXPORT
       ============================================================ */

    /**
     * Semua pengurus untuk export.
     */
    public static function allForExport(): array
    {
        $rows = Database::getInstance()->query(
            'SELECT * FROM officers ORDER BY division ASC, sort_order ASC, full_name ASC'
        )->fetchAll();
        return self::decorateAll($rows);
    }

    /**
     * Convert row ke format CSV-friendly.
     */
    public static function toCsvRow(array $row): array
    {
        return [
            'ID'        => $row['id'] ?? '',
            'Nama'      => $row['full_name'] ?? '',
            'Jabatan'   => $row['position'] ?? '',
            'Divisi'    => $row['division'] ?? '',
            'Urutan'    => $row['sort_order'] ?? '',
            'Bio'       => $row['bio'] ?? '',
        ];
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
     * Get next sort_order value.
     */
    public static function getNextSortOrder(): int
    {
        $stmt = Database::getInstance()->query(
            'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM officers'
        );
        return (int) $stmt->fetchColumn();
    }

    /* ============================================================
       MAINTENANCE
       ============================================================ */

    /**
     * Cleanup orphan files (file yang tidak ada di database).
     *
     * @param bool $dryRun Jika true, hanya return list file tanpa hapus
     * @return array List file yang orphan
     */
    public static function cleanupOrphanFiles(bool $dryRun = true): array
    {
        $uploadPath = self::getPhotoPath('');
        if (!$uploadPath || !is_dir($uploadPath)) return [];

        // Ambil semua filename dari database
        $dbFiles = array_column(
            Database::getInstance()->query('SELECT photo FROM officers')->fetchAll(),
            'photo'
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