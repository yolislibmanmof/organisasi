<?php
// File: app/Models/Gallery.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class Gallery {
    public static function all(int $limit = 0): array {
        $sql = 'SELECT * FROM galleries ORDER BY id DESC';
        if ($limit > 0) $sql .= ' LIMIT ' . (int) $limit;
        return Database::getInstance()->query($sql)->fetchAll();
    }
    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM galleries WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }
    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare('INSERT INTO galleries (title, image) VALUES (?, ?)');
        $stmt->execute([$d['title'], $d['image']]);
        return (int) Database::getInstance()->lastInsertId();
    }
    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare('UPDATE galleries SET title = ?, image = ? WHERE id = ?')
            ->execute([$d['title'], $d['image'], $id]);
    }
    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM galleries WHERE id = ?')->execute([$id]);
    }
}