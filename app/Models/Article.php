<?php
// File: app/Models/Article.php (FINAL v7.0 — KOMPATIBEL PENUH + CACHED + EXTENDED)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

/**
 * Model Article — Ultimate Edition v7.0
 *
 * Backward-compatible dengan signature v5.9 + extension untuk fitur v7.0:
 * - Search parameter (public + admin)
 * - Cover image, author photo, author email
 * - Views tracking & increment
 * - Admin stats & filter by status
 * - Prev/Next navigation
 * - Featured article
 * - Granular cache invalidation
 */
class Article
{
    /** Kategori bawaan (fallback bila tabel kosong) */
    public const CATEGORIES = ['Artikel', 'Berita', 'Edukasi', 'Podcast', 'Hari Besar'];

    /** Status artikel */
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_DRAFT     = 'draft';

    /** Cache TTL default (5 menit) */
    private const CACHE_TTL = 300;

    /* ============================================================
       DECORATOR
       ============================================================ */

    /**
     * Tambah author_name, author_photo, author_email, read_time, views.
     * backward-compatible dengan struktur lama.
     */
    private static function decorate(array $row): array
    {
        // Author info — backward compat
        $row['author_name']  = $row['author_name']  ?? ($row['username'] ?? 'Redaksi');
        $row['author_photo'] = $row['author_photo'] ?? ($row['photo'] ?? '');
        $row['author_email'] = $row['author_email'] ?? ($row['email'] ?? '');

        // Read time (200 kata/menit)
        $row['read_time'] = max(1, (int) ceil(str_word_count(strip_tags($row['content'] ?? '')) / 200));

        // Views (default 0 jika kolom belum ada)
        $row['views'] = (int) ($row['views'] ?? 0);

        // Cover image — kosong jika tidak ada
        $row['cover_image'] = $row['cover_image'] ?? '';

        // Status — default published (backward compat dengan row lama)
        $row['status'] = $row['status'] ?? self::STATUS_PUBLISHED;

        // Word count untuk UI
        $row['word_count'] = (int) str_word_count(strip_tags($row['content'] ?? ''));

        return $row;
    }

    private static function decorateAll(array $rows): array
    {
        return array_map([self::class, 'decorate'], $rows);
    }

    /* ============================================================
       CACHE HELPERS
       ============================================================ */

    /** Flush cache spesifik (lebih granular dari flush global) */
    private static function clearCache(): void
    {
        Cache::flush('articles');
    }

    /** Build cache key dengan prefix konsisten */
    private static function cacheKey(string $suffix): string
    {
        return 'articles|' . $suffix;
    }

    /* ============================================================
       SISI PUBLIK
       ============================================================ */

    /**
     * Ambil artikel yang sudah published (dengan search & kategori).
     *
     * Signature lama dipertahankan: (kategori, halaman, perPage)
     * Extended: parameter ke-4 `$search` untuk fitur pencarian v7.0
     */
    public static function published(
        string $category = '',
        int $page = 1,
        int $perPage = 6,
        string $search = ''
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];

        $sql = 'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
                FROM articles a
                LEFT JOIN users u ON u.id = a.created_by
                WHERE a.status = :status';
        $params[':status'] = self::STATUS_PUBLISHED;

        if ($category !== '') {
            $sql .= ' AND a.category = :c';
            $params[':c'] = $category;
        }

        if ($search !== '') {
            $sql .= ' AND (a.title LIKE :q1 OR a.excerpt LIKE :q2 OR a.category LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $sql .= ' ORDER BY a.created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count artikel published (dengan filter kategori & search).
     */
    public static function countPublished(string $category = '', string $search = ''): int
    {
        $params = [':status' => self::STATUS_PUBLISHED];
        $sql = 'SELECT COUNT(*) FROM articles WHERE status = :status';

        if ($category !== '') {
            $sql .= ' AND category = :c';
            $params[':c'] = $category;
        }

        if ($search !== '') {
            $sql .= ' AND (title LIKE :q1 OR excerpt LIKE :q2 OR category LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Total artikel (semua status). Digunakan admin.
     */
    public static function countAll(): int
    {
        return (int) Database::getInstance()
            ->query('SELECT COUNT(*) FROM articles')
            ->fetchColumn();
    }

    /**
     * Jumlah artikel per kategori (cached 5 menit).
     * Hanya menghitung yang published untuk tampilan publik.
     */
    public static function countByCategory(bool $onlyPublished = true): array
    {
        $suffix = 'count_by_category' . ($onlyPublished ? '_pub' : '_all');
        $cacheKey = self::cacheKey($suffix);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $sql = 'SELECT category, COUNT(*) AS total FROM articles';
        if ($onlyPublished) {
            $sql .= ' WHERE status = :status';
        }
        $sql .= ' GROUP BY category ORDER BY total DESC';

        $stmt = Database::getInstance()->prepare($sql);
        if ($onlyPublished) {
            $stmt->execute([':status' => self::STATUS_PUBLISHED]);
        } else {
            $stmt->execute();
        }

        $result = [];
        foreach ($stmt->fetchAll() as $r) {
            $result[$r['category']] = (int) $r['total'];
        }

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Total views semua artikel (cached 5 menit).
     */
    public static function totalViews(): int
    {
        $cacheKey = self::cacheKey('total_views');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return (int) $cached;

        $total = (int) Database::getInstance()
            ->query('SELECT COALESCE(SUM(views), 0) FROM articles')
            ->fetchColumn();

        Cache::set($cacheKey, $total, self::CACHE_TTL);
        return $total;
    }

    /** Daftar kategori aktif (fallback ke CATEGORIES bila kosong) */
    public static function categories(): array
    {
        $keys = array_keys(self::countByCategory());
        return !empty($keys) ? $keys : self::CATEGORIES;
    }

    /**
     * Cari artikel by ID (publik, hanya yang published).
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = :id AND a.status = :status
             LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':status' => self::STATUS_PUBLISHED]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Cari artikel by ID (admin, semua status).
     */
    public static function findAny(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Increment views counter (panggil di detail page).
     * Tidak cache-aware karena harus real-time.
     */
    public static function incrementViews(int $id): void
    {
        Database::getInstance()->prepare(
            'UPDATE articles SET views = COALESCE(views, 0) + 1 WHERE id = ?'
        )->execute([$id]);

        // Invalidate total views cache
        Cache::forget(self::cacheKey('total_views'));
    }

    /**
     * Featured article (artikel published paling baru).
     * Digunakan di list article sebagai hero card.
     */
    public static function featured(): ?array
    {
        $cacheKey = self::cacheKey('featured');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.status = :status
             ORDER BY a.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([':status' => self::STATUS_PUBLISHED]);
        $row = $stmt->fetch();
        $result = $row === false ? null : self::decorate($row);

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Signature lama dipertahankan: (limit, excludeId) — cached 5 menit
     */
    public static function latest(int $limit = 3, int $excludeId = 0): array
    {
        $cacheKey = self::cacheKey('latest_' . $limit . '_' . $excludeId);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.status = :status AND a.id <> :ex
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([':status' => self::STATUS_PUBLISHED, ':ex' => $excludeId]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /** METHOD BARU: dipakai DashboardController untuk feed aktivitas */
    public static function recent(int $limit = 3): array
    {
        return self::latest($limit);
    }

    /**
     * Artikel terkait berdasarkan kategori (cached per artikel).
     */
    public static function related(int $currentId, string $category, int $limit = 3): array
    {
        $cacheKey = self::cacheKey('related_' . $currentId . '_' . $limit);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.status = :status AND a.category = :cat AND a.id <> :id
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([
            ':status' => self::STATUS_PUBLISHED,
            ':cat'    => $category,
            ':id'     => $currentId,
        ]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, self::CACHE_TTL);
        return $result;
    }

    /**
     * Artikel sebelumnya (untuk navigasi prev/next di detail).
     */
    public static function prev(int $currentId, string $currentDate): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.status = :status AND a.created_at < :dt
             ORDER BY a.created_at DESC
             LIMIT 1'
        );
        $stmt->execute([':status' => self::STATUS_PUBLISHED, ':dt' => $currentDate]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /**
     * Artikel berikutnya (untuk navigasi prev/next di detail).
     */
    public static function next(int $currentId, string $currentDate): ?array
    {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name
             FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.status = :status AND a.created_at > :dt
             ORDER BY a.created_at ASC
             LIMIT 1'
        );
        $stmt->execute([':status' => self::STATUS_PUBLISHED, ':dt' => $currentDate]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /* ============================================================
       SISI ADMIN
       ============================================================ */

    /**
     * Statistik untuk admin dashboard (articles page).
     * Return: [total, published, draft, total_views]
     */
    public static function adminStats(): array
    {
        $cacheKey = self::cacheKey('admin_stats');
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $db = Database::getInstance();
        $total     = (int) $db->query('SELECT COUNT(*) FROM articles')->fetchColumn();
        $published = (int) $db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
        $draft     = (int) $db->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();
        $views     = (int) $db->query('SELECT COALESCE(SUM(views), 0) FROM articles')->fetchColumn();

        $stats = [
            'total'       => $total,
            'published'   => $published,
            'draft'       => $draft,
            'total_views' => $views,
        ];

        Cache::set($cacheKey, $stats, self::CACHE_TTL);
        return $stats;
    }

    /**
     * List artikel untuk admin (dengan filter status + search + pagination + sort).
     */
    public static function adminList(
        string $status = '',
        string $search = '',
        string $category = '',
        int $page = 1,
        int $perPage = 10,
        string $sort = 'created_at',
        string $order = 'desc'
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = [];

        $sql = 'SELECT a.*, u.username AS author_name, u.photo AS author_photo, u.email AS author_email
                FROM articles a
                LEFT JOIN users u ON u.id = a.created_by';

        if ($status !== '' && in_array($status, [self::STATUS_PUBLISHED, self::STATUS_DRAFT], true)) {
            $where[] = 'a.status = :status';
            $params[':status'] = $status;
        }

        if ($category !== '') {
            $where[] = 'a.category = :cat';
            $params[':cat'] = $category;
        }

        if ($search !== '') {
            $where[] = '(a.title LIKE :q1 OR a.category LIKE :q2 OR u.username LIKE :q3)';
            $like = '%' . $search . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // Safe sort — whitelist kolom yang boleh di-sort
        $sortWhitelist = ['created_at', 'title', 'category', 'views', 'author_name'];
        $sortColumn = in_array($sort, $sortWhitelist, true) ? $sort : 'created_at';
        
        // Map column ke table prefix
        if ($sortColumn === 'author_name') {
            $sortColumn = 'u.username';
        } else {
            $sortColumn = 'a.' . $sortColumn;
        }
        
        $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sortColumn $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    /**
     * Count untuk adminList.
     */
    public static function countAdminList(
        string $status = '',
        string $search = '',
        string $category = ''
    ): int {
        $params = [];
        $where = [];

        $sql = 'SELECT COUNT(*) FROM articles a LEFT JOIN users u ON u.id = a.created_by';

        if ($status !== '' && in_array($status, [self::STATUS_PUBLISHED, self::STATUS_DRAFT], true)) {
            $where[] = 'a.status = :status';
            $params[':status'] = $status;
        }

        if ($category !== '') {
            $where[] = 'a.category = :cat';
            $params[':cat'] = $category;
        }

        if ($search !== '') {
            $where[] = '(a.title LIKE :q1 OR a.category LIKE :q2 OR u.username LIKE :q3)';
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

    /**
     * Signature lama dipertahankan: search publik.
     * (backward compat untuk controller lama)
     */
    public static function search(string $q = '', int $page = 1, int $perPage = 8): array
    {
        return self::published('', $page, $perPage, $q);
    }

    public static function countSearch(string $q = ''): int
    {
        return self::countPublished('', $q);
    }

    /**
     * Buat artikel baru.
     * Extended: support cover_image dan status.
     */
    public static function create(array $d, int $authorId): int
    {
        $status     = $d['status'] ?? self::STATUS_PUBLISHED;
        $coverImage = $d['cover_image'] ?? '';
        $excerpt    = $d['excerpt'] ?? '';

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO articles (title, category, excerpt, content, cover_image, status, created_by, views)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0)'
        );
        $stmt->execute([
            $d['title'],
            $d['category'],
            $excerpt,
            $d['content'],
            $coverImage,
            $status,
            $authorId,
        ]);

        self::clearCache();
        return (int) Database::getInstance()->lastInsertId();
    }

    /**
     * Update artikel.
     * Extended: support cover_image dan status.
     */
    public static function update(int $id, array $d): void
    {
        $status     = $d['status'] ?? self::STATUS_PUBLISHED;
        $coverImage = $d['cover_image'] ?? null;

        if ($coverImage !== null) {
            Database::getInstance()->prepare(
                'UPDATE articles
                 SET title = ?, category = ?, excerpt = ?, content = ?, cover_image = ?, status = ?
                 WHERE id = ?'
            )->execute([
                $d['title'],
                $d['category'],
                $d['excerpt'] ?? '',
                $d['content'],
                $coverImage,
                $status,
                $id,
            ]);
        } else {
            Database::getInstance()->prepare(
                'UPDATE articles
                 SET title = ?, category = ?, excerpt = ?, content = ?, status = ?
                 WHERE id = ?'
            )->execute([
                $d['title'],
                $d['category'],
                $d['excerpt'] ?? '',
                $d['content'],
                $status,
                $id,
            ]);
        }

        self::clearCache();
    }

    /**
     * Hapus artikel + cleanup file cover (jika ada helper).
     */
    public static function delete(int $id): void
    {
        // Ambil cover image dulu untuk cleanup
        $row = self::findAny($id);
        Database::getInstance()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);

        // Cleanup file cover (best-effort, tidak fatal jika gagal)
        if ($row && !empty($row['cover_image'])) {
            $path = public_path('assets/uploads/articles/' . $row['cover_image']);
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }

        self::clearCache();
    }

    /**
     * Bulk delete — untuk bulk action di admin.
     */
    public static function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "DELETE FROM articles WHERE id IN ($placeholders)"
        );
        $stmt->execute(array_values($ids));

        self::clearCache();
        return $stmt->rowCount();
    }

    /**
     * Bulk update status — untuk bulk action di admin.
     */
    public static function bulkUpdateStatus(array $ids, string $status): int
    {
        if (empty($ids)) return 0;
        if (!in_array($status, [self::STATUS_PUBLISHED, self::STATUS_DRAFT], true)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::getInstance()->prepare(
            "UPDATE articles SET status = ? WHERE id IN ($placeholders)"
        );
        $params = array_merge([$status], array_values($ids));
        $stmt->execute($params);

        self::clearCache();
        return $stmt->rowCount();
    }
}