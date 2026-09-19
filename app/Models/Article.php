<?php
// File: app/Models/Article.php (FINAL v5.9 — KOMPATIBEL PENUH + CACHED)
declare(strict_types=1);

namespace Models;

use Core\Cache;
use Core\Database;

class Article {
    /** Kategori bawaan (dipakai controller & view lama) */
    public const CATEGORIES = ['Artikel', 'Berita', 'Edukasi', 'Podcast', 'Hari Besar'];

    /** Tambah author_name & estimasi waktu baca */
    private static function decorate(array $row): array {
        $row['author_name'] = $row['author_name'] ?? ($row['username'] ?? 'Redaksi');
        $row['read_time']   = max(1, (int) ceil(str_word_count(strip_tags($row['content'] ?? '')) / 200));
        return $row;
    }

    private static function decorateAll(array $rows): array {
        return array_map([self::class, 'decorate'], $rows);
    }

    /* ============================================================
       SISI PUBLIK
       ============================================================ */

    /** Signature lama dipertahankan: (kategori, halaman, perPage) */
    public static function published(string $category = '', int $page = 1, int $perPage = 6): array {
        $offset = ($page - 1) * $perPage;
        $sql    = 'SELECT a.*, u.username AS author_name FROM articles a LEFT JOIN users u ON u.id = a.created_by';
        $params = [];
        if ($category !== '') {
            $sql .= ' WHERE a.category = :c';
            $params[':c'] = $category;
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countPublished(string $category = ''): int {
        if ($category !== '') {
            $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM articles WHERE category = :c');
            $stmt->execute([':c' => $category]);
            return (int) $stmt->fetchColumn();
        }
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    }

    public static function countAll(): int {
        return self::countPublished();
    }

    /** Jumlah artikel per kategori (cached 5 menit) */
    public static function countByCategory(): array {
        $cacheKey = 'articles|count_by_category';
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $result = [];
        $rows = Database::getInstance()->query(
            'SELECT category, COUNT(*) AS total FROM articles GROUP BY category ORDER BY total DESC'
        )->fetchAll();
        foreach ($rows as $r) {
            $result[$r['category']] = (int) $r['total'];
        }

        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    /** Daftar kategori aktif (fallback ke CATEGORIES bila kosong) */
    public static function categories(): array {
        $keys = array_keys(self::countByCategory());
        return !empty($keys) ? $keys : self::CATEGORIES;
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    /** Signature lama dipertahankan: (limit, excludeId) — cached 5 menit */
    public static function latest(int $limit = 3, int $excludeId = 0): array {
        $cacheKey = 'articles|latest_' . $limit . '_' . $excludeId;
        $cached = Cache::get($cacheKey);
        if ($cached !== null) return $cached;

        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id <> :ex
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([':ex' => $excludeId]);
        $result = self::decorateAll($stmt->fetchAll());

        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    /** METHOD BARU: dipakai DashboardController untuk feed aktivitas */
    public static function recent(int $limit = 3): array {
        return self::latest($limit);
    }

    /** Artikel terkait berdasarkan kategori */
    public static function related(int $currentId, string $category, int $limit = 3): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.category = :cat AND a.id <> :id
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([':cat' => $category, ':id' => $currentId]);
        return self::decorateAll($stmt->fetchAll());
    }

    /* ============================================================
       SISI ADMIN
       ============================================================ */
    public static function search(string $q = '', int $page = 1, int $perPage = 8): array {
        $like   = '%' . $q . '%';
        $offset = ($page - 1) * $perPage;
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE (a.title LIKE :q1 OR a.category LIKE :q2)
             ORDER BY a.created_at DESC
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $q = ''): int {
        $like = '%' . $q . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM articles a WHERE (a.title LIKE :q1 OR a.category LIKE :q2)'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(array $d, int $authorId): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO articles (title, category, excerpt, content, created_by) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$d['title'], $d['category'], $d['excerpt'], $d['content'], $authorId]);
        Cache::flush('articles');
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE articles SET title = ?, category = ?, excerpt = ?, content = ? WHERE id = ?'
        )->execute([$d['title'], $d['category'], $d['excerpt'], $d['content'], $id]);
        Cache::flush('articles');
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
        Cache::flush('articles');
    }
}