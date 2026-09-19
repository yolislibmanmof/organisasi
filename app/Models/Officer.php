<?php
declare(strict_types=1);

namespace Models;

use Core\Database;
use Core\Cache;

class Officer {
    public static function all(): array {
        $cacheKey = 'officers_all';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $result = Database::getInstance()->query(
            'SELECT * FROM officers ORDER BY sort_order ASC, id ASC'
        )->fetchAll();
        
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM officers')->fetchColumn();
    }

    public static function countByDivision(): array {
        $cacheKey = 'officers_count_by_division';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $stmt = Database::getInstance()->query(
            'SELECT division, COUNT(*) as count FROM officers 
             GROUP BY division ORDER BY division'
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $divName = $row['division'] ?: 'Lainnya';
            $result[$divName] = (int) $row['count'];
        }
        
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function divisions(): array {
        $counts = self::countByDivision();
        return array_keys($counts);
    }

    public static function groupByDivision(): array {
        $cacheKey = 'officers_by_division';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $all = self::all();
        $groups = [];
        foreach ($all as $o) {
            $div = !empty($o['division']) ? $o['division'] : 'Lainnya';
            if (!isset($groups[$div])) $groups[$div] = [];
            $groups[$div][] = $o;
        }
        
        Cache::set($cacheKey, $groups, 300);
        return $groups;
    }

    public static function byDivision(string $division): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM officers WHERE division = :div ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([':div' => $division]);
        return $stmt->fetchAll();
    }

    public static function search(string $keyword = '', int $page = 1, int $perPage = 10): array {
        $like = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        
        $stmt = Database::getInstance()->prepare(
            'SELECT * FROM officers 
             WHERE full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3 
             ORDER BY sort_order ASC, id ASC 
             LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return $stmt->fetchAll();
    }

    public static function countSearch(string $keyword = ''): int {
        $like = '%' . $keyword . '%';
        $stmt = Database::getInstance()->prepare(
            'SELECT COUNT(*) FROM officers WHERE full_name LIKE :q1 OR position LIKE :q2 OR division LIKE :q3'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);
        return (int) $stmt->fetchColumn();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM officers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO officers (full_name, position, division, photo, sort_order, bio) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['full_name'],
            $d['position'],
            $d['division'] ?? null,
            $d['photo'] ?? null,
            (int) ($d['sort_order'] ?? 0),
            $d['bio'] ?? null,
        ]);
        
        Cache::flush('officers_');
        
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE officers SET full_name = ?, position = ?, division = ?, photo = ?, sort_order = ?, bio = ? WHERE id = ?'
        )->execute([
            $d['full_name'],
            $d['position'],
            $d['division'] ?? null,
            $d['photo'] ?? null,
            (int) ($d['sort_order'] ?? 0),
            $d['bio'] ?? null,
            $id,
        ]);
        
        Cache::flush('officers_');
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM officers WHERE id = ?')->execute([$id]);
        Cache::flush('officers_');
    }

    public static function reorder(array $ids): void {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            foreach ($ids as $order => $id) {
                $pdo->prepare('UPDATE officers SET sort_order = ? WHERE id = ?')
                    ->execute([$order, $id]);
            }
            $pdo->commit();
            Cache::flush('officers_');
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}