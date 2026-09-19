<?php
declare(strict_types=1);

namespace Models;

use Core\Database;
use Core\Cache;

class Gallery {
    public static function all(int $limit = 0): array {
        $cacheKey = 'galleries_all_' . $limit;
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $sql = 'SELECT * FROM galleries ORDER BY event_date DESC, id DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;
        
        $result = Database::getInstance()->query($sql)->fetchAll();
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM galleries')->fetchColumn();
    }

    public static function publicAll(int $page = 1, int $perPage = 12): array {
        $offset = ($page - 1) * $perPage;
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries ORDER BY event_date DESC, id DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countPublic(): int {
        return self::countAll();
    }

    public static function search(string $keyword = '', int $page = 1, int $perPage = 12): array {
        $like = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries 
             WHERE title LIKE :q1 OR location LIKE :q2 
             ORDER BY event_date DESC, id DESC 
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return $stmt->fetchAll();
    }

    public static function countSearch(string $keyword = ''): int {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM galleries WHERE title LIKE :q1 OR location LIKE :q2'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function uniqueYears(): array {
        $cacheKey = 'galleries_years';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $stmt = Database::getInstance()->query(
            'SELECT DISTINCT YEAR(event_date) as year FROM galleries WHERE event_date IS NOT NULL ORDER BY year DESC'
        );
        $result = array_column($stmt->fetchAll(), 'year');
        
        Cache::set($cacheKey, $result, 600);
        return $result;
    }

    public static function uniqueLocations(): array {
        $cacheKey = 'galleries_locations';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $stmt = Database::getInstance()->query(
            'SELECT DISTINCT location FROM galleries WHERE location IS NOT NULL AND location != "" ORDER BY location'
        );
        $result = array_column($stmt->fetchAll(), 'location');
        
        Cache::set($cacheKey, $result, 600);
        return $result;
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM galleries WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO galleries (title, image, event_date, location) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$d['title'], $d['image'], $d['event_date'] ?: null, $d['location'] ?: null]);
        
        Cache::flush('galleries_');
        
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE galleries SET title = ?, image = ?, event_date = ?, location = ? WHERE id = ?'
        )->execute([$d['title'], $d['image'], $d['event_date'] ?: null, $d['location'] ?: null, $id]);
        
        Cache::flush('galleries_');
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM galleries WHERE id = ?')->execute([$id]);
        Cache::flush('galleries_');
    }

    public static function byYear(int $year, int $page = 1, int $perPage = 12): array {
        $offset = ($page - 1) * $perPage;
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM galleries WHERE YEAR(event_date) = :year ORDER BY event_date DESC, id DESC 
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    public static function countByYear(int $year): int {
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM galleries WHERE YEAR(event_date) = :year'
        );
        $stmt->execute([':year' => $year]);
        return (int) $stmt->fetchColumn();
    }
}