<?php
// File: app/Models/Article.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class Article {
    public const CATEGORIES = ['Artikel', 'Berita', 'Edukasi', 'Podcast', 'Hari Besar'];

    /* ---------- SISI PUBLIK ---------- */
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
        return $stmt->fetchAll();
    }

    public static function countPublished(string $category = ''): int {
        if ($category !== '') {
            $stmt = Database::getInstance()->prepare('SELECT COUNT(*) FROM articles WHERE category = :c');
            $stmt->execute([':c' => $category]);
            return (int) $stmt->fetchColumn();
        }
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function latest(int $limit = 3, int $excludeId = 0): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username AS author_name FROM articles a
             LEFT JOIN users u ON u.id = a.created_by
             WHERE a.id <> :ex
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([':ex' => $excludeId]);
        return $stmt->fetchAll();
    }

    /* ---------- SISI ADMIN ---------- */
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
        return $stmt->fetchAll();
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
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE articles SET title = ?, category = ?, excerpt = ?, content = ? WHERE id = ?'
        )->execute([$d['title'], $d['category'], $d['excerpt'], $d['content'], $id]);
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
    }
}