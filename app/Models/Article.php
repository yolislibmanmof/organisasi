<?php
declare(strict_types=1);

namespace Models;

use Core\Database;
use Core\Cache;

class Article {
    private static function decorate(array $row): array {
        $row['author_name'] = $row['username'] ?? 'Redaksi';
        $row['read_time'] = max(1, (int) ceil(str_word_count(strip_tags($row['content'] ?? '')) / 200));
        return $row;
    }

    public static function all(): array {
        return self::decorateAll(
            Database::getInstance()->query(
                'SELECT a.*, u.username FROM articles a 
                 LEFT JOIN users u ON u.id = a.created_by 
                 ORDER BY a.created_at DESC'
            )->fetchAll()
        );
    }

    private static function decorateAll(array $rows): array {
        return array_map([self::class, 'decorate'], $rows);
    }

    public static function published(int $limit = 0): array {
        $cacheKey = 'articles_published_' . $limit;
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $sql = 'SELECT a.*, u.username FROM articles a 
                LEFT JOIN users u ON u.id = a.created_by 
                ORDER BY a.created_at DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;
        
        $result = self::decorateAll(
            Database::getInstance()->query($sql)->fetchAll()
        );
        
        Cache::set($cacheKey, $result, 300); // 5 menit
        return $result;
    }

    public static function countAll(): int {
        return (int) Database::getInstance()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    }

    public static function countByCategory(): array {
        $cacheKey = 'articles_count_by_category';
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $stmt = Database::getInstance()->query(
            'SELECT category, COUNT(*) as count FROM articles GROUP BY category ORDER BY count DESC'
        );
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['category']] = (int) $row['count'];
        }
        
        Cache::set($cacheKey, $result, 300);
        return $result;
    }

    public static function categories(): array {
        $counts = self::countByCategory();
        return array_keys($counts);
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username FROM articles a 
             LEFT JOIN users u ON u.id = a.created_by 
             WHERE a.id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : self::decorate($row);
    }

    public static function search(string $keyword = '', string $category = '', int $page = 1, int $perPage = 6): array {
        $like = '%' . $keyword . '%';
        $offset = ($page - 1) * $perPage;
        
        $where = [];
        $params = [];
        
        if ($keyword !== '') {
            $where[] = '(a.title LIKE :q1 OR a.excerpt LIKE :q2 OR a.content LIKE :q3)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        
        if ($category !== '') {
            $where[] = 'a.category = :cat';
            $params[':cat'] = $category;
        }
        
        $sql = 'SELECT a.*, u.username FROM articles a 
                LEFT JOIN users u ON u.id = a.created_by';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY a.created_at DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
        
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::decorateAll($stmt->fetchAll());
    }

    public static function countSearch(string $keyword = '', string $category = ''): int {
        $like = '%' . $keyword . '%';
        
        $where = [];
        $params = [];
        
        if ($keyword !== '') {
            $where[] = '(a.title LIKE :q1 OR a.excerpt LIKE :q2 OR a.content LIKE :q3)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        
        if ($category !== '') {
            $where[] = 'a.category = :cat';
            $params[':cat'] = $category;
        }
        
        $sql = 'SELECT COUNT(*) FROM articles a';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function latest(int $limit = 3): array {
        return self::published($limit);
    }

    public static function create(array $d, int $authorId): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO articles (title, category, excerpt, content, created_by) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$d['title'], $d['category'], $d['excerpt'], $d['content'], $authorId]);
        
        Cache::delete('articles_published_0');
        Cache::delete('articles_count_by_category');
        
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE articles SET title = ?, category = ?, excerpt = ?, content = ? WHERE id = ?'
        )->execute([$d['title'], $d['category'], $d['excerpt'], $d['content'], $id]);
        
        Cache::delete('articles_published_0');
        Cache::delete('articles_count_by_category');
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
        
        Cache::delete('articles_published_0');
        Cache::delete('articles_count_by_category');
    }

    public static function related(int $currentId, string $category, int $limit = 3): array {
        $stmt = Database::getInstance()->prepare(
            'SELECT a.*, u.username FROM articles a 
             LEFT JOIN users u ON u.id = a.created_by 
             WHERE a.category = :cat AND a.id != :id 
             ORDER BY a.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([':cat' => $category, ':id' => $currentId]);
        return self::decorateAll($stmt->fetchAll());
    }
}