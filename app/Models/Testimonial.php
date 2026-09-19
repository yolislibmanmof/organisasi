<?php
declare(strict_types=1);

namespace Models;

use Core\Database;
use Core\Cache;

class Testimonial {
    public static function all(): array {
        $cacheKey = 'testimonials_all';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $result = Database::getInstance()->query(
            'SELECT * FROM testimonials ORDER BY id DESC'
        )->fetchAll();
        
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
    }

    public static function latest(int $limit = 10): array {
        $cacheKey = 'testimonials_latest_' . $limit;
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM testimonials ORDER BY id DESC LIMIT ' . (int) $limit
        );
        $stmt->execute();
        $result = $stmt->fetchAll();
        
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function search(string $keyword = '', int $page = 1, int $perPage = 10): array {
        $like = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM testimonials 
             WHERE name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3 
             ORDER BY id DESC 
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return $stmt->fetchAll();
    }

    public static function countSearch(string $keyword = ''): int {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM testimonials WHERE name LIKE :q1 OR role LIKE :q2 OR quote LIKE :q3'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM testimonials WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare('INSERT INTO testimonials (name, role, quote) VALUES (?, ?, ?)');
        $stmt->execute([$d['name'], $d['role'] ?? null, $d['quote']]);
        
        Cache::flush('testimonials_');
        
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare('UPDATE testimonials SET name = ?, role = ?, quote = ? WHERE id = ?')
            ->execute([$d['name'], $d['role'] ?? null, $d['quote'], $id]);
        
        Cache::flush('testimonials_');
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
        Cache::flush('testimonials_');
    }
}