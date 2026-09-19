<?php
// File: app/Models/Testimonial.php
declare(strict_types=1);

namespace Models;

use Core\Database;

class Testimonial {
    public static function all(): array {
        return Database::getInstance()->query('SELECT * FROM testimonials ORDER BY id DESC')->fetchAll();
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
        return (int) Database::getInstance()->lastInsertId();
    }
    public static function update(int $id, array $d): void {
        Database::getInstance()->prepare('UPDATE testimonials SET name = ?, role = ?, quote = ? WHERE id = ?')
            ->execute([$d['name'], $d['role'] ?? null, $d['quote'], $id]);
    }
    public static function delete(int $id): void {
        Database::getInstance()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    }
}