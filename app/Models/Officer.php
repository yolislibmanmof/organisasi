<?php
// File: app/Models/Officer.php (FINAL - TAHAP 5.6)
declare(strict_types=1);

namespace Models;

use Core\Database;

class Officer {
    public static function all(): array {
        return Database::getInstance()->query('SELECT * FROM officers ORDER BY sort_order ASC, id ASC')->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = Database::getInstance()->prepare('SELECT * FROM officers WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public static function create(array $d): int {
        $stmt = Database::getInstance()->prepare(
            'INSERT INTO officers (full_name, position, photo, sort_order, bio) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$d['full_name'], $d['position'], $d['photo'] ?? null, (int) ($d['sort_order'] ?? 0), $d['bio'] ?? null]);
        return (int) Database::getInstance()->lastInsertId();
    }

    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare(
            'UPDATE officers SET full_name = ?, position = ?, photo = ?, sort_order = ?, bio = ? WHERE id = ?'
        )->execute([$d['full_name'], $d['position'], $d['photo'] ?? null, (int) ($d['sort_order'] ?? 0), $d['bio'] ?? null, $id]);
    }

    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM officers WHERE id = ?')->execute([$id]);
    }
}